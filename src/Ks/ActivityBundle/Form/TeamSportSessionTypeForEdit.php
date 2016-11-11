<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\ClubBundle\Entity\TeamRepository;
use Ks\UserBundle\Entity\UserRepository;

class ActivitySessionTypeForEdit extends AbstractType
{       
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'ks_activitybundle_teamsportsession';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {   
	    $builder
            //->setErrorBubbling(true)
            ->add('description')
            ->add('duration', 'time', array(
                                    'input'  => 'datetime',
                                    'widget' => 'single_text')
            )
            ->add('wasOfficial', 'checkbox',array('required' => false))
            
            ->add('result', 'entity', array(
                                        'class'     => 'Ks\ActivityBundle\Entity\Result',
                                        'property'  => "label",
                                        //'expanded'  => true,
                                        'empty_value' => '- Résultat -',
                                        'preferred_choices' => array(0),
                                        'required' => false
                                        )
            )

            ->add('scores', 'collection', array(
                                    //'data_class' => 'Ks\ActivityBundle\Entity\Score',
                                    'type'          => new ScoreType(),
                                    'allow_add'     => true,
                                    'allow_delete'  => true,
                                    'prototype'     => true,
                                    'label'         => 'scores'));

            $hasFriends = false;

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

            if ( $hasFriends ) {
            $builder->add('usersWhoHaveParticipated', 'entity', array(   
                                        'class' => 'Ks\UserBundle\Entity\User',
                                        'multiple' => true,
                                        'property' => "username",
                                        'query_builder' => function(UserRepository $ur) use($user) {
                                            return $ur->getFriendsAndMeQB($user, true);
                                        }))
            ->add('opponentsWhoHaveParticipated', 'entity', array(   
                                        'class' => 'Ks\UserBundle\Entity\User',
                                        'multiple' => true,
                                        'property' => "username",
                                        'query_builder' => function(UserRepository $ur) use($user) {
                                            return $ur->getFriendsAndMeQB($user);
                                        }));
            }
          

    }

    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ActivityBundle\Entity\ActivitySessionTeamSport'
        );
    }
}
