<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Yaml\Yaml;
use Carbon\Carbon;
use Symfony\Component\HttpClient\HttpClient;

use App\Entity\User;
use App\Entity\Vehicles;
use App\Entity\UserNotify;

class SendDeadlinesCommand extends Command
{
    protected static $defaultName = 'app:send-deadlines';
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        $vehicles = $this->em->getRepository(Vehicles::class)->findAll(); // pobranie terminów
        $settings = Yaml::parseFile('config/settings.yaml'); // pobranie ustawień

        foreach($vehicles as $vehicle) {
            $now = Carbon::now();

            $this->deadlinesSendVerify($now, 'overview', 'getDateOverview', $vehicle, $settings);
            $this->deadlinesSendVerify($now, 'oil', 'getDateOil', $vehicle, $settings);
            $this->deadlinesSendVerify($now, 'udt', 'getDateUdt', $vehicle, $settings);
            $this->deadlinesSendVerify($now, 'documents', 'getDateDocuments', $vehicle, $settings);
            $this->deadlinesSendVerify($now, 'oc', 'getDateInsurance', $vehicle, $settings);
            $this->deadlinesSendVerify($now, 'warranty', 'getDateWarranty', $vehicle, $settings);
            $this->deadlinesSendVerify($now, 'mechanic', 'getDateMechanic', $vehicle, $settings);
        }
        
        return 0;
    }

    public function deadlinesSendVerify($now, $notify, $get, $vehicle, $settings) 
    {
        if($settings['notify'][$notify] == 'on') {   // sprawdzenie czy ma wysyłać
            if(!empty($vehicle->$get())) {
                $date_email = new Carbon(date_format($vehicle->$get(), 'Y-m-d'));
                $date_sms = new Carbon(date_format($vehicle->$get(), 'Y-m-d'));

                $this->deadlinesSendTime($get, $settings['notify']['email_one'], $settings['notify']['sms_one'], $date_email, $date_sms, $settings, $vehicle, $notify);
                $this->deadlinesSendTime($get, $settings['notify']['email_two'], $settings['notify']['sms_two'], $date_email, $date_sms, $settings, $vehicle, $notify);
                $this->deadlinesSendTime($get, $settings['notify']['email_three'], $settings['notify']['sms_three'], $date_email, $date_sms, $settings, $vehicle, $notify);
            }
        }
    }

    public function deadlinesSendTime($get, $email, $sms, $date_email, $date_sms, $settings, $vehicle, $notify)
    {
        $now = Carbon::now();
        $apiSettings = Yaml::parseFile('config/api.yaml'); // pobranie danych do api

        $date_email->subDays($email);
        $date_sms->subDays($sms);

        if($email > 0) {


            if($date_email->year == $now->year && $date_email->month == $now->month && $date_email->day == $now->day) {

                // rozpoczecie wysyłania do api wiadomości e-mail
                $client = HttpClient::create();
                $response = $client->request('GET', $apiSettings['api']['path']['mail_send_deadlines'], [
                    'query' => [
                        'id' => $apiSettings['api']['id_user'],
                        'token' => $apiSettings['api']['token'],
                        'email' => $settings['data']['email'],
                        'vehicle_name' => $vehicle->getName(),
                        'vehicle_register' => $vehicle->getRegistration(),
                        'notify' => $notify,
                        'date' => date_format($vehicle->$get(), 'd-m-Y'),
                        'remained' => $email, 
                    ]
                ]);

                $user = $this->em->getRepository(User::class)->find(1);
                $statusCode = $response->getStatusCode();

                $note = new UserNotify();
                $note->setUser($user);
                $note->setDate(new \DateTime());
                $note->setIp('---');
            
                if($statusCode == 200) {
                    $content = $response->getContent(); 

                    if($content == true) {
                        $note->setText('Wysłano powiadomienie o "'.$vehicle->getName().'" na e-maila.');
                    } else {
                        $note->setText('Wystąpił błąd wysyłania powiadomienia e-mail.');
                    }
                } else {
                    $note->setText('Wystąpił błąd połączenia: '.$statusCode.' podczas wysyłania powiadomienia e-mail.');
                }

                $this->em->persist($note);
                $this->em->flush();
                // koniec wysyłania
            }
        }

        if($sms > 0) {
            if($date_sms->year == $now->year && $date_sms->month == $now->month && $date_sms->day == $now->day) {
                
                // rozpoczecie wysyłania do api wiadomości sms
                $client = HttpClient::create();
                $response = $client->request('GET', $apiSettings['api']['path']['sms_send_deadlines'], [
                    'query' => [
                        'id' => $apiSettings['api']['id_user'],
                        'token' => $apiSettings['api']['token'],
                        'phone' => $settings['data']['phone'],
                        'vehicle_name' => $vehicle->getName(),
                        'vehicle_register' => $vehicle->getRegistration(),
                        'notify' => $notify,
                        'date' => date_format($vehicle->$get(), 'd-m-Y'),
                        'remained' => $sms, 
                    ]
                ]);

                $user = $this->em->getRepository(User::class)->find(1);
                $statusCode = $response->getStatusCode();

                $note = new UserNotify();
                $note->setUser($user);
                $note->setDate(new \DateTime());
                $note->setIp('---');
            
                if($statusCode == 200) {
                    $content = $response->getContent(); 

                    if($content == true) {
                        $note->setText('Wysłano powiadomienie o "'.$vehicle->getName().'" na sms.');
                    } else {
                        $note->setText('Wystąpił błąd wysyłania powiadomienia sms.');
                    }
                } else {
                    $note->setText('Wystąpił błąd połączenia: '.$statusCode.' podczas wysyłania powiadomienia sms.');
                }

                $this->em->persist($note);
                $this->em->flush();
                // koniec wysyłania
            }
        }

        $date_email->addDays($email);
        $date_sms->addDays($sms);
    }
}
