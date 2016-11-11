<?php

namespace Ks\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;


class DashboardController extends Controller
{
    /**
     * @Route("/{id}", name = "ksDashboard_statistics", options={"expose"=true}  )
     * @ParamConverter("user", class="KsUserBundle:User")
     * @Template()
     */
    public function statisticsAction(\Ks\UserBundle\Entity\User $user)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $userRep    = $em->getRepository('KsUserBundle:User');
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'statistics');
        
        $friendsIds  = $userRep->getFriendIds($user->getId());
        if( count($friendsIds) < 1 ) $friendsIds[] = 0;
        //Récupération de la liste des amis trés par nom d'utilisateurs
        $friends = $userRep->findUsers(array("usersIds" => $friendsIds), $this->get('translator'));
        
        //Récupération du sport le plus partiqué par l'utilisateur
        $favoriteSport = $userRep->getFavoriteSportAndStartDate($user->getId());
        
        $sport_id = null;
        $defaultStartDate = date('01-m-Y', strtotime('-12 months'));
        $now = new \DateTime('now');
        
        if (isset($favoriteSport[0]) && $favoriteSport[0]['sport_id'] != null ) {
            $sport_id = $favoriteSport[0]['sport_id'];
            $startOn = new \DateTime($favoriteSport[0]['startOn']);
            $ecart = $startOn->diff($now)->format('%a');
            if ($ecart <= 365) $defaultStartDate = date('d-m-Y', strtotime($favoriteSport[0]['startOn']));
        }
        
        //$friendsForm = $this->createForm(new \Ks\UserBundle\Form\FriendsType($user), $user);
        $periods = array();
        
        setlocale (LC_TIME, 'fr_FR.utf8','fra');
        for( $i = 0; $i < 12 ; $i++ ) {
            //$periods[$i] = date('M Y', strtotime("- " . $i ." month"));
            //$periods[$i] = strftime("%b %Y", strtotime("- " . $i ." month"));
            $monthFR = mb_convert_encoding(strftime("%b %Y", strtotime("- " . $i ." month")), 'utf-8');
            if ($monthFR == 'aoÃ»t') $monthFR = 'aout';
            if ($monthFR == 'dÃ©c.') $monthFR = 'déc.';
            if ($monthFR == 'fÃ©vr.') $monthFR = 'févr.';
            $periods[$i] = $monthFR;
        }
        
        //var_dump($periods);exit;
        
        $activitySportChoiceForm = $this->createForm(new \Ks\ActivityBundle\Form\SportType(null), null);
        
        $myEquipmentsForm  = $this->createForm(new \Ks\EquipmentBundle\Form\MyEquipmentsType($user));
        
        $periodForm = $this->createForm(new \Ks\ActivityBundle\Form\PeriodType(null));
        
        return $this->render('KsDashboardBundle:Dashboard:statistics.html.twig', array(
            'user'    => $user,
            'friends' => $friends,
            'periods' => $periods,
            'favoriteSport'=> $sport_id,
            'defaultStartDate' => $defaultStartDate,
            'defaultEndDate' => $now->format('d-m-Y'),
            'activitySportChoiceForm' => $activitySportChoiceForm->createView(),
            'myEquipmentsForm' => $myEquipmentsForm->createView(),
            'periodForm' => $periodForm->createView()
        )); 
    }
    
    /**
     * @Route("/comparison/{id}", name = "ksDashboard_comparison", options={"expose"=true}  )
     * @ParamConverter("user", class="KsUserBundle:User")
     * @Template()
     */
    public function comparisonAction(\Ks\UserBundle\Entity\User $user)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $userRep    = $em->getRepository('KsUserBundle:User');
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'comparison');
        
        $friendsIds  = $userRep->getFriendIds($user->getId());
        if( count($friendsIds) < 1 ) $friendsIds[] = 0;
        //Récupération de la liste des amis trés par nom d'utilisateurs
        $friends = $userRep->findUsers(array("usersIds" => $friendsIds), $this->get('translator'));
        
        $periods = array();
        
        setlocale (LC_TIME, 'fr_FR.utf8','fra');
        for( $i = 0; $i < 12 ; $i++ ) {
            //$periods[$i] = date('M Y', strtotime("- " . $i ." month"));
            //$periods[$i] = strftime("%b %Y", strtotime("- " . $i ." month"));
            $monthFR = mb_convert_encoding(strftime("%b %Y", strtotime("- " . $i ." month")), 'utf-8');
            if ($monthFR == 'aoÃ»t') $monthFR = 'aout';
            if ($monthFR == 'dÃ©c.') $monthFR = 'déc.';
            if ($monthFR == 'fÃ©vr.') $monthFR = 'févr.';
            $periods[$i] = $monthFR;
        }
        
        $activitySportChoiceForm = $this->createForm(new \Ks\ActivityBundle\Form\SportType("Multi"), null);
        //var_dump($sports);
        
        return $this->render('KsDashboardBundle:Dashboard:comparison.html.twig', array(
            'user'    => $user,
            'friends' => $friends,
            'periods' => $periods,
            "activitySportChoiceForm" => $activitySportChoiceForm->createView()
        )); 
    }
    
    /**
     * @Route("/getDataGraphPointsBySportByMonth/{id}", name = "ksDashboard_getDataGraphPointsBySportByMonth", options={"expose"=true} )
     * @ParamConverter("user", class="KsUserBundle:User")
     */
    public function getDataGraphPointsBySportByMonthAction(\Ks\UserBundle\Entity\User $user) {
        $em                     = $this->getDoctrine()->getEntityManager();
        $activityRep            = $em->getRepository('KsActivityBundle:Activity');
        $activitySessionRep     = $em->getRepository('KsActivityBundle:ActivitySession');
        $sportRep               = $em->getRepository('KsActivityBundle:Sport');
        $leagueLevelRep         = $em->getRepository('KsLeagueBundle:LeagueLevel');
        $leagueHistoricRep      = $em->getRepository('KsLeagueBundle:Historic');
        
        $firstActivityDate = $activitySessionRep->findFirstActivityDateInLast12Months($user->getId());
        
        
        //La date de début est la date de première activités dans les 12 mois glissants
        if( $firstActivityDate != null ) {
            $nbStatsMonths = 12;
            $firstDayOfPeriod = date('Y-m-01', strtotime($firstActivityDate));
        } else {
            $nbStatsMonths = 4;
            $firstDayOfPeriod = date('Y-m-01', strtotime("- " . ($nbStatsMonths - 1) ." month"));
        }
        $lastDayOfPeriod = date('Y-m-t');
        $sports = $sportRep->findPractisesSportsInPeriode($user->getId(), $firstDayOfPeriod, $lastDayOfPeriod);
        
        //var_dump($firstDayOfPeriod);
        //var_dump($lastDayOfPeriod);
        
        $points = array();
        //$leagues = array();
        $periods = array();
        
        //Statistiques sur les derniers mois
        for( $i = $nbStatsMonths - 1; $i >= 0 ; $i-- ) {
            //$monthUs = date('Y-m', strtotime("- " . $i. " month", strtotime(date("F") . "1")) );
            //$monthFr = date('M Y', strtotime("- " . $i. " month", strtotime(date("F") . "1")) );
            //$periods[] = $monthFr;
            //$periods[] = mb_convert_encoding(strftime("%b %Y", strtotime("- " . $i ." month")), 'utf-8');
            setlocale (LC_TIME, 'fr_FR.utf8','fra');
            $monthFR = mb_convert_encoding(strftime("%b %Y", strtotime("- " . $i ." month")), 'utf-8');
            if ($monthFR == 'aoÃ»t') $monthFR = 'aout';
            if ($monthFR == 'dÃ©c.') $monthFR = 'déc.';
            if ($monthFR == 'fÃ©vr.') $monthFR = 'févr.';
            $periods[] = $monthFR;
            //$periods[] = strftime("%b %Y", strtotime("- " . $i ." month"));

            $firstDayOfMonth = date('Y-m-01', strtotime("- " . $i. " month", strtotime(date("F") . "1")) );
            $lastDayOfMonth = date('Y-m-t', strtotime("- " . $i. " month", strtotime(date("F") . "1")) );
                
            foreach( $sports as $sportId => $sportLabel ) {
                $temp[0] = $sportId;
                $points[$sportId][] = $activityRep->findEarnedPointsBySport($user->getId(), $temp, $firstDayOfMonth, $lastDayOfMonth);
            }
            
            $month = date('m', strtotime("- " . $i ." month"));
            $year = date('Y', strtotime("- " . $i ." month"));
            
            $chartLeagues[] = $leagueHistoricRep->findLeagueHistoric( $month, $year, $user->getId());
        }
        
        //Barre de cumul
        $cumulForPie = array();
        $cumulPoints = 0;
        $colors = array(
            '#4572A7', 
            '#AA4643', 
            '#89A54E', 
            '#80699B', 
            '#3D96AE', 
            '#DB843D', 
            '#92A8CD', 
            '#A47D7C', 
            '#B5CA92'
        );
        $iColors = 0;
        foreach( $sports as $sportId => $sportLabel ) {
            //$points[$sportId][] = $activityRep->findEarnedPoints($user->getId(), $sportId, $firstDayOfPeriod, $lastDayOfPeriod);
            $temp[0] = $sportId;
            $p = $activityRep->findEarnedPointsBySport($user->getId(), $temp, $firstDayOfPeriod, $lastDayOfPeriod);
            $cumulForPie[] = array(
                "id"    => $sportId,
                "name" => $sportLabel,
                "y"    => $p,
                "color" => $colors[$iColors]
            );
            $cumulPoints += $p;
            
            $iColors++;
            if( $iColors >= count( $colors )) $iColors = 0;
        }
        //$periods[] = "Cumul";
        
        $leagues = $leagueLevelRep->findLeagues(array(), $this->get('translator'));
        
        $responseDatas = array(
            "response" => 1,
            "chart" => array(
                "points"        => $points,
                "cumulForPie"   => $cumulForPie,
                "leaguesIds"       => $chartLeagues
            ),
            "sports"            => $sports,
            "periods"           => $periods,
            "firstActivityDate" => $firstActivityDate,
            "cumulPoints"       => $cumulPoints,
            "leagues"           => $leagues,
        );
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/getDataGraphPointsByCommonSportVersusUser/", name = "ksDashboard_getDataGraphPointsByCommonSportVersusUser", options={"expose"=true} )
     */
    public function getDataGraphPointsByCommonSportVersusUserAction() {
        $em             = $this->getDoctrine()->getEntityManager();
        $request        = $this->getRequest();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $activitySessionRep    = $em->getRepository('KsActivityBundle:ActivitySession');
        $sportRep       = $em->getRepository('KsActivityBundle:Sport');
        $userRep        = $em->getRepository('KsUserBundle:User');
        $parameters     = $request->request->all();
        
        //Recherche de la période, FIXME, pour l'instant choix sur une combolist idéalement plus tard, choix de date à date avec un dateLoader
        $periods;
        if( isset($parameters['periodeSelect']) && $parameters['periodeSelect'] != '') {
            if( $parameters['periodeSelect'] == "cumul" ) {
                $startOn = date('Y-m-01', strtotime("- 11 month"));
                $endOn = date('Y-m-t', strtotime("- 0 month"));

            } else {
                $startOn = date('Y-m-01', strtotime("- " . $parameters['periodeSelect'] ." month"));
                $endOn = date('Y-m-t', strtotime("- " . $parameters['periodeSelect'] ." month"));
                $periods = date('M Y', strtotime("- " . $parameters['periodeSelect'] ." month"));
            }
        } else {
            if( isset( $parameters['startOn'] )) $startOn = $parameters['startOn'];
            else $startOn = date('Y-m-01', strtotime("- 0 month"));
            
            if( isset( $parameters['endOn'] )) $startOn = $parameters['endOn'];
            else $endOn = date('Y-m-t', strtotime("- 0 month"));
        }
        
        //On construit le tableaux d'utilisateurs
        $users = array();
        
        if( isset( $parameters["userIds"] )) {
            foreach( $parameters["userIds"] as $userId ) {
                $user = $userRep->find($userId);
                if(!is_object($user)) {
                    throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $userId . '.');
                }
                $users[] = array($userId, $user->getUsername());
            }
        }
       
        if( isset( $parameters["top5Friends"] ) && $parameters["top5Friends"] == "true" ) {
            $top5Users = $userRep->findTopFriendsBySport($parameters["userIds"][0], 5, $startOn, $endOn, $parameters["sportSelect"]);
            
            //On tri par points
            foreach ($top5Users as $key => $row) {
                $p[$key]  = $row['points'];
                $userIds[$key] = $row['userId'];
            }
            array_multisort($p, SORT_DESC, $userIds, SORT_ASC, $top5Users);
            
            unset($users);
            
            foreach( $top5Users as $topUser ) {
                $user = $userRep->find($topUser["userId"]);
                if(!is_object($user)) {
                    throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $topUser["userId"] . '.');
                }
                $parameters["userIds"][] = $topUser["userId"];
                $users[] = array($topUser["userId"], $user->getUsername());
            }
        }
        elseif( isset( $parameters["top5Ks"] ) && $parameters["top5Ks"] == "true" ) {
            $top5Users = $userRep->findTopUsersBySport($parameters["userIds"][0], 5, $startOn, $endOn, $parameters["sportSelect"]);

            //On tri par points
            foreach ($top5Users as $key => $row) {
                $p[$key]  = $row['points'];
                $userIds[$key] = $row['userId'];
            }
            
            array_multisort($p, SORT_DESC, $userIds, SORT_ASC, $top5Users);
            
            unset($users);
            
            foreach( $top5Users as $topUser ) {
                $user = $userRep->find($topUser["userId"]);
                if(!is_object($user)) {
                    throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $topUser["userId"] . '.');
                }
                $parameters["userIds"][] = $topUser["userId"];
                $users[] = array($topUser["userId"], $user->getUsername());
            }
        }
        
        //Récupération du périmètre (en fonction de la sélection éventuelle de l'utilisateur
        if (isset($parameters["sportSelect"]) && $parameters["sportSelect"][0] != -1) {
            foreach ($parameters["sportSelect"] as $sportSelectId => $sportId) {
                $sport = $sportRep->findOneBy(array('id' => $sportId));
                $sports[$sport->getId()] = $sport->getLabel();
                foreach( $users as $user ) {
                    //FIXME : du grand nimp ce code...
                    $temp[0] = $sportId;
                    $points[$sport->getId()][] = $activityRep->findEarnedPointsBySport($user[0], $temp, $startOn, $endOn);
                }
            }
        }
        else {
            $sports = $sportRep->findPractisesSportsInPeriodeByUsers($parameters["userIds"], $startOn, $endOn);
            foreach( $sports as $sportId => $sportLabel  ) {
                $temp[0] = $sportId;
                foreach( $users as $user ) {
                    $points[$sportId][] = $activityRep->findEarnedPointsBySport($user[0], $temp, $startOn, $endOn);
                }
            }
        }
        
