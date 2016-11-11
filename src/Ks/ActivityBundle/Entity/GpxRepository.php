<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * GpxRepository
 *
 */
class GpxRepository extends EntityRepository
{
    public function getGpxByActivity($activityId) {   
        
        $dbh        = $this->_em->getConnection();
        $sql = 'SELECT id'
            .' FROM ks_gpx'
            .' WHERE activity_id = :activityId';

        $stmt = $dbh->prepare($sql);
        
        $stmt->execute(array(
            'activityId'    => $activityId,
        ));
        
        $gpx = $stmt->fetchAll();
        
        return $gpx;
    }
    
    /**
     * Pour supprimer le GPX lié à une activité
     */
    public function deleteGPXFromActivity($activityId)
    {
        $dbh    = $this->_em->getConnection();
        $stmt   = $dbh->executeQuery(
            'delete from ks_gpx where activity_id = ?',
            array($activityId)
        );
        $this->_em->flush();
    }
}