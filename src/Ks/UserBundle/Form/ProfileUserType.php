<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ProfileUserType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('email', "email", array('required' => true))
            ->add('userDetail', new \Ks\UserBundle\Form\UserDetailType())
            ->add('mailNotifications', "collection", array(
                'type' => new \Ks\NotificationBundle\Form\UserReceivesMailNotificationsType(),
                'required' => false
            ))
            
            /*->add('mailNotifications', 'entity', array(
                'class' => 'KsNotificationBundle:NotificationType',
                'property'     => 'name',
                'required' => false,
                'multiple' => true
                )
            )*/;
    }

    public function getName()
    {
        return 'user_profileusertype';
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