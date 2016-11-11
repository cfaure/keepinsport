<?php
// src/Sdz/BlogBundle/Form/ArticleHandler.php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Ks\ActivityBundle\Entity\ActivityStatus;
use Symfony\Component\DependencyInjection\Container;

class ActivityStatusHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $container;

    public function __construct(Form $form, Request $request, EntityManager $em, Container $container)
    {
        $this->form         = $form;
        $this->request      = $request;
        $this->em           = $em;
        $this->container    = $container;
    }

    public function process()
    {    
        if ( $this->request->isXmlHttpRequest()) {
            
            $parameters       = $this->request->request->all();
            
            $this->form->bindRequest($this->request);
            
            if ( $this->form->isValid() ) {
                $responseDatas = $this->onSuccess($this->form->getData(), $parameters);  
            } else {
                $errors = \Form\FormValidator::getErrorMessages($this->form);
                $responseDatas = $this->onError($errors);   
            }
        } else {
            $responseDatas = $this->onIsNotXmlHttpRequest(); 
        }

        return $responseDatas;
    }

    public function onSuccess(\Ks\ActivityBundle\Entity\ActivityStatus $activityStatus, $parameters)
    {
        
        
        $place = $activityStatus->getPlace();
        if ( is_object( $place ) && ( $place->getFullAdress() == null || $place->getFullAdress() == "" ) ) {
            $this->em->remove($place);
            $activityStatus->setPlace(null);
        }
        
        $this->em->persist($activityStatus);
        $this->em->flush();
        
        //Si la case n'a pas été cochée, le paramètre n'existe pas
        if( isset ( $parameters["isImportant"] ) ) {
            
            $host = $this->container->getParameter('host');
            $pathWeb = $this->container->getParameter('path_web');
            //$host = $_SERVER["HTTP_HOST"];
    
            $club = $activityStatus->getClub();
            $user = $activityStatus->getUser();
            //Si le statut est posté par un club, le message important doit être visible par tous les membres
            if( $club != null) {
                $subject = "Tu as reçu un message important de " . $club->getName();
                foreach( $club->getUsers() as $clubHasUser ) {
                    $userReadsImportantStatus = new \Ks\ActivityBundle\Entity\UserReadsImportantStatus( $activityStatus, $clubHasUser->getUser() );
                    $this->em->persist( $userReadsImportantStatus );
                    $this->em->flush();
                    
                    $contentMail = $this->container->get('templating')->render('KsActivityBundle:Activity:_importantStatus_mail.html.twig', 
                        array(
                            'host'              => $host,
                            'activityStatus'    => $activityStatus,
                            'toUser'            => $clubHasUser->getUser(),
                            'fromClub'          => $club
                        ), 
                    'text/html');
                    
                    $body = $this->container->get('templating')->render('KsUserBundle:User:template_mail.html.twig', 
                        array(
                            'host'      => $host,
                            'pathWeb'   => $pathWeb,
                            'content'   => $contentMail,
                            'user'      => is_object( $user ) ? $user : null
                        ), 
                    'text/html');
                    
                    //Envoie d'un mail si le statut est important
                     $message = \Swift_Message::newInstance()
                        ->setContentType('text/html')
                        ->setSubject($subject)
                        ->setFrom("contact@keepinsport.com")
                        ->setTo($clubHasUser->getUser()->getEmail())
                        ->setBody($body);      

                    $this->container->get('mailer')->getTransport()->start();
                    $this->container->get('mailer')->send($message);
                    $this->container->get('mailer')->getTransport()->stop();
                }
            } elseif( $user != null ) {
                $subject = "Tu as reçu un message important de " . $user->getUsername();
                $userRep = $this->em->getRepository("KsUserBundle:User");
                //*$friends = $userRep->getFriendsOf( $user );
                $friends = $userRep->getFriendList( $user->getId() );
                foreach( $friends as $friend ) {
                    $userReadsImportantStatus = new \Ks\ActivityBundle\Entity\UserReadsImportantStatus( $activityStatus, $friend );
                    $this->em->persist($userReadsImportantStatus);
                    $this->em->flush();
                    
                    $contentMail = $this->container->get('templating')->render('KsActivityBundle:Activity:_importantStatus_mail.html.twig', 
                        array(
                            'host'              => $host,
                            'activityStatus'    => $activityStatus,
                            'toUser'            => $friend,
                            'fromUser'          => $user
                        ), 
                    'text/html');
                    
                    $body = $this->container->get('templating')->render('KsUserBundle:User:template_mail.html.twig', 
                        array(
                            'host'      => $host,
                            'pathWeb'   => $pathWeb,
                            'content'   => $contentMail,
                            'user'      => is_object( $user ) ? $user : null
                        ), 
                    'text/html');
                    
                    //Envoie d'un mail si le statut est important
                     $message = \Swift_Message::newInstance()
                        ->setContentType('text/html')
                        ->setSubject($subject)
                        ->setFrom("contact@keepinsport.com")
                        ->setTo($friend->getEmail())
                        ->setBody($body);      

                    $this->container->get('mailer')->getTransport()->start();
                    $this->container->get('mailer')->send($message);
                    $this->container->get('mailer')->getTransport()->stop();
                }
            }
        }

        $responseDatas = array(
            'publishResponse' => 1,
            'activityStatus' => $activityStatus,
        ); 
        
        return $responseDatas;
    }
    
    public function onError($errors)
    {
        $responseDatas = array(
            'publishResponse'   => -1,
            'errorMessage'      => "Le formulaire n'est pas valide.",
            'errors'            => $errors
        ); 
        
        return $responseDatas;
    }
    
    public function onIsNotXmlHttpRequest()
    {
        $responseDatas = array(
            'publishResponse'   => -1,
            'errorMessage'      => "La requête n'est pas une requête ajax."
        ); 
        
        return $responseDatas;
    }
}
