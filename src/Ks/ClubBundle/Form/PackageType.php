<?php

namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\ClubBundle\Entity;

class PackageType extends AbstractType
{
    private $context;
    
    public function __construct($context =null)
    {
        $this->context = $context;
        
    }

    /**
     * @return string 
     */
    public function getName()
    {
        return 'packageType';
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('remainingSessions', 'number', array(
                'required'      => false,
                'attr'   =>  array(
                    'style'     => 'width:auto',
                    'class'     => 'remainingSessions input-mini'
                )
            ))
        ;
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
