<?php

namespace Ks\EquipmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\EquipmentBundle\Entity;

class MyEquipmentsType extends AbstractType
{
    private $user;

    public function __construct(\Ks\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }
    
    public function getName()
    {
        return 'myEquipments';
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $user = $this->user;
        
        $builder->add('equipments', 'entity', array(
            'class'         => 'Ks\UserBundle\Entity\Equipment',
            'property'      => "name",
            'multiple'      => false,
            'required'      => false,
            'label'         => false,
            'query_builder' => function(\Ks\UserBundle\Entity\EquipmentRepository $er) use($user) {
                return $er->getUserEquipementsQB($user->getId());
            },
            'attr'   =>  array('style'     => 'width:auto', 'class'   => 'equipments')
        ));
    }

    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
        );
    }
}
