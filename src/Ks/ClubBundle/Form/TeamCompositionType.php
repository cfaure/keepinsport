<?php

namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\UserBundle\Entity\UserRepository;


class TeamCompositionType extends AbstractType
{
    private $team;
    
    public function __construct(\Ks\ClubBundle\Entity\Team $team)
    {
        $this->team = $team;
    }
    
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $team = $this->team;
        
        $builder
            ->add('name')
           ->add('date', 'datetime', array(
                'date_format' => 'dd/MM/yyyy',
                'date_widget' => "single_text",
                'time_widget' => "single_text",
                'attr'   =>  array(
                    'class'   => 'issuedAt',
                )
            ))  
            ->add('users', 'entity', array(
                'class'     => 'Ks\UserBundle\Entity\User',
                'property'  => "username",
                'query_builder' => function( UserRepository $ul ) use( $team ) {
                    return $ul->findTeamUsersQB( $team );
                },
                'required' => false,
                'multiple' => true,
                'attr'   =>  array(
                    'class'   => 'multiselect users',
                    'style' => array(
                        //"height" => "300px",
                        //"width" => "500px"
                    )
                )
            ))      
        ;
    }

    public function getName()
    {
        return 'ksClub_teamCompositionType';
    }
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ClubBundle\Entity\TeamComposition'
        );
    }
}
