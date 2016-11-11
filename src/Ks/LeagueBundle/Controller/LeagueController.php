<?php
namespace Ks\LeagueBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class LeagueController extends Controller
{      
    /**
     * @Route("/", name = "ks_league_leagueStandings" )
     */
    public function leagueStandingsAction()
    {
        $em             = $this->getDoctrine()->getEntityManager();    
        $leagueCatRep   = $em->getRepository('KsLeagueBundle:LeagueCategory');
        $leagueLevelRep = $em->getRepository('KsLeagueBundle:LeagueLevel');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $leagueCategories = $leagueCatRep->findAll();
        $usersInCategory = array();
        
        foreach ( $leagueCategories as $leagueCategory ) {
            $usersInCategory[$leagueCategory->getId()] = $leagueLevelRep->findUsersByCategory($leagueCategory, $user);
        }

        return $this->render('KsLeagueBundle:League:_leagueStandings.html.twig', array(
            'leagueCategories'  => $leagueCategories,
            'users'             => $usersInCategory
        ));
    }
    
    /**
     * @Route("/community/{userId}", name = "ks_league_communityStandings" )
     */
    public function communityStandingsAction($userId)
    {
        $em             = $this->getDoctrine()->getEntityManager();    
        $leagueCatRep   = $em->getRepository('KsLeagueBundle:LeagueCategory');
        $leagueLevelRep = $em->getRepository('KsLeagueBundle:LeagueLevel');
        $userRep        = $em->getRepository('KsUserBundle:User');
        $user           = $userRep->find($userId);
        
        if( ! is_object($user) )
        {
           throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $userId . '.');
        }
        
        $beginSeasonDateFr = $this->container->getParameter('beginSeasonDateFr');
        $beginningOfSeason = \DateTime::createFromFormat('d/m/Y H:i:s', $beginSeasonDateFr);
        
        $leagueCategories = $leagueCatRep->findAll();  
        $usersOfMyCommunity = $userRep->getCommunityOf($user);
        
        $myCommunityIds = array();
        
        foreach( $usersOfMyCommunity as $user ) {
            $myCommunityIds[] = $user->getId();
        }

        $usersOfMyCommunityInCategory = array();
        foreach ( $leagueCategories as $leagueCategory ) {
            $usersOfMyCommunityInCategory[$leagueCategory->getId()] = $leagueLevelRep->findUsersOfMyCommunityByCategory($leagueCategory, $myCommunityIds, $beginningOfSeason);
        }
        
        //var_dump($usersOfMyCommunityInCategory);

