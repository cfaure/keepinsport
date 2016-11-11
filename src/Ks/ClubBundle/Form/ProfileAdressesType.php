<?php

namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\UserBundle\Entity\UserRepository;

class ProfileAdressesType extends AbstractType
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
           
            ->add('country_area', 'text', array('required' => false))
            ->add('longitude', 'text', array('required' => false))
            ->add('latitude', 'text', array('required' => false))
            ->add('country_code', 'text', array('required' => false))
            ->add('town', 'text', array('required' => false))
            
            ->add('adress_name', 'text', array('required' => false, 
                'attr'   =>  array(
                    'class'   => 'input-xxlarge'
                ))
            )
        ;
    }

    public function getName()
    {
        return 'ksClub_ProfileAdressesType';
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
