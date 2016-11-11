<?php
namespace Ks\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ActivityController extends Controller
{
    /**
     * @Route("/newsFeed/{firstSync}", defaults={"firstSync" = null}, name = "ksActivity_activitiesList", options={"expose"=true}  )
     */
    public function activitiesListAction($firstSync)
    {        
        $securityContext                = $this->container->get('security.context');
        $em                             = $this->getDoctrine()->getEntityManager();
        $user                           = $this->get('security.context')->getToken()->getUser();
        $importantStatusRep             = $em->getRepository('KsActivityBundle:UserReadsImportantStatus');
        $userRep                        = $em->getRepository('KsUserBundle:User');
        $userHasToDoChecklistActionRep  = $em->getRepository('KsUserBundle:UserHasToDoChecklistAction');
        $checklistActionRep             = $em->getRepository('KsUserBundle:ChecklistAction');
        $clubRep                        = $em->getRepository('KsClubBundle:Club');
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'newsFeed');
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $isExpertMode = $userRep->isExpertMode($user->getId());
        }
        else {
            //Visitor
            $isExpertMode = false;//return new RedirectResponse($this->container->get('router')->generate('_login'));
            $user = $userRep->find(1);
            $this->addUserAction('newsFeed', 'visite', 'OK', null);
        }
        $session->set('isExpertMode', $isExpertMode);
        
        $newsFeedTypePreference = array();
        $newsFeedFromPreference = array();
        $newsFeedSortPreference = array();
        $newsFeedSportsPreference = array();
        $visitSeenPreference = array();
        
        //Si l'utilisateur n'a jamais complété son profil
        if( is_object( $user )) {
            if( $user->getCompletedHisProfileRegistration() != true) {
                $this->get('session')->setFlash('alert alert-info', "Tu n'as pas encore complété les informations de ton profil. Nous t'invitons à y consacrer quelques secondes pour bénéficier de toutes les fonctionnalités du site !");
                return new RedirectResponse($this->container->get('router')->generate('ksProfile_V2'));
            }
            
            //FMO : cas de l'utilisateur qui active pour la 1ère fois un service d'import automatique
            if ($firstSync == 'firstSync') $this->get('session')->setFlash('alert alert-info', $this->get('translator')->trans("menu.invite-sync-button"));

            //Récupération des messages importants, s'il y en a
            $importantStatus = $importantStatusRep->findLastImportantStatus($user);
            
            /*Récupération des filtres de l'utilisateur
             * 2- Type
             * 3- From
             * 4- Sports
             * 5- Tri
            */
            $newsFeedTypePreference = $userRep->getUserDetailHasPreferenceByType(array("userId" => $user->getId(), "preferenceTypeId" => 2), $this->get('translator'));
            $newsFeedFromPreference = $userRep->getUserDetailHasPreferenceByType(array("userId" => $user->getId(), "preferenceTypeId" => 3), $this->get('translator'));
            $newsFeedSortPreference = $userRep->getUserDetailHasPreferenceByType(array("userId" => $user->getId(), "preferenceTypeId" => 4), $this->get('translator'));
            $newsFeedSportsPreference = $userRep->getUserDetailHasPreferenceByType(array("userId" => $user->getId(), "preferenceTypeId" => 5), $this->get('translator'));
            $visitSeenPreference = $userHasToDoChecklistActionRep->findByUserAndChecklistAction($user->getId(), $checklistActionRep->findOneByCode("visitSeen")->getId());
        }
        
        $activitySession            = new \Ks\ActivityBundle\Entity\ActivitySession();
        $activitySportChoiceForm    = $this->createForm(new \Ks\ActivityBundle\Form\SportType('MultiSimple'), $activitySession);

        ini_set('memory_limit', '1024M');
        
        return $this->render(
            'KsActivityBundle:Activity:activitiesList_NEW.html.twig',
            array(
                'user'                      => $user,
                'importantStatus'           => isset( $importantStatus ) ? $importantStatus : array(),
                'activitySportChoiceForm'   => isset( $activitySportChoiceForm ) ? $activitySportChoiceForm->createView() : null,
                'newsFeedTypePreference'    => $newsFeedTypePreference,
                'newsFeedFromPreference'    => $newsFeedFromPreference,
                'newsFeedSportsPreference'  => $newsFeedSportsPreference,
                'newsFeedSortPreference'    => $newsFeedSortPreference,
                'visitSeenPreference'       => $visitSeenPreference,
                'session'                   => $session->get('page')
            )
        );
    }
    
    /**
     * @Route("/menu", name = "ksActivity_menu")
     */
    public function menuAction()
    {        
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $userRep            = $em->getRepository('KsUserBundle:User');
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'newsFeed');
        
        //Si l'utilisateur n'a jamais complété son profil
        if( is_object( $user )) {
            $isExpertMode = $userRep->isExpertMode($user->getId());
            
            $status                 = new \Ks\ActivityBundle\Entity\ActivityStatus($user);
            $statusForm             = $this->createForm(new \Ks\ActivityBundle\Form\ActivityStatusType(), $status);
            $link                   = new \Ks\ActivityBundle\Entity\ActivityStatus($user);
            $linkForm               = $this->createForm(new \Ks\ActivityBundle\Form\ActivityLinkType(), $link);
            $photo                  = new \Ks\ActivityBundle\Entity\ActivityStatus($user);
            $photoForm              = $this->createForm(new \Ks\ActivityBundle\Form\ActivityPhotoType(), $photo);
        }
        
        return $this->render(
            'KsActivityBundle:Activity:menu.html.twig',
            array(
                'isExpertMode'              => $isExpertMode,
                'activityStatusForm'        => isset( $statusForm ) ? $statusForm->createView() : null,
                'linkForm'                  => isset( $linkForm ) ? $linkForm->createView() : null,
                'photoForm'                 => isset( $photoForm ) ? $photoForm->createView() : null
            )
        );
    }
    
    
    
    /**
     * @Route("/activity_v2/{activityId}", name = "ksActivity_showActivityV2" )
     */
    public function showActivityV2Action($activityId)
    {    
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $stateOfHealthRep   = $em->getRepository('KsActivityBundle:StateOfHealth');

        $activity           = $activityRep->find($activityId);

        if (!is_object($activity)) {
            $impossibleActivityMsg = $this->get('translator')->trans('impossible-to-find-activity-%activityId%', array('%activityId%' => $activityId));
            throw new AccessDeniedException($impossibleActivityMsg);
        }
        
        if ( $activity->getType() == "article" ) {
            return new RedirectResponse($this->generateUrl('ksWikisport_show', array("articleId" => $activity->getId())));
        }
        
        $statesOfHealth = $stateOfHealthRep->findAll();
        
        return $this->render('KsActivityBundle:Activity:showActivityV2.html.twig', array(
            'activity'          => $activity,
            'statesOfHealth'    => $statesOfHealth
        ));
    }
    
    /**
     * @Route("/{activityId}/show/{fullscreen}", defaults={"fullscreen" = true}, name = "ksActivity_showActivity", options={"expose"=true}  )
     */
    public function showActivityAction($activityId, $fullscreen)
    {
        $em                             = $this->getDoctrine()->getEntityManager();
        $securityContext                = $this->container->get('security.context');
        $activityRep                    = $em->getRepository('KsActivityBundle:Activity');
        $stateOfHealthRep               = $em->getRepository('KsActivityBundle:StateOfHealth');
        $notificationRep                = $em->getRepository('KsNotificationBundle:Notification');
        $notifTypesRep                  = $em->getRepository('KsNotificationBundle:NotificationType');
        $userHasToDoChecklistActionRep  = $em->getRepository('KsUserBundle:UserHasToDoChecklistAction');
        $checklistActionRep             = $em->getRepository('KsUserBundle:ChecklistAction');
        $userRep                        = $em->getRepository('KsUserBundle:User');
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $this->get('security.context')->getToken()->getUser();
        }
        else $user = $userRep->find(1);
        
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'detailActivity');
        
        if( !$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            //return new RedirectResponse($this->container->get('router')->generate('_login'));
            $this->addUserAction('showActivity '.$activityId, 'visite', 'OK', null);
        }
        
        $notification = null;
        
        $activityDatas      = $activityRep->findActivities(array(
            'activityId' => $activityId,
            'isNotValidatePossible' => true
        ));
        $activity           = $activityDatas['activity'];

        if (empty($activity)) {
            throw new AccessDeniedException(
                $this->get('translator')->trans('impossible-to-find-activity-%activityId%', array('%activityId%' => $activityId))
            );
        }
        
        $notificationType_name = "mustBeValidated";
        $notificationType_mbv = $notifTypesRep->findOneByName($notificationType_name);
        
        if( $activity["isValidate"] !== true ) {
            $notification = $notificationRep->findOneBy(array(
                "activity" => $activity["id"],
                "type"      => $notificationType_mbv->getId()
            ));
        }
        
        if ($activity['type'] == "article") {
            return new RedirectResponse($this->generateUrl('ksWikisport_show', array("id" => $activityId)));
        }
        
        $statesOfHealth = $stateOfHealthRep->findAll();
        
        $isAllowedPackPremium =false;
        $isAllowedPackElite =false;
        $issetHeartRates = false;
        $activityEntity = $activityRep->find($activityId);
        if ($activityEntity instanceof \Ks\ActivityBundle\Entity\ActivitySession) {
            $trackingDatas  = $activityRep->find($activityId)->getTrackingDatas();
            $hasWaypoints   = isset($trackingDatas['waypoints']) ? (count($trackingDatas["waypoints"]) != 0 ? count($trackingDatas["waypoints"]) : null) : null;
            $isAllowedPackPremium = $activityEntity->getUser()->getIsAllowedPackPremium();
            $isAllowedPackElite = $activityEntity->getUser()->getIsAllowedPackElite();
            $isUser = $user->getId() == $activityEntity->getUser()->getId() ? 1 : 0;
            $activityDetailSeenPreference = $userHasToDoChecklistActionRep->findByUserAndChecklistAction($user->getId(), $checklistActionRep->findOneByCode("activityDetailSeen")->getId());
            $issetHeartRates = $trackingDatas['info']['issetHeartRates'];
        }
        else {
            $hasWaypoints = null;
        }

        ini_set('memory_limit', '1024M');
        
        return $this->render(
            'KsActivityBundle:Activity:showActivity.html.twig',
            array_merge(
                $activityDatas,
                array(
                    'statesOfHealth'                => $statesOfHealth,
                    'hasWaypoints'                  => $hasWaypoints,
                    'notification'                  => $notification,
                    'fullscreen'                    => $fullscreen,
                    'isAllowedPackPremium'          => $isAllowedPackPremium,
                    'isAllowedPackElite'            => $isAllowedPackElite,
                    'isUser'                        => $isUser,
                    'activityDetailSeenPreference'  => $activityDetailSeenPreference,
                    'issetHeartRates'               => $issetHeartRates
                )
            )   
        ); 
    }
    
    /**
     * 
     * @Route("/load/{offset}", requirements={"offset" = "\d+"}, name="ksActivity_loadActivities", options={"expose"=true} )
     * @param int $offset 
     */
    public function loadActivitiesAction($offset)
    {
        $numActivitiesPerPage = 5;
        $em                 = $this->getDoctrine()->getEntityManager();
        $securityContext    = $this->container->get('security.context');
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $userRep            = $em->getRepository('KsUserBundle:User');
        
        $request            = $this->getRequest();
        $parameters     = $request->request->all();
        
        $fromPublicProfile  = (isset( $parameters["fromPublicProfile"]) && $parameters["fromPublicProfile"] === 'true' ) ? true : false;
        $activitiesTypes    = isset( $parameters["activitiesTypes"] ) ? $parameters["activitiesTypes"] : array();
        $activitiesFrom     = isset( $parameters["activitiesFrom"] ) && is_array( $parameters["activitiesFrom"] ) ? $parameters["activitiesFrom"] : array();
        $sports             = isset( $parameters["sports"] ) && is_array( $parameters["sports"] ) ? $parameters["sports"] : array();
        $lastModified       = ( isset( $parameters["lastModified"] ) && $parameters["lastModified"] == "false" ) ? false : true;
        
        if ( $fromPublicProfile ) $lastModified = false;
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user           = $this->get('security.context')->getToken()->getUser();
        }else {
            $activitiesFrom = array("me", "my_friends");
            $activitiesTypes = array();
            $user = $userRep->find(1);
        }
        
        //Si choix MES SPORTS, le tableau doit contenir la valeur ""
        if (in_array( "", $sports) && count($sports) > 0) $my_sports = true;
        else $my_sports = false;

        //var_dump($user->getId());
        $stime = microtime(true);
        $activities = $activityRep->findActivities(array(
            'user'                  => $user,
            'activitiesTypes'       => $activitiesTypes,
            'activitiesFrom'        => $activitiesFrom,
            'fromPublicProfile'     => $fromPublicProfile,
            'sports'                => $sports,
            'my_sports'             => $my_sports,
            //'userIds'           => $userId != 0 ? array( $userId ) : array(),
            'lastModified'          => $lastModified,
            'withNoPrivateCoaching' => true,//Pour les plans d'entrainements : si event privé uniquement le coaché et le coach peuvent voir l'activité
            'offset'                => $offset,
            'perPage'               => $numActivitiesPerPage,
            'dontGetArticles'       => true // On ne récupère pas les activités de type "article" dans le fil d'actu
        ));
        $etime = microtime(true) - $stime;

        if (count($activities) > 0) {
            $offset += count($activities);
        }
        
        //$stime = microtime(true);
        $responseDatas = array(
            'offset' => $offset,
            'html' => $this->render('KsActivityBundle:Activity:_activities.html.twig', array('activities'        => $activities))->getContent()
            //'html' => ''
        );
         
        $etime2 = microtime(true) - $stime;
        //var_dump($etime, $etime2); //Attention si on laisse ça décommenter le fil d'actu ne s'affiche plus !
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');
        
        return $response; 
    }
    
 
    
    /**
     * 
     * @Route("/loadSportSessions/{sportId}/{offset}", requirements={"offset" = "\d+"}, name = "ksActivity_loadSportSessions", options={"expose"=true} )
     * @param int $offset 
     */
    public function loadSportSessionsAction($sportId, $offset)
    {
        $numActivitiesPerPage = 4;
        
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $user           = $this->get('security.context')->getToken()->getUser();      
        $activities     = $activityRep->findActivitySessionsFromUser($user, $offset, $numActivitiesPerPage, $sportId);
        
        /*$haveAlreadyVoted = array();
        $hasNotUnsubscribed = array();
        foreach($activities as $key => $activity) {
            $haveAlreadyVoted[$key] = $activityRep->haveAlreadyVoted($activity, $user);
            $hasNotUnsubscribed[$key] = $activityRep->hasNotUnsubscribed($activity, $user);
        } */
        
        if ( count($activities) > 0 ) {
            $offset += count($activities);
        }
        
        $responseDatas = array(
            'offset' => $offset,
            'html' => $this->render('KsActivityBundle:Activity:_activities.html.twig', array(
                'activities'        => $activities,
                //'haveAlreadyVoted'  => $haveAlreadyVoted,
                //'hasNotUnsubscribed'=> $hasNotUnsubscribed,
                //'offset'            => $offset + $numActivitiesPerPage
            ))->getContent()
        ); 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/notDisplayedActivities", name = "ksActivity_getNotDisplayedLastActivities", options={"expose"=true} )
     */
    public function getNotDisplayedActivitiesAction()
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $repository     = $em->getRepository('KsActivityBundle:Activity');
        $request        = $this->get('request');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        if ($request->isXmlHttpRequest()) {
            $lastDisplayedActivityId = $request->request->get('lastDisplayedActivityId');
            $lastRefreshTime = $request->request->get('lastRefreshTime');
        }
        $activities = $repository->findNotDisplayedFriendActivities($user, $lastDisplayedActivityId, $lastRefreshTime);
        //$activities = $repository->findFriendActivities($user, 5, 1);
        $aActivities = array();
        foreach($activities as $key => $activity) {
            $aActivities[$key]["id"] = $activity->getId();
            $aActivities[$key]["user"] = array(
                "id"        => $activity->getUser()->getId(),
                "username"  => $activity->getUser()->getUsername()
            );
            $aActivities[$key]["description"] = $activity->getDescription();
            $aActivities[$key]["issuedAt"] = $activity->getIssuedAt();
        }
        $responseDatas = array(
            'getNotDisplayedActivitiesResponse' => 1,
            'activities'                        => $aActivities,
            'lastRefreshTime'                   => time()
        ); 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/voteOnActivity/{activityId}", requirements={"activityId" = "\d+"}, name = "ksActivity_voteOnActivity", options={"expose"=true} )
     * @param int $activityId 
     */
    public function voteOnActivityAction($activityId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        //Services
        $notificationService   = $this->get('ks_notification.notificationService');
        
        $activity = $activityRep->find($activityId);
        
        if (!is_object($activity) ) {
            $impossibleActivityMsg = $this->get('translator')->trans('impossible-to-find-activity-%activityId%', array('%activityId%' => $activityId));
            throw new AccessDeniedException($impossibleActivityMsg);
        }
        
        $responseDatas = array();
        
        //Si l'utilisateur n'a pas déjà voté sur l'activité
        if ( ! $activityRep->haveAlreadyVoted($activity, $user) ) {
            $activityRep->voteOnActivity($activity, $user);

            //Création d'une notification
            $notificationType_name = "vote";
            $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName($notificationType_name);

            if (!$notificationType) {
                $impossibleNotificationTypeMsg = $this->get('translator')->trans('impossible-to-find-notification-%$notificationTypeName%', array('%$notificationTypeName%' => $notificationType_name));
                throw $this->createNotFoundException($impossibleNotificationTypeMsg);
            }
            
            if ($activity->getUser() != $user) {
                $notificationService->sendNotification($activity, $user, $activity->getUser(), $notificationType_name);  
            }
            
            //Une notification de commentaire pour chaque abonné
            foreach($activity->getSubscribers() as $activityHasSubscribers) {
                $subscriber = $activityHasSubscribers->getSubscriber();

                //Si l'abonné n'est pas lui même et qu'il n'a pas posté l'activité
                if ($subscriber != $user && $activity->getUser() != $subscriber) {
                    $notificationService->sendNotification($activity, $user, $subscriber, $notificationType_name);  
                }
            } 
            
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkCommentLikeShareActivity($user->getId());
            
            $responseDatas["responseVote"]  = 1;
        } else {
            $responseDatas["responseVote"] = -1;
            $voteActivityMsg = $this->get('translator')->trans('you-voted-on-this-activity');
            $responseDatas["errorMessage"] = $voteActivityMsg;
        }
        
        $activity->numVotes             = (int)$activityRep->getNumVotesOnActivity($activity);
        
        $responseDatas["voteLink"] = $this->render('KsActivityBundle:Activity:_voteLink.html.twig', array(
            'activity'          => $activity,
            'haveAlreadyVoted'  => $activityRep->haveAlreadyVoted($activity, $user)
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/removeVoteOnActivity/{activityId}", requirements={"activityId" = "\d+"}, name = "ksActivity_removeVoteOnActivity", options={"expose"=true} )
     * @param int $activityId 
     */
    public function removeVoteOnActivityAction($activityId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $votesRep       = $em->getRepository('KsActivityBundle:ActivityHasVotes');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $responseDatas = array();
        
        $activity = $activityRep->find($activityId);
        
        if (!is_object($activity) ) {
            $impossibleActivityMsg = $this->get('translator')->trans('impossible-to-find-activity-%activityId%', array('%activityId%' => $activityId));
            throw new AccessDeniedException($impossibleActivityMsg);
        }
        
        if ( $activityRep->haveAlreadyVoted($activity, $user) ) {
            $activityHasVotes = $votesRep->find(array("activity" => $activityId, "voter" => $user->getId()));
        
            if (!is_object($activityHasVotes) ) {
                $impossibleVoteMsg = $this->get('translator')->trans('impossible-to-find-vote-%activityId%', array('%activityId%' => $activityId));
                throw new AccessDeniedException($impossibleVoteMsg);
            }

            $activityRep->removeVoteOnActivity($activityHasVotes);
            $activity->numVotes             = (int)$activityRep->getNumVotesOnActivity($activity);
            $responseDatas["responseVote"]  = 1;
        } else {
            $responseDatas["responseVote"]  = -1;
            $youAlreadyRetireMsg = $this->get('translator')->trans('you-already-retire-your-activity');
            $responseDatas["errorMessage"]  = $youAlreadyRetireMsg;
        }
        
        $responseDatas["voteLink"] = $this->render('KsActivityBundle:Activity:_voteLink.html.twig', array(
            'activity' => $activity
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/subscribeOnActivity/{activityId}", requirements={"activityId" = "\d+"}, name = "ksActivity_subscribeOnActivity", options={"expose"=true} )
     * @param int $activityId 
     */
    public function subscribeOnActivityAction($activityId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $subscrtipRep   = $em->getRepository('KsActivityBundle:ActivityHasSubscribers');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $activity = $activityRep->find($activityId);
        
        if (!is_object($activity) ) {
            throw new AccessDeniedException("Impossible de trouver l'activité " . $activityId .".");
        }
        
        $responseDatas      = array();
        $ahs                = $subscrtipRep->find(array(
            "activity"      => $activityId,
            "subscriber"    => $user->getId()
        ));
        
        if (!is_object($ahs)) { // n'a jamais été abonné
            $activityRep->subscribeOnActivity($activity, $user);
            $responseDatas["responseSubscribe"] = 1;
        } else if ($ahs->getHasUnsubscribed() != null) { // FIXME: ... sert à rien
            $responseDatas["responseSubscribe"] = 1;
            $activityRep->subscribeAgainOnActivity($ahs);
        } else { // est abonné
            $responseDatas["responseSubscribe"] = -1;
            $responseDatas["errorMessage"]      = "Vous avez déjà abonné à cette activité";
        }
        
        $responseDatas['subscriptionLink'] = $this->render(
            'KsActivityBundle:Activity:_subscriptionLink.html.twig',
            array(
                'activity' => $activity
            )
        )->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * 
     * @Route("/unsubscribeOnActivity/{activityId}", requirements={"activityId" = "\d+"}, name = "ksActivity_unsubscribeOnActivity", options={"expose"=true} )
     * @param int $activityId 
     */
    public function unsubscribeOnActivityAction($activityId)
    {
        $responseDatas  = array();
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $subscrtipRep   = $em->getRepository('KsActivityBundle:ActivityHasSubscribers');
        $user           = $this->get('security.context')->getToken()->getUser();
        $activity       = $activityRep->find($activityId);
        
        if (!is_object($activity) ) {
            throw new AccessDeniedException("Impossible de trouver l'activité " . $activityId .".");
        }
        
        $ahs = $subscrtipRep->find(array(
            "activity"      => $activityId,
            "subscriber"    => $user->getId()
        ));
        
        if (!is_object($ahs) || $ahs->getHasUnsubscribed() == 1) { // n'a jamais été abonné ou s'est désabonné manuellement
            $responseDatas["responseUnsubscribe"]   = -1;
            $responseDatas["errorMessage"]          = "Vous n'êtes pas abonné à cette activité";
        } else { // est abonné
            $activityRep->unsubscribeOnActivity($ahs);
            $responseDatas["responseUnsubscribe"] = 1;
        }
        
        $responseDatas['subscriptionLink'] = $this->render(
            'KsActivityBundle:Activity:_subscriptionLink.html.twig',
            array(
                'activity' => $activity
            )
        )->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/changeStateOfHealthOnActivity/{activityId}_{stateOfHealthId}", requirements={"activityId" = "\d+"}, name = "ksActivity_changeStateOfHealthOnActivity", options={"expose"=true} )
     * @param int $activityId 
     */
    public function changeStateOfHealthOnActivityAction($activityId, $stateOfHealthId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $stateOfHealthRep   = $em->getRepository('KsActivityBundle:StateOfHealth');
        $user               = $this->get('security.context')->getToken()->getUser();
        
        $responseDatas = array();
        
        $activity = $activityRep->find($activityId);
        
        if (!is_object($activity) ) {
            throw new AccessDeniedException("Impossible de trouver l'activité " . $activityId .".");
        }

        $stateOfHealth = $stateOfHealthRep->find($stateOfHealthId);
        
        if (!is_object($stateOfHealth) ) {
            throw new AccessDeniedException("Impossible de trouver l'état de forme " . $stateOfHealthId .".");
        }

        $activityRep->changeStateOfHealthOnActivitySession($activity, $stateOfHealth);

        if( $activity->getStateOfHealth()->getId() == $stateOfHealth->getId() ) {
            $responseDatas["modifResponse"] = 1;
            $statesOfHealth = $stateOfHealthRep->findAll();
            $activitySoh    = $activity->getStateOfHealth();
        
            $responseDatas["html"] = $this->render('KsActivityBundle:Activity:_stateOfHealth_edit.html.twig', array(
                'activity'          => array(
                    'id'                    => $activityId,
                    'user_id'               => $activity->getUser()->getId(),
                    'stateOfHealth_id'      => $activitySoh->getId(),
                    'stateOfHealth_code'    => $activitySoh->getCode()
                ),
                'statesOfHealth'    => $statesOfHealth
            ))->getContent();
        } else {
            $responseDatas["modifResponse"] = -1;
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    
    /**
     * 
     * @Route("/loadActivityToBeShared/{activityId}", requirements={"activityId" = "\d+"}, name = "ksActivity_loadActivityToBeShared", options={"expose"=true} )
     * @param int $activityId 
     */
    public function loadActivityToBeSharedAction($activityId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
                
        $responseDatas = array();
        
        $activityDatas = $activityRep->findActivities(array('activityId' => $activityId));
        
        if (empty($activityDatas)) {
            throw new AccessDeniedException("Impossible de trouver l'activité " . $activityId .".");
        }
        
        $activity = $activityRep->find($activityId);
        if (is_null($activity)) $trackingDatas  = null;
        else $trackingDatas = $activity->getTrackingDatas();
        
        if (is_null($trackingDatas)) $waypointsActivity = null;
        else $waypointsActivity = array('id' => $activityId, 'points' => $trackingDatas["waypoints"]);
        
        $responseDatas['activityToBeShared_html'] = $this->render('KsActivityBundle:Activity:_activityBloc.html.twig', 
                array_merge($activityDatas, 
                            array('isShared'            => true, 
                                  'context'             => 'shareToFB',
                                  'waypointsActivity'   => is_null($waypointsActivity) ? null : $this->var_to_js('waypointsActivity', $waypointsActivity, 1))))->getContent();
        //$responseDatas['activityToBeShared_html'] = '';
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * 
     * @Route("/shareActivity/{activityId}", requirements={"activityId" = "\d+"}, name = "ksActivity_shareActivity", options={"expose"=true} )
     * @param int $activityId 
     */
    public function shareActivityAction($activityId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $notifTypeRep   = $em->getRepository('KsNotificationBundle:NotificationType');
        $request        = $this->get('request');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        //sevices
        $notificationService  = $this->get('ks_notification.notificationService');
        
        $activity = $activityRep->find($activityId);
        
        if ( !is_object($activity) ) {
            throw $this->createNotFoundException("Impossible de trouver l'activité " . $activityId .".");
        }
        
        $responseDatas = array();
        
        //On récupère le type de notification pour le "partage"
        $notificationType_name = "share";
        $notificationType = $notifTypeRep->findOneByName($notificationType_name);

        if (!$notificationType) {
            throw $this->createNotFoundException('Impossible de trouver le type de notification ' . $notificationType_name . '.');
        }
        
        $parameters = $request->request->all();
        
        //On récupère la description
        $description = isset( $parameters["description"] ) ? $parameters["description"] : "" ;
        
        $abstractActivity = $activityRep->shareActivity($activity, $user, $notificationType, $description);
        
        if ($abstractActivity) {
            $responseDatas['shareResponse'] = 1;
            
            if( $user != $activity->getUser() ) {
                $notificationService->sendNotification($activity, $user, $activity->getUser(), "share");
            }
            // FIXME : pfff :(
            $activityDatas = $activityRep->findActivities(array('activityId' => $abstractActivity->getId()));
            //$activityDatas['isShated'] = true;
            
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkCommentLikeShareActivity($user->getId());
            
            $responseDatas['html'] = $this->render(
                'KsActivityBundle:Activity:_activity.html.twig',
                $activityDatas
            )->getContent();
            
            //On abonne l'utilisateur à l'activité qui vient d'être partagée
             //Si l'utilisateur n'a jamais été abonné
            //S'il a déjà été abonné mais qu'il s'est désabonné
            if ( $activityRep->isNotSubscribed($activity, $user) && ! $activityRep->hasNotUnsubscribed($activity, $user) ) {
                $activityRep->subscribeOnActivity($activity, $user);
            }
            
            //On abonne l'utilisateur à la nouvelle activité
            $activityRep->subscribeOnActivity($abstractActivity, $user);
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * @Route("/loadActivityStatus/", name = "ksActivity_loadActivityStatus", options={"expose"=true} )
     */
    public function loadActivityStatusFormAction()
    {   
        $em             = $this->getDoctrine()->getEntityManager();
        $user           = $this->get('security.context')->getToken()->getUser();     
        $status         = new \Ks\ActivityBundle\Entity\ActivityStatus($user);
        $statusType     = new \Ks\ActivityBundle\Form\ActivityStatusType();
        $statusform     = $this->createForm($statusType, $status);

        return  $this->render('KsActivityBundle:Sport:_sportChoiceForm.html.twig', array(
            'form'        => $statusform->createView(),
        ));
    }
    
    /**
     * @Route("/publishStatus", name = "ksActivity_publishStatus", options={"expose"=true} )
     */
    public function publishStatusAction()
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $user               = $this->get('security.context')->getToken()->getUser();
        $activityStatus     = new \Ks\ActivityBundle\Entity\ActivityStatus($user);

        $form = $this->createForm(new \Ks\ActivityBundle\Form\ActivityStatusType(), $activityStatus);
        
         // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $formHandler = new \Ks\ActivityBundle\Form\ActivityStatusHandler($form, $request, $em, $this->container);

        $responseDatas = $formHandler->process();
        
        //Si l'activité a été publié
        if ($responseDatas['publishResponse'] == 1) {
            //On abonne l'utilisateur à l'activité
            $activityRep->subscribeOnActivity($responseDatas['activityStatus'], $user);
            $activityDatas = $activityRep->findActivities(array(
                'activityId' => $responseDatas['activityStatus']->getId()
            ));
            
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkPublishStatusPhotoVideo($user->getId());
            
            $responseDatas['html'] = $this->render(
                'KsActivityBundle:Activity:_activity.html.twig', 
                $activityDatas
            )->getContent();
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/updateStatus/{activityId}", name = "ksActivity_updateStatus", options={"expose"=true} )
     */
    public function updateStatusAction($activityId)
    {
        $request                    = $this->get('request');
        $em                         = $this->getDoctrine()->getEntityManager();
        $activityRep                = $em->getRepository('KsActivityBundle:Activity');
        $user                       = $this->get('security.context')->getToken()->getUser();
        
        $status = $activityRep->find($activityId);
        
        if (!is_object($status) ) {
            $responseDatas['updateResponse'] = -1;
            $responseDatas['errorMessage'] = "Impossible de trouver le status";
        } else {
        
            $statusType = new \Ks\ActivityBundle\Form\ActivityStatusType();
            $statusform = $this->createForm($statusType, $status);
            $formHandler = new \Ks\ActivityBundle\Form\ActivityStatusHandler($statusform, $request, $em, $this->container);

            $responseDatas = $formHandler->process();

            //Si l'activité a été modifié
            if($responseDatas['updateResponse'] == 1) {

                $responseDatas['statusContentHtml'] = $this->render('KsActivityBundle:Activity:_activityStatus_content.html.twig', array(
                    'activity'              => $responseDatas['activityStatus'],
                ))->getContent();
            }
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;   
        
    }
     
    /**
     * @Route("/publishActivitySession/{sportId}", defaults={"clubId" = null}, requirements={"sportId" = "\d+"} )
     * @Route("/publishActivitySession/{sportId}/{clubId}", requirements={"sportId" = "\d+", "clubId" = "\d+"}, name = "ksActivity_publishActivitySession", options={"expose"=true} )
     */
    public function publishActivitySessionAction( $sportId, $clubId )
    {
        $request                    = $this->get('request');
        $em                         = $this->getDoctrine()->getEntityManager();
        $activityRep                = $em->getRepository('KsActivityBundle:Activity');
        $userRep                    = $em->getRepository('KsUserBundle:User');
        $clubRep                    = $em->getRepository('KsClubBundle:Club');
        $showcaseExposesTrophiesRep = $em->getRepository('KsTrophyBundle:ShowcaseExposesTrophies');
        $sportRep                   = $em->getRepository('KsActivityBundle:Sport');
        $userHasSportFrequencyRep   = $em->getRepository('KsUserBundle:UserHasSportFrequency');
        $user                       = $this->get('security.context')->getToken()->getUser();
        
        //appels aux services
        $leagueLevelService     = $this->get('ks_league.leagueLevelService');
        $activityService        = $this->get('ks_activity.activityService');
        $notificationService    = $this->get('ks_notification.notificationService');
        $trophyService          = $this->get('ks_trophy.trophyService');
        
        $sport = $sportRep->find($sportId);
        
        if (!is_object($sport) ) {
            throw new AccessDeniedException("Impossible de trouver le sport " . $sportId .".");
        }
        
        $postByClub = false;
        
        if( !empty( $clubId )) {
            $club = $clubRep->find( $clubId );
            if (!is_object( $club ) ) {
                throw $this->createNotFoundException("Impossible de trouver le club " . $clubId .".");
            } else {
                $postByClub = true;
            }
        }
        
        if( isset( $club ) && is_object( $club )) {
            $activitySession = new \Ks\ActivityBundle\Entity\ActivitySession();
            $activitySession->setClub( $club );
            $activitySession->setSport( $sport );
            $activitySessionType = new \Ks\ActivityBundle\Form\ActivitySessionType($sport, null, $club);  

        } else {
            $activitySession = new \Ks\ActivityBundle\Entity\ActivitySession( $user );
            $activitySession->setSport( $sport );
            $activitySessionType = new \Ks\ActivityBundle\Form\ActivitySessionType($sport, $user);  
        }
        
        $form = $this->createForm($activitySessionType, $activitySession);
        
        //Gestion fréquence /sport
        $sport = $activitySession->getSport();
        $userHasSportFrequency = $userHasSportFrequencyRep->findOneBy(array(
            "user"  => $user->getId(),
            "sport" => $sport->getId()
        ));
        if (!is_object($userHasSportFrequency)) {
            $userHasSportFrequency = new \Ks\UserBundle\Entity\UserHasSportFrequency();
            $userHasSportFrequency->setUser($user);
            $userHasSportFrequency->setSport($sport);
            $em->persist($userHasSportFrequency);
            $em->flush();
        }
        $frequencyForm = $this->createForm(new \Ks\UserBundle\Form\UserHasSportFrequencyType(), $userHasSportFrequency);
        $coachingPlanForm = $this->createForm(new \Ks\CoachingBundle\Form\CoachingPlanEventsType($user, $sport));
        
        // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $formHandler = new \Ks\ActivityBundle\Form\ActivitySessionHandler($form, $frequencyForm, $coachingPlanForm, $request, $em, $this->container, $this->get('translator'));

        $responseDatas = $formHandler->process();
        
        //Si l'activité a été publiée
        if ($responseDatas['publishResponse'] == 1) {
            $activityRep->subscribeOnActivity($responseDatas['activitySession'], $user);
            $points = $leagueLevelService->activitySessionEarnPoints($responseDatas['activitySession'], $activitySession->getUser());
            
            //Mise à jour des étoiles
            $leagueCategoryId = $user->getLeagueLevel()->getCategory()->getId();
            if( is_integer( $leagueCategoryId ) ) $leagueLevelService->leagueRankingUpdate( $leagueCategoryId );
            
            //$activityDatas = $activityRep->findActivities(array('activityId' => $responseDatas['activitySession']->getId()));
            
            /*$responseDatas['html'] = $this->render(
                'KsActivityBundle:Activity:_activity.html.twig',
                $activityDatas
            )->getContent();*/
            
            $responseDatas['activityId'] = $responseDatas['activitySession']->getId();

            //Pour permettre le partage FB de l'activité créée avec les données du GPX si utilisé
            $responseDatas['shareFacebookJsHtml'] = $this->render(
                'KsActivityBundle:Activity:_shareFacebookJs.html.twig',
                $activityRep->findActivities(array('activityId' => $responseDatas['activitySession']->getId()))
            )->getContent();
            
            //url contient le path de l'image PNG correspondant à l'activité
            $responseDatas['url']  = $this->container->getParameter('path_web') . "/img/activities/" . $responseDatas['activityId'] . "/" . $responseDatas['activityId'] . ".png";
            
            //name contient le message qui apparait en texte sur le post facebook
            $name = "";
            $translator = $this->get('translator');
            
            if ($responseDatas['activitySession']->getUser() !=null) {
                if ($responseDatas['activitySession']->getUser()->getId() == $user->getId()) {
                    $name = $name . $translator->trans('fb.i-have');
                }
                else {
                    $name = $name . $responseDatas['activitySession']->getUser()->getUsername() . ' ' . $translator->trans('fb.has');
                }
            }
            else if ($responseDatas['activitySession']->getClub() != null) {
                $name = $name . $responseDatas['activitySession']->getClub()->getId() . ' ' . $translator->trans('fb.has');
            }
            else {
                $name = $name . ' ' . $translator->trans('fb.somebody-has');
            }
            $serviceIdSport = 'sports.' . $responseDatas['activitySession']->getSport()->getCodeSport();
            $name = $name . ' ' . $translator->trans('fb.posted-one-activity-of') . " '" . $translator->trans($serviceIdSport) . "' ";

            $caption= "";
            if ($responseDatas['activitySession']->getPlace() != null) {  
                if ($responseDatas['activitySession']->getPlace()->getTownLabel() != null && $responseDatas['activitySession']->getPlace()->getTownLabel() != ""){
                    $caption = $caption . $translator->trans('fb.close-to') . ' ' . $responseDatas['activitySession']->getPlace()->getTownLabel() . ", ";
                }
                else if ($responseDatas['activitySession']->getPlace()->getRegionLabel() != null && $responseDatas['activitySession']->getPlace()->getRegionLabel() != "") {
                    $caption = $caption . $translator->trans('fb.in') . ' ' . $responseDatas['activitySession']->getPlace()->getRegionLabel();
                    if ($responseDatas['activitySession']->getPlace()->getCountryCode() != null) {
                        $caption = $caption . " (" . $responseDatas['activitySession']->getPlace()->getCountryCode() . ")";
                    }
                    $caption = $caption . ", ";
                }
                else if ($responseDatas['activitySession']->getPlace()->getFullAdress() != null && $responseDatas['activitySession']->getPlace()->getFullAdress() != "") {
                    $caption = $caption . $translator->trans('fb.close-to') . ' ' . $responseDatas['activitySession']->getPlace()->getFullAdress() . ", ";
                }
            }
            if ($responseDatas['activitySession']->getPoints() != null) {
                $caption = $caption . "+" . $points['pointsEarned'] . ' ' . $translator->trans('fb.points');
            }
            $responseDatas['name'] = $name . ' (' . $caption . ') ' . $this->container->getParameter('path_web') . $this->generateUrl('ksActivity_showActivity', array('activityId' => $responseDatas['activityId']));
            
            
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkPublishSportActivity($user->getId());
            
            //$trophyService->beginOrContinueToWinTrophy("prem_acti_indi", $user); 
            
            //Pour chaque utilisateurs qui ont participés à l'activité, on leur duplique et on leur permet de les valider
            foreach ( $responseDatas['activitySession']->getUsersWhoHaveParticipated() as $userWhoHasParticipated ) {
                if( $userWhoHasParticipated != $user ) {
                    $newActivity = $activityService->duplicateActivityForUsersWhoHaveParticipated($responseDatas['activitySession'], $userWhoHasParticipated);

                    //On lui fait gagner les points qu'il doit
                    $leagueLevelService->activitySessionEarnPoints($newActivity, $userWhoHasParticipated);

                    if( $responseDatas['activitySession']->getClub() != null ) {
                        $keepinsport = $userRep->findOneByUsername("keepinsport");
                        if( is_object( $keepinsport ) ) $from = $keepinsport;
                        else $from = $userWhoHasParticipated;
                    } else {
                        $from = $responseDatas['activitySession']->getUser();
                    }
                    
                    $userName = $from->getUsername();
                    if( $from->getUserDetail()->getFirstname() != null && $from->getUserDetail()->getLastname() ) {
                        $userName .= " (".$from->getUserDetail()->getFirstname()." ".$from->getUserDetail()->getLastname().")";
                    }
                    $notification = $notificationService->sendNotification(
                            $newActivity, 
                            $from, 
                            $userWhoHasParticipated,
                            "mustBeValidated",
                            $userName. " a indiqué que tu te trouves dans son activité");
                }
            }
            
            //Création d'un événement selon le type de l'activité si sport sélectionné pour l'afficher dans l'agenda de l'utilisateur
            if( $responseDatas['activitySession']->getEvent() != null) {
                //Si l'utilisateur a précisé une séance d'un plan d'entrainement on ne fait rien, il y a déjà un événement lié !
            }
            else {
                $event = new \Ks\EventBundle\Entity\Event();
                $event->setActivitySession($activitySession);
                $event->setContent($activitySession->getDescription());
                $event->setCreationDate(new \DateTime('now'));
                $event->setStartDate($activitySession->getIssuedAt());

                $duration = $activitySession->getDuration()->format('H') * 3600 + $activitySession->getDuration()->format('i') * 60 + $activitySession->getDuration()->format('s');
                $endDate = new \DateTime(date("Y-m-d H:i:s", strtotime($activitySession->getIssuedAt()->format("Y-m-d H:i:s")) + $duration));
                $event->setEndDate($endDate);

                $event->setIsAllDay(true);
                $event->setName("");
                $event->setPlace($activitySession->getPlace());
                $event->setSport($activitySession->getSport());
                if ($activitySession->getWasOfficial()) $event->setTypeEvent($em->getRepository('KsEventBundle:TypeEvent')->find(2));
                else $event->setTypeEvent($em->getRepository('KsEventBundle:TypeEvent')->find(1));
                $event->setUser($activitySession->getUser());
                
                //$activitySession->setEvent($event);
                //$em->persist($activitySession);
                
                $em->persist($event);
                $em->flush();
            }
            
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
    
    /**
     * @Route("/updateActivitySession/{activityId}", name = "ksActivity_updateActivitySession", options={"expose"=true} )
     */
    public function updateActivitySessionAction($activityId)
    {
        $request                    = $this->get('request');
        $em                         = $this->getDoctrine()->getEntityManager();
        $activityRep                = $em->getRepository('KsActivityBundle:Activity');
        $stateOfHealthRep           = $em->getRepository('KsActivityBundle:StateOfHealth');
        $userHasSportFrequencyRep   = $em->getRepository('KsUserBundle:UserHasSportFrequency');
        $user                       = $this->get('security.context')->getToken()->getUser();
        
        //appels aux services
        $leagueLevelService     = $this->get('ks_league.leagueLevelService');
        $activityService        = $this->get('ks_activity.activityService');
        $notificationService    = $this->get('ks_notification.notificationService');
        $trophyService          = $this->get('ks_trophy.trophyService');
        
        $activitySession = $activityRep->find($activityId);
        
        if (!is_object($activitySession) ) {
            throw new AccessDeniedException("Impossible de trouver l'activité " . $activityId .".");
        }
        
        //On récupère les personnes qui ont participés, pour pouvoir, une fois l'activité éditée, savoir quels sont les nouveaux participants
        $usersIdWhoHavesParticipated = array();
        foreach($activitySession->getUsersWhoHaveParticipated() as $userWhoHasParticipated ) {
            $usersIdWhoHavesParticipated[] = $userWhoHasParticipated->getId();
        }
 
        $coachingEvent = null;
        if (!is_null($activitySession->getEvent()) && $activitySession->getEvent()->getTypeEvent()->getId() == 5) $coachingEvent = $activitySession->getEvent(); 
        if (!is_null($coachingEvent)) {
            $enduranceSession->getEvent()->setActivitySession(null);
            $enduranceSession->setEvent(null);
            $em->persist($coachingEvent);
            $em->persist($enduranceSession);
            $em->flush();
        }
        $activitySessionType = new \Ks\ActivityBundle\Form\ActivitySessionType($activitySession->getSport(), $user, null, $activitySession->getEvent());
        
        $form = $this->createForm($activitySessionType, $activitySession);
        
        //Gestion fréquence /sport
        $sport = $activitySession->getSport();
        $userHasSportFrequency = $userHasSportFrequencyRep->findOneBy(array(
            "user"  => $user->getId(),
            "sport" => $sport->getId()
        ));
        if (!is_object($userHasSportFrequency)) {
            $userHasSportFrequency = new \Ks\UserBundle\Entity\UserHasSportFrequency();
            $userHasSportFrequency->setUser($user);
            $userHasSportFrequency->setSport($sport);
            $em->persist($userHasSportFrequency);
            $em->flush();
        }
        $frequencyForm = $this->createForm(new \Ks\UserBundle\Form\UserHasSportFrequencyType(), $userHasSportFrequency);
        $coachingPlanForm = $this->createForm(new \Ks\CoachingBundle\Form\CoachingPlanEventsType($user, $sport));
        
         // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $formHandler = new \Ks\ActivityBundle\Form\ActivitySessionHandler($form, $frequencyForm, $coachingPlanForm, $request, $em, $this->container, $this->get('translator'));

        $responseDatas = $formHandler->process();
        
        //Si l'activité a été modifié
        if($responseDatas['publishResponse'] == 1) {
            
            $leagueLevelService->activitySessionEarnPoints($responseDatas['activitySession'], $activitySession->getUser());
            
            //Mise à jour des étoiles
            $leagueCategoryId = $user->getLeagueLevel()->getCategory()->getId();
            if( is_integer( $leagueCategoryId ) ) $leagueLevelService->leagueRankingUpdate( $leagueCategoryId );
            
            //Pour chaque utilisateurs qui ont participés à l'activité, on leur duplique et on leur permet de les valider
            foreach ( $responseDatas['activitySession']->getUsersWhoHaveParticipated() as $userWhoHasParticipated ) {
                if( $userWhoHasParticipated != $user && ! in_array( $userWhoHasParticipated->getId(), $usersIdWhoHavesParticipated ) ) {
                    $newActivity = $activityService->duplicateActivityForUsersWhoHaveParticipated($responseDatas['activitySession'], $userWhoHasParticipated);
                    
                    //On lui fait gagner les points qu'il doit
                    $leagueLevelService->activitySessionEarnPoints($newActivity, $userWhoHasParticipated);
        
                    $userName = $responseDatas['activitySession']->getUser()->getUsername();
                    if( $responseDatas['activitySession']->getUser()->getUserDetail()->getFirstname() != null && $responseDatas['activitySession']->getUser()->getUserDetail()->getLastname() ) {
                        $userName .= " (".$responseDatas['activitySession']->getUser()->getUserDetail()->getFirstname()." ".$responseDatas['activitySession']->getUser()->getUserDetail()->getLastname().")";
                    }
                    
                    $notification = $notificationService->sendNotification(
                            $newActivity, 
                            $responseDatas['activitySession']->getUser(), 
                            $userWhoHasParticipated,
                            "mustBeValidated",
                            $userName. " a indiqué que tu te trouves dans son activité");
                }
            }
            
            $statesOfHealth = $stateOfHealthRep->findAll();
            
            /*$responseDatas['contentHtml'] = $this->render('KsActivityBundle:Activity:_activitySession_content.html.twig', array(
                'activity'              => $responseDatas['activitySession'],
            ))->getContent();
            
            $responseDatas['contentDetailsHtml'] = $this->render('KsActivityBundle:Activity:_activitySession_content_details.html.twig', array(
                'activity'              => $responseDatas['activitySession'],
                'statesOfHealth'        => $statesOfHealth
            ))->getContent();*/
        }
        
        $responseDatas['activityId'] = $responseDatas['activitySession']->getId();
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
    
    /**
     * @Route("/publishTeamSportSession/{sportId}", defaults={"clubId" = null}, requirements={"sportId" = "\d+"} )
     * @Route("/publishTeamSportSession/{sportId}/{clubId}", requirements={"sportId" = "\d+", "clubId" = "\d+"}, name = "ksActivity_publishTeamSportSession", options={"expose"=true} )
     */
    public function publishTeamSportSessionAction($sportId, $clubId)
    {
        $request                    = $this->get('request');
        $em                         = $this->getDoctrine()->getEntityManager();
        $sportRep                   = $em->getRepository('KsActivityBundle:Sport');
        $activityRep                = $em->getRepository('KsActivityBundle:Activity');
        $userRep                    = $em->getRepository('KsUserBundle:User');
        $clubRep                    = $em->getRepository('KsClubBundle:Club');
        $userHasSportFrequencyRep   = $em->getRepository('KsUserBundle:UserHasSportFrequency');
        $user                       = $this->get('security.context')->getToken()->getUser();
        
        
        //appels aux services
        $leagueLevelService     = $this->get('ks_league.leagueLevelService');
        $activityService        = $this->get('ks_activity.activityService');
        $notificationService    = $this->get('ks_notification.notificationService');
        $trophyService          = $this->get('ks_trophy.trophyService');
        
        $sport = $sportRep->find($sportId);
        
        $postByClub = false;
        
        if (!is_object($sport) ) {
            throw new AccessDeniedException("Impossible de trouver le sport " . $sportId .".");
        }
        
        if( !empty( $clubId )) {
            $club = $clubRep->find( $clubId );
            if (!is_object( $club ) ) {
                throw $this->createNotFoundException("Impossible de trouver le club " . $clubId .".");
            } else {
                $postByClub = true;
            }
        }
        
        if( isset( $club ) && is_object( $club )) {
            $teamSportSession = new \Ks\ActivityBundle\Entity\ActivitySessionTeamSport();
            $teamSportSession->setClub( $club );
            $teamSportSession->setSport( $sport );
            $teamSportSessionType = new \Ks\ActivityBundle\Form\ActivitySessionType($sport, null, $club);

        } else {
            $teamSportSession = new \Ks\ActivityBundle\Entity\ActivitySessionTeamSport($user);
            $teamSportSession->setSport( $sport );
            $teamSportSessionType = new \Ks\ActivityBundle\Form\ActivitySessionType($sport, $user, null);
        }
         
        $form = $this->createForm($teamSportSessionType, $teamSportSession);
        
        //Gestion fréquence /sport
        $sport = $teamSportSession->getSport();
        $userHasSportFrequency = $userHasSportFrequencyRep->findOneBy(array(
            "user"  => $user->getId(),
            "sport" => $sport->getId()
        ));
        if (!is_object($userHasSportFrequency)) {
            $userHasSportFrequency = new \Ks\UserBundle\Entity\UserHasSportFrequency();
            $userHasSportFrequency->setUser($user);
            $userHasSportFrequency->setSport($sport);
            $em->persist($userHasSportFrequency);
            $em->flush();
        }
        $frequencyForm = $this->createForm(new \Ks\UserBundle\Form\UserHasSportFrequencyType(), $userHasSportFrequency);
        $coachingPlanForm = $this->createForm(new \Ks\CoachingBundle\Form\CoachingPlanEventsType($user, $sport));
        
         // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $formHandler = new \Ks\ActivityBundle\Form\TeamSportSessionHandler($form, $frequencyForm, $coachingPlanForm, $request, $em, $this->container, $this->get('translator'));

        $responseDatas = $formHandler->process();
        
        //Si l'activité a été publié
        if($responseDatas['publishResponse'] == 1) {
            //On abonne l'utilisateur à l'activité
            $activityRep->subscribeOnActivity($responseDatas['teamSportSession'], $user);
            $points = $leagueLevelService->activitySessionEarnPoints($responseDatas['teamSportSession'], $teamSportSession->getUser());
            
            //Mise à jour des étoiles
            $leagueCategoryId = $user->getLeagueLevel()->getCategory()->getId();
            if( is_integer( $leagueCategoryId ) ) $leagueLevelService->leagueRankingUpdate( $leagueCategoryId );
            
            // on récupère les données à afficher
            $activityDatas = $activityRep->findActivities(array(
                'activityId' => $responseDatas['teamSportSession']->getId()
            ));
            
            //$responseDatas['teamSportSession']-> = $earnsPointsResult["response"] == 1 ? $earnsPointsResult["pointsEarned"] : 0;
            /*$responseDatas['html'] = $this->render(
                'KsActivityBundle:Activity:_activity.html.twig',
                $activityDatas
            )->getContent();*/
            
            $responseDatas['activityId'] = $responseDatas['teamSportSession']->getId();

            //Pour permettre le partage FB de l'activité créée avec les données du GPX si utilisé
            $responseDatas['shareFacebookJsHtml'] = $this->render(
                'KsActivityBundle:Activity:_shareFacebookJs.html.twig',
                $activityRep->findActivities(array('activityId' => $responseDatas['teamSportSession']->getId()))
            )->getContent();
            
            //url contient le path de l'image PNG correspondant à l'activité
            $responseDatas['url']  = $this->container->getParameter('path_web') . "/img/activities/" . $responseDatas['activityId'] . "/" . $responseDatas['activityId'] . ".png";
            
            //name contient le message qui apparait en texte sur le post facebook
            $name = "";
            $translator = $this->get('translator');
            
            if ($responseDatas['teamSportSession']->getUser() !=null) {
                if ($responseDatas['teamSportSession']->getUser()->getId() == $user->getId()) {
                    $name = $name . $translator->trans('fb.i-have');
                }
                else {
                    $name = $name . $responseDatas['teamSportSession']->getUser()->getUsername() . ' ' . $translator->trans('fb.has');
                }
            }
            else if ($responseDatas['teamSportSession']->getClub() != null) {
                $name = $name . $responseDatas['teamSportSession']->getClub()->getId() . ' ' . $translator->trans('fb.has');
            }
            else {
                $name = $name . ' ' . $translator->trans('fb.somebody-has');
            }
            $serviceIdSport = 'sports.' . $responseDatas['teamSportSession']->getSport()->getCodeSport();
            $name = $name . ' ' . $translator->trans('fb.posted-one-activity-of') . " '" . $translator->trans($serviceIdSport) . "' ";

            $caption= "";
            if ($responseDatas['teamSportSession']->getPlace() != null) {  
                if ($responseDatas['teamSportSession']->getPlace()->getTownLabel() != null && $responseDatas['teamSportSession']->getPlace()->getTownLabel() != ""){
                    $caption = $caption . $translator->trans('fb.close-to') . ' ' . $responseDatas['teamSportSession']->getPlace()->getTownLabel() . ", ";
                }
                else if ($responseDatas['teamSportSession']->getPlace()->getRegionLabel() != null && $responseDatas['teamSportSession']->getPlace()->getRegionLabel() != "") {
                    $caption = $caption . $translator->trans('fb.in') . ' ' . $responseDatas['teamSportSession']->getPlace()->getRegionLabel();
                    if ($responseDatas['teamSportSession']->getPlace()->getCountryCode() != null) {
                        $caption = $caption . " (" . $responseDatas['teamSportSession']->getPlace()->getCountryCode() . ")";
                    }
                    $caption = $caption . ", ";
                }
                else if ($responseDatas['teamSportSession']->getPlace()->getFullAdress() != null && $responseDatas['teamSportSession']->getPlace()->getFullAdress() != "") {
                    $caption = $caption . $translator->trans('fb.close-to') . ' ' . $responseDatas['teamSportSession']->getPlace()->getFullAdress() . ", ";
                }
            }
            if ($responseDatas['teamSportSession']->getPoints() != null) {
                $caption = $caption . "+" . $points['pointsEarned'] . ' ' . $translator->trans('fb.points');
            }
            $responseDatas['name'] = $name . ' (' . $caption . ') ' . $this->container->getParameter('path_web') . $this->generateUrl('ksActivity_showActivity', array('activityId' => $responseDatas['activityId']));
            
            
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkPublishSportActivity($user->getId());

           //$trophyService->beginOrContinueToWinTrophy("prem_acti_coll", $user); 
            
            //Pour chaque utilisateurs qui ont participés à l'activité, on leur duplique et on leur permet de les valider
            foreach ( $responseDatas['teamSportSession']->getUsersWhoHaveParticipated() as $userWhoHasParticipated ) {
                if( (!$postByClub && $userWhoHasParticipated != $user) || ($postByClub) ) {
                    $responseDatas['teamSportSession']->addUserWhoHasParticipated($user);
                    $newActivity = $activityService->duplicateActivityForUsersWhoHaveParticipated($responseDatas['teamSportSession'], $userWhoHasParticipated);
                    
                    //On lui fait gagner les points qu'il doit
                    $leagueLevelService->activitySessionEarnPoints($newActivity, $userWhoHasParticipated);
        
                    if( $responseDatas['teamSportSession']->getClub() != null ) {
                        $keepinsport = $userRep->findOneByUsername("keepinsport");
                        if( is_object( $keepinsport ) ) $from = $keepinsport;
                        else $from = $userWhoHasParticipated;
                    } else {
                        $from = $responseDatas['teamSportSession']->getUser();
                    }
                    
                    $userName = $from->getUsername();
                    if( $from->getUserDetail()->getFirstname() != null && $from->getUserDetail()->getLastname() ) {
                        $userName .= " (".$from->getUserDetail()->getFirstname()." ".$from->getUserDetail()->getLastname().")";
                    }
                    
                    $notification = $notificationService->sendNotification(
                            $newActivity, 
                            $from, 
                            $userWhoHasParticipated,
                            "mustBeValidated",
                            $userName. " a indiqué que tu te trouves dans son activité");
                }
            }
            
            foreach ( $responseDatas['teamSportSession']->getOpponentsWhoHaveParticipated() as $opponentWhoHasParticipated ) {
                if( (!$postByClub && $opponentWhoHasParticipated != $user) || ($postByClub) ) {
                    $responseDatas['teamSportSession']->addUserWhoHasParticipated($user);
                    $newActivity = $activityService->duplicateActivityForOpponentsWhoHaveParticipated($responseDatas['teamSportSession'], $opponentWhoHasParticipated);
                            
                    //On lui fait gagner les points qu'il doit
                    $leagueLevelService->activitySessionEarnPoints($newActivity, $opponentWhoHasParticipated);
        
                    if( $responseDatas['teamSportSession']->getClub() != null ) {
                        $keepinsport = $userRep->findOneByUsername("keepinsport");
                        if( is_object( $keepinsport ) ) $from = $keepinsport;
                        else $from = $userWhoHasParticipated;
                    } else {
                        $from = $responseDatas['teamSportSession']->getUser();
                    }
                    
                    $userName = $from->getUsername();
                    if( $from->getUserDetail()->getFirstname() != null && $from->getUserDetail()->getLastname() ) {
                        $userName .= " (".$from->getUserDetail()->getFirstname()." ".$from->getUserDetail()->getLastname().")";
                    }
                    
                    $notification = $notificationService->sendNotification(
                            $newActivity, 
                            $from, 
                            $opponentWhoHasParticipated,
                            "mustBeValidated",
                            $userName. " a indiqué que tu te trouves dans son activité");
                }
            }
            
            //Création d'un événement selon le type de l'activité si sport sélectionné pour l'afficher dans l'agenda de l'utilisateur
            if( $responseDatas['teamSportSession']->getEvent() != null) {
                //Si l'utilisateur a précisé une séance d'un plan d'entrainement on ne fait rien, il y a déjà un événement lié !
            }
            else {
                $event = new \Ks\EventBundle\Entity\Event();
                $event->setActivitySession($teamSportSession);
                $event->setContent($teamSportSession->getDescription());
                $event->setCreationDate(new \DateTime('now'));
                $event->setStartDate($teamSportSession->getIssuedAt());

                $duration = $teamSportSession->getDuration()->format('H') * 3600 + $teamSportSession->getDuration()->format('i') * 60 + $teamSportSession->getDuration()->format('s');
                $endDate = new \DateTime(date("Y-m-d H:i:s", strtotime($teamSportSession->getIssuedAt()->format("Y-m-d H:i:s")) + $duration));
                $event->setEndDate($endDate);

                $event->setIsAllDay(true);
                $event->setName("");
                $event->setPlace($teamSportSession->getPlace());
                $event->setSport($teamSportSession->getSport());
                if ($teamSportSession->getWasOfficial()) $event->setTypeEvent($em->getRepository('KsEventBundle:TypeEvent')->find(2));
                else $event->setTypeEvent($em->getRepository('KsEventBundle:TypeEvent')->find(1));
                $event->setUser($teamSportSession->getUser());
                
                //$teamSportSession->setEvent($event);
                //$em->persist($teamSportSession);
                
                $em->persist($event);
                $em->flush();
            }
        }
        
        
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
    
    /**
     * @Route("/updateTeamSportSession/{activityId}", name = "ksActivity_updateTeamSportSession", options={"expose"=true} )
     */
    public function updateTeamSportSessionAction($activityId)
    {
        $request                    = $this->get('request');
        $em                         = $this->getDoctrine()->getEntityManager();
        $activityRep                = $em->getRepository('KsActivityBundle:Activity');
        $user                       = $this->get('security.context')->getToken()->getUser();
        $stateOfHealthRep           = $em->getRepository('KsActivityBundle:StateOfHealth');
        $userHasSportFrequencyRep   = $em->getRepository('KsUserBundle:UserHasSportFrequency');
        
        //appels aux services
        $leagueLevelService     = $this->get('ks_league.leagueLevelService');
        $activityService        = $this->get('ks_activity.activityService');
        $notificationService    = $this->get('ks_notification.notificationService');
        $trophyService          = $this->get('ks_trophy.trophyService');
        
        $teamSportSession = $activityRep->find($activityId);
        
        if (!is_object($teamSportSession) ) {
            throw new AccessDeniedException("Impossible de trouver l'activité " . $activityId .".");
        }
        
        //On récupère les personnes qui ont participés, pour pouvoir, une fois l'activité éditée, savoir quels sont les nouveaux participants
        $usersIdWhoHavesParticipated = array();
        foreach($teamSportSession->getUsersWhoHaveParticipated() as $userWhoHasParticipated ) {
            $usersIdWhoHavesParticipated[] = $userWhoHasParticipated->getId();
        }
        
        $opponentsIdWhoHavesParticipated = array();
        foreach($teamSportSession->getOpponentsWhoHaveParticipated() as $opponentWhoHasParticipated ) {
            $opponentsIdWhoHavesParticipated[] = $opponentWhoHasParticipated->getId();
        }
        
 
        $teamSportSessionType = new \Ks\ActivityBundle\Form\ActivitySessionType($teamSportSession->getSport(), $user);

        //$parameters = $request->request->get($teamSportSessionType->getName());
        
        $form = $this->createForm($teamSportSessionType, $teamSportSession);
        
        //Gestion fréquence /sport
        $sport = $teamSportSession->getSport();
        $userHasSportFrequency = $userHasSportFrequencyRep->findOneBy(array(
            "user"  => $user->getId(),
            "sport" => $sport->getId()
        ));
        if (!is_object($userHasSportFrequency)) {
            $userHasSportFrequency = new \Ks\UserBundle\Entity\UserHasSportFrequency();
            $userHasSportFrequency->setUser($user);
            $userHasSportFrequency->setSport($sport);
            $em->persist($userHasSportFrequency);
            $em->flush();
        }
        $frequencyForm = $this->createForm(new \Ks\UserBundle\Form\UserHasSportFrequencyType(), $userHasSportFrequency);
        $coachingPlanForm = $this->createForm(new \Ks\CoachingBundle\Form\CoachingPlanEventsType($user, $sport));
        
         // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $formHandler = new \Ks\ActivityBundle\Form\TeamSportSessionHandler($form, $frequencyForm, $coachingPlanForm, $request, $em, $this->container, $this->get('translator'));

        $responseDatas = $formHandler->process();
        
        //Si l'activité a été modifié
        if($responseDatas['publishResponse'] == 1) {
            
            $leagueLevelService->activitySessionEarnPoints($responseDatas['teamSportSession'], $teamSportSession->getUser());
            
            //Mise à jour des étoiles
            $leagueCategoryId = $user->getLeagueLevel()->getCategory()->getId();
            if( is_integer( $leagueCategoryId ) ) $leagueLevelService->leagueRankingUpdate( $leagueCategoryId );
            
            //Pour chaque utilisateurs qui ont participés à l'activité, on leur duplique et on leur permet de les valider
            foreach ( $responseDatas['teamSportSession']->getUsersWhoHaveParticipated() as $userWhoHasParticipated ) {
                if( $userWhoHasParticipated != $user && ! in_array( $userWhoHasParticipated->getId(), $usersIdWhoHavesParticipated ) ) {
                    $newActivity = $activityService->duplicateActivityForUsersWhoHaveParticipated($responseDatas['teamSportSession'], $userWhoHasParticipated);
                    
                    //On lui fait gagner les points qu'il doit
                    $leagueLevelService->activitySessionEarnPoints($newActivity, $userWhoHasParticipated);
        
                    $userName = $responseDatas['teamSportSession']->getUser()->getUsername();
                    if( $responseDatas['teamSportSession']->getUser()->getUserDetail()->getFirstname() != null && $responseDatas['teamSportSession']->getUser()->getUserDetail()->getLastname() ) {
                        $userName .= " (".$responseDatas['teamSportSession']->getUser()->getUserDetail()->getFirstname()." ".$responseDatas['teamSportSession']->getUser()->getUserDetail()->getLastname().")";
                    }
                    $notification = $notificationService->sendNotification(
                            $newActivity, 
                            $responseDatas['teamSportSession']->getUser(), 
                            $userWhoHasParticipated,
                            "mustBeValidated",
                            $userName. " a indiqué que tu te trouves dans son activité");
                }
            }
            
            foreach ( $responseDatas['teamSportSession']->getOpponentsWhoHaveParticipated() as $opponentWhoHasParticipated ) {
                if( $opponentWhoHasParticipated != $user && ! in_array( $opponentWhoHasParticipated->getId(), $opponentsIdWhoHavesParticipated ) ) {
                    $newActivity = $activityService->duplicateActivityForOpponentsWhoHaveParticipated($responseDatas['teamSportSession'], $opponentWhoHasParticipated);
                            
                    //On lui fait gagner les points qu'il doit
                    $leagueLevelService->activitySessionEarnPoints($newActivity, $opponentWhoHasParticipated);
        
                    $userName = $responseDatas['teamSportSession']->getUser()->getUsername();
                    if( $responseDatas['teamSportSession']->getUser()->getUserDetail()->getFirstname() != null && $responseDatas['teamSportSession']->getUser()->getUserDetail()->getLastname() ) {
                        $userName .= " (".$responseDatas['teamSportSession']->getUser()->getUserDetail()->getFirstname()." ".$responseDatas['teamSportSession']->getUser()->getUserDetail()->getLastname().")";
                    }
                    $notification = $notificationService->sendNotification(
                            $newActivity, 
                            $responseDatas['teamSportSession']->getUser(), 
                            $opponentWhoHasParticipated,
                            "mustBeValidated",
                            $userName. " a indiqué que tu te trouves dans son activité");
                }
            }
            
            $statesOfHealth = $stateOfHealthRep->findAll();
            
            // on récupère les données à afficher
            $activityDatas = $activityRep->findActivities(array(
                'activityId' => $activityId
            ));
            
            /*$responseDatas['contentHtml'] = $this->render(
                'KsActivityBundle:Activity:_activityBloc.html.twig',
                array_merge($activityDatas, array('statesOfHealth' => $statesOfHealth))
            )->getContent();*/
        }
        
        $responseDatas['activityId'] = $responseDatas['teamSportSession']->getId();
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
     
    /**
     * @Route("/publishEnduranceSession/{sportId}", defaults={"clubId" = null}, requirements={"sportId" = "\d+"} )
     * @Route("/publishEnduranceSession/{sportId}/{clubId}", requirements={"sportId" = "\d+", "clubId" = "\d+"}, name = "ksActivity_publishEnduranceSession", options={"expose"=true} )
     */
    public function publishEnduranceSessionAction($sportId, $clubId)
    {
        $request                    = $this->get('request');
        $em                         = $this->getDoctrine()->getEntityManager();
        $activityRep                = $em->getRepository('KsActivityBundle:Activity');
        $clubRep                    = $em->getRepository('KsClubBundle:Club');
        $userRep                    = $em->getRepository('KsUserBundle:User');
        $showcaseExposesTrophiesRep = $em->getRepository('KsTrophyBundle:ShowcaseExposesTrophies');
        $sportRep                   = $em->getRepository('KsActivityBundle:Sport');
        $userHasSportFrequencyRep   = $em->getRepository('KsUserBundle:UserHasSportFrequency');
        $user                       = $this->get('security.context')->getToken()->getUser();
        
        //appels aux services
        $leagueLevelService     = $this->get('ks_league.leagueLevelService');
        $activityService        = $this->get('ks_activity.activityService');
        $notificationService    = $this->get('ks_notification.notificationService');
        $trophyService          = $this->get('ks_trophy.trophyService');
        
        $sport = $sportRep->find($sportId);
        
        if (!is_object($sport) ) {
            throw new AccessDeniedException("Impossible de trouver le sport " . $sportId .".");
        }
        
        $postByClub = false;
        
        if( !empty( $clubId )) {
            $club = $clubRep->find( $clubId );
            if (!is_object( $club ) ) {
                throw $this->createNotFoundException("Impossible de trouver le club " . $clubId .".");
            } else {
                $postByClub = true;
            }
        }
        
        if( isset( $club ) && is_object( $club )) {
            
            switch($sport->getSportType()->getLabel()) {
                case "endurance" :
                    $enduranceSession = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth();
                    break;

                case "endurance_under_water" :
                    $enduranceSession = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceUnderWater();
                    break;
            }
            
            $enduranceSession->setClub( $club );
            $enduranceSession->setSport( $sport );
            $enduranceSessionType = new \Ks\ActivityBundle\Form\ActivitySessionType($sport, null, $club);  

        } else {
            
            switch($sport->getSportType()->getLabel()) {
                case "endurance" :
                    $enduranceSession = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth($user);
                    break;

                case "endurance_under_water" :
                    $enduranceSession = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceUnderWater($user);
                    break;
            }
        }
        $enduranceSession->setSport( $sport );
        $enduranceSessionType = new \Ks\ActivityBundle\Form\ActivitySessionType($sport, $user);
                    
        $form = $this->createForm($enduranceSessionType, $enduranceSession);
        
        //Gestion fréquence /sport
        $userHasSportFrequency = $userHasSportFrequencyRep->findOneBy(array(
            "user"  => $user->getId(),
            "sport" => $sport->getId()
        ));
        if (!is_object($userHasSportFrequency)) {
            $userHasSportFrequency = new \Ks\UserBundle\Entity\UserHasSportFrequency();
            $userHasSportFrequency->setUser($user);
            $userHasSportFrequency->setSport($sport);
            $em->persist($userHasSportFrequency);
            $em->flush();
        }
        
        $frequencyForm = $this->createForm(new \Ks\UserBundle\Form\UserHasSportFrequencyType(), $userHasSportFrequency);
        $coachingPlanForm = $this->createForm(new \Ks\CoachingBundle\Form\CoachingPlanEventsType($user, $sport));
        
        // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $formHandler = new \Ks\ActivityBundle\Form\EnduranceSessionHandler($form, $frequencyForm, $coachingPlanForm, $request, $em, $this->container, $this->get('translator'));

        $responseDatas = $formHandler->process();
        
        //Si l'activité a été publié
        if ($responseDatas['publishResponse'] == 1) {
                        
            $activityRep->subscribeOnActivity($responseDatas['enduranceSession'], $user);
            $points = $leagueLevelService->activitySessionEarnPoints($responseDatas['enduranceSession'], $enduranceSession->getUser());
            
            //Mise à jour des étoiles
            $leagueCategoryId = $user->getLeagueLevel()->getCategory()->getId();
            if( is_integer( $leagueCategoryId ) ) $leagueLevelService->leagueRankingUpdate( $leagueCategoryId );
            
            $responseDatas['activityId'] = $responseDatas['enduranceSession']->getId();
            
            //url contient le path de l'image PNG correspondant à l'activité
            $responseDatas['url']  = $this->container->getParameter('path_web') . "/img/activities/" . $responseDatas['activityId'] . "/" . $responseDatas['activityId'] . ".png";

            //Pour permettre le partage FB de l'activité créée avec les données du GPX si utilisé
            $responseDatas['shareFacebookJsHtml'] = $this->render(
                'KsActivityBundle:Activity:_shareFacebookJs.html.twig',
                $activityRep->findActivities(array('activityId' => $responseDatas['enduranceSession']->getId()))
            )->getContent();
            
            //name contient le message qui apparait en texte sur le post facebook
            $name = "";
            $translator = $this->get('translator');
            
            if ($responseDatas['enduranceSession']->getUser() !=null) {
                if ($responseDatas['enduranceSession']->getUser()->getId() == $user->getId()) {
                    $name = $name . $translator->trans('fb.i-have');
                }
                else {
                    $name = $name . $responseDatas['enduranceSession']->getUser()->getUsername() . ' ' . $translator->trans('fb.has');
                }
            }
            else if ($responseDatas['enduranceSession']->getClub() != null) {
                $name = $name . $responseDatas['enduranceSession']->getClub()->getId() . ' ' . $translator->trans('fb.has');
            }
            else {
                $name = $name . ' ' . $translator->trans('fb.somebody-has');
            }
            $serviceIdSport = 'sports.' . $responseDatas['enduranceSession']->getSport()->getCodeSport();
            $name = $name . ' ' . $translator->trans('fb.posted-one-activity-of') . " '" . $translator->trans($serviceIdSport) . "' ";

            $caption= "";
            if ($responseDatas['enduranceSession']->getPlace() != null) {  
                if ($responseDatas['enduranceSession']->getPlace()->getTownLabel() != null && $responseDatas['enduranceSession']->getPlace()->getTownLabel() != ""){
                    $caption = $caption . $translator->trans('fb.close-to') . ' ' . $responseDatas['enduranceSession']->getPlace()->getTownLabel() . ", ";
                }
                else if ($responseDatas['enduranceSession']->getPlace()->getRegionLabel() != null && $responseDatas['enduranceSession']->getPlace()->getRegionLabel() != "") {
                    $caption = $caption . $translator->trans('fb.in') . ' ' . $responseDatas['enduranceSession']->getPlace()->getRegionLabel();
                    if ($responseDatas['enduranceSession']->getPlace()->getCountryCode() != null) {
                        $caption = $caption . " (" . $responseDatas['enduranceSession']->getPlace()->getCountryCode() . ")";
                    }
                    $caption = $caption . ", ";
                }
                else if ($responseDatas['enduranceSession']->getPlace()->getFullAdress() != null && $responseDatas['enduranceSession']->getPlace()->getFullAdress() != "") {
                    $caption = $caption . $translator->trans('fb.close-to') . ' ' . $responseDatas['enduranceSession']->getPlace()->getFullAdress() . ", ";
                }
            }
            if ($responseDatas['enduranceSession']->getPoints() != null) {
                $caption = $caption . "+" . $points['pointsEarned'] . ' ' . $translator->trans('fb.points');
            }
            $responseDatas['name'] = $name . ' (' . $caption . ') ' . $this->container->getParameter('path_web') . $this->generateUrl('ksActivity_showActivity', array('activityId' => $responseDatas['activityId']));
            
            //$trophyService->beginOrContinueToWinTrophy("prem_acti_indi", $user); 
            
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkPublishSportActivity($user->getId());
            
            //gain d'un bagde de distance s'il ne l'a pas déjà
            $trophiesToWin = array();
            $distance       = $responseDatas['enduranceSession']->getDistance();
            $wasCompetition = $responseDatas['enduranceSession']->getWasOfficial();
            if( $distance >= 42 && $wasCompetition ) $trophiesToWin[] = "cap_marathon";
            elseif( $distance >= 21 && $wasCompetition ) $trophiesToWin[] = "cap_semi";
            elseif( $distance >= 10 && $wasCompetition ) $trophiesToWin[] = "cap_10";
            elseif( $distance >= 5 && $wasCompetition ) $trophiesToWin[] = "cap_5";
            
            foreach( $trophiesToWin as $trophyCode) {
                //$trophyService->beginOrContinueToWinTrophy($trophyCode, $user);     
            }
            
            //Pour chaque utilisateurs qui ont participés à l'activité, on leur duplique et on leur permet de les valider
            foreach ($responseDatas['enduranceSession']->getUsersWhoHaveParticipated() as $userWhoHasParticipated ) {
                if ( (!$postByClub && $userWhoHasParticipated != $user) || ($postByClub) ) {
                    $newActivity = $activityService->duplicateActivityForUsersWhoHaveParticipated($responseDatas['enduranceSession'], $userWhoHasParticipated);

                    //On lui fait gagner les points qu'il doit
                    $leagueLevelService->activitySessionEarnPoints($newActivity, $userWhoHasParticipated);
                    
                    if( $responseDatas['enduranceSession']->getClub() != null ) {
                        $keepinsport = $userRep->findOneByUsername("keepinsport");
                        if( is_object( $keepinsport ) ) $from = $keepinsport;
                        else $from = $userWhoHasParticipated;
                    } else {
                        $from = $responseDatas['enduranceSession']->getUser();
                    }

                    $userName = $from->getUsername();
                    if( $from->getUserDetail()->getFirstname() != null && $from->getUserDetail()->getLastname() ) {
                        $userName .= " (".$from->getUserDetail()->getFirstname()." ".$from->getUserDetail()->getLastname().")";
                    }
                    
                    $notificationService->sendNotification(
                        $newActivity,
                        $from,
                        $from,
                        "mustBeValidated",
                        $userName. " a indiqué que tu te trouves dans son activité"
                    );
                }
            }
            
            $em->persist($enduranceSession);
            $em->flush();
            
            //Création d'un événement selon le type de l'activité si sport sélectionné pour l'afficher dans l'agenda de l'utilisateur
            if( $responseDatas['enduranceSession']->getEvent() != null) {
                //Si l'utilisateur a précisé une séance d'un plan d'entrainement on ne fait rien, il y a déjà un événement lié !
            }
            else {
                $event = new \Ks\EventBundle\Entity\Event();
                $event->setActivitySession($enduranceSession);
                $event->setContent($enduranceSession->getDescription());
                $event->setCreationDate(new \DateTime('now'));
                $event->setStartDate($enduranceSession->getIssuedAt());

                $duration = $enduranceSession->getDuration()->format('H') * 3600 + $enduranceSession->getDuration()->format('i') * 60 + $enduranceSession->getDuration()->format('s');
                $endDate = new \DateTime(date("Y-m-d H:i:s", strtotime($enduranceSession->getIssuedAt()->format("Y-m-d H:i:s")) + $duration));
                $event->setEndDate($endDate);

                $event->setIsAllDay(true);
                $event->setName("");
                $event->setPlace($enduranceSession->getPlace());
                $event->setSport($enduranceSession->getSport());
                if ($enduranceSession->getWasOfficial()) $event->setTypeEvent($em->getRepository('KsEventBundle:TypeEvent')->find(2));
                else $event->setTypeEvent($em->getRepository('KsEventBundle:TypeEvent')->find(1));
                $event->setUser($enduranceSession->getUser());
                
                //$enduranceSession->setEvent($event);
                //$em->persist($enduranceSession);
                
                $em->persist($event);
                $em->flush();
            }
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
    
    /**
     * @Route("/updateEnduranceSession/{activityId}", name = "ksActivity_updateEnduranceSession", options={"expose"=true} )
     */
    public function updateEnduranceSessionAction($activityId)
    {
        $request                    = $this->get('request');
        $em                         = $this->getDoctrine()->getEntityManager();
        $activityRep                = $em->getRepository('KsActivityBundle:Activity');
        $stateOfHealthRep           = $em->getRepository('KsActivityBundle:StateOfHealth');
        $userHasSportFrequencyRep   = $em->getRepository('KsUserBundle:UserHasSportFrequency');
        $user                       = $this->get('security.context')->getToken()->getUser();
        
        //appels aux services
        $leagueLevelService     = $this->get('ks_league.leagueLevelService');
        $activityService        = $this->get('ks_activity.activityService');
        $notificationService    = $this->get('ks_notification.notificationService');
        $trophyService          = $this->get('ks_trophy.trophyService');
        
        $enduranceSession = $activityRep->find($activityId);
        
        if (!is_object($enduranceSession) ) {
            throw new AccessDeniedException("Impossible de trouver l'activité " . $activityId .".");
        }
        
        //On récupère les personnes qui ont participés, pour pouvoir, une fois l'activité éditée, savoir quels sont les nouveaux participants
        $usersIdWhoHavesParticipated = array();
        foreach($enduranceSession->getUsersWhoHaveParticipated() as $userWhoHasParticipated ) {
            $usersIdWhoHavesParticipated[] = $userWhoHasParticipated->getId();
        }
 
        $coachingEvent = null;
        if (!is_null($enduranceSession->getEvent()) && $enduranceSession->getEvent()->getTypeEvent()->getId() == 5) $coachingEvent = $enduranceSession->getEvent();
        if (!is_null($coachingEvent)) {
            $enduranceSession->getEvent()->setActivitySession(null);
            $enduranceSession->setEvent(null);
            $em->persist($coachingEvent);
            $em->persist($enduranceSession);
            $em->flush();
        }
        $enduranceSessionType = new \Ks\ActivityBundle\Form\ActivitySessionType($enduranceSession->getSport(), $user, null, $coachingEvent);

        //$parameters = $request->request->get($teamSportSessionType->getName());
        
        $form = $this->createForm($enduranceSessionType, $enduranceSession);
        
        //Gestion fréquence /sport
        $sport = $enduranceSession->getSport();
        $userHasSportFrequency = $userHasSportFrequencyRep->findOneBy(array(
            "user"  => $user->getId(),
            "sport" => $sport->getId()
        ));
        if (!is_object($userHasSportFrequency)) {
            $userHasSportFrequency = new \Ks\UserBundle\Entity\UserHasSportFrequency();
            $userHasSportFrequency->setUser($user);
            $userHasSportFrequency->setSport($sport);
            $em->persist($userHasSportFrequency);
            $em->flush();
        }
        $frequencyForm = $this->createForm(new \Ks\UserBundle\Form\UserHasSportFrequencyType(), $userHasSportFrequency);
        $coachingPlanForm = $this->createForm(new \Ks\CoachingBundle\Form\CoachingPlanEventsType($user, $sport));
        
        // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $formHandler = new \Ks\ActivityBundle\Form\EnduranceSessionHandler($form, $frequencyForm, $coachingPlanForm, $request, $em, $this->container, $this->get('translator'));

        $responseDatas = $formHandler->process();
        
        //Si l'activité a été modifié
        if($responseDatas['publishResponse'] == 1) {
            
            $leagueLevelService->activitySessionEarnPoints($responseDatas['enduranceSession'], $enduranceSession->getUser());
            
            //Mise à jour des étoiles
            $leagueCategoryId = $user->getLeagueLevel()->getCategory()->getId();
            if( is_integer( $leagueCategoryId ) ) $leagueLevelService->leagueRankingUpdate( $leagueCategoryId );
            
            //Pour chaque utilisateurs qui ont participés à l'activité, on leur duplique et on leur permet de les valider
            foreach ( $responseDatas['enduranceSession']->getUsersWhoHaveParticipated() as $userWhoHasParticipated ) {
                if( $userWhoHasParticipated != $user && ! in_array( $userWhoHasParticipated->getId(), $usersIdWhoHavesParticipated ) ) {
                    $newActivity = $activityService->duplicateActivityForUsersWhoHaveParticipated($responseDatas['enduranceSession'], $userWhoHasParticipated);
                    
                    //On lui fait gagner les points qu'il doit
                    $leagueLevelService->activitySessionEarnPoints($newActivity, $userWhoHasParticipated);
        
                    $userName = $responseDatas['enduranceSession']->getUser()->getUsername();
                    if( $responseDatas['enduranceSession']->getUser()->getUserDetail()->getFirstname() != null && $responseDatas['enduranceSession']->getUser()->getUserDetail()->getLastname() ) {
                        $userName .= " (".$responseDatas['enduranceSession']->getUser()->getUserDetail()->getFirstname()." ".$responseDatas['enduranceSession']->getUser()->getUserDetail()->getLastname().")";
                    }
                    $notification = $notificationService->sendNotification(
                            $newActivity, 
                            $responseDatas['enduranceSession']->getUser(), 
                            $userWhoHasParticipated,
                            "mustBeValidated",
                            $userName. " a indiqué que tu te trouves dans son activité");
                }
            }
            /*$responseDatas['contentHtml'] = $this->render(
                'KsActivityBundle:Activity:_activityBloc.html.twig',
                array_merge(
                    $activityRep->findActivities(array('activityId' => $responseDatas['enduranceSession']->getId())),
                    array('statesOfHealth' => $stateOfHealthRep->findAll())
                )
            )->getContent();*/
        }
        else {
            var_dump($responseDatas['errors']);
        }
        
        //var_dump($responseDatas);
        
        $responseDatas['activityId'] = $responseDatas['enduranceSession']->getId();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
    
    /**
     * @Route("/publishLink", name = "ksActivity_publishLink", options={"expose"=true} )
     */
    public function publishLinkAction()
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep = $em->getRepository('KsActivityBundle:Activity');
        $user               = $this->get('security.context')->getToken()->getUser();
        $activityStatus     = new \Ks\ActivityBundle\Entity\ActivityStatus($user);

        $form = $this->createForm(new \Ks\ActivityBundle\Form\ActivityLinkType(), $activityStatus);
        
        // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $statusHandler = new \Ks\ActivityBundle\Form\ActivityStatusHandler($form, $request, $em, $this->container);

        $responseDatas = $statusHandler->process();
        
        //Si l'activité a été publié
        if($responseDatas['publishResponse'] == 1) {
            //On abonne l'utilisateur à l'activité
            $activityRep->subscribeOnActivity($responseDatas['activityStatus'], $user);
            
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkPublishStatusPhotoVideo($user->getId());
            
            // on récupère les données à afficher
            $activityDatas = $activityRep->findActivities(array(
                'activityId' => $responseDatas['activityStatus']->getId()
            ));
            
            $responseDatas['html'] = $this->render(
                'KsActivityBundle:Activity:_activity.html.twig',
                $activityDatas
            )->getContent();
        }
                
        
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
  
    /**
     * @Route("/publishAlbumPhoto", name = "ksActivity_publishAlbumPhoto", options={"expose"=true} )
     */
    public function publishAlbumPhotoAction()
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $user               = $this->get('security.context')->getToken()->getUser();
        $photo              = new \Ks\ActivityBundle\Entity\ActivityStatus($user);

        $responseDatas = array();
        $responseDatas["publishResponse"] = 1;
        
        $parameters = $request->request->all();
        $description = isset( $parameters["description"] ) ? $parameters["description"] : "" ;
        $localisation = isset( $parameters["localisation"] ) ? $parameters["localisation"] : array(
            "fullAdress"        => "",
            "countryArea"       => "",
            "countryCode"       => "",
            "town"              => "",
            "latitude"          => "",
            "longitude"         => ""
        ) ;
        $uploadedPhotos = isset( $parameters["uploadedPhotos"] ) ? $parameters["uploadedPhotos"] : array() ;
                
        $uploadDirRelative = $this->container->get('templating.helper.assets')->getUrl('uploads');
        $uploadDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $uploadDirRelative . "/photos/";
        $imgDirRelative = $this->container->get('templating.helper.assets')->getUrl('img');
        $activitiesDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/activities/";
        //var_dump($uploadedPhotos);
        
        $photoAlbum = new\Ks\ActivityBundle\Entity\PhotoAlbum($user);
        $photoAlbum->setDescription($description);
        
        if( ! empty ( $localisation["fullAdress"] ) ) {
            $place = new \Ks\EventBundle\Entity\Place();
            $place->setFullAdress( $localisation["fullAdress"] );
            $place->setRegionLabel( $localisation["countryArea"] );
            $place->setCountryCode( $localisation["countryCode"] );
            $place->setLatitude( $localisation["latitude"] );
            $place->setLongitude( $localisation["longitude"] );
            $place->setTownLabel( $localisation["town"] );
            $em->persist($place);
            
            $photoAlbum->setPlace( $place );
        }
        $em->persist( $photoAlbum );
        $em->flush();
        
        if ($photo) {
            foreach( $uploadedPhotos as $key => $uploadedPhoto ) {
                
                //On récupère l'extension de la photo
                $ext = explode('.', $uploadedPhoto);
                $ext = array_pop($ext);
                $ext = "." . $ext;

                $activityId = $photoAlbum->getId();
                
                $activityDirAbsolute = $activitiesDirAbsolute.$activityId."/";

                //On crée le dossier qui contient les images de l'article s'il n'existe pas
                if (! is_dir( $activityDirAbsolute ) ) mkdir( $activityDirAbsolute );
                
                $activityOriginalPhotosDirAbsolute = $activityDirAbsolute . 'original/';
                if (! is_dir( $activityOriginalPhotosDirAbsolute ) ) mkdir($activityOriginalPhotosDirAbsolute);

                $activityThumbnailPhotosDirAbsolute = $activityDirAbsolute . 'thumbnail/';
                if (! is_dir( $activityThumbnailPhotosDirAbsolute ) ) mkdir($activityThumbnailPhotosDirAbsolute);
                
                $photo = new \Ks\ActivityBundle\Entity\Photo($ext);
                $em->persist($photo);
                $em->flush();

                $photoPath = $photo->getId().$ext;

                //On la déplace les photos originales et redimentionnés
                $renameOriginale = rename( $uploadDirAbsolute."original/"  . $uploadedPhoto, $activityOriginalPhotosDirAbsolute.$photoPath );
                $renameThumbnail = rename( $uploadDirAbsolute."thumbnail/" . $uploadedPhoto, $activityThumbnailPhotosDirAbsolute.$photoPath );
                
                if( $renameOriginale && $renameThumbnail){
                    $movePhotoResponse = 1;

                    $photo->setActivity($photoAlbum);
                    $photoAlbum->addPhoto($photo);
                    $em->persist($photo);
                    $em->persist($photoAlbum);
                    
                } else {
                    $em->remove($photo);
                    $movePhotoResponse = -1;
                    $responseDatas["publishResponse"] = -1;
                }

                $responseDatas["movePhotosResponse"][$key] = $movePhotoResponse;
            }
        } else {
            $responseDatas["publishResponse"] = -1;
        }
        
        if ($responseDatas["publishResponse"] == 1) {
            //On abonne l'utilisateur à l'activité
            $activityRep->subscribeOnActivity( $photoAlbum, $user );
            
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkPublishStatusPhotoVideo($user->getId());
            
            $activityDatas = $activityRep->findActivities(array(
                'activityId' => $photoAlbum->getId()
            ));
            
            $em->flush();
            $responseDatas['html'] = $this->render(
                'KsActivityBundle:Activity:_activity.html.twig',
                $activityDatas
            )->getContent();
        } else {
            //On supprime l'entité photo album
            $em->remove($photoAlbum);
            $em->flush();
        }
                
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
    
    /**
     * 
     * @Route("/disableActivity/{activityId}", requirements={"activityId" = "\d+"}, name = "ksActivity_disableActivity", options={"expose"=true} )
     * @param int $activityId 
     */
    public function disableActivityAction($activityId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $responseDatas = array();
        
        $activity = $activityRep->find($activityId);
        
        if ( ! is_object($activity) ) {
            $responseDatas["responseDelete"] = -1;
            $responseDatas["errorMessage"] = "Impossible de désactiver cette activité.";
        } else {
            //Si c'est lui qui a posté l'activité
            if($activity->getUser()->getId() == $user->getId()) {

                if (! $activity->getIsDisabled() ) {
                    $activityRep->disableActivity($activity);
                    $responseDatas["responseDisable"] = 1;

                    //On vérifie que la désactivation a bien été prise en compte
                    $responseDatas["isDisabled"] = $activity->getIsDisabled();
                } else {
                    $responseDatas["responseDisable"] = -1;
                    $responseDatas["errorMessage"] = "L'activité est déjà désactivée";
                }
            } else {
                $responseDatas["responseDisable"] = -1;
                $responseDatas["errorMessage"] = "Vous n'avez pas le droit de désactiver cette activité";
            }
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/deleteActivity/{activityId}", requirements={"activityId" = "\d+"}, name = "ksActivity_deleteActivity", options={"expose"=true} )
     * @param int $activityId 
     */
    public function deleteActivityAction($activityId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $clubManagersRep = $em->getRepository('KsClubBundle:UserManageClub');
        
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $responseDatas = array();
        
        $activity = $activityRep->find($activityId);
        
        if ( ! is_object($activity) ) {
            $responseDatas["responseDelete"] = -1;
            $responseDatas["errorMessage"] = "Impossible de supprimer cette activité.";
        } else {
            $canDelete = false;
            
            $activityUser = $activity->getUser();
            $activityClub = $activity->getClub();
            
            if( is_object( $activityUser ) ) {
                if($activityUser->getId() == $user->getId()) 
                    $canDelete = true;
            } elseif ( is_object( $activityClub ) ){
                //On vérifie que l'utilisateur est manager du club
                $userManageClub = $clubManagersRep->findOneBy(array(
                    "user"  => $user->getId(),
                    "club"  => $activityClub->getId()
                ));
                if( is_object( $userManageClub ) )
                    $canDelete = true;
            }
            
            //Si l'utilisateur est keepinsport ou ziiicos possibilité de supprimer les activités
            //FIXME : pas beau mais urgent...
            if ($user->getId() == '1' || $user->getId() == '7') {
                $canDelete = true;
            }
            
            //Si c'est lui qui a posté l'activité
            if($canDelete) {

                if (! $activity->getIsDisabled() ) {
                    $activityRep->deleteActivity($activity);
                    $responseDatas["responseDelete"] = 1;

                    //On vérifie que la désactivation a bien été prise en compte
                    $responseDatas["isDisabled"] = $activity->getIsDisabled();
                } else {
                    $responseDatas["responseDelete"] = -1;
                    $responseDatas["errorMessage"] = "L'activité est déjà désactivée";
                }
            } else {
                $responseDatas["responseDelete"] = -1;
                $responseDatas["errorMessage"] = "Vous n'avez pas le droit de supprimer cette activité";   
            }
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/hideActivity/{activityId}", requirements={"activityId" = "\d+"}, name = "ksActivity_hideActivity", options={"expose"=true} )
     * @param int $activityId 
     */
    public function hideActivityAction($activityId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $responseDatas = array();
        
        $activity = $activityRep->find($activityId);
        
        if (!is_object($activity) ) {
            $this->createNotFoundException("Impossible de trouver l'activité " . $activityId .".");
        }

        //Si cet utilisateur n'a pas déjà caché cette activitée
        if(! $user->getActivitiesIHaveHidden()->contains($activity) ) {
            $activityRep->hideActivity($activity, $user);
            $responseDatas["responseHide"] = 1;

            //On vérifie que modification a bien été prise en compte
            //$responseDatas["isHide"] = $activityRep->isHidden($activity, $user);
        } else {
            $responseDatas["responseHide"] = -1;
            $responseDatas["errorMessage"] = "Cette actualité est déjà cachée.";
            
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/warnActivityLikeDisturbing/{activityId}", requirements={"activityId" = "\d+"}, name = "ksActivity_warnActivityLikeDisturbing", options={"expose"=true} )
     * @param int $activityId 
     */
    public function warnActivityLikeDisturbingAction($activityId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        //services
        $notificationService    = $this->get('ks_notification.notificationService');
        
        $activity = $activityRep->find($activityId);
        
        if (!is_object($activity) ) {
            $impossibleActivityMsg = $this->get('translator')->trans('impossible-to-find-activity-%activityId%', array('%activityId%' => $activityId));
            throw new AccessDeniedException($impossibleActivityMsg);
        }
        
        $responseDatas = array();
        
        //Si l'utilisateur n'a pas déjà signalé l'activité comme dérengeante
        if ( ! $activityRep->haveAlreadyWarnedLikeDisturbing($activity, $user) ) {
            $activityRep->warnActivityLikeDisturbing($activity, $user);
            
            if ($activity->getUser() != $user) {
                $notificationService->sendNotification($activity, $user, $activity->getUser(), "warning");  
            }
            $activity->numWarnings          = (int)$activityRep->getNumWarningsOnActivity($activity);
            $responseDatas["responseWarn"]  = 1;
        } else {
            $responseDatas["responseWarn"] = -1;
            $voteActivityMsg = $this->get('translator')->trans('you-voted-on-this-activity');
            $responseDatas["errorMessage"] = $voteActivityMsg;
        }
                
        $responseDatas["warnLink"] = $this->render('KsActivityBundle:Activity:_warnLink.html.twig', array(
            'activity'          => $activity,
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/removeWarnActivityLikeDisturbing/{activityId}", requirements={"activityId" = "\d+"}, name = "ksActivity_removeWarnActivityLikeDisturbing", options={"expose"=true} )
     * @param int $activityId 
     */
    public function removeWarnActivityLikeDisturbingAction($activityId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $warningRep     = $em->getRepository('KsActivityBundle:ActivityIsDisturbing');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $responseDatas = array();
        
        $activity = $activityRep->find($activityId);
        
        if (!is_object($activity) ) {
            $impossibleActivityMsg = $this->get('translator')->trans('impossible-to-find-activity-%activityId%', array('%activityId%' => $activityId));
            throw new AccessDeniedException($impossibleActivityMsg);
        }
        
        if ( $activityRep->haveAlreadyWarnedLikeDisturbing($activity, $user) ) {
            $activityIsDisturbing = $warningRep->find(array("activity" => $activityId, "user" => $user->getId()));
        
            if (!is_object($activityIsDisturbing) ) {
                $impossibleVoteMsg = $this->get('translator')->trans('impossible-to-find-vote-%activityId%', array('%activityId%' => $activityId));
                throw new AccessDeniedException($impossibleVoteMsg);
            }

            $activityRep->removeWarnActivityLikeDisturbing($activityIsDisturbing);
            $activity->numWarnings          = (int)$activityRep->getNumWarningsOnActivity($activity);
            $responseDatas["responseWarn"]  = 1;
        } else {
            $responseDatas["responseWarn"] = -1;
            $youAlreadyRetireMsg = $this->get('translator')->trans('you-already-retire-your-warn-on-activity');
            $responseDatas["errorMessage"] = $youAlreadyRetireMsg;
        }
        
        $responseDatas["warnLink"] = $this->render('KsActivityBundle:Activity:_warnLink.html.twig', array(
            'activity'          => $activity,
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
     /**
     * 
     * @Route("/parseLink", name = "ksActivity_parseLink", options={"expose"=true} )
     * @param string $linkToParse 
     */
    public function parseLinkAction()
    {
        set_time_limit(0);
        error_reporting(E_ALL  & ~E_NOTICE & ~E_WARNING);
        //ini_set('display_errors', 0);

        $url = urldecode($_POST['link']);
        $url = $this->checkValues($url);
        $return_array = array();

        $base_url = substr($url,0, strpos($url, "/",8));
        $relative_url = substr($url,0, strrpos($url, "/")+1);

        // Get Data
        $cc = new \Ks\ActivityBundle\Entity\Url();
        $string = $cc->get($url);
        $string = str_replace(array("\n","\r","\t",'</span>','</div>'), '', $string);

        $string = preg_replace('/(<(div|span)\s[^>]+\s?>)/',  '', $string);
        if (mb_detect_encoding($string, "UTF-8") != "UTF-8") 
            $string = utf8_encode($string);

        if($string != "") {
            // Parse Title
            $nodes = $this->extract_tags( $string, 'title' );
            $return_array['title'] = trim($nodes[0]['contents']);

            // Parse Base
            $base_override = false; 
            $base_regex = '/<base[^>]*'.'href=[\"|\'](.*)[\"|\']/Ui';
            preg_match_all($base_regex, $string, $base_match, PREG_PATTERN_ORDER);
            if(strlen($base_match[1][0]) > 0)
            {
                $base_url = $base_match[1][0];
                $base_override = true; 
            }

            // Parse Description
            $return_array['description'] = '';

            $metas = $this->extract_tags( $string, 'meta' );
            //var_dump($metas);
            $viewUrl = "";
            foreach($metas as $meta)
            {
                if ( strtolower($meta['attributes']['name']) == 'description' && empty( $return_array['description'] ) ) {
                    $return_array['description'] = trim($meta['attributes']['content']);
                }
                
                if ( strtolower($meta['attributes']['itemprop']) == "description" && empty( $return_array['description'] ) ) {
                    $return_array['description'] = trim($meta['attributes']['content']);
                }
                
                if ( strtolower($meta['attributes']['name']) == 'twitter:player' ) {
                        $viewUrl = $meta['attributes']['content'];
                    }
            }

            $images = array();
            

            //Si on parse un lien youtube
            //if( strpos($url, "youtube") !== FALSE ) {
                //var_dump($nodes);
                foreach($metas as $meta) {
                    if (strtolower($meta['attributes']['property']) == 'og:image') {
                        $images[] = array("img" => $meta['attributes']['content']);
                    }
                }
            /*}
            elseif( strpos($url, "dailymotion") !== FALSE ) {
                $links = $this->extract_tags( $string, 'link' );
                
                foreach($links as $l) {
                    if ($l['attributes']['itemprop'] == 'thumbnailUrl')
                        $images[] = array("img" => $l['attributes']['href']);
                    if (strtolower($l['attributes']['rel']) == 'image_src') {
                        //$img = new \Ks\ActivityBundle\Entity\Url();
                        //$img_string = $img->get($l['attributes']['href']);
                        $images[] = array("img" => $l['attributes']['href']);
                    }
                }               
            }
            else {*/
                // Parse Images
                $images_array = $this->extract_tags( $string, 'img' );
                //var_dump($images_array);

                for ($i=0;$i<=sizeof($images_array);$i++)
                {      
                    $img = trim(@$images_array[$i]['attributes']['src']);
                    $width = preg_replace("/[^0-9.]/", '', $images_array[$i]['attributes']['width']);
                    $height = preg_replace("/[^0-9.]/", '', $images_array[$i]['attributes']['height']);

                    $ext = trim(pathinfo($img, PATHINFO_EXTENSION));

                    if($img && $ext != 'gif') 
                    {
                        if (substr($img,0,7) == 'http://')
                            ;
                        else	if (substr($img,0,1) == '/' || $base_override)
                            $img = $base_url . $img;
                        else 
                            $img = $relative_url . $img;

                        if ($width == '' && $height == '')
                        {
                            $details = @getimagesize($img);

                            if(is_array($details))
                            {
                                list($width, $height, $type, $attr) = $details;
                            } 
                        }
                        $width = intval($width);
                        $height = intval($height);


                        if ($width > 199 || $height > 199 )
                        {
                            if (
                                (($width > 0 && $height > 0 && (($width / $height) < 3) && (($width / $height) > .2)) 
                                    || ($width > 0 && $height == 0 && $width < 700) 
                                    || ($width == 0 && $height > 0 && $height < 700)
                                ) 
                                && strpos($img, 'logo') === false )
                            {
                                $images[] = array("img" => $img, "width" => $width, "height" => $height, 'area' =>  ($width * $height),'offset' => $images_array[$i]['offset']);
                            }
                        }

                    }
                }
            //}

            $return_array['images'] = array_values(($images));
            $return_array['viewUrl'] = $viewUrl;
            $return_array['total_images'] = count($return_array['images']); 
            $return_array["parseResponse"] = 1;
        } else {
            $return_array["parseResponse"] = -1;
            $return_array["errorMessage"] = "Impossible de récupérer la page";
        }
        
        $response = new Response(json_encode($return_array));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;  
    }
    
    function checkValues($value)
    {
        $value = trim($value);
        if (get_magic_quotes_gpc())
        {
            $value = stripslashes($value);
        }
        $value = strtr($value, array_flip(get_html_translation_table(HTML_ENTITIES)));
        $value = strip_tags($value);
        $value = htmlspecialchars($value);
        return $value;
    }


    function extract_tags( $html, $tag, $selfclosing = null, $return_the_entire_tag = false, $charset = 'ISO-8859-1' ){

        if ( is_array($tag) ){
            $tag = implode('|', $tag);
        }

        //If the user didn't specify if $tag is a self-closing tag we try to auto-detect it
        //by checking against a list of known self-closing tags.
        $selfclosing_tags = array( 'area', 'base', 'basefont', 'br', 'hr', 'input', 'img', 'link', 'meta', 'col', 'param' );
        if ( is_null($selfclosing) ){
            $selfclosing = in_array( $tag, $selfclosing_tags );
        }

        //The regexp is different for normal and self-closing tags because I can't figure out 
        //how to make a sufficiently robust unified one.
        if ( $selfclosing ){
            $tag_pattern = 
                '@<(?P<tag>'.$tag.')			# <tag
                (?P<attributes>\s[^>]+)?		# attributes, if any
                \s*/?>					# /> or just >, being lenient here 
                @xsi';
        } else {
            $tag_pattern = 
                '@<(?P<tag>'.$tag.')			# <tag
                (?P<attributes>\s[^>]+)?		# attributes, if any
                \s*>					# >
                (?P<contents>.*?)			# tag contents
                </(?P=tag)>				# the closing </tag>
                @xsi';
        }

        $attribute_pattern = 
            '@
            (?P<name>\w+)							# attribute name
            \s*=\s*
            (
                (?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)	# a quoted value
                |							# or
                (?P<value_unquoted>[^\s"\']+?)(?:\s+|$)			# an unquoted value (terminated by whitespace or EOF) 
            )
            @xsi';

        //Find all tags 
        if ( !preg_match_all($tag_pattern, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) ){
            //Return an empty array if we didn't find anything
            return array();
        }

        $tags = array();
        foreach ($matches as $match){

            //Parse tag attributes, if any
            $attributes = array();
            if ( !empty($match['attributes'][0]) ){ 

                if ( preg_match_all( $attribute_pattern, $match['attributes'][0], $attribute_data, PREG_SET_ORDER ) ){
                    //Turn the attribute data into a name->value array
                    foreach($attribute_data as $attr){
                        if( !empty($attr['value_quoted']) ){
                            $value = $attr['value_quoted'];
                        } else if( !empty($attr['value_unquoted']) ){
                            $value = $attr['value_unquoted'];
                        } else {
                            $value = '';
                        }

                        //Passing the value through html_entity_decode is handy when you want
                        //to extract link URLs or something like that. You might want to remove
                        //or modify this call if it doesn't fit your situation.
                        $value = html_entity_decode( $value, ENT_QUOTES, $charset );

                        $attributes[$attr['name']] = $value;
                    }
                }

            }

            $tag = array(
                'tag_name' => $match['tag'][0],
                'offset' => $match[0][1], 
                'contents' => !empty($match['contents'])?$match['contents'][0]:'', //empty for self-closing tags
                'attributes' => $attributes, 
            );
            if ( $return_the_entire_tag ){
                $tag['full_tag'] = $match[0][0]; 			
            }

            $tags[] = $tag;
        }

        return $tags;
    }
    
    
    /*Action which permit to update google agenda when the user is logging*/
    public function syncWithGoogleAgendaSilentAction($accessToken)
    {   
        //$_SESSION['cal_token']  = $accessToken;
        
        $user                   = $this->get('security.context')->getToken()->getUser();
        $agenda                 = $user->getAgenda();
        $em                     = $this->get('doctrine')->getEntityManager();
        
        $my_calendar            = 'http://www.google.com/calendar/feeds/default/private/full';

        $eventFeed              = null;

 
        //Authentification aupres du service
        /*$client = \Zend_Gdata_AuthSub::getHttpClient($accessToken);
        $cal = new \Zend_Gdata_Calendar($client);
        //Requête pour récupérer les event postérieur
        $query = $cal->newEventQuery();
        $query->setUser('default');
        $query->setVisibility('private');
        $query->setProjection('full');
        $query->setOrderby('starttime');
        //Chargera par défaut même les événements postérieurs
        $query->setFutureevents(false);

        try {
            $eventFeed = $cal->getCalendarEventFeed($query);
        } catch (\Zend_Gdata_App_Exception $e) {
            echo "Error: " . $e->getMessage();
        }

        $aNotifications = array();
        $aNotifications['updated'] = 0;
        $aNotifications['added'] = 0;
        
        if(!empty($eventFeed)){
            
            foreach($eventFeed as $key => $event){
                //google infos
            $startTime = $event->when[0]->startTime;
            $startTime = new \DateTime($startTime);
            $startTime = $startTime->format("Y-m-d H:i:s");
            $endTime = $event->when[0]->endTime;
            $endTime = new \DateTime($endTime);
            $endTime = $endTime->format("Y-m-d H:i:s");
            $nameGoogle = $event->title;
            $contentGoogle = $event->content;
            //Pour chaque événement google, on vérifie si celui existe en base 
            $googleEventBDD = $em->getRepository('KsEventBundle:GoogleEvent')->findOneBy(array("id_url_event"=>$event->id));
          
            if($googleEventBDD!=null){
                //Récupération de l'événement en question 
                    $eventBDD = $em->getRepository('KsEventBundle:Event')->findOneByIsGoogleEvent($googleEventBDD->getId());
                    if (!is_object($eventBDD) ) {
                            throw new AccessDeniedException("Impossible de trouver l'événement avec l'identifiant google : '".$googleEventBDD->getId()."' ");
                    }
                    $dateStartBDD = $eventBDD->getStartDate();
                    $dateStartBDD = $eventBDD->getStartDate()->format("Y-m-d H:i:s");
                    getEndDate();
                    $dateEndBDD = $eventBDD->getEndDate()->format("Y-m-d H:i:s");
                    $nameBDD = $eventBDD->getName();
                    $contentBDD = $eventBDD->getContent();
                    //Détection ici des changements 
                    if($dateStartBDD != $startTime || 
                        $dateEndBDD != $endTime ||
                        $nameBDD != $nameGoogle ||
                        $contentBDD != $contentGoogle   
                    ){
                        $dateGoogle = new \DateTime($event->updated);
                        $dateLastModificationsGoogle = $dateGoogle->setTimeZone(new \DateTimeZone("Europe/Paris"));
                        $dateLastModificationsGoogle = $dateLastModificationsGoogle->format("Y-m-d H:i:s");
                        $dateLastModificationsBDD = $eventBDD->getLastModificationDate()->format("Y-m-d H:i:s");
                        //l'événement doit etre synchronisé si la date de modification de l'événement keepin est > date google 
                        if($dateLastModificationsBDD < $dateLastModificationsGoogle){
                            //update google event
                            $googleEventBDD->setName($nameGoogle);
                            $googleEventBDD->setIdUrlEvent($event->id);
                            $em->persist($googleEventBDD);
                            $em->flush();

                            $eventBDD->setName($nameGoogle);
                            if(isset($contentGoogle)){
                                $eventBDD->setContent($contentGoogle);  
                            }
                            //$event->setCreationDate(new \DateTime('now'));
                            $startTime = new \DateTime($startTime);
                            $eventBDD->setStartDate($startTime);
                            $endTime = new \DateTime($endTime);
                            $eventBDD->setEndDate($endTime);
                            $eventBDD->setLastModificationDate(new \DateTime('now'));
                            $eventBDD->setUser($user);
                            $typeEvent = $em->getRepository('KsEventBundle:TypeEvent')->findOneBy(array("nom_type"=>"event_google"));
                            if($typeEvent!=null){
                                $eventBDD->setTypeEvent($typeEvent);
                            }
                            $eventBDD->setIsGoogleEvent($googleEventBDD);
                            $em->persist($eventBDD);
                            $em->flush();
                            $agendaHasEvent = $em->getRepository('KsAgendaBundle:AgendaHasEvents')->findOneBy(array("agenda"=>$agenda->getId(),"event"=>$eventBDD->getId()));
                            if (!is_object($agendaHasEvent) ) {
                                throw new AccessDeniedException("Impossible de trouver l'événement ".$eventBDD->getId()." de votre agenda ".$agenda->getId()." ");
                            }
                            $agendaHasEvent->setAgenda($agenda);
                            $agendaHasEvent->setEvent($eventBDD);
                            $em->persist($agendaHasEvent);
                            $em->flush();

                            $aNotifications['updated'] = $aNotifications['updated']+1;

                        }
                    }

                }else{
                    //update google event
                    $googleEvent = new \Ks\EventBundle\Entity\GoogleEvent();
                    $eventBDD = new \Ks\EventBundle\Entity\Event();
                    $agendaHasEvent = new \Ks\AgendaBundle\Entity\AgendaHasEvents($agenda, $eventBDD);

                    $googleEvent->setName($nameGoogle);
                    $googleEvent->setIdUrlEvent($event->id);
                    $em->persist($googleEvent);
                    $em->flush();

                    $eventBDD->setName($nameGoogle);
                    if(isset($contentGoogle)){
                        $eventBDD->setContent($contentGoogle);  
                    }
                    //$event->setCreationDate(new \DateTime('now'));
                    $startTime = new \DateTime($startTime);
                    $eventBDD->setStartDate($startTime);
                    $endTime = new \DateTime($endTime);
                    $eventBDD->setEndDate($endTime);
                    $eventBDD->setLastModificationDate(new \DateTime('now'));
                    $eventBDD->setUser($user);
                    $typeEvent = $em->getRepository('KsEventBundle:TypeEvent')->findOneBy(array("nom_type"=>"event_google"));
                    if($typeEvent!=null){
                        $eventBDD->setTypeEvent($typeEvent);
                    }
                    $eventBDD->setIsGoogleEvent($googleEvent);
                    $em->persist($eventBDD);
                    $em->flush();
      
                    $em->persist($agendaHasEvent);
                    $em->flush();

                    $aNotifications['added'] = $aNotifications['added']+1;

                }

            }

            //On créé une notfication pour dire que la synchro s'est bien passé
            $notification = new \Ks\NotificationBundle\Entity\Notification();
            $notification->setCreatedAt(new \DateTime('now'));
            $notification->setGotAnAnswer(0);
            $notification->setText("Synchronisation avec votre agenda KeepInSport : ".$aNotifications['added']." événements ajoutés et ".$aNotifications['updated']." mis à jour");
            $notification->setNeedAnAnswer(1);
            $notification->setOwner($user);
            $notification->setReadAt(new \DateTime('now'));
            $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName("message");
            $notification->setType($notificationType);
            $em->persist($notification);
            $em->flush(); 
            
        }*/
  
        

    }
    
    /*Action which permit to update google agenda when the user is logging*/
    public function syncWithKeepInAgendaSilentAction($accessToken)
    {   

        $user                       = $this->get('security.context')->getToken()->getUser();
        $agenda                     = $user->getAgenda();
        $em                         = $this->getDoctrine()->getEntityManager();
        $request                    = $this->getRequest();
        //$_SESSION['cal_token']      = $accessToken;
        $aNotifications             = array();
        $aNotifications['updated']  = 0;
        $aNotifications['added']    = 0;
        
        
        
        /*if (isset($accessToken)) {
            $client = \Zend_Gdata_AuthSub::getHttpClient($accessToken);
            $cal = new \Zend_Gdata_Calendar($client);
        }*/

        //on récupère tous les événements du la BDD
        $agendaHasEvents = $em->getRepository('KsAgendaBundle:AgendaHasEvents')->findBy(array("agenda"=>$agenda->getId()));

        foreach($agendaHasEvents as $key => $agendaHasEvent){
            
            $event = $agendaHasEvent->getEvent();
            $startDateBDD = $event->getStartDate()->format("Y-m-d H:i:s");
            $endDateBDD = $event->getEndDate()->format("Y-m-d H:i:s");
            $eventNameBDD = $event->getName();
            $eventContentBDD = $event->getContent();
            if($event->getIsGoogleEvent()){
              $googleEventBDD = $event->getIsGoogleEvent();
              $idUrlEvent = $googleEventBDD->getIdUrlEvent();
              try {
                 $eventGoogle = $cal->getCalendarEventEntry($idUrlEvent);
                 //il faut vérifier si l'événement n'as pas subit de modifications
                 $startTime = $eventGoogle->when[0]->startTime;
                 $endTime = $eventGoogle->when[0]->endTime;
                 $startTime = new \DateTime($startTime);
                 $startDateGoogle = $startTime->format("Y-m-d H:i:s");
                 $endTime = new \DateTime($endTime);
                 $endDateGoogle = $endTime->format("Y-m-d H:i:s");
                 $eventNameGoogle = $eventGoogle->title;
                 $eventContentGoogle = $eventGoogle->content;
                 //il y a une différence entre le bdd et la calendirer google
                 if($startDateBDD != $startDateGoogle || 
                    $endDateBDD != $endDateGoogle ||
                    $eventNameBDD != $eventNameGoogle ||
                    $eventContentBDD != $eventContentGoogle   
                    ){
                        $dateGoogle = new \DateTime($eventGoogle->updated);
                        $dateLastModificationsGoogle = $dateGoogle->setTimeZone(new \DateTimeZone("Europe/Paris"));
                        $dateLastModificationsGoogle = $dateLastModificationsGoogle->format("Y-m-d H:i:s");
                        $dateLastModificationsBDD = $event->getLastModificationDate()->format("Y-m-d H:i:s");
                        //l'événement doit etre synchronisé si la date de modification de l'événement keepin est > date google 
                        if($dateLastModificationsBDD > $dateLastModificationsGoogle){
                            //Suppression de l'evenement Keepin pour le remplacer coté google 
                            $eventGoogle->delete();
                            // creation coté google calendar
                            $eventGoogle = $cal->newEventEntry();
                            $eventGoogle->title = $cal->newTitle($eventNameBDD);
                            //$event->where = array($cal->newWhere("Mountain View, California"));
                            if(isset($eventContentBDD)){
                                $eventGoogle->content = $cal->newContent($eventContentBDD);
                            }
                            $startDateTime = new \DateTime($startDateBDD);
                            $startDate = $startDateTime->format("Y-m-d");
                            $startTime = $startDateTime->format("H:i");
                            $endDateTime = new \DateTime($endDateBDD);
                            $endDate = $endDateTime->format("Y-m-d");
                            $endTime = $endDateTime->format("H:i");
                            $tzOffset = "+02";
                            $when = $cal->newWhen();
                            $when->startTime = "{$startDate}T{$startTime}:00.000{$tzOffset}:00";
                            $when->endTime = "{$endDate}T{$endTime}:00.000{$tzOffset}:00";
                            $eventGoogle->when = array($when);
                            $newEvent = $cal->insertEvent($eventGoogle);
                            //Coté bdd
                            $googleEventBDD->setName($event->getName());
                            $googleEventBDD->setIdUrlEvent($newEvent->id);
                            /*$em->persist($googleEventBDD);
                            $em->flush();*/
                            //on le lis a l'événement courant
                            /*$eventK = $em->getRepository('KsEventBundle:Event')->find($event->getId());
                            if($eventK!=null){
                                $isGoogleEvent  = $eventK->getIsGoogleEvent();
                                if($isGoogleEvent==null){
                                    $eventK->setIsGoogleEvent($googleEventBDD);
                                }

                            }*/

                            //$event->setIsGoogleEvent($googleEventBDD);
                            $em->persist($googleEventBDD);
                            $em->flush();
                            
                            $aNotifications['updated'] = $aNotifications['updated']+1;

                    }
                 }               
              }catch (Exception $e) {
                 //Ne trouve plus un événement google qui est en base
                 // creation coté google calendar
                //$googleEventBDD = new \Ks\EventBundle\Entity\GoogleEvent();
                /*$eventGoogle = $cal->newEventEntry();
                $eventGoogle->title = $cal->newTitle($eventNameBDD);
                //$event->where = array($cal->newWhere("Mountain View, California"));
                if(isset($eventContentBDD)){
                    $eventGoogle->content = $cal->newContent($eventContentBDD);
                }
                $startDateTime = new \DateTime($startDateBDD);
                $startDate = $startDateTime->format("Y-m-d");
                $startTime = $startDateTime->format("H:i");
                $endDateTime = new \DateTime($endDateBDD);
                $endDate = $endDateTime->format("Y-m-d");
                $endTime = $endDateTime->format("H:i");
                $tzOffset = "+02";
                $when = $cal->newWhen();
                $when->startTime = "{$startDate}T{$startTime}:00.000{$tzOffset}:00";
                $when->endTime = "{$endDate}T{$endTime}:00.000{$tzOffset}:00";
                $eventGoogle->when = array($when);
                $newEvent = $cal->insertEvent($eventGoogle);
                //Coté bdd
                $googleEventBDD->setName($event->getName());
                $googleEventBDD->setIdUrlEvent($newEvent->id);
                $event->setIsGoogleEvent($googleEventBDD);
                $em->persist($event);
                $em->flush();
                $aNotifications['updated'] = $aNotifications['updated']+1;*/
                  
              }
              
            }else{
                 // creation coté google calendar
                $googleEventBDD = new \Ks\EventBundle\Entity\GoogleEvent();
                $eventGoogle = $cal->newEventEntry();
                $eventGoogle->title = $cal->newTitle($eventNameBDD);
                //$event->where = array($cal->newWhere("Mountain View, California"));
                if(isset($eventContentBDD)){
                    $eventGoogle->content = $cal->newContent($eventContentBDD);
                }
                $startDateTime = new \DateTime($startDateBDD);
                $startDate = $startDateTime->format("Y-m-d");
                $startTime = $startDateTime->format("H:i");
                $endDateTime = new \DateTime($endDateBDD);
                $endDate = $endDateTime->format("Y-m-d");
                $endTime = $endDateTime->format("H:i");
                $tzOffset = "+02";
                $when = $cal->newWhen();
                $when->startTime = "{$startDate}T{$startTime}:00.000{$tzOffset}:00";
                $when->endTime = "{$endDate}T{$endTime}:00.000{$tzOffset}:00";
                $eventGoogle->when = array($when);
                $newEvent = $cal->insertEvent($eventGoogle);
                //Coté bdd
                $googleEventBDD->setName($event->getName());
                $googleEventBDD->setIdUrlEvent($newEvent->id);
                $event->setIsGoogleEvent($googleEventBDD);
                $em->persist($event);
                $em->flush();
                $aNotifications['added'] = $aNotifications['added']+1;  
            }

        }
        
        //On créé une notfication pour dire que la synchro s'est bien passé
        $notification = new \Ks\NotificationBundle\Entity\Notification();
        $notification->setCreatedAt(new \DateTime('now'));
        $notification->setGotAnAnswer(0);
        $notification->setText("Synchronisation avec votre agenda Google : ".$aNotifications['added']." événements ajoutés et ".$aNotifications['updated']." mis à jour");
        $notification->setNeedAnAnswer(1);
        $notification->setOwner($user);
        $notification->setReadAt(new \DateTime('now'));
        $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName("message");
        $notification->setType($notificationType);
        $em->persist($notification);
        $em->flush();

    }
    
    
    /**
     * @Route("/all_state_of_health", name = "ksAllStateOfHealth" )
    */
    public function getAllStateOfHealth() {

        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {
            
            $em = $this->getDoctrine()->getEntityManager();
            $statesOfHealth = $em->getRepository('KsActivityBundle:StateOfHealth')->findAll();
 
            $aStateOfHealth = array();
            foreach($statesOfHealth as $stateOfHealth){
                $aStateOfHealth[$stateOfHealth->getId()] = $stateOfHealth->getName();
            }

            $response = new Response(json_encode(array('aStateOfHealth' => $aStateOfHealth)));
            
             //« Content-type » json pour le retour
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }


    }
    
     /**
     * @Route("/all_weather", name = "ksAllWeather" )
    */
    public function getAllWeather() {
        
        $request = $this->container->get('request');
        
        if ($request->isXmlHttpRequest()) {
            
            $em = $this->getDoctrine()->getEntityManager();
            $weathers = $em->getRepository('KsActivityBundle:Weather')->findAll();
            $aWeathers = array();
            foreach($weathers as $weather){
                $aWeathers[$weather->getId()] = $weather->getName();
            }

            $response = new Response(json_encode(array('weather' => $aWeathers)));
            
             //« Content-type » json pour le retour
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }
        
    }
    
     /**
     * @Route("/get_activity_by_id", name = "getActivityById" )
    */
    public function getActivityById() {
        
        $request = $this->container->get('request');
        
        
        if ($request->isXmlHttpRequest()) {
            $idActivity = $request->request->get('idActivity');
            $em = $this->getDoctrine()->getEntityManager();
            
            if(is_numeric($idActivity)){
                $activity = $em->getRepository('KsActivityBundle:ActivitySession')->find($idActivity); 
                if($activity != null){
                    $event    = $em->getRepository('KsEventBundle:Event')->findOneBy(array("activitySession"=>$idActivity));
                    $description = $event->getContent();
                    $startDate = $event->getStartDate()->format("Y-m-d H:i:s");
                    $aActivity[] =  array(
                            'id'              => $activity->getId(),
                            'label'           => $activity->getLabel(),
                            'duration'        => $activity->getDuration()->format("H:i:s"),
                            'description'     => $description,  
                            'startDate'       => $startDate,  
                        );
                    
                }
            }
            $response = new Response(json_encode(array('activity' => $aActivity)));
            
             //« Content-type » json pour le retour
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }
        
    }
    
    
     /**
     * @Route("/validate_and_activate_activity", name = "ksEditAndValidateActivity" )
    */
    public function editAndValidateActivity() {
        
        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {
            $idActivity = $request->request->get('idActivity');
            $name = $request->request->get('name');
            $idSport = $request->request->get('idSport');
            $idStateOfHealth = $request->request->get('idStateOfHealth');
            $idWeather = $request->request->get('idWeather');
            $duration = $request->request->get('duration');
            $startDate = $request->request->get('startDate');
            
            $description = $request->request->get('description');
            
            
            $em = $this->getDoctrine()->getEntityManager();

            if(is_numeric($idActivity)){
                $activity = $em->getRepository('KsActivityBundle:ActivitySession')->find($idActivity);
                
                if (!is_object($activity) ) {
                    throw new AccessDeniedException("Impossible de trouver l'activté ".$idActivity."");
                }
                
                $sport    = $em->getRepository('KsActivityBundle:Sport')->find($idSport); 
                if (!is_object($sport) ) {
                    throw new AccessDeniedException("Impossible de trouver le sport ".$idSport."");
                }
                
                $stateOfHealth    = $em->getRepository('KsActivityBundle:StateOfHealth')->find($idStateOfHealth); 
                if (!is_object($stateOfHealth) ) {
                    throw new AccessDeniedException("Impossible de trouver l'état de santé ".$idStateOfHealth."");
                }
                
                $weather    = $em->getRepository('KsActivityBundle:Weather')->find($idWeather); 
                if (!is_object($weather) ) {
                    throw new AccessDeniedException("Impossible de trouver la météo ".$idWeather."");
                }
                
                $event    = $em->getRepository('KsEventBundle:Event')->findOneBy(array("activitySession"=>$idActivity));
                if (!is_object($event) ) {
                    throw new AccessDeniedException("Impossible de trouver l'événement associé à l'activité ".$idActivity."");
                }
                
                $split = preg_split("|/|",$startDate);
   
                $day    = $split[0];
                $month  = $split[1];
                $rest   = $split[2];
                $split2 = preg_split("| |",$rest);
                $year   = $split2[0];
                $rest2  = $split2[1];
                $split3 = preg_split("|:|",$rest2);
                $hour   = $split3[0];
                $min    = $split3[1];

                $startDate = new \DateTime("$year-$month-$day $hour:$min:00");
                
                                $splitTime          = preg_split("|:|",$duration);
                $hourDuration       = $splitTime[0];
                $minDuration        = $splitTime[1];
                
                
                $endDate = new \DateTime("$year-$month-$day $hour:$min:00");
                $interval = 'PT'.$hourDuration.'H'.$minDuration.'M';
                $i = new \DateInterval( $interval );
                date_add($endDate, $i);

                
                
                
                
                //$endDate = 

                $duration =  new \DateTime($duration);
                
                $event->setName($name);
                $event->setContent($description);
                $event->setStartDate($startDate);
                $event->setEndDate($endDate);
               
                $em->persist($event);
                $em->flush();
                
                $activity->setLabel($name);
                $activity->setDuration($duration);
                $activity->setSport($sport);
                $activity->setStateOfHealth($stateOfHealth);
                $activity->setWeather($weather);
                
                
                $activity->setIsValidate(true);
                $activity->setEvent($event);
               
                $em->persist($activity);
                $em->flush();
                
                $update = true;    
                
            }
            $response = new Response(json_encode(array('update' => $update)));
            
             //« Content-type » json pour le retour
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }
        
    }
    
     /**
     * Pour l'upload
     *
     * @Route("/imagesUpload/{uploadDirName}", name="ksActivity_imagesUpload", options={"expose"=true})
     * @Template()
     */
    public function imagesUploadAction ($uploadDirName) {
        
        $request    = $this->getRequest();
        
        $responseDatas = array();
        $responseDatas["uploadResponse"] = 1;
        
        // If you want to ignore the uploaded files, 
        // set $demo_mode to true;

        $demo_mode = false;
        $uploadDirRelative = $this->container->get('templating.helper.assets')->getUrl('uploads');
        $uploadDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $uploadDirRelative ."/" . $uploadDirName ."/";
        $allowed_ext = array('jpg','jpeg','png','gif');

        if(array_key_exists('pic',$_FILES) && $_FILES['pic']['error'] == 0 ){
	
            $pic = $_FILES['pic'];

            $ext = explode('.', $pic['name']);
            $ext = array_pop($ext);
            
            if(!in_array(strtolower($ext),$allowed_ext)){
                    $responseDatas["uploadResponse"] = -1;
                    $responseDatas["errorMessage"] = "Only '.implode(',',$allowed_ext).' files are allowed!";
            }	

            if($demo_mode){

                    // File uploads are ignored. We only log them.

                    $line = implode('		', array( date('r'), $_SERVER['REMOTE_ADDR'], $pic['size'], $pic['name']));
                    file_put_contents('log.txt', $line.PHP_EOL, FILE_APPEND);

                    $responseDatas["uploadResponse"] = -1;
                    $responseDatas["errorMessage"] = "Uploads are ignored in demo mode.";
            }


            // Move the uploaded file from the temporary 
            // directory to the uploads folder:
            //var_dump($pic['tmp_name'])
            if(move_uploaded_file($pic['tmp_name'], $uploadDirAbsolute.$pic['name'])){
                    $responseDatas["uploadResponse"] = 1;
                    $responseDatas["message"] = "File was uploaded successfuly!";
                    $responseDatas["tmpPath"] = $pic['name'];
            }
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 
        
        return $response;
    }

    
      /**
     * 
     * @Route("/shareActivityEmail/{activityId}", requirements={"activityId" = "\d+"}, name = "ksActivity_shareEmailActivity", options={"expose"=true} )
     * @param int $activityId 
     */
    public function shareActivityEmailAction($activityId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $request        = $this->get('request');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $activity       = $activityRep->findActivities(array('activityId' => $activityId));
        
        if (empty($activity)) {
            throw $this->createNotFoundException("Impossible de trouver l'activité " . $activityId .".");
        }
        
        $responseDatas  = array();        
        $parameters     = $request->request->all();
        //$responseDatas['parameters'] = $parameters;
        if (isset($parameters["emails"])) {
            
            //$emailInviting = $user->getEmail();
            //Récupération du Nom Prénom, sinon username
            $userDetail = $user->getUserDetail();
            $username = $user->getUsername();
            if(isset($userDetail)){
                $firstname = $userDetail->getFirstname();
                $lastname = $userDetail->getLastname();
                if(isset($firstname) && isset($lastname)){
                    $username = $firstname." ".$lastname;
                }
            }

            //$emailGuest = //$form->getData()->getEmailGuest();
            $subject    = "Actualité partagée par " . $username;
            //$host       = $_SERVER["HTTP_HOST"];
            $host       = $this->container->getParameter('host');
            $pathWeb    = $this->container->getParameter('path_web');
            
            $aEmails = explode(";", $parameters["emails"]);
            
            foreach ($aEmails as $mail) {
                $emailGuest = trim($mail);
                 //On reconstruit l'activité dans un template spécifique à l'envois de mail
                $contentMail = $this->renderView(
                    'KsUserBundle:User:emailShareInvit.txt.twig',
                    array(
                        'user' => $user, 
                        'host' => $host, 
                        'pathWeb'   => $pathWeb,
                        'datas' => $activity,
                        'description' => $parameters["description"]
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
                
                // NOTE CF: on remplace les href="" et src="" pour rajouter le http://$host
                // ... en attendant mieux
                // TODO: trouver une affichage simplifié pour les partages d'activité
                //$body = preg_replace('/href="(.+)"/Ui', 'href="http://'.$host.'${1}"', $body);
                //$body = preg_replace('/src="(.+)"/Ui', 'src="http://'.$host.'${1}"', $body);
                
                $message = \Swift_Message::newInstance()
                    ->setContentType('text/html')
                    ->setSubject($subject)
                    ->setFrom("contact@keepinsport.com")
                    ->setTo($emailGuest)
                    ->setBody($body);      

                $this->get('mailer')->getTransport()->start();
                $this->get('mailer')->send($message);
                $this->get('mailer')->getTransport()->stop();
            }
            
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkCommentLikeShareActivity($user->getId());
        
           $responseDatas['shareResponse'] = 1;         
            
        } else {
            $responseDatas['shareResponse'] = 0;
            $responseDatas['error']         = "Veuillez saisir un e-mail svp";
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
     /**
     * 
     * @Route("/getActivitySessionList", name = "ksActivity_getActivitySessionList", options={"expose"=true} )
     */
    public function getActivitySessionListAction()
    {
        
        $em                     = $this->getDoctrine()->getEntityManager();
        $activitySessionRep     = $em->getRepository('KsActivityBundle:ActivitySession');
        $user                   = $this->get('security.context')->getToken()->getUser();
        
        //Récupérations des session d'activités sous forme de liste ( Sport activité - Durée )
        //$activitySession = $activitySessionRep->findNotConnectedToEvent($user);
        $activitySessionChoiceForm  = $this->createForm(new \Ks\ActivityBundle\Form\ActivitySessionChoiceType($user) );
        
        $responseDatas['activitySessionChoiceForm_html'] = $this->render('KsActivityBundle:Activity:_activitySessionChoiceForm.html.twig', array(
            'form'        => $activitySessionChoiceForm->createView(),
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
        
        
    }
    
    /**
     *
     * @Route("/activitiesNotConnectedToEvent", name="ksActivity_activitiesNotConnectedToEvent")
     */
    public function activitiesNotConnectedToEventAction()
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $activitySessionRep = $em->getRepository('KsActivityBundle:ActivitySession');
        $eventTypeRep       = $em->getRepository('KsEventBundle:TypeEvent');
        
        $activities         = $activitySessionRep->findNotConnectedToEvent($user);
        
        $event_training     = $eventTypeRep->findOneBy(array("nom_type"=>"event_training"));
        $event_competition  = $eventTypeRep->findOneBy(array("nom_type"=>"event_competition"));
        
        $eventsForms        = array();
        
        foreach ($activities as &$activity) {
            $eventsForms[$activity->getId()] = $this->createForm(new \Ks\ActivityBundle\Form\ActivitySessionEventType($activity->getId()), $activity)->createView();
            // add. infos...
            $sport  = $activity->getSport();
            $soh    = $activity->getStateOfHealth();
            $points = $activity->getPoints();
            $activity->sportType_hexadecimalColor = $sport->getSportType()->getHexadecimalColor();
            $activity->sport_label          = $sport->getLabel();
            $activity->sport_codeSport      = $sport->getCode();
            $activity->stateOfHealth_name   = $soh ? $soh->getName() : null;
            $activity->stateOfHealth_code   = $soh ? $soh->getCode() : null;
            $activity->earnedPoints         = $points && $points[0] ? $points[0]->getPoints() : null;
        }
 
        return $this->render(
            'KsActivityBundle:Activity:activitiesNotConnectedToEvent.html.twig',
            array(
                'activities'        => $activities,
                'event_training'    => $event_training,
                'event_competition' => $event_competition,
                'eventsForms'       => $eventsForms
            )
        );
    }
    
    /**
     * 
     * @Route("/connectToNewEvent/{eventTypeId}", requirements={"eventTypeId" = "\d+"}, name = "ksActivity_connectToNewEvent", options={"expose"=true} )
     */
    public function connectToNewEventAction($eventTypeId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $agendaHasEventsRep = $em->getRepository('KsAgendaBundle:AgendaHasEvents');
        $eventRep           = $em->getRepository('KsEventBundle:Event');
        $typeEventRep       = $em->getRepository('KsEventBundle:TypeEvent');
        $agendaRep          = $em->getRepository('KsAgendaBundle:Agenda');
        $user               = $this->get('security.context')->getToken()->getUser();
        $request            = $this->getRequest();
        $parameters         = $request->request->all();
        
        $activityService        = $this->get('ks_activity.activityService');
        
        $agenda = $user->getAgenda();
        
        $activitiesId = isset( $parameters["activitiesId"] ) ? $parameters["activitiesId"] : array() ;
        
        $typeEvent = $typeEventRep->find($eventTypeId);
        
        if (!is_object($typeEvent) ) {
            $this->createNotFoundException("Impossible de trouver le type d'événement " . $eventTypeId .".");
        }
        
        foreach( $activitiesId as $activityId ) {
            $activity = $activityRep->find($activityId);
        
            if (!is_object($activity) ) {
                $this->createNotFoundException("Impossible de trouver l'activité " . $activityId .".");
            }
        
            $event = $eventRep->findOneByActivitySession($activity->getId());

            if (!is_object($event) ) {
                $eventName = $activity->getSport()->getLabel();

                $eventContent = "Activité de " . $activity->getSport()->getLabel().".";
                $eventContent .= " Importée depuis " .ucfirst($activity->getSource());
                
                $creationDate           = new \DateTime('now');
                $lastModificationDate   = new \DateTime('now');
                $startDate              = $activity->getIssuedAt();
                $endDate                = $activityService->calculEndDate( $startDate, $activity->getTimeMoving() );
                
                $event = $agendaRep->createEvent(
                    $eventName,
                    $eventContent,
                    $creationDate,
                    $lastModificationDate,
                    $startDate,
                    $endDate,
                    $user,
                    $typeEvent,
                    $activity
                );

                $agendaHasEventsRep->addEventToAgenda($agenda, $event);
            } else {
                $activity->setEvent($event);
                $em->persist($activity);
                $em->flush();
            }
        }
        
        $responseDatas = array();
        $responseDatas["urlToRedirect"] = $this->generateUrl('ksActivity_activitiesNotConnectedToEvent');
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * 
     * @Route("/connectToExistantEvent/{activityId}_{eventId}", requirements={"eventId" = "\d+", "activityId" = "\d+"}, name = "ksActivity_connectToExistantEvent", options={"expose"=true} )
     * @param int $activityId 
     */
    public function connectToExistantEventAction($activityId, $eventId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $eventRep           = $em->getRepository('KsEventBundle:Event');
        
        $activity = $activityRep->find($activityId);
        
        if (!is_object($activity) ) {
            $this->createNotFoundException("Impossible de trouver l'activité " . $activityId .".");
        }
        
        $event = $eventRep->find($eventId);
        
        if (!is_object($event) ) {
            $this->createNotFoundException("Impossible de trouver l'événement " . $eventId .".");
        }
                
        $activity->setEvent($event);
        $event->setActivitySession($activity);
        $em->persist($activity);
        
        $em->persist($event);
        $em->persist($activity);
        $em->flush();
        
        return new RedirectResponse($this->generateUrl('ksActivity_activitiesNotConnectedToEvent'));    
    }
    
    /**
     * @Route("/activityBlockList", name = "ksActivity_activityBlockList" )
     */
    public function activityBlockListAction() {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $activitySessionRep = $em->getRepository('KsActivityBundle:ActivitySession');
        
        $activities = $activitySessionRep->findNotConnectedToEvent($user);

        return $this->render('KsActivityBundle:Activity:activityBlockList.html.twig', array(
            'notConnectedActivitiesNumber' => count($activities),
        ));
    }
    
    /**
     * @Route("/testsGraph", name = "ksActivity_testGraphs" )
     */
    public function testsGraphAction() {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $activitySessionRep = $em->getRepository('KsActivityBundle:ActivitySession');
        
        
        return $this->render('KsActivityBundle:Activity:testsGraph2.html.twig', array(
            //'notConnectedActivitiesNumber' => count($activities),
        ));
    }

    /**
     * @param array $waypoints
     * @param $dimension
     * @param $windowSize
     */
    protected function averageFilter(array &$waypoints, $dimension, $windowSize)
    {
        $newDimensionDatas  = array();
        $numPoints          = count($waypoints);
        for ($i = 0; $i < $numPoints; ++$i) {
            if (($i - $windowSize) < 0 || ($i + $windowSize) >= $numPoints) {
                $newDimensionDatas[$i] = $waypoints[$i][$dimension];
                continue;
            }
            $window = array_slice($waypoints, $i - $windowSize, $windowSize * 2 + 1);
            $sum    = 0;
            for ($j = 0; $j < count($window); ++$j) {
                $sum += $window[$j][$dimension];
            }
            $newDimensionDatas[$i] = $sum / count($window);
        }
        $i = 0;
        foreach ($newDimensionDatas as $data) {
            $waypoints[$i][$dimension] = $data;
            ++$i;
        }
    }
    
    /**
     * @Route("/getDataGraph/{activityId}", name = "ksActivity_getDataGraph", options={"expose"=true} )
     */
    public function getDataGraphAction($activityId) {
        try {
        $em                 = $this->getDoctrine()->getEntityManager();
        $activitySessionRep = $em->getRepository('KsActivityBundle:ActivitySession');
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        $preferenceRep      = $em->getRepository('KsUserBundle:Preference');
        $preferenceTypeRep  = $em->getRepository('KsUserBundle:PreferenceType');
        
        $ele    = array();
        
        $responseDatas = array(
                "response" => 1,
                "source" => "",
                "chart" => array(
                    "distances"     => array(),
                    "fullDurationMoving" => array(),
                    "elevations"    => array(),
                    "elevations_green"    => array(),
                    "elevations_orange"    => array(),
                    "elevations_red"    => array(),
                    "speeds"        => array(),
                    "paces"         => array(),
                    "heartRates"    => array(),
                    "temperatures"  => array()
                ),
                "map" => array(
                    "waypoints"     => array()
                ),
                "laps" => array(),
                "info" => array(
                    "distance"      => null,
                    "D-"            => null,
                    "D+"            => null,
                    "minEle"        => null,
                    "maxEle"        => null,
                    "avgEle"        => null,
                    "duration"      => null,
                    "minTemp"       => null,
                    "maxTemp"       => null,
                    "minPF"         => null,
                    "maxPF"         => null,
                    "minPower"      => null,
                    "maxPower"      => null,
                    "avgPace"       => null,
                    "minPace"       => null,
                    "maxPace"       => null,
                    "ecartTypePace" => null
                )
            );
        
        $context        = 'activity';
        $xAxis          = "fullDurationMoving";
        $activity       = $activitySessionRep->find($activityId);
        if ($activity == null) {
            $activity   = $articleRep->find($activityId);
            $context    = 'article';
            $xAxis      = "fullDistances"; // Cas des compétitions notamment en abscisse on affiche la distance et pas le temps
        }
        else {
            $user = $activity->getUser();
            if (!is_null($user)) {
                $HRRest = $user->getUserDetail()->getHRRest();
                $HRMax  = $user->getUserDetail()->getHRMax();
            }
        }
        
        $trackingDatas  = $activity->getTrackingDatas();
        $waypoints  = isset($trackingDatas['waypoints']) && isset($trackingDatas['waypoints'][0]) && isset($trackingDatas['waypoints'][0]['fullDistances']) ? $trackingDatas["waypoints"] : array();
        $laps       = isset($trackingDatas['laps']) && isset($trackingDatas['laps'][0]) ? $trackingDatas["laps"] : array();
        $responseDatas["laps"] = $laps;
        
        $numWaypoints   = count($waypoints);
        $iStep          = round($numWaypoints / 500);
        
        $responseDatas["info"]["minPace"] = 99999;
        $pace = array();
        
        if ($iStep == 0) $iStep = 1;

        $distance   = 0;
        $lastPoint  = 'green';
        $y          = array('green' => null, 'orange' => null, 'red' => null);

        // Moyenne mobile pour lissage
        // la taille de la fenêtre dépend du nombre d'échantillons
        $windowSize     = $numWaypoints / 1000;
        if ($windowSize > 50) $windowSize = 50;
        else if ($windowSize < 4) $windowSize = 3;
        $this->averageFilter($waypoints, 'speed', $windowSize);
        $this->averageFilter($waypoints, 'pace', $windowSize);
        $this->averageFilter($waypoints, 'pedalingFrequency', $windowSize);
        $this->averageFilter($waypoints, 'power', $windowSize);

        for ($i = 0; $i < $numWaypoints; ++$i) {
            if ($i % $iStep == 0) { // NOTE CF: on ne prend qu'1 relevé sur $iStep
                if ($context == 'article') $responseDatas["chart"]["distances"][]  = $waypoints[$i]["fullDistances"];
                else $responseDatas["chart"]["distances"][]  = array($waypoints[$i][$xAxis], $waypoints[$i]["fullDistances"]);
                $responseDatas["chart"]["elevations"][] = array($waypoints[$i][$xAxis], $waypoints[$i]["ele"]);
                
                //Calcul de pente = Dénivelé x 100 / distance parcourue (en m)
                if ($i == 0) {
                    $pente = 0;
                }
                else {
                    $distance = ($waypoints[$i]["fullDistances"] - $waypoints[$i-$iStep]["fullDistances"])*1000;
                    $deniv = $waypoints[$i]["ele"] - $waypoints[$i-$iStep]["ele"];
                    if ($distance != 0) $pente = abs(round($deniv * 100 / $distance, 0));
                }
                
                if ($pente <=5) {
                    $y['green'] = $waypoints[$i]["ele"];
                    $y['orange'] = null;
                    $y['red'] = null;
                    $y["$lastPoint"] = $waypoints[$i]["ele"];
                    $lastPoint = 'green';
                }
                else if ($pente >=5 && $pente <=15) {
                    $y['green'] = null;
                    $y['orange'] = $waypoints[$i]["ele"];
                    $y['red'] = null;
                    $y["$lastPoint"] = $waypoints[$i]["ele"];
                    $lastPoint = 'orange';
                }
                else if ($pente >=15) {
                    $y['green'] = null;
                    $y['orange'] = null;
                    $y['red'] = $waypoints[$i]["ele"];
                    $y["$lastPoint"] = $waypoints[$i]["ele"];
                    $lastPoint = 'red';
                }
                
                $responseDatas["chart"]["elevations_green"][] = 
                        array(
                            'x' => $waypoints[$i]["fullDistances"],
                            'y' => $y['green'],
                            'pente' => $pente
                        );
                $responseDatas["chart"]["elevations_orange"][] = 
                        array(
                            'x' => $waypoints[$i]["fullDistances"],
                            'y' => $y['orange'],
                            'pente' => $pente
                        );
                $responseDatas["chart"]["elevations_red"][] = 
                        array(
                            'x' => $waypoints[$i]["fullDistances"],
                            'y' => $y['red'],
                            'pente' => $pente
                        );
                
                $responseDatas["chart"]["speeds"][]     = array( $waypoints[$i][$xAxis], $waypoints[$i]["speed"] );
                $responseDatas["chart"]["paces"][]      = array( $waypoints[$i][$xAxis], $waypoints[$i]["pace"] );
                $responseDatas["info"]["avgPace"]       = $responseDatas["info"]["avgPace"] + $waypoints[$i]["pace"];
                
                $pace[] = $waypoints[$i]["pace"];
                $ele[]  = $waypoints[$i]["ele"];
                
                $responseDatas["chart"][$xAxis][] = $waypoints[$i][$xAxis];
                
                if ( isset($trackingDatas["info"]["issetTemperatures"]) && $trackingDatas["info"]["issetTemperatures"] ) { // Les données températures sont présentes;
                    $responseDatas["chart"]["temperatures"][] = array( $waypoints[$i][$xAxis], $waypoints[$i]["temperature"]);
                }
                if ( isset($trackingDatas["info"]["issetHeartRates"]) && $trackingDatas["info"]["issetHeartRates"] ) { // Les données cardio sont présentes;
                    $responseDatas["chart"]["heartRates"][] = array( $waypoints[$i][$xAxis], $waypoints[$i]["heartRate"]);
                    if ($waypoints[$i]["heartRate"] != 0) $responseDatas["chart"]["RR"][] = array( $waypoints[$i][$xAxis], 60000 / $waypoints[$i]["heartRate"]);
                    //if ($waypoints[$i]["heartRate"] != 0) $responseDatas["chart"]["EPOC"][] = array( $waypoints[$i][$xAxis], 60000 / $waypoints[$i]["heartRate"]);
                }
                if ( isset($trackingDatas["info"]["issetPedalingFrequency"]) && $trackingDatas["info"]["issetPedalingFrequency"] ) {
                    $responseDatas["chart"]["pedalingFrequencies"][] = array( $waypoints[$i][$xAxis], $waypoints[$i]["pedalingFrequency"]);
                }
                if ( isset($trackingDatas["info"]["issetPower"]) && $trackingDatas["info"]["issetPower"] ) {
                    $responseDatas["chart"]["powers"][] = array( $waypoints[$i][$xAxis], $waypoints[$i]["power"]);
                }
                //var_dump("$i=".$waypoints[$i][$xAxis]);
                $responseDatas["map"]["waypoints"][(string)$waypoints[$i][$xAxis]] = array(
                    'lat'               => $waypoints[$i]["lat"],
                    'lon'               => $waypoints[$i]["lon"],
                    'ele'               => $waypoints[$i]["ele"],
                    'heartRate'         => $waypoints[$i]["heartRate"],
                    'pedalingFrequency' => $waypoints[$i]["pedalingFrequency"],
                    'power'             => $waypoints[$i]["power"],
                    'speed'             => $waypoints[$i]["speed"],
                    'distance'          => $waypoints[$i]["fullDistances"]
                );
            }
        }
        
        if ($activity->getDistance() != null) $responseDatas["info"]["distance"] = $activity->getDistance();
        else $responseDatas["info"]["distance"] = $trackingDatas["info"]["distance"];
        $responseDatas["info"]["D-"]        = $trackingDatas["info"]["D-"];
        $responseDatas["info"]["D+"]        = $trackingDatas["info"]["D+"];
        $responseDatas["info"]["avgEle"]    = count($ele) > 0 ? array_sum($ele) / count($ele) : null;
        $responseDatas["info"]["minEle"]    = $trackingDatas["info"]["minEle"];
        $responseDatas["info"]["maxEle"]    = $trackingDatas["info"]["maxEle"];
        if ($context == 'activity') $responseDatas["info"]["duration"]  = $activity->getTimeMoving(); // en sec
        $responseDatas["info"]["minTemp"]   = $trackingDatas["info"]["minTemp"];
        $responseDatas["info"]["maxTemp"]   = $trackingDatas["info"]["maxTemp"];
        $responseDatas["info"]["minHR"]     = isset($trackingDatas["info"]["minHR"]) ? $trackingDatas["info"]["minHR"] : 70;
        $responseDatas["info"]["maxHR"]     = isset($trackingDatas["info"]["maxHR"]) ? $trackingDatas["info"]["maxHR"] : 190;
        $responseDatas["info"]["minPF"]     = $trackingDatas["info"]["minPF"];
        $responseDatas["info"]["maxPF"]     = $trackingDatas["info"]["maxPF"];
        $responseDatas["info"]["minPower"]  = $trackingDatas["info"]["minPower"];
        $responseDatas["info"]["maxPower"]  = $trackingDatas["info"]["maxPower"];
        $responseDatas["info"]["avgPace"]   = count($pace) > 0 ? array_sum($pace) / count($pace) : null;
        $responseDatas["info"]["minPace"]   = count($pace) > 0 ? min($pace) : null;
        $responseDatas["info"]["maxPace"]   = count($pace) > 0 ? max($pace) : null;

        if (count($pace) > 0) {
            $fVariance = 0.0;
            foreach ($pace as $i) {
                $fVariance += pow($i - $responseDatas["info"]["avgPace"], 2);
            }
            $fVariance /= count($pace);
            $responseDatas["info"]["ecartTypePace"] =  (float) sqrt($fVariance);
        } else {
            $responseDatas["info"]["ecartTypePace"] = null;
        }
        
        if ($context == 'activity') {
            //Traitement des zones cardio si pack premium / elite (FIXME : copié/collé de coachingController.php...
            if ( isset($trackingDatas["info"]["issetHeartRates"]) && $trackingDatas["info"]["issetHeartRates"] ) {
                $HRZones = array();
                $HRZonesPreference = $preferenceTypeRep->findOneByCode("hr");
                $preferences = $preferenceRep->findBy(array("preferenceType" => $HRZonesPreference->getId()));
                if (!is_null($HRRest) && !is_null($HRMax)) {
                    foreach($preferences as $key => $preference) {
                        $HRZones[$key] = array(
                            "id"        => $key,
                            "y"         => 0,
                            "label"     => $this->get('translator')->trans('hr.'.$preference->getCode()),
                            "range"     => $preference->getCode() == 'zone0' ? $this->get('translator')->trans('hr.'.$preference->getCode()) : $this->get('translator')->trans('hr.from') . " ". strval(round($HRRest + $preference->getVal1() /100 * ($HRMax - $HRRest),0)) . " " . $this->get('translator')->trans('hr.to') . " ". strval(round($HRRest + $preference->getVal2() /100 * ($HRMax - $HRRest),0)) . " bpm",
                            "val1"      => round($HRRest + $preference->getVal1() /100 * ($HRMax - $HRRest),0),
                            "val2"      => round($HRRest + $preference->getVal2() /100 * ($HRMax - $HRRest),0),
                            "duration"  => 0
                        );
                    }
                    $totalHRDuration = 0;
                    if (!is_null($trackingDatas)) {
                        if (!is_null($HRRest) && !is_null($HRMax) && isset($trackingDatas['info']['HRZones']) && !is_null($trackingDatas['info']['HRZones'])) {
                            foreach($preferences as $key => $preference) {
                                if (isset($trackingDatas['info']['HRZones'][$preference->getCode()]) && !is_null($trackingDatas['info']['HRZones'][$preference->getCode()])) $HRZones[$key]["duration"] += $trackingDatas['info']['HRZones'][$preference->getCode()]["duration"];
                                else $HRZones[$key]["duration"] += 0;
                                $totalHRDuration += $HRZones[$key]["duration"];
                            }
                        }
                    }

                    $displayPieByHRZone = false;
                    $pieByHRZone = array();
                    if (!is_null($HRRest) && !is_null($HRMax)) {
                        $displayPieByHRZone = true;
                        foreach( $HRZones as $HRZone ) {
                            $HRDuration = $HRZone["duration"];
                            if ($HRDuration != 0) $pieByHRZone[] = array("id" => $HRZone['id'], "val1" => $HRZone["val1"], "val2" => $HRZone["val2"], "name" => $HRZone['range'], "label" => $HRZone['label'], "duration" => strval($this->secondesToTimeDuration($HRDuration)), "y" => $totalHRDuration !=0 ? round($HRDuration/$totalHRDuration *100, 0) : 0, "sliced" => false);
                        }
                    }

                    $responseDatas['pieByHRZone']   = $pieByHRZone;
                }
            }

            $responseDatas['HRRest'] = is_null($HRRest) ? "-" : $HRRest;
            $responseDatas['HRMax']  = is_null($HRMax)  ? "-" : $HRMax;

            if (!isset($responseDatas['pieByHRZone']) && $context == 'activity') {
                $pieByHRZone[] = array("id"         => 0, 
                                       "val1"       => 0, 
                                       "val2"       => 0, 
                                       "name"       => $this->get('translator')->trans('hr.zone0'), 
                                       "label"      => "", 
                                       "duration"   => strval($this->secondesToTimeDuration($responseDatas["info"]["duration"])), 
                                        "y"         => 100, 
                                       "sliced"     => false);
                $responseDatas['pieByHRZone']   = $pieByHRZone;
            }
        }
        
        //var_dump($responseDatas);exit;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
        } catch (Exception $e) {
            var_dump($e->getMessage()); exit;
        }
    }
    
    
    
    /**
     * @Route("/lastActivities/{nbActivities}", requirements={"nbActivities" = "\d+"}, name = "ksActivity_lastActivities" )
     */
    public function lastActivitiesAction($nbActivities, $activitiesTypes, $activitiesFrom, $sports, $lastModified)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $activitySessionRep = $em->getRepository('KsActivityBundle:ActivitySession');
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $user               = $this->get('security.context')->getToken()->getUser();
        $securityContext    = $this->container->get('security.context');
        
        $now = new \DateTime();
        
        $sportsExplode= array();
        foreach ($sports as $sport) {
            $sportsExplode[] = $sport['code'];
        }
        
        $activitiesTypesExplode= array();
        foreach ($activitiesTypes as $activityType) {
            $activitiesTypesExplode[] = $activityType['code'];
        }
        
        $activitiesFromExplode= array();
        foreach ($activitiesFrom as $activityFrom) {
            $activitiesFromExplode[] = $activityFrom['code'];
        }
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user           = $this->get('security.context')->getToken()->getUser();
        }else {
            $activitiesFromExplode = array("me", "my_friends");
            $activitiesTypesExplode = array();
            $user = $userRep->find(1);
        }
        
        //Si choix MES SPORTS, le tableau doit contenir la valeur ""
        if (in_array( "", $sportsExplode) && count($sportsExplode) > 0) $my_sports = true;
        else $my_sports = false;
        
        if(!is_object($user)) {
            $user = $userRep->find(1);
        }
        
        $activities = $activityRep->findActivities(array(
            'user'                  => $user,
            'activitiesTypes'       => $activitiesTypesExplode,
            'activitiesFrom'        => $activitiesFromExplode,
            'sports'                => $sportsExplode,
            'my_sports'             => $my_sports,
            //'userIds'           => $userId != 0 ? array( $userId ) : array(),
            'lastModified'          => $lastModified,
            'endOn'                 => $now->format("Y-m-d"),
            'perPage'               => $nbActivities,
            'activityTypes'         => array('session'),
            'withNoPrivateCoaching' => true,//Pour les plans d'entrainements : si event privé uniquement le coaché et le coach peuvent voir l'activité
            'dontGetArticles' => true
            //'withLocalisation'    => true
        ));
        
        //if ($user->getId() == 7) var_dump($activities);
        
        $waypointsActivities = array();
        foreach( $activities as $activityArray ) {
            if (!is_null($activityArray['activity']['id'])) {
                $activity = $activityRep->find($activityArray['activity']['id']);
                if (is_null($activity)) $trackingDatas  = null;
                else {
                    if ($activity instanceof \Ks\ActivityBundle\Entity\ActivitySession) $trackingDatas = $activity->getTrackingDatas();
                    else $trackingDatas  = null;
                }
                if (!is_null($trackingDatas["waypoints"])) $waypoints = $trackingDatas["waypoints"];
                else $waypoints = null;
                if (!is_null($waypoints) && count($waypoints) != 0) {
                    $waypointsActivities[] = array('id' => $activityArray['activity']['id'], 'points' => $trackingDatas["waypoints"]);
                }
            }
        }
        
        
        //var_dump($activities);
        return $this->render(
            'KsActivityBundle:Activity:_last_activities.html.twig',
            array(
                'activities'                => $activities,
                'waypointsActivities'       => is_null($waypointsActivities) ? null : $this->var_to_js('waypointsActivities', $waypointsActivities, 1)
            )
        );
    }
    
    /**
     * @Route("/lastActivitiesFromKS/{nbActivities}", requirements={"nbActivities" = "\d+"}, name = "ksActivity_lastActivitiesFromKS" )
     */
    public function lastActivitiesFromKSAction($nbActivities)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $activitySessionRep = $em->getRepository('KsActivityBundle:ActivitySession');
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $userRep            = $em->getRepository('KsUserBundle:User');
        
        $now = new \DateTime();
        
        $user = $userRep->find(1);
        
        $activitiesFrom = array("keepinsport");
        
        $activities = $activityRep->findActivities(array(
            'user'                      => $user,
            'activitiesFrom'            => $activitiesFrom,
            'endOn'                     => $now->format("Y-m-d"),
            'perPage'                   => $nbActivities,
            'activityTypes'             => array('session'),
            'withNoPrivateCoaching'     => true,//Pour les plans d'entrainements : si event privé uniquement le coaché et le coach peuvent voir l'activité
            'dontGetArticles'           => false
            //'withLocalisation'    => true
        ));
        
        return $this->render(
            'KsActivityBundle:Activity:_last_activitiesFromKS.html.twig',
            array(
                'activitiesFromKS' => $activities,
            )
        );
    }
    
    /**
     * @Route("/lastActivitiesFromWikisport/{nbActivities}", requirements={"nbActivities" = "\d+"}, name = "ksActivity_lastActivitiesFromWikisport" )
     */
    public function lastActivitiesFromWikisportAction($nbActivities)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $activitySessionRep = $em->getRepository('KsActivityBundle:ActivitySession');
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $userRep            = $em->getRepository('KsUserBundle:User');
        
        $now = new \DateTime();
        
        $user = $userRep->find(1);
        
        //$activitiesFrom = array("me", "my_friends");
        
        $activities = $activityRep->findActivities(array(
            'user'                      => $user,
            //'activitiesFrom'            => $activitiesFrom,
            'endOn'                     => $now->format("Y-m-d"),
            'perPage'                   => $nbActivities,
            'activityTypes'             => array('session'),
            'withNoPrivateCoaching'     => true,//Pour les plans d'entrainements : si event privé uniquement le coaché et le coach peuvent voir l'activité
            'getArticles'               => true
            //'withLocalisation'    => true
        ));
        
        return $this->render(
            'KsActivityBundle:Activity:_last_activitiesFromKS.html.twig',
            array(
                'context'           => 'fromWikisport',
                'activitiesFromKS'  => $activities,
            )
        );
    }
    
    /**
     * @Route("/lastActivitiesFromUser/{nbActivities}/{userId}", name = "ksActivity_lastActivitiesFromUser" )
     */
    public function lastActivitiesFromUserAction($nbActivities, $userId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        
        $now = new \DateTime();
        
        $activities = $activityRep->findActivities(array(
            'fromPublicProfile'         => true,
            'userId'                    => $userId,
            'endOn'                     => $now->format("Y-m-d"),
            'perPage'                   => $nbActivities,
            'activityTypes'             => array('session'),
            'withNoPrivateCoaching'     => true,//Pour les plans d'entrainements : si event privé uniquement le coaché et le coach peuvent voir l'activité
            'dontGetArticles'           => false
            //'withLocalisation'    => true
        ));
        
        return $this->render(
            'KsActivityBundle:Activity:_last_activities.html.twig',
            array(
                'activities' => $activities,
            )
        );
    }
    
    /**
     * @Route("/nearbyShops/{nbShops}", requirements={"nbShops" = "\d+"}, name = "ksActivity_nearbyShops" )
     */
    public function nearbyShopsAction($nbShops)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $shopRep        = $em->getRepository('KsShopBundle:Shop');
        $userRep        = $em->getRepository('KsUserBundle:User');
        $securityContext    = $this->container->get('security.context');
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user           = $this->get('security.context')->getToken()->getUser();
            $shops = $shopRep->findShops(array(
                'userId' => $user->getId(),
                'shopsWithConditions'  => true,
                'nearby' => true
            ));
        }
        else {
            $user = $userRep->find(1);
            $shops = null;
        }
        
        return $this->render(
            'KsActivityBundle:Activity:_nearbyShops.html.twig',
            array(
                'shops' => $shops
            )
        );
    }
    
    /**
     * @Route("/communityDayActivities/{userId}", requirements={"userId" = "\d+"}, name = "ksActivity_communityDayActivities" )
     */
    public function communityDayActivitiesAction($userId) {
        
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $userRep        = $em->getRepository('KsUserBundle:User');
        $user           = $userRep->find($userId);
        
        
        $date = date('Y-m-d');
        
        if(!is_object($user)) {
           throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $userId . '.');
        } 
        
        $activities = $activityRep->findActivities(array(
            'user'      => $user,
            'date'      => $date,
            'perimeter' => 'friends'
        ));
        
        $contentMail = $this->renderView(
            'KsActivityBundle:Activity:_activities_mail.html.twig',
            array(
                'activities'    => $activities,
                'host'          => $this->container->getParameter('host'),
                'pathWeb'       => $this->container->getParameter('path_web')
            ),
            'text/html'
        );
        
        $body = $this->renderView(
            'KsUserBundle:User:template_mail.html.twig', 
            array(
                'host'      => $this->container->getParameter('host'),
                'pathWeb'   => $this->container->getParameter('path_web'),
                'content'   => $contentMail,
                'user'      => is_object( $user ) ? $user : null
            ), 
            'text/html'
        );
        
        if (count($activities) > 0) {
            $subject = "Activités du ".$date." réalisées par ta communauté";
            $message = \Swift_Message::newInstance()
                ->setContentType('text/html')
                ->setSubject($subject)
                ->setFrom("contact@keepinsport.com")
                ->setTo("cedric.delpoux@gmail.com")
                ->setBody($body);

            $this->get('mailer')->getTransport()->start();
            $this->get('mailer')->send($message);
            $this->get('mailer')->getTransport()->stop();
        }
    
        
        return $this->render(
            'KsActivityBundle:Activity:activities.html.twig',
            array(
                'activities'    => $activities
            )
        );
    }
    
    /**
     * @Route("/sendDayActivitiesMails", requirements={"userId" = "\d+"}, name = "ksActivity_sendDayActivitiesMails" )
     */
    public function sendDayActivitiesMailsAction() {

        $em             = $this->getDoctrine()->getEntityManager();
        $userRep        = $em->getRepository('KsUserBundle:User');

        //services
        $notificationService    = $this->get('ks_notification.notificationService');
        
        
        $users = $userRep->findAll();

        $sendedUsers = array();
        foreach( $users as $user ) {
            if( $user->getUsername() != "keepinsport") {
                //On met à jour la configuration pour l'envoi de notifications par mail
                $result = $notificationService->sendActivitiesDailyMail( $user );

                if( $result ) {
                    $sendedUsers[] = $user->getUsername();
                }
            }
        }
          
        return $this->render(
            'KsActivityBundle:Activity:sendDayActivitiesMails.html.twig',
            array(
                'sendedUsers'    => $sendedUsers
            )
        );
    }
    
    /**
     * 
     * @Route("/readImportantStatus/{activityId}", requirements={"activityId" = "\d+"}, name = "ksActivity_readImportantStatus", options={"expose"=true} )
     * @param int $activityId 
     */
    public function readImportantStatusAction($activityId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $importantStatusRep         = $em->getRepository('KsActivityBundle:UserReadsImportantStatus');
        $user           = $this->get('security.context')->getToken()->getUser();

        
        $activity = $activityRep->find($activityId);
        
        if (!is_object($activity) ) {
            $impossibleActivityMsg = $this->get('translator')->trans('impossible-to-find-activity-%activityId%', array('%activityId%' => $activityId));
            throw new AccessDeniedException($impossibleActivityMsg);
        }
        
        $responseDatas = array();
        
        $importantStatus = $importantStatusRep->findOneBy( array(
            "activity"  => $activity->getId(),
            "user"      => $user->getId()
        ));
        
       if( is_object( $importantStatus )) {
           $importantStatusAfter = $importantStatusRep->readImportantStatus( $importantStatus );
           
           if( $importantStatusAfter->getIsRead() ) {
               $responseDatas["response"] = 1;
           } else {
               $responseDatas["response"] = -1;
               $responseDatas["errorMessage"] = "Le statut important n'a pas été lu.";
           }
       } else {
           $responseDatas["response"] = -1;
           $responseDatas["errorMessage"] = "Impossible de trouver le statut important.";
       }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * @Route("/V2", name="ksV2", options={"expose"=true} )
     */
    public function V2Action()
    {
        //$user               = $this->get('security.context')->getToken()->getUser();
        
        //if ($user->getId() == 3 || $user->getId() == 5 || $user->getId() == 7 or $user->getId() == 744) return $this->render('KsUserBundle:Security:loginV2.html.twig', array());
        
        return $this->render('KsUserBundle:Security:loginV2.html.twig', array());
    }
    
    /**
     * @Route("/getActivitiesToSyncFromList", name="ksActivity_syncFromList", options={"expose"=true} )
     */
    public function getActivitiesToSyncFromListAction()
    {
        $serviceName        = null;
        $user               = $this->get('security.context')->getToken()->getUser();
        $em                 = $this->getDoctrine()->getEntityManager();
        $userHasServicesRep = $em->getRepository('KsUserBundle:UserHasServices');
        $userActiveService  = $userHasServicesRep->findOneBy(array(
            "user"      => $user->getId(),
            "is_active" => true
        ));
        
        ini_set('memory_limit', '2048M');
        
        // On ne synchronise pas manuellement son agenda avec Google Agenda
        // NOTE CF: c'était getName() == 2, donc je pense que ça ne marchait pas
        if (!empty($userActiveService) && $userActiveService->getService()->getName() !== 'Google-Agenda') {
            $preferenceRep = $em->getRepository('KsUserBundle:Preference');
            $serviceName   = strtolower($userActiveService->getService()->getName());
            $servicePref   = $preferenceRep->findOneBy(array(
                "code" => $serviceName."SearchNumber"
            ));
            $parameter     = !empty($servicePref) ? $servicePref->getVal1() : 10;
        }
        
        return $serviceName === null ?
            $this->redirect($this->generateUrl('ks_set_services'))
            : $this->render('KsActivityBundle:Activity:syncFromList.html.twig', array(
                'user'        => $user,
                'serviceName' => $serviceName,
                'parameter'   => $parameter
            ));
    }
    
    /**
     * @Route("/getActivitiesToSync", name="ksActivity_getActivitiesToSync")
     */
    public function getActivitiesToSync(Request $request)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $activityToSyncRep  = $em->getRepository('KsActivityBundle:ActivityToSync');
        $userHasServicesRep = $em->getRepository('KsUserBundle:UserHasServices');
        $sportRep           = $em->getRepository('KsActivityBundle:Sport');
        $importActivityService = $this->get('ks_activity.importActivityService');
        $serviceId          = null; //On ne synchronise pas manuellement son agenda avec Google Agenda
        $userActiveService  = $userHasServicesRep->findOneBy(array(
            "user"      => $user->getId(),
            "is_active" => true
        ));
        
        // On ne synchronise pas manuellement son agenda avec Google Agenda
        // NOTE CF: c'était getName() == 2, donc je pense que ça ne marchait pas
        // NOTE CF: bizarre, ce serait plus logique de passer le serviceId en paramètre
        if (!empty($userActiveService) && $userActiveService->getService()->getName() !== 'Google-Agenda') {
            $serviceId   = $userActiveService->getService()->getId();
            $serviceName = $userActiveService->getService()->getName();
        }
        
        //Suppression de la table temporaire qui contient les activités synchronisables
        $activityToSyncRep->deleteActivityToSync($user->getId(), $serviceId);
        
        //On récupère les activités à synchroniser
        set_time_limit(600);
        ini_set('memory_limit', '1024M');
        $activities = $importActivityService->getActivitiesToSyncFromService($serviceId, $user);
        
        if (!is_array($activities)) {
            //Cas d'une erreur 503 par exemple
            $coachingSessionsForms   = null;
            $coachingEquipmentsForms = null;
        } else {
            $coachingSessionsForms   = array();
            $coachingEquipmentsForms = array();
            $sportByDefault          = $sportRep->findOneByCodeSport('running');
            $numActivities           = count($activities);
            for ($i = 0; $i < $numActivities; ++$i) {
                $coachingSessionsForms[]   = $this->createForm(new \Ks\CoachingBundle\Form\CoachingPlanEventsType($user, null, $i))->createView();
                $coachingEquipmentsForms[] = $this->createForm(new \Ks\CoachingBundle\Form\CoachingEquipmentsType($user, $sportByDefault, $i))->createView();
            }
        }
        
        return $this->render('KsActivityBundle:Activity:_getActivitiesToSync.html.twig', array(
            'serviceId'               => $serviceId,
            'serviceName'             => $serviceName,
            'activities'              => $activities,
            'coachingSessionsForms'   => $coachingSessionsForms,
            'coachingEquipmentsForms' => $coachingEquipmentsForms,
        ));
    }
    
    /**
     * @Route("/importActivitesFromSyncList", name="ksActivity_importActivitesFromSyncList" ) 
     */
    public function importActivitesFromSyncList()
    {
        $numCreated             = 0;
        $activities             = $this->getRequest()->get('activity', array());
        $intensities            = $this->getRequest()->get('intensity', array());
        $statesOfHealth         = $this->getRequest()->get('stateOfHealth', array());
        $achievements           = $this->getRequest()->get('achievement', array());
        $events                 = $this->getRequest()->get('event', array());
        $equipments             = $this->getRequest()->get('equipments', array());
        $descriptions           = $this->getRequest()->get('description', array());
        $user                   = $this->get('security.context')->getToken()->getUser();
        $em                     = $this->getDoctrine()->getEntityManager();
        $activityToSyncRep      = $em->getRepository('KsActivityBundle:ActivityToSync');
        $userHasServicesRep     = $em->getRepository('KsUserBundle:UserHasServices');
        $importActivityService  = $this->get('ks_activity.importActivityService');
        
        $userServices = $userHasServicesRep->findBy(array("user" => $user->getId(), "is_active" => true));
        
        foreach ($userServices as $userService) {
            if ($userService->getService()->getName() == 2) $serviceId =null; //On ne synchronise pas manuellement son agenda avec Google Agenda
            else $serviceId = $userService->getService()->getId();
        }
        
        $service = $em->getRepository('KsUserBundle:Service')->find($serviceId);
        $userService = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array("user" => $user->getId(), "service" => $service->getId()));
        
        set_time_limit(600);
        ini_set('memory_limit', '1024M');
        
        $responseDatas = array();
        
        foreach ($activities as $key => $activityId) {
            $activity = $activityToSyncRep->findOneBy(array("user" => $user->getId(), "service" => $serviceId, "id_website_activity_service" => $activityId));
//            var_dump($key);
//            var_dump("--statesOfHealth:".$statesOfHealth[$key]);
//            var_dump("--intensities:".$intensities[$key]);
//            var_dump("--achievements:".$achievements[$key]);
//            var_dump("--eventId:".$eventId);
//            var_dump("--eventId2:".$events[$key]);
//            var_dump("--descriptions:".$descriptions[$key]);
//            var_dump("--equipments:".$equipments[$key]);
            $equipmentsArray = array();
            $equipmentsArray = explode(",", $equipments[$key]);
            list($newActivityId, $error) = $importActivityService->saveUserActivityFromService($this->get('translator'),
                                                                $service->getName(), 
                                                                json_decode($activity->getSourceDetailsActivity(), true), 
                                                                $userService, 
                                                                $statesOfHealth[$key],
                                                                $intensities[$key],
                                                                $achievements[$key],
                                                                $events[$key] == "" ? null : $events[$key],
                                                                $descriptions[$key],
                                                                $equipments[$key] == "" ? null : $equipmentsArray
                                                                );
            if ($error != 1) $responseDatas['error'] = $error;
            ++$numCreated;
            unset($equipmentsArray);
            $responseDatas['activityId'] = $newActivityId;
        }
        //exit;
        //$responseDatas['urlToRedirect']         = $this->generateUrl('ksActivity_activitiesList');
        $responseDatas['numActivitiesCreated']  = $numCreated;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    /**
     * @Route("/uploadFfaActivities", name="ksActivity_uploadFfaActivities" )
     */
    public function uploadFfaActivities()
    {
        $user           = $this->get('security.context')->getToken()->getUser();
        $userDetail     = $user->getUserDetail();
        $gender         = '';
        
        if ($userDetail && $userDetail->getSexe()) {
            $gender     = $userDetail->getSexe();
            if ($gender == 'Masculin') $gender = 'M';
            else if ($gender == 'Feminin') $gender = 'F';
        }
        
        $birthDate      = $userDetail->getBornedAt(); // NOTE CF: wtf le nom de variable t_t
        if ($birthDate) {
            $birthYear  = $birthDate->format('Y');
        } else {
            $birthYear  = '';
        }
        
        $userDatas = array(
            'firstname' => $userDetail->getFirstname(),
            'lastname'  => $userDetail->getLastname(),
            'birthYear' => $birthYear,
            'gender'    => $gender
        );
        
        return $this->render('KsActivityBundle:Activity:uploadFfaActivities.html.twig', array(
            'user'      => $userDatas
        ));
    }
    
    /**
     * @Route("/getFfaActivities", name="ksActivity_getFfaActivities" )
     */
    public function getFfaActivities(Request $request)
    {
        // TODO: faire un sanitize sur les variables passées en paramètres
        $activities = array();
        $ffaService = $this->get('ks_activity.ffaService');
        $curYear    = date('Y');
        $targetYear = $curYear - 4;
        for ($year = $curYear; $year > $targetYear; --$year) {
            $activities = array_merge(
                $activities,
                $ffaService->getActivities(
                    $request->get('firstname'),
                    $request->get('lastname'),
                    $request->get('gender'),
                    $request->get('birthyear'),
                    $year
            ));
        }
                
        return $this->render('KsActivityBundle:Activity:_getFfaActivities.html.twig', array(
            'activities' => $activities
        ));
    }
    
    /**
     * @Route("/importFfaActivities", name="ksActivity_importFfaActivities" ) 
     */
    public function importFfaActivities()
    {
        $numCreated         = 0;
        $activities         = $this->getRequest()->get('ffaActivity', array());
        $user               = $this->get('security.context')->getToken()->getUser();
        $em                 = $this->getDoctrine()->getEntityManager();
        $leagueLvlService   = $this->get('ks_league.leagueLevelService');
        $activityRep        = $em->getRepository('KsActivityBundle:Activity'); 
        $sportRep           = $em->getRepository('KsActivityBundle:Sport'); 
        $sport              = $sportRep->findOneByCodeSport('running'); // FIXME: temporaire !
         
        foreach ($activities as $activity) {
            $activityDatas  = unserialize(base64_decode($activity));

            $session = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth();
            $session->setUser($user);
            //$session->setLabel($activityDatas['descr']);
            // TODO: $session->setPlace(); appeler le service géoloc
            $session->setDescription($activityDatas['fullDesc']."\n Classement : ".$activityDatas['rank']);
            $session->setIssuedAt(new \DateTime($activityDatas['enDate'] != '' ? $activityDatas['enDate'] : 'now'));
            $session->setModifiedAt(new \DateTime('now'));
            $session->setDuration(new \DateTime("@".$activityDatas['secDuration']));
            $session->setDistance($activityDatas['distance']);
            $session->setWasOfficial(1);
            $session->setSource('ffa');
            $session->setType('session_endurance_on_earth');
            $session->setSport($sport);
            $em->persist($session);
            $em->flush();
            ++$numCreated;
            
            $activityRep->subscribeOnActivity($session, $user);
            $leagueLvlService->activitySessionEarnPoints($session, $user);
        }
        
        $responseDatas = array();
        $responseDatas['urlToRedirect']         = $this->generateUrl('ksActivity_activitiesList');
        $responseDatas['numActivitiesCreated']  = $numCreated;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    /**
     * @Route("/activitiesByParameters", name = "ksActivity_activitiesByParameters", options={"expose"=true} )
     */
    public function activitiesByParametersAction() {
        
        $em             = $this->getDoctrine()->getEntityManager();
        $request        = $this->getRequest();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $userRep        = $em->getRepository('KsUserBundle:User');
        $sportRep       = $em->getRepository('KsActivityBundle:Sport');
        $parameters     = $request->request->all();
        $user           = $userRep->find($parameters['userId']);
        
        if(!is_object($user)) {
           throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $parameters['userId'] . '.');
        }
        
        if( isset($parameters['indexPreviousMonth']) && $parameters['indexPreviousMonth'] != '') {
            $startOn = date('Y-m-01', strtotime("- " . $parameters['indexPreviousMonth'] ." month"));
            $endOn = date('Y-m-t', strtotime("- " . $parameters['indexPreviousMonth'] ." month"));
        } else {
            $startOn = $parameters['startOn'];
            $endOn = $parameters['endOn'];
        }
        
        $activitiesParameters = array(
            'user'      => $user,
            'startOn'   => $startOn,
            'endOn'     => $endOn,
            'perimeter' => 'me',
            'activityTypes' => array('session')
        );
        
        $sportDetails = array(
            "id"    => null,
            "label" => null,
            "type"  => null
        );
        
        if( isset($parameters['sportId']) && $parameters['sportId'] != '') {
            $sport          = $sportRep->find($parameters['sportId']);
            if( is_object($sport) ) {
                $activitiesParameters['sportId'] = $sport->getId();
                $sportDetails["id"] = $sport->getId();
                $sportDetails["label"] = $sport->getLabel();
                $sportDetails["type"] = $sport->getSportType()->getCode(); 
            }
        }
        
        if( isset($parameters['wasOfficial'])) {
            $activitiesParameters['wasOfficial'] = $parameters['wasOfficial'];
        }
        
        if( isset($parameters['resultCode']) && $parameters['resultCode'] != '') {
            $activitiesParameters['resultCode'] = $parameters['resultCode'];
        }
        
        $activities = $activityRep->findActivities($activitiesParameters); 
        
        $responseDatas = array();
        $responseDatas["html"] = $this->render('KsActivityBundle:Activity:_activities.html.twig', array(
            'activities'    => $activities
        ))->getContent();
        
        $responseDatas["sport"] = $sportDetails;
        $responseDatas["periode"] = $periode = "Du " . $startOn . " au " . $endOn;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    
    
    /**
     * @Route("/checkSynchronisationInProgress", name = "ksActivity_checkSynchronisationInProgress", options={"expose"=true} )
     */
    public function checkSynchronisationInProgressAction()
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $userHasServicesRep = $em->getRepository('KsUserBundle:UserHasServices');
        
        //On récupère l'utilisateur connecté
        $user = $this->container->get('security.context')->getToken()->getUser();
        
       // $activitiesSynchronizedHtml = "";
        $areBeingSynchronized = $userHasServicesRep->areBeingSynchronized($user->getId());
        
        $activitiesSynchronized = array();
        
        if( !$areBeingSynchronized ) {
            $activitiesSynchronized = $activityRep->findLastSynchronizedActivities(array(
                'user'      => $user,
            )); 
        }
        
        $responseDatas = array(
            "servicesAreBeingSynchronized"  => $areBeingSynchronized,
            "activitiesSynchronizedNb"      => count( $activitiesSynchronized )
        );
        
        $response = new Response( json_encode($responseDatas) );
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    /**
     * @Route("/checkUserHasActiveServices", name = "ksActivity_checkUserHasActiveServices", options={"expose"=true} )
     */
    public function checkUserHasActiveServicesAction()
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $userHasServicesRep = $em->getRepository('KsUserBundle:UserHasServices');
        
        //On récupère l'utilisateur connecté
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $userHasActiveServices = $userHasServicesRep->userHasActiveServices($user->getId());
        
        $responseDatas = array(
            "userHasActiveServices"  => $userHasActiveServices
        );
        
        $response = new Response( json_encode($responseDatas) );
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    
    ## RENDERS ##
    public function rightColumnAction()
    {   
        $em                 = $this->getDoctrine()->getEntityManager();
        $securityContext    = $this->container->get('security.context');
        $user               = $securityContext->getToken()->getUser();
        $userRep            = $em->getRepository('KsUserBundle:User');
        //$sportsmenSearchRep = $em->getRepository('KsActivityBundle:SportsmenSearch');
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $userChecklistActionRep = $em->getRepository('KsUserBundle:UserHasToDoChecklistAction');
        
        $usersKs = array("cedricdelpoux", "fred", "clem", "grichard", "ziiicos", "grichard", "gyom", "joemiage", "Natachab2", "zourito974", "chino.legrincheux");
        $texts = array();
        
        $texts[$usersKs[0]] = "J'avais des amis sous Runkeeper, d'autres sous Nike. Grâce à Keepinsport on peut tous comparer nos activités";
        $texts[$usersKs[1]] = "J'ai trouvé de la motivation pour faire plus de sport grâce aux ligues et étoiles !";
        $texts[$usersKs[2]] = "Je peux me comparer avec mes amis, quels que soient leurs sports";
        $texts[$usersKs[3]] = "La communication au sein de notre club est bien meilleure dorénavant";
        $texts[$usersKs[4]] = "J'adore le WikiSport et ses articles collaboratifs !";
        $texts[$usersKs[5]] = "Avec Keepinsport je peux partager mes activités avec tous mes amis, quel que soit l'outil qu'ils ont utilisé pour tracker leur activité (smartphone, montres spécialisées...)";
        $texts[$usersKs[6]] = "Avec keepinsport j'ai enfin un suivi global et unifié de toutes mes activités sportives !";
        $texts[$usersKs[7]] = "Essayer de gagner la ligue argent et passer en ligue or, ça me motive à me bouger !";
        $texts[$usersKs[8]] = "Youhou !!! Merci à Keepinsport pour ce bon d'achat, un site qui mérite à être connu et surtout à être utilisé !";
        $texts[$usersKs[9]] = "Grace a Keepinsport je peux enfin stocker et gérer mes performances de manière ludique et me tirer la bourre avec mes potes. Outil indispensable et très agréable à utiliser, que l'on soit simple pratiquant ou compétiteur. Belle initiative, bravo à toute l'équipe !";
        $texts[$usersKs[10]] = "Merci à KS pour le bon d'achat et merci aussi pour le site qui offre un service de qualité et qui marche super bien! longue vie à KS!";
        
        $citations = array();
        
        for ($i=0;$i<9;$i++) {
            $userToAffich = $userRep->findOneUser( array(
                "username" => $usersKs[$i]
            ), $this->get('translator'));
            
            $citations[] = array(
                "user" => $userToAffich,
                "text" => $texts[$usersKs[$i]]
            );
        }
        
        $aFriendsSuggest    = array();
        $aClubsSuggest      = array();
        $aCodeSports        = array();
        $agendaEvents       = array();
        $checklistActions   = array();
        
        /*Sponsors */
        $refSponsors = array();

        $refSponsors["running"] = array(
            "S4572B555C4D1419", 
            "S3786555C4D1D222", 
            "S3786555C4D1D276", 
            "S3786555C4D1D290", 
            "S3786555C4D1D225", 
            "S3786555C4D1D280",
            "S3786555C4D1D241",
            "S3786555C4D1D251",
            "S3786555C4D1D246",
            "S3786555C4D1D261",
            "S3786555C4D1D285",
            "S483CF555C4D1514",
            "S44C45555C4D118"
        );

        $refSponsors["cycling"] = array(
            "S3786555C4D1D122",
            "S48127555C4D1313",
            "S414E5555C4D119",
        );

        $refSponsors["musculation"] = array(
            "S431B5555C4D114",
            "S3291555C4D1713",
            "S2B0555C4D1D6"
        );

        $refSponsors["judo"] = array(
            "S46FD1555C4D134",
        );

        $refSponsors["karate"] = $refSponsors["judo"];

        $refSponsors["scuba-diving"] = array(
            "S418FD555C4D111",
        );

        $refSponsors["ski"] = array(
            "S41684555C4D1115",
            "S3C03555C4D1139",
            "S3C02555C4D1156",
            "S4142B555C4D2113",
            "S41A15555C4D115"
        );

        $refSponsors["snowboard"] = $refSponsors["ski"];

        $refSponsors["all"] = array(
            "S3CE1555C4D21654",
            "S447F1555C4D1415"
        );
            
        if( !$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $userRep->find(1);
            $isExpertMode = false;
        }
        else {
            $isExpertMode = $userRep->isExpertMode($user->getId());
        }

        $checklistActions = $userChecklistActionRep->findActionsToDo($user->getId());
        
        //FMO : traduction pour affichage direct sur le bloc de droite
        $checklistActionsWithLabelsOK = array();
        foreach( $checklistActions as $checklistAction ) {
            $checklistAction["label"] = $this->get('translator')->trans("checklist.".$checklistAction["code"]);
            //var_dump($checklistAction["label"]);
            $checklistActionsWithLabelsOK[] = array("id" => $checklistAction["id"], "label" => $checklistAction["label"], "code" => $checklistAction["code"], "date" => $checklistAction["date"]);
        }
        
        //Recherche de sportifs qui pourraient m'interesser
        $nearbyUsers        = $userRep->getRandomUserByProximity($user);
        $friendsOfFriends   = $userRep->getFriendsOfFriends($user);
        $usersInMyClubs     = $userRep->getUsersInMyClubs($user);
        $allUsers           = array_merge(array_merge($nearbyUsers, $friendsOfFriends), $usersInMyClubs);

        //Recherche de clubs qui pourraient m'interesser
        $fofIds = array();
        foreach ($friendsOfFriends as $friendOfFriend) {
            $fofIds[] = $friendOfFriend['id'];
        }
        $friendIds          = $userRep->getFriendIds($user->getId());
        $nearbyClubs        = $clubRep->getRandomClubByProximity($user);
        $clubsFof           = $clubRep->getClubsWithFriendsOfFriends($user, $fofIds, $friendIds);
        $allClubs           = array_merge($nearbyClubs, $clubsFof);

        foreach ($allUsers as $friend) {
            if (!isset($aFriendsSuggest[$friend['id']])) {
                $aFriendsSuggest[$friend['id']] = array();
            }
            $aFriendsSuggest[$friend['id']] = array_merge($aFriendsSuggest[$friend['id']], $friend);
        }
        shuffle($aFriendsSuggest);
        $aFriendsSuggest = array_slice($aFriendsSuggest, 0, 3);

        foreach ($allClubs as $club) {
            if (!isset($aClubsSuggest[$club['id']])) {
                $aClubsSuggest[$club['id']] = array();
            }
            $aClubsSuggest[$club['id']] = array_merge($aClubsSuggest[$club['id']], $club);
        }
        shuffle($aClubsSuggest);
        $aClubsSuggest = array_slice($aClubsSuggest, 0, 5);


        $agendaEvents       = $userRep->getAgendaEvents($user);



        if ( $user->getUserDetail() != null ) {
            foreach( $user->getUserDetail()->getSports() as $sport ) {
                $aCodeSports[] = $sport->getCodeSport();
            }
        }
        
        $serviceIdSport = null;
        
        //Si l'utilisateur a renseigné au moins 1 sport
        if( count( $aCodeSports ) > 0 ) {
            $rand = array_rand( $aCodeSports, 1);
            $serviceIdSport = $aCodeSports[$rand];
        } 
        
        //On verifie si on a des references pour ce sport là, sinon on prend une valeur au hazard
        if( $serviceIdSport == null || !array_key_exists( $serviceIdSport, $refSponsors ) ) { 
            $sportsSponsors = array_keys( $refSponsors );
            $rand = array_rand( $sportsSponsors, 1);  
            $serviceIdSport = $sportsSponsors[$rand]; 
        }
            
        $rand = array_rand( $refSponsors[$serviceIdSport], 1);  
        $refToAffich = $refSponsors[$serviceIdSport][$rand];
        
        
        return $this->render('KsActivityBundle:Activity:_rightColumn.html.twig', array(
            'isExpertMode'          => $isExpertMode,
            'aFriendsSuggest'       => $aFriendsSuggest,
            'aClubsSuggest'         => $aClubsSuggest,
            'agendaEvents'          => $agendaEvents,
            'checklistActions'      => $checklistActionsWithLabelsOK,        
            'refSponsor'            => $refToAffich,
            'citations'             => $citations
        ));
    }
    
    public function leftColumnAction()
    {    
        $securityContext    = $this->container->get('security.context');
        $em                 = $this->getDoctrine()->getEntityManager();
        $userRep            = $em->getRepository('KsUserBundle:User');
        
        $sportsmenSearchRep = $em->getRepository('KsActivityBundle:SportsmenSearch');
        $isExpertMode = false;
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user               = $securityContext->getToken()->getUser();
            
            $isExpertMode           = $userRep->isExpertMode($user->getId());
            $session = $this->get('session');
            $session->set('isExpertMode', $isExpertMode);
            
            $status                 = new \Ks\ActivityBundle\Entity\ActivityStatus($user);
            $statusForm             = $this->createForm(new \Ks\ActivityBundle\Form\ActivityStatusType(), $status);
            $link                   = new \Ks\ActivityBundle\Entity\ActivityStatus($user);
            $linkForm               = $this->createForm(new \Ks\ActivityBundle\Form\ActivityLinkType(), $link);
            $photo                  = new \Ks\ActivityBundle\Entity\ActivityStatus($user);
            $photoForm              = $this->createForm(new \Ks\ActivityBundle\Form\ActivityPhotoType(), $photo);
            
            //Propositions d'activités
            $nerbyProgrammedActivities = $sportsmenSearchRep->getRandomSportsmenSearchByProximity($user);
            shuffle($nerbyProgrammedActivities);
        }
        
        return $this->render('KsActivityBundle:Activity:_leftColumn.html.twig', array(
            'isExpertMode'              => $isExpertMode,
            'activityStatusForm'        => isset( $statusForm ) ? $statusForm->createView() : null,
            'linkForm'                  => isset( $linkForm ) ? $linkForm->createView() : null,
            'photoForm'                 => isset( $photoForm ) ? $photoForm->createView() : null,
            'aActivitiesSuggest'        => isset( $nerbyProgrammedActivities ) ? $nerbyProgrammedActivities : array()
        ));
    }
    
    public function rightColumn_newAction()
    {   
        $em                 = $this->getDoctrine()->getEntityManager();
        $securityContext    = $this->container->get('security.context');
        $user               = $securityContext->getToken()->getUser();
        $userRep            = $em->getRepository('KsUserBundle:User');
        //$sportsmenSearchRep = $em->getRepository('KsActivityBundle:SportsmenSearch');
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $userChecklistActionRep = $em->getRepository('KsUserBundle:UserHasToDoChecklistAction');
        
        $usersKs = array("cedricdelpoux", "fred", "clem", "grichard", "ziiicos", "grichard", "gyom", "joemiage", "Natachab2", "zourito974", "chino.legrincheux");
        $texts = array();
        
        $texts[$usersKs[0]] = "J'avais des amis sous Runkeeper, d'autres sous Nike. Grâce à Keepinsport on peut tous comparer nos activités";
        $texts[$usersKs[1]] = "J'ai trouvé de la motivation pour faire plus de sport grâce aux ligues et étoiles !";
        $texts[$usersKs[2]] = "Je peux me comparer avec mes amis, quels que soient leurs sports";
        $texts[$usersKs[3]] = "La communication au sein de notre club est bien meilleure dorénavant";
        $texts[$usersKs[4]] = "J'adore le WikiSport et ses articles collaboratifs !";
        $texts[$usersKs[5]] = "Avec Keepinsport je peux partager mes activités avec tous mes amis, quel que soit l'outil qu'ils ont utilisé pour tracker leur activité (smartphone, montres spécialisées...)";
        $texts[$usersKs[6]] = "Avec keepinsport j'ai enfin un suivi global et unifié de toutes mes activités sportives !";
        $texts[$usersKs[7]] = "Essayer de gagner la ligue argent et passer en ligue or, ça me motive à me bouger !";
        $texts[$usersKs[8]] = "Youhou !!! Merci à Keepinsport pour ce bon d'achat, un site qui mérite à être connu et surtout à être utilisé !";
        $texts[$usersKs[9]] = "Grace a Keepinsport je peux enfin stocker et gérer mes performances de manière ludique et me tirer la bourre avec mes potes. Outil indispensable et très agréable à utiliser, que l'on soit simple pratiquant ou compétiteur. Belle initiative, bravo à toute l'équipe !";
        $texts[$usersKs[10]] = "Merci à KS pour le bon d'achat et merci aussi pour le site qui offre un service de qualité et qui marche super bien! longue vie à KS!";
        
        $citations = array();
        
        for ($i=0;$i<9;$i++) {
            $userToAffich = $userRep->findOneUser( array(
                "username" => $usersKs[$i]
            ), $this->get('translator'));
            
            $citations[] = array(
                "user" => $userToAffich,
                "text" => $texts[$usersKs[$i]]
            );
        }
        
        $aFriendsSuggest    = array();
        $aClubsSuggest      = array();
        $aCodeSports        = array();
        $agendaEvents       = array();
        $checklistActions   = array();
        
        /*Sponsors */
        $refSponsors = array();

        $refSponsors["running"] = array(
            "S4572B555C4D1419", 
            "S3786555C4D1D222", 
            "S3786555C4D1D276", 
            "S3786555C4D1D290", 
            "S3786555C4D1D225", 
            "S3786555C4D1D280",
            "S3786555C4D1D241",
            "S3786555C4D1D251",
            "S3786555C4D1D246",
            "S3786555C4D1D261",
            "S3786555C4D1D285",
            "S483CF555C4D1514",
            "S44C45555C4D118"
        );

        $refSponsors["cycling"] = array(
            "S3786555C4D1D122",
            "S48127555C4D1313",
            "S414E5555C4D119",
        );

        $refSponsors["musculation"] = array(
            "S431B5555C4D114",
            "S3291555C4D1713",
            "S2B0555C4D1D6"
        );

        $refSponsors["judo"] = array(
            "S46FD1555C4D134",
        );

        $refSponsors["karate"] = $refSponsors["judo"];

        $refSponsors["scuba-diving"] = array(
            "S418FD555C4D111",
        );

        $refSponsors["ski"] = array(
            "S41684555C4D1115",
            "S3C03555C4D1139",
            "S3C02555C4D1156",
            "S4142B555C4D2113",
            "S41A15555C4D115"
        );

        $refSponsors["snowboard"] = $refSponsors["ski"];

        $refSponsors["all"] = array(
            "S3CE1555C4D21654",
            "S447F1555C4D1415"
        );
            
        if( !$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $userRep->find(1);
            $isExpertMode = false;
        }
        else {
            $isExpertMode = $userRep->isExpertMode($user->getId());
        }

        $checklistActions = $userChecklistActionRep->findActionsToDo($user->getId());
        
        //FMO : traduction pour affichage direct sur le bloc de droite
        $checklistActionsWithLabelsOK = array();
        foreach( $checklistActions as $checklistAction ) {
            $checklistAction["label"] = $this->get('translator')->trans("checklist.".$checklistAction["code"]);
            //var_dump($checklistAction["label"]);
            $checklistActionsWithLabelsOK[] = array("id" => $checklistAction["id"], "label" => $checklistAction["label"], "code" => $checklistAction["code"], "date" => $checklistAction["date"]);
        }
        
        //Recherche de sportifs qui pourraient m'interesser
        $nearbyUsers        = $userRep->getRandomUserByProximity($user);
        $friendsOfFriends   = $userRep->getFriendsOfFriends($user);
        $usersInMyClubs     = $userRep->getUsersInMyClubs($user);
        $allUsers           = array_merge(array_merge($nearbyUsers, $friendsOfFriends), $usersInMyClubs);

        //Recherche de clubs qui pourraient m'interesser
        $fofIds = array();
        foreach ($friendsOfFriends as $friendOfFriend) {
            $fofIds[] = $friendOfFriend['id'];
        }
        $friendIds          = $userRep->getFriendIds($user->getId());
        $nearbyClubs        = $clubRep->getRandomClubByProximity($user);
        $clubsFof           = $clubRep->getClubsWithFriendsOfFriends($user, $fofIds, $friendIds);
        $allClubs           = array_merge($nearbyClubs, $clubsFof);

        foreach ($allUsers as $friend) {
            if (!isset($aFriendsSuggest[$friend['id']])) {
                $aFriendsSuggest[$friend['id']] = array();
            }
            $aFriendsSuggest[$friend['id']] = array_merge($aFriendsSuggest[$friend['id']], $friend);
        }
        shuffle($aFriendsSuggest);
        $aFriendsSuggest = array_slice($aFriendsSuggest, 0, 3);

        foreach ($allClubs as $club) {
            if (!isset($aClubsSuggest[$club['id']])) {
                $aClubsSuggest[$club['id']] = array();
            }
            $aClubsSuggest[$club['id']] = array_merge($aClubsSuggest[$club['id']], $club);
        }
        shuffle($aClubsSuggest);
        $aClubsSuggest = array_slice($aClubsSuggest, 0, 5);


        $agendaEvents       = $userRep->getAgendaEvents($user);



        if ( $user->getUserDetail() != null ) {
            foreach( $user->getUserDetail()->getSports() as $sport ) {
                $aCodeSports[] = $sport->getCodeSport();
            }
        }
        
        $serviceIdSport = null;
        
        //Si l'utilisateur a renseigné au moins 1 sport
        if( count( $aCodeSports ) > 0 ) {
            $rand = array_rand( $aCodeSports, 1);
            $serviceIdSport = $aCodeSports[$rand];
        } 
        
        //On verifie si on a des references pour ce sport là, sinon on prend une valeur au hazard
        if( $serviceIdSport == null || !array_key_exists( $serviceIdSport, $refSponsors ) ) { 
            $sportsSponsors = array_keys( $refSponsors );
            $rand = array_rand( $sportsSponsors, 1);  
            $serviceIdSport = $sportsSponsors[$rand]; 
        }
            
        $rand = array_rand( $refSponsors[$serviceIdSport], 1);  
        $refToAffich = $refSponsors[$serviceIdSport][$rand];
        
        
        return $this->render('KsActivityBundle:Activity:_rightColumn.html.twig', array(
            'isExpertMode'          => $isExpertMode,
            'aFriendsSuggest'       => $aFriendsSuggest,
            'aClubsSuggest'         => $aClubsSuggest,
            'agendaEvents'          => $agendaEvents,
            'checklistActions'      => $checklistActionsWithLabelsOK,        
            'refSponsor'            => $refToAffich,
            'citations'             => $citations
        ));
    }
    
    public function synchronizationLoaderAction()
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $userHasServicesRep = $em->getRepository('KsUserBundle:UserHasServices');
         
        //On récupère l'utilisateur connecté
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $areBeingSynchronized = $userHasServicesRep->areBeingSynchronized($user->getId());
        return $this->render('KsActivityBundle:Activity:_synchronisationInProgress.html.twig', array(
            'areBeingSynchronized'             => $areBeingSynchronized,
        ));
    }
    
    /**
     * @Route("/upload/{uploadDirName}", requirements={"method" = "GET|POST|HEAD|PUT|DELETE"}, name = "ksActivity_ajax_uploadGPX" )
     */
    public function uploadAction($uploadDirName) {
        $em = $this->getDoctrine()->getEntityManager();

        $request = $this->get('request');

        $uploadGPXDirAbsolute = dirname($_SERVER['SCRIPT_FILENAME']). '/uploads/' . $uploadDirName .'/';
        if (! is_dir( $uploadGPXDirAbsolute ) ) mkdir($uploadGPXDirAbsolute);
        
        if( $uploadDirName == "gpx") {
    
            $options = array(
                'upload_dir' => $uploadGPXDirAbsolute,
                'upload_url' => $this->getFullUrl().'/uploads/' . $uploadDirName
            );
        }
        
        $upload_handler = new \Ks\ImageBundle\Classes\UploadHandler($options, $this->generateUrl('ksActivity_ajax_uploadGPX', array('uploadDirName' => $uploadDirName)));

        switch ($request->getMethod()) {
            case 'OPTIONS':
                break;
            case 'HEAD':
            case 'GET':
                $upload_handler->get();
                break;
            case 'POST':
                if ($request->get('_method') === 'DELETE') {
                    $upload_handler->delete();
                } else {
                    $upload_handler->post();
                }
                break;
            case 'DELETE':
                $upload_handler->delete();
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
        }

        $response = new Response();
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Content-Disposition', 'inline; filename="files.json"');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'OPTIONS, HEAD, GET, POST, PUT, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'X-File-Name, X-File-Type, X-File-Size');
        return $response;
    }
    
    function getFullUrl() {
      	return
    		(isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
    		(isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
    		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
    		(isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] === 443 ||
    		$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
    		substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }
    
    /**
     * @Route("/addUserAction/{action}/{type}/{result}/{error}", name = "ksActivity_addUserAction", options={"expose"=true}  )
     */
    public function addUserAction($action, $type, $result, $error)
    {    
        $em                 = $this->getDoctrine()->getEntityManager();
        $userActionRep      = $em->getRepository('KsActivityBundle:UserAction');
        $securityContext    = $this->container->get('security.context');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $user               = $this->get('security.context')->getToken()->getUser();

        $userAction = new \Ks\ActivityBundle\Entity\UserAction();
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $this->get('security.context')->getToken()->getUser();
        }
        else {
            $user = $userRep->find(1);
        }
        
        $userAction->setUserId($user->getId());
        $userAction->setAction($action);
        $userAction->setType($type);
        $userAction->setResult($result);
        $userAction->setError($error);
        $userAction->setDoneAt(new \DateTime("now"));
        
        $em->persist($userAction);
        $em->flush();
        
        $responseDatas = array(
            'response' => true
        ); 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/saveCanevas/{activityId}", name = "ksActivity_saveCanevas", options={"expose"=true}  )
     */
    public function saveCanevasAction($activityId)
    {    
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();

        $img = $_POST['dataURL'];
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        
        $imgDirRelative = $this->container->get('templating.helper.assets')->getUrl('img');
        //$activitiesDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/activities/";
        $activityDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/activities/" .$activityId."/";
        //$activityDirAbsolute = $this->container->getParameter('path_web') . "/img/activities/".$activityId."/";
        
        if (! is_dir( $activityDirAbsolute ) ) mkdir( $activityDirAbsolute );
        
        $file = $activityDirAbsolute. $activityId. ".png";
        $success = file_put_contents($file, $data);

        $responseDatas = array(
            'response' => $success,
            'file' => $file
        ); 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/getFindEquipments/{userId}/{sportId}/{equipments}", name = "ksActivity_getFindEquipments", options={"expose"=true}  )
     */
    public function getFindEquipmentsAction($userId, $sportId, $equipments)
    {    
        $em                 = $this->getDoctrine()->getEntityManager();
        $equipmentRep       = $em->getRepository('KsUserBundle:Equipment');
        $user               = $this->get('security.context')->getToken()->getUser();

        //Récupération des équipments activés par défaut selon le sport sélectionné
        $equipments = $equipmentRep->findEquipments(array(
            "isByDefault" => true,
            "sport_id"    => $sportId,
            "userId"      => $userId
        ));
        
        $responseDatas = array(
            'response' => true,
            'equipments' => $equipments
        ); 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    function secondesToTimeDuration($duration) {
        $heure = intval(abs($duration / 3600));
        $duration = $duration - ($heure * 3600);
        $minute = intval(abs($duration / 60));
        $duration = $duration - ($minute * 60);
        $seconde = round($duration);
        //$time = new \DateTime("$heure:$minute:$seconde");
        //$time = $heure."H ".$minute."' ".$seconde."''";
        if ($seconde ==0) $time = "$heure h $minute min";
        else $time = "$heure h $minute min $seconde";
        return $time;
    }
    
    public function html_to_js_var($t){
        return str_replace('</script>','<\/script>',addslashes(str_replace("\r",'',str_replace("\n","",$t))));
    }
    
    public function var_to_js($jsname,$a, $array){
        $ret='';
        if (is_array($a) ) {
            if ($array) 
                $ret.=$jsname.'= new Array();
                ';
            else
                $ret.=$jsname.'= new Object();
                ';

            foreach ($a as $k => $a) {
                if (is_int($k) || is_integer($k))
                    $ret.= $this->var_to_js($jsname.'['.$k.']',$a, 0);
                else
                    $ret.= $this->var_to_js($jsname."['".$k."']",$a, 0);
            }

        }
        elseif (is_bool($a)) {
            $v=$a ? "true" : "false";
            $ret.=$jsname.'='.$v.';
            ';
        }
        elseif (is_int($a) || is_integer($a) || is_double($a) || is_float($a)) {
           $ret.=$jsname.'='.$a.';
            ';
        }
        elseif (is_string($a)) {
           $ret.=$jsname.'=\''.$this->html_to_js_var($a).'\';
            ';
        }
        return $ret;
    }
    
    /**
     * 
     * @Route("/noteOnActivity/{activityId}", requirements={"activityId" = "\d+"}, name = "ksActivity_noteOnActivity", options={"expose"=true} )
     * @param int $activityId
     */
    public function noteOnActivityAction($activityId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $activityRep            = $em->getRepository('KsActivityBundle:Activity');
        $activityNoteRep        = $em->getRepository('KsActivityBundle:ActivityNote');
        $activityHasNotesRep    = $em->getRepository('KsActivityBundle:ActivityHasNotes');
        $user                   = $this->get('security.context')->getToken()->getUser();
        
        $request        = $this->getRequest();
        $parameters     = $request->request->all();
        
        $newNotes = array();
        $newNotes[] = isset( $parameters["signs"]) ? $parameters["signs"] : 0;
        $newNotes[] = isset( $parameters["food"]) ? $parameters["food"] : 0;
        $newNotes[] = isset( $parameters["promoters"]) ? $parameters["promoters"] : 0;
        $newNotes[] = isset( $parameters["trace"]) ? $parameters["trace"] : 0;
        $newNotes[] = isset( $parameters["awards"]) ? $parameters["awards"] : 0;
        
        $activity = $activityRep->find($activityId);
        
        if (!is_object($activity) ) {
            $impossibleActivityMsg = $this->get('translator')->trans('impossible-to-find-activity-%activityId%', array('%activityId%' => $activityId));
            throw new AccessDeniedException($impossibleActivityMsg);
        }
        
        $responseDatas = array();
        $responseDatas["notes"] = array();
        $myNotes = $activityHasNotesRep->findBy(array("activity" => $activity->getId(), "noter" => $user->getId()));
        $notes = $activityNoteRep->findBy(array("activityNoteType" => 1)); //Note pour les compétitions
        
        if (count($myNotes) != 0) {
            foreach($notes as $key => $note) {
                $myNotes = $activityHasNotesRep->findBy(array("activity" => $activity->getId(), "noter" => $user->getId(), "activityNote" => $note->getId()));
                $myNote = $myNotes[0];
                if (isset($myNote) && !is_null($myNote)) {
                    $myNote->setVal($newNotes[$key]);
                    $em->persist($myNote);
                    $em->flush();
                }
                $responseDatas["notes"][$note->getCode()] = array("average" => $activityRep->getNoteOnActivity($activity, $note));
            }
        }
        else {
            foreach($notes as $key => $note) {
                $activityRep->noteOnActivity($activity, $user, $note, $newNotes[$key]);
                $responseDatas["notes"][$note->getCode()] = array("average" => $activityRep->getNoteOnActivity($activity, $note));
            }
        }
        $responseDatas["code"]  = 1;

        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/getMyNoteOnActivity/{activityId}", requirements={"activityId" = "\d+"}, name = "ksActivity_getMyNoteOnActivity", options={"expose"=true} )
     * @param int $activityId
     */
    public function getMyNoteOnActivityAction($activityId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $activityRep            = $em->getRepository('KsActivityBundle:Activity');
        $activityNoteRep        = $em->getRepository('KsActivityBundle:ActivityNote');
        $activityHasNotesRep    = $em->getRepository('KsActivityBundle:ActivityHasNotes');
        $user                   = $this->get('security.context')->getToken()->getUser();
        
        $request        = $this->getRequest();
        $parameters     = $request->request->all();
        
        $activity = $activityRep->find($activityId);
        
        if (!is_object($activity) ) {
            $impossibleActivityMsg = $this->get('translator')->trans('impossible-to-find-activity-%activityId%', array('%activityId%' => $activityId));
            throw new AccessDeniedException($impossibleActivityMsg);
        }
        
        $responseDatas = array("code" => 1);
        $myNotes = $activityHasNotesRep->findBy(array("activity" => $activity->getId(), "noter" => $user->getId()));
        
        if (count($myNotes) != 0) {
            $responseDatas["code"] = 1;
            $responseDatas["notes"] = array();
            foreach($myNotes as $key => $myNote) {
                $responseDatas["notes"][$myNote->getActivityNote()->getCode()] = array("myVal" => $myNote->getVal(), "average" => $activityRep->getNoteOnActivity($activity, $myNote->getActivityNote()));
            }
        }
        else $responseDatas["code"] = 0;
        
        
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/downloadTCXFromActivity/{idFromService}/{userId}", requirements={"idFromService" = "\d+"}, name = "ksActivity_downloadTCXFromActivity", options={"expose"=true} )
     * @param int $idFromService 
     * @param int $userId
     */
    public function downloadTCXFromActivityAction($idFromService, $userId) {
        $em                 = $this->getDoctrine()->getEntityManager();
        $garminServiceRep   = $em->getRepository('KsUserBundle:Service');
        $userHasServiceRep  = $em->getRepository('KsUserBundle:UserHasServices');
        
        $responseDatas = array();
        
        $garminService = $garminServiceRep->findOneByName('Garmin');
        $userService   = $userHasServiceRep->findOneBy(array(
            'user'       => $userId,
            'service'    => $garminService->getId()
        ));
        $credentials   = array(
            'username'   => $userService->getConnectionId(),
            'password'   => base64_decode($userService->getConnectionPassword()),
            'identifier' => microtime(true)
        );
        
        $garminApi     = new \dawguk\GarminConnect($credentials);
        
        try { 
            $tcx = $garminApi->getDataFile('tcx', $idFromService);
            //var_dump($tcx);exit;
            
            $responseDatas["tcx"] = $tcx;
            $responseDatas["idFromService"] = $idFromService;
            
        } catch (\dawguk\GarminConnect\exceptions\UnexpectedResponseCodeException $e) {
            return array(array(), false, 403);
        };
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
