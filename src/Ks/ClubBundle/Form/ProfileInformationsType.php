<?php

namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\UserBundle\Entity\UserRepository;

class ProfileInformationsType extends AbstractType
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
            ->add('name', 'text', array('required' => true))
            ->add('url_site_web', 'text', array('required' => false))
            ->add('tel_number', 'text', array('required' => false))
            ->add('mobile_number', 'text', array('required' => false))
            ->add('email', 'text', array('required' => false))   
        ;
    }

    public function getName()
    {
        return 'ksClub_ProfileInformationsType';
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
