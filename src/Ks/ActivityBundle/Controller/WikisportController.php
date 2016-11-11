<?php

namespace Ks\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Article controller.
 *
 * 
 */
class WikisportController extends Controller
{
    /**
     * Lists all Article entities.
     *
     * @Route("/index", name="ksWikisport_index", options={"expose"=true})
     * @Template()
     */
    public function indexAction()
    {
        $securityContext    = $this->container->get('security.context');
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $articleTagRep      = $em->getRepository('KsActivityBundle:ArticleTag');
        $equipmentRep       = $em->getRepository('KsUserBundle:Equipment');
        $coachingPlanRep    = $em->getRepository('KsCoachingBundle:CoachingPlan');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $agendaRep          = $em->getRepository('KsAgendaBundle:Agenda');
        
        if( !$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            //return new RedirectResponse($this->container->get('router')->generate('_login'));
            $user = $userRep->find(1);
        }
        else $user = $securityContext->getToken()->getUser();
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'wikisport');
        
        $articlesCategories  = $articleTagRep->findBy(array("isCategory" => true));
        
        krsort($articlesCategories);
        
        $articles = $subscriptions = array();
        
        foreach( $articlesCategories as $articlesCategoriy ) {
            $articleTagId = $articlesCategoriy->getId();
            
            $articles = $activityRep->findActivities(array(
                'activityTypes' => array('article'),
                'categoryTagId' => $articleTagId,
                'user' => $user,
                //'activitiesFrom' => array('me', 'my_friends', 'public')
            ));
            
            $subscriptions = $activityRep->findActivities(array(
                'activityTypes' => array('article'),
                'categoryTagId' => $articleTagId,
                'subscriberId'  => $user->getId()
            ));
            
            //Ajout de données pour les articles de type Matériel
            if ($articleTagId == $articleTagRep->findOneByLabel("Matériel")->getId()) {
                foreach($articles as $key => $article) {
                    $params = array(
                        'userId'        => $user->getId(),
                        'activityId'    => $article['activity']['id']
                    );
                    
                    $equipments = $equipmentRep->findEquipments($params, $this->get('translator'));
                    
                    if (isset($equipments[0])) {
                        $articles[$key]['equipment'] = $equipments[0];
                        if (in_array($article, $subscriptions)) $subscriptions[array_search($article, $subscriptions)]['equipment'] = $equipments[0];
                    }
                    else {
                        $articles[$key]['equipment'] = array('id' => null, 'name' => '', 'brand' => '', 'avatar' => null);
                        if (in_array($article, $subscriptions)) $subscriptions[array_search($article, $subscriptions)]['equipment'] = array('id' => null, 'name' => '', 'brand' => '', 'avatar' => null);
                    }
                }
            }
            //Ajout de données pour les articles de type Plan d'entrainement
            if ($articleTagId == $articleTagRep->findOneByLabel("Programme Entrainement")->getId()) {
                foreach($articles as $key => $article) {
                    //Membres qui ont utilisé ce plan
                    $plan = $coachingPlanRep->find($article['activity']['activityCoachingPlanId']);
                    $sons = $coachingPlanRep->findByFather($plan->getId());
                    $users = array();
                    $userUsesPlan = $userRep->findUsers(array("userId" => $plan->getUser()->getId()), $this->get('translator'));
                    $users[] = $userUsesPlan;
                    foreach ($sons as $son) {
                        $userUsesPlan = $userRep->findUsers(array("userId" => $son->getUser()->getId()), $this->get('translator'));
                        if (!in_array($userUsesPlan, $users)) $users[] = $userUsesPlan;
                    }
                    $articles[$key]['users'] = $users;
                    if (in_array($article, $subscriptions)) $subscriptions[array_search($article, $subscriptions)]['users'] = $users;
                    
                    //Calcul du nombre de semaines
                    $plan = $coachingPlanRep->find($plan->getId());
                    if (!is_null($plan->getUser())) $userIdForFlatView = $plan->getUser()->getId();
                    else $userIdForFlatView = null;
                    
                    $events = $agendaRep->findAgendaEvents(array(
                        "planId"                    => $plan->getId(),
                        "getUserSessionForFlatView" => false,
                        "userIdForFlatView"         => $userIdForFlatView,
                        "order"                     => array("DATE(e.startDate)" => "ASC")
                    ), $this->get('translator'));

                    //var_dump($events);exit;

                    $lastDay ='';

                    if (count($events) >0 ) {
                        //Récupération de la 1ère et dernière date du plan + nombre de semaines
                        $firstDay = new \DateTime($events[0]['start']);
                        $lastDay = new \DateTime($events[count($events)-1]['start']);
                        $weeksNumber = ceil(abs($firstDay->diff($lastDay)->format('%R%a')) / 7);

                        $firstWeek = $events[0]['weekNumber'];
                        
                        $totalCoachingDuration =0;
                        foreach($events as $event) {
                            if ($event['weekNumber'] != $firstWeek) {
                                $firstWeek = $event['weekNumber'];
                                $weeks[] = $week;
                                unset($week);
                                $week[] = $event;
                            }
                            else {
                                $week[] = $event;
                            }
                            if ($event['sport_id'] != null && $event['sport_label'] != 'empty') {
                                if ($event['eventDurationMin'] != null && $event['eventDurationMax'] != null) $coachingDuration = 1/2 * ($this->dateTimeToSeconds(new \DateTime($event['eventDurationMin'])) + $this->dateTimeToSeconds(new \DateTime($event['eventDurationMax'])));
                                else $coachingDuration = 0;
                                $totalCoachingDuration += $coachingDuration;
                            }
                        }
                        $weeks[] = $week;
                    }
                    $articles[$key]['weeks'] = $weeks;
                    $articles[$key]['duration'] = $this->secondesToTimeDuration($totalCoachingDuration);
                    if (in_array($article, $subscriptions)) {
                        $subscriptions[array_search($article, $subscriptions)]['weeks'] = $weeks;
                        $subscriptions[array_search($article, $subscriptions)]['duration'] = $this->secondesToTimeDuration($totalCoachingDuration);
                    }
                }
            }
            $allArticles[ $articleTagId ] = $articles;
            $allSubscriptions[ $articleTagId ] = $subscriptions;
        }
        
