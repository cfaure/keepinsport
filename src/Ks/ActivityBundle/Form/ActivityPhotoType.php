<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ActivityPhotoType extends AbstractType
{
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'ks_activitybundle_phototype';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
	    $builder
            ->add('description', 'textarea', array('required' => false))
            /*->add('place', new \Ks\EventBundle\Form\PlaceType(), array(
                "required" => false
            ))*/;
    }

    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ActivityBundle\Entity\ActivityStatus'
        );
    }
}