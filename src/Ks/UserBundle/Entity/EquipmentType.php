<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\UserBundle\Entity\EquipmentType
 *
 * @ORM\Table(name="ks_equipment_type")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\EquipmentTypeRepository")
 */
class EquipmentType
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\Equipment", mappedBy="type", cascade={"remove", "persist"})
     */
    private $equipments;
    
    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;
    
    /**
     * @ORM\Column(type="boolean")
     * 
     * @var boolean
     */
    private $isWeightEnabled;
    
    /**
     * @ORM\Column(type="boolean")
     * 
     * @var boolean
     */
    private $isPrimaryColorEnabled;
    
    /**
     * @ORM\Column(type="boolean")
     * 
     * @var boolean
     */
    private $isSecondaryColorEnabled;

    public function __construct()
    {
        $this->equipments  = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setIsWeightEnabled( false );
        $this->setIsPrimaryColorEnabled( false );
        $this->setIsSecondaryColorEnabled( false );
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Add equipments
     *
     * @param Ks\UserBundle\Entity\Equipment $equipments
     */
    public function addEquipment(\Ks\UserBundle\Entity\Equipment $equipments)
    {
        $this->equipments[] = $equipments;
    }

    /**
     * Get equipments
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getEquipments()
    {
        return $this->equipments;
    }

    /**
     * Set isPrimaryColorEnabled
     *
     * @param boolean $isPrimaryColorEnabled
     */
    public function setIsPrimaryColorEnabled($isPrimaryColorEnabled)
    {
        $this->isPrimaryColorEnabled = $isPrimaryColorEnabled;
    }

    /**
     * Get isPrimaryColorEnabled
     *
     * @return boolean 
     */
    public function getIsPrimaryColorEnabled()
    {
        return $this->isPrimaryColorEnabled;
    }

    /**
     * Set isSecondaryColorEnabled
     *
     * @param boolean $isSecondaryColorEnabled
     */
    public function setIsSecondaryColorEnabled($isSecondaryColorEnabled)
    {
        $this->isSecondaryColorEnabled = $isSecondaryColorEnabled;
    }

    /**
     * Get isSecondaryColorEnabled
     *
     * @return boolean 
     */
    public function getIsSecondaryColorEnabled()
    {
        return $this->isSecondaryColorEnabled;
    }

    /**
     * Set isWeightEnabled
     *
     * @param boolean $isWeightEnabled
     */
    public function setIsWeightEnabled($isWeightEnabled)
    {
        $this->isWeightEnabled = $isWeightEnabled;
    }

    /**
     * Get isWeightEnabled
     *
     * @return boolean 
     */
    public function getIsWeightEnabled()
    {
        return $this->isWeightEnabled;
    }
}