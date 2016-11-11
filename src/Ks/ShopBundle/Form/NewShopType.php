<?php

namespace Ks\ShopBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\ShopBundle\Entity;

class NewShopType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => true))
            ->add('webShop', 'checkbox')
            ->add('address', 'text', array('required' => true))
            ->add('town', 'text', array('required' => true))
            ->add('email', 'text', array('required' => true))
            ->add('telNumber', 'text', array('required' => true))
            ->add('conditions', 'textarea', array('attr' => array('cols' => 50, 'rows' => 5)))
        ;
    }

    public function getName()
    {
        return 'ks_shopbundle_newshoptype';
    }
    
    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
        );
    }
}
