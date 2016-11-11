<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserDetailAvatarsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('image', 'file', array('required' => false)); 
    }

    public function getName()
    {
        return 'UserDetailAvatarsType';
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