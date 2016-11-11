<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\ClubBundle\Entity\TeamRepository;
use Ks\UserBundle\Entity\UserRepository;
use Ks\ClubBundle\Entity\ClubRepository;

class TeamSportSessionType extends AbstractType
{   
    private $sport;
    private $user;
    private $club;
    
    public function __construct(\Ks\ActivityBundle\Entity\Sport $sport, \Ks\UserBundle\Entity\User $user = null, \Ks\ClubBundle\Entity\Club $club = null)
    {
        $this->sport = $sport;
        $this->user = $user;
        $this->club = $club;
    }
    
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'ksActivity_teamSportSessionType';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $user = $this->user;
        $club = $this->club;
        $sport = $this->sport;

	    $builder
            //->setErrorBubbling(true)
            ->add('description')
            /*->add('issuedAt', 'date', array(
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy H:m',
            ))*/
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
                                    'widget' => 'single_text')
            )

            ->add('calories')
            /*->add('ourTeam', 'entity', array(
                                    'class'     => 'Ks\ClubBundle\Entity\Team',
                                    'property'  => "label",
                                    'query_builder' => function(TeamRepository $tr) use($sport, $user) {
                                        return $tr->findMyTeamsQB($sport, $user);
                                    },
                                    'required' => false
                                ))
           ->add('opposingTeam', 'entity', array(   
                                    'class' => 'Ks\ClubBundle\Entity\Team',
                                    'property' => "label",
                                    'query_builder' => function(TeamRepository $tr)  use($sport, $user) {
                                        return $tr->findOthersTeamsQB($sport, $user);
                                    },
                                    'required' => false
                                ))*/
                
                
          /*->add('usersWhoHaveParticipated', 'collection', array(
                                'type' => new UsersWhoHaveParticipatedType(),
                                'allow_add' => true,
                                'allow_delete' => true,
                                'prototype' => true,
                                'label' => 'users Who have Participated'))*/
          //->add('usersWhoHaveParticipated', 'text')
        
            ->add('result', 'entity', array(
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
            )

            ->add('scores', 'collection', array(
                                    //'data_class' => 'Ks\ActivityBundle\Entity\Score',
                                    'type'          => new ScoreType(),
                                    'allow_add'     => true,
                                    'allow_delete'  => true,
                                    'prototype'     => true,
                                    'label'         => 'scores'))

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
                'empty_value' => '- Etat de forme -',
                'preferred_choices' => array(0),
                'required' => false,
                'attr'   =>  array(
                    'style'     => 'display:none'
                )
            ))
        
            ->add('intensity', 'entity', array(
                'class'     => 'Ks\ActivityBundle\Entity\Intensity',
                'empty_value' => 'intensity.label* -',
                'preferred_choices' => array(0),
                'required' => false,
                'attr'   =>  array(
                    'class'   => 'intensity',
                    'style'     => 'display:none'
                    )
                )
            )
                
            ->add('isPublic', 'checkbox', array(
                'attr'   =>  array(
                    'class'   => 'isPublic',
                    )
                )
            )
            ;
        
        if( is_object( $user ) ) {
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
                $builder->add('usersWhoHaveParticipated', 'entity', array(   
                                            'class' => 'Ks\UserBundle\Entity\User',
                                            'multiple' => true,
                                            'property' => "username",
                                            'query_builder' => function(UserRepository $ur) use($user) {
                                                return $ur->getFriendsAndMeQB($user, true);
                                            }
                                            ))
             ->add('opponentsWhoHaveParticipated', 'entity', array(   
                                            'class' => 'Ks\UserBundle\Entity\User',
                                            'multiple' => true,
                                            'property' => "username",
                                            'query_builder' => function(UserRepository $ur) use($user) {
                                                return $ur->getFriendsAndMeQB($user);
                                            }));
            }
            
            //S'il est adhérent de clubs
            if( count( $user->getClubs() ) > 0 ) {
                $builder->add('club', 'entity', 
                    array(   
                            'class' => 'Ks\ClubBundle\Entity\Club',
                            'multiple' => false,
                            'property' => "name",
                            'empty_value' => '- Club -',
                            'preferred_choices' => array(0),
                            'required' => false,
                            'query_builder' => function(ClubRepository $cr) use($user) {
                                return $cr->findMyClubsQB($user);
                            }
                    )
                );
            }
        
        } elseif( is_object( $club ) ) {
            $builder->add('usersWhoHaveParticipated', 'entity', array(   
                'class' => 'Ks\UserBundle\Entity\User',
                'multiple' => true,
                'property' => "username",
                'query_builder' => function(UserRepository $ur) use( $club ) {
                    return $ur->findClubMembersQB( $club );
                }))
          ->add('opponentsWhoHaveParticipated', 'entity', array(   
                'class' => 'Ks\UserBundle\Entity\User',
                'multiple' => true,
                'property' => "username",
                'query_builder' => function(UserRepository $ur) use( $club ) {
                    return $ur->findClubMembersQB( $club );
                }));
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
                'property'      => "label",
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
                
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('Ks\ActivityBundle\Entity\ActivitySessionTeamSport', 'teamSport')
        ));
    }

    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ActivityBundle\Entity\ActivitySessionTeamSport',     
        );
    }
}
