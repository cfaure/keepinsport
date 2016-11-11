<?php

namespace Ks\MessageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


class MessageController extends Controller
{
    /**
     * @Route("/box/{numPage}", name = "ksMessage_box", options={"expose"=true}  )
     */
    public function boxAction($numPage)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $messageRep         = $em->getRepository('KsMessageBundle:Message');
        
        if( !is_object( $user )) return $this->redirect($this->generateUrl('fos_user_security_login'));
        
        $nb_messages = $messageRep->findNbReceivesMessages( $user->getId() );

        $nb_messages_page = 5;
  
        $nb_pages = $nb_messages > 0 ? ceil($nb_messages/$nb_messages_page) : 0;
        $nb_pages = $nb_pages > 0 ? $nb_pages : 1;
        $offset = ($numPage-1) * $nb_messages_page;

        $messages = $messageRep->findReceivesMessages( $user->getId(), $nb_messages_page, $offset );

        // Ici on a changé la condition pour déclencher une erreur 404
        // lorsque la page est inférieur à 1 ou supérieur au nombre max.
        if( $numPage < 1 OR $numPage > $nb_pages )
        {
            throw $this->createNotFoundException('Page inexistante (page = '. $numPage .')');
        }
        
        return $this->render('KsMessageBundle:Message:receivedMessages.html.twig', array(
            'messages' => $messages,
            'page'     => $numPage,  
            'nb_pages' => $nb_pages,
        ));
    }
    
    /**
     * @Route("/new/{userId}", name = "ksMessage_new", defaults={"userId" = null}, options={"expose"=true}  )
     * @Template()
     */
    public function newAction($userId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $messageRep         = $em->getRepository('KsMessageBundle:Message');
        $userRep            = $em->getRepository('KsUserBundle:User');
        
        if( !is_object( $user )) return $this->redirect($this->generateUrl('fos_user_security_login'));
        
        $message     = new \Ks\MessageBundle\Entity\Message($user);
        $form = $this->createForm(new \Ks\MessageBundle\Form\MessageType($user, false, $userId), $message);
        
        $toUser =null;
        if (isset($userId) && !is_null($userId))
            $toUser = $userRep->find($userId);
        
        return array(
            'message'   => $message,
            'form'      => $form->createView(),
            'toUser'    => $toUser
        );
    }
    
    /**
     * @Route("/clubNew/{clubId}", name = "ksMessage_club_new", defaults={"clubId" = null}, options={"expose"=true}  )
     * @Template()
     */
    public function clubNewAction($clubId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $messageRep         = $em->getRepository('KsMessageBundle:Message');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        
        if( !is_object( $user )) return $this->redirect($this->generateUrl('fos_user_security_login'));
        
        $toUsers =null;
        if (isset($clubId) && !is_null($clubId)) {
            $club = $clubRep->find($clubId);
            $message     = new \Ks\MessageBundle\Entity\Message($user);
            $form = $this->createForm(new \Ks\MessageBundle\Form\MessageType($user, false, null, $club), $message);
            $qb = $userRep->findClubMembersQB($club);
            $res = $qb->getQuery()->getResult();
            foreach ($res as $curRes) {
                $toUsers .= $curRes->getId() . ',';
            }
            $toUsers .= '-1';
        }
        
        return $this->render('KsMessageBundle:Message:new.html.twig', array(
            'message'   => $message,
            'form'      => $form->createView(),
            'toUsers'   => $toUsers,
            'clubId'    => $clubId
        ));
    }
    
    /**
     * @Route("/sendMessage", name = "ksMessage_send", options={"expose"=true} )
     */
    public function sendMessage() {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        
        //Services
        $notificationService   = $this->get('ks_notification.notificationService');
        
        if( !is_object( $user )) return $this->redirect($this->generateUrl('fos_user_security_login'));
        
        $message     = new \Ks\MessageBundle\Entity\Message($user);
        $form = $this->createForm(new \Ks\MessageBundle\Form\MessageType($user), $message);
        
        $formHandler = new \Ks\MessageBundle\Form\MessageHandler($form, $request, $em, $this->container);

        $responseDatas = $formHandler->process();
        
        if( $responseDatas["response"] == 1) {
            $this->get('session')->setFlash('alert alert-success', 'Message envoyé avec succès !');
            $firstMessage = is_object( $responseDatas['message']->getPreviousMessage() ) ? $responseDatas['message']->getPreviousMessage() : $responseDatas['message'];
            
            //On envoi une notification à tous les participants de la conversation 
            $notificationService->sendMessageNotifications($responseDatas['message'], $user);
             
             return $this->redirect($this->generateUrl('ksMessage_show', array('id' => $firstMessage->getId())));
        } else {
            $this->get('session')->setFlash('alert alert-danger', 'Message non envoyé !');
        }
        return array(
            'message' => $message,
            'form' => $form->createView(),
        );
    }
    
    /**
     * @Route("/sendMessageClub/{clubId}", name = "ksMessage_send_club", defaults={"clubId" = null}, options={"expose"=true} )
     */
    public function sendMessageClub($clubId) {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        
        //Services
        $notificationService   = $this->get('ks_notification.notificationService');
        
        if( !is_object( $user )) return $this->redirect($this->generateUrl('fos_user_security_login'));
        
        if (isset($clubId) && !is_null($clubId)) {
            $club = $clubRep->find($clubId);
            $message     = new \Ks\MessageBundle\Entity\Message($user);
            $form = $this->createForm(new \Ks\MessageBundle\Form\MessageType($user, false, null, $club), $message);
        }
        
        $formHandler = new \Ks\MessageBundle\Form\MessageHandler($form, $request, $em, $this->container);

        $responseDatas = $formHandler->process();
        
        if( $responseDatas["response"] == 1) {
            $this->get('session')->setFlash('alert alert-success', 'Message envoyé avec succès !');
            $firstMessage = is_object( $responseDatas['message']->getPreviousMessage() ) ? $responseDatas['message']->getPreviousMessage() : $responseDatas['message'];
            
            //On envoi une notification à tous les participants de la conversation 
            $notificationService->sendMessageNotifications($responseDatas['message'], $user);
             
             return $this->redirect($this->generateUrl('ksMessage_show', array('id' => $firstMessage->getId())));
        } else {
            $this->get('session')->setFlash('alert alert-danger', 'Message non envoyé !');
        }
        return array(
            'message' => $message,
            'form' => $form->createView(),
        );
    }
    
    /**
     * Finds and displays a Message entity.
     *
     * @Route("/{id}/show", name="ksMessage_show")
     * @ParamConverter("message", class="KsMessageBundle:Message")
     * @Template()
     */
    public function showAction(\Ks\MessageBundle\Entity\Message $message)
    {
        $user       = $this->get('security.context')->getToken()->getUser();
        $em         = $this->getDoctrine()->getEntityManager();
        $messageRep = $em->getRepository('KsMessageBundle:Message');
        
        if( !is_object( $user )) return $this->redirect($this->generateUrl('fos_user_security_login'));

        $answer     = new \Ks\MessageBundle\Entity\Message($user);
        
        $answer->setSubject( "Re: " . $message->getSubject() );
        $answer->setPreviousMessage( $message );
        
        /*$answer->addUser($message->getFromUser());
        foreach( $message->getToUsers() as $toUser ) {
            //On ne renvoi pas le message à nous même
            if( $toUser != $user ) {
                $answer->addUser( $toUser );
            }
        }*/
        $isAnAnswer = true;
        $answerForm = $this->createForm(new \Ks\MessageBundle\Form\MessageType($user, $isAnAnswer), $answer);

        return array(
            'message'      => $message, 
            'answerForm'  => $answerForm->createView()
        );
    }
    
    /**
     * Deletes a Message entity.
     *
     * @Route("/{id}/delete", name="ksMessage_delete", options={"expose"=true})
     * @ParamConverter("message", class="KsMessageBundle:Message")
     */
    public function deleteAction(\Ks\MessageBundle\Entity\Message $message)
    {
        $user       = $this->get('security.context')->getToken()->getUser();
        $em         = $this->getDoctrine()->getEntityManager();
        
        if( !is_object( $user )) return $this->redirect($this->generateUrl('fos_user_security_login'));
        
        $result = $message->removeUser($user);
        
        if( $result ) {
            $em->persist($message);
            $em->flush();
            $this->get('session')->setFlash('alert alert-success', 'Message supprimé avec succès');
        } else {
            $this->get('session')->setFlash('alert alert-error', 'Message non supprimé');
        }

        return $this->redirect($this->generateUrl('ksMessage_box', array('numPage' => 1)));
    }
}
