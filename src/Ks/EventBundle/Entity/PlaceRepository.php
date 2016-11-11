<?php

namespace Ks\EventBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PlaceRepository
 *
 */
class PlaceRepository extends EntityRepository
{
    /**
     *
     * @return query builder des pays par ordre alphabétique
     */
    public function findCountryQB() {
        $qb = $this->_em->createQueryBuilder();
 
        $qb->select('ud')
                ->from('KsUserBundle:UserDetail', 'ud')
                ->where('ud.country_code IS NOT NULL')
                ->andWhere('ud.country_code != \'\'')
                ->groupBy('ud.country_code')
                ->orderBy('ud.country_code', 'ASC');
        
        return $qb;
    }
    
    /**
     *
     * @return query builder des pays par ordre alphabétique
     */
    public function findRegionQB() {
        $qb = $this->_em->createQueryBuilder();
 
        $qb->select('ud')
                ->from('KsUserBundle:UserDetail', 'ud')
                ->where('ud.country_area IS NOT NULL')
                ->andWhere('ud.country_area != \'\'')
                ->groupBy('ud.country_area')
                ->orderBy('ud.country_area', 'ASC');
        
        return $qb;
    }
    
    /**
     *
     * @return query builder des pays par ordre alphabétique
     */
    public function findTownQB() {
        $qb = $this->_em->createQueryBuilder();
 
        $qb->select('ud')
                ->from('KsUserBundle:UserDetail', 'ud')
                ->where('ud.town IS NOT NULL')
                ->andWhere('ud.town != \'\'')
                ->groupBy('ud.town')
                ->orderBy('ud.town', 'ASC');
        
        return $qb;
    }
}