<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\ActivityBundle\Entity\SportRepository;

class PeriodType extends AbstractType
{
    
    private $context;
    
    public function __construct($context=null)
    {
        $this->context = $context;
        
    }
    
    /**
     * Context = Multi => pour permettre de choisir plusieurs sports ou tous les sports
     * @return string 
     */
    public function getName()
    {
        return 'ksPerioType';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
	$builder
            ->add('start', 'text')
            ->add('end', 'text')
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
            'data_class' => 'Ks\ActivityBundle\Entity\Activity'
        );
    }
}