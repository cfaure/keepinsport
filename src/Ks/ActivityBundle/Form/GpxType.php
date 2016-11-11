<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class GpxType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('file', 'file', array("required" => false));
    }

    public function getName()
    {
        return 'ks_activitybundle_gpxtype';
    }   
}