<?php

namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Ks\ActivityBundle\Entity\SportRepository;

class SportType extends AbstractType
{
    
    private $context;
    
    public function __construct($context=null)
    {
        $this->context = $context;
        
    }
    
    /**
     * Context = Multi => pour permettre de choisir plusieurs sports ou tous les sports
     * @return string 
     */
    public function getName()
    {
        if ($this->context == "Multi") return 'ksSportTypeMulti'; // page comparison et statistiques du dashboadBundle
        else if ($this->context == "MultiNotAll") return 'ksSportTypeMultiNotAll'; // page magasin plusieurs sports possible mais on bloque tous les sports
        else if ($this->context == "MultiSimple") return 'ksSportTypeMultiSimple'; //filtre sur newsFeed, plusieurs choix possibles mais affichage simplifiée du <select>
        else return 'ksSportType'.$this->context;
    }
    
    /**
     *
     * @param FormBuilder $builder
     * @param array $options 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
	if ($this->context == "MultiSimple") {
            $builder
                ->add('sport', 'entity', array(   
                                    'class' => 'Ks\ActivityBundle\Entity\Sport',
                                    'property' => "label",
                                    'empty_value' => 'MES SPORTS',
                                    'empty_data'  => -1,
                                    'required' => false,
                                    'query_builder' => function(SportRepository $sr) {
                                        return $sr->findSportsQB();
                                    }
                                ));
        }
        else {
            $builder
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
            'data_class' => 'Ks\ActivityBundle\Entity\Activity'
        );
    }
}