        return array(
            "userId"                => ( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ? $user->getId() : "-1"),
            "articlesCategories"    => $articlesCategories,
            "articles"              => $allArticles,
            "subscriptions"         => $allSubscriptions,
            "users"                 => $users
        );
    }
    
    function dateTimeToSeconds(\DateTime $dateTime) {
         $hours     = $dateTime->format('H');
         $minutes   = $dateTime->format('i');
         $seconds   = $dateTime->format('s');
         
         return $seconds + 60 * $minutes + $hours * 60 * 60;
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
    
    /**     
     * @Route("/{id}/show", name="ksWikisport_show", options={"expose"=true})
     * @Template()
     */
    public function showAction($id)
    {      
        $em                 = $this->getDoctrine()->getEntityManager();
        $securityContext    = $this->container->get('security.context');
        $user               = $securityContext->getToken()->getUser();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        $modificationsRep   = $em->getRepository('KsActivityBundle:UserModifiesArticle');
        
        $session = $this->get('session');
        $session->set('pageType', 'wikisport');
        $session->set('page', 'showArticle');
        
        if( !$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            //return new RedirectResponse($this->container->get('router')->generate('_login'));
            $user = $userRep->find(1);
        }
        
        $article = $activityRep->findActivities(array(
            "activityId" => $id
        ));
        
        //Récupération des données de l'event associé
        $articleEvent = $articleRep->find($id);
        
        //Récupération des photos
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        $articlePhotos      = $articleRep->find($id);
        if (!$articlePhotos) {
            throw $this->createNotFoundException('Unable to find Article entity.');
        }
        $lastArticleModifications = $modificationsRep->getLastModification($articlePhotos);
        if ( ! empty($lastArticleModifications)) {
            $articleContent = json_decode($lastArticleModifications->getContent(), true);
        }        
        
        // on récupère les trackingDatas uniquement pour l'affichage détaillé des articles de type compétitions
        $rawTrackingDatas = $activityRep->getRawTrackingDatasOnActivityId($id);
        
        return array(
            "userId"                => $user->getId(),
            "article"               => $article,
            "articleEvent"          => $articleEvent,
            "rawTrackingDatas"      => $rawTrackingDatas,
            "articleContent"        => $articleContent
        );
    }
    
    /**     
     * @Route("/{id}/edit", name="ksWikisport_edit", options={"expose"=true})
     * @Template()
     */
    public function editAction($id)
    {      
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        $modificationsRep   = $em->getRepository('KsActivityBundle:UserModifiesArticle');
        
        $session = $this->get('session');
        $session->set('pageType', 'wikisport');
        $session->set('page', 'editArticle');
        
        $articleArray = $activityRep->getActivityDatas($id);
        $article      = $articleRep->find($id);
        
        //Récupération des photos
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        $articlePhotos      = $articleRep->find($id);
        if (!$articlePhotos) {
            throw $this->createNotFoundException('Unable to find Article entity.');
        }
        $lastArticleModifications = $modificationsRep->getLastModification($articlePhotos);
        $articleContent = array();
        $articleContent['photos'] = array();
        if ( ! empty($lastArticleModifications)) {
            $articleContent = json_decode($lastArticleModifications->getContent(), true);
        }
        
//        if( $article->getIsBeingEdited() )  {
//        
//            $redirectionShow = false;
//            //Si l'article est en cours d'édition depuis plus de 15 minutes
//            $isBeingEditedDate = $article->getIsBeingEditedDate();
//            if( $isBeingEditedDate != null ) {
//                $timeSinceLastEdition = time() - $isBeingEditedDate->getTimestamp();
//                if ( $timeSinceLastEdition < 900 ) $redirectionShow = true;
//            } else $redirectionShow = true;
//            
//            //On sette une flash (Message qui apparaitra au chargement de la prochaine page)
//            
//            if( $redirectionShow ) {
//                $this->get('session')->setFlash('alert alert-error', 'article.flash.article_is_being_edited');
//                return new RedirectResponse($this->generateUrl('ksArticle_show', array("articleId" => $article->getId())));
//            }
//        }
        
//        //On indique que l'article est en cours d'édition
//        $article->setIsBeingEditedDate(new \DateTime());
//        $article->setIsBeingEdited(true);
//        $em->persist($article);
//        $em->flush();
        
        //var_dump($articleContent);
        
        //Récupération du champ "place"
        $form = $this->createForm(new \Ks\ActivityBundle\Form\ArticleEventType(), $article)->createView();
        
        //Affichage du choix du sport
        $sportChoiceForm = $this->createForm(new \Ks\ActivityBundle\Form\SportType(null), $article);
        
        return array(
            "form"    => $form,
            "sportChoiceForm" => $sportChoiceForm->createView(),
            "article" => $articleArray,
            "articleContent" => $articleContent
        );
    }
    
    /**     
     * @Route("/{id}/duplicate", name="ksWikisport_duplicate", options={"expose"=true})
     * @Template()
     */
    public function duplicateAction($id)
    {      
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $articleRep         = $em->getRepository('KsActivityBundle:Article');
        $modificationsRep   = $em->getRepository('KsActivityBundle:UserModifiesArticle');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $user               = $this->get('security.context')->getToken()->getUser();
        
        $session = $this->get('session');
        $session->set('pageType', 'wikisport');
        
        //Duplicate activity
        
        $activity = $activityRep->find($id);
        $newArticle = new \Ks\ActivityBundle\Entity\Article();
        $newArticle->setUser($user);
        if (!is_null($activity->getSport())) $newArticle->setSport($activity->getSport());
        $newArticle->setLabel("COPIE - " . $activity->getLabel());
        $newArticle->setDescription($activity->getDescription());
        $newArticle->setDistance($activity->getDistance());
        $newArticle->setElevationGain($activity->getElevationGain());
        $newArticle->setElevationLost($activity->getElevationLost());
        $newArticle->setCategoryTag($activity->getCategoryTag());
        if (!is_null($activity->getTrackingDatas())) $newArticle->setTrackingDatas($activity->getTrackingDatas());
        
        $place = $activity->getPlace();
        if (!is_null($place)) {
            $newPlace = new \Ks\EventBundle\Entity\Place();
            $newPlace->setFullAdress($place->getFullAdress());
            $newPlace->setCountryCode($place->getCountryCode());
            $newPlace->setRegionLabel($place->getRegionLabel());
            $newPlace->setTownLabel($place->getTownLabel());
            $newPlace->setLongitude($place->getLongitude());
            $newPlace->setLatitude($place->getLatitude());
            $newPlace->setCountryLabel($place->getCountryLabel());
            $newPlace->setRegionCode($place->getRegionCode());
            $newPlace->setCountyCode($place->getCountryCode());
            $newPlace->setCountyLabel($place->getCountyLabel());
            $newPlace->setTownLabel($place->getTownLabel());
            $em->persist($newPlace);
            $em->flush();
            $newArticle->setPlace($newPlace);
        }
        
        $event = $activity->getEvent();
        if (!is_null($event)) {
            $newEvent = new \Ks\EventBundle\Entity\Event();
            $newEvent->setName($event->getName());
            $newEvent->setContent($event->getContent());
            $startDate = clone $event->getStartDate();
            $endDate = clone $event->getEndDate();
            $newEvent->setStartDate($startDate->add(new \DateInterval('P365D')));
            $newEvent->setEndDate($endDate->add(new \DateInterval('P365D')));
            $newEvent->setTypeEvent($event->getTypeEvent());
            $newEvent->setIsAllDay($event->getIsAllDay());
            if (!is_null($event->getSport())) $newEvent->setSport($event->getSport());
            $em->persist($newEvent);
            $em->flush();
            $newArticle->setEvent($newEvent);
        }
        
        $em->persist($newArticle);
        $em->flush();
        
        $lastArticleModifications = $modificationsRep->getLastModification( $activity );
        
        $newArticleModifications = new \KS\ActivityBundle\Entity\UserModifiesArticle($newArticle, $user);
        
        $newArticleModifications->setUser($user);
        $newArticleModifications->setContent($lastArticleModifications->getContent());
        $newArticleModifications->setTitleWasChanged(false);
        $em->persist($newArticleModifications);
        $em->flush();
        
        
        //Show duplicate article
        return new RedirectResponse($this->generateUrl('ksWikisport_show', array("id" => $newArticle->getId())));
    }
    
    /**     
     * @Route("/{id}/update", name="ksWikisport_update", options={"expose"=true})
     */
    public function updateAction($id)
    {      
        $securityContext    = $this->container->get('security.context');
        $em                 = $this->getDoctrine()->getEntityManager();
        $request            = $this->getRequest();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        
        //appels aux services
        $trophyService          = $this->get('ks_trophy.trophyService');
        $notificationService    = $this->get('ks_notification.notificationService');
        
        $parameters = $request->request->all();
        $articleDescription = isset( $parameters["description"] ) ? $parameters["description"] : "" ;
        
        $article = $activityRep->find( $id );
        
        if( is_object( $article ) ) {
            $article->setDescription( $articleDescription );
            $em->persist( $article );
            $em->flush();
            
            //$this->get('session')->setFlash('alert alert-success', 'Les modifications ont été enregistrés avec succès');
            //return new RedirectResponse($this->generateUrl('ksWikisport_show', array("id" => $article->getId())));
            //Création d'une notification FMO : déjà effectué via articleController !
//            if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
//                $notificationType_name = "edit";
//                $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName($notificationType_name);
//
//                if (!$notificationType) {
//                    throw $this->createNotFoundException('Impossible de trouver le type de notification ' . $notificationType_name . '.');
//                }
//
//                $user               = $securityContext->getToken()->getUser();
//
//                $activityRep->collaborationOnArticle($article, $user, $notificationType);
//
//                //On envoie une notification à tout les abonnés de l'article
//                foreach($article->getSubscribers() as $key => $activityHasSubscribers) {
//                    $subscriber = $activityHasSubscribers->getSubscriber();
//
//                    //S'il ne s'est pas déabonné de l'activité
//                    if ($activityRep->hasNotUnsubscribed($article, $subscriber)) {
//
//                        //Si l'abonné n'est pas lui même et qu'il n'a pas posté l'activité
//                        if($subscriber != $user) {
//                            $message =  $user->getUsername() . " a collaboré à l'article " . base64_decode( $article->getLabel() );
//                            $notificationService->sendNotification($article, $user, $subscriber, $notificationType_name, $message);  
//                        }
//                    }
//                }
//            }
            
        }
        
        $responseDatas = array(
            "code"          => 1,
            //"description"   => $articleDescription
        );
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * Lists all Article entities.
     *
     * @Route("/category/{tagId}/list", name="ksWikisport_category")
     * @Template()
     */
    public function categoryAction($tagId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $activityRep         = $em->getRepository('KsActivityBundle:Activity');
        $articleTagRep      = $em->getRepository('KsActivityBundle:ArticleTag');
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'wikisport');
                
        $category = $articleTagRep->find($tagId);
        
        $articles = $activityRep->findActivities(array(
            'categoryTagId' => $tagId
        ));
        
        //var_dump($articles);
        
        return array(
            "category" => $category,
            "articles" => $articles
        );
    }
    
    
    /* RENDERS */
    
    /**
     * Lists all Article entities.
     *
     */
    public function navAction()
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $articleTagRep      = $em->getRepository('KsActivityBundle:ArticleTag');
        
        $articleCategories  = $articleTagRep->findBy(array("isCategory" => true));
        
        return $this->render('KsActivityBundle:Wikisport:_nav.html.twig', array(
            'articleCategories'               => $articleCategories,

        ));
    }
    
    public function new_modalAction($articleTagId) {
        $em                 = $this->getDoctrine()->getEntityManager();
        $articleTagRep      = $em->getRepository('KsActivityBundle:ArticleTag');
        
        $article = new \Ks\ActivityBundle\Entity\Article();
        $form  = $this->createForm(new \Ks\ActivityBundle\Form\ArticleType(), $article);
        $equipmentform  = $this->createForm(new \Ks\EquipmentBundle\Form\NewEquipmentType("menu"));
        
        $articleCategories  = $articleTagRep->findBy(array("isCategory" => true));
        
        if ($articleTagId != '-1') {
            $articleTag = $articleTagRep->find( $articleTagId );
            $article->setCategoryTag($articleTag);
        }
        
        return $this->render('KsActivityBundle:Wikisport:_new_modal.html.twig', array(
            "category"          => $articleTagId != '-1' ? $articleTag : array("id" => "-1", "label" => "all"),
            "articleCategories" => $articleCategories,
            "form"              => $form->createView(),
            "equipmentform"     => $equipmentform->createView()
        ));
    }

    /**
     * @Route("/competitions", name = "ksWikisport_competitions", options={"expose"=true})
     */	
    public function competitionsAction()
    {
        $em                             = $this->getDoctrine()->getEntityManager();
        $securityContext                = $this->container->get('security.context');
        $userRep                        = $em->getRepository('KsUserBundle:User');
        $userHasToDoChecklistActionRep  = $em->getRepository('KsUserBundle:UserHasToDoChecklistAction');
        $checklistActionRep             = $em->getRepository('KsUserBundle:ChecklistAction');
        
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'competitions');
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $me = $this->container->get('security.context')->getToken()->getUser();
        }
        else {
            //Visitor
            $me = $userRep->find(1);
        }
        
        $competitionsSeenPreference = array();
        $competitionsSeenPreference = $userHasToDoChecklistActionRep->findByUserAndChecklistAction($me->getId(), $checklistActionRep->findOneByCode("competitionsSeen")->getId());
        
        return $this->render('KsActivityBundle:Wikisport:competitions.html.twig', array(
            'user'                          => $me,
            'competitionsSeenPreference'    => $competitionsSeenPreference,
        ));
    }
    
}
