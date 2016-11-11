<?php

namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\UserBundle\Entity\UserRepository;
use Ks\ClubBundle\Entity\ClubRepository;

class ClubHasUsersType extends AbstractType
{
    private $user;
    private $club;
    private $context;

    public function __construct( \Ks\ClubBundle\Entity\Club $club, \Ks\UserBundle\Entity\User $user, $context=null)
    {
        $this->club = $club;
        $this->user = $user;
        $this->context = $context;
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $user = $this->user;
        $club = $this->club;
        $context = $this->context;
        
        $builder->add('user', 'entity', 
            array(   
                'class' => 'Ks\UserBundle\Entity\User',
                'multiple' => false,
                'property' => "username",
                'query_builder' => function(UserRepository $ur) use( $user, $club, $context ) {
                    if ($context == 'fromClub') return $ur->findClubMembersQB($club);
                    else return $ur->getFriendsAndMeQB( $user, false );
                }
            ));
    }

    public function getName()
    {
        return 'ksClub_ClubHasUsersType';
    }
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ClubBundle\Entity\ClubHasUsers'
        );
    }
}
