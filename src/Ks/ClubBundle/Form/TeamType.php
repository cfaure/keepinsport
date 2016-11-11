<?php

namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\UserBundle\Entity\UserRepository;
use Ks\ActivityBundle\Entity\SportRepository;


class TeamType extends AbstractType
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
            ->add('label')
            ->add('sport'/*, 'entity', array(
                'class'     => 'Ks\ActivityBundle\Entity\Sport',
                'property'  => "label",
                'query_builder' => function( SportRepository $sr ) use( $club ) {
                    return $sr->findSportsClubQB( $club );
                },
                'required' => false,
            )*/)
            ->add('users', 'entity', array(
                'class'     => 'Ks\UserBundle\Entity\User',
                'property'  => "username",
                'query_builder' => function( UserRepository $ul ) use( $club ) {
                    return $ul->findClubMembersQB( $club );
                },
                'required' => false,
                'multiple' => true,
                'attr'   =>  array(
                    'class'   => 'multiselect users',
                    'style' => array(
                        "height" => "300px",
                        "width" => "500px"
                    )
                )
            ))    
                
        ;
    }

    public function getName()
    {
        return 'ksClub_teamType';
    }
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ClubBundle\Entity\Team'
        );
    }
}
