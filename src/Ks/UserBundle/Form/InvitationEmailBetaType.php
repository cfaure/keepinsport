<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class InvitationEmailBetaType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('could_invit')
            ->add('nomber_invitation')
            ->add('userInviting')
        ;
    }

    public function getName()
    {
        return 'ks_userbundle_invitationemailbetatype';
    }
}
