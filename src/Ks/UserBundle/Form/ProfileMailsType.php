<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ProfileMailsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('userDetail', new \Ks\UserBundle\Form\UserDetailMailsType())
            ->add('mailNotifications', "collection", array(
                'type' => new \Ks\NotificationBundle\Form\UserReceivesMailNotificationsType(),
                'required' => false,
            ));
    }

    public function getName()
    {
        return 'ProfileMailsType';
    }
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\UserBundle\Entity\User'
        );
    }
}