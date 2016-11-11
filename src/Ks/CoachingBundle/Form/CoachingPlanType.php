<?php

namespace Ks\CoachingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Ks\CoachingBundle\Entity\CoachingPlanRepository;

class CoachingPlanType extends AbstractType
{
    private $club;
    private $user;
    
    public function __construct(\Ks\ClubBundle\Entity\Club $club =null, \Ks\UserBundle\Entity\User $user = null)
    {
        $this->club = $club;
        $this->user = $user;
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $club = $this->club;
        $user = $this->user;
        
        if ($club != null )
            $builder->add('name', 'entity', array( 
                'class' => 'Ks\CoachingBundle\Entity\CoachingPlan',
                'property' => "name",
                'required' => false,
                'query_builder' => function(CoachingPlanRepository $cpr) use( $club ) {
                        return $cpr->findCoachingPlanByClubQB($club);
                    }
            ));
        else if ($user != null)
            $builder->add('name', 'entity', array( 
                'class' => 'Ks\CoachingBundle\Entity\CoachingPlan',
                'property' => "name",
                'required' => false,
                'query_builder' => function(CoachingPlanRepository $cpr) use( $user ) {
                        return $cpr->findCoachingPlanByUserQB($user);
                    }
            ));
    }

    public function getName()
    {
        return 'ksCoachingPlanType';
    }
}
