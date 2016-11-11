<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\UserBundle\Entity\UserRepository;
use Ks\ClubBundle\Entity\ClubRepository;
use Ks\ActivityBundle\Entity\SportRepository;

class ActivitySessionType extends AbstractType
{   
    private $sport;
    private $user;
    private $club;
    private $event;

    public function __construct(\Ks\ActivityBundle\Entity\Sport $sport, \Ks\UserBundle\Entity\User $user = null, \Ks\ClubBundle\Entity\Club $club = null, \Ks\EventBundle\Entity\Event $event=null)
    {
        $this->sport    = $sport;
        $this->user     = $user;
        $this->club     = $club;
        $this->event    = $event;
    }
    
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'ksActivity_activitySessionType';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $user   = $this->user;
        $club   = $this->club;
        $sport  = $this->sport;
        $event  = $this->event;
        
        $builder
            ->add('description') 
            ->add('issuedAt', 'datetime', array(
                'date_format' => 'dd/MM/yyyy',
                'date_widget' => "single_text",
                'time_widget' => "single_text",
                'attr'   =>  array(
                    'class'   => 'issuedAt',
                )
            ))          
            ->add('duration', 'time', array(
                'input'  => 'datetime',
                'with_seconds' => true,
                'widget' => 'single_text')
            )

           ->add('calories')
            ->add('place', new \Ks\EventBundle\Form\PlaceType(), array(
                "required" => false
            ))
                
            ->add('wasOfficial', 'choice', array(
                'choices' => array(2 => "no", 1 => "yes"),
                'multiple' => false,
                'expanded' => false,
                'preferred_choices' => array(0),
                'empty_value' => '- Competition* -',
                'empty_data'  => 0,
                'required' => false,
                'attr'   =>  array(
                    'style'     => 'display:none'
                )
            ))
                
            ->add('stateOfHealth', 'entity', array(
                'class'     => 'Ks\ActivityBundle\Entity\StateOfHealth',
                'property'  => "code",
                //'empty_value' => 'stateOfHealth.name* -',
                'empty_value' => 'so_so',
                'preferred_choices' => array(0),
                'required' => false,
                'attr'   =>  array(
                    'class'     => 'stateOfHealth',
                    'style'     => 'display:none'
                )
            ))
        
            ->add('intensity', 'entity', array(
                'class'     => 'Ks\ActivityBundle\Entity\Intensity',
                'property'  => "code",
                'empty_value' => 'intensity.label* -',
                'preferred_choices' => array(0),
                'required' => false,
                'attr'   =>  array(
                    'class'     => 'intensity',
                    'style'     => 'display:none'
                    )
                )
            )
                
            ->add('isPublic', 'checkbox', array(
                'attr'   =>  array(
                    'class'   => 'isPublic',
                    )
                )
            );
        
        if( is_object( $user ) ) {
            $builder->add('achievement')
                    ->add('event', 'entity', array( 
                        'class' => 'Ks\UserBundle\Entity\User',
                        'property' => "dateSportAndDate",
                        //'empty_value' => 'Choix d\'un plan',
                        //'empty_data'  => "-1",
                        //'preferred_choices' => array(0),
                        'required' => false,
                        'query_builder' => function(UserRepository $ur) use($user, $sport, $event) {
                                return $ur->findCoachingPlanEventsByUserQB($user, $sport, $event);
                            }
                    ));
            
            $hasFriends = false;

            foreach($user->getFriendsWithMe() as $userHasFriends) {
                if ($userHasFriends->getPendingFriendRequest() == 0 ) {
                    $hasFriends = true;
                    break;
                }
            }

            if ( ! $hasFriends ) {
                foreach($user->getMyFriends() as $userHasFriends) {
                    if ($userHasFriends->getPendingFriendRequest() == 0 ) {
                        $hasFriends = true;
                        break;
                    }
                }
            }

            if ( $hasFriends ) {
            $builder->add('usersWhoHaveParticipated', 'entity', 
                    array(   
                            'class' => 'Ks\UserBundle\Entity\User',
                            'multiple' => true,
                            'property' => "username",
                            'query_builder' => function(UserRepository $ur) use($user) {
                                return $ur->getFriendsAndMeQB($user, false);
                            }
                        ));
            }

            //S'il est adhérent de clubs
            if( count( $user->getClubs() ) > 0 ) {
                $builder->add('club', 'entity', 
                    array(   
                            'class' => 'Ks\ClubBundle\Entity\Club',
                            'multiple' => false,
                            'property' => "name",
                            'empty_value' => '-- Club --',
                            'preferred_choices' => array(0),
                            'required' => false,
                            'query_builder' => function(ClubRepository $cr) use($user) {
                                return $cr->findMyClubsQB($user);
                            }
                    )
                );
            }
            
        } elseif( is_object( $club ) ) {
            $builder->add('usersWhoHaveParticipated', 'entity', 
                array(   
                    'class' => 'Ks\UserBundle\Entity\User',
                    'multiple' => true,
                    'property' => "username",
                    'query_builder' => function(UserRepository $ur) use( $club ) {
                        return $ur->findClubMembersQB( $club );
                    }
                )
            );
        }
        
