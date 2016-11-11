<?php
namespace Ks\NotificationBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class NotificationController extends Controller
{    
    /**
     * @Route("/{numPage}", name = "ksNotification_notificationsList" )
     */
    public function notificationsListAction($numPage)
    {
        $clubRep = $this->getDoctrine()->getEntityManager()->getRepository('KsClubBundle:Club');
        $clubHasUsersRep = $this->getDoctrine()->getEntityManager()->getRepository('KsClubBundle:ClubHasUsers');
        
        $repository = $this->getDoctrine()->getEntityManager()->getRepository('KsNotificationBundle:Notification');

        //On récupère l'utilisateur connecté
        $user = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if (!is_object($user)) {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }
        
        // On récupère le nombre total d'notifications
        $nb_notifications = $repository->getTotalNotificationsNumber($user);
        
        
        // On récupère les notifications qu'il faut grâce à findBy() :
        $notifications = $repository->findBy(
            array("owner" => $user->getId()),
            array('createdAt' => 'desc')
        );
        
        $isInClub = array();
        foreach ($notifications as $notification) {
            $club = $notification->getFromClub();
            $user = $notification->getFromUser();
            if (!is_null($club) && !is_null($user)) {
                $isInClub[$notification->getId()] = $clubHasUsersRep->isInClub($club, $user);
            }
        }
        
        //On indique que toutes les notifications ont été lu
        //$repository->setReadAll($user);

        return $this->render('KsNotificationBundle:Notification:notificationsList.html.twig', array(
            'notifications' => $notifications,
            'isInClub'      => $isInClub
        ));
    }
    
    /**
     * @Route("/notifs/read", name = "ksNotification_read", options={"expose"=true} )
     */
    public function readNotifications() {
        
        $request = $this->container->get('request');
        
        if ($request->isXmlHttpRequest()) {
            //On récupère les notifications passé en paramètres dans la requetes
            $newNotifsId = $request->request->get('newNotifs');
            
            //On récupère l'utilisateur connecté
            $user = $this->container->get('security.context')->getToken()->getUser();

            // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
            if( ! is_object($user) )
            {
                throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
            }
            
            // On récupère le repository
            $repository = $this->getDoctrine()
                        ->getEntityManager()
                        ->getRepository('KsNotificationBundle:Notification');
        
            //On indique que toutes les notifications ont été lu
            $result = $repository->setReadNotifications($user, $newNotifsId);
            
            $response = new Response(json_encode(array('nb_update' => $result)));
            
             //« Content-type » json pour le retour
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }
        
    }
    
    /**
     * @Route("/notification/{numNotification}", name = "ksNotification_showNotification" )
     */
    /*public function showNotificationAction($numNotification) {
        $em                 = $this->getDoctrine()->getEntityManager();
        $notificationRep    = $em->getRepository('KsNotificationBundle:Notification');

        //On récupère l'utilisateur connecté
        $notification = $notificationRep->find($numNotification);

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($notification) )
        {
            throw new AccessDeniedException('Impossible de trouver la notification ' . $numNotification .'.');
        }

        return $this->render('KsNotificationBundle:Notification:showNotification.html.twig', array(
            'notification' => $notification,
            'page'     => $numPage,    // On transmet à la vue la page courante,
            'nb_pages' => $nb_pages   // Et le nombre total de pages.
        ));
    }*/
    
    /**
     * @Route("/notifs/last/{context}", name = "ksNotification_notificationsBlockList" )
     */
    public function notificationsBlocListAction($context)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $notifService   = $this->get('ks_notification.notificationService');
        $notifRep       = $em->getRepository('KsNotificationBundle:Notification');
        $notifTypesRep  = $em->getRepository('KsNotificationBundle:NotificationType');
        $userRep        = $em->getRepository('KsUserBundle:User');
        
        //On récupère l'utilisateur connecté
        $user = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if (!is_object($user) )
        {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }
        
        $keepinsportUser               = $userRep->findOneByUsername("keepinsport");
        
        $new_notifications_number = $notifRep->getNumberWithoutNeedAnAnswer($user);
        
        // On récupère les notifications qu'il faut :
        $numberToGet = $new_notifications_number > 5 ? $new_notifications_number : 5;
        $notificationsTemp = $notifRep->findWithoutNeedAnAnswer($user, $numberToGet);
        
        $indexedNotifs = array();
        // On indexe les notification par type et par activité
        foreach( $notificationsTemp as $notification ) {
            $activityId =  $notification->getActivity() != null ? $notification->getActivity()->getId() : null;
            $indexedNotifs[$notification->getType()->getId()][$activityId][] = $notification;
        }
        
        //var_dump($indexedNotifs);
        //exit(0);
        
