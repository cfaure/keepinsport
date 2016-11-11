<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\ClubBundle\Entity\TeamRepository;

class TrainingPlanType extends AbstractType
{   
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'ks_activitybundle_trainingPlan';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
	    $builder
            ->add('numberOfWeeks')
            ->add('numberOfSessionsPerWeek');
            //->add('numberMinOfRestDaysBetweenTwoSessions')
            //->add('numberMaxOfRestDaysBetweenTwoSessions');
    }

    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ActivityBundle\Entity\TrainingPlan'
        );
    }
}