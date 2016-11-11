<?php

namespace Ks\NotificationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class NotificationType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('type')
            ->add('message')
            ->add('isRead', 'checkbox',array('required' => false))
            ->add('createdAt')
            ->add('owner')
            ->add('fromUser')
        ;
    }

    public function getName()
    {
        return 'ks_notificationbundle_notificationtype';
    }
}
