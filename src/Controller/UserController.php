<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpClient\HttpClient;

// klasy
use App\Entity\User;
use App\Entity\UserNotify;


class UserController extends AbstractController
{

    /**
     * @Route("/admin/dashboard", name="dashboard")
     */
    public function indexDashboard(Request $request, UserInterface $user)
    {
		$settings = Yaml::parseFile('../config/settings.yaml');

		$entityManager = $this->getDoctrine()->getManager();
        $user_notify = $entityManager->getRepository(UserNotify::class)->findBy(array(), array('date' => 'DESC'), 30, 0);

        return $this->render('admin/dashboard.html.twig', ['pack' => $settings['pack'], 'notify' => $user_notify]);
    }

    /**
     * @Route("/admin/dashboard/password", name="passwordSave", methods="POST")
     */
    public function profilSave(Request $request, UserInterface $user, ValidatorInterface $validator, UserPasswordEncoderInterface $passwordEncoder)
    {

        $entityManager = $this->getDoctrine()->getManager();
        $base_user = $entityManager->getRepository(User::class)->find($user->getId());

        if (!$base_user) {
            $this->addFlash('danger', 'Wystąpił błąd z połączeniem do bazy danych.');
            return $this->redirectToRoute('dashboard');
        } else {

            $password_test = $passwordEncoder->isPasswordValid($user, $request->request->get('password_old'));

            $password = $user->getPassword();
            if($password_test == true) {
                if($request->request->get('password_old') == $request->request->get('password_new')) {
                    $this->addFlash('danger', 'Nowe hasło jest takie samo jak stare hasło.');
        			return $this->redirectToRoute('dashboard');
                } else {
                    $password = $passwordEncoder->encodePassword($user, $request->request->get('password_new'));
                }
            } else {
                $this->addFlash('danger', 'Nowe i stare hasło jest takie samo.');
        		return $this->redirectToRoute('dashboard');
            }

            $base_user->setPassword($password);

            $errors = $validator->validate($base_user);

            if (count($errors) > 0) {
                $msg = (string) $errors;
                $this->addFlash('danger', $msg);
            } else {
                $entityManager->flush();

                $ip = $request->getClientIp();
                $note = new UserNotify();
                $note->setUser($this->getUser());
                $note->setDate(new \DateTime());
                $note->setIp($ip);
                $note->setText('Hasło do tego konta zostało zmienione.');
                $entityManager->persist($note);
                $entityManager->flush();

                $this->addFlash('success', 'Poprawnie zapisano nowe dane.');
            }
        }

        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/admin/help/load", name="load_help")
     */
    public function loadHelp(Request $request) {
        // pobieranie wiadomości pomocy z api
        $apiSettings = Yaml::parseFile('../config/api.yaml');
        $client = HttpClient::create();
        $response = $client->request('GET', $apiSettings['api']['path']['help_load'], [
            'query' => [
                'id' => $apiSettings['api']['id_user'],
                'token' => $apiSettings['api']['token'],
            ]
        ]);

        $statusCode = $response->getStatusCode();
        if($statusCode == 200) {
            $content = $response->getContent(); 
            $content = json_decode($content);

            if(!empty($content)) {
                $print = "";
                foreach($content as $tab) {
                    $xyz = $this->render('admin/help/help_div.html.twig', ['text' => $tab->text, 'what' => $tab->what, 'date' => $tab->date->date]);
                    $print.= $xyz->getContent();
                }

                return new Response($print);
            } else {
                return new JsonResponse(false);
            }
        } else {
            return new JsonResponse(false);
        }
    }

    /**
     * @Route("/admin/help/send", name="send_help")
     */
    public function sendHelp(Request $request) {
        $text = $request->get('text');
        $date = new \DateTime();

        // rozpoczecie wysyłania do api wiadomości pomocy
        $apiSettings = Yaml::parseFile('../config/api.yaml');
        $client = HttpClient::create();
        $response = $client->request('GET', $apiSettings['api']['path']['help_send'], [
            'query' => [
                'id' => $apiSettings['api']['id_user'],
                'token' => $apiSettings['api']['token'],
                'text' => $text,
                'date' => $date,
            ]
        ]);

        $statusCode = $response->getStatusCode();
        if($statusCode == 200) {
            return $this->render('admin/help/help_div.html.twig', ['text' => $text, 'what' => '1', 'date' => $date]);
        } else {
            return new JsonResponse(false);
        }
    }

}
