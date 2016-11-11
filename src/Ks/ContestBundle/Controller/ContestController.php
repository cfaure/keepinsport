<?php

namespace Ks\ContestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;


class ContestController extends Controller
{
    /**
     * @Route("/coach", name = "ksContest_coach", options={"expose"=true}  )
     */
    public function indexAction()
    {
        $securityContext    = $this->container->get('security.context');
        $em         = $this->getDoctrine()->getEntityManager();
        $userRep    = $em->getRepository('KsUserBundle:User');
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'contest');
        
        $userH = array();
        $godsons = array();
        $godFatherForm = null;
        $contestUsers = array();
        
        //Précédent contest : 2013-04-15 => 2013-06-30
        
        $now = new \DateTime();
        $startOn = date("Y-m-01");
        $endOn = date("Y-m-t");
        
        $params = array(
            "activitiesStartOn"             => $startOn,
            "activitiesEndOn"               => $endOn,
            "withPoints"                    => true,
            "withGodsonsPoints"             => true, 
            "withFriendsNumber"             => true,
            "withActivitiesNumber"          => true,
            "usersWith0points"              => false,
            "withKsUser"                    => false,
            "withOnlyGodSonsFromThisMonth"  => true,
            "withUsersKsTeam"               => false,
            //"godFathersOnly"        => true
        );
        
        $users      = $userRep->findUsers( $params, $this->get('translator') );
        
        //usort( $users, array( "Ks\UserBundle\Entity\UserRepository", "orderUsersByPointsDesc" ) );
        usort( $users, array( "Ks\UserBundle\Entity\UserRepository", "orderUsersByGodFatherPointsDesc" ) );
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user       = $this->container->get('security.context')->getToken()->getUser();
            $godFatherForm = $this->createForm(new \Ks\UserBundle\Form\GodFatherType($user), $user)->createView();
        
            //Pour la partie "Mon classement"
            $params["withUsersKsTeam"] = false;

            $userH = $userRep->findOneUser(array("userId" => $user->getId()), $this->get('translator'));
            $contestUsers = $userRep->findContestUsers($userH["id"], $params);

