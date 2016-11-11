<?php

namespace Ks\AgendaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;


class AgendaController extends Controller
{
    /**
     * @Route("/{id}", defaults={"newPlanId" = null}, name = "ksAgenda_dashboard", options={"expose"=true})
     * @Route("/{id}/{newPlanId}", defaults={"newPlanId" = null}, requirements={"id" = "\d+", "newPlanId" = "\d+"}, name = "ksAgenda_dashboard", options={"expose"=true})
     * @ParamConverter("user", class="KsUserBundle:User")
     */	
    public function dashboardAction(\Ks\UserBundle\Entity\User $user, $newPlanId)
    {
        $em                             = $this->getDoctrine()->getEntityManager();
        $securityContext                = $this->container->get('security.context');
        $agendaRep                      = $em->getRepository('KsAgendaBundle:Agenda');
        $clubRep                        = $em->getRepository('KsClubBundle:Club');
        $userRep                        = $em->getRepository('KsUserBundle:User');
        $articleTagRep                  = $em->getRepository('KsActivityBundle:ArticleTag');
        $coachingPlanRep                = $em->getRepository('KsCoachingBundle:CoachingPlan');
        $preferenceRep                  = $em->getRepository('KsUserBundle:Preference');
        
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'agenda');
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $me = $this->container->get('security.context')->getToken()->getUser();
            if( $me->getCompletedHisProfileRegistration() != true) {
                $this->get('session')->setFlash('alert alert-info', "Tu n'as pas encore complété les informations de ton profil. Nous t'invitons à y consacrer quelques secondes pour bénéficier de toutes les fonctionnalités du site !");
                return new RedirectResponse($this->container->get('router')->generate('ksProfile_V2'));
            }
        }
        else {
            //Visitor
            $me = $userRep->find(1);
        }
        
        //FIXME Création en masse d'event sur 2013 pour chaque activité de chaque user
        //Création d'un événement selon le type de l'activité si sport sélectionné pour l'afficher dans l'agenda de l'utilisateur
        $activitySessionRep = $em->getRepository('KsActivityBundle:ActivitySession');
        $activities = $activitySessionRep->findActivitiesInLast12Months($user->getId());
        //var_dump(count($activities));exit;
        foreach($activities as $activity) {
            $activitySession = $activitySessionRep->find((int)$activity["id"]);
            //var_dump((int)$activity["id"]);var_dump((int)$activity["event_id"]);var_dump((int)$activity["activityEventId"]);//exit;
            if ((int)$activity["event_id"] == 0 && (int)$activity["activityEventId"] != 0 ) {
                //Cas ou l'event existe bien avec la référence à l'activité mais le le lien de l'activité n'est pas renseigné
                $event = $em->getRepository('KsEventBundle:Event')->find((int)$activity["activityEventId"]);
                //var_dump($event->getId());
                $activitySession->setEvent($event);
                $em->persist($activitySession);
                $em->flush();
            }
            else if (is_object($activitySession) && $activitySession != null) {
                //Cas ou l'event n'existe pas, il faut donc le créer !
                $event = $em->getRepository('KsEventBundle:Event')->getEventFromActivity((int)$activity["id"]);
                
                //var_dump((int)$activity["id"]);
                
                if (is_object($event) && $event != null) {
                    //var_dump($event->getId());
                }
                else {
                    $event = new \Ks\EventBundle\Entity\Event();
                    $event->setActivitySession($activitySession);
                    $event->setContent($activitySession->getDescription());
                    $event->setCreationDate(new \DateTime('now'));
                    $event->setStartDate($activitySession->getIssuedAt());

                    if (!is_null($activitySession->getDuration())) $duration = $activitySession->getDuration()->format('H') * 3600 + $activitySession->getDuration()->format('i') * 60 + $activitySession->getDuration()->format('s');
                    else $duration = 0;
                    $endDate = new \DateTime(date("Y-m-d H:i:s", strtotime($activitySession->getIssuedAt()->format("Y-m-d H:i:s")) + $duration));
                    $event->setEndDate($endDate);

                    $event->setIsAllDay(true);
                    $event->setName("");
                    $event->setPlace($activitySession->getPlace());
                    $event->setSport($activitySession->getSport());
                    if ($activitySession->getWasOfficial()) $event->setTypeEvent($em->getRepository('KsEventBundle:TypeEvent')->find(2));
                    else $event->setTypeEvent($em->getRepository('KsEventBundle:TypeEvent')->find(1));
                    $event->setUser($activitySession->getUser());

                    $em->persist($event);
                    $em->flush();
                    
                    $activitySession->setEvent($event);
                    $em->persist($activitySession);
                    $em->flush();
                }
            }
        }
        
        //FIXME : à supprimer une fois la copie faite
        //copie des images des articles vers les équipements des users
