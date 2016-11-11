<?php

namespace Ks\LeagueBundle\Service;

use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ks\ActivityBundle\Activity;
use Ks\NotificationBundle\Service\NotificationService;

/**
 * Description of Runkeeper
 *
 * @author Clem
 */
class LeagueLevelService
    extends \Twig_Extension
{
    protected $doctrine;
    protected $notificationService;
    protected $translator;
    
    /**
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine, NotificationService $notificationService, $translator)
    {
        $this->doctrine = $doctrine;
        $this->notificationService = $notificationService;
        $this->translator = $translator;
    }
    
    /**
     * 
     * @return string
     */
    public function getName()
    {
        return 'LeagueLevelService';
    }

    
    /**
     * 
     * @param \DateTime $beginDateTime
     * @return string
     * @throws AccessDeniedException
     */
    public function weeklyUpdate( $withNotifs = true )
    {
        $em                 = $this->doctrine->getEntityManager();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $activitySessionRep = $em->getRepository('KsActivityBundle:ActivitySession');
        $leagueCatRep       = $em->getRepository('KsLeagueBundle:LeagueCategory');
        $leagueLevelRep     = $em->getRepository('KsLeagueBundle:LeagueLevel');
        $leagueCategories   = $leagueCatRep->findAll();       
        //$weekNumber         = date('W');
        
        $treatedUsersId = array();
        
        $debug = true;
        
        if( $debug ) echo "maj hebdomadaire - " . date("d/m/Y")."\n\n";
        
        $userPointsStmt = $activityRep->getPreparedStatementForUserPoints(true);
        
        foreach ( $leagueCategories as $leagueCategory ) {
            
            //Les sportifs dans la ligue chocolat n'évoluent pas
            if ( $leagueCategory->getLabel() != "other" ) {
                
                if( $debug ) $this->drawConsoleHead($leagueCategory->getLabel());
            
                $usersOfThisCategory    = $leagueLevelRep->findUsersByCategory($leagueCategory);
                $levelsOfThisCategory   = $leagueLevelRep->findOneByCategory($leagueCategory->getId());

                $usersPoints = array();

                //On calcule les points que chaque utilisateur a gagné sur la semaine et on les place dans un tableau
                foreach( $usersOfThisCategory as $user ) {
                    //$activitiesSessions = $activitySessionRep->findUserActivitiesSessionsByWeek($user, $weekNumber);
                    //$activitiesSessions = $activitySessionRep->findUserActivitiesSessionsSinceDate($user, $beginDateTime);
                   
                    $userPointsStmt->execute(array(
                        'userId'    => $user->getId(),
                        'startOn'   => date('Y-m-01')
                    ));
                    
                    //$sumOfTheEarnedPoints = $activitySessionRep->sumOfTheEarnedPoints($activitiesSessions);
                    $userPoints = $userPointsStmt->fetchColumn();
                    if( $debug ) {
                        echo $user->getUsername()." => " .$userPoints."\n";
                    }
                    $usersPoints[] = array( "user" => $user->getId(), "points" => $userPoints );
                }

                //On tri le tableau d'utilisateurs par points décroissants
                //array_multisort() nécessite un tableau de colonnes, donc nous utilisons le code suivant pour obtenir les colonnes et ainsi effectuer le tri.
                //Obtient une liste de colonnes
                $users = $points = array();
                foreach ($usersPoints as $key => $row) {
                    $users[$key]    = $row['user'];
                    $points[$key]   = $row['points'];
                }
                
                // Trie les données par points décroissant, user croissant
                // Ajoute $usersPoints en tant que dernier paramètre, pour trier par la clé commune
                array_multisort($points, SORT_DESC, $users, SORT_ASC, $usersPoints);
                //$usersPoints = aksort( $usersPoints, true );
                //
                //var_dump($usersPoints);
                //On inverse le tableau pour obtenir un classement virtuel décroissant
                $usersPointsSorted = array_reverse($usersPoints);
                
                
                //var_dump($usersPointsSorted);
                //La category est partagé en 4 en fonction du nombre d'utilisateurs dans cette catégorie
                $nbUsersInCategory = $leagueLevelRep->findNbUsersByCategory( $leagueCategory );
                
                //S'il n'y a pas d'utilisateur dans cette catégory, on passe à la catégory suivante
                if ( $nbUsersInCategory > 0 ) {

                    //On arrondi à l'entier inférieur. Le niveau 0 étoiles aura contiendra plus d'utilisateurs
                    $nbUsersInEachLevel = floor( $nbUsersInCategory / 4 );

                    $nbStartLevel   = $nbUsersInCategory -1;
                    $nbEndLevel     = $nbStartLevel - $nbUsersInEachLevel;
                    
                    if( $debug ) {
                        echo "nbUsersInCategory => " .$nbUsersInCategory."\n";
                        echo "nbUsersInEachLevel => " .$nbUsersInEachLevel."\n";

                        echo "\n3 etoiles\n";
                        echo "nbStartLevel => " .$nbStartLevel."\n";
                        echo "nbEndLevel => " .$nbEndLevel."\n";
                        echo "Utilisateurs : ";
                    }
                    //Ces utilisateurs sont au niveau 3 étoiles, on les fait monter de catégorie
                    for ( $i = $nbStartLevel ; $i > $nbEndLevel ; $i-- ) {
                        $user               = $userRep->find($usersPointsSorted[$i]["user"]);
                        $leagueCategoryId   = $leagueCategory->getId();
                        $starNumber         = 3;
                        
                        if( ! in_array( $user->getId(), array_keys( $treatedUsersId ) ) ) {
                        
                            if( $debug ) echo $user->getId()."|";

                            $leagueLevel = $leagueLevelRep->findOneBy(array(
                                "category"      => $leagueCategoryId,
                                "starNumber"    => $starNumber)
                            );

                            if (!is_object($leagueLevel) ) {
                                throw new AccessDeniedException("Impossible de trouver le niveau de ligue " . $starNumber . " étoiles et de category ". $leagueCategoryId .".");
                            }
                            
                            $treatedUsersId[$user->getId()]["user"] = $user;
                            $treatedUsersId[$user->getId()]["oldLeague"] = $user->getLeagueLevel();

                            $resultUpdate = $leagueLevelRep->updateUserLeagueLevel( $leagueLevel, $user );
                            if( $withNotifs ) $this->sendNotificationWithUpdateLeagueLevel( $leagueLevel, $user, $resultUpdate );
                            
                            $treatedUsersId[$user->getId()]["newLeague"] = $user->getLeagueLevel();
                        }

                        #$actualRank = $user->getLeagueLevel()->getRank();
                        #$leagueLevel = $leagueLevelRep->getLeagueLevelSup($actualRank);

                        #if (!is_object($leagueLevel) ) {
                        #    throw new AccessDeniedException("Impossible de trouver le niveau de ligue de rang ". $actualRank .".");
                        #}

                    }

                    $nbStartLevel   = $nbStartLevel - $nbUsersInEachLevel;
                    $nbEndLevel     = $nbEndLevel - $nbUsersInEachLevel;
    
                    if( $debug ) {
                        echo "\n\n2 etoiles\n";
                        echo "nbStartLevel => " .$nbStartLevel."\n";
                        echo "nbEndLevel => " .$nbEndLevel."\n";
                        echo "Utilisateurs : ";
                    }
                    
                    //Catégorie 2 étoiles
                    for ( $i = $nbStartLevel ; $i > $nbEndLevel ; $i-- ) {
                        $user               = $userRep->find($usersPointsSorted[$i]["user"]);
                        $leagueCategoryId   = $leagueCategory->getId();
                        $starNumber         = 2;
                        
                        if( ! in_array( $user->getId(), array_keys( $treatedUsersId ) ) ) {
                        
                            if( $debug ) echo $user->getId()."|";

                            $leagueLevel = $leagueLevelRep->findOneBy(array(
                                "category"      => $leagueCategoryId,
                                "starNumber"    => $starNumber)
                            );

                            if (!is_object($leagueLevel) ) {
                                throw new AccessDeniedException("Impossible de trouver le niveau de ligue " . $starNumber . " étoiles et de category ". $leagueCategoryId .".");
                            }

                            $treatedUsersId[$user->getId()]["user"] = $user;
                            $treatedUsersId[$user->getId()]["oldLeague"] = $user->getLeagueLevel();

                            $resultUpdate = $leagueLevelRep->updateUserLeagueLevel( $leagueLevel, $user );
                            if( $withNotifs ) $this->sendNotificationWithUpdateLeagueLevel( $leagueLevel, $user, $resultUpdate );
                            
                            $treatedUsersId[$user->getId()]["newLeague"] = $user->getLeagueLevel();
                        }
                    }

                    $nbStartLevel   = $nbStartLevel - $nbUsersInEachLevel;
                    $nbEndLevel     = $nbEndLevel - $nbUsersInEachLevel;
        
                    if( $debug ) {
                        echo "\n\n1 etoile\n";
                        echo "nbStartLevel => " .$nbStartLevel."\n";
                        echo "nbEndLevel => " .$nbEndLevel."\n";
                        echo "Utilisateurs : ";
                    }
                    
                    //Catégorie 1 étoiles
                    for ( $i = $nbStartLevel ; $i > $nbEndLevel ; $i-- ) {
                        $user               = $userRep->find($usersPointsSorted[$i]["user"]);
                        $leagueCategoryId   = $leagueCategory->getId();
                        $starNumber         = 1;
                        
                        if( ! in_array( $user->getId(), array_keys( $treatedUsersId ) ) ) {
                        
                            if( $debug ) echo $user->getId()."|";

                            $leagueLevel = $leagueLevelRep->findOneBy(array(
                                "category"      => $leagueCategoryId,
                                "starNumber"    => $starNumber)
                            );

                            if (!is_object($leagueLevel) ) {
                                throw new AccessDeniedException("Impossible de trouver le niveau de ligue " . $starNumber . " étoiles et de category ". $leagueCategoryId .".");
                            }

                            $treatedUsersId[$user->getId()]["user"] = $user;
                            $treatedUsersId[$user->getId()]["oldLeague"] = $user->getLeagueLevel();

                            $resultUpdate = $leagueLevelRep->updateUserLeagueLevel( $leagueLevel, $user );
                            if( $withNotifs ) $this->sendNotificationWithUpdateLeagueLevel( $leagueLevel, $user, $resultUpdate );
                            
                            $treatedUsersId[$user->getId()]["newLeague"] = $user->getLeagueLevel();
                        }
                    }

                    $nbStartLevel   = $nbStartLevel - $nbUsersInEachLevel;
                    $nbEndLevel     = 0;
    
                    if( $debug ) {
                        echo "\n\n0 etoiles\n";
                        echo "nbStartLevel => " .$nbStartLevel."\n";
                        echo "nbEndLevel => " .$nbEndLevel."\n";
                        echo "Utilisateurs : ";
                    }
                    
                    //Catégorie 0 étoiles, on fait descendre les utilisateurts
                    for ( $i = $nbStartLevel ; $i >= $nbEndLevel ; $i-- ) {
                        $user               = $userRep->find($usersPointsSorted[$i]["user"]);
                        $leagueCategoryId   = $leagueCategory->getId();
                        $starNumber         = 0;
                        
                        if( ! in_array( $user->getId(), array_keys( $treatedUsersId ) ) ) {
                            if( $debug ) echo $user->getId()."|";

                            $leagueLevel = $leagueLevelRep->findOneBy(array(
                                "category"      => $leagueCategoryId,
                                "starNumber"    => $starNumber)
                            );

                            if (!is_object($leagueLevel) ) {
                                throw new AccessDeniedException("Impossible de trouver le niveau de ligue " . $starNumber . " étoiles et de category ". $leagueCategoryId .".");
                            }

                            $treatedUsersId[$user->getId()]["user"] = $user;
                            $treatedUsersId[$user->getId()]["oldLeague"] = $user->getLeagueLevel();

                            $resultUpdate = $leagueLevelRep->updateUserLeagueLevel( $leagueLevel, $user );
                            if( $withNotifs ) $this->sendNotificationWithUpdateLeagueLevel( $leagueLevel, $user, $resultUpdate );
                            
                            $treatedUsersId[$user->getId()]["newLeague"] = $user->getLeagueLevel();
                        }
                    }
                    
                    if( $debug ) echo "\n\n";
                }
            } else {
                if( $debug ) $this->drawConsoleHead($leagueCategory->getLabel());
            
                $usersOfThisCategory    = $leagueLevelRep->findUsersByCategory($leagueCategory);
                foreach( $usersOfThisCategory as $user ) {
                    if( ! in_array( $user->getId(), array_keys( $treatedUsersId ) ) ) {
                        
                        if( $debug ) echo $user->getId()."|";

                        $treatedUsersId[$user->getId()]["user"] = $user;
                        $treatedUsersId[$user->getId()]["oldLeague"] = $user->getLeagueLevel();
                        
                        if( $withNotifs ) $this->sendNotificationWithUpdateLeagueLevel( $leagueLevel, $user, "stable" );

                        $treatedUsersId[$user->getId()]["newLeague"] = $user->getLeagueLevel();
                    }
                }
            }
        }
        
        $this->saveHistoric( $treatedUsersId );

        echo "MAJ WEEKLY OK !";
    }
    

    /* public function seasonUpdateOld()
    {
        $em                 = $this->doctrine->getEntityManager();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $activitySessionRep = $em->getRepository('KsActivityBundle:ActivitySession');
        $leagueCatRep       = $em->getRepository('KsLeagueBundle:LeagueCategory');
        $leagueLevelRep     = $em->getRepository('KsLeagueBundle:LeagueLevel');
        $leagueCategories   = $leagueCatRep->findAll();  
        
        $treatedUsersId = array();
        
        $debug = true;
        
        if( $debug ) echo "maj de fin de saison - " . date("d/m/Y")."\n\n";
        $this->weeklyUpdate( false );
        
        foreach ( $leagueCategories as $leagueCategory ) {
            
            //Les sportifs dans la ligue chocolat n'évoluent pas
            if ( $leagueCategory->getLabel() != "other" ) {
                
                if( $debug ) $this->drawConsoleHead($leagueCategory->getLabel());
            
                $levels3StarsOfThisCategory   = $leagueLevelRep->findBy(array(
                    "category" => $leagueCategory->getId(),
                    "starNumber" => 3
                ));

                foreach( $levels3StarsOfThisCategory as $leagueLevel ) {
                    $usersOfThisLevel    = $userRep->findByLeagueLevel($leagueLevel->getId());
                    
                    //Il ne faut pas traiter les utilisateurs qui viennent juste de descendre
                    $nbUsersOfThisLevel = 0;
                    foreach ( $usersOfThisLevel as $user ) {
                        if( ! in_array( $user->getId(), array_keys( $treatedUsersId ) ) ) {
                            $nbUsersOfThisLevel += 1;
                        }
                    }
                    if( $debug ) echo "Nombre utilisateurs 3 etoiles => " .$nbUsersOfThisLevel."\n";

                    $nbUserUpgrade = 0;
                    if( $debug ) echo "Utilisateurs : ";
                    
                    foreach ( $usersOfThisLevel as $user ) {
                        if( ! in_array( $user->getId(), array_keys( $treatedUsersId ) ) ) {
                            if( $debug ) echo $user->getId() . "|";

                            $actualRank = $leagueLevel->getRank();
                            if( $actualRank > 1 ) {
                                $newRank = $actualRank - 1;
                            } else {
                                $newRank = $actualRank;
                            }
                            $leagueLevelUp = $leagueLevelRep->findOneByRank( $newRank );

                            if (!is_object( $leagueLevelUp ) ) {
                                throw new AccessDeniedException("Impossible de trouver le niveau de rank " . $newRank . ".");
                            }
                            
                            $treatedUsersId[$user->getId()]["user"] = $user;
                            $treatedUsersId[$user->getId()]["oldLeague"] = $user->getLeagueLevel();

                            $resultUpdate = $leagueLevelRep->updateUserLeagueLevel( $leagueLevelUp, $user );
                            $this->sendNotificationWithUpdateLeagueLevel( $leagueLevelUp, $user, $resultUpdate );
                            $nbUserUpgrade += 1;
                            
                            $treatedUsersId[$user->getId()]["newLeague"] = $user->getLeagueLevel();
                        }
                    }
                    
                    if( $debug ) echo "\n";
                    if( $debug ) echo "Nombre utilisateurs ayant augmente de ligue => " . $nbUserUpgrade ."\n";
                }
                
                $lowestRank = $leagueLevelRep->getLowestRank();           

                
                $levelsOfThisCategory = $leagueLevelRep->findByCategory( $leagueCategory->getId() );
                 
                if( $debug ) echo "\n";
                 
                foreach( $levelsOfThisCategory as $leagueLevel ) {
                    $usersOfThisLevel    = $userRep->findByLeagueLevel($leagueLevel->getId());
                    
                    //Il ne faut pas traiter les utilisateurs qui viennent juste de monter
                    $nbUsersOfThisLevel = 0;
                    foreach ( $usersOfThisLevel as $user ) {
                        if( ! in_array( $user->getId(), array_keys( $treatedUsersId ) ) ) {
                            $nbUsersOfThisLevel += 1;
                        }
                    }
                    if( $debug ) echo "Nombre utilisateurs 0 etoiles => " .$nbUsersOfThisLevel."\n";
                    
                    $nbUserDowngrade = 0;
                    if( $debug ) echo "Utilisateurs : ";

                    foreach ( $usersOfThisLevel as $user ) {
                        if( ! in_array( $user->getId(), array_keys( $treatedUsersId ) ) ) {
                            if( $debug ) echo $user->getId() . "|";

                            $actualRank = $leagueLevel->getRank();

                            if( $actualRank < ( $lowestRank - 2 ) ) {
                                $newRank = $leagueLevelRep->getInfRankWith0star( $actualRank );
                            } else {
                                $newRank = $actualRank;
                            }
                            $leagueLevelDown = $leagueLevelRep->findOneByRank( $newRank );

                            if (!is_object( $leagueLevelDown ) ) {
                                throw new AccessDeniedException("Impossible de trouver le niveau de rank " . $newRank . ".");
                            }
                            
                            $treatedUsersId[$user->getId()]["user"] = $user;
                            $treatedUsersId[$user->getId()]["oldLeague"] = $user->getLeagueLevel();

                            $resultUpdate = $leagueLevelRep->updateUserLeagueLevel( $leagueLevelDown, $user );
                            $this->sendNotificationWithUpdateLeagueLevel( $leagueLevelDown, $user, $resultUpdate );
                            $nbUserDowngrade += 1;

                            $treatedUsersId[$user->getId()]["newLeague"] = $user->getLeagueLevel();
                        }
                    }
                    
                    if( $debug ) echo "\n";
                    if( $debug ) echo "Nombre utilisateurs ayant baisse de ligue => " . $nbUserDowngrade ."\n";
                }
            }
        }
        
        $this->saveHistoric( $treatedUsersId, false );
        
        echo "MAJ SEASON OK !";
    }
    
    private function saveHistoric( $treatedUsersId, $isWeeklyUpdate = true) {
        $em                 = $this->doctrine->getEntityManager();
        $userRep            = $em->getRepository('KsUserBundle:User');
        
        $historic = new \Ks\LeagueBundle\Entity\Historic();
        $historic->setIsSeasonUpdate( ! $isWeeklyUpdate );
        $historic->setIsWeeklyUpdate( $isWeeklyUpdate );
        
        $em->persist( $historic );
        $em->flush();
        
        foreach( $treatedUsersId as $userId => $treatedUser ) {
            $user = $userRep->find( $userId );
            
            if( is_object( $user ) ) {
                $leagueUpdate = new \Ks\LeagueBundle\Entity\LeagueUpdate( $historic, $user );
                $leagueUpdate->setOldLeague( $treatedUser["oldLeague"] );
                $leagueUpdate->setNewLeague( $treatedUser["newLeague"] );
                
                $em->persist( $leagueUpdate );
                
                $historic->addLeagueUpdate($leagueUpdate);
            } else {
                echo "ERROR : Impossible de trouver l'utilisateur " . $userId.".\n";
            }
            
        }
        
        $em->persist( $historic );
        $em->flush();
    }*/
    
    
    
    /**
     * 
     * @param type $activitySession
     * @param type $user
     * @return string
     * @throws AccessDeniedException
     * @throws type
     */
    public function activitySessionEarnPoints($activitySession, $user)
    {
        $em             = $this->doctrine->getEntityManager();
        $pointsRep      = $em->getRepository('KsActivityBundle:ActivitySessionEarnsPoints');
        $leagueLevelRep = $em->getRepository('KsLeagueBundle:LeagueLevel');
        $points         = 0;
        $coef           = 1.0;
        $earnsPointsReturn = array();
        
        $sexeLabel = "Masculin";
        $userDetail = $user->getUserDetail();
        if( $userDetail != null ) {
            if ( $userDetail->getWeight() != null ) {
                $weight = $userDetail->getWeight();
            }
            
            $sexe = $userDetail->getSexe();
            if( $sexe != null && $sexe->getNom() == "Feminin" )
                $sexeLabel = $sexe->getNom();
        }
        if( !isset( $weight )) {
            if( $sexeLabel == "Feminin" ) $weight = 50;
            else $weight = 70;
        }
        
        $duration = $activitySession->getDuration();
        
        if (!$duration) {
            $nbMinutes  = 0;
            $nbHours    = 0.0;
        } else {
            $hours      = $duration->format('H');
            $minutes    = $duration->format('i');
            $seconds    = $duration->format('s');
            $nbMinutes  = $hours * 60 + $minutes;
            $nbHours    = (float)($hours + $minutes /60 + $seconds / 3600);
        }
        
        //if( $nbHours == 0 ) $nbHours = 0.15;
        
        if ($activitySession->getWasOfficial() == "1") {
            $coef *= 1.2;
        }
        
        //Gestion de l'effort
        if ($activitySession->getIntensity() != null) {
            switch ($activitySession->getIntensity()->getCode()) {
                case "low":
                    $coef *= 0.9;
                    break;
                
                case "medium":
                    break;
                
                case "high":
                    $coef *= 1.1;
                    break;
            }
        }
        
        $pointsForOneHour = 500;
        switch( $activitySession->getSport()->getCodeSport()) {
            /* Sports collectifs */
            case "football":
            case "handball":
            case "basketball":
            case "rugby":
            case "waterPolo":
            case "volley":
                $pointsForOneHour = 700;
                break;
                
            /* Sports de raquette */
            case "tennis":
            case "tennis-table":
            case "badminton":
            case "peloteBasque":
            case "squash":
                $pointsForOneHour = 600;
                break;
              
            /* sports de combat */
            case "judo":
            case "karate":
                $pointsForOneHour = 600;
                break;
                
            /* sports de glisse */
            case "canoe-kayak":
            case "voile":
            case "skateboard":
            case "ice_skate":
            case "surf":
            case "windsurfing":
                $pointsForOneHour = 500;
                break;
                
            /* ski */
            case "ski":
            case "crossCountrySkiing":
            case "snowboard":
                $pointsForOneHour = 300;
                break;
                
            case "musculation":
                $pointsForOneHour = 500;
                break;
                
            case "scuba-diving":
            case "golf":
                $pointsForOneHour = 300;
                break;
            
            case "video_games":
                $pointsForOneHour = 200;
                break;
                
            case "yoga":
                $pointsForOneHour = 200;
                break;
            
            /* sports soft */
            case "petanque":
            case "baby-foot":
            case "billard":
                $pointsForOneHour = 100;
                break;
            
            /* accrobranche */
            case "tree_climbing":
                $pointsForOneHour = 200;
                break;
            
            case "empty":
                $pointsForOneHour = 0;
                break;
            
            default:
                $pointsForOneHour = 500;
                break;
        }
        
        switch ($activitySession->getType()) {
            case 'session_team_sport':
                if ( $activitySession->getResult() != null ) {
                    if      ($activitySession->getResult()->getCode() == "v") $coef *= 1.2;
                    elseif  ($activitySession->getResult()->getCode() == "d") $coef *= 0.8;
                }
                
                $points = $pointsForOneHour * $nbMinutes / 60 * $coef;
                break; 
             
            case 'session':
//                if ($activitySession->getCalories() != null) {
//                    $points    = $activitySession->getCalories();
//                } else {
                    
                    $points    = $pointsForOneHour * $nbMinutes / 60 * $coef;
//                }
                break;

            case 'session_endurance':
            case 'session_endurance_on_earth':
                          
                
                $distance   = $activitySession->getDistance() != null       ? (float)$activitySession->getDistance()        : 0;
                $denPlus    = $activitySession->getElevationGain() != null  ? (float)$activitySession->getElevationGain()   : 0;
                $denNeg     = $activitySession->getElevationLost() != null  ? (float)$activitySession->getElevationLost()   : 0;
                
//              FMO : pour permettre à l'utilisateur de modifier à postériori ses données importées par GPX
//                if ($activitySession->getTrackingDatas() != null) {
//                    $trackingDatas = $activitySession->getTrackingDatas(); // NOTE CF: retourne json_decode($trackingDatas, true)
//
//                    if (isset( $trackingDatas["info"]["distance"])) {
//                        $distance = (float)$trackingDatas["info"]["distance"];
//                    }
//                    if (isset( $trackingDatas["info"]["D-"])) {
//                        $denNeg = (int)$trackingDatas["info"]["D-"];
//                    }
//                    if (isset( $trackingDatas["info"]["D+"])) {
//                        $denPlus = (int)$trackingDatas["info"]["D+"];
//                    }
//                }

                //Si l'activité est rentrée à la main et que les calories sont rentrées, elles sont prioritaires sur le calcul des points
//                if ($activitySession->getCalories() != null && $activitySession->getSource() == null ) {
//                    $points     = $activitySession->getCalories();
//                } else {
                    if ( in_array( $activitySession->getSport()->getCodeSport(), array("running", "walking", "hiking" ) ) && $distance > 0 && $nbHours > 0 ) {
                        $points = $weight * ( $distance + $denPlus * 10 / 1000);
                    } elseif ($activitySession->getSport()->getCodeSport() == "cycling" && $distance > 0 && $nbHours > 0) {

                        $averageSpeed   = $distance / $nbHours;
                        $as             = ( $averageSpeed / 3.6 )*( $averageSpeed / 3.6 );
                        $points         = (1/8) * $weight * $nbHours * $as + $weight * $denPlus / 100;
                    }  elseif ($activitySession->getSport()->getCodeSport() == "triathlon" && $distance > 0) {
                        //3% natation, 19% course à pied, et 78% pour le vélo
                        $pointsSwimming = 300 * $distance / $nbHours;
                        
                        $pointsRunning = $weight * ( $distance + $denPlus * 10 / 1000);
                        
                        $averageSpeed   = $distance / $nbHours;
                        $as             = ( $averageSpeed / 3.6 )*( $averageSpeed / 3.6 );
                        $pointsCycling  = (1/8) * $weight * $nbHours * $as + $weight * $denPlus / 100;
                        
                        $points = 3/100*$pointsSwimming + 19/100*$pointsRunning + 78/100*$pointsCycling;
                    }else {
//                        if ($activitySession->getCalories() != null) {
//                            $points     = $activitySession->getCalories();
//                        } else {
                            $points             = $pointsForOneHour * $nbMinutes / 60 * $coef;
//                        }
                    }
//                }
                $points *= $coef;
                break;
                
            case 'session_endurance_under_water':  
                $distance   = $activitySession->getDistance() != null       ? (float)$activitySession->getDistance()        : 0;
                
                if ($activitySession->getSport()->getCodeSport() == "swimming" && $distance > 0 && $nbHours > 0) {
                    $points         = 300 * $distance / $nbHours;
                    //$points         = 500 * $distance;
                } else {
//                    if ($activitySession->getCalories() != null) {
//                        $points     = $activitySession->getCalories();
//                    } else {
                        $points             = $pointsForOneHour * $nbMinutes / 60 * $coef;
//                    }
                }
        }
        
        //if ($points > 0) {
            if ( $pointsRep->hasAlreadyEarnPoints($activitySession, $user) ) {
                $activityPoints = $pointsRep->find(array(
                    "activitySession"   => $activitySession->getId(),
                    "user"              => $user->getId()
                ));
                if( is_object( $activityPoints ) ) {
                    $em->remove( $activityPoints );
                    $em->flush();
                } 
            }
            if ( ! $pointsRep->hasAlreadyEarnPoints($activitySession, $user) ) {
                $earnsPointsReturn["response"] = 1;
                $activitySession->addActivitySessionEarnsPoints($pointsRep->addPointsToTheSession($activitySession, $user, $points));
                
                /* anticipation. N'est pas encore supporté */
                $activitySession->setPointsWon( $points );
                $em->persist($activitySession);
                $em->flush();
        
                $earnsPointsReturn["pointsEarned"] = $points;
                //$earnsPointsReturn["activitySessionEarnedPoints"] = ;

                //Si l'utilisateur publie sa première activité, il entre dans la ligue la plus basse
                /*$lowestRank = $leagueLevelRep->getLowestRank();
                $lowestLeagueLevel = $leagueLevelRep->findOneByRank($lowestRank);
                if (!is_object($lowestLeagueLevel) ) {
                    throw new AccessDeniedException("Impossible de trouver le niveau de ligue le plus bas (rang ". $lowestRank .").");
                }*/
                    
                if ( $user->getLeagueLevel()->getRank() == 13 or $user->getLeagueLevel()->getRank() == 14 ) { //chocolate ou other
                    
                    //$actualRank = $user->getLeagueLevel()->getRank();
                    //$leagueLevel = $leagueLevelRep->getLeagueLevelSup($actualRank - 1);
                    $leagueLevel = $leagueLevelRep->findOneByRank( 12 );

                    if (!is_object($leagueLevel) ) {
                        throw new AccessDeniedException("Impossible de trouver le niveau de ligue de rang 12.");
                    }

                    $resultUpdate = $leagueLevelRep->updateUserLeagueLevel( $leagueLevel, $user );
                    if( $resultUpdate ) $this->sendNotificationWithUpdateLeagueLevel( $leagueLevel, $user );
                } 
                
            } else {
                
                $earnsPointsReturn["response"] = -1;
                $earnsPointsReturn["errorMessage"] = "Cette session à déjà rapporté des points à " . $user->getUsername() . ".";
            }
        /*} else {
            $earnsPointsReturn["response"] = -1;
            $earnsPointsReturn["errorMessage"] = "Impossible de calculer le nombre de points.";
        }*/
        
        return $earnsPointsReturn;
    }
    
    public function sendNotificationWithUpdateLeagueLevel( $leagueLevel, $user, $updateState = "up" ) {        
        switch( $leagueLevel->getCategory()->getLabel() ) {
            case "bronze":
                $categoryName = "BRONZE";
                break;
            case "silver":
                $categoryName = "ARGENT";
                break;
            case "gold":
                $categoryName = "OR";
                break;
            default:
                $categoryName = "BRONZE";
                break;
        }
        
        switch( $leagueLevel->getStarNumber() ) {
            case 0:
                $stars = "";
                break;
            case 1:
                $stars = "★☆☆";
                break;
            case 2:
                $stars = "★★☆";
                break;
            case 3:
                $stars = "★★★";
                break;
            default:
                $stars = "";
                break;
        }
        
        switch ( $updateState ) {
            case "up":
                $message = "Félicitations, tu passes en ligue " . $categoryName. " " . $stars; 
                break;
            case "stable":
                $message = "Félicitations, tu restes en ligue " . $categoryName. " " . $stars; 
                break;
            case "down":
                $message = "Tu passes en ligue " . $categoryName. " " . $stars; 
                break;
            default:
               $message = "Felicitation, tu passes dans la ligue " . $categoryName. " " . $stars; 
        }

        //Création d'une notification
        $notificationType_name = "league";
        
        $this->notificationService->sendNotification(null, $user, $user, $notificationType_name, $message);
    }
    
    /**
     * 
     * @param type $title
     */
    private function drawConsoleHead($title)
    {
        $nbCaract = 30;
        $c = "#";
        echo "\n";
        for($i=1;$i<=$nbCaract;$i++) echo $c;
        echo "\n#";
        for($i=2;$i<=($nbCaract-strlen($title)-1)/2;$i++) echo " ";
        echo " " . $title . " ";
        for($i=2;$i<=($nbCaract-strlen($title)-2)/2;$i++) echo " ";
        echo "#\n";
        for($i=1;$i<=$nbCaract;$i++) echo $c;
        echo "\n\n";
    }
    
    public function leaguesRankingUpdate($month = null)
    {
        $em                 = $this->doctrine->getEntityManager();
        $leagueCatRep       = $em->getRepository('KsLeagueBundle:LeagueCategory');   
        
        if( $month == null ) {
            $month = date("m");
        }
        
        //Récupération des leagues à mettre à jour
        $leaguesCategories = $leagueCatRep->findLeaguesUpdatables();
        
        foreach( $leaguesCategories as $leagueCategory ) {
            $this->leagueRankingUpdate($leagueCategory["id"], $month );
        }
    }
    
    public function leagueRankingUpdate( $leagueCategoryId, $month = null)
    {
        $em                 = $this->doctrine->getEntityManager();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $leagueCatRep       = $em->getRepository('KsLeagueBundle:LeagueCategory');   
        
        if( $month == null ) {
            $month = date("m");
        }
        
        $firstDayOfMonth = date("Y-$month-01"); 
        $lastDayOfMonth = date("Y-$month-t"); 
        
        
        //Récupération des users Ids dans cette ligue
        //$usersIdsInCategory   = $leagueCatRep->findUsersIdsByCategoryId( $leagueCategoryId );
        //var_dump($usersIdsInCategory);
        //if( count( $usersIdsInCategory ) < 1 ) $usersIdsInCategory = array(0);

        //Récupération plus d'informations sur les utilisateurs (les points nous interessent)
        $users      = $userRep->findUsers(array(
            //"usersIds"          => $usersIdsInCategory,
            "leagueCategoryId"  => $leagueCategoryId,
            "withPoints"        => true,
            //"usersWith0points"  => false,
            "activitiesStartOn" => $firstDayOfMonth,
            "activitiesEndOn"   => $lastDayOfMonth
        ), $this->translator);

        //Tri par points décroissants
        usort( $users, array( "Ks\UserBundle\Entity\UserRepository", "orderUsersByPointsDesc" ) );

        //On met à jour l'utilisateur en bdd dans la table "MEMORY"
        foreach( $users as $key => $user ) {
            //Ue fois trié, le rang = clé + 1
            $rank = $key + 1;
            
            $hasPoints = $user["points"] > 0 ? 1 : 0;
            //var_dump($user["id"]." => " .$user["points"]);
            $leagueCatRep->updateUserRank($user["id"], $leagueCategoryId, $rank, $hasPoints);
        }

        //Mise à jour des étoiles 
        $leagueCatRep->updateStars( $leagueCategoryId, $month );
    }
    
    public function seasonUpdate($month = null, $testOnly = false)
    {
        $em                 = $this->doctrine->getEntityManager();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $leagueCatRep       = $em->getRepository('KsLeagueBundle:LeagueCategory');   
        $leagueLevelRep     = $em->getRepository('KsLeagueBundle:LeagueLevel');   
        
        $usersIdsByLeagues = array();
        
        if( $month == null ) {
            $month = date("m");
        }
        
        //FIXME : pble d'encodage pas réussi à afficher correctement les mois en français...
        setlocale (LC_TIME, 'fr_FR.utf8','fra');
        $monthFR = mb_convert_encoding(strftime("%B", strtotime("- 1 month")), 'utf-8');
        if ($monthFR == 'aoÃ»t') $monthFR = 'aout';
        if ($monthFR == 'dÃ©cembre') $monthFR = 'décembre';
        if ($monthFR == 'fÃ©vrier') $monthFR = 'février';
        
        $firstDayOfMonth = date("Y-$month-01"); 
        $lastDayOfMonth = date("Y-$month-t"); 
        
        //echo "Mois du $firstDayOfMonth au $lastDayOfMonth \n\n";
        
        //Les étoiles seront cohérentes quoi qu'il arrive, même si une activité a été posté dans le nouveau mois
        $this->leaguesRankingUpdate($month);
        
        //On garde l'historique des ligues de chaque utilisateur à la fin de chaque saison
        $this->saveHistoric($month, $testOnly);
        
        $notifications = array();
   
        $keepinsportUser = $userRep->findOneByUsername( "keepinsport" );
        
        //Récupération des leagues à mettre à jour
        $leaguesCategories = $leagueCatRep->findLeaguesUpdatables();
        
        foreach( $leaguesCategories as $leagueCategory ) {
            
            //Récupération des utilisateurs :
            //   - avec plus de 0 points
            //   - de la category $leagueCategory["id"]
            //   - entre début du mois précédent et fin du mois précédent
            $users      = $userRep->findUsers(array(
                "withPoints"        => true,
                "usersWith0points"  => false,
                "leagueCategoryId"  => $leagueCategory["id"],
                "activitiesStartOn" => $firstDayOfMonth,
                "activitiesEndOn"   => $lastDayOfMonth
            ), $this->translator);
            
            if( $testOnly ) {
                echo count($users) ." utilisateurs avec des points dans la category " . $leagueCategory["label"]."\n";
            }

            //Tri par points décroissants
            usort( $users, array( "Ks\UserBundle\Entity\UserRepository", "orderUsersByPointsDesc" ) );
            
            //Construction d'un double tableau [League category][Nombre étoiles] 
            foreach( $users as $user ) {
                $usersIdsByLeagues[$leagueCategory["label"]][$user["leagueLevelStarNumber"]."*"][] = $user["id"];
            }
        }
        
        $translator = $this->translator;
        
        //On fait monter les bronze 3* en argent 0*
        if( isset( $usersIdsByLeagues["bronze"]["3*"] ) && is_array( $usersIdsByLeagues["bronze"]["3*"] )) {    
            $silver_leagueLevelId = $leagueLevelRep->findLeagueLevelIdByCategoryLabelAndStars( "silver", 0);
            foreach( $usersIdsByLeagues["bronze"]["3*"] as $userId ) {
                if( $testOnly ) {
                    echo "$userId passe de bronze3* a argent 0*\n";
                } else {
                    $leagueLevelRep->updateLeagueLevel( $userId, $silver_leagueLevelId );
                    
                    $dataForSeasonUpdate = $userRep->getDataForSeasonUpdate($userId, $firstDayOfMonth, $lastDayOfMonth);
                    $message = $translator->trans('mails.league-update1') . " ". $monthFR . " " . $dataForSeasonUpdate[0]['activities'] . " ". $translator->trans('mails.league-update2') . " ". $dataForSeasonUpdate[0]['points'];
                    $message .= " " . $translator->trans('mails.league-update3') . " BRONZE 3 " . $translator->trans('mails.league-update4') . " ARGENT" . $translator->trans('mails.league-update5');
                    //var_dump($message);
                    $notifications[] = array(
                        "fromUser"              => $keepinsportUser,
                        "toUser"                => $userRep->find( $userId ),
                        "message"               => $message
                    );
                }
            }
        }
                
        //On fait monter les argent 3* en or 0*
        if( isset( $usersIdsByLeagues["silver"]["3*"] ) && is_array( $usersIdsByLeagues["silver"]["3*"] )) {
            $gold_leagueLevelId = $leagueLevelRep->findLeagueLevelIdByCategoryLabelAndStars( "gold", 0);
            foreach( $usersIdsByLeagues["silver"]["3*"] as $userId ) {
                if( $testOnly ) {
                    echo "$userId passe de argent 3* a or 0*\n";
                } else {
                    $leagueLevelRep->updateLeagueLevel( $userId, $gold_leagueLevelId );
     
                    $dataForSeasonUpdate = $userRep->getDataForSeasonUpdate($userId, $firstDayOfMonth, $lastDayOfMonth);
                    $message = $translator->trans('mails.league-update1') . " ". $monthFR . " " . $dataForSeasonUpdate[0]['activities'] . " ". $translator->trans('mails.league-update2') . " ". $dataForSeasonUpdate[0]['points'];
                    $message .= " " . $translator->trans('mails.league-update3') . " ARGENT 3 " . $translator->trans('mails.league-update4') . " OR" . $translator->trans('mails.league-update5');
                    //var_dump($message);
                    $notifications[] = array(
                        "fromUser"              => $keepinsportUser,
                        "toUser"                => $userRep->find( $userId ),
                        "message"               => $message
                    );
                }
            }
        }
        
        //On fait descendre les or 0* en argent 0*
        if( isset( $usersIdsByLeagues["gold"]["0*"] ) && is_array( $usersIdsByLeagues["gold"]["0*"] )) {
            $silver_leagueLevelId = $leagueLevelRep->findLeagueLevelIdByCategoryLabelAndStars( "silver", 0);
            foreach( $usersIdsByLeagues["gold"]["0*"] as $userId ) {  
                if( $testOnly ) {
                    echo "$userId passe de or 0* a argent 0*\n";
                } else {
                    $leagueLevelRep->updateLeagueLevel( $userId, $silver_leagueLevelId );
                    
                    $dataForSeasonUpdate = $userRep->getDataForSeasonUpdate($userId, $firstDayOfMonth, $lastDayOfMonth);
                    $message = $translator->trans('mails.league-update1') . " ". $monthFR . " " . $dataForSeasonUpdate[0]['activities'] . " ". $translator->trans('mails.league-update2') . " ". $dataForSeasonUpdate[0]['points'];
                    $message .= " " . $translator->trans('mails.league-update3') . " OR 0 " . $translator->trans('mails.league-update4') . " ARGENT" . $translator->trans('mails.league-update5');
                    //var_dump($message);
                    $notifications[] = array(
                        "fromUser"              => $keepinsportUser,
                        "toUser"                => $userRep->find( $userId ),
                        "message"               => $message
                    );
                }
            }
        }
        
        //On fait descendre les argent 0* en bronze 0*
        if( isset( $usersIdsByLeagues["silver"]["0*"] ) && is_array( $usersIdsByLeagues["silver"]["0*"] )) {   
            $bronze_leagueLevelId = $leagueLevelRep->findLeagueLevelIdByCategoryLabelAndStars( "bronze", 0 );
            foreach( $usersIdsByLeagues["silver"]["0*"] as $userId ) {
                if( $testOnly ) {
                    echo "$userId passe de argent 0* a bronze 0*\n";
                } else {
                    $leagueLevelRep->updateLeagueLevel( $userId, $bronze_leagueLevelId );

                    $dataForSeasonUpdate = $userRep->getDataForSeasonUpdate($userId, $firstDayOfMonth, $lastDayOfMonth);
                    $message = $translator->trans('mails.league-update1') . " ". $monthFR . " " . $dataForSeasonUpdate[0]['activities'] . " ". $translator->trans('mails.league-update2') . " ". $dataForSeasonUpdate[0]['points'];
                    $message .= " " . $translator->trans('mails.league-update3') . " ARGENT 0 " . $translator->trans('mails.league-update4') . " BRONZE" . $translator->trans('mails.league-update5');
                    //var_dump($message);
                    $notifications[] = array(
                        "fromUser"              => $keepinsportUser,
                        "toUser"                => $userRep->find( $userId ),
                        "message"               => $message
                    );
                }
            }
        }
        
        //   /!\  L'ordre est important : Faire descendre dans l'ordre : Bronze; Argent, Or
        
        //On fait descendre les bronze qui n'ont fait aucunes activités en chocolat
        //Récupération des utilisateurs :
        //   - avec 0 points
        //   - de la category bronze
        //   - entre début du mois précédent et fin du mois précédent
        $usersBronze0With0Points      = $userRep->findUsers(array(
            "withPoints"            => true,
            "usersWith0points"      => true,
            "usersWith0PointsOnly"  => true,
            "leagueCategoryLabel"   => "bronze",
            "activitiesStartOn"     => $firstDayOfMonth,
            "activitiesEndOn"       => $lastDayOfMonth
        ), $this->translator);
        
        if( $testOnly ) {
            echo count($usersBronze0With0Points) ." utilisateurs sans points dans la category bronze\n";
        }
        
        $chocolate_leagueLevelId = $leagueLevelRep->findLeagueLevelIdByLeagueLevelLabel( "chocolate" );
        foreach( $usersBronze0With0Points as $user ) {
            if( $testOnly ) {
                echo $user["id"] ." passe de bronze 0* a 'Hors ligues'\n";
            } else {
                $leagueLevelRep->updateLeagueLevel( $user["id"], $chocolate_leagueLevelId );
                
                $dataForSeasonUpdate = $userRep->getDataForSeasonUpdate($user["id"], $firstDayOfMonth, $lastDayOfMonth);
                $message = $translator->trans('mails.league-update1') . " ". $monthFR . " " . $dataForSeasonUpdate[0]['activities'] . " ". $translator->trans('mails.league-update2') . " ". $dataForSeasonUpdate[0]['points'];
                $message .= " " . $translator->trans('mails.league-update3') . " BRONZE 0 " . $translator->trans('mails.league-update4') . " 'Hors ligues'" . $translator->trans('mails.league-update5');
                //var_dump($message);
                $notifications[] = array(
                    "fromUser"              => $keepinsportUser,
                    "toUser"                => $userRep->find( $user["id"] ),
                    "message"               => $message
                );
            }
        }
        
        //On fait descendre les argent qui n'ont fait aucunes activités, en bronze
        //Récupération des utilisateurs :
        //   - avec 0 points
        //   - de la category bronze
        //   - entre début du mois précédent et fin du mois précédent
        $usersSilver0With0Points      = $userRep->findUsers(array(
            "withPoints"            => true,
            "usersWith0points"      => true,
            "usersWith0PointsOnly"  => true,
            "leagueCategoryLabel"   => "silver",
            "activitiesStartOn"     => $firstDayOfMonth,
            "activitiesEndOn"       => $lastDayOfMonth
        ), $this->translator);
        
        if( $testOnly ) {
            echo count($usersSilver0With0Points) ." utilisateurs sans points dans la category silver\n";
        }
        
        $bronze_leagueLevelId = $leagueLevelRep->findLeagueLevelIdByCategoryLabelAndStars( "bronze", 0 );
        foreach( $usersSilver0With0Points as $user ) {
            if( $testOnly ) {
                echo $user["id"] ." passe de argent 0* a bronze\n";
            } else {
                $leagueLevelRep->updateLeagueLevel( $user["id"], $bronze_leagueLevelId );
                
                $dataForSeasonUpdate = $userRep->getDataForSeasonUpdate($user["id"], $firstDayOfMonth, $lastDayOfMonth);
                $message = $translator->trans('mails.league-update1') . " ". $monthFR . " " . $dataForSeasonUpdate[0]['activities'] . " ". $translator->trans('mails.league-update2') . " ". $dataForSeasonUpdate[0]['points'];
                $message .= " " . $translator->trans('mails.league-update3') . " ARGENT 0 " . $translator->trans('mails.league-update4') . " BRONZE" . $translator->trans('mails.league-update5');
                //var_dump($message);
                $notifications[] = array(
                    "fromUser"              => $keepinsportUser,
                    "toUser"                => $userRep->find( $user["id"] ),
                    "message"               => $message
                );
            }
        }
        
        //On fait descendre les or qui n'ont fait aucunes activités, en argent
        //Récupération des utilisateurs :
        //   - avec 0 points
        //   - de la category bronze
        //   - entre début du mois précédent et fin du mois précédent
        $usersGold0With0Points      = $userRep->findUsers(array(
            "withPoints"            => true,
            "usersWith0points"      => true,
            "usersWith0PointsOnly"  => true,
            "leagueCategoryLabel"   => "gold",
            "activitiesStartOn"     => $firstDayOfMonth,
            "activitiesEndOn"       => $lastDayOfMonth
        ), $this->translator);
        
        if( $testOnly ) {
            echo count($usersGold0With0Points) ." utilisateurs sans points dans la category gold\n";
        }
        
        $silver_leagueLevelId = $leagueLevelRep->findLeagueLevelIdByCategoryLabelAndStars( "silver", 0);
        foreach( $usersGold0With0Points as $user ) { 
            if( $testOnly ) {
                echo $user["id"] ." passe de or 0* a argent 0\n";
            } else {
                $leagueLevelRep->updateLeagueLevel( $user["id"], $silver_leagueLevelId );
                
                $dataForSeasonUpdate = $userRep->getDataForSeasonUpdate($user["id"], $firstDayOfMonth, $lastDayOfMonth);
                $message = $translator->trans('mails.league-update1') . " ". $monthFR . " " . $dataForSeasonUpdate[0]['activities'] . " ". $translator->trans('mails.league-update2') . " ". $dataForSeasonUpdate[0]['points'];
                $message .= " " . $translator->trans('mails.league-update3') . " OR 0 " . $translator->trans('mails.league-update4') . " ARGENT" . $translator->trans('mails.league-update5');
                //var_dump($message);
                $notifications[] = array(
                    "fromUser"              => $keepinsportUser,
                    "toUser"                => $userRep->find( $user["id"] ),
                    "message"               => $message
                );
            }
        }
        
        //Envoi des notifs restantes (ligues 1* et 2* => aucune incidence)
        
        //Utilisateurs qui restent en ligues Or
        if( isset( $usersIdsByLeagues["gold"]["3*"] ) && is_array( $usersIdsByLeagues["gold"]["3*"] )) {   
            foreach( $usersIdsByLeagues["gold"]["3*"] as $userId ) {
                $dataForSeasonUpdate = $userRep->getDataForSeasonUpdate($userId, $firstDayOfMonth, $lastDayOfMonth);
                $message = $translator->trans('mails.league-update1') . " ". $monthFR . " " . $dataForSeasonUpdate[0]['activities'] . " ". $translator->trans('mails.league-update2') . " ". $dataForSeasonUpdate[0]['points'];
                $message .= " " . $translator->trans('mails.league-update3') . " OR 3 " . $translator->trans('mails.league-update4bis') . " OR" . $translator->trans('mails.league-update5');
                //var_dump($message);
                $notifications[] = array(
                    "fromUser"              => $keepinsportUser,
                    "toUser"                => $userRep->find( $userId ),
                    "message"               => $message
                );
            }
        }
        if( isset( $usersIdsByLeagues["gold"]["2*"] ) && is_array( $usersIdsByLeagues["gold"]["2*"] )) {   
            foreach( $usersIdsByLeagues["gold"]["2*"] as $userId ) {
                $dataForSeasonUpdate = $userRep->getDataForSeasonUpdate($userId, $firstDayOfMonth, $lastDayOfMonth);
                $message = $translator->trans('mails.league-update1') . " ". $monthFR . " " . $dataForSeasonUpdate[0]['activities'] . " ". $translator->trans('mails.league-update2') . " ". $dataForSeasonUpdate[0]['points'];
                $message .= " " . $translator->trans('mails.league-update3') . " OR 2 " . $translator->trans('mails.league-update4bis') . " OR" . $translator->trans('mails.league-update5');
                //var_dump($message);
                $notifications[] = array(
                    "fromUser"              => $keepinsportUser,
                    "toUser"                => $userRep->find( $userId ),
                    "message"               => $message
                );
            }
        }
        if( isset( $usersIdsByLeagues["gold"]["1*"] ) && is_array( $usersIdsByLeagues["gold"]["1*"] )) {   
            foreach( $usersIdsByLeagues["gold"]["1*"] as $userId ) {
                $dataForSeasonUpdate = $userRep->getDataForSeasonUpdate($userId, $firstDayOfMonth, $lastDayOfMonth);
                $message = $translator->trans('mails.league-update1') . " ". $monthFR . " " . $dataForSeasonUpdate[0]['activities'] . " ". $translator->trans('mails.league-update2') . " ". $dataForSeasonUpdate[0]['points'];
                $message .= " " . $translator->trans('mails.league-update3') . " OR 1 " . $translator->trans('mails.league-update4bis') . " OR" . $translator->trans('mails.league-update5');
                //var_dump($message);
                $notifications[] = array(
                    "fromUser"              => $keepinsportUser,
                    "toUser"                => $userRep->find( $userId ),
                    "message"               => $message
                );
            }
        }
        
        //Utilisateurs qui restent en ligues Argent
        if( isset( $usersIdsByLeagues["silver"]["2*"] ) && is_array( $usersIdsByLeagues["silver"]["2*"] )) {   
            foreach( $usersIdsByLeagues["silver"]["2*"] as $userId ) {
                $dataForSeasonUpdate = $userRep->getDataForSeasonUpdate($userId, $firstDayOfMonth, $lastDayOfMonth);
                $message = $translator->trans('mails.league-update1') . " ". $monthFR . " " . $dataForSeasonUpdate[0]['activities'] . " ". $translator->trans('mails.league-update2') . " ". $dataForSeasonUpdate[0]['points'];
                $message .= " " . $translator->trans('mails.league-update3') . " ARGENT 2 " . $translator->trans('mails.league-update4bis') . " ARGENT" . $translator->trans('mails.league-update5');
                //var_dump($message);
                $notifications[] = array(
                    "fromUser"              => $keepinsportUser,
                    "toUser"                => $userRep->find( $userId ),
                    "message"               => $message
                );
            }
        }
        if( isset( $usersIdsByLeagues["silver"]["1*"] ) && is_array( $usersIdsByLeagues["silver"]["1*"] )) {   
            foreach( $usersIdsByLeagues["silver"]["1*"] as $userId ) {
                $dataForSeasonUpdate = $userRep->getDataForSeasonUpdate($userId, $firstDayOfMonth, $lastDayOfMonth);
                $message = $translator->trans('mails.league-update1') . " ". $monthFR . " " . $dataForSeasonUpdate[0]['activities'] . " ". $translator->trans('mails.league-update2') . " ". $dataForSeasonUpdate[0]['points'];
                $message .= " " . $translator->trans('mails.league-update3') . " ARGENT 1 " . $translator->trans('mails.league-update4bis') . " ARGENT" . $translator->trans('mails.league-update5');
                //var_dump($message);
                $notifications[] = array(
                    "fromUser"              => $keepinsportUser,
                    "toUser"                => $userRep->find( $userId ),
                    "message"               => $message
                );
            }
        }
        
        //Utilisateurs qui restent en ligues Bronze
        if( isset( $usersIdsByLeagues["bronze"]["2*"] ) && is_array( $usersIdsByLeagues["bronze"]["2*"] )) {   
            foreach( $usersIdsByLeagues["bronze"]["2*"] as $userId ) {
                $dataForSeasonUpdate = $userRep->getDataForSeasonUpdate($userId, $firstDayOfMonth, $lastDayOfMonth);
                $message = $translator->trans('mails.league-update1') . " ". $monthFR . " " . $dataForSeasonUpdate[0]['activities'] . " ". $translator->trans('mails.league-update2') . " ". $dataForSeasonUpdate[0]['points'];
                $message .= " " . $translator->trans('mails.league-update3') . " BRONZE 2 " . $translator->trans('mails.league-update4bis') . " BRONZE" . $translator->trans('mails.league-update5');
                //var_dump($message);
                $notifications[] = array(
                    "fromUser"              => $keepinsportUser,
                    "toUser"                => $userRep->find( $userId ),
                    "message"               => $message
                );
            }
        }
        if( isset( $usersIdsByLeagues["bronze"]["1*"] ) && is_array( $usersIdsByLeagues["bronze"]["1*"] )) {   
            foreach( $usersIdsByLeagues["bronze"]["1*"] as $userId ) {
                $dataForSeasonUpdate = $userRep->getDataForSeasonUpdate($userId, $firstDayOfMonth, $lastDayOfMonth);
                $message = $translator->trans('mails.league-update1') . " ". $monthFR . " " . $dataForSeasonUpdate[0]['activities'] . " ". $translator->trans('mails.league-update2') . " ". $dataForSeasonUpdate[0]['points'];
                $message .= " " . $translator->trans('mails.league-update3') . " BRONZE 1 " . $translator->trans('mails.league-update4bis') . " BRONZE" . $translator->trans('mails.league-update5');
                //var_dump($message);
                $notifications[] = array(
                    "fromUser"              => $keepinsportUser,
                    "toUser"                => $userRep->find( $userId ),
                    "message"               => $message
                );
            }
        }
        
        
        //Maj de la table temporaire
        if( !$testOnly ) {
            foreach( $leaguesCategories as $leagueCategory ) {
                $users      = $userRep->findUsers(array(
                    //"usersIds"          => $usersIdsInCategory,
                    "leagueCategoryId"  => $leagueCategory['id'],
                    "withPoints"        => true,
                    //"usersWith0points"  => false,
                    "activitiesStartOn" => $firstDayOfMonth,
                    "activitiesEndOn"   => $lastDayOfMonth
                ), $this->translator);

                foreach( $users as $user ) { 
                    $leagueCatRep->updateUserRank($user["id"], $leagueCategory['id'], 1);
                }
            }
        }
        
        if( !$testOnly ) {
            foreach( $notifications as $notification ) {
                //var_dump($notification["toUser"]->getId());
                $this->notificationService->sendNotification(
                    null, 
                    $notification["fromUser"], 
                    $notification["toUser"], 
                    "league", 
                    $notification["message"]
                );
            }
        }
        
        //Remise à jour des étoiles pour la saison en cours
        $this->leaguesRankingUpdate('m');
    }
    
    private function saveHistoric( $month = null, $testOnly = false ) {
        $em                 = $this->doctrine->getEntityManager();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $leagueCatRep       = $em->getRepository('KsLeagueBundle:LeagueCategory');   
        $leagueLevelRep     = $em->getRepository('KsLeagueBundle:LeagueLevel');   
        $leagueHistoricRep  = $em->getRepository('KsLeagueBundle:Historic');
        $trophyRep          = $em->getRepository('KsTrophyBundle:Trophy');
        $userWinTrophiesRep = $em->getRepository('KsTrophyBundle:UserWinTrophies');
        
        if( $month == null ) {
            $month = date("m");
        }
        
        $year = date("Y");
        
        $firstDayOfMonth = date("Y-$month-01"); 
        $lastDayOfMonth = date("Y-$month-t"); 
        
        //Récupération des leagues à mettre à jour
        $leaguesCategories = $leagueCatRep->findLeagues();
        
        foreach( $leaguesCategories as $leagueCategory ) {
            
            if( !$testOnly ) {
                //Création du trophée 3* de cette category
                $leagueLevel_3stars_Id = $leagueLevelRep->findLeagueLevelIdByCategoryLabelAndStars( $leagueCategory["label"], 3);
                if( is_numeric( $leagueLevel_3stars_Id ) ) {
                    $trophyRep->createEndSeasonTrophy($month, $year, $leagueLevel_3stars_Id);
                }
            }
        
            //Récupération plus d'informations sur les utilisateurs (les points nous interessent)
            $users      = $userRep->findUsers(array(
                "leagueCategoryId"  => $leagueCategory["id"],
                "withPoints"        => true,
                "activitiesStartOn" => $firstDayOfMonth,
                "activitiesEndOn"   => $lastDayOfMonth
            ), $this->translator);

            //Tri par points décroissants
            usort( $users, array( "Ks\UserBundle\Entity\UserRepository", "orderUsersByPointsDesc" ) );

            //On sauvegarde l'historique pour chaque utilisateur
            foreach( $users as $key => $user ) {        
                //Ue fois trié, le rang = clé + 1
                $rank = $key + 1;

                $leagueHistoricRep->save( $month, $year, $user["id"], $user["leagueLevelId"], $user["leagueCategoryId"], $rank, $user["points"] );
                
                $starsNumber        = intval( $leagueLevelRep->findStarsNumberByLeagueLevelId( 113 /*$user["leagueLevelId"]*/ ));

                if( !$testOnly ) {
                    //On fait gagner un trophée aux utilisateurs 3*
                    if( $starsNumber == 3 ) {
                        $trophyHabBeenUnlocked = $userWinTrophiesRep->unlockTrophy( $user["id"], $month, $year, $leagueLevel_3stars_Id );

                        //Si le trophé a bien été déverrouillé
                        if( $trophyHabBeenUnlocked ) {
                            //Création d'une notification
                            //$notificationType_name = "trophy";
                            //$this->notificationService->sendNotification(null, $user, $user, $notificationType_name, "Félicitations ! Tu viens de débloquer le badge : ".$trophy->getCode());
                        }
                    }
                }
            }
        }
    }
}