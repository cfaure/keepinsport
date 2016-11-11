<?php

namespace Ks\NotificationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserReceivesMailNotificationsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('type', 'entity', array(
                'class' => 'KsNotificationBundle:NotificationType',
                'property'     => 'name',
                'required' => false,
                'multiple'     => false
             ))
            ->add('wantsReceive', 'choice', array(
                'choices' => array(0 => "Non", 1 => "Oui"),
                'expanded' => true
            ));
        ;
    }

    public function getName()
    {
        return 'userReceivesMailNotificationsType';
    }
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\NotificationBundle\Entity\UserReceivesMailNotifications'
        );
    }
}