        $notifications = array();
        $mergedNotifications = array();
        $new_notifications_number = 0;
        foreach( $indexedNotifs as $typeId => $indexedNotifsByType ) {
            foreach( $indexedNotifsByType as $activityId => $indexedNotifsByActivity ) {
                if( count( $indexedNotifsByActivity ) > 0 ) {
                    $firstNotification = $indexedNotifsByActivity[0];
                    // Si l'activité n'est pas définie, il ne s'agit pas d'un vote, d'un commentaire, ect (badge, ligues, etc)
                    //Donc on ne merge pas
                    if ($activityId != null && $firstNotification->getPossibleMerge()) { //
                        $notificationTemp = new \Ks\NotificationBundle\Entity\Notification();
                        $notificationTemp->setOwner( $firstNotification->getOwner() );
                        $notificationTemp->setType( $firstNotification->getType() );
                        $notificationTemp->setIsRead( false );
                        $notificationTemp->setIsMerged( true );
                        
                        $fromUsers = array();
                        foreach( $indexedNotifsByActivity as $notification ) {
                            if( !$notification->getIsRead() ) {
                                if( !isset($lastDate) || empty($lastDate) || $notification->getCreatedAt() > $lastDate ) {
                                    $lastDate = $notification->getCreatedAt();
                                }
                                
                                $fromUserInArray = false;
                                foreach( $fromUsers as $fromUser ) {
                                    if( $fromUser->getId() == $notification->getFromUser()->getId()) {
                                        $fromUserInArray = true;
                                        break;
                                    }
                                }
                                
                                if( !$fromUserInArray ) {
                                    $fromUsers[] = $notification->getFromUser();
                                }
                                //if( !in_array($notification->getFromUser(), $fromUsers) ) {
                                    
                                //}
                                $notificationTemp->addNotification( $notification );
                            } else {
                                //Si la notification est déjà lue, on ne la fusionne pas
                                $notifications[] = $notification;
                            }
                        }
                        
                        if( count( $fromUsers ) > 0 ) {
                            if( count( $fromUsers ) > 1 ) {
                                $rand = array_rand( $fromUsers, 1);
                                $fromUser = $fromUsers[$rand];
                            } else {
                                $fromUser = $fromUsers[0];
                            }
                            
                            $notificationTemp->setFromUser($fromUser);
                            
                            $message = $notifService->generateMessage( $firstNotification->getActivity(), $fromUsers, $firstNotification->getOwner(), $firstNotification->getType()->getName() );
                            $notificationTemp->setText( $message );
                            //$notificationTemp->setText( count($indexedNotifsByActivity) . ":" . $firstNotification->getType()->getName().":".$firstNotification->getActivity()->getType() );
                            $notificationTemp->setActivity( $firstNotification->getActivity() );
                            $notificationTemp->setCreatedAt( $lastDate );
                            $lastDate = "";
                            $mergedNotifications[] = $notificationTemp;
                            $new_notifications_number += 1;
                        }
                        //
                    } else {
                        foreach( $indexedNotifsByActivity as $notification ) {
                            $notifications[] = $notification;
                            if( !$notification->getIsRead() ) {
                                $new_notifications_number += 1;
                            }
                        }
                    }
                } /*elseif ( count( $indexedNotifsByActivity == 1 )) {
                    $notification = $indexedNotifsByActivity[0];
                    $notifications[] = $notification;
                    if( !$notification->getIsRead() ) {
                        $new_notifications_number += 1;
                    }
                }*/
            }
         }
         
         $notifications = array_merge($mergedNotifications, $notifications);
         
        
        //$new_notifications_number = $notifRep->getNumberWithoutNeedAnAnswer($user);
        
        $notificationType_name = "ask_friend_request";
        $notificationType_afk = $notifTypesRep->findOneByName($notificationType_name);

        if ($notificationType_afk) {
            $askFriendsRequests = $notifRep->getNewNeedAnAnswerNotifications($user, $notificationType_afk);
            $new_notifications_number += count($askFriendsRequests);
            $notifications = array_merge($askFriendsRequests, $notifications);
        }
        
        $notificationType_name = "mustBeValidated";
        $notificationType_mbv = $notifTypesRep->findOneByName($notificationType_name);

        if ($notificationType_mbv) {
            $activitiesMustBeValidated = $notifRep->getNewNeedAnAnswerNotifications($user, $notificationType_mbv);
            $new_notifications_number += count($activitiesMustBeValidated);
            $notifications = array_merge($activitiesMustBeValidated, $notifications);
        }
        
        $notificationType_name = "mustBeValidatedEvent";
        $notificationType_mbve = $notifTypesRep->findOneByName($notificationType_name);

        if ($notificationType_mbve) {
            $activitiesMustBeValidated = $notifRep->getNewNeedAnAnswerNotifications($user, $notificationType_mbve);
            $new_notifications_number += count($activitiesMustBeValidated);
            $notifications = array_merge($activitiesMustBeValidated, $notifications);
        }
        
        $notificationType_name = "userHasSportFrequency";
        $notificationType_uhsf = $notifTypesRep->findOneByName($notificationType_name);

        if ($notificationType_uhsf) {
            $activitiesFromUserHasSportFrequency = $notifRep->getNewNeedAnAnswerNotifications($user, $notificationType_uhsf);
            $new_notifications_number += count($activitiesFromUserHasSportFrequency);
            $notifications = array_merge($activitiesFromUserHasSportFrequency, $notifications);
        }
        
        usort($notifications, array($this, 'sortNotifications'));
        