//        if ($user->getId() == 7) {
//            $equipmentRep = $em->getRepository('KsUserBundle:Equipment');
//            $equipments = $equipmentRep->findEquipmentsToCopy();
//            
//            foreach($equipments as $equipment) {
//                $imgDirRelative = $this->container->get('templating.helper.assets')->getUrl('img');
//                $equipmentsDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/equipments/";
//                $wikisDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/wiki/";
//
//                $equipmentDirAbsolute = $equipmentsDirAbsolute.$equipment['equipment_id']."/";
//                $wikiDirAbsolute = $wikisDirAbsolute.$equipment['activity_id']."/";
//
//                if (! is_dir( $equipmentDirAbsolute ) ) mkdir($equipmentDirAbsolute);
//                
//                $newEquipmentDirAbsolute_original = $equipmentDirAbsolute . 'original/';
//                if (! is_dir( $newEquipmentDirAbsolute_original ) ) mkdir($newEquipmentDirAbsolute_original);
//
//                $newEquipmentDirAbsolute_1024x1024 = $equipmentDirAbsolute . 'resize_1024x1024/';
//                if (! is_dir( $newEquipmentDirAbsolute_1024x1024 ) ) mkdir($newEquipmentDirAbsolute_1024x1024);
//
//                $newEquipmentDirAbsolute_512x512 = $equipmentDirAbsolute . 'resize_512x512/';
//                if (! is_dir( $newEquipmentDirAbsolute_512x512 ) ) mkdir($newEquipmentDirAbsolute_512x512);
//
//                $newEquipmentDirAbsolute_128x128 = $equipmentDirAbsolute . 'resize_128x128/';
//                if (! is_dir( $newEquipmentDirAbsolute_128x128 ) ) mkdir($newEquipmentDirAbsolute_128x128);
//
//                $newEquipmentDirAbsolute_48x48 = $equipmentDirAbsolute . 'resize_48x48/';
//                if (! is_dir( $newEquipmentDirAbsolute_48x48 ) ) mkdir($newEquipmentDirAbsolute_48x48);
//
//                //On copie les photos originales et redimentionnés 
//                $rename_original = copy( $wikiDirAbsolute."original/"  . "0.jpg", $newEquipmentDirAbsolute_original."0.jpg" );
//                $rename_1024x1024 = copy( $wikiDirAbsolute."original/" . "0.jpg", $newEquipmentDirAbsolute_1024x1024."0.jpg" );
//                $rename_512x512 = copy( $wikiDirAbsolute."original/" . "0.jpg", $newEquipmentDirAbsolute_512x512."0.jpg" );
//                $rename_128x128 = copy( $wikiDirAbsolute."original/" . "0.jpg", $newEquipmentDirAbsolute_128x128."0.jpg" );
//                $rename_48x48 = copy( $wikiDirAbsolute."original/" . "0.jpg", $newEquipmentDirAbsolute_48x48."0.jpg" );
//                
//                $userEquipment = $em->getRepository('KsUserBundle:Equipment')->find((int)$equipment["equipment_id"]);
//                $userEquipment->setAvatar("0.jpg");
//                $em->persist($userEquipment);
//                $em->flush();
//           }
//        }
        
        //FIXME Pour LUGRIN qui avait son poids à -7 les points étaient tous <0 depuis 2 mois !
        $activities = $activitySessionRep->findActivitiesInLast12MonthsWithNegPoints($user->getId());
        $leagueLevelService = $this->get('ks_league.leagueLevelService');
        foreach ($activities as $activity) {
            $activitySession = $activitySessionRep->find((int)$activity["id"]);
            $leagueLevelService->activitySessionEarnPoints($activitySession, $user);
        }
        
        $event                 = new \Ks\EventBundle\Entity\Event();
        $event->setUser($user);
        $eventForm             = $this->createForm(new \Ks\EventBundle\Form\EventType(null, $user, $coachingPlanRep->find($newPlanId), 'withNoImportedPlans'), $event);
        
        //Formulaire pour filtre par SPORT
        $activitySession            = new \Ks\ActivityBundle\Entity\ActivitySession($user);
        $eventSportChoiceForm       = $this->createForm(new \Ks\ActivityBundle\Form\SportType('MultiSimple'), $activitySession);
        
        //Formulaire pour filtre par Plan d'entrainement
        $coachingPlan = new \Ks\CoachingBundle\Entity\CoachingPlan();
        $eventCoachingPlanChoiceForm       = $this->createForm(new \Ks\CoachingBundle\Form\CoachingPlanType(null, $user), $coachingPlan);
        
        $status         = new \Ks\ActivityBundle\Entity\ActivityStatus();
        $statusForm     = $this->createForm(new \Ks\ActivityBundle\Form\ActivityStatusType(), $status);
        $link           = new \Ks\ActivityBundle\Entity\ActivityStatus();
        $linkForm       = $this->createForm(new \Ks\ActivityBundle\Form\ActivityLinkType(), $link);
        $photo          = new \Ks\ActivityBundle\Entity\ActivityStatus();
        $photoForm      = $this->createForm(new \Ks\ActivityBundle\Form\ActivityPhotoType(), $photo);
        
        //Récupération du formulaire pour création de la vue à plat d'un plan
        $article      = new \Ks\ActivityBundle\Entity\Article();
        $categoryTag =  $articleTagRep->find(3); // 3= plan d'entrainement
        $article->setCategoryTag($categoryTag);
        
        $articleForm  = $this->createForm(new \Ks\ActivityBundle\Form\ArticleType('withTrainingPlan'), $article);
        
        //Récupération des dates debut et fin des plans du user
        $coachingPlansData = $clubRep->getCoachingPlansData('user', $user->getId());
        
        //Récupération du plan principal d'un user (=celui en cours si 1 seul)
        $mainPlan = $clubRep->getFirstPlan($user->getId());
        
        $friendWithMe                       = $userRep->areFriends($me->getId(), $user->getId()); 
        $mustGiveRequestFriendResponse      = $userRep->mustGiveRequestFriendResponse($me->getId(), $user->getId());
        $isAwaitingRequestFriendResponse    = $userRep->isAwaitingRequestFriendResponse($me->getId(), $user->getId());
        
        $isAllowedPackPremium = $user->getIsAllowedPackPremium();
        $isAllowedPackElite = $user->getIsAllowedPackElite();
        
        $isManagerFromAClub = $userRep->isManagerFromAClub($user->getId());
        
        //Récupération des séances
        $favoriteSessions = $clubRep->getSessions('user', $user->getId());
        $wikiSessions = null;
        
        //Nombre de semaines mini pour partager un plan
        $weeksLengthPlan = $preferenceRep->findOneBy(array('code' => "minLengthPlanPremium"))->getVal1();
        
        $citations = array();
        if ($user->getId() == 744) { //Pascal Blanc
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
        }
        else if ($user->getId() == 753) { //Terre De Sport
            $usersKs = array("didier");
            $texts = array();

            $texts[$usersKs[0]] = "Cela fait 14 ans que je pratique la course de montagne sans jamais avoir eu recours à un coach. J'ai obtenu des résultats honorables pour un coureur autodidacte et pas toujours régulier. Cependant je rêvais secrètement de progresser et de pouvoir pleinement m'exprimer sur les courses. Depuis que j'ai Laurent pour coach cela m'a permis d'avoir un entraînement plus spécifique et adapté à mes objectifs. Au niveau des résultats cela se ressent,en un an j'ai fait deux top 10(9eme D-tour et 8eme Restonica) et deux top 14(Arc-en ciel et Cimasa). Pour sûr je continue avec lui l'année prochaine !";
            
            $citations = array();

            for ($i=0;$i<1;$i++) {
                $userToAffich = $userRep->findOneUser( array(
                    "username" => $usersKs[$i]
                ));

                $citations[] = array(
                    "user" => $userToAffich,
                    "text" => $texts[$usersKs[$i]]
                );
            }
        }
        
        $HRMax = $user->getUserDetail()->getHRMax();
        $HRRest = $user->getUserDetail()->getHRRest();
        $VMASpeed = $user->getUserDetail()->getVMASpeed();
        
        ini_set('memory_limit', '1024M');
        
        return $this->render('KsAgendaBundle::dashboard.html.twig', array(
            "statusForm"                    => $statusForm->createView(),
            "linkForm"                      => $linkForm->createView(),
            "photoForm"                     => $photoForm->createView(),
            "eventForm"                     => $eventForm->createView(),
            "eventSportChoiceForm"          => $eventSportChoiceForm->createView(),
            "eventCoachingPlanChoiceForm"   => $eventCoachingPlanChoiceForm->createView(),
            "user"                          => $user,
            "friendWithMe"                  => $friendWithMe,
            "mustGiveRFResponse"            => $mustGiveRequestFriendResponse,
            "isAwaitingRFResponse"          => $isAwaitingRequestFriendResponse,
            "session"                       => $session->get("page"),
            "articleForm"                   => $articleForm->createView(),
            "favoriteSessions"              => $favoriteSessions,
            "wikiSessions"                  => $wikiSessions,
            "coachingPlansData"             => $coachingPlansData,
            "mainPlan"                      => $mainPlan,
            "newPlanId"                     => $newPlanId,
            'isAllowedPackPremium'          => $isAllowedPackPremium,
            'isAllowedPackElite'            => $isAllowedPackElite,
            'isManagerFromAClub'            => $isManagerFromAClub,
            'weeksLengthPlan'               => $weeksLengthPlan,
            'citations'                     => $citations,
            "HRMax"                         => $HRMax == null ? -1 : $HRMax,
            "HRRest"                        => $HRRest == null ? -1 : $HRRest,
            "VMASpeed"                      => $VMASpeed == null ? -1 : $VMASpeed,
            "clubIsCoach"                   => 0,
        ));
    }
    
    /**
     * @Route("/{id}", defaults={"newPlanId" = null}, name = "ksAgenda_index", options={"expose"=true})
     * @Route("/{id}/{newPlanId}", defaults={"newPlanId" = null}, requirements={"id" = "\d+", "newPlanId" = "\d+"}, name = "ksAgenda_index", options={"expose"=true})
     * @ParamConverter("user", class="KsUserBundle:User")
     */	
    public function indexAction(\Ks\UserBundle\Entity\User $user, $newPlanId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $agendaRep          = $em->getRepository('KsAgendaBundle:Agenda');
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $articleTagRep      = $em->getRepository('KsActivityBundle:ArticleTag');
        $coachingPlanRep    = $em->getRepository('KsCoachingBundle:CoachingPlan');
        
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'agenda');
        
        //$user = $userRep->find($id);
        
        //var_dump($newPlanId);
        
        //FIXME Création en masse d'event sur 2013 pour chaque activité de chaque user
        //Création d'un événement selon le type de l'activité si sport sélectionné pour l'afficher dans l'agenda de l'utilisateur
        $activitySessionRep = $em->getRepository('KsActivityBundle:ActivitySession');
        $activities = $activitySessionRep->findActivitiesInLast12Months($user->getId());
        
        foreach($activities as $activity) {
            $activitySession = $activitySessionRep->find((int)$activity["id"]);
            
            if (is_object($activitySession) && $activitySession != null) { 
                $event = $em->getRepository('KsEventBundle:Event')->getEventFromActivity((int)$activity["id"]);
                
                //var_dump((int)$activity["id"]);
                
                if (is_object($event) && $event != null) { 
                    //var_dump($event->getId());
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

                    $em->persist($event);
                    $em->flush();
                    
                    $activitySession->setEvent($event);
                    $em->persist($activitySession);
                    $em->flush();
                }
            }
        }
        
        //FIXME : à supprimer une fois la copie faite
        //copie des images des articles vers les équipements des users
