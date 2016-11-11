<?php

namespace Ks\AgendaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;


class AgendaClubController extends Controller
{
    /**
     * @Route("/{id}/agenda", name = "ksAgendaClub_index", options={"expose"=true}  )
     * @Template()
     */
    public function indexAction($id)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $user               = $this->get('security.context')->getToken()->getUser();
        
        $session = $this->get('session');
        $session->set('pageType', 'club');
        $session->set('page', 'agenda');
        
        $club = $clubRep->find($id);
        
        $event                 = new \Ks\EventBundle\Entity\Event();
        $event->setClub($club);
        $eventForm             = $this->createForm(new \Ks\EventBundle\Form\EventType($club), $event);
        
        //Formulaire pour filtre par SPORT
        //$activitySession            = new \Ks\ActivityBundle\Entity\ActivitySession($user);
        //$eventSportChoiceForm       = $this->createForm(new \Ks\ActivityBundle\Form\SportType('MultiSimple'), $activitySession);
        
        //Formulaire pour filtre par USER
        $clubHasUsers               = new \Ks\ClubBundle\Entity\ClubHasUsers( $club );
        $eventUsersChoiceForm       = $this->createForm(new \Ks\ClubBundle\Form\ClubHasUsersType( $club, $user, 'fromClub'), $clubHasUsers);
        
        //Formulaire pour filtre par Plan d'entrainement
        $coachingPlan = new \Ks\CoachingBundle\Entity\CoachingPlan();
        $eventCoachingPlanChoiceForm       = $this->createForm(new \Ks\CoachingBundle\Form\CoachingPlanType($club), $coachingPlan);
        
        //Si le club n'a pas d'agenda, on le crée
        if( !is_object( $club->getAgenda() ) ) {
            $agenda = new \Ks\AgendaBundle\Entity\Agenda();
            $club->setAgenda( $agenda );
            
            $em->persist( $agenda );
            $em->persist( $club );
            $em->flush();
            
        }
        
        $isManager = $clubRep->isManager($club->getId(), $user->getId());
        $status         = new \Ks\ActivityBundle\Entity\ActivityStatus();
        $statusForm     = $this->createForm(new \Ks\ActivityBundle\Form\ActivityStatusType(), $status);
        $link           = new \Ks\ActivityBundle\Entity\ActivityStatus();
        $linkForm       = $this->createForm(new \Ks\ActivityBundle\Form\ActivityLinkType(), $link);
        $photo          = new \Ks\ActivityBundle\Entity\ActivityStatus();
        $photoForm      = $this->createForm(new \Ks\ActivityBundle\Form\ActivityPhotoType(), $photo);
        
        //Récupération du formulaire pour création de la vue à plat d'un plan
        $article      = new \Ks\ActivityBundle\Entity\Article();
        
        $articleForm  = $this->createForm(new \Ks\ActivityBundle\Form\ArticleType(), $article);
        
        //Récupération des membres dont la date de fin de coaching approche
        $scheduleRequired = null;
        if ($isManager) {
            $scheduleRequired = $clubRep->getScheduleRequired($club->getId());
        }
        
        //Récupération des séances
        $favoriteSessions = $clubRep->getSessions('club', $club->getId());
        $wikiSessions = null;
        
        //Récupération des dates debut et fin des plans du user
        $coachingPlansData = $clubRep->getCoachingPlansData('club', $club->getId());
        
        $citations = array();
        
        ini_set('memory_limit', '1024M');
        