        return $this->render('KsNotificationBundle:Notification:notificationsBlockList.html.twig', array(
            'notifications'             => $notifications,
            'new_notifications_number'  => $new_notifications_number,
            'context'                   => $context
        ));
    }
    
    private function sortNotifications($notif1, $notif2)
    {
        $aPriorityTypes = array(
            "mustBeValidatedEvent" => 3,
            "mustBeValidated" => 2,
            "ask_friend_request" => 1
        );
        
        //On trie par priorité
        if( in_array( $notif1->getType()->getName(), array_keys( $aPriorityTypes ) ) && in_array( $notif2->getType()->getName(), array_keys( $aPriorityTypes ) ) ) {
            return $aPriorityTypes[$notif1->getType()->getName()] < $aPriorityTypes[$notif2->getType()->getName()];
        } 
        elseif( in_array( $notif1->getType()->getName(), array_keys( $aPriorityTypes )  ) ) {
            return -1;
        }
        elseif( in_array( $notif2->getType()->getName(), array_keys( $aPriorityTypes ) ) ) {
            return 1;
        }
        //On tri par date s'il n'y a pas de priorité
        else {
            if ( $notif1->getCreatedAt() > $notif2->getCreatedAt() ) {
                return -1;
            }
            elseif ($notif1->getCreatedAt() == $notif2->getCreatedAt()) {
                return 0;
            } else {
                return 1;
            }
        }
    }
    
    /**
     * @Route("/requests_friends/last", name = "ksNotification_friendRequestsBlockList" )
     */
    public function friendRequestsBlocListAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $repository = $em->getRepository('KsNotificationBundle:Notification');
        
        //On récupère l'utilisateur connecté
        $user = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($user) )
        {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }
        
        $notificationType_name = "ask_friend_request";
        $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName($notificationType_name);

        if (!$notificationType) {
            throw $this->createNotFoundException('Impossible de trouver le type de notification ' . $notificationType_name . '.');
        } 
        
        $new_notifications_number = $repository->getNewRequestsFriendNumber($user, $notificationType);
                
        // On récupère les notifications qu'il faut grâce à findBy() :
        $notifications = $repository->findBy(
            array(
                "owner"         => $user->getId(),
                "type"          => $notificationType->getId(),
                "gotAnAnswer"   => 0
            ),
            array('createdAt' => 'desc'),
            10
        );
        
        //On indique que toutes les notifications ont été lu
        //$repository->setReadAll($user);

        return $this->render('KsNotificationBundle:Notification:friendRequestsBlockList.html.twig', array(
            'notifications'             => $notifications,
            'new_notifications_number'  => $new_notifications_number
        ));
    }
    
    /**
     *
     * @Route("/validateActivity/{activityId}_{notificationId}", name="ksNotification_validateActivity", options={"expose"=true})
     */
    public function validateActivityAction ($activityId, $notificationId) {
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $notificationRep    = $em->getRepository('KsNotificationBundle:Notification');
        $user               = $this->get('security.context')->getToken()->getUser();
        
        $request = $this->container->get('request');
       
        
        $responseDatas = array();
        
        $activity = $activityRep->find($activityId);
        
        if (!is_object($activity) ) {
            throw $this->createNotFoundException("Impossible de trouver l'activité " . $activityId .".");
        }
        
        $notification = $notificationRep->find($notificationId);
        
        if (!is_object($notification) ) {
            throw $this->createNotFoundException("Impossible de trouver la notification " . $notificationId .".");
        }
        
        $activityRep->validateActivity($activity, $notification);
        
        if (!$request->isXmlHttpRequest() ) {
            //on redirige sur l'activité
            $this->get('session')->setFlash('alert alert-info', "Ton activité a été validée avec succès ! Tu peux modifier certaines données si tu le souhaites via le bouton \"Editer\" tout en bas (en forme de crayon)");
            return new RedirectResponse($this->generateUrl('ksActivity_showActivity', array("activityId" => $activityId)));
        }
        $responseDatas["validateResponse"] = 1;
        
        //return new RedirectResponse($this->generateUrl('ksActivity_showActivity', array("activityId" => $activity->getId())));
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;  
    }
    
    
    /**
     *
     * @Route("/unvalidateActivity/{activityId}_{notificationId}", name="ksNotification_unvalidateActivity", options={"expose"=true})
     */
    public function unvalidateActivityAction ($activityId, $notificationId) {
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $notificationRep    = $em->getRepository('KsNotificationBundle:Notification');
        
        $activity = $activityRep->find($activityId);
        
        if (!is_object($activity) ) {
            throw $this->createNotFoundException("Impossible de trouver l'activité " . $activityId .".");
        }
        
        $notification = $notificationRep->find($notificationId);
        
        if (!is_object($notification) ) {
            throw $this->createNotFoundException("Impossible de trouver la notification " . $notificationId .".");
        }
        
        $activityRep->unvalidateActivity($activity, $notification);
        
        //on redirige sur l'activité
            $this->get('session')->setFlash('alert alert-info', "Ton activité n'a pas été validée comme tu l'as souhaité !");
        return new RedirectResponse($this->generateUrl('ksActivity_activitiesList'));
    }

}
