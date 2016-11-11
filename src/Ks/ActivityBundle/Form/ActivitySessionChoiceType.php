<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\ActivityBundle\Entity\ActivitySessionRepository;

class ActivitySessionChoiceType extends AbstractType
{
    
    /**
     * @var integer $sports
     *
     * 
     */
    //private $sports;
    private $user;
    
    public function __construct(\Ks\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }
    
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'ks_activitybundle_ActivityChoice';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $user = $this->user;
        $builder->add('activitySession', 'entity', array(   
                                    'class' => 'Ks\ActivityBundle\Entity\ActivitySession',
                                    'query_builder' => function(ActivitySessionRepository $asr) use($user) {
                                        return $asr->findNotConnectedToEventQB($user);
                                    }
                                ));
    }

    /**
     *
     * @param array $options
     * @return type 
     */
    /*public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ActivityBundle\Entity\ActivitySession'
        );
    }*/
}