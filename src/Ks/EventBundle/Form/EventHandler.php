<?php 
namespace Ks\EventBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;

use Ks\EventBundle\Entity\Event;

class EventHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $container;
    protected $translator;


    public function __construct(Form $form, Request $request = null, EntityManager $em, Container $container, $translator =null)
    {
        $this->form         = $form;
        $this->request      = $request;
        $this->em           = $em; 
        $this->container    = $container;
        $this->translator   = $translator;
    }

    public function process()
    {
        $parameters       = $this->request->request->all();
            
        $this->form->bindRequest($this->request);

        if ( $this->form->isValid() ) {
            $responseDatas = $this->onSuccess($this->form->getData(), $parameters);  
        } else {
            $errors = \Form\FormValidator::getErrorMessages($this->form);
            $responseDatas = $this->onError($errors);   
        }
        
        return $responseDatas;
    }
    
    public function onSuccess(Event $event, $parameters)
    {
        $name = $event->getName();
        if( empty( $name ) ) {
            $event->setName(" ");
        }
        
        $activityRep = $this->em->getRepository('KsActivityBundle:Activity');
        
        $hrCoeffMin  = isset( $parameters['hrCoeffMin'] ) && $parameters['hrCoeffMin'] != "null" ? $parameters['hrCoeffMin'] : null;
        $hrCoeffMax  = isset( $parameters['hrCoeffMax'] ) && $parameters['hrCoeffMax'] != "null" ? $parameters['hrCoeffMax'] : null;
        $hrType  = isset( $parameters['hrType'] ) && $parameters['hrType'] != "null" ? $parameters['hrType'] : null;
        $intervalDuration  = isset( $parameters['intervalDuration'] ) && $parameters['intervalDuration'] != "null" ? $parameters['intervalDuration'] : null;
        $intervalDistance  = isset( $parameters['intervalDistance'] ) && $parameters['intervalDistance'] != "null" ? $parameters['intervalDistance'] : null;
        $VMACoeff  = isset( $parameters['VMACoeff'] ) && $parameters['VMACoeff'] != "null" ? $parameters['VMACoeff'] : null;
        $durationMin  = isset( $parameters['durationMin'] ) && $parameters['durationMin'] != "null" ? $parameters['durationMin'] : null;
        $durationMax  = isset( $parameters['durationMax'] ) && $parameters['durationMax'] != "null" ? $parameters['durationMax'] : null;
        $distanceMin  = isset( $parameters['distanceMin'] ) && $parameters['distanceMin'] != "null" ? $parameters['distanceMin'] : null;
        $distanceMax  = isset( $parameters['distanceMax'] ) && $parameters['distanceMax'] != "null" ? $parameters['distanceMax'] : null;
        $elevationGainMin  = isset( $parameters['elevationGainMin'] ) && $parameters['elevationGainMin'] != "null" ? $parameters['elevationGainMin'] : null;
        $elevationGainMax  = isset( $parameters['elevationGainMax'] ) && $parameters['elevationGainMax'] != "null" ? $parameters['elevationGainMax'] : null;
        $elevationLostMin  = isset( $parameters['elevationLostMin'] ) && $parameters['elevationLostMin'] != "null" ? $parameters['elevationLostMin'] : null;
        $elevationLostMax  = isset( $parameters['elevationLostMax'] ) && $parameters['elevationLostMax'] != "null" ? $parameters['elevationLostMax'] : null;
        $speedAverageMin  = isset( $parameters['speedAverageMin'] ) && $parameters['speedAverageMin'] != "null" ? $parameters['speedAverageMin'] : null;
        $speedAverageMax  = isset( $parameters['speedAverageMax'] ) && $parameters['speedAverageMax'] != "null" ? $parameters['speedAverageMax'] : null;
        $powMin  = isset( $parameters['powMin'] ) && $parameters['powMin'] != "null" ? $parameters['powMin'] : null;
        $powMax  = isset( $parameters['powMax'] ) && $parameters['powMax'] != "null" ? $parameters['powMax'] : null;
        
        $event->setIsPublic($parameters["checkboxIsPublic"] == 'true' ? true : false);
        if (isset($parameters["competition"]) && $parameters["competition"] != "null") $event->setCompetition($activityRep->find($parameters["competition"]));
        
        $event->setHrCoeffMin($hrCoeffMin);
        $event->setHrCoeffMax($hrCoeffMax);
        $event->setHrType($hrType);
        if ($intervalDuration == null) $event->setIntervalDuration(null); else $event->setIntervalDuration(new \DateTime(date("H:i:s", strtotime($intervalDuration))));
        $event->setIntervalDistance($intervalDistance);
        $event->setVMACoeff($VMACoeff);
        if ($durationMin == null) $event->setDurationMin(null); else $event->setDurationMin(new \DateTime(date("H:i", strtotime($durationMin))));
        if ($durationMax == null) $event->setDurationMax(null); else $event->setDurationMax(new \DateTime(date("H:i", strtotime($durationMax))));
        $event->setDistanceMin($distanceMin);
        $event->setDistanceMax($distanceMax);
        $event->setElevationGainMin($elevationGainMin);
        $event->setElevationGainMax($elevationGainMax);
        $event->setElevationLostMin($elevationLostMin);
        $event->setElevationLostMax($elevationLostMax);
        $event->setSpeedAverageMin($speedAverageMin);
        $event->setSpeedAverageMax($speedAverageMax);
        $event->setPowMin($powMin);
        $event->setPowMax($powMax);
        
        $this->em->persist($event); 
        $this->em->flush();
        
        
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        /*
        if (isset($parameters["sendMail"]) && $parameters["sendMail"] != null) {
            //Gestion des envois auto de mail
            if ($parameters["sendMail"] == 'to_members') {
                //Cas de la modif d'un événement par un club, on envoie un mail à tous les participants de l'événement

                $notificationService   = $this->container->get('ks_notification.notificationService');
                $message = $user->__toString() . " " . $this->translator->trans('coaching.mail-update-event-to-members');

                foreach($event->getUsersParticipations() as $participant) {
                    $notificationService->sendNotification(null, $user, $participant, 'coaching', $message, $event->getClub(), $event);
                }
            }
            else if ($parameters["sendMail"] == 'to_club') {
                //Cas de la modif d'un événement par un des membres, on envoie un mail au club
                $clubRep   = $this->em->getRepository('KsClubBundle:Club');
                $notificationService   = $this->container->get('ks_notification.notificationService');
                $message = $user->__toString() . " " . $this->translator->trans('coaching.mail-update-event-to-club');
                $club = $event->getClub();

                foreach($clubRep->getManagersFromClub($club->getId()) as $coachId) {
                    $userRep   = $this->em->getRepository('KsUserBundle:User');
                    $coach = $userRep->find($coachId);

                    $notificationService->sendNotification(null, $user, $coach, 'coaching', $message, $club, $event);
                }
            }
        }
        */
        $responseDatas = array(
            'code' => 1,
            'event' => $event,
        ); 
        
        return $responseDatas;
    }
    
    public function onError($errors)
    {
        $responseDatas = array(
            'code'   => -1,
            'errorMessage'      => "Le formulaire n'est pas valide.",
            'errors'            => $errors
        ); 
        
        return $responseDatas;
    }
}