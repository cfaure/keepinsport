<?php

namespace Ks\NotificationBundle\Service;

use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ks\NotificationBundle\Notification;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @author Ced
 */
class NotificationService
    extends \Twig_Extension
{

    protected $doctrine;
    protected $container;
    protected $translator;
    
    /**
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine, $container, $translator =null)
    {
        $this->doctrine            = $doctrine;
        $this->container           = $container;
        $this->translator           = $translator;
    }
    
    // La méthode getName(), obligatoire
    public function getName()
    {
        return 'NotificationService';
    }
	
    // La méthode getFunctions(), qui retourne un tableau avec les fonctions qui peuvent être appelées depuis cette extension
    public function getFunctions()
    {
        return array(
            'sendNotification' => new \Twig_Function_Method($this, 'sendNotification'),
        );
    }
        
    public function sendNotification($activity, $fromUser, $toUser, $notificationType_name, $text = "", $club=null, $event=null)
    {
        
        // TODO: comment on gère notifications pour les activités sur les clubs
        if ($toUser == null) {
            return null;
        }
        
        $em                     = $this->doctrine->getEntityManager();
        $notificationRep        = $em->getRepository('KsNotificationBundle:Notification');
        $notificationTypeRep    = $em->getRepository('KsNotificationBundle:NotificationType');
        $activityRep            = $em->getRepository('KsActivityBundle:Activity');
        
        $isSubscribe = true;
           
        $notificationType = $notificationTypeRep->findOneByName($notificationType_name);

        if ($notificationType) {
            
            if (is_object($activity) && is_object($toUser)) { // FIXME: ce serait mieux d'utiliser is_a ...
                if( empty( $text ) ) {
                    $text = $this->generateMessage( $activity, array( $fromUser ), $toUser, $notificationType_name );
                }

                //S'il n'est pas abonné et qu'il s'est désabonné
                if ($activityRep->isNotSubscribed( $activity, $toUser) || ! $activityRep->hasNotUnsubscribed( $activity, $toUser)) {
                    $isSubscribe = false;
                }
            }

            if ($notificationType_name == 'coaching') {
                //Cas particulier de l'envoi de mail d'un membre d'un club qui a réalisé une activité d'un plan d'un club (isSubscribe vaut false ici)
                $isSubscribe = true;
                
                //Recherche d'une précédente notification (cas ou le sportif modifie une des données de son activité qui a déjà généré une notif
                $notification = $notificationRep->findBy(
                    array(
                        "activity"  => $activity->getId(),
                        "fromUser"  => $fromUser->getId(),
                        "owner"     => $toUser->getId(),
                        "type"      => $notificationType->getId()
                    )
                );
                if( is_array($notification) && isset( $notification[0] ) && is_object( $notification[0] ) ) {
                    $text = $fromUser->__toString() . " " . $this->translator->trans('coaching.mail-activity-update');
                }
            }
                    
            if ($isSubscribe) {
                $notificationsTypesWhichNeedAnAnswer = array(
                    "mustBeValidated",
                    "ask_friend_request",
                    "mustBeValidatedEvent",
                    "userHasSportFrequency"
                );

                $needAnAnswer = false;
                if( in_array($notificationType_name, $notificationsTypesWhichNeedAnAnswer ) ) {
                    $needAnAnswer = true;
                }
                
                $notification = $notificationRep->createNotification(
                    $toUser,
                    $fromUser,
                    $notificationType,
                    $text,
                    $activity,
                    $needAnAnswer,
                    $club,
                    $event
                );

                
                $this->sendMailNotification( $notification ); 

                return $notification;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
    
    /**
     *
     * @param \Ks\ClubBundle\Entity\Club $fromClub
     * @param \Ks\UserBundle\Entity\User $toUser
     * @param type $notificationType_name
     * @param type $message
     * @param \Ks\ClubBundle\Entity\TeamComposition $teamComposition
     * @return null 
     */
    public function sendClubNotification(\Ks\ClubBundle\Entity\Club $fromClub, \Ks\UserBundle\Entity\User $toUser, $notificationType_name, $message = "", \Ks\ClubBundle\Entity\TeamComposition $teamComposition = null, \Ks\UserBundle\Entity\User $fromUser = null) {
        $em                     = $this->doctrine->getEntityManager();
        $notificationRep        = $em->getRepository('KsNotificationBundle:Notification');
        $notificationTypeRep    = $em->getRepository('KsNotificationBundle:NotificationType');
           
        $notificationType = $notificationTypeRep->findOneByName($notificationType_name);

        if ($notificationType) {

            $notification = $notificationRep->createClubNotification(
                $fromClub,
                $toUser,
                $notificationType,
                $message,
                $teamComposition,
                $fromUser
            );

            $this->sendMailNotification( $notification );

            return $notification;
        } else {
            return null;
        }

    }
    
    /**
     *
     * @param type $activity
     * @param type $fromUsers
     * @param type $toUser
     * @param type $notificationType_name
     * @return string 
     */
    public function generateMessage( $activity, $fromUsers, $toUser, $notificationType_name )
    {
        $em                     = $this->doctrine->getEntityManager();
        $modificationsRep       = $em->getRepository('KsActivityBundle:UserModifiesArticle');
        
        $message = "";
        
        switch( $activity->getType() ) {
            case "article":
                $prefix = "son";
                $ownPrefix = $prefix . " propre";
                $what = "article";
                $whatWithPrefix = "l'" . $what;
                $whatWithArticle = "ton " . $what;
                break;

            case "status":
                $prefix = "son";
                $ownPrefix = $prefix . " propre";
                $what = "statut";
                $whatWithPrefix = "le " . $what;
                $whatWithArticle = "ton " . $what;
                break;

            case "link":
                $prefix = "son";
                $ownPrefix = $prefix . " propre";
                $what = "lien";
                $whatWithPrefix = "le " . $what;
                $whatWithArticle = "ton " . $what;
                break;

            case "video":
                $prefix = "sa";
                $ownPrefix = $prefix . " propre";
                $what = "vidéo";
                $whatWithPrefix = "la " . $what;
                $whatWithArticle = "ta " . $what;
                break;

            case "photo":
                $prefix = "sa";
                $what = "photo";
                $ownPrefix = $prefix . " propre";
                $whatWithPrefix = "la " . $what;
                $whatWithArticle = "ta " . $what;
                break;
            
            case "photo_album":
                $prefix = "son";
                $ownPrefix = $prefix . " propre";
                $what = "album photo";
                $whatWithPrefix = "l'" . $what;
                $whatWithArticle = "ton " . $what;
                break;

            case "session_endurance_on_earth":
            case "session_endurance_under_water":
            case "session_team_sport":
                $prefix = "son";
                $ownPrefix = "sa propre";
                $what = "activité de ". $this->translator->trans('sports.' . $activity->getSport()->getCodeSport());
                $whatWithPrefix = "l'" . $what;
                $whatWithArticle = "ton " . $what;
                break;

            default:
                $prefix = "son";
                $ownPrefix = "sa propre";
                $what = "actualité";
                $whatWithPrefix = "l'" . $what;
                $whatWithArticle = "ton " . $what;
                break;
        }

        switch( $notificationType_name ) {
            case "comment":
                $action = "commenté";
                break;

            case "vote":
                $action = "encouragé";
                break;

            case "share":
                $action = "partagé";
                break;
            
            case "warning":
                $action = "signalé";
                break;
        }
        
        $nbUsers = count($fromUsers);
        if( $nbUsers == 1 ) {
            $fromUser = $fromUsers[0];
            $from = $fromUser->getUsername() . " a ";
        } else {
            $from = $nbUsers . " utilisateurs ont ";
        }
            
        if( $activity->getType() == "article" ) {
            $lastArticleModifications = $modificationsRep->getLastModification($activity);
            if ( ! empty($lastArticleModifications)) {
                $articleContent = json_decode($lastArticleModifications->getContent(), true);
                if( isset( $articleContent["title"] )) {
                    $articleTitle = base64_decode($articleContent["title"]);
                } else {
                    $articleTitle = $activity->getLabel();
                }
            } else {
                $articleTitle = $activity->getLabel();
            }
            $message = $from . $action . " " . $whatWithPrefix . " " . $articleTitle;
        } else {
            if ( isset( $fromUser ) && $fromUser == $activity->getUser() ) {
                $message = $from . $action . " " . $ownPrefix . " " . $what;
            }
            elseif ( $toUser == $activity->getUser() ) {
                $message = $from . $action . " " . $whatWithArticle;
            } else {
                $username ='-';
                if ($activity->getUser()!= null) {
                    $username = $activity->getUser()->getUsername();
                }
                elseif ($activity->getClub() != null) {
                    $username = $activity->getClub()->getName();
                }
                $message = $from . $action . " " . $whatWithPrefix ." de " . $username;
            }
        }
 
        return $message;
    }
    
    
    public function sendNotificationEvent($event, $fromUser, $toUser, $notificationType_name, $message = "", $createdAt = null, $readAt = null) {
        $em                     = $this->doctrine->getEntityManager();
        $notificationRep        = $em->getRepository('KsNotificationBundle:Notification');
        $notificationTypeRep    = $em->getRepository('KsNotificationBundle:NotificationType');
        
        $notificationType = $notificationTypeRep->findOneByName($notificationType_name);
        if(isset($notificationType)){
            $notificationsTypesWhichNeedAnAnswer = array(
                "mustBeValidatedEvent",
            );

            $needAnAnswer = false;
            if( in_array($notificationType_name, $notificationsTypesWhichNeedAnAnswer ) ) {
                $needAnAnswer = true;
            }

            $notification = $notificationRep->createNotificationEvent(
                $toUser,
                $fromUser,
                $notificationType,
                $message,
                $event,
                $needAnAnswer,
                $createdAt,
                $readAt    
            );

            return $notification;         
        }
    } 
    
    public function sendMailNotification( \Ks\NotificationBundle\Entity\Notification $notification )
    {
        $em                     = $this->doctrine->getEntityManager();
        $commentRep             = $em->getRepository('KsActivityBundle:Comment');
        $mailNotificationsRep   = $em->getRepository('KsNotificationBundle:UserReceivesMailNotifications');
        $host                   = $this->container->getParameter('host');
        $pathWeb                = $this->container->getParameter('path_web');
        $isSendingMailsEnabled  = (boolean)$this->container->getParameter('send_mails');
        $mailer                 = $this->container->get('mailer');
        
        $this->container->enterScope('request');
        $this->container->set('request', new Request(), 'request');
        
        //Si l'envoi de mails est activé
        if( $isSendingMailsEnabled ) {
            //Si l'utilisateur est abonné par mail à ce type de notifications, on lui envoi
            $userReceivesMailNotifications = $mailNotificationsRep->findOneBy( 
                array(
                    "user" => $notification->getOwner()->getId(),
                    "type" => $notification->getType()->getId()
                )
            );

            if( is_object( $userReceivesMailNotifications ) ) {
                //L'utilisateur souhaite recevoir cette notification
                if( $userReceivesMailNotifications->getWantsReceive() ) {
                    if( $notification->getType()->getName() == "comment" ) {
                        //On récupère le dernier commentaire
                        $lastComments = $commentRep->findBy(
                            array(
                                "activity"  => $notification->getActivity()->getId(),
                                "user"      => $notification->getFromUser()->getId()
                            ),
                            array('id' => 'desc', 'commentedAt' => 'desc')
                        );

                        if( is_array($lastComments) && isset( $lastComments[0] ) && is_object( $lastComments[0] ) ) {
                            $contentMail = $this->container->get('templating')->render('KsNotificationBundle:Notification:_notification_mail.html.twig', 
                                array(
                                    'notification'          => $notification,
                                    'host'                  => $host,
                                    "pathWeb"               => $pathWeb,
                                    "lastComment"           => $lastComments[0]
                                ), 
                            'text/html');
                        }
                    }

                    if( !isset( $contentMail ) ) {
                        $contentMail = $this->container->get('templating')->render('KsNotificationBundle:Notification:_notification_mail.html.twig', 
                            array(
                                'notification'          => $notification,
                                'host'                  => $host,
                                "pathWeb"               => $pathWeb
                            ), 
                        'text/html');
                    }

                    $body = $this->container->get('templating')->render('KsUserBundle:User:template_mail.html.twig', 
                        array(
                            'host'      => $host,
                            'pathWeb'   => $pathWeb,
                            'content'   => $contentMail,
                            'user'      => $notification->getOwner()
                        ), 
                    'text/html');
                    
                    if( $notification->getType()->getName() == "league" ) {
                        $subject = "Fin de saison";
                    }
                    else if( $notification->getType()->getName() == "setPack" ) {
                        $subject = "Félicitations !";
                    }
                    else {
                        $subject = "" . $notification->getText();
                    }
                    
                    //$mail = \Ks\MailBundle\Entity\Mail::newInstance();
                    $message = \Swift_Message::newInstance()
                                    ->setContentType('text/html')
                                    ->setSubject( $subject )
                                    ->setFrom("contact@keepinsport.com")
                                    ->setTo( $notification->getOwner()->getEmail() )
                                    ->setBody( $body ); //$notification->getText()."<br><a href=''>Clique ici pour plus de details</a>"

                    try {
                        $mailer->getTransport()->start();
                        $mailer->send($message);
                        $mailer->getTransport()->stop();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }
    }
    
    public function sendMessageNotifications(\Ks\MessageBundle\Entity\Message $message, $fromUser)
    {
               
        $em                     = $this->doctrine->getEntityManager();
        $notificationRep        = $em->getRepository('KsNotificationBundle:Notification');
        $notificationTypeRep    = $em->getRepository('KsNotificationBundle:NotificationType');
           
        $notificationType = $notificationTypeRep->findOneByName("message");

        if ($notificationType) {   
            $text = $fromUser->getUsername() ." t'a envoyé un message.";

            $firstMessage = is_object( $message->getPreviousMessage() ) ? $message->getPreviousMessage() : $message;
            /*if( $firstMessage->getFromUser()->getId() != $fromUser->getId()) {
                $notification = $notificationRep->createMessageNotification(
                        $firstMessage->getFromUser(),
                        $fromUser,
                        $notificationType,
                        $firstMessage,
                        $text
                    );
            }*/
            foreach( $firstMessage->getToUsers() as $toUser ) {
                if( $toUser->getId() != $fromUser->getId()) {
                    $notification = $notificationRep->createMessageNotification(
                        $toUser,
                        $fromUser,
                        $notificationType,
                        $firstMessage,
                        $text
                    );
                    
                    $this->sendMailNotification( $notification );
                }
            }

            return true;
        } else {
            return false;
        }
    }
    
    /**
     *
     * @param type $user
     * @param type $date
     * @return boolean 
     */
    public function sendActivitiesDailyMail( $user )
    {
        $em                     = $this->doctrine->getEntityManager();
        $activityRep            = $em->getRepository('KsActivityBundle:Activity');
        $userRep                = $em->getRepository('KsUserBundle:User');
        
        $userDetail = $user->getUserDetail();
        
         $date = date('Y-m-d');
         $dateUs = date("Y-m-d", strtotime($date ." -1 day"));
         $dateFr = date("d/m/Y", strtotime($date ." -1 day"));
        
        
        //Seul le cas où il a configuré sur "non" empeche l'envoi du mail
        if ( ($userDetail != null && $userDetail->getReceivesDailyEmail()) || $userDetail == null ) {
            
            $host       = $this->container->getParameter('host');
            $pathWeb    = $this->container->getParameter('path_web');
            $mailer     = $this->container->get('mailer');
            
            $isManagerFromAClub = $userRep->isManagerFromAClub($user->getId());
            
            $activities = $activityRep->findActivities(array(
                'user'              => $user,
                'activitiesFrom'    => $isManagerFromAClub > 0 ? array("my_athletes") : array("my_friends"),
                'date'              => $dateUs,
                'activitiesTypes'   => array('session')
            ));
            
            //var_dump($activities);exit;
            
            if (count($activities) > 0) {
                $contentMail = $this->container
                    ->get('templating')
                    ->render(
                        'KsActivityBundle:Activity:_activities_mail.html.twig',
                        array(
                            'user'          => $user,
                            'activities'    => $activities,
                            'host'          => $host,
                            'pathWeb'       => $pathWeb
                        ),
                        'text/html'
                    );
                
                $body = $this->container->get('templating')->render('KsUserBundle:User:template_mail.html.twig', 
                    array(
                        'host'      => $host,
                        'pathWeb'   => $pathWeb,
                        'content'   => $contentMail,
                        'user'      => is_object( $user ) ? $user : null
                    ), 
                'text/html');

                $subject = $isManagerFromAClub >0 ? "Keepinsport - Rapport COACH - ".$dateFr : "Keepinsport - Rapport d'activité - ".$dateFr;
                
                $message = \Swift_Message::newInstance()
                    ->setContentType('text/html')
                    ->setSubject($subject)
                    ->setFrom("contact@keepinsport.com")
                    ->setTo($user->getEmail())
                    ->setBody($body);
                $mailer->getTransport()->start();
                $mailer->send($message);
                $mailer->getTransport()->stop();

                return true; 
            }
        }
        
        return false;
    }
    
    public function updateUserMailNotifications ( \Ks\UserBundle\Entity\User $user ) {
        $em                     = $this->doctrine->getEntityManager();
        $notificationTypeRep    = $em->getRepository('KsNotificationBundle:NotificationType');
        $mailNotificationsRep   = $em->getRepository('KsNotificationBundle:UserReceivesMailNotifications');
        
        $notificationTypes = $notificationTypeRep->findAll();
        
        foreach( $notificationTypes as $notificationType ) {
            //On cherche si la configuration existe déjà
            $userReceivesMailNotifications = $mailNotificationsRep->findOneBy( 
                array(
                    "user" => $user->getId(),
                    "type" => $notificationType->getId()
                )
            );
            
            //Si elle n'existe pas on la crée
            if( !is_object( $userReceivesMailNotifications ) ) {
                $userReceivesMailNotifications = new \Ks\NotificationBundle\Entity\UserReceivesMailNotifications();
                $userReceivesMailNotifications->setUser( $user );
                $userReceivesMailNotifications->setType( $notificationType );
                $userReceivesMailNotifications->setWantsReceive( true );
                
                $em->persist( $userReceivesMailNotifications );
                
                $user->addUserReceivesMailNotifications( $userReceivesMailNotifications );
                
                $em->persist( $user );
                
                $em->flush();
            }
        }
        //Si l'utilisateur est abonné par mail à ce type de notifications, on lui envoi            
    }
    
    public function createSimpleNotificationInBdd( $fromUserId, $toUserId, $notificationTypeId, $message ) {
        $em  = $this->doctrine->getEntityManager();
        $dbh = $em->getConnection();
        
        $sql =   "INSERT INTO ks_notification (`fromUser_id`, `owner_id`, `type_id`, `text`) "
                ." VALUES (:fromUserId, :toUserId, :notificationTypeId, :message ) ";
        
        $dbh->executeQuery($sql, array(
            'fromUserId'    => $fromUserId,
            'toUserId'  => $toUserId,
            'notificationTypeId'      => $notificationTypeId,
            'message' => $message
        ));
    }
}


