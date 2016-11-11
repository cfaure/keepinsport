<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class NetaffiliationsType extends AbstractType
{
    private $user;
    
    public function __construct( \Ks\UserBundle\Entity\User $user )
    {
        $this->user = $user;
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $user = $this->user;
        
        $builder
            ->add('netaffiliations', 'collection', array(
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                //'prototype_name' => '__sports__',
                'by_reference' => false,
                'type' => new \Ks\UserBundle\Form\NetaffiliationType($user),
                
            ))
        ;
        
        parent::buildForm($builder, $options);
    }

    public function getName() {
        return 'ks_user_netaffiliation';
    }
    
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\UserBundle\Entity\User'
        );
    }
}
