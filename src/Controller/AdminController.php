<?php

namespace App\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\EventDispatcher\GenericEvent;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\DomCrawler\Crawler;
use Goutte\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpClient\HttpClient;
use Sonata\Exporter\Handler;
use Sonata\Exporter\Source\ArraySourceIterator;
use Sonata\Exporter\Writer\CsvWriter;
use Sonata\Exporter\Writer\JsonWriter;
use Carbon\Carbon;
use Dompdf\Dompdf;

// klasy
use App\Entity\User;
use App\Entity\UserNotify;
use App\Entity\Vehicles;
use App\Entity\Records;
use App\Entity\Route as Routes;
use App\Entity\RecordsCourse;
use App\Entity\Costs;
use App\Entity\RecordsCosts;

class AdminController extends EasyAdminController
{

    // dodanie notice
    public function addNote($text, $request) {
        $entityManager = $this->getDoctrine()->getManager();
        $ip = $request->getClientIp();

        $note = new UserNotify();
        $note->setUser($this->getUser());
        $note->setDate(new \DateTime());
        $note->setIp($ip);
        $note->setText($text);
        $entityManager->persist($note);
        $entityManager->flush();
    }

    /**
     * @Route("/admin", name="easyadmin")
     */
    public function indexAction(Request $request)
    {
        $settings = Yaml::parseFile('../config/settings.yaml');
        $request->pack = $settings['pack'];

        return parent::indexAction($request);
    }

    /**
     * @Route("/admin/data", name="data_company")
     */
    public function indexData(Request $request)
    {
    	$settings = Yaml::parseFile('../config/settings.yaml');

        return $this->render('admin/settings.html.twig', ['data' => $settings['data']]);
    }

    /**
     * @Route("/admin/settings/save", name="settingsSave", methods="POST")
     */
    public function dataSave(Request $request)
    {
    	$settings = Yaml::parseFile('../config/settings.yaml');
    	$form = $request->request->get('settings');
        $ip = $request->getClientIp();
        $entityManager = $this->getDoctrine()->getManager();

		$array = [
			'pack' => $settings['pack'],
		    'data' => ['name' => $form['name'], 'street' => $form['street'], 'code' => $form['code'], 'city' => $form['city'], 'nip' => $form['nip'], 'regon' => $form['regon'], 'email' => $form['email'], 'phone' => $form['phone']],
		    'notify' => $settings['notify'],
		];

		$yaml = Yaml::dump($array);

		file_put_contents('../config/settings.yaml', $yaml);

        $this->addNote('Wprowadzono zmiany w ustawieniach danych firmy.', $request);
        $this->addFlash('success', 'Poprawnie zapisano nowe dane.');
        return $this->redirectToRoute('data_company');
    }

    /**
     * @Route("/admin/notifications", name="notifications")
     */
    public function indexNote(Request $request)
    {
    	$settings = Yaml::parseFile('../config/settings.yaml');

        return $this->render('admin/notifications.html.twig', ['notify' => $settings['notify']]);
    }

