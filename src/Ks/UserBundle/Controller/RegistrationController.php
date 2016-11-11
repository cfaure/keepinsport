<?php

namespace Ks\UserBundle\Controller;

//use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundException;

use FOS\UserBundle\Controller\RegistrationController as BaseController;

//use Symfony\Component\HttpFoundation\Request;
use Ks\UserBundle\Entity\Invitation;
/*use Ks\UserBundle\Entity\User;*/
use Ks\UserBundle\Entity\UserHasInvited;
use Ks\NotificationBundle\Entity\Notification;
use Ks\NotificationBundle\Entity\NotificationType;

use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\ContainerAware;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller as c2;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Leg\GoogleChartsBundle\Charts\Gallery\Bar\VerticalGroupedChart;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class RegistrationController extends BaseController
{
    public function registerAction()
    {
        $em             = $this->container->get('doctrine')->getEntityManager();
        $request        = $this->container->get('request');
        $leagueLevelRep = $em->getRepository('KsLeagueBundle:LeagueLevel');  
        $userRep        = $em->getRepository('KsUserBundle:User'); 
        $checklistActionRep     = $em->getRepository('KsUserBundle:ChecklistAction');
        $userChecklistActionRep = $em->getRepository('KsUserBundle:UserHasToDoChecklistAction');
        
        //Services
        $notificationService  = $this->container->get('ks_notification.notificationService');
        
        //Récupération de la session
        $session = $this->container->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'register');
        
        //Paramètres GET
        $parameters = $request->query->all();
        $userInvitingId = isset( $parameters["userId"] ) && !empty( $parameters["userId"] ) ? $parameters["userId"] : null;
        
        $choosenPack = isset( $parameters["pack"] ) && !empty( $parameters["pack"] ) ? $parameters["pack"] : null;
        $choosenWatch = isset( $parameters["watch"] ) && !empty( $parameters["watch"] ) ? $parameters["watch"] : null;
        $choosenCoach = isset( $parameters["coach"] ) && !empty( $parameters["coach"] ) ? $parameters["coach"] : null;
        $choosenPackOffer = isset( $parameters["packOfferChoice"] ) && !empty( $parameters["packOfferChoice"] ) ? $parameters["packOfferChoice"] : null;
        $choosenWatchOffer = isset( $parameters["watchOfferChoice"] ) && !empty( $parameters["watchOfferChoice"] ) ? $parameters["watchOfferChoice"] : null;
        
        //var_dump($choosenPack);var_dump($choosenWatch);var_dump($choosenCoach);var_dump($choosenOffer);
        
        //Paramètres POST
        /*$parameters = $request->request->all();
        
        var_dump($parameters);*/
        
        //Si on est authentifié inutile d'afficher cette page 
     
        /*if($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')){
            $route = '_login';
            $url = $this->container->get('router')->generate($route);
            return new RedirectResponse($url);
        }*/
       
        $form = $this->container->get('fos_user.registration.form');
        $formHandler = $this->container->get('fos_user.registration.form.handler');
        $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');
     
        $process = $formHandler->process($confirmationEnabled);
        
        //si il s'agit d'une invitation on passe les variables en get au form 
        if($this->container->get('request')->getMethod() == "GET") {
            $saltInvit = $this->container->get('request')->get("salt");
            $userId = $this->container->get('request')->get("userId");      
        }
        
        if ($process) {
            $user = $form->getData();
            
            /* Mise à jour des données "PACK" souhaitées par le user */
            $user->setChoosenPack($choosenPack);
            $user->setChoosenWatch($choosenWatch);
            $user->setChoosenCoach($choosenCoach);
            $user->setChoosenPackOffer($choosenPackOffer);
            $user->setChoosenWatchOffer($choosenWatchOffer);
            
            //Envoi de mail pour être au courant coté admin de la création d'un nouveau compte
            $notificationService   = $this->container->get('ks_notification.notificationService');
            $message = $user->__toString() . " s'est créé un compte !<br>Pack : " . $choosenPack . " / " . $choosenPackOffer . "<br>Coach : " . $choosenCoach . "<br>Watch : " . $choosenWatch . " / " . $choosenWatchOffer;
            $notificationService->sendNotification(null, $userRep->find(1), $userRep->find(7), 'setPack', $message, null, null);
  
            /*$betainvitation = $this->container->getParameter('betainvitation');
            if($betainvitation=="true"){
                $email = $form->getData()->getEmail();
                $authorizedBetaClosed = $em->getRepository('KsUserBundle:InvitationEmailBeta')->findOneByEmail($email);
                if(!isset($authorizedBetaClosed)){
                    $em->remove($user);
                    $em->flush();
                    $this->setFlash('alert alert-warning', 'users.you-must-be-invite-for-the-beta-closed');
                    $route = 'ks_user_registration_register';
                    $url = $this->container->get('router')->generate($route);
                    return new RedirectResponse($url);

                }
            }*/

           

            $lowestRank     = $leagueLevelRep->getLowestRank();
            $leagueLevel    = $leagueLevelRep->findOneByRank($lowestRank);

            if ( $leagueLevel != null ) {
                $user->setLeagueLevel($leagueLevel);
            }

            $showcase = new \Ks\TrophyBundle\Entity\Showcase();
            $user->setShowcase($showcase);
            $em->persist($showcase);
            $em->persist($user);
            $em->flush();

            $agenda = new \Ks\AgendaBundle\Entity\Agenda();
            $agenda->setName("agenda-".$user->getUsername()."");
            $agenda->setCreatedAt(new \DateTime('now'));
            $user->setAgenda($agenda);
            $em->persist($agenda);
            $em->persist($user);
            $em->flush();

            $services = $em->getRepository('KsUserBundle:Service')->findAll();
            foreach($services as $service) {
                if ($service->getName() != 'Suunto') { //FMO : pour éviter un bug sur connection via FB
                    $userHasService = new \Ks\UserBundle\Entity\UserHasServices();
                    $userHasService->setIsActive(false);
                    $userHasService->setSyncServiceToUser(false);
                    $userHasService->setUserSyncToService(false);
                    $userHasService->setFirstSync(true);
                    $userHasService->setUser($user);
                    $userHasService->setService($service);
                    $em->persist($userHasService);
                    $em->flush();
                }
            }

            $agenda->setName("agenda-".$user->getUsername()."");
            $agenda->setCreatedAt(new \DateTime('now'));
            $user->setAgenda($agenda);
            $em->persist($agenda);
            $em->persist($user);
            $em->flush();
            
            //On cherche l'utilisateur keepinsport et on l'ajoute en ami s'il existe
            $keepinsportUser = $userRep->findOneByUsername("keepinsport");
            
            if( is_object( $keepinsportUser ) ) {
                $userRep->set2UsersLikeFriends( $keepinsportUser, $user );
            } else {
                //$this->setFlash('ksuser', 'utilisateur keepinsport non trouvé');
            }
            
            //On met à jour la configuration pour l'envoi de notifications par mail
            $notificationService->updateUserMailNotifications( $user );
            
            //On met à jour sa checklist
            $checklistActions = $checklistActionRep->findAll();
            foreach( $checklistActions as $checklistAction ) {

                $userHasToDoChecklistAction = $userChecklistActionRep->findOneBy( 
                    array(
                        "user" => $user->getId(),
                        "checklistAction" => $checklistAction->getId()
                    )
                );

                if( !is_object( $userHasToDoChecklistAction ) ) {
                    $userHasToDoChecklistAction = new \Ks\UserBundle\Entity\UserHasToDoChecklistAction();
                    $userHasToDoChecklistAction->setUser( $user );
                    $userHasToDoChecklistAction->setChecklistAction( $checklistAction );

                    $em->persist( $userHasToDoChecklistAction );

                    $em->flush();
                }
            }
            
             //Par default, l'utilisateur recevra les mails quotidiens
            //$user->GetUserDetail()->setReceivesWeeklyEmail( true );

            //$this->leagueLevel = $leagueLevel;
            //$saltInvit = $this->container->get('request')->get("salt");
            
            
            //On a trouvé l'utilisateur qui a invité cette personne
            if( $userInvitingId != null  ) {
                //On cherche le parrain
                $userInviting = $userRep->find( $userInvitingId );
                
                if( is_object( $userInviting ) ) {
                    //On créé la liaison d'amitié
                    $userRep->set2UsersLikeFriends( $userInviting, $user );
                    
                    //On créé la liaison de parrainage
                    $user->setGodFather( $userInviting );
                    $em->persist($user);
                    $em->flush();
                }
            }
            
            /*if($saltInvit!=null && $userId!=null) {  

                //on regarde dans la table ks_invitation si cette invitation n'a pas déja été acceptée 
                    $Invitation = $em->getRepository('KsUserBundle:Invitation')->findOneBy(
                            array(
                                'userInviting'            => $userId ,
                                'salt'                    => $saltInvit,
                                'pending_friend_request'  => "1",
                            )
                        );
                    //si c'est le cas alors on met a jour la table des utilisateurs en question
                    if($Invitation!=null){

                        //on met a jour la table ks_user_has_invited
                        $inviting = $em->getRepository('KsUserBundle:User')->find($userId);
                        if($inviting!=null){
                            $userHasInvited = new \Ks\UserBundle\Entity\UserHasInvited($inviting,$user,$saltInvit);
                            $em->persist($userHasInvited);
                            $em->flush();
                            //on met a jour la demande d'amis dans les notifications
                            $notification = new \Ks\NotificationBundle\Entity\Notification();
                            $createdAt = new \DateTime();
                            $notification->setCreatedAt($createdAt);
                            $notification->setFromUser($inviting);
                            $notification->setGotAnAnswer(0);
                            $notification->setText($inviting->getUsername()." souhaite vous ajouter en ami.");
                            $notification->setNeedAnAnswer(0);
                            $notification->setOwner($user);
                            $notification->setReadAt($createdAt);
                            $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName("friend_request");
                            $notification->setType($notificationType);
                            $em->persist($notification);
                            $em->flush();
                            //on met a jour la demande d'amis 
                            $notification = new \Ks\NotificationBundle\Entity\Notification();
                            $createdAt = new \DateTime();
                            $notification->setCreatedAt($createdAt);
                            $notification->setFromUser($inviting);
                            $notification->setGotAnAnswer(0);
                            $notification->setText("Souhaitez vous ajouter ".$inviting->getUsername()." à votre liste d'amis ?");
                            $notification->setNeedAnAnswer(1);
                            $notification->setOwner($user);
                            $notification->setReadAt($createdAt);
                            $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName("ask_friend_request");
                            $notification->setType($notificationType);
                            $em->persist($notification);
                            $em->flush();
                            //on met a jour la table des invitation 
                            $Invitation->setPendingFriendRequest(0);
                            $em->persist($Invitation);
                            $em->flush();
                        }



                    }
                }  */        

            if ($confirmationEnabled) {
                $this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
                //$route = 'fos_user_registration_check_email';
                $route = 'ks_user_registration_check_email';
                //$route = 'ksRegistration_checkEmail';

            } else {
                $this->authenticateUser($user);
                $route = 'ks_user_registration_confirmed';
            }

            $this->setFlash('fos_user_success', 'registration.flash.user_created');
            $url = $this->container->get('router')->generate($route);

            return new RedirectResponse($url);


        }
      
        
        /*if(isset($saltInvit) && $saltInvit!=null && isset($userId) && $userId!=null){
             return $this->container->get('templating')->renderResponse('KsUserBundle:Registration:register.html.'.$this->getEngine(), array(
                'form'   => $form->createView(),
                'theme'  => $this->container->getParameter('fos_user.template.theme'),
                'salt'   => $saltInvit,
                'userId' => $userInvitingId,
            ));
        }else{
              return $this->container->get('templating')->renderResponse('KsUserBundle:Registration:register.html.'.$this->getEngine(), array(
                'form' => $form->createView(),
                'theme' => $this->container->getParameter('fos_user.template.theme'),
            ));
        }*/
        
        $citations = array();
        $usersKs = array("clements", "adrien974", "stephane974", "quentin973", "moreljeanalain", "Sofi", "Domi76974");
        $texts = array();
        $texts[$usersKs[0]] = "Depuis que j'utilise keepinsport, je béneficie d'un suivi constant de mon entraînement et de ma progression. super outil qui permet à pascal blanc d'avoir un oeil attentif sur mon entraînement !";
        $texts[$usersKs[1]] = "Grâce à Keepinsport j'ai gagné en interactivité sur les plans d'entrainement que Pascal Blanc me fait. En cas de soucis le coach me modifie instantanément mon planning, et toutes mes séances sont détaillées ce qui me permet une progression continue. Vraiment un superbe outil au service de notre progression !";
        $texts[$usersKs[2]] = "Pour la préparation de La Diagonale des Fous 2014 j'ai décidé de prendre un coach . Ce n'est pas la motivation qui me manquait , ce n'est pas une perf que je recherchais , j'avais juste besoin d'un plan d'entraînement à suivre ... Pascal Blanc m'a apporté ce suivi , ses conseils , et grâce à Keepinsport j'ai de façon ludique 'enquillé' les séances , j'ai constaté ma progression , je me suis mesuré aux autres ... Et 4 mois après j'ai franchi cette putain de ligne au stade de la Redoute !";
        $texts[$usersKs[3]] = "Idéal pour observer sa progression et atteindre ses objectifs sportifs, merci Keepinsport pour sa plateforme interactive: géniale pour un coaching avec Pascal !";
        $texts[$usersKs[4]] = "Avec Keepinsport, je peux partager mes résussites et mes difficultés lors de mes entraînements !";
        $texts[$usersKs[5]] = "Pour ma part keepinsport m'as permis d'avoir un suivi plus direct avec Pascal, on peut échanger plus rapidement j'ai mon planning sur le site sur lequel je peux mettre des commentaires, on peut apporter une modification au plan si on a un empêchement et de son côté Pascal peut nous suivre de plus près et réadapter le plan selon notre emploi du temps c pratique :)";
        $texts[$usersKs[6]] = " Keepinsport est outil performant qui me permet de mesurer mon effort et ma performance et donne à mon coach Pascal Blanc toutes les données pour vérifier ma pratique correcte des activités planifiées. ";
        
        $citations = array();
        for ($i=0;$i<6;$i++) {
            $userToAffich = $userRep->findOneUser( array(
                "username" => $usersKs[$i]
            ));

            $citations[] = array(
                "user" => $userToAffich,
                "text" => $texts[$usersKs[$i]]
            );
        }
        
        return $this->container->get('templating')->renderResponse('KsUserBundle:Registration:register.html.'.$this->getEngine(), array(
                'form'   => $form->createView(),
                //'theme'  => $this->container->getParameter('fos_user.template.theme'),
                //'salt'   => $saltInvit,
                //'userInvitingId' => $userInvitingId,
                'citations'     => $citations
            ));
    }

    /**
     * Tell the user to check his email provider
     */
    public function checkEmailAction()
    {
        $email = $this->container->get('session')->get('fos_user_send_confirmation_email/email');
        $this->container->get('session')->remove('fos_user_send_confirmation_email/email');
        $user = $this->container->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->container->get('templating')->renderResponse('KsUserBundle:Registration:checkEmail.html.'.$this->getEngine(), array(
            'user' => $user,
        ));
    }
    
    /**
     * Receive the confirmation token from user email provider, login the user
     */
    public function confirmAction($token)
    {
        //$user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            //throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));7
            $this->container->get('session')->setFlash('alert alert-error', 'users.profil_confirm_fail');
            $this->container->get('session')->setFlash('alert alert-info', 'users.profil_confirm_info');
            //Redirection vers la page de réinitialisation du mot de passe + msg flash 
            return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_request'));
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $user->setLastLogin(new \DateTime());

        $this->container->get('fos_user.user_manager')->updateUser($user);
        $this->authenticateUser($user);

        return new RedirectResponse($this->container->get('router')->generate('ks_user_registration_confirmed'));
    }

    /**
     * Tell the user his account is now confirmed
     */
    public function confirmedAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        if (!is_object($user)) {
            //throw new AccessDeniedException('This user does not have access to this section.');
            $this->container->get('session')->setFlash('alert alert-error', "Vous n'avez pas accès à cette section");
            return new RedirectResponse($this->container->get('router')->generate('fos_user_security_login'));
        }

        //$this->get('translator')->trans('impossible-to-find-activity-%activityId%', array('%activityId%' => $activityId))
        //$message = "FÃ©licitations" . $user->getUsername() .", tom compte est maintenant activÃ©. Merci de complÃ©ter les informations suivantes pour profiter pleinement du site !";
        //this->container->get('session')->setFlash('alert alert-success', $message);
        return new RedirectResponse($this->container->get('router')->generate('ksProfile_V2', array()));
        /*return $this->container->get('templating')->renderResponse('KsUserBundle:Registration:confirmed.html.'.$this->getEngine(), array(
            'user' => $user,
        ));*/
    }
    
    public function registerCarouselAction()
    {   
        $em      = $this->container->get('doctrine')->getEntityManager();
        $userRep = $em->getRepository('KsUserBundle:User');
        
        $usersKs = array("cedricdelpoux", "fred", "clem", "ziiicos", "grichard", "gyom", "joemiage", "Natachab2", "zourito974", "chino.legrincheux");
        $texts = array();
        
        $texts[$usersKs[0]] = "J'avais des amis sous Runkeeper, d'autres sous Nike. Grâce à Keepinsport on peut tous comparer nos activités";
        $texts[$usersKs[1]] = "J'ai trouvé de la motivation pour faire plus de sport grâce aux ligues et étoiles !";
        $texts[$usersKs[2]] = "Je peux me comparer avec mes amis, quels que soient leurs sports";
        $texts[$usersKs[3]] = "J'adore le WikiSport et ses articles collaboratifs !";
        $texts[$usersKs[4]] = "Avec Keepinsport je peux partager mes activités avec tous mes amis, quel que soit l'outil qu'ils ont utilisé pour tracker leur activité (smartphone, montres spécialisées...)";
        $texts[$usersKs[5]] = "Avec keepinsport j'ai enfin un suivi global et unifié de toutes mes activités sportives !";
        $texts[$usersKs[6]] = "Essayer de gagner la ligue argent et passer en ligue or, ça me motive à me bouger !";
        $texts[$usersKs[7]] = "Youhou !!! Merci à Keepinsport pour ce bon d'achat, un site qui mérite à être connu et surtout à être utilisé !";
        $texts[$usersKs[8]] = "Grace a Keepinsport je peux enfin stocker et gérer mes performances de manière ludique et me tirer la bourre avec mes potes. Outil indispensable et très agréable à utiliser, que l'on soit simple pratiquant ou compétiteur. Belle initiative, bravo à toute l'équipe !";
        $texts[$usersKs[9]] = "Merci à KS pour le bon d'achat et merci aussi pour le site qui offre un service de qualité et qui marche super bien! longue vie à KS!";
        
        $citations = array();
        
        for ($i=0;$i<10;$i++) {
            $userToAffich = $userRep->findOneUser( array(
                "username" => $usersKs[$i]
            ));
            
            $citations[] = array(
                "user" => $userToAffich,
                "text" => $texts[$usersKs[$i]]
            );
        }
        
        return $this->container->get('templating')->renderResponse('KsUserBundle:Registration:_registerCarousel.html.twig', array(
            'citations'             => $citations
        ));
    }
}