<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use App\Entity\UserNotify;


class EasyAdminSubscriber implements EventSubscriberInterface
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage) {
        $this->token = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            'easy_admin.post_update' => 'onEasyAdminPostUpdate',
            'easy_admin.post_persist' => 'onEasyAdminPostCreate',
            'easy_admin.post_remove' => 'onEasyAdminPostRemove',
        ];
    }

    // dodawanie powiadomień po edycji
    public function onEasyAdminPostUpdate(GenericEvent $event)
    {
        $arg = $event->getArguments();
        $subject = $event->getSubject();
        $token = $this->token->getToken();
        $ip = $arg['request']->getClientIp();
        $entity = $arg['request']->query->get('entity');

        switch($entity) {
            case 'Vehicles': $ent = "pojeździe"; $sub = $subject->getName(); break;
            case 'Drivers': $ent = "kierowcy"; $sub = $subject->getFullName(); break;
            case 'Costs': $ent = "koszcie"; $sub = $subject->getName(); break;
            case 'Records': $ent = "ewidencji"; $sub = $subject->getName(); break;
            case 'Route': $ent = "trasie"; $sub = $subject->getName(); break;
            case 'User': $ent = "koncie"; $sub = $subject->getUsername(); break;
            case 'Deadlines': $ent = "pojeździe"; $sub = $subject->getName(); break;
            default: $ent = ""; $sub = ""; break;
        }

        $text = 'Wprowadzono zmiany w '.$ent.' "'.$sub.'".';
        $this->sendAction($token, $ip, $text, $arg);
    }

    // dodawanie powiadomień po dodaniu
    public function onEasyAdminPostCreate(GenericEvent $event)
    {
        $arg = $event->getArguments();
        $subject = $event->getSubject();
        $token = $this->token->getToken();
        $ip = $arg['request']->getClientIp();
        $entity = $arg['request']->query->get('entity');

        switch($entity) {
            case 'Vehicles': $ent = "pojazd"; $sub = $subject->getName(); break;
            case 'Drivers': $ent = "kierowce"; $sub = $subject->getFullName(); break;
            case 'Costs': $ent = "koszt"; $sub = $subject->getName(); break;
            case 'Records': $ent = "ewidencje"; $sub = $subject->getName(); break;
            case 'Route': $ent = "trase"; $sub = $subject->getName(); break;
            case 'User': $ent = "konto"; $sub = $subject->getUsername(); break;
            case 'Deadlines': $ent = "pojazd"; $sub = $subject->getName(); break;
            default: $ent = ""; $sub = ""; break;
        }
  
        $text = 'Utworzono '.$ent.' "'.$sub.'".';
        $this->sendAction($token, $ip, $text, $arg);
    }

    // dodawanie powiadomień po usunięciu
    public function onEasyAdminPostRemove(GenericEvent $event)
    {
        $arg = $event->getArguments();
        $subject = $event->getSubject();
        $token = $this->token->getToken();
        $ip = $arg['request']->getClientIp();
        $entity = $arg['request']->query->get('entity');

        switch($entity) {
            case 'Vehicles': $ent = "pojazd"; $sub = $subject->getName(); break;
            case 'Drivers': $ent = "kierowce"; $sub = $subject->getFullName(); break;
            case 'Costs': $ent = "koszt"; $sub = $subject->getName(); break;
            case 'Records': $ent = "ewidencje"; $sub = $subject->getName(); break;
            case 'Route': $ent = "trase"; $sub = $subject->getName(); break;
            case 'User': $ent = "konto"; $sub = $subject->getUsername(); break;
            case 'Deadlines': $ent = "pojazd"; $sub = $subject->getName(); break;
            default: $ent = ""; $sub = ""; break;
        }

        $text = 'Usunięto '.$ent.' "'.$sub.'".';
        $this->sendAction($token, $ip, $text, $arg);
    }

    public function sendAction($token, $ip, $text, $arg) 
    {
        $note = new UserNotify();
        $note->setUser($token->getUser());
        $note->setDate(new \DateTime());
        $note->setIp($ip);
        $note->setText($text);
        $arg['em']->persist($note);
        $arg['em']->flush(); 
    }
}
