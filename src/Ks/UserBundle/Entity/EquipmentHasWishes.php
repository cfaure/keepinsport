<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\UserBundle\Entity\EquipmentHasWishes
 *
 * @ORM\Table(name="ks_equipment_has_wishes")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\EquipmentHasWishesRepository")
 */
class EquipmentHasWishes
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\Equipment")
     * @ORM\JoinColumn(onDelete="cascade", onUpdate="cascade", nullable=false)
     */
    private $equipment;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $wisher;
    
    public function __construct(\Ks\UserBundle\Entity\Equipment $equipment, \Ks\UserBundle\Entity\User $wisher)
    {
        $this->equipment    = $equipment;
        $this->wisher       = $wisher;
    }

    /**
     * Set Equipment
     *
     * @param Ks\UserBundle\Entity\Equipment $equipment
     */
    public function setEquipment(\Ks\UserBundle\Entity\Equipment $equipment)
    {
        $this->equipment = $equipment;
    }

    /**
     * Get Equipment
     *
     * @return Ks\UserBundle\Entity\Equipment 
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * Set wisher
     *
     * @param Ks\UserBundle\Entity\User $wisher
     */
    public function setWisher(\Ks\UserBundle\Entity\User $wisher)
    {
        $this->wisher = $wisher;
    }

    /**
     * Get wisher
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getWisher()
    {
        return $this->wisher;
    }
}