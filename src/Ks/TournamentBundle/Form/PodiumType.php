<?php

namespace Ks\TournamentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\UserBundle\Entity\UserRepository;

use Ks\ActivityBundle\Entity\SportRepository;

class PodiumType extends AbstractType
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
            ->add('firstUser', 'entity', array( 
                'class' => 'Ks\UserBundle\Entity\User',
                'property' => "username",
                'empty_value' => '-- Premier --',
                'query_builder' => function( UserRepository $ul ) use( $club ) {
                    return $ul->findClubMembersQB( $club );
                },
                'preferred_choices' => array(0),
                'multiple' => false,
                'required' => false,
                'attr'   =>  array(
                    'class'   => 'firstUser',
                )
            )) 
            ->add('secondUser', 'entity', array( 
                'class' => 'Ks\UserBundle\Entity\User',
                'property' => "username",
                'multiple' => false,
                'empty_value' => '-- Second --',
                'query_builder' => function( UserRepository $ul ) use( $club ) {
                    return $ul->findClubMembersQB( $club );
                },
                'preferred_choices' => array(0),
                'required' => false,
                'attr'   =>  array(
                    'class'   => 'secondUser',
                )
            )) 
            ->add('thirdUser', 'entity', array( 
                'class' => 'Ks\UserBundle\Entity\User',
                'property' => "username",
                'multiple' => false,
                'empty_value' => '-- Troisième --',
                'query_builder' => function( UserRepository $ul ) use( $club ) {
                    return $ul->findClubMembersQB( $club );
                },
                'preferred_choices' => array(0),
                'required' => false,
                'attr'   =>  array(
                    'class'   => 'thirdUser',
                )
            )) 
            ->add('firstUsername', "text", array(
                'attr'   =>  array(
                    'class'   => 'firstUsername',
                )
            )) 
            ->add('secondUsername', "text", array(
                'attr'   =>  array(
                    'class'   => 'secondUsername',
                )
            ))                 
            ->add('thirdUsername', "text", array(
                'attr'   =>  array(
                    'class'   => 'thirdUsername',
                )
            ))                 
        ;
    }

    public function getName()
    {
        return 'ksPodiumType';
    }
}