        return $this->render('KsAgendaBundle::dashboard.html.twig', array(
            "isManager"                     => $isManager,
            "clubIsCoach"                   => is_null($club->getIsCoach()) ? 0 : $club->getIsCoach(),
            "delayWarning"                  => $club->getDelayWarning(),
            "scheduleRequired"              => $scheduleRequired,
            "statusForm"                    => $statusForm->createView(),
            "linkForm"                      => $linkForm->createView(),
            "photoForm"                     => $photoForm->createView(),
            "eventForm"                     => $eventForm->createView(),
            //"eventSportChoiceForm"          => $eventSportChoiceForm->createView(),
            "eventUsersChoiceForm"          => $eventUsersChoiceForm->createView(),
            "eventCoachingPlanChoiceForm"   => $eventCoachingPlanChoiceForm->createView(),
            "club"                          => $club,
            "session"                       => $session->get("page"),
            "articleForm"                   => $articleForm->createView(),
            "favoriteSessions"              => $favoriteSessions,
            "wikiSessions"                  => $wikiSessions,
            "coachingPlansData"             => $coachingPlansData,
            'citations'                     => $citations
        ));
    }
    
    /**
      * @Route("/{id}/getEvents", name = "ksAgendaClub_getEvents", options={"expose"=true} )
      * @ParamConverter("club", class="KsClubBundle:Club")
      */
    public function getEvents(\Ks\ClubBundle\Entity\Club $club) {
 
        $em           = $this->getDoctrine()->getEntityManager();
        $agendaRep    = $em->getRepository('KsAgendaBundle:Agenda');
        $eventRep     = $em->getRepository('KsEventBundle:Event');
        $clubRep      = $em->getRepository('KsClubBundle:Club');
        $user         = $this->get('security.context')->getToken()->getUser();
                
        $request = $this->getRequest();
        
        //Paramètres GET
        //$parameters = $request->query->all();
        $parameters = $request->request->all();
        $startOn    = date('Y-m-d', $parameters['start']);
        $endOn      = date('Y-m-d', $parameters['end']);
        
        $eventsTypes            = isset( $parameters['eventsTypes'] ) && $parameters['eventsTypes'][0] != "" ? $parameters['eventsTypes'] : array();
        $eventsSports           = isset( $parameters['eventsSports'] ) && $parameters['eventsSports'][0] != "" ? $parameters['eventsSports'] : array();
        $eventsUsers            = isset( $parameters['eventsUsers'] ) && $parameters['eventsUsers'][0] != "" ? $parameters['eventsUsers'] : array();
        $eventsCoachingPlans    = isset( $parameters['eventsCoachingPlans'] ) && $parameters['eventsCoachingPlans'][0] != "" ? $parameters['eventsCoachingPlans'] : array();
        $eventsAvailability     = isset( $parameters['eventsAvailability'] ) && $parameters['eventsAvailability'] != "" ? $parameters['eventsAvailability'] : "all";
        
        //Si choix MES SPORTS, le tableau doit contenir la valeur ""
        if (in_array( "", $eventsSports) && count($eventsSports) > 0) $my_sports = true;
        else $my_sports = false;
        
        $isManager = $clubRep->isManager($club->getId(), $user->getId());
        
        $events = $agendaRep->findAgendaEvents(array(
            "clubId"                => $club->getId(),
            "isManager"             => $isManager,
            "startOn"               => $startOn,
            "endOn"                 => $endOn,
            "eventsFrom"            => array(),
            "eventsTypes"           => $eventsTypes,
            "eventsSports"          => $eventsSports,
            "eventsUsers"           => $eventsUsers,
            "eventsCoachingPlans"   => $eventsCoachingPlans,
            "my_sports"             => $my_sports,
            "eventsAvailability"    => $eventsAvailability
        ), $this->get('translator'));
        
        $myClubsIds = $clubRep->findUserClubsIds($user->getId());
        
        //Ajout des participants en tableau (plus facile ici que récupérer en sql avec un GROUP CONCAT à retravailler après car 7,85 à transformer en tableau...
        foreach($events as $event) {
            foreach ($eventRep->find($event['id'])->getUsersParticipations() as $key => $userParticipates) {
                $locked = true;
                if ( $event["club_id"] != null && in_array( $event["club_id"], $myClubsIds ) ) {
                    //Si l'utilisateur est manager du club ou qu'il fait partie des participants il peut modifier
                    if ($clubRep->isManager( $event["club_id"], $user->getId())) $locked = false;
                    if ($userParticipates->getId() == $user->getId()) $locked = false;
                }
                $event["usersParticipate"][] = array('id' => $userParticipates->getId(), 'locked' => $locked, 'text' => $userParticipates->getUsername());
            }
            $newEvents[] = $event;
        }
        //var_dump($newEvents);exit;
        
        if (!isset($newEvents)) {
            //Cas ou aucun événement correspond aux critères de recherche de l'utilisateur
            $newEvents = $events;
        }
        $response = new Response(json_encode($newEvents));

        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }
    
    /**
     * @Route("/{id}/createEvent", name = "ksAgendaClub_createEvent", options={"expose"=true} )
     * @ParamConverter("club", class="KsClubBundle:Club")
     */
    public function createEventAction(\Ks\ClubBundle\Entity\Club $club)
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $agendaRep          = $em->getRepository('KsAgendaBundle:Agenda');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $eventRep           = $em->getRepository('KsEventBundle:Event');
        
        $event              = new \Ks\EventBundle\Entity\Event();
        $event->setClub($club);
        $eventForm          = $this->createForm(new \Ks\EventBundle\Form\EventType($club), $event);
        
         // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $formHandler = new \Ks\EventBundle\Form\EventHandler($eventForm, $request, $em, $this->container);

        $responseDatas = $formHandler->process();
        
        //Si l'événement a été publié
        if ($responseDatas['code'] == 1) {
            //Si le club n'a pas d'agenda, on le cré
            if( !is_object( $club->getAgenda() ) ) {
                $agenda = new \Ks\AgendaBundle\Entity\Agenda();
                $club->setAgenda( $agenda );

                $em->persist( $agenda );
                $em->persist( $club );
                $em->flush();

            }
        
            //Si le club est de type CLUB (isCoach = 0 ou null alors on doit dupliquer chaque event pour tous les membres
            $club = $event->getClub();
            if (!is_null($club) && $club->getIsCoach() == 0) {
                $agendaRep = $em->getRepository('KsAgendaBundle:Agenda');
                $userRep = $em->getRepository('KsUserBundle:User');
                foreach( $club->getUsers() as $clubHasUser ) {
                    $memberId = $clubHasUser->getUser()->getId();
                    foreach($event->getUsersParticipations() as $userParticipates){
                        if ($clubHasUser->getUser() != $userParticipates) {
                            //var_dump("create:".$clubHasUser->getUser()->getId()."-".$userParticipates->getId());
                            $agendaRep->duplicateEvent( $event, 0, 0, true, $memberId, $userRep->findCoachingPlanIDFromClub($memberId, $club->getId()));
                        }
                    }
                }
            }
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
}
