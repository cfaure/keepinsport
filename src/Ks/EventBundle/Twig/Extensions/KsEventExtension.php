<?php

namespace Ks\EventBundle\Twig\Extensions;


class KsEventExtension extends \Twig_Extension
{
    protected $container;
    
    public function __construct($container)
    {
        $this->container        = $container;
    }
    
    public function getFilters()
    {
        return array(
            'userParticipatesEvent'              => new \Twig_Filter_Method($this, 'userParticipatesEvent'),
        );
    }

    public function userParticipatesEvent($eventParticipations, $userId)
    {
        foreach($eventParticipations as $eventParticipation) {
  
            if((string)$eventParticipation['user_id'] == (string)$userId ) {
                return true;
            }
        }
        return false;
    }
   

    public function getName()
    {
        return 'ks_event_extension';
    }
}
?>
