<?php

namespace Ks\EquipmentBundle\Twig\Extensions;


class KsEquipmentExtension extends \Twig_Extension
{
    protected $container;
    
    public function __construct($container)
    {
        $this->container        = $container;
    }
    
    public function getFilters()
    {
        return array(
            'userUsesEquipment'              => new \Twig_Filter_Method($this, 'userUsesEquipment'),
        );
    }

    public function userUsesEquipment($users, $userId)
    {
        foreach($users as $user) {
  
            if((string)$user['user_id'] == (string)$userId ) {
                return true;
            }
        }
        return false;
    }
   

    public function getName()
    {
        return 'ks_equipment_extension';
    }
}
?>