        $builder->add('tournament', 'entity', 
            array(   
                    'class' => 'Ks\TournamentBundle\Entity\Tournament',
                    'multiple' => false,
                    'property' => "title",
                    'empty_value' => '-- Tournoi --',
                    'preferred_choices' => array(0),
                    'required' => false,
                    'query_builder' => function(\Ks\TournamentBundle\Entity\TournamentRepository $tr) use($user) {
                        return $tr->getTournamentsByMyClubQB($user->getId());
                    }
            )
        );
            
        $builder->add('equipments', 'entity', array(
                'class'         => 'Ks\UserBundle\Entity\Equipment',
                'property'      => "name",
                'multiple'      => true,
                'required' => false,
                'query_builder' => function(\Ks\UserBundle\Entity\EquipmentRepository $er) use($user, $sport) {
                    return $er->getUserEquipementsBySportQB($user->getId(), $sport->getId());
                },
                'attr'   =>  array(
                    'class'   => 'equipments',
                )
            )
        );
               
        if( $sport->getSportsGroundsEnabled() ) {
            $builder->add('sportGround', 'entity', array(
                    'class'         => 'Ks\ActivityBundle\Entity\SportGround',
                    'empty_value' => '-- Terrain --',
                    'preferred_choices' => array(0),
                    'property'      => "label",
                    'multiple'      => false,
                    'required'      => false,
                    /*'query_builder' => function(\Ks\ActivityBundle\Entity\SportRepository $er) use( $sport ) {
                        return $sgr->getSportsGroundsBySportQB( $sport->getId() );
                    },*/
                    'attr'          =>  array(
                        'style'     => 'display:none',
                        'class'   => 'sportsGrounds'
                    )
                )
            );
        }
                
        switch($sport->getSportType()->getLabel()) {
            case "endurance" :
                $builder
                    ->add('distance')
                    /*->add('elevationMin')
                    ->add('elevationMax')*/
                    ->add('elevationGain')
                    ->add('elevationLost');
                break;
            case "endurance_under_water" :
                $builder
                    ->add('distance');
                    /*->add('depthGain')
                    ->add('depthMax');*/
                break;
            case "team_sport":
                if( is_object( $user ) && $hasFriends )  {
                    $builder->add('opponentsWhoHaveParticipated', 'entity', array(   
                        'class' => 'Ks\UserBundle\Entity\User',
                        'multiple' => true,
                        'property' => "username",
                        'query_builder' => function(UserRepository $ur) use($user) {
                            return $ur->getFriendsAndMeQB($user);
                        })
                    );
                } elseif( is_object( $club ) ) {
                    $builder->add('opponentsWhoHaveParticipated', 'entity', array(   
                        'class' => 'Ks\UserBundle\Entity\User',
                        'multiple' => true,
                        'property' => "username",
                        'query_builder' => function(UserRepository $ur) use( $club ) {
                            return $ur->findClubMembersQB( $club );
                        })
                    );
                }
                $builder->add('result', 'entity', array(
                    'class'     => 'Ks\ActivityBundle\Entity\Result',
                    'property'  => "code",
                    //'expanded'  => true,
                    'empty_value' => '- Résultat* -',
                    'preferred_choices' => array(0),
                    'required' => false,
                    'attr'   =>  array(
                        'style'     => 'display:none',
                        'class'     => 'activityResult'
                        )
                    )
                );

                $builder->add('scores', 'collection', array(
                    //'data_class' => 'Ks\ActivityBundle\Entity\Score',
                    'type'          => new ScoreType(),
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'prototype'     => true,
                    'label'         => 'scores',
                    'attr'   =>  array(
                        'class'     => 'scores'
                        )
                    )
                );
                break;
        }

    }

    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ActivityBundle\Entity\ActivitySession'
        );
    }
    
}