<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\ActivityBundle\Entity\SportRepository;

class ActivityTypeChoiceType extends AbstractType
{
    
    /**
     * @var integer $sports
     *
     * 
     */
    //private $sports;
    
    public function __construct()
    {
        //$this->sports = $sports;
    }
    
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'ks_activitybundle_activityTypeChoice';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
	    $builder
            /*->add('sport', 'choice', array(   'choices' => $this->sports,
                                    'multiple' => false,
                                    'expanded' => false,
                                    //'preferred_choices' => array(2),
                                    'empty_value' => '- Choisissez un sport -',
                                    'empty_data'  => -1));*/
        ->add('activity_type', 'choice', array( 'choices' => array("ActivityStatus" => "statuts", "ActivitySessionEnduranceOnEarth" => "Session d'endurance", "ActivitySessionTeamSport" => "Session de sport collectifs"),
                                               // 'multiple' => false, 
                                                'expanded' => false, 
                                                'empty_value' => 'Toutes',
                                                //'empty_data' => 0,
                                                'preferred_choices' => array(0),
                                                'required' => false
                                                )
                    );
    }

    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ActivityBundle\Entity\Activity'
        );
    }
}