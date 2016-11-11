<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CommentType extends AbstractType
{
    /**
     * @var integer $idActivity
     *
     * 
     */
    private $idActivity;
    
    public function __construct($idActivity)
    {
        $this->idActivity = $idActivity;
    }
    
    /**
     *
     * @return string 
     */
    public function getName()
    {
        return 'ks_activitybundle_commenttype_' . $this->idActivity;
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
	    $builder
            //->add('activity', 'text')
            ->add('comment', 'textarea');
    }

    /**
     *
     * @param array $options
     * @return type 
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Ks\ActivityBundle\Entity\Comment'
        );
    }
}