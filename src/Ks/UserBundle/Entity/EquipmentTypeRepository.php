<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * EquipmentTypeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EquipmentTypeRepository extends EntityRepository
{
    public function findEquipmentsTypes( array $params = array() ) {
        $dbh        = $this->_em->getConnection();
        
        $vars       = array();  
        $sqlParts   = array(
            'select' => 'SELECT equipment_type.id, equipment_type.code, '
                .' equipment_type.isPrimaryColorEnabled, equipment_type.isSecondaryColorEnabled, equipment_type.isWeightEnabled'
            ,
            'from'  => 'FROM ks_equipment_type equipment_type'
            ,
            'where' => "WHERE 1",
            'group' => 'GROUP BY equipment_type.id',
            'order' => '',
            'limit' => ''
        ); 
        
        $stmt = $dbh->executeQuery(implode(' ', $sqlParts), $vars);
        
        $characters = array();
        while( $character = $stmt->fetch(\PDO::FETCH_ASSOC) ) {
                
            $characters[] = $character;
        }
        
        return $characters;
    }
}