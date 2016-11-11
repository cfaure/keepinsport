<?php

namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\UserBundle\Entity\UserRepository;

class ProfileAvatarsType extends AbstractType
{    
    public function buildForm(FormBuilder $builder, array $options)
    {       
        $builder 
            ->add('avatar', 'file', array('required' => false))   
        ;
    }

    public function getName()
    {
        return 'ksClub_ProfileAvatarsType';
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
