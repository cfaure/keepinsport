<?php

namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\UserBundle\Entity\UserRepository;

class ProfileInviteFriendsType extends AbstractType
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
            ->add('users', 'entity', array(
                'required'      => false,
                'class'         => 'Ks\UserBundle\Entity\User',
                'property'      => "username",
                'query_builder' => function(UserRepository $ur) use( $user ) {
                    return $ur->getFriendsAndMeQB( $user,  false ); // NOTE CF: pourquoi c'était à false ? je comprends pas... //NOTE CD : Parce que je ne veux que mes amis...
                },
                'multiple'      => true,
                'attr'          =>  array(
                    'class'     => 'multiselect users',
                    'style'     => array("height" => "300px")
                )
            ))  
        ;
    }

    public function getName()
    {
        return 'ksClub_ProfileInviteFriends';
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
