<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UsersWhoHaveParticipatedType extends AbstractType
{
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'ks_activitybundle_userwhohaveparticipatedtype';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
	    $builder
            ->add('id', 'entity', array(   
                                    'class' => 'Ks\UserBundle\Entity\User',
                                    'property' => "id",
                                    'empty_value' => '- Toutes -',
                                    'preferred_choices' => array(0),
                                    'required' => false));
    }

    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ActivityBundle\Entity\ActivitySession'
        );
    }
}