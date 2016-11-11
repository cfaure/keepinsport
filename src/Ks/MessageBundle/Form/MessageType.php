<?php

namespace Ks\MessageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\UserBundle\Entity\UserRepository;

class MessageType extends AbstractType
{
    
    private $user;
    private $isAnAnswer;
    private $toUserId;
    private $toClubId;
    
    public function __construct(\Ks\UserBundle\Entity\User $user, $isAnAnswer = false, $toUserId = null, \Ks\ClubBundle\Entity\Club $club = null)
    {
        $this->user = $user;
        $this->isAnAnswer = $isAnAnswer;
        $this->toUserId = $toUserId;
        $this->club     = $club;
    }
    
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'messageType';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $user = $this->user;
        $toUserId = $this->toUserId;
        $club   = $this->club;
        
        $builder
            ->add('subject', 'text', array(
                'required' => $this->isAnAnswer ? false : true,
                'attr'   =>  array(
                    'style'   => $this->isAnAnswer ? 'display:none' : '',
                    'placeholder' => 'Sujet'
                ),
            ))
            ->add('content', 'textarea', array(
                'required' => true,
                'attr'   =>  array(
                    'placeholder' => $this->isAnAnswer ? 'Réponse' : 'Message'
                ),
            ));
        
        $builder->add('previousMessage', 'entity', array(
                'class'     => 'Ks\MessageBundle\Entity\Message',
                'property'  => "id",
                'attr'   =>  array(
                    'style'   => 'display:none'
                ),
                'empty_value' => 'Message',
                'required' => false
                )
            );
        
        if (is_null($club))
            $builder->add('toUsers', 'entity', array(
                'class' => 'Ks\UserBundle\Entity\User',
                'multiple' => true,
                'property' => "username",
                'query_builder' => function(UserRepository $ur) use($user, $toUserId) {
                    return $ur->getFriendsAndMeQB($user, false, $toUserId);
                },
                'required' => $this->isAnAnswer ? false : true,
                'attr'   =>  array(
                    'class'     => 'multiselect toUsers input-block-level',
                    'style'   => $this->isAnAnswer ? 'display:none;' : ''
                ),
            ));
        else {
            $builder->add('toUsers', 'entity', array(
                'class' => 'Ks\UserBundle\Entity\User',
                'multiple' => true,
                'property' => "username",
                'query_builder' => function(UserRepository $ur) use($club, $user) {
                    return $ur->findClubMembersQB($club, $user);
                },
                'required' => $this->isAnAnswer ? false : true,
                'attr'   =>  array(
                    'class'   => 'multiselect toUsers input-block-level',
                    'style'   => $this->isAnAnswer ? 'display:none;' : ''
                ),
            ));
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
            'data_class' => 'Ks\MessageBundle\Entity\Message'
        );
    }
}