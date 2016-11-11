<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserModifiesArticleType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('content')
        ;
    }

    public function getName()
    {
        return 'ks_activitybundle_usermodifiesseizedcontenttype';
    }
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ActivityBundle\Entity\UserModifiesArticle'
        );
    }
}
