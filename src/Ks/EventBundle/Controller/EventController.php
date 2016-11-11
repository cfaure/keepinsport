<?php

namespace Ks\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Ks\EventBundle\Entity\Event;
use Ks\EventBundle\Form\EventEditType;
use Ks\EventBundle\Form\EventType;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Ks\EventBundle\Form\EventHandler;
use Ks\EventBundle\Form\EventEditHandler;

use Ks\EventBundle\KsEventBundle;

/**
 * Event controller.
 *
 */
class EventController extends Controller
{
    /**
     * Lists all Event entities.
     *
     * @Route("/{idClub}/index", name="event")
     * @Template()
     */
    /*public function indexAction($idClub)
    {
         //Verification droits sur le club
        $em = $this->getDoctrine()->getEntityManager();
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        
        $club = $em->getRepository('KsClubBundle:Club')->find($idClub);
        
        if( !is_object( $club )) {
            $this->get('session')->setFlash('alert alert-error', "Ce club n'existe pas");
            return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }
        
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$currentUser->getId(),"club"=>$idClub));
        if($userManageClub==null){
            $this->get('session')->setFlash('alert alert-error', "Vous n'êtes pas autoriser à modifier ce club");
            return $this->redirect($this->generateUrl('ksClub_public_profile', array("clubId" => $idClub)));
        }
        
        $entities = $em->getRepository('KsEventBundle:Event')->findAll();
 
        return array('entities' => $entities,
                       'club' => $club);
    }*/

    /**
     * Finds and displays a Event entity.
     *
     * @Route("/{id}/show", name="ksEvent_show", options={"expose"=true})
     * @Template()
     */
    public function showAction($id)
    {
         //Verification droits sur le club
        $em = $this->getDoctrine()->getEntityManager();
        $agendaRep    = $em->getRepository('KsAgendaBundle:Agenda');
        $articleRep    = $em->getRepository('KsActivityBundle:Article');
        
        //Si l'événement est un article wikisport, on redirige vers la bonne page
        $article = $articleRep->findOneByEvent($id);
        if( is_object($article)) {
             return new RedirectResponse($this->container->get('router')->generate('ksWikisport_show', array('id' => $article->getId())));
        }
 
        $eventInfos = $agendaRep->findAgendaEvents(array(
            "eventId"  => $id,
            "extended"  => true
        ), $this->get('translator'));
        
        if (!isset($eventInfos["event"])) {
            throw $this->createNotFoundException('Unable to find Event entity.');
        }

        return array(
            'event'             => $eventInfos["event"],
            'participations'    => $eventInfos["participations"],
        );
    }

