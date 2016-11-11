<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\ActivityBundle\Entity;

class ArticleEventType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('place', new \Ks\EventBundle\Form\PlaceType(), array("required" => false))
            ->add('isPublic', 'checkbox', array(
                'attr'   =>  array(
                    'class'   => 'isPublic',
                    )
                )
            )
        ;
    }

    public function getName()
    {
        return 'ks_activitybundle_articleEventType';
    }
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ActivityBundle\Entity\Activity'
        );
    }
}
