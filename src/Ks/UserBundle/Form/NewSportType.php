<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\UserBundle\Entity;

class NewSportType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('label')
            /*->add('usersWhoModifiedArticle', 'entity', array(
                                    'class'     => 'Ks\ActivityBundle\Entity\UserModifiesArticle',
                                    'property'  => "content",
                'multiple' => true,))*/
            /*->add('usersWhoModifiedArticle', 'collection', array(
                                    'type' => "textarea",
                                    'allow_add' => true,
                                    'allow_delete' => true,
                                    'prototype' => true,

                                )
            )*/
        ;
    }

    public function getName()
    {
        return 'ks_userbundle_newsporttype';
    }
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
        );
    }
}
