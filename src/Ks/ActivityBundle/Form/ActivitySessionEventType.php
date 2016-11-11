<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\EventBundle\Entity\EventRepository;

class ActivitySessionEventType extends AbstractType
{   
    private $idActivity;
    
    public function __construct($idActivity)
    {
        $this->idActivity = $idActivity;
    }
    
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'activitySession_' . $this->idActivity . '_event';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
	    $builder
            ->add('event', 'entity', array(   
                'class' => 'Ks\EventBundle\Entity\Event',
                'property' => "name",
                'query_builder' => function(EventRepository $er) {
                                        return $er->findEventsNotConnectedToActivityQB();
                                    },
                'required' => true)
            );
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