    /**
     * @Route("/admin/notifications/save", name="notifySave")
     */
    public function noteSave(Request $request)
    {
    	$settings = Yaml::parseFile('../config/settings.yaml');
    	$form = $request->request->get('notify');
        $ip = $request->getClientIp();
        $entityManager = $this->getDoctrine()->getManager();

    	if(isset($form['overview'])) {
    		$overview = $form['overview'];
    	} else {
    		$overview = "off";
    	}

    	if(isset($form['oil'])) {
    		$oil = $form['oil'];
    	} else {
    		$oil = "off";
    	}

    	if(isset($form['udt'])) {
    		$udt = $form['udt'];
    	} else {
    		$udt = "off";
    	}

    	if(isset($form['documents'])) {
    		$documents = $form['documents'];
    	} else {
    		$documents = "off";
    	}

    	if(isset($form['oc'])) {
    		$oc = $form['oc'];
    	} else {
    		$oc = "off";
    	}

    	if(isset($form['warranty'])) {
    		$warranty = $form['warranty'];
    	} else {
    		$warranty = "off";
    	}

    	if(isset($form['mechanic'])) {
    		$mechanic = $form['mechanic'];
    	} else {
    		$mechanic = "off";
    	}


		$array = [
			'pack' => $settings['pack'],
			'data' => $settings['data'],
		    'notify' => ['email_one' => $form['email_one'], 'email_two' => $form['email_two'], 'email_three' => $form['email_three'], 'sms_one' => $form['sms_one'], 'sms_two' => $form['sms_two'], 'sms_three' => $form['sms_three'], 'overview' => $overview, 'oil' => $oil, 'udt' => $udt, 'documents' => $documents, 'oc' => $oc, 'warranty' => $warranty, 'mechanic' => $mechanic],
		];

		$yaml = Yaml::dump($array);

		file_put_contents('../config/settings.yaml', $yaml);

        $this->addNote('Wprowadzono zmiany w ustawieniach powiadomień.', $request);
        $this->addFlash('success', 'Poprawnie zapisano nowe dane.');
        return $this->redirectToRoute('notifications');
    }

    // Edycja użytkownika render debilu :)
    protected function editUserAction()
    {
        $id = $this->request->query->get('id');

        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        $notify = $entityManager->getRepository(UserNotify::class)->findBy(array('user' => $id), array('date' => 'DESC'), 30, 0);

        return $this->render('admin/users_edit_form.html.twig', ['entity' => $user, 'notify' => $notify]);
    }

    // Edycja ewidencji render debilu :)
    protected function showRecordsAction()
    {
        $id = $this->request->query->get('id');
        $settings = Yaml::parseFile('../config/settings.yaml');

        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Records::class)->find($id);
        $route = $entityManager->getRepository(Routes::class)->findAll();
        $base_costs = $entityManager->getRepository(Costs::class)->findAll();

