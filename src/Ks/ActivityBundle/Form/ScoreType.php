<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ScoreType extends AbstractType
{
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'ks_activitybundle_scoretype';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
	    $builder
            ->add('score1', 'text', array('label' => " "))
            ->add('score2', 'text', array('label' => " "))
            ->add('roundOrder', 'hidden');
    }

    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ActivityBundle\Entity\Score'
        );
    }
}