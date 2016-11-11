<?php

namespace Ks\NotificationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserReceivesDailyEmailsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            
            ->add('receivesDailyEmail', 'choice', array(
                'choices' => array(0 => "Non", 1 => "Oui"),
                'expanded' => true
            ));
        ;
    }

    public function getName()
    {
        return 'userReceivesDailyEmailsType';
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
