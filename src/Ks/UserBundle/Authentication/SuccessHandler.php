<?php
namespace Ks\UserBundle\Authentication;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\DoctrineBundle\Registry as Doctrine;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Symfony\Component\Routing\RouterInterface,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface,
    Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
* Custom login listener.
*/
class SuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /** @var \Symfony\Component\Security\Core\SecurityContext */
    private $context;

    /** @var \Doctrine\ORM\EntityManager */
    private $em;
    
    /** @var \Symfony\Component\Translation\Translator */
    private $_translator;
    
    /** @var \Ks\TrophyBundle\Service\TrophyService */
    private $trophyService;
    
    private $container;
            

    /**
    * Constructor
    *
    * @param SecurityContext $context
    * @param Doctrine $doctrine
    */
    public function __construct(
        SecurityContext $context,
        Doctrine $doctrine,
        \Symfony\Component\Translation\Translator $translator,
        \Ks\TrophyBundle\Service\TrophyService $trophyService,
        $container
    ) {
        $this->context          = $context;
        $this->em               = $doctrine->getEntityManager();
        $this->_translator      = $translator;
        $this->trophyService    = $trophyService;
        $this->container        = $container;
    }


    public function onAuthenticationSuccess(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token) {
        $user       = $this->context->getToken()->getUser();
        $jobPath    = $this->container->get('kernel')->getRootdir().'/jobs/';
        
        //----------------------------------------------------//
        // On demande à l'utilisateur de valider des sessions d'actvités 
        // de type (entrainements/compétitions) correspondant 
        // à des événements passés dans son calendrier
        //----------------------------------------------------//
        $agenda             = $user->getAgenda();
        $userRep            = $this->em->getRepository('KsUserBundle:User');
        $serviceRep         = $this->em->getRepository('KsUserBundle:Service');
        $userHasServicesRep = $this->em->getRepository('KsUserBundle:UserHasServices');
        
        //Requete pour récupérer uniquement les événements de type (entrainement ou compétition de luser courant)
        $userHasEvents = $userRep->getEventOfActivitySessionPassedAndIsInactive($agenda->getId());
        $nbActivityInStandBy = count($userHasEvents);
        if ($nbActivityInStandBy > 0 ){

            $notificationType = $this->em->getRepository('KsNotificationBundle:NotificationType')->findOneByName("validation_activity");
            if (!is_object($notificationType) ) {
                    $impossible_to_find_the_notification_type_validation_activity = $this->_translator->trans('impossible_to_find_the_notification_type_validation_activity');
                    throw new AccessDeniedException($impossible_to_find_the_notification_type_validation_activity);
            }
            //création d'une notification
            $notification = new \Ks\NotificationBundle\Entity\Notification();
            $notification->setCreatedAt(new \DateTime('now'));
            $notification->setGotAnAnswer(0);
            $sessioninstandbymsg = $this->_translator->trans('you-ve-got-%nbActivityInStandBy%-session-in-standby', array('%nbActivityInStandBy%' => $nbActivityInStandBy));
            $notification->setText($sessioninstandbymsg);
            $notification->setNeedAnAnswer(1);
            $notification->setOwner($user);
            $notification->setReadAt(new \DateTime('now'));
            $notification->setType($notificationType);
            $this->em->persist($notification);
            $this->em->flush();
        }

        $status = $this->em->getRepository('KsEventBundle:Status')->findOneByName("en-attente"); 
        if (!is_object($status)) {
            $statutStandByMsg = $this->_translator->trans('impossible_to_find_status_stand_by');
            throw new AccessDeniedException($statutStandByMsg);
        }
        $invitationEvents = $this->em->getRepository('KsEventBundle:InvitationEvent')->findBy(array("userInvited"=>$user->getId(),"status"=>$status->getId()));
        if ($invitationEvents != null) {
            $nbInvitations = count($invitationEvents);
            $notificationType = $this->em->getRepository('KsNotificationBundle:NotificationType')->findOneByName("invitation_event");
            if (!is_object($notificationType)) {
                $notificationInvitationEventMsg = $this->_translator->trans('impossible_to_find_notification_invitationevent');
                throw new AccessDeniedException($notificationInvitationEventMsg);
            }
            //création d'une notification
            $notification = new \Ks\NotificationBundle\Entity\Notification();
            $notification->setCreatedAt(new \DateTime('now'));
            $notification->setGotAnAnswer(0);
            $invationEventMsg = $this->_translator
                ->trans('you-ve-got-%$nbInvitations%-to-event', array('%$nbInvitations%' => $nbInvitations));
            $notification->setText($invationEventMsg);
            $notification->setNeedAnAnswer(1);
            $notification->setOwner($user);
            $notification->setReadAt(new \DateTime('now'));
            $notification->setType($notificationType);
            $this->em->persist($notification);
            $this->em->flush();
        }
        
        //$this->trophyService->beginOrContinueToWinTrophy("prem_connexion", $user);
        
        //Mise en attente de la synchronisation nike plus
        $nikePlusService = $serviceRep->findOneByName('NikePlus');
        if (is_object($nikePlusService) ) {
            $serviceId  = $nikePlusService->getId();
            $userId     = $user->getId();
            $userHasService = $userHasServicesRep->findOneBy(array(
                'service'   => $serviceId,
                'user'      => $userId,
                'is_active' => true
            ));

            if (is_object($userHasService) ) {
                $mailNike = $userHasService->getConnectionId();
                $mdpMail = base64_decode($userHasService->getConnectionPassword());

                if( $mailNike != null && $mdpMail != null ) {
                    if(!file_exists($jobPath.'pending/nikeplus/'.$userId."_".$serviceId.'.job')){
                        $file = fopen ($jobPath.'pending/nikeplus/'.$userId."_".$serviceId.'.job', "a+");
                        fwrite($file, "php ".$this->container->get('kernel')->getRootdir()."/console ks:activity:nikeplussync $userId $serviceId");
                        fclose($file);

                        //Mise a jour de userHasAService
                        $userHasService->setStatus("pending");
                        $this->em->persist($userHasService);
                        $this->em->flush();
                    }
                }
            }            
        } 
        
        //Mise en attente de la synchronisation runkeeper
        $runkeeperService = $serviceRep->findOneByName("Runkeeper");
        if (is_object($runkeeperService) ) {
            $userHasService = $userHasServicesRep->findOneBy(array(
                'service'   => $runkeeperService->getId(),
                'user'      => $user->getId(),
                'is_active' => true
            ));

            if (is_object($userHasService) ) {
                $accessToken = $userHasService->getToken();
                if(!empty($accessToken)){
                    if(!file_exists($jobPath.'pending/runkeeper/'.$accessToken.'.job')){
                        $file = fopen ($jobPath.'pending/runkeeper/'.$accessToken.'.job', "a+");
                        fwrite($file, "php ".$this->container->get('kernel')->getRootdir()."/console ks:user:runkeepersync $accessToken");
                        fclose($file);

                        //Mise a jour de userHasAService
                        $userHasService->setStatus("pending");
                        $this->em->persist($userHasService);
                        $this->em->flush();
                    }
                }
            }
        }
            
        //Mise en attente de la synchronisation endomondo
        $endomondoService = $serviceRep->findOneByName('Endomondo');
        if (is_object($endomondoService) ) {
            $userHasService = $userHasServicesRep->findOneBy(array(
                'service'   => $endomondoService->getId(),
                'user'      => $user->getId(),
                'is_active' => 1
            ));
            if (is_object($userHasService) ) {
                $accessToken = $userHasService->getToken();
                if (!empty($accessToken)){
                    if (!file_exists($jobPath.'pending/endomondo/'.$accessToken.'.job')){
                        $file = fopen ($jobPath.'pending/endomondo/'.$accessToken.'.job', "a+");
                        fwrite($file, "php ".$this->container->get('kernel')->getRootdir()."/console ks:user:endomondosync $accessToken");
                        fclose($file);

                        //Mise a jour de userHasAService
                        $userHasService->setStatus("pending");
                        $this->em->persist($userHasService);
                        $this->em->flush();
                    }
                }
            }
        }
        //$response =  new RedirectResponse($this->container->get('router')->generate('ksProfile_informations', array('creationOrEdition' => 'creation')));
        $response =  new RedirectResponse("www.keepinsport.com");
        throw new AccessDeniedException("test");
        //S'il n'est pas passé par la complétion du profil lors de l'inscription
        /*if( $user->getCompletedHisProfileRegistration() != true) {
            //throw new AccessDeniedException("test");
            $response =  new RedirectResponse($this->container->get('router')->generate('ksProfile_informations', array('creationOrEdition' => 'creation')));
        } else {
            $response =  new RedirectResponse($this->container->get('router')->generate('ksActivity_activitiesList'));
        }*/
        
        return $response;
    }
}