<?php

namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\UserBundle\Entity\UserRepository;

class ClubType extends AbstractType
{
    private $user;
    
    public function __construct(\Ks\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $user = $this->user;
        
        $builder
            ->add('name', 'text', array('required' => false))
            ->add('country_area', 'text', array('required' => false))
            ->add('longitude', 'text', array('required' => false))
            ->add('latitude', 'text', array('required' => false))
            ->add('country_code', 'text', array('required' => false))
            ->add('town', 'text', array('required' => false))
            ->add('url_site_web', 'text', array('required' => false))
            ->add('adress_name', 'text', array('required' => false, 
                'attr'   =>  array(
                    'class'   => 'input-xxlarge'
                ))
            )
            ->add('tel_number', 'text', array('required' => false))
            ->add('mobile_number', 'text', array('required' => false))
            ->add('email', 'text', array('required' => false))
            ->add('sports', 'entity', array(
                'class'         => 'KsActivityBundle:Sport',
                'property'      => 'label',
                'required'      => false,
                'multiple'      => true
             )) 
            //->add('avatar', 'file', array('required' => false))
            ->add('users', 'entity', array(
                'class'         => 'Ks\UserBundle\Entity\User',
                'property'      => "username",
                'query_builder' => function(UserRepository $ur) use( $user ) {
                    return $ur->getFriendsAndMeQB( $user,  true); // NOTE CF: pourquoi c'était à false ? je comprends pas...
                },
                'multiple'      => true,
                'attr'          =>  array(
                    'class'     => 'multiselect users',
                    'style'     => array("height" => "300px")
                )
            ))  
            ->add('presidents', 'entity', array(
                'class'         => 'Ks\UserBundle\Entity\User',
                'property'      => "username",
                'query_builder' => function(UserRepository $ur) use( $user ) {
                    return $ur->getFriendsAndMeQB( $user, true );
                },
                'multiple'      => true,
                'attr'          =>  array(
                    'class'     => 'multiselect presidents',
                    'style'     => array(
                        "height" => "300px"
                    )
                )
            ))    
        ;
    }

    public function getName()
    {
        return 'ksClub_clubType';
    }
    
        /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ClubBundle\Entity\Club'
        );
    }
}
