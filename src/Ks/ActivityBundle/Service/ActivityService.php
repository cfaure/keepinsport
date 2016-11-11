<?php

namespace Ks\ActivityBundle\Service;

use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ks\ActivityBundle\Activity;

/**
 *
 * @author Ced
 */
class ActivityService
    extends \Twig_Extension
{

    protected $_doctrine;
    
    /**
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->_doctrine            = $doctrine;
    }
    
    public function getName()
    {
        return 'ActivityService';
    }
    
    public function duplicateActivityForUsersWhoHaveParticipated($activity, $user) {      
        $em             = $this->_doctrine->getEntityManager(); 
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        
         switch ($activity->getType()) {
            case 'session_team_sport':
                $newActivity = new \Ks\ActivityBundle\Entity\ActivitySessionTeamSport( $user );
                
                foreach( $activity->getScores() as $scores ) {
                    $newScore = new \Ks\ActivityBundle\Entity\Score();
                    $newScore->setScore1($scores->getScore1());
                    $newScore->setScore2($scores->getScore2());
                    $newScore->setRoundOrder($scores->getRoundOrder());
                    $newScore->setActivity($newActivity);
                    $em->persist($newScore);
                    $newActivity->addScore($newScore);
                }

                $result = $activity->getResult();
                if ( $result != null ) {
                    $newActivity->setResult($result);
                }
        
                break;
            
            case 'session_endurance_on_earth':
                $newActivity = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth( $user );
                
                if( $activity->getTrackingDatas() != null )
                    $newActivity->setTrackingDatas($activity->getTrackingDatas());
                
                $newActivity->setElevationMin($activity->getElevationMin());
                $newActivity->setElevationMax($activity->getElevationMax());
                $newActivity->setElevationGain($activity->getElevationGain());
                $newActivity->setElevationLost($activity->getElevationLost());
                
                $newActivity->setDistance($activity->getDistance());
                //$newActivity->setTimeElapsed($newActivity->getTimeElapsed());
                $newActivity->setTimeMoving($activity->getTimeMoving());
                $newActivity->setSpeedMin($activity->getSpeedMin());
                $newActivity->setSpeedMax($activity->getSpeedMax());
                $newActivity->setSpeedAverage($activity->getSpeedAverage());
                break;
            
            case 'session_endurance_under_water':
                $newActivity = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceUnderWater( $user );
                
                if( $activity->getTrackingDatas() != null )
                    $newActivity->setTrackingDatas($activity->getTrackingDatas());
                
                $newActivity->setDepthGain($activity->getDepthGain());
                $newActivity->setDepthMax($activity->getDepthMax());
                
                $newActivity->setDistance($activity->getDistance());
                //$newActivity->setTimeElapsed($newActivity->getTimeElapsed());
                $newActivity->setTimeMoving($activity->getTimeMoving());
                $newActivity->setSpeedMin($activity->getSpeedMin());
                $newActivity->setSpeedMax($activity->getSpeedMax());
                $newActivity->setSpeedAverage($activity->getSpeedAverage());
                break;
            
            default :
                $newActivity = new \Ks\ActivityBundle\Entity\ActivitySessionTeamSport( $user );
         }
         
        
        $newActivity->setIsValidate(false);

        $newActivity->setDuration( $activity->getDuration() );

        $newActivity->setSport($activity->getSport());
        $newActivity->setIssuedAt($activity->getIssuedAt());

        foreach( $activity->getUsersWhoHaveParticipated() as $userWhoHasParticipated ) {
            if( $user != $userWhoHasParticipated) {
                $newActivity->addUserWhoHasParticipated($userWhoHasParticipated);
            }
        }

        foreach( $activity->getOpponentsWhoHaveParticipated() as $opponent ) {
            if( $user != $opponent) {
                $newActivity->addOpponentWhoHasParticipated($opponent);
            }
        }
        
        $place = $activity->getPlace();
        if( $place != null ) {
            $newPlace = new \Ks\EventBundle\Entity\Place();
            $newPlace->setLatitude($place->getLatitude());
            $newPlace->setLongitude($place->getLongitude());  
            $newPlace->setCountryCode($place->getCountryCode());
            $newPlace->setCountryLabel($place->getCountryLabel());
            $newPlace->setRegionCode($place->getRegionCode());
            $newPlace->setRegionLabel($place->getRegionLabel());
            $newPlace->setCountyCode($place->getCountyCode());
            $newPlace->setCountyLabel($place->getCountyLabel());
            $newPlace->setTownCode($place->getTownCode());
            $newPlace->setTownLabel($place->getTownLabel());
            $newPlace->setFullAdress($place->getFullAdress());
            
            $em->persist($newPlace);
            $newActivity->setPlace( $newPlace );
        }

        $em->persist($newActivity);
        $em->flush();
        
        //On abonne l'utilisateur à l'activité
        $activityRep->subscribeOnActivity($newActivity, $user);
        
        return $newActivity;
    }
    
    public function duplicateActivityForOpponentsWhoHaveParticipated($activity, $user) {      
        $em             = $this->_doctrine->getEntityManager(); 
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');  
        $resultRep      = $em->getRepository('KsActivityBundle:Result');
        
        switch ($activity->getType()) {
            case 'session_team_sport':
                $newActivity = new \Ks\ActivityBundle\Entity\ActivitySessionTeamSport( $user );
                
                foreach( $activity->getScores() as $scores ) {
                    $newScore = new \Ks\ActivityBundle\Entity\Score();
                    $newScore->setScore1($scores->getScore2());
                    $newScore->setScore2($scores->getScore1());
                    $newScore->setRoundOrder($scores->getRoundOrder());
                    $newScore->setActivity($newActivity);
                    $em->persist($newScore);
                    $newActivity->addScore($newScore);
                }

                $result = $activity->getResult();
                if ( $result != null ) {
                    if ($result->getCode() == "v") {
                        $defaite = $resultRep->findOneByCode("d") ;
                        if( is_object( $defaite ) ) {
                            $newActivity->setResult($defaite);
                        }
                    }

                    if ($result->getCode() == "d") {
                        $victoire = $resultRep->findOneByCode("v") ;
                        if( is_object( $victoire ) ) {
                            $newActivity->setResult($victoire);
                        }
                    }
                }
        
                break;
            
            case 'session_endurance_on_earth':
                $newActivity = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth( $user );
                
                if( $activity->getTrackingDatas() != null )
                    $newActivity->setTrackingDatas($activity->getTrackingDatas());
                
                $newActivity->setElevationMin($activity->getElevationMin());
                $newActivity->setElevationMax($activity->getElevationMax());
                $newActivity->setElevationGain($activity->getElevationGain());
                $newActivity->setElevationLost($activity->getElevationLost());
                
                $newActivity->setDistance($activity->getDistance());
                //$newActivity->setTimeElapsed($newActivity->getTimeElapsed());
                $newActivity->setTimeMoving($activity->getTimeMoving());
                $newActivity->setSpeedMin($activity->getSpeedMin());
                $newActivity->setSpeedMax($activity->getSpeedMax());
                $newActivity->setSpeedAverage($activity->getSpeedAverage());
                
                break;
            
            case 'session_endurance_under_water':
                $newActivity = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceUnderWater( $user );
                
                if( $activity->getTrackingDatas() != null )
                    $newActivity->setTrackingDatas($activity->getTrackingDatas());
                
                $newActivity->setElevationMin($activity->getElevationMin());
                $newActivity->setElevationMax($activity->getElevationMax());
                $newActivity->setElevationGain($activity->getElevationGain());
                $newActivity->setElevationLost($activity->getElevationLost());
                
                $newActivity->setDistance($activity->getDistance());
                //$newActivity->setTimeElapsed($newActivity->getTimeElapsed());
                $newActivity->setTimeMoving($activity->getTimeMoving());
                $newActivity->setSpeedMin($activity->getSpeedMin());
                $newActivity->setSpeedMax($activity->getSpeedMax());
                $newActivity->setSpeedAverage($activity->getSpeedAverage());
                break;
            
            default :
                $newActivity = new \Ks\ActivityBundle\Entity\ActivitySessionTeamSport( $user );
         }
         
        $newActivity->setIsValidate(false);

        $newActivity->setDuration($activity->getDuration());

        $newActivity->setSport($activity->getSport());
        $newActivity->setIssuedAt($activity->getIssuedAt());

        foreach( $activity->getUsersWhoHaveParticipated() as $userWhoHasParticipated ) {
            if( $user != $userWhoHasParticipated) {
                $newActivity->addOpponentWhoHasParticipated($userWhoHasParticipated);
            }
        }

        foreach( $activity->getOpponentsWhoHaveParticipated() as $opponent ) {
            if( $user != $opponent) {
                $newActivity->addUserWhoHasParticipated($opponent);
            }
        }
        
        $place = $activity->getPlace();
        if( $place != null ) {
            $newPlace = new \Ks\EventBundle\Entity\Place();
            $newPlace->setLatitude($place->getLatitude());
            $newPlace->setLongitude($place->getLongitude());  
            $newPlace->setCountryCode($place->getCountryCode());
            $newPlace->setCountryLabel($place->getCountryLabel());
            $newPlace->setRegionCode($place->getRegionCode());
            $newPlace->setRegionLabel($place->getRegionLabel());
            $newPlace->setCountyCode($place->getCountyCode());
            $newPlace->setCountyLabel($place->getCountyLabel());
            $newPlace->setTownCode($place->getTownCode());
            $newPlace->setTownLabel($place->getTownLabel());
            $newPlace->setFullAdress($place->getFullAdress());
            
            $em->persist($newPlace);
            $newActivity->setPlace( $newPlace );
        }

        $em->persist($newActivity);
        $em->flush();
        
        //On abonne l'utilisateur à l'activité
        $activityRep->subscribeOnActivity($newActivity, $user);
        
        return $newActivity;
    }
    
    public function duplicateActivityForUserHasSportFrequency($activity, $user) {      
        $em             = $this->_doctrine->getEntityManager(); 
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        
         switch ($activity->getType()) {
            case 'session_team_sport':
                $newActivity = new \Ks\ActivityBundle\Entity\ActivitySessionTeamSport( $user );
                
                $result = $activity->getResult();
                if ( $result != null ) {
                    $newActivity->setResult($result);
                }
        
                break;
            
            case 'session_endurance_on_earth':
                $newActivity = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth( $user );
                
                $newActivity->setDistance($activity->getDistance());
                //$newActivity->setTimeElapsed($newActivity->getTimeElapsed());
                $newActivity->setTimeMoving($activity->getTimeMoving());
                break;
            
            case 'session_endurance_under_water':
                $newActivity = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceUnderWater( $user );
                
                $newActivity->setDistance($activity->getDistance());
                //$newActivity->setTimeElapsed($newActivity->getTimeElapsed());
                $newActivity->setTimeMoving($activity->getTimeMoving());
                break;
            
            default :
                $newActivity = new \Ks\ActivityBundle\Entity\ActivitySessionTeamSport( $user );
         }
         
        
        $newActivity->setIsValidate(false);

        $newActivity->setDuration( $activity->getDuration() );

        $newActivity->setSport($activity->getSport());
        $newActivity->setIssuedAt(new \DateTime('now'));
        $newActivity->setModifiedAt(new \DateTime('now'));
        
        $place = $activity->getPlace();
        if( $place != null ) {
            $newPlace = new \Ks\EventBundle\Entity\Place();
            $newPlace->setLatitude($place->getLatitude());
            $newPlace->setLongitude($place->getLongitude());  
            $newPlace->setCountryCode($place->getCountryCode());
            $newPlace->setCountryLabel($place->getCountryLabel());
            $newPlace->setRegionCode($place->getRegionCode());
            $newPlace->setRegionLabel($place->getRegionLabel());
            $newPlace->setCountyCode($place->getCountyCode());
            $newPlace->setCountyLabel($place->getCountyLabel());
            $newPlace->setTownCode($place->getTownCode());
            $newPlace->setTownLabel($place->getTownLabel());
            $newPlace->setFullAdress($place->getFullAdress());
            
            $em->persist($newPlace);
            $newActivity->setPlace( $newPlace );
        }

        $em->persist($newActivity);
        $em->flush();
        
        //On abonne l'utilisateur à l'activité
        $activityRep->subscribeOnActivity($newActivity, $user);
        
        return $newActivity;
    }
    
    function calculEndDate( $startDate, $duration ) {
        $endDate    = new \DateTime($startDate->format("Y-m-d H:i:s"));
        $duration   = round($duration);
        if ( $duration > 0 ) {
            $i = new \DateInterval('PT'.$duration.'S');
            $endDate->add($i);
        }
        
        return $endDate;
    }
    
    public function millisecondesToTimeDuration($duration) {
        //$time = strftime('%H:%M:%S', $duration/1000);
        $duration /= 1000;
        $heure = min(intval(abs($duration / 3600)), 23);
        $duration = $duration - ($heure * 3600);
        $minute = min(intval(abs($duration / 60)), 59);
        $duration = $duration - ($minute * 60);
        $seconde = min(round($duration), 59);
        $time = new \DateTime("$heure:$minute:$seconde");
        
        return $time;
    }
    
    public function secondesToTimeDuration($duration){
        $heure = min(intval(abs($duration / 3600)), 23);
        $duration = $duration - ($heure * 3600);
        $minute = min(intval(abs($duration / 60)), 59);
        $duration = $duration - ($minute * 60);
        $seconde = min(round($duration), 59);
        $time = new \DateTime("$heure:$minute:$seconde");
        
        return $time;
    }     
}