<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserHasSportFrequencyType extends AbstractType
{   
    public function __construct()
    {
    }
    
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'ksUser_userHasSportFrequencyType';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('isScheduledOnMon', 'checkbox', array('attr' => array('class' => 'isScheduledOnMon')))
            ->add('isScheduledOnTue', 'checkbox', array('attr' => array('class' => 'isScheduledOnTue')))
            ->add('isScheduledOnWed', 'checkbox', array('attr' => array('class' => 'isScheduledOnWed')))
            ->add('isScheduledOnThu', 'checkbox', array('attr' => array('class' => 'isScheduledOnThu')))
            ->add('isScheduledOnFri', 'checkbox', array('attr' => array('class' => 'isScheduledOnFri')))
            ->add('isScheduledOnSat', 'checkbox', array('attr' => array('class' => 'isScheduledOnSat')))
            ->add('isScheduledOnSun', 'checkbox', array('attr' => array('class' => 'isScheduledOnSun')))
            ->add('mailTime', 'time', array(
                                    'input'  => 'datetime',
                                    'widget' => 'single_text')
            );  
    }

    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\UserBundle\Entity\UserHasSportFrequency'
        );
    }
    
}