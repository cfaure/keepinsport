<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\ActivityBundle\Entity;
use Ks\ActivityBundle\Entity\ArticleTagRepository;

class ArticleType extends AbstractType
{
    private $context;
    
    public function __construct($context=null)
    {
        $this->context = $context;
        
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        if ($this->context == 'withTrainingPlan') {
            $builder
                ->add('label')
                ->add('categoryTag', 'entity', array(
                    'class'     => 'Ks\ActivityBundle\Entity\ArticleTag',
                    'property'  => "label",
                    'label'     => "label",
                    'empty_value' => '----Choisir une catégorie----',
                    'empty_data'  => -1,
                    'attr'   =>  array(
                        'style'     => 'width:auto',
                        'class'     => 'categoryTag'
                    ),
                    'query_builder' => function(ArticleTagRepository $atr) {
                         return $atr->getTagIsCategoryWithTrainingPlan();
                    }
                ));
        }
        else {
            $builder
                ->add('label')
                ->add('categoryTag', 'entity', array(
                    'class'     => 'Ks\ActivityBundle\Entity\ArticleTag',
                    'property'  => "label",
                    'label'     => "label",
                    'empty_value' => '----Choisir une catégorie----',
                    'empty_data'  => -1,
                    'attr'   =>  array(
                        'style'     => 'width:auto',
                        'class'     => 'categoryTag'
                    ),
                    'query_builder' => function(ArticleTagRepository $atr) {
                         return $atr->getTagIsCategory();
                    }
                ));
        }
    }

    public function getName()
    {
        return 'ks_activitybundle_articletype';
    }
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ActivityBundle\Entity\Article'
        );
    }
}
