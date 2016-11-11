<?php
namespace Ks\ActivityBundle\Form;
//namespace FormEntity;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Ks\ActivityBundle\Entity\ActivityStatus;

class TeamSportSessionHandler
{
    protected $form;
    protected $frequencyForm;
    protected $coachingPlanForm;
    protected $request;
    protected $em;
    protected $container;
    protected $translator;

    public function __construct(Form $form, Form $frequencyForm, Form $coachingPlanForm, Request $request, EntityManager $em, $container =null, $translator =null)
    {
        $this->form             = $form;
        $this->frequencyForm    = $frequencyForm;
        $this->coachingPlanForm = $coachingPlanForm;
        $this->request          = $request;
        $this->em               = $em;
        $this->container        = $container;
        $this->translator       = $translator;
    }

    public function process()
    {    
        if ( $this->request->isXmlHttpRequest()) {
            
            $teamSportSession = $this->form->getData();
            $request          = $this->request;
            $parameters       = $request->request->all();
            
            
            $this->form->bindRequest($request);
            
            $this->frequencyForm->bindRequest($this->request);
            $this->em->persist($this->frequencyForm->getData());
            $this->em->flush();
            
            if ( $this->form->isValid() ) {
//                
//                if(isset($parameters["eventId"]) && $parameters["eventId"] != "NC" && $parameters["eventId"] != -1) {
//                    $event = $this->em->getRepository('KsEventBundle:Event')->find($parameters["eventId"]); 
//                    if (!is_object($event) ) {
//                        echo "Impossible de trouver l'evènement " . $eventId .".";
//                    }
//                    $notificationType = $this->em->getRepository('KsNotificationBundle:NotificationType')->findOneByName("mustBeValidatedEvent");
//                    
//                    if (!is_object($notificationType) ) {
//                        echo "Impossible de trouver ce type de notification 'mustBeValidatedEvent' ";
//                    }
//                    
//                    //On passe la notification à répondu
//                    $notification = $this->em->getRepository('KsNotificationBundle:Notification')->findOneBy(array("event"=>$parameters["eventId"],"type"=> $notificationType->getId()));
//                     
//                    if (!is_object($notification) ) {
//                        echo "Impossible de trouver la notification de l'événement ".$parameters["eventId"]." ayant pour type ".$notificationType->getId()." ";
//                    }
//                    
//                    $notification->setGotAnAnswer(true);
//                    
//                    $teamSportSession->setEvent($event);
//                }
                $responseDatas = $this->onSuccess($teamSportSession, $parameters);  
            } else {
                $errors = \Form\FormValidator::getErrorMessages($this->form);
                $responseDatas = $this->onError($errors);  
            }
        } else {
            $responseDatas = $this->onIsNotXmlHttpRequest(); 
        }
//$responseDatas = $this->onSuccess($this->form->getData());  
         
        return $responseDatas;
    }

    public function onSuccess(\Ks\ActivityBundle\Entity\ActivitySessionTeamSport $teamSportSession, $parameters)
    {
        /*$description = strip_tags($teamSportSession->getDescription());
        $teamSportSession->setDescription($description);*/
        /*if ( $teamSportSession->getClub() == null) {
            $teamSportSession->setClub(null);
        }*/
        if ( $teamSportSession->getWasOfficial() == 1 ) {
            $teamSportSession->setWasOfficial(true);
        } else {
            $teamSportSession->setWasOfficial(false);
        }
    
        $place = $teamSportSession->getPlace();
        if ( is_object( $place ) && ( $place->getFullAdress() == null || $place->getFullAdress() == "" ) ) {
            $this->em->remove($place);
            $teamSportSession->setPlace(null);
        }
        
        $this->em->persist($teamSportSession);
        $this->em->flush();
        
        foreach($teamSportSession->getScores() as $scores)  
        {
            $scores->setActivity($teamSportSession);
            $this->em->persist($scores);
        }
        $this->em->flush();

        if( isset ( $parameters["photosToAdd"] ) && ! empty( $parameters["photosToAdd"] ) ) {
            \Form\FormValidator::processingForActivityAddingPhoto( $teamSportSession, $parameters["photosToAdd"], $this->em );
        }
        
        if( isset ( $parameters["photosToDelete"] ) && ! empty( $parameters["photosToDelete"] ) ) {
            \Form\FormValidator::processingForActivityDeletingPhoto( $teamSportSession, $parameters["photosToDelete"], $this->em );
        }
        
        //Si changement de sport
        if( isset ( $parameters["changeSport"] ) && ! empty( $parameters["changeSport"] ) ) {
            $teamSportSession->setSport($this->em->getRepository('KsActivityBundle:Sport')->find($parameters["changeSport"]));
        }
        
        $this->em->persist($teamSportSession);
        $this->em->flush();
        
        //FIXME : FMO soucis lien entre event et activity...
        $this->em->getRepository('KsEventBundle:Event')->updateOddLinks($teamSportSession->getId());
            
        //Si rattachement à une séance d'un plan d'entrainement
        if( isset ( $parameters["coachingPlanId"] ) && ! empty( $parameters["coachingPlanId"] ) ) {
            
            $event = $teamSportSession->getEvent();
            $club = $event->getClub();
            
            $event->setActivitySession($teamSportSession);
            $this->em->persist($event);
            $this->em->flush();
                
            $teamSportSession->setIsPublic($event->getIsPublic());
            
            if ($club != null) {
                $teamSportSession->setClub($club);
                
                //Envoi notif + mail au coach pour qu'il prenne connaissance de l'activité postée par l'utilisateur
                //Pas d'envoi de mail si user qui s'est fait son propre plan
                $user = $teamSportSession->getUser();
                $notificationService   = $this->container->get('ks_notification.notificationService');
                $message = $user->__toString() . " " . $this->translator->trans('coaching.mail-activity-done');
                
                foreach($club->getManagers() as $clubHasManagers) {
                    $manager = $clubHasManagers->getUser();
                    //var_dump($manager->getId());
                    $notificationService->sendNotification($teamSportSession, $user, $manager, 'coaching', $message, $club);
                }
            }
        }
        
        $this->em->persist($teamSportSession);
        $this->em->flush();
        
        //FIXME : FMO soucis lien entre event et activity...
        $this->em->getRepository('KsEventBundle:Event')->deleteOddLinks();

        $responseDatas = array(
            'publishResponse' => 1,
            'teamSportSession' => $teamSportSession
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
