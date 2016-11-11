<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserHasServicesRepository
 *
 */
class UserHasServicesRepository extends EntityRepository
{
    
    /**
     *
     * @return boolean si l'utilisateur a un service en cours de synchro
     */
    public function areBeingSynchronized($userId)
    {      
        $dbh = $this->_em->getConnection();
        $sql = "SELECT count(uhs.service_id)"
            ." FROM ks_user_has_services uhs" 
            ." WHERE uhs.user_id = :userId"
            ." AND uhs.is_active = :serviceIsActive"
            ." AND uhs.status = :serviceStatus";
        
        $stmt = $dbh->executeQuery($sql, array(
            'userId' => $userId,
            'serviceIsActive' => true,
            'serviceStatus' =>    "pending"
        ));

        $result = $stmt->fetchColumn();
        
        //var_dump($result);
        return ($result > 0 ? true : false);
    }
    
    /**
     *
     * @return boolean si l'utilisateur a un service paramétré comme actif (pour afficher le bouton de synchro manuelle ou non en haut)
     */
    public function userHasActiveServices($userId)
    {      
        $dbh = $this->_em->getConnection();
        $sql = "SELECT count(uhs.service_id)"
            ." FROM ks_user_has_services uhs" 
            ." WHERE uhs.user_id = :userId"
            ." AND uhs.is_active = :serviceIsActive"
            ." AND uhs.status = :serviceStatus";
            //." AND uhs.service_id != 2"; //GOOGLE agenda
        
        $stmt = $dbh->executeQuery($sql, array(
            'userId' => $userId,
            'serviceIsActive' => true,
            'serviceStatus' =>    "done"
        ));

        $result = $stmt->fetchColumn();
        
        //var_dump($result);
        return ($result > 0 ? true : false);
    }
}
