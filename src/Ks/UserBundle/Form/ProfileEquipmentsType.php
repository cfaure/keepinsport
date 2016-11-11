<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ProfileEquipmentsType extends AbstractType
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
            ->add('equipments', 'collection', array(
                    //'data_class' => 'Ks\ActivityBundle\Entity\Score',
                    'type'          => new ProfileEquipmentType($user),
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'prototype'     => true,
                    'by_reference'  => false,
                    //'label'         => 'equipement'
                )
            );
    }

    public function getName()
    {
        return 'ProfileEquipmentsType';
    }
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\UserBundle\Entity\User'
        );
    }
}