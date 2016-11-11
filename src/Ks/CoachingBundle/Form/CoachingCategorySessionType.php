<?php

namespace Ks\CoachingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Ks\UserBundle\Entity\UserRepository;

class CoachingCategorySessionType extends AbstractType
{
    private $user;
    
    public function __construct(\Ks\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $user = $this->user;
        
        $builder->add('coachingCategory', 'entity', array( 
            'class' => 'Ks\UserBundle\Entity\User',
            'property' => "isCompetitionAndName",
            'required' => false,
            'query_builder' => function(UserRepository $ur) use( $user ) {
                return $ur->findCoachingCategoryQB( null, $user, null );
            }
        ))
        ->add('coachingSession', 'entity', array( 
            'class' => 'Ks\UserBundle\Entity\User',
            'property' => "name",
            'required' => false,
            'query_builder' => function(UserRepository $ur) use( $user ) {
                return $ur->findcoachingSessionQB( null, $user, null);
            }
        ));
    }

    public function getName()
    {
        return 'ksCoachingCategorySessionType';
    }
}
