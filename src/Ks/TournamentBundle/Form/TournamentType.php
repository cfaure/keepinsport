<?php

namespace Ks\TournamentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Ks\ActivityBundle\Entity\SportRepository;

class TournamentType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('sport', 'entity', array(   
                'class' => 'Ks\ActivityBundle\Entity\Sport',
                'property' => "label",
                'empty_value' => '- Sport -',
                'empty_data'  => "",
                'required' => false,
                'query_builder' => function(SportRepository $sr) {
                    return $sr->findSportsQB();
                }
            ))
            ->add('startDate', 'date', array(
                'format' => 'dd/MM/yyyy',
                'widget' => "single_text",
                'attr'   =>  array(
                    'class'   => 'startDate',
                )
            ))    
            ->add('endDate', 'date', array(
                'format' => 'dd/MM/yyyy',
                'widget' => "single_text",
                'attr'   =>  array(
                    'class'   => 'endDate',
                )
            ))    
                
        ;
    }

    public function getName()
    {
        return 'ksTournamentType';
    }
}
