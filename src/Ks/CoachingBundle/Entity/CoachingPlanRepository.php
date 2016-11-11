<?php

namespace Ks\CoachingBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * CoachingPlanRepository
 *
 */
class CoachingPlanRepository extends EntityRepository
{
    public function findCoachingPlanByClubQB(\Ks\ClubBundle\Entity\Club $club) {
        $queryBuilder = $this->_em->createQueryBuilder();
        
        $queryBuilder->add('select', 'cp')
                ->from('KsCoachingBundle:CoachingPlan',  'cp')
                ->where('cp.club = ?1 ')
                ->setParameter(1, $club->getId());
        return $queryBuilder;
    }
    
    public function findCoachingPlanByUserQB(\Ks\UserBundle\Entity\User $user) {
        $queryBuilder = $this->_em->createQueryBuilder();
        
        $queryBuilder->add('select', 'cp')
                ->from('KsCoachingBundle:CoachingPlan',  'cp')
                ->where('cp.user = ?1 ')
                ->setParameter(1, $user->getId());
        return $queryBuilder;
    }
    
    public function getNextCompetition($planId)
    {
        $competition = array();
        
        $sql = "SELECT e.id, e.competition, e.name, e.startDate, DATEDIFF(e.startDate, CURDATE()) as delay "
                ." FROM ks_event e, ks_coaching_category cc "
                ." WHERE e.coachingPlan_id = ". $planId
                ." AND coachingCategory_id = cc.id "
                ." AND cc.isCompetition = 1"
                ." AND DATEDIFF(e.startDate, CURDATE()) >0 "
                ." ORDER BY delay ASC";
        
        //var_dump($sql);
        $dbh        = $this->_em->getConnection();
        
        $results = $dbh->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        foreach( $results as $res ) {
            $competition[] = $res;
        }
        //var_dump($res);
        
        return $competition;
    }
    
    public function getPlanOverlap($userId, $startDate, $endDate) {
        $dbh    = $this->_em->getConnection();
        
        $planOverLap = 0;
        
        $stmt   = $dbh->executeQuery(
            " SELECT cp.name FROM ks_event e "
            ." LEFT JOIN ks_coaching_plan cp on (cp.id = e.coachingPlan_id)"
            ." WHERE e.user_id = :userId"
            ." AND   e.coachingPlan_id IS NOT NULL"
            ." AND   e.typeEvent_id = 5" /*Type event_coaching*/
            ." AND   (e.startDate >= STR_TO_DATE(:startDate, '%Y-%m-%d') AND e.startDate <= STR_TO_DATE(:endDate, '%Y-%m-%d') OR e.endDate >= STR_TO_DATE(:startDate, '%Y-%m-%d') AND   e.endDate <= STR_TO_DATE(:endDate, '%Y-%m-%d'))",
            array(
                "userId"    => $userId,
                "startDate" => $startDate->format("Y-m-d"),
                "endDate"   => $endDate->format("Y-m-d")
            )
        );
        
        $planOverLap = $stmt->fetchColumn();
//        var_dump($startDate->format("Y-m-d"));
//        var_dump($endDate->format("Y-m-d"));
//        var_dump($planOverLap);exit;
        
        return $planOverLap;
        
    }
}