<?php

namespace Ks\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Event controller.
 *
 */
class EventClubController extends Controller
{
    /**
     * Displays a form to create a new Event entity.
     *
     * @Route("/{id}/new", name="ksEventClub_new")
     * @ParamConverter("club", class="KsClubBundle:Club")
     * @Template()
     */
    public function newAction($club)
    {
        //Verification droits sur le club
        $em = $this->getDoctrine()->getEntityManager();
        $clubRep      = $em->getRepository('KsClubBundle:Club');
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        if( !is_object( $club )) {
            $this->get('session')->setFlash('alert alert-error', "Ce club n'existe pas");
            return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }
        
        if( !$clubRep->isManager( $club->getId(), $user->getId() ) ) {
            $this->get('session')->setFlash('alert alert-error', "Vous n'êtes pas autoriser à modifier ce club");
            return $this->redirect($this->generateUrl('ksClub_public_profile', array("clubId" => $club->getId())));
        }

        $entity = new \Ks\EventBundle\Entity\Event();
        $form   = $this->createForm(new \Ks\EventBundle\Form\EventType($club), $entity);

        return array(
            'form'   => $form->createView(),
            'clubId' => $club->getId(),       
        );
    }
    
    /**
     * @Route("/{id}/create", name = "ksEventClub_create" )
     * @ParamConverter("club", class="KsClubBundle:Club")
     */
    public function createAction(\Ks\ClubBundle\Entity\Club $club)
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $eventRep           = $em->getRepository('KsEventBundle:Event');
        
        $event                 = new \Ks\EventBundle\Entity\Event();
        $event->setClub($club);
        
        $eventForm             = $this->createForm(new \Ks\EventBundle\Form\EventType($club), $event);

        $formHandler = new \Ks\EventBundle\Form\EventHandler($eventForm, $request, $em, $this->container);

        $responseDatas = $formHandler->process();

