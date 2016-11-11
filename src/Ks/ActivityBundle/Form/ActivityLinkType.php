<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ActivityLinkType extends AbstractType
{
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'ks_activitybundle_activitylinktype';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
	    $builder
            ->add('label', 'text')
            ->add('description', 'textarea', array('required' => false))
            ->add('photo', 'text')
            ->add('link', 'url')
            ->add('viewLink', 'url')
            ->add('linkDescription', 'textarea');
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