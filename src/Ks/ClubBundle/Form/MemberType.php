<?php

namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('firstname')
            ->add('lastname')
        ;
    }

    public function getName()
    {
        return 'ks_clubbundle_membertype';
    }
}
