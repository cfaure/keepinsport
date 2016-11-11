<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserDetailSportsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('description', 'textarea', array('required' => false))
            ->add('sports', 'entity', array(
                'class' => 'KsActivityBundle:Sport',
                'property'     => 'label',
                'required' => false,
                'multiple'     => true
             )); 
    }

    public function getName()
    {
        return 'UserDetailSportsType';
    }   
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\UserBundle\Entity\UserDetail'
        );
    }
}