        return $this->render('KsLeagueBundle:League:_leagueStandings.html.twig', array(
            'leagueCategories'      => $leagueCategories,
            'users'                 => $usersOfMyCommunityInCategory
        ));
    }
    
    /**
     * @Route("/lastUpdate", name = "ks_league_lastUpdate" )
     
    public function lastUpdateAction()
    {
        $em                 = $this->getDoctrine()->getEntityManager();    
        $historicRep        = $em->getRepository('KsLeagueBundle:Historic');
        $leagueUpdateRep    = $em->getRepository('KsLeagueBundle:LeagueUpdate');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $user               = $this->get('security.context')->getToken()->getUser();
          
        if( ! is_object($user) )
        {
            throw new AccessDeniedException("Il faut être connecté pour consulter cette page");
        }
        
        $historic = $historicRep->findLastUpdate();
        
        if( ! is_object( $historic ) )
        {
            $this->get('session')->setFlash('alert alert-error', 'Impossible de trouver la dernière mise à jour de ligue');
            return new RedirectResponse($this->generateUrl('ks_user_communityDynamicList', array("userId" => $user->getId())));
        }
        
        //$community = $userRep->getCommunityOf($user);
        
        //$leagueUpdates = $leagueUpdateRep->findByHistoric( $historic->getId() );
        $leagueUpdates = $leagueUpdateRep->findByHistoricAndCommunity( $historic, $user );
        
        
        //var_dump($usersOfMyCommunityInCategory);

        return $this->render('KsLeagueBundle:League:lastUpdate.html.twig', array(
            'historic'           => $historic,   
            'leagueUpdates'      => $leagueUpdates,
        ));
    }*/
    
    /**
     * @Route("/communityGraph/{userId}", name = "ksLeague_communityGraph" )
     */
    public function communityGraphAction($userId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $userRep            = $em->getRepository('KsUserBundle:User');
        $leagueLevelRep     = $em->getRepository('KsLeagueBundle:LeagueLevel');
        
        $user = $userRep->find($userId);
        
        if (!is_object($user))
        {
            throw new AccessDeniedException('Impossible de récupérer l\'utilisateur ' . $userId .".");
        }
        
        $leagueLevels = $leagueLevelRep->findBy(array(), array('rank' => 'desc'));
        $usersOfMyCommunity = $userRep->getCommunityOf($user);
        
        $myCommunityIds = array();
       
        foreach( $usersOfMyCommunity as $userOfMyCommunity ) {
            $myCommunityIds[] = $userOfMyCommunity->getId();
        }
        $nbUsersOfMyCommunityByLeagueLevels = $leagueLevelRep->findNbUsersOfMyCommunityByLeagueLevels($myCommunityIds);
     
        $nbUsers = array();
        foreach($leagueLevels as $leagueLevel) {
            //if( !in_array($leagueLevel->getLabel(), array("nothing", "chocolate"))) {
                $exist = false;
                foreach($nbUsersOfMyCommunityByLeagueLevels as $nbUsersOfMyCommunity) {
                    $id = $nbUsersOfMyCommunity['id'];

                    if($leagueLevel->getId() == $id) {
                        $exist = $nbUsersOfMyCommunity['nb'];
                        break;
                    }
                }

                if ( $exist !== false ) {
                    $nbUsers[$leagueLevel->getId()] = $exist;
                } else {
                    $nbUsers[$leagueLevel->getId()] = 0;
                }
            //}
        }

        return $this->render('KsLeagueBundle:League:_leagueGraph.html.twig', array(
            'leagueLevels'              => $leagueLevels,
            'nbUsersByLeagueLevels'     => $nbUsers,
        ));
    }
    
    /**
     * @Route("/sportifsGraph", name = "ksLeague_sportifsGraph" )
     */
    public function sportifsGraphAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $userRep            = $em->getRepository('KsUserBundle:User');
        $leagueLevelRep     = $em->getRepository('KsLeagueBundle:LeagueLevel');
        
        
        $leagueLevels = $leagueLevelRep->findBy(array(), array('rank' => 'desc'));
      
        $users = $userRep->findUsers(array(), $this->get('translator'));
        
        $usersIds = array();
       
        foreach( $users as $u ) {
            $usersIds[] = $u["id"];
        }
        $nbUsersByLeagueLevels = $leagueLevelRep->findNbUsersOfMyCommunityByLeagueLevels($usersIds);
     
        $nbUsers = array();
        foreach($leagueLevels as $leagueLevel) {
            //if( !in_array($leagueLevel->getLabel(), array("nothing", "chocolate"))) {
                $exist = false;
                foreach($nbUsersByLeagueLevels as $nbUsersOfMyCommunity) {
                    $id = $nbUsersOfMyCommunity['id'];

                    if($leagueLevel->getId() == $id) {
                        $exist = $nbUsersOfMyCommunity['nb'];
                        break;
                    }
                }

                if ( $exist !== false ) {
                    $nbUsers[$leagueLevel->getId()] = $exist;
                } else {
                    $nbUsers[$leagueLevel->getId()] = 0;
                }
            //}
        }

        return $this->render('KsLeagueBundle:League:_leagueGraph.html.twig', array(
            'leagueLevels'              => $leagueLevels,
            'nbUsersByLeagueLevels'     => $nbUsers,
        ));
    }
    
    /**
     * @Route("/clubMembersGraph/{clubId}", name = "ksLeague_clubMembersGraph" )
     */
    public function clubMembersGraphAction($clubId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $leagueLevelRep     = $em->getRepository('KsLeagueBundle:LeagueLevel');
        $clubRep    = $em->getRepository('KsClubBundle:Club');
        
        $leagueLevels = $leagueLevelRep->findBy(array(), array('rank' => 'desc'));
      
        $club = $clubRep->find( $clubId );
        
        if (! $club ) {
            throw $this->createNotFoundException('Unable to find Club entity.');
        }
        
        $usersIds = $clubRep->findMembersIds($clubId);
        
        $nbUsersByLeagueLevels = $leagueLevelRep->findNbUsersOfMyCommunityByLeagueLevels($usersIds);
     
        $nbUsers = array();
        foreach($leagueLevels as $leagueLevel) {
            //if( !in_array($leagueLevel->getLabel(), array("nothing", "chocolate"))) {
                $exist = false;
                foreach($nbUsersByLeagueLevels as $nbUsersOfMyCommunity) {
                    $id = $nbUsersOfMyCommunity['id'];

                    if($leagueLevel->getId() == $id) {
                        $exist = $nbUsersOfMyCommunity['nb'];
                        break;
                    }
                }

                if ( $exist !== false ) {
                    $nbUsers[$leagueLevel->getId()] = $exist;
                } else {
                    $nbUsers[$leagueLevel->getId()] = 0;
                }
            //}
        }

        return $this->render('KsLeagueBundle:League:_leagueGraph.html.twig', array(
            'leagueLevels'              => $leagueLevels,
            'nbUsersByLeagueLevels'     => $nbUsers,
        ));
    }
    
    /**
     * @Route("/ranking", name = "ksLeague_ranking", options={"expose"=true} )
     * @Template()
     */
    public function rankingAction()
    {
        $securityContext    = $this->container->get('security.context');
        $em                 = $this->getDoctrine()->getEntityManager();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $leagueCatRep       = $em->getRepository('KsLeagueBundle:LeagueCategory');
        $sportRep           = $em->getRepository('KsActivityBundle:Sport');
        
        ini_set('memory_limit', '1024M');
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'ranking');
        
        $user = null;
        
        $month = 'm';
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $userId = $this->get('security.context')->getToken()->getUser()->getId();
        }
        else {
            $userId = 1;
        }
        $user = $userRep->findOneUser(array(
            "userId"            => $userId,
            "withPoints"        => true,
            "activitiesStartOn" => date("Y-$month-01"),
            "activitiesEndOn"   => date("Y-$month-t")
        ), $this->get('translator'));
        
        $leaguesUsers = array();
        $stepPoints = array();
        
        //Récupération des leagues
        $leaguesCategories = $leagueCatRep->findLeaguesUpdatables();
        
        foreach( $leaguesCategories as $leagueCategory ) {
            //Récupération plus d'informations sur les utilisateurs (les points nous interessent)
            $users      = $userRep->findUsers(array(
                "withPoints"            => true,
                "leagueCategoryId"      => $leagueCategory["id"],
                "activitiesStartOn"     => date("Y-$month-01"),
                "activitiesEndOn"       => date("Y-$month-t")
            ), $this->get('translator'));

            //Tri par points décroissants
            usort( $users, array( "Ks\UserBundle\Entity\UserRepository", "orderUsersByPointsDesc" ) );
            
            $leaguesUsers[$leagueCategory["label"]] = $users;
            
            if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
                if( $user["leagueCategoryId"] == $leagueCategory["id"] ) {
                    if( $user["leagueLevelStarNumber"] != 3 ) {
                       $stepPoints["3*"] = $leagueCatRep->findWorsePointsByLeagueAndStars( $leagueCategory["id"], 3, $this->get('translator'));
                    }


                    if( $user["leagueLevelStarNumber"] != 2 ) {
                        if( $user["leagueLevelStarNumber"] > 2 ) {
                            $stepPoints["2*"] = $leagueCatRep->findBestPointsByLeagueAndStars( $leagueCategory["id"], 2, $this->get('translator'));
                        } else {
                            $stepPoints["2*"] = $leagueCatRep->findWorsePointsByLeagueAndStars( $leagueCategory["id"], 2, $this->get('translator'));
                        }
                    }


                    if( $user["leagueLevelStarNumber"] != 1 ) {
                        if( $user["leagueLevelStarNumber"] > 1 ) {
                            $stepPoints["1*"] = $leagueCatRep->findBestPointsByLeagueAndStars( $leagueCategory["id"], 1, $this->get('translator'));
                        } else {
                            $stepPoints["1*"] = $leagueCatRep->findWorsePointsByLeagueAndStars( $leagueCategory["id"], 1, $this->get('translator'));
                        }
                    }

                    if( $user["leagueLevelStarNumber"] != 0 )
                        $stepPoints["0*"] = $leagueCatRep->findBestPointsByLeagueAndStars( $leagueCategory["id"], 0, $this->get('translator'));
                }
            }
       }
       
        //Cas particulier pour la ligue Nutella