        return $this->render('admin/records_show_form.html.twig', ['entity' => $record, 'company' => $settings['data'], 'route' => $route, 'base_costs' => $base_costs]);
    }

    // dodawanie i sprawdzanie rekordów
    protected function persistRecordsEntity($entity)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Records::class)->findBy(array('month' => $entity->getMonth(), 'year' => $entity->getYear(), 'vehicle' => $entity->getVehicle(), 'driver' => $entity->getDriver()));

        if(count($record) > 0) {
            $this->addFlash('danger', 'Istnieje już taka ewidencja. Nie możesz posiadać dwóch takich samych ewidencji.');
            return $this->redirectToRoute('easyadmin', array(
                'action' => 'new',
                'entity' => 'Records',
            ));
        } else {

            $vehicle = $entityManager->getRepository(Vehicles::class)->find($entity->getVehicle());
            if($vehicle->getGoalVat() == 1) {
                $entity->setVat($vehicle->getGoalVat());
                $entity->setVatDateStart($vehicle->getDateCommencementRecords());
                $entity->setVatCounterStart($vehicle->getStateCounter());
            }

            parent::persistEntity($entity);
        }
    }

    /**
     * @Route("/admin/records/update/vat/{id}", name="update_records_vat")
     */
    public function updateRecordsVat($id, Request $request) {
        $form = $request->get('vat_form');

        $date_start = new \DateTime($form['date_start']);
        $date_end = new \DateTime($form['date_end']);

        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Records::class)->find($id);
        $record->setVatDateStart($date_start);
        $record->setVatDateEnd($date_end);
        $record->setVatCounterStart($form['counter_start']);
        $record->setVatCounterEnd($form['counter_end']);
        $record->setVatPeriodStart($form['period_start']);
        $record->setVatPeriodEnd($form['period_end']);
        $record->setVatKmEnd($form['km_end']);
        $entityManager->flush();

        $this->addNote('Edytowano formularz VAT w arkuszu '.$record->getMonth().'/'.$record->getYear().'.', $request);
        return new Response($request);
    }

    /**
     * @Route("/admin/note/save", name="save_note")
     */
    public function noteCourseSave(Request $request) {
        $id = $request->get('id');
        $text = $request->get('text');

        $entityManager = $this->getDoctrine()->getManager();
        $recordCourse = $entityManager->getRepository(RecordsCourse::class)->find($id);
        $recordCourse->setComments($text);
        $entityManager->flush();

        $record = $recordCourse->getRecord();
        $this->addNote('Zmieniono treść uwagi w rekordzie '.$recordCourse->getID().' w arkuszu '.$record->getMonth().'/'.$record->getYear().'.', $request);

        return new Response($request);
    }

    /**
     * @Route("/admin/records/course/save", name="add_records_course", methods={"POST"})
     */
    public function addRecordsCourse(Request $request) {
        $form = $request->get('course');

        if(empty($form['date'])) {
            return new JsonResponse(['danger' => 'true']);
        } elseif (empty($form['description'])) {
            return new JsonResponse(['danger' => 'true']);
        } elseif (empty($form['trip'])) {
            return new JsonResponse(['danger' => 'true']);
        } elseif (empty($form['km'])) {
            return new JsonResponse(['danger' => 'true']);
        } elseif(empty($form['counter'])) {
            return new JsonResponse(['danger' => 'true']);
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $record = $entityManager->getRepository(Records::class)->find($form['id']);

            $this->ifCoutingCourse($record, $form['counter'], $entityManager);

            $lp = $entityManager->getRepository(RecordsCourse::class)->findBy(array('record' => $record));
            $lp = count($lp) + 1;

            $course = $this->addCourse($form, $record);

            $this->addNote('Utworzono nowy rekord w arkuszu '.$record->getMonth().'/'.$record->getYear().'.', $request);
            return $this->render('admin/table/records_course.html.twig', ['course' => $course, 'lp' => $lp]);
        }
    }

    /**
     * @Route("/admin/records/course/save/many/{id}", name="add_records_course_many")
     */
    public function addRecordsCourseMany($id, Request $request) {
        $form = $request->get('course_many');

        if(empty($form['route'])) {
            $this->addFlash('danger', 'Brak wybranej trasy.');
        } elseif(empty($form['date'])) {
            $this->addFlash('danger', 'Brak wybranej daty/dat.');
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $record = $entityManager->getRepository(Records::class)->find($id);
            $route = $entityManager->getRepository(Routes::class)->find($form['route']);

            $type = $record->getVehicle()->getType();
            if($type == 1) {
                $rate = "0.8358";
            } elseif($type == 2) {
                $rate = "0.5214";
            } elseif($type == 3) {
                $rate = "0.8358";
            } elseif($type == 5) {
                $rate = "0.2320";
            } elseif($type == 6) {
                $rate = "0.1382";
            } else {
                $rate = "0";
            }

            $date = explode(",", $form['date']);

            $courses = array();
            $km_many = $record->getVehicle()->getCourse();
            foreach($date as $tab) {
                $value = $route->getKm() * $rate;

                if($record->getVehicle()->getCountingCourse() == 1) {
                    $km_many = $km_many + $route->getKm();
                } else {
                    $km_many = 0;
                }

                $data = array('date' => $tab, 'description' => $route->getDescription(), 'trip' => $route->getName(), 'km' => $route->getKm(), 'rate' => $rate, 'value' => $value, 'counter' => $km_many, 'comments' => '');

                $courses[] = $this->addCourse($data, $record);
            }

            $this->ifCoutingCourse($record, $km_many, $entityManager);

            if(count($courses) > 0) {
                $this->addFlash('success', 'Prawidłowo powielono trasę.');
                $this->addNote('Utworzono wiele nowych tras w arkuszu '.$record->getMonth().'/'.$record->getYear().'.', $request);
            }
        }

        return $this->redirectToRoute('easyadmin', array(
            'action' => 'show',
            'id' => $id,
            'entity' => 'Records',
        ));
    }

    // dodawanie trasy do arkusza
    public function addCourse($form, $record) {
        $date = new \DateTime($form['date']);

        $course = new RecordsCourse();
        $course->setDate($date);
        $course->setRouteDescription($form['description']);
        $course->setGoalTrip($form['trip']);
        $course->setKm($form['km']);
        $course->setRate($form['rate']);
        $course->setValue($form['value']);
        $course->setCounter($form['counter']);
        $course->setComments($form['comments']);
        $course->setRecord($record);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($course);
        $entityManager->flush();

        return $course;
    }

    // jeśli ma to dolicza przebieg do pojazdu
    public function ifCoutingCourse($record, $counter, $entityManager) {
        if($record->getVehicle()->getCountingCourse() == 1) {
            $vehicle = $entityManager->getRepository(Vehicles::class)->find($record->getVehicle());
            $vehicle->setCourse($counter);
            $entityManager->flush();
        }
    }

    /**
     * @Route("/admin/records/course/edit", name="edit_records_course")
     */
    public function editRecordsCourse(Request $request) {
        $form = $request->request->get('e_course');

        $date = new \DateTime($form['date']);

        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Records::class)->find($form['id_record']);
        $course = $entityManager->getRepository(RecordsCourse::class)->find($form['id']);
        $course->setDate($date);
        $course->setRouteDescription($form['description']);
        $course->setGoalTrip($form['trip']);
        $course->setKm($form['km']);
        $course->setRate($form['rate']);
        $course->setValue($form['value']);
        $course->setCounter($form['counter']);
        $entityManager->flush();

        $this->addNote('Edytowano rekord w arkuszu '.$record->getMonth().'/'.$record->getYear().'.', $request);
        return new JsonResponse($form);
    }

    /**
     * @Route("/admin/records/delete/course/{id}", name="delete_records_course")
     */
    public function deleteRecordsCourse($id, Request $request) {
        $entityManager = $this->getDoctrine()->getManager();
        $course = $entityManager->getRepository(RecordsCourse::class)->find($id);
        $entityManager->remove($course);
        $entityManager->flush();

        $this->addNote('Usunięto rekord w arkuszu '.$course->getRecord()->getMonth().'/'.$course->getRecord()->getYear().'.', $request);
        $this->addFlash('success', 'Usunięto rekord prawidłowo.');
        return $this->redirectToRoute('easyadmin', array(
            'action' => 'show',
            'id' => $course->getRecord()->getID(),
            'entity' => 'Records',
        ));
    }

    /**
     * @Route("/admin/records/download/course/{id}/{comments}/{render}", name="records_download_course")
     */
    public function recordsDownloadCourse($id, $comments, $render, Request $request) {
        $settings = Yaml::parseFile('../config/settings.yaml');
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Records::class)->find($id);

        $date = Carbon::now();

        if($render == 1) {
            $name = "ewidencja_przebiegu";
            $tmp = "admin/pdf/pdf_records_course.html.twig";
        } else {
            $name = "ewidencja_przebiegu_vat";
            $tmp = "admin/pdf/pdf_records_course_vat.html.twig";
        }

        $path = $name."_".$record->getMonth()."_".$record->getYear()."_".$date->toDateString()."_".$date->second.".pdf";
        $pdf = $this->render($tmp, ['entity' => $record, 'company' => $settings['data'], 'comments' => $comments]);
  
        // to pdf
        $dompdf = new Dompdf('UTF-8');
        $dompdf->loadHtml($pdf->getContent());
        $dompdf->setPaper('A4');
        $dompdf->render();
        $dompdf->stream($path);
    }

    /**
     * @Route("/admin/records/cost/save", name="add_records_cost", methods={"POST"})
     */
    public function addRecordsCost(Request $request) {
        $form = $request->get('cost');

        if(empty($form['date'])) {
            return new JsonResponse(['danger' => 'true']);
        } elseif (empty($form['description'])) {
            return new JsonResponse(['danger' => 'true']);
        } elseif (empty($form['document'])) {
            return new JsonResponse(['danger' => 'true']);
        } elseif (empty($form['value'])) {
            return new JsonResponse(['danger' => 'true']);
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $record = $entityManager->getRepository(Records::class)->find($form['id']);
            $lp = $entityManager->getRepository(RecordsCosts::class)->findBy(array('record' => $record));
            $lp = count($lp) + 1;

            $date = new \DateTime($form['date']);

            $cost = new RecordsCosts();
            $cost->setDate($date);
            $cost->setDescription($form['description']);
            $cost->setDocument($form['document']);
            $cost->setValue($form['value']);
            $cost->setRecord($record);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cost);
            $entityManager->flush();

            $this->addNote('Utworzono nowy koszt w arkuszu '.$record->getMonth().'/'.$record->getYear().'.', $request);
            return $this->render('admin/table/records_costs.html.twig', ['cost' => $cost, 'lp' => $lp]);
        }
    }

    /**
     * @Route("/admin/records/cost/edit", name="edit_records_cost")
     */
    public function editRecordsCost(Request $request) {
        $form = $request->request->get('e_cost');

        $date = new \DateTime($form['date']);

        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Records::class)->find($form['id_record']);
        $course = $entityManager->getRepository(RecordsCosts::class)->find($form['id']);
        $course->setDate($date);
        $course->setDescription($form['description']);
        $course->setDocument($form['document']);
        $course->setValue($form['value']);
        $entityManager->flush();

        $this->addNote('Edytowano koszt w arkuszu '.$record->getMonth().'/'.$record->getYear().'.', $request);
        return new JsonResponse($form);
    }

    /**
     * @Route("/admin/records/delete/cost/{id}", name="delete_records_cost")
     */
    public function deleteRecordsCost($id, Request $request) {
        $entityManager = $this->getDoctrine()->getManager();
        $cost = $entityManager->getRepository(RecordsCosts::class)->find($id);
        $entityManager->remove($cost);
        $entityManager->flush();

        $this->addNote('Usunięto koszt z arkusza '.$cost->getRecord()->getMonth().'/'.$cost->getRecord()->getYear().'.', $request);
        $this->addFlash('success', 'Usunięto koszt prawidłowo.');
        return $this->redirectToRoute('easyadmin', array(
            'action' => 'show',
            'id' => $cost->getRecord()->getID(),
            'entity' => 'Records',
        ));
    }

    /**
     * @Route("/admin/records/taily/load/{id}", name="load_taily")
     */
    public function loadRecordsTaily($id, Request $request) {
        $data = $this->queryTaily($id);

        return $this->render('admin/table/records_taily.html.twig', ['table' => $data['table'], 'count' => $data['count']]);
    }

    // pobranie danych zestawienia do tabelki
    public function queryTaily($id) {
        $em = $this->getDoctrine()->getManager();
        $record = $em->getRepository(Records::class)->find($id);

        $RAW_QUERY = "SELECT *, 
                (SELECT SUM(rc.value) FROM records_course AS rc WHERE rc.record_id = r.id) AS sum_course,
                (SELECT SUM(rs.value) FROM records_costs AS rs WHERE rs.record_id = r.id) AS sum_cost 
            FROM records AS r WHERE r.month <= ".$record->getMonth()." AND r.year = ".$record->getYear()." GROUP BY r.id";
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $statement = $statement->fetchAll();
        $count = count($statement);

        return array('record' => $record, 'table' => $statement, 'count' => $count);
    }

    /**
     * @Route("/admin/records/download/costs/{id}", name="records_download_costs")
     */
    public function recordsDownloadCosts($id, Request $request) {
        $settings = Yaml::parseFile('../config/settings.yaml');
        $entityManager = $this->getDoctrine()->getManager();
        $record = $entityManager->getRepository(Records::class)->find($id);

        $date = Carbon::now();
        $name = "koszty_eksploatacyjne";
        $path = $name."_".$record->getMonth()."_".$record->getYear()."_".$date->toDateString()."_".$date->second.".pdf";

        $pdf = $this->render('admin/pdf/pdf_records_costs.html.twig', ['entity' => $record, 'company' => $settings['data']]);
  
        // to pdf
        $dompdf = new Dompdf('UTF-8');
        $dompdf->loadHtml($pdf->getContent());
        $dompdf->setPaper('A4');
        $dompdf->render();
        $dompdf->stream($path);
    }

    /**
     * @Route("/admin/records/download/taily/{id}", name="records_download_taily")
     */
    public function recordsDownloadTaily($id, Request $request) {
        $settings = Yaml::parseFile('../config/settings.yaml');
        $data = $this->queryTaily($id);
        $record = $data['record'];

        $date = Carbon::now();
        $name = "zestawienie";
        $path = $name."_".$record->getMonth()."_".$record->getYear()."_".$date->toDateString()."_".$date->second.".pdf";

        $pdf = $this->render('admin/pdf/pdf_records_taily.html.twig', ['entity' => $data['record'], 'table' => $data['table'], 'count' => $data['count'], 'company' => $settings['data']]);
  
        // to pdf
        $dompdf = new Dompdf('UTF-8');
        $dompdf->loadHtml($pdf->getContent());
        $dompdf->setPaper('A4');
        $dompdf->render();
        $dompdf->stream($path);
    }

    /**
     * @Route("/admin/download/{what}/{class}", name="download")
     */
    public function download($what, $class, Request $request) {
        $date = Carbon::now();

        switch($class) {
            case 'Records':
                $RAW_QUERY = 'SELECT id, month, year FROM records';
                $name = "ewidencja";
            break;
            case 'Costs':
                $RAW_QUERY = 'SELECT id, name, description, document, value FROM costs';
                $name = "koszty";
            break;
            case 'Drivers':
                $RAW_QUERY = 'SELECT id, firstname, name, city, code_post, street, phone, email, description FROM drivers';
                $name = "kierowcy";
            break;
            case 'Route':
                $RAW_QUERY = 'SELECT id, name, description, km FROM route';
                $name = "trasy";
            break;
            case 'User':
                $RAW_QUERY = 'SELECT id, username, email, phone FROM user';
                $name = "konta";
            break;
            case 'Vehicles':
                $RAW_QUERY = 'SELECT id, name, registration, vin, first_registration, description FROM vehicles';
                $name = "pojazdy";
            break;
            case 'RecordsCourse':
                $RAW_QUERY = 'SELECT id, date, route_description, goal_trip, km, rate, value, counter, comments FROM records_course';
                $name = "ewidencja_przebiegu";
            break;
            case 'RecordsCosts':
                $RAW_QUERY = 'SELECT id, date, description, document, value FROM records_costs';
                $name = "koszty_eksploatacyjne";
            break;
        }
        
        $em = $this->getDoctrine()->getManager();
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $statement = $statement->fetchAll();

        switch($what) {
            case 'csv':
                $path = "uploads/csv/".$name."_".$date->toDateString()."_".$date->hour."_".$date->minute."_".$date->second.".csv";
                $source = new ArraySourceIterator($statement);
                $writer = new CsvWriter($path);
                $file = Handler::create($source, $writer)->export();

                return new Response($path);
            break;
            case 'pdf':
                $path = "uploads/pdf/".$name."_".$date->toDateString()."_".$date->hour."_".$date->minute."_".$date->second.".pdf";

                $headers = [];
                if(!empty($statement)) {
                    foreach($statement[0] as $header => $value) {
                        $headers[] = $header;
                    }
                }
                
                $pdf = $this->render('admin/pdf/pdf_base_standard.html.twig', ['title' => $name, 'headers' => $headers, 'data' => $statement]);
  
                // to pdf
                $dompdf = new Dompdf('UTF-8');
                $dompdf->loadHtml($pdf->getContent());
                $dompdf->setPaper('A4');
                $dompdf->render();
                $dompdf->stream($path);
            break;
        }

        
    }   

}