//        unset($sports);
//        $sports = $sportRep->findPractisesSportsInPeriodeByUsers($parameters["userIds"], $startOn, $endOn);
//        
        
                
        
        
//        foreach( $sports as $sportId => $sportLabel  ) {
//            foreach( $users as $user ) {
//                $points[$sportId][] = $activityRep->findEarnedPointsBySport($user[0], $parameters["sportSelect"], $startOn, $endOn);
//            }
//        }
        
        //var_dump($points);

        $responseDatas = array(
            "response" => 1,
            "chart" => array(
                "points"     => $points,
            ),
            "sports"    => $sports,
            //"periods"   => $periods,
            "users"     => $users,
            "startOn"   => $startOn,
            "endOn"     => $endOn
        );
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/getDataGraphTopLeaguesUsers/", name = "ksDashboard_getDataGraphTopLeaguesUsers", options={"expose"=true} )
     */
    public function getDataGraphTopLeaguesUsersAction() {
        $em             = $this->getDoctrine()->getEntityManager();
        $request        = $this->getRequest();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $activitySessionRep    = $em->getRepository('KsActivityBundle:ActivitySession');
        $leagueCatRep   = $em->getRepository('KsLeagueBundle:LeagueCategory');
        $sportRep       = $em->getRepository('KsActivityBundle:Sport');
        $userRep        = $em->getRepository('KsUserBundle:User');
        $parameters     = $request->request->all();
        

        $startOn = date('Y-m-01', strtotime("- 0 month"));
        $endOn = date('Y-m-t', strtotime("- 0 month"));
        
        $leagues = array();
        $gold = $leagueCatRep->findOneByLabel("gold");
        if( is_object($gold) ) $leagues[$gold->getId()] = "gold";
        $silver = $leagueCatRep->findOneByLabel("silver");
        if( is_object($silver) ) $leagues[$silver->getId()] = "silver";
        $bronze = $leagueCatRep->findOneByLabel("bronze");
        if( is_object($bronze) ) $leagues[$bronze->getId()] = "bronze";
        
        $users = array();
        $points = array();
        $sports = array();
        if( !isset($parameters["userIds"]) ) $parameters["userIds"] = array();
        
        foreach( $leagues as $leagueCategoryId => $leagueCategoryLabel ) {
            //On construit le tableaux d'utilisateurs
            $users[$leagueCategoryId] = array();

            $top5Users = $userRep->findTopLeagueUsers(3, $leagueCategoryId, $startOn, $endOn);
            
            $p = array();
            $userIds = array();
            //On tri par points
            foreach ($top5Users as $key => $row) {
                $p[$key]  = $row['points'];
                $userIds[$key] = $row['userId'];
            }

            array_multisort($p, SORT_DESC, $userIds, SORT_ASC, $top5Users);
            
            foreach( $top5Users as $topUser ) {
                $user = $userRep->find($topUser["userId"]);
                if(!is_object($user)) {
                    throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $topUser["userId"] . '.');
                }
                $parameters["userIds"][] = $topUser["userId"];
                $users[$leagueCategoryId][$topUser["userId"]] = $user->getUsername();
            }

            $sports[$leagueCategoryId] = $sportRep->findPractisesSportsInPeriodeByUsers($parameters["userIds"], $startOn, $endOn);

            foreach( $sports[$leagueCategoryId] as $sportId => $sportLabel ) {
                foreach( $users[$leagueCategoryId] as $userId => $userUsername ) {
                    $points[$leagueCategoryId][$sportId][] = $activityRep->findEarnedPointsBySport($userId, $sportId, $startOn, $endOn);
                }
            }
        }
        
        $responseDatas = array(
            "response" => 1,
            "chart" => array(
                "points"     => $points,
            ),
            "sports" => $sports,
            //"periods" => $periods,
            "users"     => $users,
            "leagues"   => $leagues
        );
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/getDataGraphCumulPointsOnPeriod/", name = "ksDashboard_getDataGraphCumulPointsOnPeriod", options={"expose"=true} )
     */
    public function getDataGraphCumulPointsOnPeriodAction() {
        $em             = $this->getDoctrine()->getEntityManager();
        $request        = $this->getRequest();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $activitySessionRep    = $em->getRepository('KsActivityBundle:ActivitySession');
        $sportRep       = $em->getRepository('KsActivityBundle:Sport');
        $userRep        = $em->getRepository('KsUserBundle:User');
        $parameters     = $request->request->all();
        $user           = $userRep->find($parameters['userId']);
        
        if(!is_object($user)) {
           throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $parameters['userId'] . '.');
        } 
        
        $firstDayOfPeriod = $parameters["startOn"];
        $lastDayOfPeriod = $parameters["endOn"];
        
        $sports = $sportRep->findPractisesSportsInPeriode($user->getId(), $firstDayOfPeriod, $lastDayOfPeriod);
        $points = array();
        $periods = array();
        
        $periods[] = "Du " . $firstDayOfPeriod . " au " . $lastDayOfPeriod;

        foreach( $sports as $sportId => $sportLabel ) {
            $points[$sportId][] = $activityRep->findEarnedPointsBySport($user->getId(), $sportId, $firstDayOfPeriod, $lastDayOfPeriod);
        }
        
        $responseDatas = array(
            "response" => 1,
            "chart" => array(
                "points"     => $points,
            ),
            "sports" => $sports,
            "periods" => $periods,
            "parameters" => $parameters
        );
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/getDataGraphDependingOnSport/", name = "ksDashboard_getDataGraphDependingOnSport", options={"expose"=true} )
     */
    public function getDataGraphDependingOnSportAction() {
        $em             = $this->getDoctrine()->getEntityManager();
        $request        = $this->getRequest();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $activitySessionRep    = $em->getRepository('KsActivityBundle:ActivitySession');
        $sportRep       = $em->getRepository('KsActivityBundle:Sport');
        $equipmentRep   = $em->getRepository('KsUserBundle:Equipment');
        $userRep        = $em->getRepository('KsUserBundle:User');
        $parameters     = $request->request->all();
        $user           = $userRep->find($parameters['userId']);
        
        if(!is_object($user)) {
           throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $parameters['userId'] . '.');
        } 
        
        $sportDetails = array(
            "id"    => null,
            "label" => null,
            "type"  => null
        );
             
        if( isset($parameters['sportId']) && $parameters['sportId'] != '') {
            $sport          = $sportRep->find($parameters['sportId']);
            if( is_object($sport) ) {
                $sportDetails["id"] = $sport->getId();
                $sportDetails["label"] = $sport->getLabel();
                $sportDetails["type"] = $sport->getSportType()->getCode(); 
            }
        }
        
        if( isset($parameters['planId']) && $parameters['planId'] != '') {
            $planId = $parameters['planId'];
        }
        else $planId = null;
        
        $equipmentDetails = array();
        $equipmentDetails["id"] = null;
        if( isset($parameters['equipmentId']) && $parameters['equipmentId'] != '') {
            $equipment = $equipmentRep->find($parameters['equipmentId']);
            if( is_object($equipment) ) {
                $equipmentDetails["id"] = $equipment->getId();
                $equipmentDetails["label"] = $equipment->getName();
            }
        }
        
        if (isset($parameters['lastMonths']) && $parameters['lastMonths'] != -1) {
            $nbStatsMonths = $parameters['lastMonths'];
        }
        else {
            $nbStatsMonths = 1;
        }
        if (isset($parameters['startOn']) && $parameters['startOn'] != '') {
            //var_dump($parameters['startOn']);
            $startOn = new \DateTime($parameters['startOn']);
        }
        else {
            $startOn = $firstDayOfPeriod = date('01/m/Y', strtotime("- " . ($nbStatsMonths - 1) ." month"));
        }
        
        if (isset($parameters['endOn']) && $parameters['endOn'] != '') {
            $endOn = new \DateTime($parameters['endOn']);
            //var_dump($endOn);
        }
        else {
            $endOn = date('t/m/Y');
        }
        
        $periodeLabel = $this->get('translator')->trans('coaching.from')." " . $startOn->format('d/m/y') . " " . $this->get('translator')->trans('coaching.to') . " " . $endOn->format('d/m/y');
        //var_dump($periodeLabel);
        $ecart = $startOn->diff($endOn)->format('%a');
        //var_dump($ecart);
        
        $periods = array();
        $durations = array();
        $teamSportSessionResults = array(
            "v" => array(),
            "n" => array(),
            "d" => array(),
        );
        $enduranceSessionDetails = array(
            "distance" => array(),
            "d+" => array(),
            "d-" => array(),
        );
        
        $cumulResuts = array(
            "v" => 0,
            "n" => 0,
            "d" => 0,
        );
        
        $cumulCompetitionsTrainings = array(
            "competition" => 0,
            "training" => 0,
        );
        
        $cumulDurations = 0;
        $cumulKilometers = 0;
        $cumulDenPos = 0;
        $cumulDenNeg = 0;
        
        //var_dump("ecart=".$ecart);
        //exit;
        
        //FMO : si ecart 
        if ($ecart >= 7*12) {
            //Vue avec mois en abscisse
            $step = round($ecart/31, 0) +1;
            $monthEnd = $endOn->format('Y-m-01');
            //var_dump($monthEnd);
        }
        else if ($ecart >=12 && $ecart < 7*12) {
            //Vue avec semaine en abscisse
            $step = round($ecart/7,0 ) +1;
            //$week = date('W', strtotime($endOn->format('Y-m-d')));
            //var_dump($week);
            $delay = date('w', strtotime($startOn->format('Y-m-d')))-1;
            
            if ($delay == -1) $delay +=7; //Sunday
            
            //var_dump($week_start);
            //var_dump($week_end);
            //exit;
        }
        else {
            //Vue avec jours en abscisse
            $step = $ecart +1;
        }
        
        //var_dump("step=".$step);
        
        for( $i = $step - 1; $i >= 0 ; $i-- ) {
            if ($ecart >= 7*12) {
                //$monthUs = date('Y-m', strtotime("- " . $i ." month"));
                //$monthFr = date('M Y', strtotime("- " . $i ." month"));
                //$periods[] = $monthFr;
                //$periods[] = mb_convert_encoding(strftime("%b %Y", strtotime("- " . $i ." month"));
                //$periods[] = strftime("%b %Y", strtotime("- " . $i ." month"));
                //setlocale (LC_TIME, 'fr_FR.utf8','fra');
                //$monthFR = mb_convert_encoding(strftime("%b %Y", strtotime($monthEnd.strtotime(" - " . $i ." month"))), 'utf-8');
                //if ($monthFR == 'aoÃ»t') $monthFR = 'aout';
                //if ($monthFR == 'dÃ©cembre') $monthFR = 'decembre';
                //if ($monthFR == 'fÃ©vrier') $monthFR = 'fevrier';
                $month = date('M. Y', strtotime($monthEnd.' -' . $i .' months'));
                $month = str_replace('Feb','Fev',$month);
                $month = str_replace('Apr','Avr',$month);
                $month = str_replace('May','Mai',$month);
                $month = str_replace('Jun','Juin',$month);
                $month = str_replace('Jul','Jui',$month);
                $month = str_replace('Aug','Aou',$month);
                $periods[] = $month;
                $firstDay = date('Y-m-01', strtotime($monthEnd.' -' . $i .' months'));
                $lastDay = date('Y-m-t', strtotime($monthEnd.' -' . $i .' months'));
                //var_dump($firstDay);
                //var_dump($lastDay);
                
            }
            else if ($ecart >=12 && $ecart < 7*12) {
                $daysToSu = $delay + 7*($step - ($i+1));
                $daysToMo = $daysToSu - 6;
                //var_dump("delay=$delay"."-"."i=".$i."-"."daysToMo=$daysToMo"."-"."daysToSu=$daysToSu");
                
                $firstDay = date('Y-m-d', strtotime($startOn->format('Y-m-d').' +'.$daysToMo.' days'));
                $lastDay = date('Y-m-d', strtotime($startOn->format('Y-m-d').' +'.$daysToSu.' days'));
                //var_dump($firstDay);
                //var_dump($lastDay);
                
                //$week = strftime("S%w", strtotime($endOn->format('Y-m-d')." - " . $i ." week"));
                $week = date('dM', strtotime($startOn->format('Y-m-d').' +'.$daysToMo.' days'))." / ".date('dM', strtotime($startOn->format('Y-m-d').' +'.$daysToSu.' days'));
                $week = str_replace('Feb','Fev',$week);
                $week = str_replace('Apr','Avr',$week);
                $week = str_replace('May','Mai',$week);
                $week = str_replace('Jun','Juin',$week);
                $week = str_replace('Jul','Jui',$week);
                $week = str_replace('Aug','Aou',$week);
                
                $periods[] = $week;
            }
            else {
                $firstDay = date('Y-m-d', strtotime($endOn->format('Y-m-d')." -" . $i ." day"));
                $lastDay = $firstDay;
                $day = strftime("%d-%m", strtotime($endOn->format('Y-m-d')." -" . $i ." day"));
                $periods[] = $day;
            }
            
            $duration = $activitySessionRep->findActivitiesDuration($user->getId(), $firstDay, $lastDay, $sportDetails["id"], $equipmentDetails["id"], $planId);
            $durations[] = $duration;
            $cumulDurations += $duration;
            
            if( $sportDetails["type"] != null && $sportDetails["type"] == "TS" ) {
                $results = $activitySessionRep->findTeamSportSessionsWithResult($user->getId(), $firstDay, $lastDay, $sportDetails["id"], $equipmentDetails["id"], $planId);
                $teamSportSessionResults["v"][] = $results["v"];
                $teamSportSessionResults["n"][] = $results["n"];
                $teamSportSessionResults["d"][] = $results["d"];
                
                $cumulResuts["v"] += $results["v"];
                $cumulResuts["n"] += $results["n"];
                $cumulResuts["d"] += $results["d"];
            }
            
            if( $sportDetails["type"] != null && ( $sportDetails["type"] == "EOE" || $sportDetails["type"] == "EUW" ) ) {
                $details = $activitySessionRep->findEnduranceSessionDetails($user->getId(), $firstDay, $lastDay, $sportDetails["id"], $equipmentDetails["id"], $planId);
                $enduranceSessionDetails["distance"][] = $details["distance"];
                $enduranceSessionDetails["d+"][] = $details["d+"];
                $enduranceSessionDetails["d-"][] = $details["d-"];
                
                $cumulKilometers += $details["distance"];
                $cumulDenPos += $details["d+"];
                $cumulDenNeg += $details["d-"];
            }
            
            //var_dump("ici");
            $competitionsAndTrainingsRate = $activitySessionRep->findCompetitionsAndTrainingsRate($user->getId(), $firstDay, $lastDay, $sportDetails["id"], $equipmentDetails["id"], $planId);
            $cumulCompetitionsTrainings["competition"] += $competitionsAndTrainingsRate['number']["competition"];
            $cumulCompetitionsTrainings["training"] += $competitionsAndTrainingsRate['number']["training"];
        }
        
        
        //var_dump($sportDetails);exit;
            
        $responseDatas = array(
            "response" => 1,
            "chart" => array(
                "durations"                             => $durations,
                "teamSportSessionResults"               => $teamSportSessionResults,
                "enduranceSessionDetails"               => $enduranceSessionDetails,
                "cumulResuts"                           => $cumulResuts,
                "cumulCompetitionsTrainings"            => $cumulCompetitionsTrainings,
                "cumulDurations" => $cumulDurations,
                "cumulKilometers" => $cumulKilometers,
                "cumulDenPos" => $cumulDenPos,
                "cumulDenNeg" => $cumulDenNeg
            ),
            "sport" => $sportDetails,
            "equipment" => $equipmentDetails,
            "periods" => $periods,
            "periodeLabel" => $periodeLabel,
            "startOn" => $startOn,
            "endOn"   => $endOn
            
        );

        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/getDataComparisonTool/", name = "ksDashboard_getDataComparisonTool", options={"expose"=true} )
     */
    public function getDataComparisonToolAction() {
        $em             = $this->getDoctrine()->getEntityManager();
        $request        = $this->getRequest();
        $activitySessionRep    = $em->getRepository('KsActivityBundle:ActivitySession');
        $userRep        = $em->getRepository('KsUserBundle:User');
        $parameters     = $request->request->all();
        //var_dump($parameters);exit;
        
        if (isset($parameters['userId']) && !is_null($parameters['userId'])) $user   = $userRep->find($parameters['userId']);
        else $user  = $this->get('security.context')->getToken()->getUser();
        
        if(!is_object($user)) {
           throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $parameters['userId'] . '.');
        }
        
        $suuntoApi  = $this->container->get('ks_user.suunto');
        $code       = $em->getRepository('KsUserBundle:Service')->find(5);
        $userServiceArray = $em->getRepository('KsUserBundle:UserHasServices')->findBy(array("user" => $user->getId(), "service" => $code->getId(), "is_active" => 1));
        if (isset($userServiceArray) && !is_null($userServiceArray) && count($userServiceArray)>0) {
            $userService = $userServiceArray[0];
            $accessToken    = $userService->getToken();
            $suuntoApi->setAccessToken($accessToken);
        }
        
        $importActivityService = $this->container->get('ks_activity.importActivityService');
        
        $details = $activitySessionRep->findActivitiesFromCoachingSessionId($user->getId(), $parameters['activityId'], $parameters['checkboxCT'], $importActivityService, $suuntoApi);
            
        $isAllowedPackElite = 0;
        $isAllowedPackPremium = 0;
        if ($user->getIsAllowedPackElite()) $isAllowedPackElite = 1;
        if ($user->getIsAllowedPackPremium()) $isAllowedPackPremium = 1;
        
        if ($user->getId() == 7) {
            //Pour permettre l'affichage du comparateur de séance sur clic "DECOUVRIR" de la page de login
            $isAllowedPackElite = 1;
        }
        
        $responseDatas = array(
            "isAllowedPackElite"    => $isAllowedPackElite,
            "isAllowedPackPremium"  => $isAllowedPackPremium,
            "coachingSessionName"   => $details["name"],
            "chart"                 => array("details" => $details)
        );

        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
