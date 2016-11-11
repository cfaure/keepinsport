<?php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\UserBundle\Entity\UserRepository;

class ProfileInformationsType extends AbstractType
{
    private $user;
    
    public function __construct(\Ks\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        
        $user = $this->user;
        
        $builder
            //->add('username', "text", array('required' => true))
            ->add('email', "email", array('required' => true))
            ->add('userDetail', new \Ks\UserBundle\Form\UserDetailInformationsType())
            ;
                
            if( is_object( $user ) ) {
                $hasFriends = false;
                //$userRep = new UserRepository();
                //$friendsIds = $userRep->getFriendIds( $user->getId() );
                
                foreach($user->getFriendsWithMe() as $userHasFriends) {
                    if ($userHasFriends->getPendingFriendRequest() == 0 ) {
                        $hasFriends = true;
                        break;
                    }
                }

                if ( ! $hasFriends ) {
                    foreach($user->getMyFriends() as $userHasFriends) {
                        if ($userHasFriends->getPendingFriendRequest() == 0 ) {
                            $hasFriends = true;
                            break;
                        }
                    }
                }
                
                if( $hasFriends ) {
                    $builder->add('godFather', 'entity', 
                        array(   
                            'class' => 'Ks\UserBundle\Entity\User',
                            'multiple' => false,
                            'property' => "username",
                            'empty_value' => '- Parrain -',
                            'preferred_choices' => array(0),
                            'required' => false,
                            'query_builder' => function(UserRepository $ur) use($user) {
                                return $ur->getFriendsAndMeQB($user, false);
                            }
                        )
                    );
                }
            }
    }

    public function getName()
    {
        return 'ProfileInformationsType';
    }
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\UserBundle\Entity\User'
        );
    }
}