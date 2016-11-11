<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\ActivityBundle\Entity\SportRepository;
use Ks\ClubBundle\Entity\ClubRepository;

class SportsmenSearchType extends AbstractType
{
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
        return 'SportsmenSearchType';
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $user = $this->user;
        
	    $builder
            ->add('description', 'textarea', array(
                'required' => false,
                'attr'   =>  array(
                    'class'   => 'description input-block-level',
                    'placeholder' => "Description, nombre de sportifs, durée..."
                )
            ))
            ->add('programmedPlace', new \Ks\EventBundle\Form\PlaceType(), array(
                "required" => false
            ))
            ->add('scheduledAt', 'datetime', array(
                //'widget' => 'text',
                'date_format' => 'dd/MM/yyyy',
                'date_widget' => "single_text",
                'time_widget' => "single_text",
                'attr'   =>  array(
                    'class'   => 'scheduledAt',
                )
                
            ))
            ->add('sport', 'entity', array(   
                'class' => 'Ks\ActivityBundle\Entity\Sport',
                'property' => "label",
                'empty_value' => '- Sport -',
                'empty_data'  => -1,
                'required' => false,
                'query_builder' => function(SportRepository $sr) {
                    return $sr->findSportsQB();
                }
            ));
            
            if( is_object( $user ) ) {
                
                //S'il est adhérent de clubs
                if( count( $user->getClubs() ) > 0 ) {
                    $builder->add('club', 'entity', 
                        array(   
                                'class' => 'Ks\ClubBundle\Entity\Club',
                                'multiple' => false,
                                'property' => "name",
                                'empty_value' => '- Club* -',
                                'preferred_choices' => array(0),
                                'required' => false,
                                'query_builder' => function(ClubRepository $cr) use($user) {
                                    return $cr->findMyClubsQB($user);
                                }
                        )
                    );
                }
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
            'data_class' => 'Ks\ActivityBundle\Entity\SportsmenSearch'
        );
    }
}