    /**
     * Displays a form to create a new Event entity.
     *
     * @Route("/{idClub}/new", name="event_new")
     * @Template()
     */
    public function newAction($idClub)
    {
        //Verification droits sur le club
        $em = $this->getDoctrine()->getEntityManager();
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        
        $club = $em->getRepository('KsClubBundle:Club')->find($idClub);
        
        if( !is_object( $club )) {
            $this->get('session')->setFlash('alert alert-error', "Ce club n'existe pas");
            return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }
        
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$currentUser->getId(),"club"=>$idClub));
        if($userManageClub==null){
            $this->get('session')->setFlash('alert alert-error', "Vous n'êtes pas autoriser à modifier ce club");
            return $this->redirect($this->generateUrl('ksClub_public_profile', array("clubId" => $idClub)));
        }
        
        //$entities = $em->getRepository('KsEventBundle:Event')->findAll();

        $entity = new Event();
        $form   = $this->createForm(new EventType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'idClub' => $idClub,       
        );
    }

    /**
     * Creates a new Event entity.
     *
     * @Route("/create", name="event_create")
     * @Method("post")
     * @Template("KsEventBundle:Event:new.html.twig")
     */
    public function createAction()
    {
        //Verification droit sur le club
        $idClub = $this->container->get('request')->get("idClub");
        $em = $this->getDoctrine()->getEntityManager();
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        $idUser = $currentUser->getId();
        $club = $em->getRepository('KsClubBundle:Club')->find($idClub);
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$idUser,"club"=>$idClub));
        if($userManageClub==null){
             return $this->redirect($this->generateUrl('admin_club'));
        }
        
        $entity  = new Event();
        $request = $this->getRequest();
        $form    = $this->createForm(new EventType(), $entity);
        
        $formHandler = new EventHandler($form, $request, $this->getDoctrine()->getEntityManager(),$club,$currentUser);
      
        if( $formHandler->process()){
            $this->get('session')->setFlash('alert alert-success', 'users.club_add_event_success');
            return $this->redirect($this->generateUrl('ksClub_events', array('clubId' => $idClub)));
        }
        
        
        /*$form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('ksEvent_show', array('id' => $entity->getId())));
            
        }*/

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'idClub' => $idClub,      
        );
    }

    /**
     * Displays a form to edit an existing Event entity.
     *
     * @Route("/{id}/{idClub}/edit", name="event_edit")
     * @Template()
     */
    public function editAction($id,$idClub)
    {
        //Verification droit sur le club
        $em             = $this->getDoctrine()->getEntityManager();
        $currentUser    = $this->container->get('security.context')->getToken()->getUser();
        $idUser         = $currentUser->getId();
        $club           = $em->getRepository('KsClubBundle:Club')->find($idClub);
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$idUser,"club"=>$idClub));
        if($userManageClub==null){
             return $this->redirect($this->generateUrl('admin_club'));
        }
        
        $entity = $em->getRepository('KsEventBundle:Event')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Event entity.');
        }

        $editForm   = $this->createForm(new EventType(), $entity);
        $deleteForm = $this->createDeleteForm($id,$idClub);
        $arrayDate  = array();
        $arrayDate["start"] =  $entity->getStartDate();
        $arrayDate["end"]   =  $entity->getEndDate();

        return array(
            'entity'        => $entity,
            'edit_form'     => $editForm->createView(),
            'delete_form'   => $deleteForm->createView(),
            'arrayDate'     => $arrayDate,
            'idClub'        => $idClub,
        );
    }

    /**
     * Edits an existing Event entity.
     *
     * @Route("/{id}/update", name="event_update")
     * @Method("post")
     * @Template("KsEventBundle:Event:edit.html.twig")
     */
    public function updateAction($id)
    {
        //Verification droit sur le club
        $idClub         = $this->container->get('request')->get("idClub");
        $em             = $this->getDoctrine()->getEntityManager();
        $currentUser    = $this->container->get('security.context')->getToken()->getUser();
        $idUser         = $currentUser->getId();
        $club           = $em->getRepository('KsClubBundle:Club')->find($idClub);
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$idUser,"club"=>$idClub));
        if ($userManageClub==null){
             return $this->redirect($this->generateUrl('admin_club'));
        }
 
        $entity = $em->getRepository('KsEventBundle:Event')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Event entity.');
        }

        $editForm   = $this->createForm(new EventType(), $entity);
        $deleteForm = $this->createDeleteForm($id,$idClub);
        $request    = $this->getRequest();

        /*$editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('event_edit', array('id' => $id)));
        }*/
        
        $formHandler = new EventHandler($editForm, $request, $em,null,$currentUser);
      
        if( $formHandler->process()){
            $this->get('session')->setFlash('alert alert-success', 'users.club_update_event_success');
            return $this->redirect($this->generateUrl('ksClub_events', array('clubId' => $idClub)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Event entity.
     *
     * @Route("/{id}/{idClub}/delete", name="event_delete")
     * @Method("post")
     */
    public function deleteAction($id,$idClub)
    {
        //Verification droits sur le club
        $em             = $this->getDoctrine()->getEntityManager();
        $currentUser    = $this->container->get('security.context')->getToken()->getUser();
        $idUser         = $currentUser->getId();
        $club           = $em->getRepository('KsClubBundle:Club')->find($idClub);
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$idUser,"club"=>$idClub));
        if ($userManageClub==null){
             return $this->redirect($this->generateUrl('admin_club'));
        }
        
        $form       = $this->createDeleteForm($id,$idClub);
        $request    = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $entity = $em->getRepository('KsEventBundle:Event')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Event entity.');
            }

            $em->remove($entity);
            $em->flush();
        }
        
        $this->get('session')->setFlash('alert alert-success', 'users.club_delete_event_success');
        return $this->redirect($this->generateUrl('ksClub_events', array('clubId' => $idClub)));
    }

    private function createDeleteForm($id,$idClub)
    {
        return $this->createFormBuilder(array('id' => $id,'idClub' => $idClub))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
    /**
     * @Route("/event/all_type_event", name = "ksAllTypesEvents" )
     */
    public function getAllTypesEvents() {
        
        $request = $this->container->get('request');
        
        if ($request->isXmlHttpRequest()) {
            
            $em = $this->getDoctrine()->getEntityManager();
            $typeEvents = $em->getRepository('KsEventBundle:TypeEvent')->findAll();

            foreach($typeEvents as $typeEvent){
                $aNameTypeEvent[$typeEvent->getId()] = $typeEvent->getNomType();
            }

            $response = new Response(json_encode(array('type_events' => $aNameTypeEvent)));
            
             //« Content-type » json pour le retour
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }
        
    }
    
     /**
     * @Route("/event/add_event_to_user_calendar", name = "ksAddEventToUserCalendar" )
     */
    public function addEventToUserCalendar() {
        
        $em                     = $this->getDoctrine()->getEntityManager();
        $request                = $this->container->get('request');
        $result                 = false ;
        $user                   = $this->container->get('security.context')->getToken()->getUser();
        $notificationService    = $this->get('ks_notification.notificationService');
        $activityRep            = $em->getRepository('KsActivityBundle:Activity');
        
        if ($request->isXmlHttpRequest()) {
            
            $em = $this->getDoctrine()->getEntityManager();
            $currentUser = $this->container->get('security.context')->getToken()->getUser();
            
            $nameEvent = $request->request->get('nameEvent');
            $description = $request->request->get('description');
            $idTypeEvent = $request->request->get('idTypeEvent');
            $startEvent = $request->request->get('startEvent');
            $endEvent = $request->request->get('endEvent');
            
            $weatherId = $request->request->get('weather');
            $stateOfHealthId = $request->request->get('stateOfHealth');
            $sportId = $request->request->get('sport');
            
            $generic_adress_name = $request->request->get('generic_adress_name');
            $friendList = $request->request->get('friendList');
            
            if(is_numeric($idTypeEvent)){
                $typeEvent = $em->getRepository('KsEventBundle:TypeEvent')->find($idTypeEvent);
                $place = null;
                if(!empty($generic_adress_name)){
                    $generic_longitude = $request->request->get('generic_longitude');
                    $generic_town = $request->request->get('generic_town');
                    $generic_latitude = $request->request->get('generic_latitude');
                    $generic_country_area = $request->request->get('generic_country_area');
                    $generic_country_code = $request->request->get('generic_country_code');
                    
                    $place = new \Ks\EventBundle\Entity\Place();
                    $place->setRegionLabel($generic_country_area);
                    $place->setCountryCode($generic_country_code);
                    $place->setFullAdress($generic_adress_name);
                    $place->setLatitude($generic_latitude);
                    $place->setLongitude($generic_longitude);
                    $place->setTownLabel($generic_town);
                    $em->persist($place);
                    $em->flush();  
                }
                                
                $event = new \Ks\EventBundle\Entity\Event();
                $event->setName($nameEvent);
                $event->setContent($description);
                $event->setCreationDate(new \DateTime('now'));
                $event->setStartDate(new \DateTime($startEvent));
                $event->setEndDate(new \DateTime($endEvent));
                $event->setLastModificationDate(new \DateTime('now'));
                $event->setUser($currentUser);
                $event->setTypeEvent($typeEvent);
               
                $agenda = $currentUser->getAgenda();
                
                /*$em->persist($event);
                $em->flush();*/
                
                $nomTypeEvent = $typeEvent->getNomType(); 
  
                 // Validation de l'activité automatiquement si temps, météo, et duration remplit 
                // Et de type entrainement ou compétition 
                if($nomTypeEvent=="event_training" || $nomTypeEvent=="event_competition"){
                    
                    //on récupère le sport pour savoir quel type d'activité instancier
                    /*if(!empty($sportId)){
                       $sport = $em->getRepository('KsActivityBundle:Sport')->find($sportId);
                       $typeSport = $sport->getSportType();
                       if(isset($typeSport)){
                           $sportTypeName = $typeSport->getLabel();
                           if($sportTypeName == "team_sport"){
                               $activitySession = new \Ks\ActivityBundle\Entity\ActivitySessionTeamSport($user);
                           }
                           
                           if($sportTypeName == "endurance"){
                               
                               $activitySession = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth($user);
                           }
                           
                           if($sportTypeName == "endurance_under_water"){
                               $activitySession = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceUnderWater($user);
                           }
                       }

                    }*/
                    
                    //création d'un session d'acitivité lié

                    /*$activitySession->setSport($sport);
                    $activitySession->setLabel($nameEvent);*/
                    //$activitySession->setType("session");
                    //$activitySession->setEvent($event);
                    
                    /*if($description){
                       $activitySession->setDescription($description);
                    }*/
                        
                    $startEvent = new \DateTime($startEvent);
                    $endEvent   = new \DateTime($endEvent);

                    $startHourTime   = $startEvent->format("H");
                    $endHourTime     = $endEvent->format("H");
                    $startMinuteTime = $startEvent->format("i");
                    $endMinuteTime   = $endEvent->format("i");

                    $durationHour = $endHourTime-$startHourTime;
                    $durationHour = abs($durationHour);

                    $durationMinute = $endMinuteTime-$startMinuteTime;
                    $durationMinute = abs($durationMinute);

                    if($endMinuteTime < $startMinuteTime){
                        $durationHour = $durationHour-1; 
                    }

                    if($durationMinute < 10){  
                        $duration = "$durationHour:0$durationMinute:00";
                    }else{
                        $duration = "$durationHour:$durationMinute:00";
                    }

                    $duration = new \DateTime($duration);

                    /*$activitySession->setDuration($duration);
                    $activitySession->setModifiedAt(new \DateTime('now'));*/

                    //On créé une activité lié pleine et validé 
                    /*if(!empty($weatherId)){
                            $weather        = $em->getRepository('KsActivityBundle:Weather')->find($weatherId);
                            $activitySession->setWeather($weather);
                    }

                    if(!empty($stateOfHealthId)){
                            $stateOfHealth  = $em->getRepository('KsActivityBundle:StateofHealth')->find($stateOfHealthId);
                            $activitySession->setStateOfHealth($stateOfHealth);
                    }
                    
                    //Par défaut toutes les sessions seront à valider par l'utilisateur
                    //Et les sessions désactivées
                    $activitySession->setIsValidate(false);
                    $activitySession->setIsDisabled(true);
                    
                    //$activitySession->setEvent($event);

                    $em->persist($activitySession);
                    $em->flush();*/
                    //liaison à l'événement courant
                    //$event->setActivitySession($activitySession);
                    
                    //$activityRep->subscribeOnActivity($activitySession, $user);
                    
                    

                    //Création d'une notification pour la validation
                    /*$notification = $notificationService->sendNotification(
                    $activitySession, 
                    $user, 
                    $user,
                    "mustBeValidatedEvent",
                    "Vous avez une activité en attente de validation",$startEvent,$startEvent);*/
                    
                    //Création d'une notification liéé à un événement
                    
                    
                    
                    
                    
                    

                }
                
                $em->persist($event);
                $em->flush();
                
                
                if($place!=null){
                   /*$eventHasPlaces = new \Ks\EventBundle\Entity\EventHasPlaces();
                   $eventHasPlaces->setEvent($event);
                   $eventHasPlaces->setPlace($place); 
                   $em->persist($eventHasPlaces);
                   $em->flush();*/
                }
                $agendaHasEvent = new \Ks\AgendaBundle\Entity\AgendaHasEvents($agenda, $event);
                $em->persist($agendaHasEvent);
                $em->flush();
                
                $notification = $notificationService->sendNotificationEvent(
                    $event, 
                    $user, 
                    $user,
                    "mustBeValidatedEvent",
                    "Tu as une activité sportive dans votre calendrier (".$event->getName()." du ".$event->getStartDate()->format("d/m/Y H:i:s")." au ".$event->getEndDate()->format("d/m/Y H:i:s").") en attente de validation",$startEvent,$startEvent);
                
              
                
               if($friendList!="null"){
                   //Création des invitation à un événement 
                   foreach($friendList as $friendId){
                       $invitationEvent = new \Ks\EventBundle\Entity\InvitationEvent();
                       $invitationEvent->setEvent($event);
                       $userInvited = $em->getRepository('KsUserBundle:User')->find($friendId); 
                       $invitationEvent->setUserInvited($userInvited);
                       $invitationEvent->setUserInviting($user);
                       $status = $em->getRepository('KsEventBundle:Status')->findOneByName("en-attente"); 
                       $invitationEvent->setStatus($status);
                       $em->persist($invitationEvent);
                       $em->flush();
                   }
               }
               
               $result = true;

            }

            $response = new Response(json_encode(array('result' => $result)));
            
             //« Content-type » json pour le retour
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }
        
    }
    
    
     /**
     * @Route("/event/get_all_events_of_user_calendar", name = "ksGetAllEventOfUserCalendar" )
     */
    public function getAllEventsOfUserCalendar() {
        
        $request = $this->container->get('request');
        $result = false ;
        if ($request->isXmlHttpRequest()) {
            
            $em = $this->getDoctrine()->getEntityManager();
            $currentUser = $this->container->get('security.context')->getToken()->getUser();
            $agenda = $currentUser->getAgenda();
            $agendaHasEvents = $agenda->getEvents();
            $aEvents = array();
            foreach($agendaHasEvents as $agendaHasEvent){
                $event = $agendaHasEvent->getEvent();
                $idEvent = $event->getId();
                $titleEvent = $event->getName();
                $startEvent = $event->getStartDate()->format('Y-m-d H:i:s');
                $endEvent = $event->getEndDate()->format('Y-m-d H:i:s');
                
                /*$startEvent = $event->getStartDate();
                $startDate = $startEvent->format("Y-m-d");
                $startTime = $startEvent->format("H:i");
                $endEvent = $event->getEndDate();
                $endDate = $endEvent->format("Y-m-d");
                $endTime = $endEvent->format("H:i");
                $tzOffset = "+02";
                
                $startEvent = "{$startDate}T{$startTime}:00.000{$tzOffset}:00";
                $endEvent = "{$endDate}T{$endTime}:00.000{$tzOffset}:00";*/
                
                
                
                $color = $event->getTypeEvent() != null ? $event->getTypeEvent()->getColor() : null;
                $aEvents[] =  array(
                            'id' => $idEvent,
                            'title' => $titleEvent,
                            'start' => $startEvent,
                            'end'   => $endEvent,
                            'color'   => $color,
                            'allDay' => false, 
                    );
                
            }

            $response = new Response(json_encode($aEvents));

            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
 
        }
        
        
    }
    
    
     /**
     * @Route("/event/delete_an_event_of_a_user_calendar", name = "ksDeleteEventOfAUserCalendar" )
     */
    public function deleteEventOfAUserCalendar() {
        
        $request = $this->container->get('request');
        $result = false ;
        if ($request->isXmlHttpRequest()) {
            
            $em = $this->getDoctrine()->getEntityManager();
            $currentUser = $this->container->get('security.context')->getToken()->getUser();
            $agenda = $currentUser->getAgenda();
            $idAgenda = $agenda->getId();
            $idEvent = $request->request->get('idEvent');

            if(is_numeric($idEvent)){
                $event = $em->getRepository('KsEventBundle:Event')->find($idEvent);
                if($event){

                    $agendaHasEvents = $em->getRepository('KsAgendaBundle:AgendaHasEvents')->findOneBy(array("agenda"=>$idAgenda,"event"=>$idEvent));
                    if($agendaHasEvents){
                        $em->remove($agendaHasEvents);
                        $em->flush();
                        $result = true;
                    }

                    
                    $googleEvent = $event->getIsGoogleEvent();
                    
                    if(isset($googleEvent)){
                       $idGoogleEvent = $googleEvent->getIdUrlEvent();
                       $user = $this->container->get('security.context')->getToken()->getUser(); 
                       $infos = $em->getRepository('KsEventBundle:Event')->deleteGoogleAgendaEvent($user , $idGoogleEvent );
                    }
   
                    $em->remove($event);
                    $em->flush();
                    
                    
                    
                    /*$activitySession = $event->getActivitySession();
                    //Suppression de la notification
                    if(isset($activitySession)){
                        
                        $notification_mustBeValidatedEvent = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName("mustBeValidatedEvent");
                        
                        if(isset($notification_mustBeValidatedEvent)){
                            //Récupération de la notification correspondante
                            $notification = $em->getRepository('KsNotificationBundle:Notification')->findOneBy(array("activity"=>$activitySession->getId(),"type"=>$notification_mustBeValidatedEvent->getId()));
                            if(isset($notification)){
                                $em->remove($notification);
                                $em->flush();
                            }
                            
                        }
                        
                        //suppression de l'acitivySubscribers
                        $ActivityHasSubscribers = $em->getRepository('KsActivityBundle:ActivityHasSubscribers')->findOneByActivity($activitySession->getId());
                        if(isset($ActivityHasSubscribers)){
                           $em->remove($ActivityHasSubscribers);
                           $em->flush(); 
                        }
                        
                        //suppresion de l'activité
                        $em->remove($activitySession);
                        $em->flush();
                            
                    }*/
                    
                    
                  
                  
                    
                }
              
            }
            

            $response = new Response(json_encode(array('result' => $result)));
            
             //« Content-type » json pour le retour
            $response->headers->set('Content-Type', 'application/json');
           
            return $response;
        }
        
    }
    
     /**
     * @Route("/event/edit_an_event_of_a_user_calendar", name = "ksEditEventOfAUserCalendar" )
     */
    public function editEventOfAUserCalendar() {
        
        $request = $this->container->get('request');
        $aEvents = array() ;
        if ($request->isXmlHttpRequest()) {
            
            $em = $this->getDoctrine()->getEntityManager();
            $currentUser = $this->container->get('security.context')->getToken()->getUser();
            $agenda = $currentUser->getAgenda();
            $idAgenda = $agenda->getId();
            $idEvent = $request->request->get('idEvent');
            

            if(is_numeric($idEvent)){
                $event = $em->getRepository('KsEventBundle:Event')->find($idEvent);
                if($event!=null){
                    $fullAdress = "";
                    $eventHasPlaces = $event->getPlaces();
                    if($eventHasPlaces!=null){
                        foreach($eventHasPlaces as $eventHasPlace){
                            $fullAdress = $eventHasPlace->getPlace()->getFullAdress();
                        }
                    }
                    $weatherId = "";
                    $stateOfHealthId = "";
                    $sportId = "";
                    
                    $activitySession = $event->getActivitySession();
                    
                    if($activitySession!=null){
                        $sport = $activitySession->getSport();
                        if($sport!=null){
                            $sportId = $sport->getId();
                        }
                        
                        $stateOfHealth = $activitySession->getStateOfHealth();
                        if($stateOfHealth!=null){
                            $stateOfHealthId = $stateOfHealth->getId();
                        }
                        
                        $weather = $activitySession->getWeather();
                        if($weather!=null){
                            $weatherId = $weather->getId();
                        }
                        
                    }
                    
                    
                    $aEvents[] =  array(
                            'id' => $event->getId(),
                            'title' => $event->getName(),
                            'content' => $event->getContent(),
                            'idTypeEvent'   => $event->getTypeEvent()->getId(),
                            'startDate' => $event->getStartDate()->format("d/m/Y H:i"),
                            'endDate' => $event->getEndDate()->format("d/m/Y H:i"),
                            'fullAdress' => $fullAdress,
                            'sportId' => $sportId,
                            'stateOfHealthId' => $stateOfHealthId,
                            'weatherId' => $weatherId,
                    );
                }
            }
            

            $response = new Response(json_encode(array('aEvents' => $aEvents)));
            
             //« Content-type » json pour le retour
            $response->headers->set('Content-Type', 'application/json');
           
            return $response;
        }
        
    }
    
    /**
     * @Route("/event/edit_event_to_user_calendar", name = "ksEditEventToUserCalendar" )
     */
    public function editEventToUserCalendar() {
        
        $em                     = $this->getDoctrine()->getEntityManager();
        $user                   = $this->container->get('security.context')->getToken()->getUser();
        $notificationService    = $this->get('ks_notification.notificationService');
        $activityRep            = $em->getRepository('KsActivityBundle:Activity');
        $request                = $this->container->get('request');
        $result                 = false ;
        
        if ($request->isXmlHttpRequest()) {
            
            $em = $this->getDoctrine()->getEntityManager();
            $currentUser = $this->container->get('security.context')->getToken()->getUser();
            
            $nameEvent = $request->request->get('nameEvent');
            $description = $request->request->get('description');
            $idTypeEvent = $request->request->get('idTypeEvent');
            $idEvent = $request->request->get('idEvent');
            $dateEndEvent = $request->request->get('dateEndEvent');
            $dateStartEvent = $request->request->get('dateStartEvent');
            $generic_adress_name = null;
            $generic_adress_name = $request->request->get('generic_adress_name');
            $generic_longitude = $request->request->get('generic_longitude');
            $generic_town = $request->request->get('generic_town');
            $generic_latitude = $request->request->get('generic_latitude');
            $generic_country_area = $request->request->get('generic_country_area');
            $generic_country_code = $request->request->get('generic_country_code');
            
            $weatherId = $request->request->get('weather');
            $stateOfHealthId = $request->request->get('stateOfHealth');
            $sportId = $request->request->get('sport');
            
            
            
            
            
            if(is_numeric($idTypeEvent) && is_numeric($idEvent)){
                $typeEvent = $em->getRepository('KsEventBundle:TypeEvent')->find($idTypeEvent);
                $event = $em->getRepository('KsEventBundle:Event')->find($idEvent);
                if($event!=null){
                    $event->setName($nameEvent);
                    $event->setContent($description);
                    $event->setStartDate(new \DateTime($dateStartEvent));
                    $event->setEndDate(new \DateTime($dateEndEvent));
                    $event->setLastModificationDate(new \DateTime('now'));
                    $event->setTypeEvent($typeEvent);
                   
                    
                    $eventHasPlaces = $event->getPlaces();
                    $alreadyExist = false;
                   
                    //TODO gestion multiplace
                     if ($eventHasPlaces!=null) {
                         
                        foreach($eventHasPlaces as $eventHasPlace){
                            $alreadyExist = true;
                            $place = $eventHasPlace->getPlace();
                            
                            $place->setRegionLabel($generic_country_area);
                            $place->setCountryCode($generic_country_code);
                            $place->setFullAdress($generic_adress_name);
                            $place->setLatitude($generic_latitude);
                            $place->setLongitude($generic_longitude);
                            $place->setTownLabel($generic_town);
                            $em->persist($place);
                            $em->flush();
   
                        }
                        
                        if(!$alreadyExist){
                            $place = new \Ks\EventBundle\Entity\Place();
                            $place->setRegionLabel($generic_country_area);
                            $place->setCountryCode($generic_country_code);
                            $place->setFullAdress($generic_adress_name);
                            $place->setLatitude($generic_latitude);
                            $place->setLongitude($generic_longitude);
                            $place->setTownLabel($generic_town);
                            $em->persist($place);
                            $em->flush();

                            $eventHasPlaces = new \Ks\EventBundle\Entity\EventHasPlaces();
                            $eventHasPlaces->setEvent($event);
                            $eventHasPlaces->setPlace($place); 
                            $em->persist($eventHasPlaces);
                            $em->flush();
                        }
                        
  
                    }
                    
                    $nomTypeEvent = $typeEvent->getNomType(); 
  
                    // Validation de l'activité automatiquement si temps, météo, et duration remplit 
                    // Et de type entrainement ou compétition 
                    if($nomTypeEvent=="event_training" || $nomTypeEvent=="event_competition"){
                        
                        /*$activitySession = $event->getActivitySession();
                        
                        if(!isset($activitySession)){
                             if(!empty($sportId)){
                                $sport = $em->getRepository('KsActivityBundle:Sport')->find($sportId);
                                $typeSport = $sport->getSportType();
                                if(isset($typeSport)){
                                    $sportTypeName = $typeSport->getLabel();
                                    if($sportTypeName == "team_sport"){
                                        $activitySession = new \Ks\ActivityBundle\Entity\ActivitySessionTeamSport($user);
                                    }

                                    if($sportTypeName == "endurance"){

                                        $activitySession = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth($user);
                                    }

                                    if($sportTypeName == "endurance_under_water"){
                                        $activitySession = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceUnderWater($user);
                                    }
                                }

                            }
                        }*/

                        /*if($activitySession==null){
                            $activitySession = new \Ks\ActivityBundle\Entity\ActivitySession($currentUser);
                        }*/

                        //$activitySession->setLabel($nameEvent);
                        //$activitySession->setType("session");
                        

                        /*if($description){
                            $activitySession->setDescription($description);
                        }*/

                        $startEvent = new \DateTime($dateStartEvent);
                        $endEvent   = new \DateTime($dateEndEvent);

                        $startHourTime   = $startEvent->format("H");
                        $endHourTime     = $endEvent->format("H");
                        $startMinuteTime = $startEvent->format("i");
                        $endMinuteTime   = $endEvent->format("i");

                        $durationHour = $endHourTime-$startHourTime;
                        $durationHour = abs($durationHour);

                        $durationMinute = $endMinuteTime-$startMinuteTime;
                        $durationMinute = abs($durationMinute);

                        if($endMinuteTime < $startMinuteTime){
                            $durationHour = $durationHour-1; 
                        }

                        if($durationMinute < 10){  
                            $duration = "$durationHour:0$durationMinute:00";
                        }else{
                            $duration = "$durationHour:$durationMinute:00";
                        }

                        $duration = new \DateTime($duration);

                        /*$activitySession->setDuration($duration);
                        $activitySession->setModifiedAt(new \DateTime('now'));*/
                        
                        /*if(!empty($weatherId)){
                             $weather        = $em->getRepository('KsActivityBundle:Weather')->find($weatherId);
                             $activitySession->setWeather($weather);
                        }
                        
                        if(!empty($stateOfHealthId)){
                             $stateOfHealth  = $em->getRepository('KsActivityBundle:StateofHealth')->find($stateOfHealthId);
                             $activitySession->setStateOfHealth($stateOfHealth);
                        }
                        
                        if(!empty($sportId)){
                             $sport          = $em->getRepository('KsActivityBundle:Sport')->find($sportId); 
                             $activitySession->setSport($sport);
                        }
                        
                        $em->persist($activitySession);
                        $em->flush();*/
                        //liaison à l'événement courant
                        //$event->setActivitySession($activitySession);
                        
                        $notification_mustBeValidatedEvent = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName("mustBeValidatedEvent");
                        
                        if(isset($notification_mustBeValidatedEvent)){
                            //Récupération de la notification correspondante
                            $notification = $em->getRepository('KsNotificationBundle:Notification')->findOneBy(array("event"=>$event->getId(),"type"=>$notification_mustBeValidatedEvent->getId()));
                            //on écrase la date de la notification
                            $notification->setCreatedAt($startEvent);
                            $notification->setReadAt($startEvent);
                            $em->persist($notification);
                            $em->flush();
                            
                        }
                        
                        

                    }
                    
                    $em->persist($event);
                    $em->flush();
                    
                    $result = true;
                }

            }
            
            $response = new Response(json_encode(array('result' => $result)));
            
             //« Content-type » json pour le retour
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }
        
    }
    
    /**
     * @Route("/event/update_event_to_user_calendar", name = "ksUpdateEventToUserCalendar" )
     */
    public function updateEventToUserCalendar() {
        
        $request = $this->container->get('request');
        $result = false ;
        if ($request->isXmlHttpRequest()) {
            
            $em = $this->getDoctrine()->getEntityManager();
            $currentUser = $this->container->get('security.context')->getToken()->getUser();
            
            $idEvent = $request->request->get('idEvent');
            $dayDelta = $request->request->get('dayDelta');
            $minuteDelta = $request->request->get('minuteDelta');
            $allDay = $request->request->get('allDay');
            
            if(is_numeric($idEvent)){
                $event = $em->getRepository('KsEventBundle:Event')->find($idEvent);
                if($event!=null){
                    $activitySession = $event->getActivitySession();
                    
                    $startDateTime = $event->getStartDate();
                    $endDateTime = $event->getEndDate();
                                        
                    if($dayDelta < 0){
                        $dayDelta = substr($dayDelta, 1, strlen($dayDelta));
                        $interval = 'P'.$dayDelta.'D';
                        $i = new \DateInterval( $interval );
                        date_sub($startDateTime, $i);
                        date_sub($endDateTime, $i);
                    }else{
                        $interval = 'P'.$dayDelta.'D';
                        $i = new \DateInterval( $interval );
                        date_add($startDateTime, $i);
                        date_add($endDateTime, $i);
                    }
                    
                    if($minuteDelta < 0){
                        $minuteDelta = substr($minuteDelta, 1, strlen($minuteDelta));
                        $interval = 'PT'.$minuteDelta.'M';
                        $i = new \DateInterval( $interval );
                        date_sub($startDateTime, $i);
                        date_sub($endDateTime, $i);
                    }else{
                        $interval = 'PT'.$minuteDelta.'M';
                        $i = new \DateInterval( $interval );
                        date_add($startDateTime, $i);
                        date_add($endDateTime, $i);
                    }

                    $event->setStartDate(new \DateTime($startDateTime->format("Y-m-d H:i:s")));
                    $event->setEndDate(new \DateTime($endDateTime->format("Y-m-d H:i:s")));
                    $event->setLastModificationDate(new \DateTime("Now"));
                    $em->persist($event);
                    $em->flush();

                    $notification_mustBeValidatedEvent = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName("mustBeValidatedEvent");
                        
                    if(isset($notification_mustBeValidatedEvent)){
                        //Récupération de la notification correspondante
                        $notification = $em->getRepository('KsNotificationBundle:Notification')->findOneBy(array("event"=>$event->getId(),"type"=>$notification_mustBeValidatedEvent->getId()));
                        //on écrase la date de la notification
                        $notification->setCreatedAt($startDateTime);
                        $notification->setReadAt($startDateTime);
                        $em->persist($notification);
                        $em->flush();

                    }
                    
                    $result = true;
                }
                
            }
            

            $response = new Response(json_encode(array('result' => $result)));
            
             //« Content-type » json pour le retour
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }
        
    }
    
    /**
     * @Route("/event/resize_event_to_user_calendar", name = "ksResizeEventToUserCalendar" )
     */
    public function resizeEventToUserCalendar() {
        
        $request = $this->container->get('request');
        $result = false ;
        if ($request->isXmlHttpRequest()) {
            
            $em = $this->getDoctrine()->getEntityManager();
            $currentUser = $this->container->get('security.context')->getToken()->getUser();
            
            $idEvent = $request->request->get('idEvent');
            $dayDelta = $request->request->get('dayDelta');
            $minuteDelta = $request->request->get('minuteDelta');
            
            if(is_numeric($idEvent)){
                $event = $em->getRepository('KsEventBundle:Event')->find($idEvent);
                if($event!=null){
                    $endDateTime = $event->getEndDate();
                                        
                    if($dayDelta < 0){
                        $dayDelta = substr($dayDelta, 1, strlen($dayDelta));
                        $interval = 'P'.$dayDelta.'D';
                        $i = new \DateInterval( $interval );
                        date_sub($endDateTime, $i);
                    }else{
                        $interval = 'P'.$dayDelta.'D';
                        $i = new \DateInterval( $interval );
                        date_add($endDateTime, $i);
                    }
                    
                    if($minuteDelta < 0){
                        $minuteDelta = substr($minuteDelta, 1, strlen($minuteDelta));
                        $interval = 'PT'.$minuteDelta.'M';
                        $i = new \DateInterval( $interval );
                         date_sub($endDateTime, $i);
                    }else{
                        $interval = 'PT'.$minuteDelta.'M';
                        $i = new \DateInterval( $interval );
                        date_add($endDateTime, $i);
                    }

                    $event->setEndDate(new \DateTime($endDateTime->format("Y-m-d H:i:s")));
                    $event->setLastModificationDate(new \DateTime('now'));
                    $em->persist($event);
                    $em->flush();
                    $result = true;
                }
                
            } 

            $response = new Response(json_encode(array('result' => $result)));
            
             //« Content-type » json pour le retour
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }
        
    }
    
    /**
     * @Route("/getEventEditForm/{eventId}", requirements={"eventId" = "\d+"}, name = "ksEvent_getEventEditForm", options={"expose"=true} )
     */
    public function getEventEditFormAction($eventId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $eventRep       = $em->getRepository('KsEventBundle:Event');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $event = $eventRep->find($eventId);
        
        if (!is_object($event) ) {
            throw $this->createNotFoundException("Impossible de trouver l'événement " . $eventId .".");
        }

        $form = $this->createForm(new EventEditType(), $event)->createView();

        return $this->render('KsEventBundle:Event:_eventEditForm.html.twig', array(
            'form'          => $form,
            'event'         => $event
        ));
    }
    
    /**
     * @Route("/editEvent/{eventId}", requirements={"eventId" = "\d+"}, name = "ksEvent_editEvent", options={"expose"=true} )
     */
    public function editEventAction($eventId)
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $eventRep           = $em->getRepository('KsEventBundle:Event');
        $user               = $this->get('security.context')->getToken()->getUser();
        
        $event = $eventRep->find($eventId);
        
        if (!is_object($event) ) {
            throw $this->createNotFoundException("Impossible de trouver l'événement " . $eventId .".");
        }

        $form = $this->createForm(new EventEditType(), $event);
        
         // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $formHandler = new EventEditHandler($form, $request, $em);

        $responseDatas = $formHandler->process();
        
        //Si l'événement a été édité
        if($responseDatas['editResponse'] == 1) {
            $responseDatas['basicInfoHtml'] = $this->render('KsEventBundle:Event:_basicInfos.html.twig', array(
                'event'              => $responseDatas['event'],
            ))->getContent();
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * 
     * @Route("/userParticipation/{eventId}", requirements={"eventId" = "\d+"}, name = "ksEvent_userParticipation", options={"expose"=true} )
     * @param int $eventId 
     */
    public function userParticipationAction($eventId)
    {
        $em                         = $this->getDoctrine()->getEntityManager();
        $eventRep                   = $em->getRepository('KsEventBundle:Event');
        $userParticipatesEventRep   = $em->getRepository('KsEventBundle:UserParticipatesEvent');
        $agendaRep                  = $em->getRepository('KsAgendaBundle:Agenda');
        $agendaHasEventsRep         = $em->getRepository('KsAgendaBundle:AgendaHasEvents');
        $user                       = $this->get('security.context')->getToken()->getUser();
        
        //Services
        $notificationService   = $this->get('ks_notification.notificationService');
        
        $event = $eventRep->find($eventId);
        
        if (!is_object($event) ) {
            throw new AccessDeniedException("Impossible de trouver l'evenement " . $eventId .".");
        }
        
        $responseDatas = array();
        $responseDatas["participateResponse"] = 1;
        
        if( ! $userParticipatesEventRep->userAlreadyParticipatesEvent( $event, $user ) ) {
            $userParticipatesEventRep->userParticipatesEvent( $event, $user );
            
            $agenda = $user->getAgenda();
            
            if( is_object( $agenda ) && is_object( $event ) ) {
                if( ! $agendaHasEventsRep->eventIsInAgenda( $agenda, $event ) ) {
                    $agendaHasEventsRep->addEventToAgenda( $agenda, $event );
                    
                    $userParticipatesEventRep->publishEventParticipation( $event, $user );
                } else {
                    $responseDatas["participateResponse"] = -1;
                    $responseDatas["errorMessage"] = "Cet événement est déjà dans votre agenda";
                }
            }
            
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkSubscribeEvent($user->getId());
            
            //Envoi d'une notif à tous les participants de l'événement pour les prévenir que qu'elqu'un s'est inscrit
            foreach( $event->getUsersParticipations() as $userParticipation ) {
                //$userParticipation = $usersParticipations->getUser();
                if( $userParticipation->getId() != $user->getId() ) {
                    $message = $user->getUsername(). " s'est inscrit à l'événement ". $event->getName();
                    $notificationService->sendNotificationEvent($event, $user, $userParticipation, "eventParticipation", $message );
                }
            }
        } else {
            $responseDatas["participateResponse"] = -1;
            $responseDatas["errorMessage"] = "Vous participez déjà à cet événement.";
        }
        
        $event = $agendaRep->findAgendaEvents(array(
            "eventId"  => $eventId,
            "extended"  => true
        ), $this->get('translator'));
        
        $responseDatas["userParticipatesEventLink"] = $this->render('KsEventBundle:Event:_userParticipatesEventLink.html.twig', array(
            'event'             => $event["event"],
            'participations'    => $event["participations"]
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/removeUserParticipation/{eventId}", requirements={"eventId" = "\d+"}, name = "ksEvent_removeUserParticipation", options={"expose"=true} )
     * @param int $eventId 
     */
    public function removeUserParticipationAction($eventId)
    {
        $em                         = $this->getDoctrine()->getEntityManager();
        $eventRep                   = $em->getRepository('KsEventBundle:Event');
        $agendaRep                  = $em->getRepository('KsAgendaBundle:Agenda');
        $agendaHasEventsRep         = $em->getRepository('KsAgendaBundle:AgendaHasEvents');
        $userParticipatesEventRep   = $em->getRepository('KsEventBundle:UserParticipatesEvent');
        $user                       = $this->get('security.context')->getToken()->getUser();
        
        $event = $eventRep->find($eventId);
        
        if (!is_object($event) ) {
            throw new AccessDeniedException("Impossible de trouver l'evenement " . $eventId .".");
        }
        
        $responseDatas = array();
        $responseDatas["participateResponse"] = 1;
        
        if( $userParticipatesEventRep->userAlreadyParticipatesEvent( $event, $user ) ) {
            $userParticipatesEventRep->userParticipatesAnymoreEvent( $event, $user);
            //FIXME : suppression de ks_event_has_user aussi en // car les 2 sont alimentés à tort !! ks_event_has_user sert pour les CLUBS et ks_user_participates_event pour les compét...
            $eventRep->deleteEventHasUsers($eventId, $user->getId());
            
            $agenda = $user->getAgenda();
            
            if( is_object( $agenda ) && is_object( $event ) ) {
                if( $agendaHasEventsRep->eventIsInAgenda( $agenda, $event ) ) {
                    $agendaHasEvents = $agendaHasEventsRep->findOneBy(
                        array(
                            "agenda" => $agenda->getId(), 
                            "event" => $event->getId()
                        )
                    );

                    if (!is_object($agendaHasEvents) ) {
                        throw new AccessDeniedException("Impossible de trouver la liasion agenda-événement.");
                    }
                    $agendaHasEventsRep->removeEventToAgenda( $agendaHasEvents );
                    
                } else {
                    $responseDatas["participateResponse"] = -1;
                    $responseDatas["errorMessage"] = "Cet événement n'est pas dans votre agenda";
                }
            }
            
        } else {
            $responseDatas["participateResponse"] = -1;
            $responseDatas["errorMessage"] = "Vous ne participez pas à cet événement.";
        }
        
        $event = $agendaRep->findAgendaEvents(array(
            "eventId"  => $eventId,
            "extended"  => true
        ), $this->get('translator'));
        
        $responseDatas["userParticipatesEventLink"] = $this->render('KsEventBundle:Event:_userParticipatesEventLink.html.twig', array(
            'event'             => $event["event"],
            'participations'    => $event["participations"]
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * @Route("/{id}/eventInfos", name = "ksEvent_eventInfos", options={"expose"=true} )
     */
    public function eventInfosAction($id)
    {
        $em           = $this->getDoctrine()->getEntityManager();
        $agendaRep    = $em->getRepository('KsAgendaBundle:Agenda');
        $clubRep      = $em->getRepository('KsClubBundle:Club');
        $user         = $this->get('security.context')->getToken()->getUser();
        
        $event = $agendaRep->findAgendaEvents(array(
            "eventId"  => $id,
            "extended"  => true
        ), $this->get('translator'));

        
        shuffle($event["participations"]);
        
        return $this->render('KsEventBundle:Event:_eventInfos.html.twig', array(
            'event'                     => $event["event"],
            'participations'            => $event["participations"],
            'participationsToAffich'    => $event["participations"],//array_slice($event["participations"], 0, 5),
            'affichDesc'                => false
        ));

    }
    
    /**
     * @Route("/{id}/eventParticipants", name = "ksEvent_eventParticipants", options={"expose"=true} )
     */
    public function eventParticipantsAction($id)
    {
        $em           = $this->getDoctrine()->getEntityManager();
        $agendaRep    = $em->getRepository('KsAgendaBundle:Agenda');
        $clubRep      = $em->getRepository('KsClubBundle:Club');
        $user         = $this->get('security.context')->getToken()->getUser();
        
        $event = $agendaRep->findAgendaEvents(array(
            "eventId"  => $id,
            "extended"  => true
        ), $this->get('translator'));

        shuffle($event["participations"]);
        
        return $this->render('KsEventBundle:Event:_eventParticipants.html.twig', array(
            'event'                     => $event["event"],
            'participations'            => $event["participations"],
            'participationsToAffich'    => $event["participations"],//array_slice($event["participations"], 0, 5),
            'affichDesc'                => false
        ));

    }
    
    /**
     * @Route("/{id}/eventForm", name = "ksEvent_eventForm", options={"expose"=true} )
     * @ParamConverter("event", class="KsEventBundle:Event")
     */
    public function eventFormAction($event)
    {
        $em           = $this->getDoctrine()->getEntityManager();
        
        $eventForm             = $this->createForm(new \Ks\EventBundle\Form\EventType(), $event);
        
        return $this->render('KsEventBundle:Event:_eventForm.html.twig', array(
            'form'                     => $eventForm->createView(),
            'class' => 'wikisport'
        ));

    }
    
    /**
     * @Route("/nextEvents/{nbEvents}", name = "ksEvent_nextEvents" )
     */
    public function nextEventsAction($nbEvents)
    {   
        $securityContext    = $this->container->get('security.context');
        $user           = $securityContext->getToken()->getUser();
        $em             = $this->getDoctrine()->getEntityManager();
        $agendaRep      = $em->getRepository('KsAgendaBundle:Agenda');
        
        $events = array();
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $now = new \Datetime("now");
            $events = $agendaRep->findAgendaEvents(array(
                "userId"        => $user->getId(),
                "startOn"       => $now->format("Y-m-d"),
                "eventsFrom"    => array("me", "my_clubs"),
                "eventsTypes"   => array("competitions"),
                "limit"         => $nbEvents,
                "order"         => array("e.startDate" => "ASC")
            ), $this->get('translator'));
        }
        //var_dump($events);
        
        return $this->render('KsEventBundle:Event:_nextEvents.html.twig', array(
            'events'                     => $events,
        ));
    }
}
