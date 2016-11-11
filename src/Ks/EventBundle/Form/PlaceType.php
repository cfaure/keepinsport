<?php

namespace Ks\EventBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormValidatorInterface;


class PlaceType extends AbstractType
{
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'placetype';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
	    $builder
            ->add('fullAdress', 'textarea', array( 
                'attr'   =>  array(
                    'class'   => 'full_adress ui-autocomplete-input form-control'),
                )
            )
            //Pays
            ->add('countryCode', 'hidden', array( 
                'attr'   =>  array(
                    'class'   => 'countryCode')
                )
            )
            ->add('countryLabel', 'hidden', array( 
                'attr'   =>  array(
                    'class'   => 'countryLabel')
                )
            )
            //Région
            ->add('regionCode', 'hidden', array( 
                'attr'   =>  array(
                    'class'   => 'regionCode')
                )
            )
            ->add('regionLabel', 'hidden', array( 
                'attr'   =>  array(
                    'class'   => 'regionLabel')
                )
            )
            //Département
            ->add('countyCode', 'hidden', array( 
                'attr'   =>  array(
                    'class'   => 'countyCode')
                )
            )
            ->add('countyLabel', 'hidden', array( 
                'attr'   =>  array(
                    'class'   => 'countyLabel')
                )
            )
            //Ville
            ->add('townCode', 'hidden', array( 
                'attr'   =>  array(
                    'class'   => 'townCode')
                )
            )
            ->add('townLabel', 'hidden', array( 
                'attr'   =>  array(
                    'class'   => 'townLabel')
                )
            )
            ->add('longitude', 'hidden', array( 
                'attr'   =>  array(
                    'class'   => 'longitude')
                )
            )
            ->add('latitude', 'hidden', array( 
                'attr'   =>  array(
                    'class'   => 'latitude')
                )
            );
    }
    
    /*public function buildView(FormView $view, FormInterface $form)
    {
        $view->set('placeholder', $form->getAttribute('placeholder'));
    }*/


    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\EventBundle\Entity\Place'
        );
    }
}