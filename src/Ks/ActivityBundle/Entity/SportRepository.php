<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * SportRepository
 *
 */
class SportRepository extends EntityRepository
{
    /**
     *
     * @return query builder de sports classés par ordre alphabétique
     */
    public function findSportsQB() {      
        $queryBuilder = $this->_em->createQueryBuilder();
 
        $queryBuilder->select('s')
                ->from('KsActivityBundle:Sport', 's')
                ->where("s.id != -1")
                ->orderBy('s.label', 'ASC');

        return $queryBuilder;
    }
    
    /**
     *
     * @return query builder de sports classés par ordre alphabétique
     */
    public function findSportsClubQB($clubId) {      
        $queryBuilder = $this->_em->createQueryBuilder();
 
        $queryBuilder->select('s')
                ->from('KsActivityBundle:Sport', 's')
                ->innerJoin("s.clubs", "c")
                ->where("c.id = ?1 and s.id != -1")
                ->orderBy('s.label', 'ASC')
                ->setParameter(1, $clubId);

        return $queryBuilder;
    }
    
     public function getSportsGroundsBySport($sportId) {   
        //On récupère les sports ground ids
        $dbh        = $this->_em->getConnection();
        $sql = 'SELECT sportGround.id, sportGround.code, sportGround.label'
            .' FROM ks_sport_ground sportGround'
            .' INNER JOIN ks_sport_has_ground sportHasGround on (sportHasGround.sportground_id = sportGround.id) '
            .' WHERE sportHasGround.sport_id = :sportId'
            .' ORDER BY sportGround.label ASC';

        $stmt = $dbh->prepare($sql);
        
        $stmt->execute(array(
            'sportId'    => $sportId,
        ));
        
        $sportsGrounds = $stmt->fetchAll();
        
        return $sportsGrounds;
    }
    
    /**
     *
     * @return query builder des terrain de sports classés
     */
    public function getSportsGroundsBySportQB($sportId) {   
        //On récupère les sports ground ids
        $dbh        = $this->_em->getConnection();
        $sql = 'SELECT sportGround.id'
            .' FROM ks_sport_ground sportGround'
            .' INNER JOIN ks_sport_has_ground sportHasGround on (sportHasGround.sportground_id = sportGround.id) '
            .' WHERE sportHasGround.sport_id = :sportId'
            .' ORDER BY sportGround.label ASC';

        $stmt = $dbh->prepare($sql);
        
        $stmt->execute(array(
            'sportId'    => $sportId,
        ));
        
        $sportsGroundsIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        //var_dump($sportsGroundsIds);
        
        //Construction du query builer
        $queryBuilder = $this->_em->createQueryBuilder();
 
        $queryBuilder->select('sg')
                ->from('KsActivityBundle:SportGround', 'sg')
                ->where("sg.id in (?1)")
                ->orderBy('sg.label', 'ASC')
                ->setParameter(1, array('6', '7', '8'));

        return $queryBuilder;
    }
    
    
    /**
     *
     * @return query builder de sports classés par ordre alphabétique
     */
    public function getSportsASC()
    {      
        $queryBuilder = $this->_em->createQueryBuilder();
 
        $queryBuilder->select('s')
                ->from('KsActivityBundle:Sport', 's')
                ->where("s.id != -1")
                ->orderBy('s.label', 'ASC');

        $query = $queryBuilder->getQuery();
        
        return $query->getResult();
    }
    
    /**
     * 
     * @param type $firstDayOfMonth
     * @param type $lastDayOfMonth
     */
    public function findPractisesSportsInPeriode($userId, $firstDayOfMonth, $lastDayOfMonth) {
        $dbh        = $this->_em->getConnection();
        $sql = 'SELECT sport.id, sport.label '
            .' FROM ks_activity a'
            .' INNER JOIN ks_sport sport on (a.sport_id = sport.id) '
            .' WHERE 1'
            .' AND a.user_id = :userId'
            .' AND SUBSTRING(a.issuedAt, 1, 10) >= :startOn'
            .' AND SUBSTRING(a.issuedAt, 1, 10) <= :endOn'
            .' AND (a.calories != null OR a.duration > "00:00:00")'
            .' GROUP BY sport.id'
            .' ORDER BY sport.label ASC';
        //var_dump($sql);
        $stmt = $dbh->prepare($sql);
        
        $stmt->execute(array(
            'userId'    => $userId,
            'startOn'   => $firstDayOfMonth,
            'endOn'     => $lastDayOfMonth
        ));
        
        $sports = array();
        while ($sport = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            
            $sports[$sport["id"]] = $sport["label"];
        }
        
        return $sports;
    }
    
    /**
     * Trouver les sports en commun sur une période
     * @param type $firstDayOfMonth
     * @param type $lastDayOfMonth
     */
    public function findCommonPractisesSportsInPeriode($user1Id, $user2Id, $firstDayOfMonth, $lastDayOfMonth) {
        $sportsUser1 = $this->findPractisesSportsInPeriode($user1Id, $firstDayOfMonth, $lastDayOfMonth);
        $sportsUser2 = $this->findPractisesSportsInPeriode($user2Id, $firstDayOfMonth, $lastDayOfMonth);
        
        $commonSports      = array_intersect(
                $sportsUser1,
                $sportsUser2
            );
        
        return $commonSports;
    }
    
    /**
     * Trouver les sports pratiqués sur une période par un ensemble d'utilisateurs
     * @param type $firstDayOfMonth
     * @param type $lastDayOfMonth
     */
    public function findPractisesSportsInPeriodeByUsers($usersId, $firstDayOfMonth, $lastDayOfMonth) {
        $sports = array();
        
        foreach( $usersId as $userId ) {
            $userSports = $this->findPractisesSportsInPeriode($userId, $firstDayOfMonth, $lastDayOfMonth);
            foreach( $userSports as $userSportKey => $userSportLabel ) {
                if( !in_array( $userSportKey, $sports)) {
                    $sports[$userSportKey] = $userSportLabel;
                }
            }
        }

        return $sports;
    }
}