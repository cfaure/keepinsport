<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class TrophyCategoryType extends AbstractType
{
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'ks_activitybundle_trophycategorytype';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
	    $builder
            ->add('label', 'entity', array(   
                                    'class' => 'Ks\TrophyBundle\Entity\TrophyCategory',
                                    'property' => "label",
                                    'empty_value' => '- Toutes -',
                                    'preferred_choices' => array(0),
                                    'required' => false));
    }

    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\TrophyBundle\Entity\TrophyCategory'
        );
    }
}