<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserDetailInformationsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('firstname', 'text', array(
                'required' => true,
                'invalid_message' => "Cette valeur n'est pas valide.",
            ))
            ->add('lastname', 'text', array(
                'required' => true,
                'invalid_message' => "Cette valeur n'est pas valide.",
            ))
            ->add('bornedAt', 'date', array(
                'format' => 'yyyy-MM-dd',
                'widget' => "single_text",
                'required' => true,
                'invalid_message' => "Cette valeur n'est pas valide."
            ))
            ->add('weight', 'integer', array(
                'required' => true,
                'invalid_message' => "Cette valeur n'est pas valide.",
            ))
            ->add('height', 'integer', array(
                'required' => true,
                'invalid_message' => "Cette valeur n'est pas valide.",
            ))
            ->add('sexe', 'entity', array(
                'class' => "KsUserBundle:Sexe",
                'required' => true,
                'invalid_message' => "Cette valeur n'est pas valide.",
            ))
            ->add('HRRest', 'integer', array(
                'required' => false,
                'invalid_message' => "Cette valeur n'est pas valide.",
            ))
            ->add('HRMax', 'integer', array(
                'required' => false,
                'invalid_message' => "Cette valeur n'est pas valide.",
            ))
            ->add('VMASpeed', 'number', array(
                'required' => false,
                'invalid_message' => "Cette valeur n'est pas valide.",
            ))
            ;
        
    }

    public function getName()
    {
        return 'UserDetailInformationsType';
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