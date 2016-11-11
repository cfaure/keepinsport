<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;


class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
         $builder
            ->add('description', 'textarea');
    }

    public function getName()
    {
        return 'ks_user_feedback';
    }
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\UserBundle\Entity\Feedback'
        );
    }
}
