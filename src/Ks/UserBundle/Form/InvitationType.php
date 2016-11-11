<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class InvitationType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('email_guest', 'email')
            ->add('pending_friend_request')
            ->add('userInviting')
        ;
    }

    public function getName()
    {
        return 'ks_userbundle_invitationtype';
    }
}
