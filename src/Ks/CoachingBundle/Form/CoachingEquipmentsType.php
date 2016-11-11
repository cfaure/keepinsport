<?php

namespace Ks\CoachingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CoachingEquipmentsType extends AbstractType
{
    private $user;
    private $sport;
    
    public function __construct(\Ks\UserBundle\Entity\User $user = null, \Ks\ActivityBundle\Entity\Sport $sport = null, $key=null)
    {
        $this->user = $user;
        $this->sport = $sport;
        $this->key = $key;
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $user = $this->user;
        $sport = $this->sport;
        
        $builder->add('equipments', 'entity', array(
                'class'         => 'Ks\UserBundle\Entity\Equipment',
                'property'      => "name",
                'multiple'      => true,
                'required' => false,
                'query_builder' => function(\Ks\UserBundle\Entity\EquipmentRepository $er) use($user, $sport) {
                    return $er->getUserEquipementsBySportQB($user->getId(), $sport == null ? -1 : $sport->getId());
                },
                'attr'   =>  array(
                    'class'   => 'equipments',
                )
            )
        );
    }

    public function getName()
    {
        if (!is_null($this->key)) return 'ksCoachingEquipments_' . $this->key;
        else return 'ksCoachingEquipmentsType';
    }
}
