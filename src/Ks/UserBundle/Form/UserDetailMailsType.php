<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserDetailMailsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('receivesDailyEmail', 'choice', array(
                'choices' => array(0 => "Non", 1 => "Oui"),
                'preferred_choices' => array(1),
                'expanded' => true,
                'attr'   =>  array(
                    'class'   => 'receivesDailyEmail',
                    'style'  => 'display:none'
                )
            )); 
    }

    public function getName()
    {
        return 'UserDetailMailsType';
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