            $godsons = $userRep->findGodsonsAndLittlesGodsons($userH["id"]);
        }
        
        return $this->render('KsContestBundle:Contest:coach.html.twig', array(
            "users"         => $users,
            "contestUsers"  => $contestUsers,
            "startOn"       => $startOn,
            "endOn"         => $endOn,
            "godFatherForm" => $godFatherForm,
            "user"          => $userH,
            "godsons"       => $godsons
        ));
    }
    
    /**
     * @Route("/seasonShop/{shopId}/{delay}", defaults={"delay" = 0}, name = "KsContest_seasonShop", options={"expose"=true}  )
     */
    public function seasonShopAction($shopId, $delay)
    {
        $securityContext    = $this->container->get('security.context');
        $em                 = $this->getDoctrine()->getEntityManager();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $shopRep            = $em->getRepository('KsShopBundle:Shop');
        $leagueCatRep       = $em->getRepository('KsLeagueBundle:LeagueCategory');  
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'contest');
        
        $shop = $shopRep->find($shopId);
        
        $contestUsers = array();
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $userId = $this->get('security.context')->getToken()->getUser()->getId();

            $user = $userRep->findOneUser(array(
                "userId"            => $userId,
                "withPoints"        => true,
                "activitiesStartOn" => date('Y-m-01', strtotime(($delay>0 ? "+ " : "- ").abs($delay)." month")),
                "activitiesEndOn"   => date('Y-m-t', strtotime(($delay>0 ? "+ " : "- ").abs($delay)." month"))
            ), $this->get('translator'));
        }
        
        $leaguesUsers = array();
        $stepPoints = array();
        
        //Récupération des leagues
        $leaguesCategories = $leagueCatRep->findLeaguesUpdatables();
        
        $totalPoints = 0;
        $totalPointsLeague = 0;
        
        foreach( $leaguesCategories as $leagueCategory ) {
            
            //Récupération plus d'informations sur les utilisateurs (les points nous interessent)
            $users      = $userRep->findUsers(array(
                "withPoints"            => true,
                "withGodsonsPoints"     => true,
                "withUsersKsTeam"       => false,
                "sports"                => $shop->getSports(),
                "countryCode"           => $shop->getCountryCode(),
                "countryArea"           => $shop->getCountryArea(),
                "town"                  => $shop->getTown(),
                "latitude"              => $shop->getLatitude(),
                "longitude"             => $shop->getLongitude(),
                "userWithEmail"         => true,
                "userWithPhone"         => false,
                "leagueCategoryId"      => $leagueCategory["id"],
                "activitiesStartOn"     => date('Y-m-01', strtotime(($delay>0 ? "+ " : "- ").abs($delay)." month")),
                "activitiesEndOn"       => date('Y-m-t', strtotime(($delay>0 ? "+ " : "- ").abs($delay)." month")),
                "test"                  => false,
                "delay"                 => $delay,
                "shopId"                => $shopId
                
            ), $this->get('translator'));

            //Tri par points décroissants
            usort( $users, array( "Ks\UserBundle\Entity\UserRepository", "orderUsersByPointsDesc" ) );
            
            //1 bon d'achat parmi les 3 ligues pour Endurance shop Toulouse (remplacer -1 par 5)
            //1 bon d'achat par ligue pour les autres (à terme)
            if ($shopId == -1) $totalPointsLeague = 0;
            foreach ($users as $userTemp) {
                $totalPointsLeague += $userTemp['points'];
                $totalPoints += $userTemp['points'];
            }

            if ($shopId == -1) {
                $key =0;
                if ($totalPointsLeague != 0) {
                    foreach ($users as $userTemp) {
                        $userTemp['chance'] = $userTemp['points'] / $totalPointsLeague;
                        $users[$key]['chance'] = round($userTemp['points'] / $totalPointsLeague *100);
                        $key += 1;
                    }
                }
            }
            //var_dump($users);
            
            $leaguesUsers[$leagueCategory["label"]] = $users;
        }
        
        if ($shopId != -1) {
            if ($totalPoints != 0) {
                foreach( $leaguesCategories as $leagueCategory ) {
                    $key =0;
                    foreach ($leaguesUsers[$leagueCategory["label"]] as $userTemp) {
                        $userTemp['chance'] = $userTemp['points'] / $totalPoints;
                        $leaguesUsers[$leagueCategory["label"]][$key]['chance'] = round($userTemp['points'] / $totalPoints *100);
                        $key += 1;
                    }
                }
            }
        }
        //var_dump($leaguesUsers);
        
        return $this->render('KsContestBundle:Contest:seasonShop.html.twig', array(
            'user'                  => $user,
            'shop'                  => $shop,
            'leaguesUsers'          => $leaguesUsers,
            'month'                 => date('F', strtotime(($delay>0 ? "+ " : "- ").abs($delay)." month")),
            'delay'                 => $delay
        ));
    }
    
    
    
    /**
     * @Route("/columnBloc", name = "ksContest_columnBloc" )
     */
    public function columnBlocAction()
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $userRep            = $em->getRepository('KsUserBundle:User');
        
        $now = new \DateTime();
        if( $now->format('Y-m-d') >= "2013-04-15") {
            $startOn = new \DateTime("2013-04-15");
            $endOn = new \DateTime("2013-06-30");
        }
        else {
            $startOn = new \DateTime("2013-04-01");
            $endOn = new \DateTime("2013-04-15");
        }
        
        $params = array(
            "activitiesStartOn"     => $startOn->format('Y-m-d'),
            "activitiesEndOn"       => $endOn->format('Y-m-d'),
            "withPoints"            => true,
            "withGodsonsPoints"     => true, 
            "withFriendsNumber"     => true,
            "withActivitiesNumber"  => true,
            "usersWith0points"      => false,
            //"withKsUser"            => false,
            "withUsersKsTeam"       => false,
            //"godFathersOnly"        => true
        );
        
        $users = $userRep->findContestUsers($user->getId(), $params);
        
        return $this->render('KsContestBundle:Contest:_columnBloc.html.twig', array(
            "users"     => $users,
        ));
    }
}