        //Si l'événement a été publié
        if ($responseDatas['code'] == 1) {

            //$eventRep->publishEventCreation( $responseDatas['event'] );
            
            $this->get('session')->setFlash('alert alert-success', "L'événement a été créé avec succès");
            return $this->redirect($this->generateUrl('ksClub_public_profile', array(
                'clubId' => $event->getClub()->getId()
            )));

        } else {
            $this->get('session')->setFlash('alert alert-error', 'Il y a des erreurs dans le formulaire');
            return $this->render('KsEventBundle:EventClub:_new.html.twig', array(
                'form'   => $eventForm->createView(),
                'clubId' => $event->getClub()->getId(), 
                'eventId' => $event->getId()
            ));
        }
    }
    
    
    
    /**
     * Displays a form to create a new Event entity.
     *
     * @Route("/{id}/edit", name="ksEventClub_edit")
     * @ParamConverter("event", class="KsEventBundle:Event")
     * @Template()
     */
    public function editAction($event)
    {
        
        $em = $this->getDoctrine()->getEntityManager();
        $clubRep      = $em->getRepository('KsClubBundle:Club');
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $club = $event->getClub();
        if( !is_object( $club )) {
            $this->get('session')->setFlash('alert alert-error', "Ce club n'existe pas");
            return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }
        
        //Verification droits sur le club
        if( !$clubRep->isManager( $club->getId(), $user->getId() ) ) {
            $this->get('session')->setFlash('alert alert-error', "Vous n'êtes pas autoriser à modifier ce club");
            return $this->redirect($this->generateUrl('ksClub_public_profile', array("clubId" => $club->getId())));
        }
        
        $form   = $this->createForm(new \Ks\EventBundle\Form\EventType($club), $event);

        return array(
            'form'   => $form->createView(),
            'clubId' => $club->getId(), 
            'eventId' => $event->getId()
        );
    }
    
    /**
     * @Route("/{id}/update", name = "ksEventClub_update" )
     * @ParamConverter("event", class="KsEventBundle:Event")
     */
    public function updateAction(\Ks\EventBundle\Entity\Event $event)
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        
        if( is_object( $event ) ) {
            $eventForm             = $this->createForm(new \Ks\EventBundle\Form\EventType($club), $event);

             // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
            $formHandler = new \Ks\EventBundle\Form\EventHandler($eventForm, $request, $em, $this->container);

            $responseDatas = $formHandler->process();

            //Si l'événement a été publié
            if ($responseDatas['code'] == 1) {
                $this->get('session')->setFlash('alert alert-success', "L'événement a été édité avec succès");
                return $this->redirect($this->generateUrl('ksClub_public_profile', array(
                    'clubId' => $event->getClub()->getId()
                )));
                
            } else {
                $this->get('session')->setFlash('alert alert-error', 'Il y a des erreurs dans le formulaire');
                return $this->render('KsEventBundle:EventClub:_edit.html.twig', array(
                    'form'   => $eventForm->createView(),
                    'clubId' => $event->getClub()->getId(), 
                    'eventId' => $event->getId()
                ));
            }
        } else {
            $this->get('session')->setFlash('alert alert-error', "Impossible de trouver l'événement");
            return $this->redirect($this->generateUrl('ksClub_public_profile', array(
                'clubId' => $event->getClub()->getId()
            )));
        } 
    }
    
    /**
     * Finds and displays a Event entity.
     *
     * @Route("/{id}/show", name="ksEventClub_show", options={"expose"=true})
     * @Template()
     */
    public function showAction($id)
    {
         //Verification droits sur le club
        $em = $this->getDoctrine()->getEntityManager();
        $agendaRep    = $em->getRepository('KsAgendaBundle:Agenda');
        $clubRep      = $em->getRepository('KsClubBundle:Club');
        $user         = $this->container->get('security.context')->getToken()->getUser();


        /*$event = $em->getRepository('KsEventBundle:Event')->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Unable to find Event entity.');
        }*/
        
        $eventInfos = $agendaRep->findAgendaEvents(array(
            "eventId"  => $id,
            "extended"  => true
        ), $this->get('translator'));
        
        if (!isset($eventInfos["event"])) {
            $this->get('session')->setFlash('alert alert-error', "Impossible de trouver l'événement");
            return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }
        
        $isManager = false;
        
        if ( $eventInfos["event"]["club_id"] != null && $clubRep->isManager( $eventInfos["event"]["club_id"], $user->getId() ) ) {
            $isManager = true;
        }

        return array(
            'event'             => $eventInfos["event"],
            'participations'    => $eventInfos["participations"],
            'isManager'         => $isManager
        );
    }
    
    /**
     * Deletes a event club.
     *
     * @Route("/{id}/delete", name="ksEventClub_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $clubRep = $em->getRepository('KsClubBundle:Club');
        $eventRep = $em->getRepository('KsEventBundle:Event');
        
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $event = $eventRep->find($id);
        if (is_object($event)) {
            $club = $event->getClub();
            //$club = $em->getRepository('KsClubBundle:Club')->find($idClub);
            if( !$clubRep->isManager( $club->getId(), $user->getId() ) ) {
                return $this->redirect($this->generateUrl('ksEventClub_show', array('id' => $id)));
            }
        
            $em->remove($event);
            $em->flush();
            
            $this->get('session')->setFlash('alert alert-success', "L'événement a été suprimée");
            return $this->redirect($this->generateUrl('ksClub_events', array('clubId' => $club->getId())));
        }

        return $this->redirect($this->generateUrl('ksEventClub_show', array('id' => $id)));
    }
    
    /* RENDERS */
    public function nextEventsAction($clubId, $nbEvents)
    {    
        $em             = $this->getDoctrine()->getEntityManager();
        $agendaRep      = $em->getRepository('KsAgendaBundle:Agenda');
        $clubRep        = $em->getRepository('KsClubBundle:Club');
     
        
        $now = new \Datetime("now");
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $isManager      = $clubRep->isManager($clubId, $user->getId());
        
        $events = $agendaRep->findAgendaEvents(array(
            "startOn"       => $now->format("Y-m-d"),
            //"clubIds"    => array("me", "my_clubs", "public"),
            "isManager"     => $isManager,
            "clubIds"       => array($clubId),
            "limit"         => $nbEvents,
            "order"         => array("e.startDate" => "ASC")
        ), $this->get('translator'));
        
        //var_dump($events);
        
        return $this->render('KsEventBundle:Event:_nextEvents.html.twig', array(
            'events'                     => $events,
        ));
    }
}
