<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserDetailQuestionnaireType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $choices = array(
                    '1'     => 'Oui',
                    '0'     => 'Non',
                    null    => 'Ne se prononce pas');
        $builder
            ->add('phone')
            ->add('occupation')
            ->add('familyConstraint')
            ->add('occupationConstraint')
            ->add('contactEmergency')
            ->add('medicalFollow', 'choice', array(
                'choices' => $choices, 'attr' => array('class' => 'radiolabel'),
                'expanded'  => true))
            ->add('medicalFollowingOK', 'choice', array(
                'choices' => $choices, 'attr' => array('class' => 'radiolabel'),
                'expanded'  => true))
            ->add('sportDoctor', 'choice', array(
                'choices' => $choices, 'attr' => array('class' => 'radiolabel'),
                'expanded'  => true))
            ->add('osteo', 'choice', array(
                'choices' => $choices, 'attr' => array('class' => 'radiolabel'),
                'expanded'  => true))
            ->add('kine', 'choice', array(
                'choices' => $choices, 'attr' => array('class' => 'radiolabel'),
                'expanded'  => true))
            ->add('podo', 'choice', array(
                'choices' => $choices, 'attr' => array('class' => 'radiolabel'),
                'expanded'  => true))
            ->add('medicalMonitoring', 'choice', array(
                'choices' => $choices, 'attr' => array('class' => 'radiolabel'),
                'expanded'  => true))
            ->add('allergies', 'choice', array(
                'choices' => $choices, 'attr' => array('class' => 'radiolabel'),
                'expanded'  => true))
            ->add('effortTest', 'choice', array(
                'choices' => $choices, 'attr' => array('class' => 'radiolabel'),
                'expanded'  => true))
            ->add('medicalMisc')
            ->add('shoes')
            ->add('bags')
            ->add('HR')
            ->add('bikes')
            ->add('equipmentMisc')
            ->add('license', 'choice', array(
                'choices' => $choices, 'attr' => array('class' => 'radiolabel'),
                'expanded'  => true))
            ->add('mySports')
            ->add('sportLevel')
            ->add('achievement')
            ->add('injuries', 'choice', array(
                'choices' => $choices, 'attr' => array('class' => 'radiolabel'),
                'expanded'  => true))
            ->add('mySports')
            ->add('injuriesTreat')
            ->add('workoutHours')
            ->add('crossWorkout', 'choice', array(
                'choices' => $choices, 'attr' => array('class' => 'radiolabel'),
                'expanded'  => true))
            ->add('crossWorkouts')
            ->add('workoutDesc')
            ->add('workoutWeek')
            ->add('strongPoints')
            ->add('weakPoints')
            ->add('regularInjuries')
            ->add('gotCoach', 'choice', array(
                'choices' => $choices, 'attr' => array('class' => 'radiolabel'),
                'expanded'  => true))
            ->add('wantCoach', 'choice', array(
                'choices' => $choices, 'attr' => array('class' => 'radiolabel'),
                'expanded'  => true))
            ->add('whyCoach')
            ->add('goalsImpediment')
            ->add('goalsImpedimentDrop', 'choice', array(
                'choices' => $choices, 'attr' => array('class' => 'radiolabel'),
                'expanded'  => true))
            ->add('goalsImpedimentManage')
            ->add('goalsRank')
            ;
    }

    public function getName()
    {
        return 'UserDetailQuestionnaireType';
    }   
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\UserBundle\Entity\UserDetail'
        );
    }
}