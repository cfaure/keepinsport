<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ProfileEquipmentType extends AbstractType
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
            ->add('user', 'entity', array(
                'class' => 'Ks\UserBundle\Entity\User',
                'multiple' => false,
                'property' => "username",
                'required' => true,
                'query_builder' => function(\Ks\UserBundle\Entity\UserRepository $ur) use($user) {
                    return $ur->getUserQB($user->getId());
                },
                'attr'   =>  array(
                    'style'     => 'display:none',
                    'class'     => 'user'
                )
            ))
            
            ->add('type', 'entity', array(
                'class'     => 'Ks\UserBundle\Entity\EquipmentType',
                'property'  => "code",
                'label'         => null,
                'attr'   =>  array(
                    'style'     => 'display:none',
                    'class'     => 'equipment_type'
                )
            ))
            ->add('sports', 'entity', array(
                'class' => 'KsActivityBundle:Sport',
                'property'      => 'label',
                'required'      => false,
                'multiple'      => true,
                'attr'   =>  array(
                    'style'     => 'display:none',
                    'class'     => 'sports'
                )
             ))
            ->add('name', 'text', array(
                'required'      => true,
                'attr'   =>  array(
                    'style'     => 'width:auto',
                    'class'     => 'name'
                )
            ))
            ->add('weight', 'number', array(
                'required'      => false,
                'attr'   =>  array(
                    'style'     => 'width:auto',
                    'class'     => 'weight input-mini'
                )
            ))
            ->add('primaryColor', 'text', array(
                'required'      => false,
                'attr'   =>  array(
                    'style'     => 'width:auto',
                    'class'     => 'primaryColor'
                )
            ))
            ->add('secondaryColor', 'text', array(
                'required'      => false,
                'attr'   =>  array(
                    'style'     => 'width:auto',
                    'class'     => 'secondaryColor'
                )
            ))
        ;
    }

    public function getName()
    {
        return 'ProfileEquipmentType';
    }
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\UserBundle\Entity\Equipment'
        );
    }
}