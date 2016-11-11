<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserDetailType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('image', 'file', array('required' => false))
            ->add('country_code', 'text', array('required' => false))
            ->add('firstname', 'text', array('required' => true))
            ->add('lastname', 'text', array('required' => true))
            ->add('bornedAt','birthday', array('widget' => 'text', 'required' => false))
            ->add('description', 'textarea', array('required' => false))
            ->add('country_area', 'text', array('required' => false))
            ->add('town', 'text', array('required' => false))
            ->add('longitude', 'text', array('required' => false))
            ->add('latitude', 'text', array('required' => false))
            ->add('weight', 'integer', array('required' => false))
            ->add('sexe', 'entity', array('class' => "KsUserBundle:Sexe"))    
            ->add('height', 'integer', array('required' => false))
            ->add('sports', 'entity', array(
                'class' => 'KsActivityBundle:Sport',
                'property'     => 'label',
                'required' => false,
                'multiple'     => true
             ))
            ->add('receivesDailyEmail', 'choice', array(
                'choices' => array(0 => "Non", 1 => "Oui"),
                'preferred_choices' => array(1),
                'expanded' => true
            ))
                
            //->add('sports')
                /*->add('sports', 'choice', array(
                    'required' => false,
                ))*/
            //->add('sports', 'choice', array( 'multiple' => true, 'required' => false))   
            
            /*    ->add('sports', 'choice', array(
                    'choices'   => array(
                        'morning'   => 'Morning',
                        'afternoon' => 'Afternoon',
                        'evening'   => 'Evening',
                    ),
                    'multiple'  => true,
            ))*/
                
                
                
                
            ->add('full_address','text', array('required' => false, 
                    'attr'   =>  array(
                        'class'   => 'input-xlarge'
                    )
                ))
                ; 
    }

    public function getName()
    {
        return 'ks_userbundle_userdetailtype';
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