//        $leaguesUsers["nutella"] = $userRep->findUsers(array(
//            "withPoints"            => true,
//            "leagueLevelLabel"      => "chocolate",
//            "activitiesStartOn"     => date("Y-$month-01"),
//            "activitiesEndOn"       => date("Y-$month-t")
//        ), $this->get('translator'));
       
        //Filtres
//        $countryForm  = $this->createFormBuilder()->add('country', 'entity', array(   
//            'class' => 'Ks\EventBundle\Entity\Place',
//            'property' => 'country_code',
//            'empty_value' => 'PAYS',
//            'empty_data'  => -1,
//            'required' => false,
//            'query_builder' => function(\Ks\EventBundle\Entity\PlaceRepository $pr) {
//                return $pr->findCountryQB();
//            }
//        ))->getForm();
//        
//        $regionForm  = $this->createFormBuilder()->add('region', 'entity', array(   
//            'class' => 'Ks\EventBundle\Entity\Place',
//            'property' => 'country_area',
//            'empty_value' => 'REGION',
//            'empty_data'  => -1,
//            'required' => false,
//            'query_builder' => function(\Ks\EventBundle\Entity\PlaceRepository $pr) {
//                return $pr->findRegionQB();
//            }
//        ))->getForm();
//        
//        $townForm  = $this->createFormBuilder()->add('town', 'entity', array(   
//            'class' => 'Ks\EventBundle\Entity\Place',
//            'property' => 'town',
//            'empty_value' => 'VILLE',
//            'empty_data'  => -1,
//            'required' => false,
//            'query_builder' => function(\Ks\EventBundle\Entity\PlaceRepository $pr) {
//                return $pr->findTownQB();
//            }
//        ))->getForm();
        
        //Récupération des classements par sport de l'utilisateur (sauf si user keepinsport = visitor)
        $sportsUsers = null;
        $sportsUsersByCountry = null;
        if ($userId != 1) {
            $sports = $userRep->findMySports($userId);

            foreach( $sports as $sport ) {
                //Récupération plus d'informations sur les utilisateurs (les points nous interessent)
                $users      = $userRep->findUsers(array(
                    "withPoints"            => true,
                    "usersWith0points"      => false,
                    "sportId"               => $sport["id"],
                    "sports"                => $sportRep->find($sport["id"]),
                    "activitiesStartOn"     => date("Y-$month-01"),
                    "activitiesEndOn"       => date("Y-$month-t")
                ), $this->get('translator'));
                
                //Tri par points décroissants
                usort( $users, array( "Ks\UserBundle\Entity\UserRepository", "orderUsersByPointsDesc" ) );

                $sportsUsers[$sport["codeSport"]] = $users;
            }
            
            //Récupération des classements par sport de l'utilisateur selon son pays d'origine
            foreach( $sports as $sport ) {
                //Récupération plus d'informations sur les utilisateurs (les points nous interessent)
                $users      = $userRep->findUsers(array(
                    "withPoints"            => true,
                    "usersWith0points"      => false,
                    "sportId"               => $sport["id"],
                    "countryCode"           => $user["country_code"],
                    "activitiesStartOn"     => date("Y-$month-01"),
                    "activitiesEndOn"       => date("Y-$month-t")
                ), $this->get('translator'));

                //Tri par points décroissants
                usort( $users, array( "Ks\UserBundle\Entity\UserRepository", "orderUsersByPointsDesc" ) );

                $sportsUsersByCountry[$sport["codeSport"]] = $users;
            }
        }
        
        return array(
            'user'                  => $user,
            'leaguesUsers'          => $leaguesUsers,
            'sportsUsers'           => $sportsUsers,
            'sportsUsersByCountry'  => $sportsUsersByCountry,
            'country'               => $user["country_code"],
            'stepPoints'            => $stepPoints,
//            'countryForm'       => $countryForm->createView(),
//            'regionForm'        => $regionForm->createView(),
//            'townForm'          => $townForm->createView()
        );
    }
    
 
}
