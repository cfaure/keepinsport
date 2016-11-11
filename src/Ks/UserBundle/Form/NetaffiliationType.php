<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class NetaffiliationType extends AbstractType
{
    private $user;
    
    public function __construct( \Ks\UserBundle\Entity\User $user )
    {
        $this->user = $user;
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $user = $this->user;
        
        $builder
            ->add('user', 'entity', array(
                'class' => 'Ks\UserBundle\Entity\User',
                'multiple' => false,
                'property' => "username",
                'required' => true,
                'query_builder' => function(\Ks\UserBundle\Entity\UserRepository $ur) use($user) {
                    return $ur->getUserQB($user->getId());
                },
                'attr'   =>  array(
                    'style'     => 'display:none',
                    'class'     => 'user'
                )
            ))
            ->add('label', 'text', array(
                'required'      => true,
                'attr'   =>  array(
                    'class'     => 'input-block-level'
                )
            ))
            ->add('reference', 'text', array(
                'required'      => true,
                'attr'   =>  array(
                    'style'     => 'width:auto',
                    'class'     => 'reference input-block-level'
                )
            ))
            ->add('sports', 'entity', array(
                'multiple' => true,
                'class' => 'KsActivityBundle:Sport',
                'property' => 'codeSport',
                'attr'   =>  array(
                    'style'     => 'display:none',
                    'class'     => 'sports'
                )
                
            ))
        ;
        
        parent::buildForm($builder, $options);
    }

    public function getName() {
        return 'ks_user_netaffiliation';
    }
    
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\UserBundle\Entity\Netaffiliation'
        );
    }
}