//        if ($user->getId() == 7) {
//            $equipmentRep = $em->getRepository('KsUserBundle:Equipment');
//            $equipments = $equipmentRep->findEquipmentsToCopy();
//            
//            foreach($equipments as $equipment) {
//                $imgDirRelative = $this->container->get('templating.helper.assets')->getUrl('img');
//                $equipmentsDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/equipments/";
//                $wikisDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/wiki/";
//
//                $equipmentDirAbsolute = $equipmentsDirAbsolute.$equipment['equipment_id']."/";
//                $wikiDirAbsolute = $wikisDirAbsolute.$equipment['activity_id']."/";
//
//                if (! is_dir( $equipmentDirAbsolute ) ) mkdir($equipmentDirAbsolute);
//                
//                $newEquipmentDirAbsolute_original = $equipmentDirAbsolute . 'original/';
//                if (! is_dir( $newEquipmentDirAbsolute_original ) ) mkdir($newEquipmentDirAbsolute_original);
//
//                $newEquipmentDirAbsolute_1024x1024 = $equipmentDirAbsolute . 'resize_1024x1024/';
//                if (! is_dir( $newEquipmentDirAbsolute_1024x1024 ) ) mkdir($newEquipmentDirAbsolute_1024x1024);
//
//                $newEquipmentDirAbsolute_512x512 = $equipmentDirAbsolute . 'resize_512x512/';
//                if (! is_dir( $newEquipmentDirAbsolute_512x512 ) ) mkdir($newEquipmentDirAbsolute_512x512);
//
//                $newEquipmentDirAbsolute_128x128 = $equipmentDirAbsolute . 'resize_128x128/';
//                if (! is_dir( $newEquipmentDirAbsolute_128x128 ) ) mkdir($newEquipmentDirAbsolute_128x128);
//
//                $newEquipmentDirAbsolute_48x48 = $equipmentDirAbsolute . 'resize_48x48/';
//                if (! is_dir( $newEquipmentDirAbsolute_48x48 ) ) mkdir($newEquipmentDirAbsolute_48x48);
//
//                //On copie les photos originales et redimentionnés 
//                $rename_original = copy( $wikiDirAbsolute."original/"  . "0.jpg", $newEquipmentDirAbsolute_original."0.jpg" );
//                $rename_1024x1024 = copy( $wikiDirAbsolute."original/" . "0.jpg", $newEquipmentDirAbsolute_1024x1024."0.jpg" );
//                $rename_512x512 = copy( $wikiDirAbsolute."original/" . "0.jpg", $newEquipmentDirAbsolute_512x512."0.jpg" );
//                $rename_128x128 = copy( $wikiDirAbsolute."original/" . "0.jpg", $newEquipmentDirAbsolute_128x128."0.jpg" );
//                $rename_48x48 = copy( $wikiDirAbsolute."original/" . "0.jpg", $newEquipmentDirAbsolute_48x48."0.jpg" );
//                
//                $userEquipment = $em->getRepository('KsUserBundle:Equipment')->find((int)$equipment["equipment_id"]);
//                $userEquipment->setAvatar("0.jpg");
//                $em->persist($userEquipment);
//                $em->flush();
//           }
//        }
        
        $event                 = new \Ks\EventBundle\Entity\Event();
        $event->setUser($user);
        $eventForm             = $this->createForm(new \Ks\EventBundle\Form\EventType(null, $user, $coachingPlanRep->find($newPlanId), 'withNoImportedPlans'), $event);
        
        //Formulaire pour filtre par SPORT
        $activitySession            = new \Ks\ActivityBundle\Entity\ActivitySession($user);
        $eventSportChoiceForm       = $this->createForm(new \Ks\ActivityBundle\Form\SportType('MultiSimple'), $activitySession);
        
        //Formulaire pour filtre par Plan d'entrainement
        $coachingPlan = new \Ks\CoachingBundle\Entity\CoachingPlan();
        $eventCoachingPlanChoiceForm       = $this->createForm(new \Ks\CoachingBundle\Form\CoachingPlanType(null, $user), $coachingPlan);
        
        $status         = new \Ks\ActivityBundle\Entity\ActivityStatus();
        $statusForm     = $this->createForm(new \Ks\ActivityBundle\Form\ActivityStatusType(), $status);
        $link           = new \Ks\ActivityBundle\Entity\ActivityStatus();
        $linkForm       = $this->createForm(new \Ks\ActivityBundle\Form\ActivityLinkType(), $link);
        $photo          = new \Ks\ActivityBundle\Entity\ActivityStatus();
        $photoForm      = $this->createForm(new \Ks\ActivityBundle\Form\ActivityPhotoType(), $photo);
        
        //Récupération du formulaire pour création de la vue à plat d'un plan
        $article      = new \Ks\ActivityBundle\Entity\Article();
        $categoryTag =  $articleTagRep->find(3); // 3= plan d'entrainement
        $article->setCategoryTag($categoryTag);
        
        $articleForm  = $this->createForm(new \Ks\ActivityBundle\Form\ArticleType('withTrainingPlan'), $article);
        
        //Récupération des séances
        $favoriteSessions = $clubRep->getSessions('user', $user->getId());
        $wikiSessions = $clubRep->getSessions("all");
        
        //Récupération des dates debut et fin des plans du user
        $coachingPlansData = $clubRep->getCoachingPlansData('user', $user->getId());
        
        //Récupération du plan principal d'un user (=celui en cours si 1 seul)
        $mainPlan = $clubRep->getFirstPlan($user->getId());
        
        return $this->render('KsAgendaBundle:AgendaClub:index.html.twig', array(
            "statusForm"                    => $statusForm->createView(),
            "linkForm"                      => $linkForm->createView(),
            "photoForm"                     => $photoForm->createView(),
            "eventForm"                     => $eventForm->createView(),
            "eventSportChoiceForm"          => $eventSportChoiceForm->createView(),
            "eventCoachingPlanChoiceForm"   => $eventCoachingPlanChoiceForm->createView(),
            "user"                          => $user,
            "session"                       => $session->get("page"),
            "articleForm"                   => $articleForm->createView(),
            "favoriteSessions"              => $favoriteSessions,
            "wikiSessions"                  => $wikiSessions,
            "coachingPlansData"             => $coachingPlansData,
            "mainPlan"                      => $mainPlan,
            "newPlanId"                     => $newPlanId
        ));
    }
    
    /**
      * @Route("/{id}/getEvents", name = "ksAgenda_getEvents", options={"expose"=true} )
      * @ParamConverter("user", class="KsUserBundle:User")
      */
    public function getEvents(\Ks\UserBundle\Entity\User $user) {
 
        $em                 = $this->getDoctrine()->getEntityManager();
        $agendaRep          = $em->getRepository('KsAgendaBundle:Agenda');
        $coachingPlanRep    = $em->getRepository('KsCoachingBundle:CoachingPlan');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $securityContext    = $this->get('security.context');
        $request            = $this->getRequest();
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $appUser = $this->container->get('security.context')->getToken()->getUser();
        }
        else {
            //Visitor pour afficher l'agende de ZIICOS depuis la page de login
            $appUser = $userRep->find(7);
        }
        
        //Paramètres GET
        $parameters = $request->request->all();
        
        //var_dump($parameters);exit;
        
        $startOn    = date('Y-m-d', $parameters['start']);
        $endOn      = date('Y-m-d', $parameters['end']);
        $competitionsOnly       = isset( $parameters['competitionsOnly']) ? $parameters['competitionsOnly'] : null;
        $eventsRegions          = isset( $parameters['eventsRegions'] ) && $parameters['eventsRegions'][0] != "" ? $parameters['eventsRegions'] : array();
        $eventsDistances        = isset( $parameters['eventsDistances'] ) && $parameters['eventsDistances'][0] != "" ? $parameters['eventsDistances'] : array();
        $eventsTypes            = isset( $parameters['eventsTypes'] ) && $parameters['eventsTypes'][0] != "" ? $parameters['eventsTypes'] : array();
        $eventsSports           = isset( $parameters['eventsSports'] ) && $parameters['eventsSports'][0] != "" ? $parameters['eventsSports'] : array();
        $planId                 = isset( $parameters['eventsCoachingPlans'] ) && $parameters['eventsCoachingPlans'][0] != "" ? $parameters['eventsCoachingPlans'][0] : null;
        
        
        //Si choix MES SPORTS, le tableau doit contenir la valeur ""
        if (in_array( "", $eventsSports) && count($eventsSports) > 0) $my_sports = true;
        else $my_sports = false;
        
        //Si l'utilisateur est un coach on ne lui affiche pas ses activités lorsqu'il choisi un plan premium
        $getUserSessionForFlatView = true;
        $isManagerFromAClub = $userRep->isManagerFromAClub($appUser->getId());
        $plan = $coachingPlanRep->find($planId);
        if (!is_null($plan) && $isManagerFromAClub > 0 && (is_null($plan->getClub()) or !is_null($plan->getClub()) && $plan->getCoachingPlanType()->getCode() != 'coach')) $getUserSessionForFlatView = false;
        
        //var_dump($eventsRegions);        exit;
        
        $events = $agendaRep->findAgendaEvents(array(
            "userId"                    => $user->getId(),
            "startOn"                   => $startOn,
            "endOn"                     => $endOn,
            "eventsFrom"                => array(),
            "eventsTypes"               => $eventsTypes,
            "eventsSports"              => $eventsSports,
            "planId"                    => $planId,
            "getUserSessionForFlatView" => $getUserSessionForFlatView,
            "userIdForFlatView"         => $user->getId(),
            "my_sports"                 => $my_sports,
            "competitionsOnly"          => $competitionsOnly,
            "eventsRegions"             => $eventsRegions,
            "eventsDistances"           => $eventsDistances,
        ), $this->get('translator'));

        $response = new Response(json_encode($events));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/{id}/createEvent", name = "ksAgenda_createEvent", options={"expose"=true} )
     * @ParamConverter("user", class="KsUserBundle:User")
     */
    public function createEventAction(\Ks\UserBundle\Entity\User $user)
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $agendaRep          = $em->getRepository('KsAgendaBundle:Agenda');
        
        $event              = new \Ks\EventBundle\Entity\Event();
        $event->setUser($user);
        $eventForm          = $this->createForm(new \Ks\EventBundle\Form\EventType(null, $user), $event);
        
         // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $formHandler = new \Ks\EventBundle\Form\EventHandler($eventForm, $request, $em, $this->container, $this->get('translator'));

        $responseDatas = $formHandler->process();
        
        //Si l'événement a été publié
        if ($responseDatas['code'] == 1) {
            //On ajoute l'évènement à l'agenda
            $agendaRep->addEventToAgenda( $user->getAgenda(), $event );
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{id}/editEvent", name = "ksAgenda_editEvent", options={"expose"=true} )
     * @ParamConverter("event", class="KsEventBundle:Event")
     */
    public function editEventAction(\Ks\EventBundle\Entity\Event $event)
    {
        $request        = $this->get('request');
        $em             = $this->getDoctrine()->getEntityManager();
        $agendaRep      = $em->getRepository('KsAgendaBundle:Agenda');
        
        if( is_object( $event) ) {
            if (is_object ($event->getUser()) && $event->getUser() != null) $eventForm = $this->createForm(new \Ks\EventBundle\Form\EventType(null, $event->getUser()), $event);
            if (is_object ($event->getClub()) && $event->getClub() != null) $eventForm = $this->createForm(new \Ks\EventBundle\Form\EventType($event->getClub()), $event);

             // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
            $formHandler = new \Ks\EventBundle\Form\EventHandler($eventForm, $request, $em, $this->container, $this->get('translator'));

            $responseDatas = $formHandler->process();

            //Si l'événement a été publié
            if ($responseDatas['code'] == 1) {
                //Si le club est de type CLUB (isCoach = 0 ou null alors on doit dupliquer chaque event pour tous les membres
                $club= $event->getClub();
                if (!is_null($club) && $club->getIsCoach() == 0) {
                    $agendaRep = $em->getRepository('KsAgendaBundle:Agenda');
                    $userRep = $em->getRepository('KsUserBundle:User');
                    foreach( $club->getUsers() as $clubHasUser ) {
                        $memberId = $clubHasUser->getUser()->getId();
                        foreach($event->getUsersParticipations() as $userParticipates){
                            if ($clubHasUser->getUser() != $userParticipates) $agendaRep->updateEventForClub( $event );
                        }
                    }
                }
            }
        } else {
            $responseDatas = array(
                "code"          => -1,
                "errorMessage" => "Impossible de trouver l'événement"
            );
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{id}/deleteEvent", name = "ksAgenda_deleteEvent", options={"expose"=true} )
     * @ParamConverter("event", class="KsEventBundle:Event")
     */
    public function deleteEventAction(\Ks\EventBundle\Entity\Event $event)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $responseDatas = array("code" => 1);
        $delete = true;
        
        if(!is_null($event->getCoachingPlan())) {
            if ($event->getCoachingPlan()->getCoachingPlanType()->getCode() == 'shared') {
                //Si l'event est lié à un plan premium partagé on n'autorise pas la suppression !
                $responseDatas = array("code" => -1);
                $delete = false;
            }
            else if ($event->getCoachingPlan()->getCoachingPlanType()->getCode() == 'club') {
                if (is_null($event->getActivitySession())) $delete = false;
                
                //Si le club est de type CLUB (isCoach = 0 ou null alors on doit supprimer aussi chaque event pour tous les membres
                $club= $event->getClub();
                if (!is_null($club) && $club->getIsCoach() != 1) {
                    $agendaRep = $em->getRepository('KsAgendaBundle:Agenda');
                    $agendaRep->removeEventForClub( $event );
                }
            }
        }
        
        if ($delete) {
            $userParticipatesEventRep   = $em->getRepository('KsEventBundle:UserParticipatesEvent');
            foreach($event->getUsersParticipations() as $userParticipates){
                $userParticipatesEventRep->userParticipatesAnymoreEvent( $event, $userParticipates);
            }
            $em->remove($event);
            $em->flush();
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{id}/moveOrResize", name = "ksAgenda_moveOrResizeEvent", options={"expose"=true} )
     * @ParamConverter("event", class="KsEventBundle:Event")
     */
    public function moveOrResizeEventAction(\Ks\EventBundle\Entity\Event $event)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $agendaRep  = $em->getRepository('KsAgendaBundle:Agenda');
        $request    = $this->container->get('request');
        
        $responseDatas = array("code" => 1);
        $move = true;
        
        if(!is_null($event->getCoachingPlan())) {
            if ($event->getCoachingPlan()->getCoachingPlanType()->getCode() == 'shared') {
                //Si l'event est lié à un plan premium partagé on n'autorise pas la suppression !
                $responseDatas = array(
                    "code" => -1);
                $move = false;
            }
        }
        
        if ($move) {
            $dayDelta = $request->request->get('dayDelta');
            $minuteDelta = $request->request->get('minuteDelta');
            $allDay = $request->request->get('allDay');
            $isMove = $request->request->get('isMove') == "true" ? true : false;

            //Si le club est de type CLUB (isCoach = 0 ou null alors on doit décaler AUSSI chaque event pour tous les membres
            $club = $event->getClub();
            if (!is_null($club) && $club->getIsCoach() != 1) {
                $agendaRep = $em->getRepository('KsAgendaBundle:Agenda');
                $agendaRep->moveEventForClub( $event, $dayDelta, $minuteDelta, $isMove );
            }
            else {
                //on décale uniquement celui en cours
                $agendaRep->moveOrResizeEvent( $event, $dayDelta, $minuteDelta, $isMove );
            }

        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{id}/duplicateEvent", name = "ksAgenda_duplicateEvent", options={"expose"=true} )
     * @ParamConverter("event", class="KsEventBundle:Event")
     */
    public function duplicateEventAction(\Ks\EventBundle\Entity\Event $event)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $agendaRep  = $em->getRepository('KsAgendaBundle:Agenda');
        $request    = $this->container->get('request');
        
        $responseDatas = array("code" => 1);
        $duplicate = true;
        
        if(!is_null($event->getCoachingPlan())) {
            if ($event->getCoachingPlan()->getCoachingPlanType()->getCode() == 'shared') {
                //Si l'event est lié à un plan premium partagé on n'autorise pas la suppression !
                $responseDatas = array(
                    "code" => -1);
                $duplicate = false;
            }
        }
        
        if ($duplicate) {
            $dayDelta = $request->request->get('dayDelta');
            $minuteDelta = $request->request->get('minuteDelta');
            $allDay = $request->request->get('allDay');
            $isMove = $request->request->get('isMove') == "true" ? true : false;

            $eventDuplicated = $agendaRep->duplicateEvent( $event, $dayDelta, $minuteDelta, $isMove );
            $em->flush();
            
            //Si le club est de type CLUB (isCoach = 0 ou null alors on doit dupliquer chaque event pour tous les membres
            $club = $event->getClub();
            if (!is_null($club) && $club->getIsCoach() == 0) {
                $agendaRep = $em->getRepository('KsAgendaBundle:Agenda');
                $userRep = $em->getRepository('KsUserBundle:User');
                foreach( $club->getUsers() as $clubHasUser ) {
                    $memberId = $clubHasUser->getUser()->getId();
                    foreach($event->getUsersParticipations() as $userParticipates){
                        if ($clubHasUser->getUser() != $userParticipates) {
                            //var_dump("la".$memberId."-".$userParticipates->getId());
                            $agendaRep->duplicateEvent( $eventDuplicated, 0, 0, true, $memberId, $userRep->findCoachingPlanIDFromClub($memberId, $club->getId()));
                        }
                    }
                }
            }
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{id}/duplicateEvents", name = "ksAgenda_duplicateEvents", options={"expose"=true} )
     * @ParamConverter("event", class="KsEventBundle:Event")
     */
    public function duplicateEventsAction(\Ks\EventBundle\Entity\Event $event)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $agendaRep          = $em->getRepository('KsAgendaBundle:Agenda');
        $eventRep           = $em->getRepository('KsEventBundle:Event');
        
        $responseDatas = array("code" => 1);
        $duplicate = true;
        
        if(!is_null($event->getCoachingPlan())) {
            if ($event->getCoachingPlan()->getCoachingPlanType()->getCode() == 'shared') {
                //Si l'event est lié à un plan premium partagé on n'autorise pas la suppression !
                $responseDatas = array(
                    "code" => -1);
                $duplicate = false;
            }
        }
        
        if ($duplicate) {
            $request = $this->container->get('request');
        
            $plan = $event->getCoachingPlan();

            $dayDelta = $request->request->get('dayDelta');
            $minuteDelta = $request->request->get('minuteDelta');
            $isMove = $request->request->get('isMove') == "true" ? true : false;

            $eventsToMove = $agendaRep->findAgendaEvents(array(
                "planId"                    => $plan->getId(),
                "getUserSessionForFlatView" => false,
                "fromSpecificDay"           => $event->getStartDate()->format('w'),
                "notLinkedToActivity"       => true,
                "order"                     => array("DATE(e.startDate)" => "ASC")
            ), $this->get('translator'));

            $startDate = clone $event->getStartDate();

            if ($dayDelta >= 0) $destinationDay = $startDate->add(new \DateInterval('P'.$dayDelta.'D'));
            else $destinationDay = $startDate->sub(new \DateInterval('P'.abs($dayDelta).'D'));

            $eventsToDestination = $agendaRep->findAgendaEvents(array(
                "planId"                    => $plan->getId(),
                "getUserSessionForFlatView" => false,
                "fromSpecificDay"           => $destinationDay->format('w'),
                "notLinkedToActivity"       => true,
                "order"                     => array("DATE(e.startDate)" => "ASC")
            ), $this->get('translator'));

            if (count($eventsToDestination) == 0) {
                foreach($eventsToMove as $eventArray) {
                    //var_dump($eventArray['id']."/".$dayDelta."/".$minuteDelta."/".$isMove);
                    $eventFromPlan = $eventRep->find($eventArray['id']);
                    $agendaRep->moveOrResizeEvent( $eventFromPlan, $dayDelta, $minuteDelta, $isMove );
                }
                $responseDatas = array(
                    "code" => 1
                );

            }
            else {
                $responseDatas = array(
                    "code" => -2
                );
            }
        }
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{id}/getEventInfos", name = "ksAgenda_getEventInfos", options={"expose"=true} )
     */
    public function getEventInfosAction($id)
    {
        $em           = $this->getDoctrine()->getEntityManager();
        $agendaRep    = $em->getRepository('KsAgendaBundle:Agenda');
        $clubRep      = $em->getRepository('KsClubBundle:Club');
        $eventRep     = $em->getRepository('KsEventBundle:Event');
        $user         = $this->get('security.context')->getToken()->getUser();
        
        $event = $agendaRep->findAgendaEvents(array(
            "eventId"  => $id,
            "extended"  => true
        ), $this->get('translator'));
        
        //var_dump($event);exit;
        
        shuffle($event["participations"]);
        
        $responseDatas["html"] = $this->render('KsAgendaBundle:Agenda:_eventInfos.html.twig', array(
            'event'                     => $event["event"],
            //'usersParticipations'       => $event["usersParticipations"],
            'participations'            => $event["participations"],
            'participationsToAffich'    => array_slice($event["participations"], 0, 5),
            'HRMax'                     => $user->getUserDetail()->getHRMax(),
            'HRRest'                    => $user->getUserDetail()->getHRRest(),
            'VMASpeed'                  => $user->getUserDetail()->getVMASpeed(),
            
        ))->getContent();
        
        $canBeEdited = false;
        
        $myClubsIds = $clubRep->findUserClubsIds( $user->getId() );
        
        if ( $event["event"]["user_id"] != null && (string)$event["event"]["user_id"] == (string)$user->getId() ) {
            //Si le plan est importé i.e. issu d'un plan partagé on ne peut pas modifier l'événément au niveau détail (seuls suppression, déplacement, duplication possibles)
            if ($event["event"]["father_id"] != null) $canBeEdited = false;
            else $canBeEdited = true;
        }
        
        //On vérifie si cette événement est un événement créé par un des clubs auwquels j'appartient
        else if ( $event["event"]["club_id"] != null && in_array( $event["event"]["club_id"], $myClubsIds ) ) {
            if( $clubRep->isManager( $event["event"]["club_id"], $user->getId() )) {
                $canBeEdited = true;
            }
        }
        
        $club = $clubRep->find($event["event"]["club_id"]);
        if (is_object($club) && !is_null($club) && !$club->getIsCoach()) {
            //Cas du club
            $canBeEdited = true;
        }
        
        if ($user->getId() == 7) $canBeEdited = true;
        
        $responseDatas["canBeEdited"] = $canBeEdited;
        //var_dump($responseDatas); //exit;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{activityId}/trainingPlanUsers", name = "ksAgenda_trainingPlanUsers", options={"expose"=true} )
     */
    public function trainingPlanUsersAction($activityId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $agendaRep          = $em->getRepository('KsAgendaBundle:Agenda');
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $coachingPlanRep    = $em->getRepository('KsCoachingBundle:CoachingPlan');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $user               = $this->get('security.context')->getToken()->getUser();
        
        $activity = $activityRep->find($activityId);
        
        $plan = $coachingPlanRep->find($activity->getCoachingPlan()->getId());
        
        $events = $agendaRep->findAgendaEvents(array(
            "planId"                    => $plan->getId(),
            "getUserSessionForFlatView" => false
        ), $this->get('translator'));

        //shuffle($event["participations"]);
        
        $lastDay ='';
        
        //Pour permettre de bloquer les jours possibles des dates de debut / date de fin
        $lastDay = new \DateTime($events[0]['start']);
        $firstDay = new \DateTime($events[count($events)-1]['start']);
        $delay = $firstDay->diff($lastDay)->format('%R%a') + 1;
        $now = new \DateTime('now');
        
        /*
        var_dump($lastDay);
        var_dump($firstDay);
        var_dump($delay);
        var_dump(date('Y-m-d', strtotime($now->format('Y-m-d').' +'.$delay .' days')));
        var_dump($firstDay->format("w"));
        var_dump($now->format('w'));
        */
        //FMO : recherche du prochain jour possible
        $plus = abs($now->format('w') - $firstDay->format("w"));
        //var_dump("plus=".$plus);        exit;
        
        //Membres qui ont utilisé ce plan
        $sons = $coachingPlanRep->findByFather($plan->getId());
        $users = array();
        $userUsesPlan = $userRep->findUsers(array("userId" => $plan->getUser()->getId()), $this->get('translator'));
        $users[] = $userUsesPlan;
        foreach ($sons as $son) {
            $userUsesPlan = $userRep->findUsers(array("userId" => $son->getUser()->getId()), $this->get('translator'));
            if (!in_array($userUsesPlan, $users)) $users[] = $userUsesPlan;
        }
        
        //Récupération droits sur pack2 pour import des plans d'entrainement
        $userPacks = $user->getPacks();
        $isAllowedPackPremium = false;
        $now = new \DateTime();
        foreach($userPacks->toArray() as $userHasPack) {
            $pack = $userHasPack->getPack();
            if ($pack->getCode() == 'premium' && $userHasPack->getStartDate()->format("y-m-d") <= $now->format("y-m-d")) $isAllowedPackPremium = true;
        }
        
        return $this->render('KsAgendaBundle:Agenda:_trainingPlanUsers.html.twig', array(
            'planId'                => $plan->getId(),
            'firstDay'              => $firstDay->format("w"),
            'lastDay'               => $lastDay->format("w"),
            'minStartDate'          => date('Y-m-d', strtotime($now->format('Y-m-d').' +'.$plus .' days')),
            'minEndDate'            => date('Y-m-d', strtotime($now->format('Y-m-d').' +'.$delay .' days')),
            'weeks'                 => ceil(abs($firstDay->diff($lastDay)->format('%R%a')) / 7),
            'users'                 => $users,
            'ownerId'               => $plan->getUser()->getId(),
            'affichDesc'            => false,
            "isAllowedPackPremium"  => $isAllowedPackPremium,
        ));
    }
    
    /**
     * @Route("/getPlanOverlap/{planId}", name = "ksAgenda_getPlanOverlap", options={"expose"=true} )
     */
    public function getPlanOverlapAction($planId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $request                = $this->get('request');
        $agendaRep              = $em->getRepository('KsAgendaBundle:Agenda');
        $coachingPlanRep        = $em->getRepository('KsCoachingBundle:CoachingPlan');
        $user                   = $this->get('security.context')->getToken()->getUser();
        
        $responseDatas = array(
            "code" => 1
        );
        
        $parameters     = $request->request->all();
        
        $events = $agendaRep->findAgendaEvents(array(
            "planId"                    => $planId,
            "getUserSessionForFlatView" => false
        ), $this->get('translator'));
        
        //var_dump($events);
        
        $lastDay = new \DateTime($events[0]['start']);
        $firstDay = new \DateTime($events[count($events)-1]['start']);
        
        //Durée du plan initial
        $planLength = abs($firstDay->diff($lastDay)->format('%R%a'));
        
        if (isset($parameters["startDate"]) && $parameters["startDate"] != '') {
            $startDate = new \DateTime($parameters["startDate"]);
            $endDate = clone $startDate;
            $endDate = $endDate->add(new \DateInterval('P'.$planLength.'D'));
            /*var_dump($startDate);
            var_dump($endDate);
            var_dump($firstDay);
            var_dump($firstDay->diff($startDate)->format('%R%a'));
            exit;*/
        }
        else if (isset($parameters["endDate"]) && $parameters["endDate"] != '') {
            $endDate = new \DateTime($parameters["endDate"]);
            $startDate = clone $endDate;
            $startDate = $startDate->sub(new \DateInterval('P'.$planLength.'D'));
        }

        //Test s'il existe un plan premium qui chevauche celui qu'on souhaite importer
        $planOverLap = $coachingPlanRep->getPlanOverlap($user->getId(), $startDate, $endDate);
        
        //var_dump($planOverLap);exit;
        
        if ($planOverLap) {
            $responseDatas["publishResponse"] = 1;
            //$responseDatas["error"] = "Impossible d'importer ce nouveau plan !<br><br>Sur la période du <b>" . $startDate->format("d/m/y") . " au " . $endDate->format("d/m/y") . "</b> tu as déjà un plan <i>Premium</i> dans ton agenda : <br><br><b>" . $planOverLap . "</b><br><br> => Tu dois terminer ou supprimer celui-ci avant d'en importer un nouveau !";
            $responseDatas["message"] = "ATTENTION sur la période du <b>" . $startDate->format("d/m/y") . " au " . $endDate->format("d/m/y") . "</b> tu as déjà un plan <i>Premium</i> dans ton agenda : <br><br><b>" . $planOverLap . "</b><br><br> => Souhaites-tu importer malgré tout ce nouveau plan ?";
        }
        else {
            $responseDatas["publishResponse"] = 0;
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/userImportTrainingPlan/{planId}", name = "ksAgenda_userImportTrainingPlan", options={"expose"=true} )
     */
    public function userImportTrainingPlanAction($planId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $request                = $this->get('request');
        $agendaRep              = $em->getRepository('KsAgendaBundle:Agenda');
        $coachingPlanRep        = $em->getRepository('KsCoachingBundle:CoachingPlan');
        $coachingPlanTypeRep    = $em->getRepository('KsCoachingBundle:CoachingPlanType');
        $eventRep               = $em->getRepository('KsEventBundle:Event');
        $user                   = $this->get('security.context')->getToken()->getUser();
        
        $responseDatas = array(
            "code" => 1
        );
        
        $parameters     = $request->request->all();
        //var_dump($parameters);exit;
        
        //Duplication de chaque event du plan $id vers le nouvel l'agenda du nouvel user
        $plan = $coachingPlanRep->find($planId);
        
        $events = $agendaRep->findAgendaEvents(array(
            "planId"                    => $planId,
            "getUserSessionForFlatView" => false
        ), $this->get('translator'));
        
        //var_dump($events);
        
        $lastDay = new \DateTime($events[0]['start']);
        $firstDay = new \DateTime($events[count($events)-1]['start']);
        
        //Durée du plan initial
        $planLength = abs($firstDay->diff($lastDay)->format('%R%a'));
        
        if (isset($parameters["startDate"]) && $parameters["startDate"] != '') {
            $startDate = new \DateTime($parameters["startDate"]);
            $endDate = clone $startDate;
            $endDate = $endDate->add(new \DateInterval('P'.$planLength.'D'));
            /*var_dump($startDate);
            var_dump($endDate);
            var_dump($firstDay);
            var_dump($firstDay->diff($startDate)->format('%R%a'));
            exit;*/
        }
        else if (isset($parameters["endDate"]) && $parameters["endDate"] != '') {
            $endDate = new \DateTime($parameters["endDate"]);
            $startDate = clone $endDate;
            $startDate = $startDate->sub(new \DateInterval('P'.$planLength.'D'));
        }

        //Pour importer à partir de la bonne date, on décale chaque séance en fonction du choix de la date de début souhaitée
        $gap = $firstDay->diff($startDate)->format('%R%a');
        
        //Création d'un nouveau plan pour le nouvel utilisateur
        $coachingPlan = new \Ks\CoachingBundle\Entity\CoachingPlan();
        $coachingPlan->setName($plan->getName());
        $coachingPlan->setCoachingPlanType($coachingPlanTypeRep->find(1));
        $coachingPlan->setUser($user);
        $coachingPlan->setFather($plan);
        $em->persist($coachingPlan);
        $em->flush();

        foreach ($events as $event) {
            //var_dump($event['id']);
            $agendaRep->duplicateEvent( $eventRep->find($event['id']), $gap, 0, true, $user->getId(), $coachingPlan->getId());
        }

        $responseDatas["publishResponse"] = 1;
        $responseDatas["newPlanId"] = $coachingPlan->getId();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
}
