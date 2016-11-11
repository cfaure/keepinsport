<?php

namespace Ks\UserBundle\Form\UserType;

//use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class UserType extends BaseType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        /*$builder
            ->add('email')
            ->add('password')
        ;*/
        
        parent::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'ks_user_registration';
    }
}
