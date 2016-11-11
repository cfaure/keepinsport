<?php

namespace Ks\EquipmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\EquipmentBundle\Entity;

class NewEquipmentType extends AbstractType
{
    private $context;
    
    public function __construct($context =null)
    {
        $this->context = $context;
        
    }

    /**
     * Context = Multi => pour permettre de choisir plusieurs sports ou tous les sports (page comparison et statistiques du dashboadBundle
     * @return string 
     */
    public function getName()
    {
        if ($this->context == "menu") return 'equipmentType_fromMenu';
        else return 'equipmentType';
    }

    //'ks_equipmentbundle_newequipmenttype';

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('type', 'entity', array(
                'class'     => 'Ks\UserBundle\Entity\EquipmentType',
                'property'  => "code",
                'label'         => null,
                'attr'   =>  array(
                    'style'     => 'width:auto',
                    'class'     => 'equipment_type'
                )
            ))
            ->add('brand', 'text', array(
                'required'      => true,
                'attr'   =>  array(
                    'style'     => 'width:auto',
                    'class'     => 'brand'
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
            /*->add('primaryColor', 'text', array(
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
            ))*/
            ->add('isByDefault', 'checkbox')
        ;
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
