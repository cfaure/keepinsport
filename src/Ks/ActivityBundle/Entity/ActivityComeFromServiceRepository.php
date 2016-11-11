<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ActivityComeFromServiceRepository extends EntityRepository
{
    public function findByWebsiteId($id)
    {
        $dbh            = $this->_em->getConnection();
        $now = new \DateTime();
        
        $sql  = "select a.activity_id "
            ." FROM ks_activity_come_from_service a"
            ." WHERE a.service_id = 9"
            ." AND a.id_website_activity_service = " . $id;
        
        $stmt = $dbh->executeQuery($sql);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}