<?php
namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

class ActivitySessionHandler
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
            $activitySession = $this->form->getData();
            $request          = $this->request;
            $parameters       = $request->request->all();
            

            $this->form->bindRequest($request);

            $this->frequencyForm->bindRequest($this->request);
            $this->em->persist($this->frequencyForm->getData());
            $this->em->flush();
            
            if ( $this->form->isValid() ) {
//                if(isset($parameters["eventId"]) && $parameters["eventId"] != "NC" && $parameters["eventId"] != -1) {
//                    $event = $this->em->getRepository('KsEventBundle:Event')->find($parameters["eventId"]); 
//                    if (!is_object($event) ) {
//                        throw $this->createNotFoundException("Impossible de trouver l'evènement " . $eventId .".");
//                    }
//                    
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
//                    $notification->setActivity($activitySession);
//
//                    $activitySession->setEvent($event);
//                }
                
                $responseDatas = $this->onSuccess($activitySession, $parameters);  
                
            } else {
                $errors = \Form\FormValidator::getErrorMessages($this->form);
                $responseDatas = $this->onError($errors);  
            }
        } else {
            $responseDatas = $this->onIsNotXmlHttpRequest(); 
        }

        return $responseDatas;
    }

    /**
     * 
     * @param \Ks\ActivityBundle\Entity\ActivitySessionEndurance $activitySession
     * @param type $parameters
     * @return int
     */
    public function onSuccess(\Ks\ActivityBundle\Entity\ActivitySession $activitySession, $parameters)
    {
        $tDuration          = $activitySession->getDuration(); // FIMXE: temp, le temps de basculer définitivement en secondes...
        $tDate              = $tDuration->format('H:i:s');
        list($h, $m, $s)    = explode(':', $tDate);
        $activitySession->setTimeMoving(
            $h * 3600 + $m * 60 + $s
        );
        
        if ( $activitySession->getWasOfficial() == 1 ) {
            $activitySession->setWasOfficial(true);
        } else {
            $activitySession->setWasOfficial(false);
        }
        
        $place = $activitySession->getPlace();
        if ( is_object( $place ) && ( $place->getFullAdress() == null || $place->getFullAdress() == "" ) ) {
            $this->em->remove($place);
            $activitySession->setPlace(null);
        } 
        
        $this->em->persist($activitySession);
        $this->em->flush();
         
        if( isset ( $parameters["photosToAdd"] ) && ! empty( $parameters["photosToAdd"] ) ) {
            \Form\FormValidator::processingForActivityAddingPhoto( $activitySession, $parameters["photosToAdd"], $this->em );
        }
        
        if( isset ( $parameters["photosToDelete"] ) && ! empty( $parameters["photosToDelete"] ) ) {
            \Form\FormValidator::processingForActivityDeletingPhoto( $activitySession, $parameters["photosToDelete"], $this->em );
        }
        
        //Si changement de sport
        if( isset ( $parameters["changeSport"] ) && ! empty( $parameters["changeSport"] ) ) {
            $activitySession->setSport($this->em->getRepository('KsActivityBundle:Sport')->find($parameters["changeSport"]));
        }
        
        $this->em->persist($activitySession);
        $this->em->flush();
        
        //FIXME : FMO soucis lien entre event et activity...
        $this->em->getRepository('KsEventBundle:Event')->updateOddLinks($activitySession->getId());
        
        //Si rattachement à une séance d'un plan d'entrainement
        if( isset ( $parameters["coachingPlanId"] ) && ! empty( $parameters["coachingPlanId"] ) ) {
            
            $event = $activitySession->getEvent();
            $club = $event->getClub();
            
            $event->setActivitySession($activitySession);
            $this->em->persist($event);
            $this->em->flush();
            
            $activitySession->setIsPublic($event->getIsPublic());
                
            if ($club != null) {
                $activitySession->setClub($club);
                
                //Envoi notif + mail au coach pour qu'il prenne connaissance de l'activité postée par l'utilisateur
                //Pas d'envoi de mail si user qui s'est fait son propre plan
                $user = $activitySession->getUser();
                $notificationService   = $this->container->get('ks_notification.notificationService');
                $message = $user->__toString() . " " . $this->translator->trans('coaching.mail-activity-done');
                
                foreach($club->getManagers() as $clubHasManagers) {
                    $manager = $clubHasManagers->getUser();
                    //var_dump($manager->getId());
                    $notificationService->sendNotification($activitySession, $user, $manager, 'coaching', $message, $club);
                }
            }
        }
        
        $this->em->persist($activitySession);
        $this->em->flush();
        
        //FIXME : FMO soucis lien entre event et activity...
        $this->em->getRepository('KsEventBundle:Event')->deleteOddLinks();

        $responseDatas = array(
            'publishResponse' => 1,
            'activitySession' => $activitySession
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
    
    public function getFullUrl() {
      	return
    		(isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
    		(isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
    		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
    		(isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] === 443 ||
    		$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
    		substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }
}
