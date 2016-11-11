<?php

namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\ClubBundle\Entity\TeamCompositionRepository;

class TeamCompositionChoiceType extends AbstractType
{
    private $team;
    
    public function __construct( \Ks\ClubBundle\Entity\Team $team )
    {
        $this->team = $team;
    }
    
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'ksTeam_teamCompositionChoiceType';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $team = $this->team;
        
	    $builder
        ->add('teamCompositions', 'entity', array(   
                                    'class' => 'Ks\ClubBundle\Entity\TeamComposition',
                                    'property' => "name",
                                    "multiple" => true,
                                    'query_builder' => function( TeamCompositionRepository $tcr ) use( $team ) {
                                        return $tcr->findTeamCompositionsQB( $team );
                                    }
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
            'data_class' => 'Ks\ClubBundle\Entity\Team'
        );
    }
}