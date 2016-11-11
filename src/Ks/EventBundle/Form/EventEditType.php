<?php

namespace Ks\EventBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class EventEditType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('content')
            ->add('startDate', 'date', array(
                'widget' => 'single_text',
                //'input' => 'datetime',
                //'with_seconds' => false,
                'format' => 'dd/MM/yyyy H:m',
                /*'empty_value' => array('year' => 'Année', 'month' => 'Mois', 'day' => 'Jour'),*/
                //'pattern' => "{{ day }}/{{ month }}/{{ year }}",
            ))
            ->add('endDate', 'date', array(
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy H:m',
            ))
            ->add('typeEvent')      
        ;
    }

    public function getName()
    {
        return 'ks_eventbundle_eventedittype';
    }
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\EventBundle\Entity\Event'
        );
    }
}
