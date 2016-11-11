<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserHasServicesType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('is_active')
            ->add('token')
            ->add('userDetail')
            ->add('service')
        ;
    }

    public function getName()
    {
        return 'ks_userbundle_userhasservicestype';
    }
}
