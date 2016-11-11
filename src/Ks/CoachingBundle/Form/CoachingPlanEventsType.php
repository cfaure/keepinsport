<?php

namespace Ks\CoachingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Ks\UserBundle\Entity\UserRepository;

class CoachingPlanEventsType extends AbstractType
{
    private $user;
    private $sport;
    
    public function __construct(\Ks\UserBundle\Entity\User $user = null, \Ks\ActivityBundle\Entity\Sport $sport = null, $key=null)
    {
        $this->user = $user;
        $this->sport = $sport;
        $this->key = $key;
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $user = $this->user;
        $sport = $this->sport;
        $key = $this->key;
        
        $builder->add('achievement')
                ->add('event', 'entity', array( 
                'class' => 'Ks\UserBundle\Entity\User',
                'property' => "dateSportAndDate",
                //'empty_value' => 'Choix d\'un plan',
                //'empty_data'  => "-1",
                //'preferred_choices' => array(0),
                'required' => false,
                'query_builder' => function(UserRepository $ur) use( $user, $sport ) {
                        return $ur->findCoachingPlanEventsByUserQB( $user, $sport );
                    }
            ));
    }

    public function getName()
    {
        if (!is_null($this->key)) return 'ksCoachingPlanEventsType_' . $this->key;
        else return 'ksCoachingPlanEventsType';
    }
}
