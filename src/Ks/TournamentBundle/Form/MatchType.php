<?php

namespace Ks\TournamentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\UserBundle\Entity\UserRepository;

use Ks\ActivityBundle\Entity\SportRepository;

class MatchType extends AbstractType
{
    private $club;
    
    public function __construct(\Ks\ClubBundle\Entity\Club $club)
    {
        $this->club = $club;
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $club = $this->club;
        
        $builder
            ->add('user1', 'entity', array( 
                'class' => 'Ks\UserBundle\Entity\User',
                'property' => "username",
                'empty_value' => '-- sportif 1 --',
                'query_builder' => function( UserRepository $ul ) use( $club ) {
                    return $ul->findClubMembersQB( $club );
                },
                'preferred_choices' => array(0),
                'multiple' => false,
                'required' => false,
                'attr'   =>  array(
                    'class'   => 'user1',
                )
            )) 
            ->add('username1', "text", array(
                'attr'   =>  array(
                    'class'   => 'username1',
                )
            )) 
            ->add('user1Won', "checkbox", array(
                'attr'   =>  array(
                    'class'   => 'user1Won',
                )
            ))         
            ->add('user2', 'entity', array( 
                'class' => 'Ks\UserBundle\Entity\User',
                'property' => "username",
                'multiple' => false,
                'empty_value' => '-- sportif 2 --',
                'query_builder' => function( UserRepository $ul ) use( $club ) {
                    return $ul->findClubMembersQB( $club );
                },
                'preferred_choices' => array(0),
                'required' => false,
                'attr'   =>  array(
                    'class'   => 'user2',
                )
            )) 
            ->add('username2', "text", array(
                'attr'   =>  array(
                    'class'   => 'username2',
                )
            )) 
            ->add('user2Won', "checkbox", array(
                'attr'   =>  array(
                    'class'   => 'user2Won',
                )
            )) 
                        
            ->add('score', "text", array(
                'attr'   =>  array(
                    'class'   => 'score',
                )
            )) 
                
        ;
    }

    public function getName()
    {
        return 'ksMatchType';
    }
}
