<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Yaml\Yaml;

// klasy
use App\Entity\User;
use App\Entity\UserNotify;

// formularze
use App\Form\ForgotPasswordType;


class SecurityController extends AbstractController 
{
    /**
     * @Route("/", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('start/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout()
    {
        // controller can be blank: it will never be executed!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    /**
     * @Route("/admin/account/new", name="newAccount")
     */
    public function newAccount(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $form = $request->request->get('account');

        $user = new User();
        $user->setUsername($form['username']);
        $user->setEmail($form['email']);
        $user->setActive(0);
        $user->setPhone($form['phone']);

        $entityManager = $this->getDoctrine()->getManager();
        $base_user = $entityManager->getRepository(User::class)->findOneBy(['email' => $form['email']]);

        if ($base_user) {
            $this->addFlash('danger', 'Już istenieje użytkownik o takim adresie e-mail.');
            return $this->redirect('/admin/?entity=User&action=list&menuIndex=7&submenuIndex=0');
        } else {
            if($form['password'] == $form['password_return']) {

                $password = $passwordEncoder->encodePassword($user, $form['password']);
                $user->setPassword($password);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $ip = $request->getClientIp();
                $note = new UserNotify();
                $note->setUser($this->getUser());
                $note->setDate(new \DateTime());
                $note->setIp($ip);
                $note->setText('Utworzono nowe konto "'.$user->getUsername().'".');
                $entityManager->persist($note);
                $entityManager->flush();

                $this->addFlash('success', 'Poprawnie dodano nowe konto.');
                return $this->redirect('/admin/?entity=User&action=list&menuIndex=7&submenuIndex=0');
            } else {
                $this->addFlash('danger', 'Niestety, ale hasła nie są takie same.');
                return $this->redirect('/admin/?entity=User&action=list&menuIndex=7&submenuIndex=0');           
            }
        }

        $this->addFlash('danger', 'Niestety, ale coś poszło nie tak.');
        return $this->redirect('/admin/?entity=User&action=list&menuIndex=7&submenuIndex=0');
    }

    /**
     * @Route("/admin/account/edit/{id}", name="editAccount")
     */
    public function editAccount($id, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $form = $request->request->get('account');

        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            $this->addFlash('danger', 'Nie istenieje taki użytkownik.');
            return $this->redirect('/admin/?entity=User&action=list&menuIndex=7&submenuIndex=0');
        }

        $user->setUsername($form['username']);
        $user->setPhone($form['phone']);

        if(!empty($form['password'])) {

            $password_test = $passwordEncoder->isPasswordValid($user, $form['password']);

            if($password_test == true) {

                $new_password = $passwordEncoder->encodePassword($user, $form['password_new']);
                $user->setPassword($new_password);
            } else {
                $this->addFlash('danger', 'Niestety, ale stare hasło nie jest takie samo.');
                return $this->redirect('/admin/?entity=User&action=list&menuIndex=7&submenuIndex=0');           
            }
        }

        $ip = $request->getClientIp();
        $note = new UserNotify();
        $note->setUser($this->getUser());
        $note->setDate(new \DateTime());
        $note->setIp($ip);
        $note->setText('Wprowadzono zmiany w koncie "'.$user->getUsername().'".');
        $entityManager->persist($note);
        $entityManager->flush();

        $this->addFlash('success', 'Poprawnie zmieniono dane.');
        return $this->redirect('/admin/?entity=User&action=list&menuIndex=7&submenuIndex=0');
    }

    /**
     * @Route("/forgot/password", name="forgot_password")
     */
    public function forgotAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(ForgotPasswordType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $base_user = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
    
            $new_password = $user->newPassword(); // wygenerowanie nowego hasła

            $newEncodedPassword = $passwordEncoder->encodePassword($base_user, $new_password);
            $base_user->setPassword($newEncodedPassword);
                
            $entityManager->persist($base_user);
            $entityManager->flush();

            // rozpoczecie wysyłania do api, a poten na e-maila hasła
            $apiSettings = Yaml::parseFile('../config/api.yaml');

            $client = HttpClient::create();
            $response = $client->request('GET', $apiSettings['api']['path']['mail_send'], [
                'query' => [
                    'id' => $apiSettings['api']['id_user'],
                    'token' => $apiSettings['api']['token'],
                    'email' => $base_user->getEmail(),
                    'password' => $new_password,
                ]
            ]);

            $statusCode = $response->getStatusCode();
            
            if($statusCode == 200) {
                $content = $response->getContent(); 

                if($content == true) {

                    $ip = $request->getClientIp();
                    $note = new UserNotify();
                    $note->setUser($base_user);
                    $note->setDate(new \DateTime());
                    $note->setIp($ip);
                    $note->setText('Wygenerowano nowe hasło dla tego konta przesłane na e-mail.');
                    $entityManager->persist($note);
                    $entityManager->flush();

                    $this->addFlash('success', 'Nowe hasło wysłano na e-maila.');
                    return $this->redirectToRoute('app_login');
                } else {
                    $this->addFlash('danger', 'Wystąpił błąd wysłania e-maila.');
                    return $this->redirectToRoute('forgot_password');
                }
            } else {
                $this->addFlash('danger', 'Wystąpił błąd połączenia: '.$statusCode.'.');
                return $this->redirectToRoute('forgot_password');
            }
            // koniec wysyłania hasła
        }
        
        return $this->render('start/forgot.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
