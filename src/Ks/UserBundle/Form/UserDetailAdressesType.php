<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserDetailAdressesType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('country_code', 'text', array('required' => false))
            ->add('country_area', 'text', array('required' => false))
            ->add('town', 'text', array('required' => false))
            ->add('longitude', 'text', array('required' => false))
            ->add('latitude', 'text', array('required' => false))    
            ->add('full_address','text', array(
                'required' => false, 
                'attr'   =>  array(
                    'class'   => 'input-xlarge'
                )
            )); 
    }

    public function getName()
    {
        return 'UserDetailAdressesType';
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