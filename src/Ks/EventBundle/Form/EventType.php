<?php

namespace Ks\EventBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Ks\ActivityBundle\Entity\SportRepository;
use Ks\UserBundle\Entity\UserRepository;
use Ks\CoachingBundle\Entity\CoachingPlanRepository;

class EventType extends AbstractType
{
    private $club;
    private $user;
    private $plan;
    private $context;
    
    public function __construct(\Ks\ClubBundle\Entity\Club $club = null, \Ks\UserBundle\Entity\User $user = null, \Ks\CoachingBundle\Entity\CoachingPlan $plan = null, $context=null)
    {
        $this->club     = $club;
        $this->user     = $user;
        $this->plan     = $plan;
        $this->context  = $context;
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $club       = $this->club;
        $user       = $this->user;
        $plan       = $this->plan;
        $context    = $this->context;
        
        $builder
            ->add('name', 'text', array('required' => false))
            ->add('content', 'textarea', array('required' => false))
            ->add('startDate', 'datetime', array(
                'date_format' => 'dd/MM/yyyy',
                'date_widget' => "single_text",
                'time_widget' => "single_text",
                'attr'   =>  array(
                    'class'   => 'startDate',
                )
            ))
            ->add('endDate', 'datetime', array(
                'date_format' => 'dd/MM/yyyy',
                'date_widget' => "single_text",
                'time_widget' => "single_text",
                'attr'   =>  array(
                    'class'   => 'endDate',
                )
            ))
            
            ->add('isWarning')  
            ->add('typeEvent', 'entity', array( 
                'class' => 'Ks\EventBundle\Entity\TypeEvent',
                'property' => "nom_type",
                'empty_value' => 'event_without_type',
                'preferred_choices' => array(0),
                'required' => false,
            )); 
        
        if ($club != null or $user != null) {
            $builder->add('difficulty', 'entity', array(
                'class'     => 'Ks\ActivityBundle\Entity\Intensity',
                'property'  => "code",
                'empty_value' => 'intensity.label* -',
                'preferred_choices' => array(0),
                'required' => false,
                'attr'   =>  array(
                    'class'     => 'difficulty',
                    'style'     => 'display:none'
                    )
                )
            )
            ->add('coachingPlan', 'entity', array( 
                'class' => 'Ks\UserBundle\Entity\User',
                'property' => "name",
                //'empty_value' => 'Choix d\'un plan',
                //'empty_data'  => "-1",
                //'preferred_choices' => array(0),
                'required' => false,
                'query_builder' => function(UserRepository $ur) use( $club, $user, $context ) {
                        return $ur->findCoachingPlanQB( $club, $user, $context );
                    }
            ))
            ->add('coachingCategory', 'entity', array( 
                'class' => 'Ks\UserBundle\Entity\User',
                'property' => "isCompetitionAndName",
                'required' => false,
                'query_builder' => function(UserRepository $ur) use( $club, $user, $plan ) {
                        return $ur->findCoachingCategoryQB( $club, $user, $plan );
                    }
            ))
            ->add('coachingSession', 'entity', array( 
                'class' => 'Ks\UserBundle\Entity\User',
                'property' => "name",
                'required' => false,
                'query_builder' => function(UserRepository $ur) use( $club, $user, $plan ) {
                        return $ur->findcoachingSessionQB( $club, $user, $plan );
                    }
            ));
        }
        
        $builder->add('isAllDay') 
            ->add('isPublic') 
            ->add('place', new \Ks\EventBundle\Form\PlaceType(), array(
                "required" => false
            ))
            ->add('sport', 'entity', array(   
                'class' => 'Ks\ActivityBundle\Entity\Sport',
                'property' => "label",
                'empty_value' => '- Sport -',
                'empty_data'  => "",
                'required' => false,
                'query_builder' => function(SportRepository $sr) {
                    return $sr->findSportsQB();
                }
            ));
        
        if ($club != null) {
            $builder
                ->add('maxParticipations', 'integer')
                ->add('usersParticipations', 'entity', array(   
                    'class' => 'Ks\UserBundle\Entity\User',
                    'multiple' => true,
                    'property' => "username",
                    'query_builder' => function(UserRepository $ur) use( $club ) {
                            return $ur->findClubMembersQB( $club );
                        }
                ));
        }
    }

    public function getName()
    {
        return 'ksEventType';
    }
}
