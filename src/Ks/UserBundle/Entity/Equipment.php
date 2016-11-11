<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\UserBundle\Entity\Equipment
 *
 * @ORM\Table(name="ks_equipment")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\EquipmentRepository")
 */
class Equipment
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
     * @var Ks\UserBundle\Entity\User $user
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="equipments")
     */
    private $user;
    
    /**
     * @var Ks\UserBundle\Entity\EquipmentType $type
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\EquipmentType", inversedBy="equipments")
     */
    private $type;
    
    /**
     * @ORM\ManyToMany(targetEntity="Ks\ActivityBundle\Entity\Sport")
     * @ORM\JoinTable(name="ks_equipment_used_in_sports")
     */
    private $sports;
    
    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;
    
    /**
     * @var string $brand
     *
     * @ORM\Column(name="brand", type="string", length=255, nullable=false)
     */
    private $brand;
    
    /**
     * @var integer
     * 
     * @ORM\Column(name="weight", type="float", nullable=true)
     */
    private $weight;
    
    /**
     * @var integer
     * 
     * @ORM\Column(name="primaryColor", type="string", length=255, nullable=true)
     */
    private $primaryColor;
    
    /**
     * @var integer
     * 
     * @ORM\Column(name="secondaryColor", type="string", length=255, nullable=true)
     */
    private $secondaryColor;
    
    /**
     * @var string $avatar
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
     */
    private $avatar;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Activity")
     * @ORM\JoinColumn(name="activity_id", referencedColumnName="id", nullable=true)
    */
    protected $activity;
    
    /**
     * @var boolean $isByDefault
     * 
     * @ORM\Column(name="isByDefault", type="boolean", options={"default" = true})
     */
    protected $isByDefault;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\EquipmentHasWishes", mappedBy="equipment")
     * 
     * @var ArrayCollection
     */
    protected $wishers;
    

    public function __construct()
    {
        
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        if( $name == null) $name == "";
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set brand
     *
     * @param string $brand
     */
    public function setBrand($brand)
    {
        if( $brand == null) $brand == "";
        $this->brand = $brand;
    }

    /**
     * Get brand
     *
     * @return string 
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set weight
     *
     * @param float $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * Get weight
     *
     * @return float 
     */
    public function getWeight()
    {
        return $this->weight;
    }


    /**
     * Set type
     *
     * @param Ks\UserBundle\Entity\EquipmentType $type
     */
    public function setType(\Ks\UserBundle\Entity\EquipmentType $type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return Ks\UserBundle\Entity\EquipmentType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set user
     *
     * @param Ks\UserBundle\Entity\User $user
     */
    public function setUser(\Ks\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add sports
     *
     * @param Ks\ActivityBundle\Entity\Sport $sports
     */
    public function addSport(\Ks\ActivityBundle\Entity\Sport $sports)
    {
        $this->sports[] = $sports;
    }

    /**
     * Get sports
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getSports()
    {
        return $this->sports;
    }
    
    /**
     * Set sport
     *
     * @param \Ks\ActivityBundle\Entity\Sport $sport
     */
    public function setSports($sports)
    {
        foreach( $sports as $sport ) {
            $this->addSport( $sport );
        }
    }

    /**
     * Set primaryColor
     *
     * @param string $primaryColor
     */
    public function setPrimaryColor($primaryColor)
    {
        $this->primaryColor = $primaryColor;
    }

    /**
     * Get primaryColor
     *
     * @return string 
     */
    public function getPrimaryColor()
    {
        return $this->primaryColor;
    }

    /**
     * Set secondaryColor
     *
     * @param string $secondaryColor
     */
    public function setSecondaryColor($secondaryColor)
    {
        $this->secondaryColor = $secondaryColor;
    }

    /**
     * Get secondaryColor
     *
     * @return string 
     */
    public function getSecondaryColor()
    {
        return $this->secondaryColor;
    }
    
    /**
     * Set avatar
     *
     * @param string $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }
    
    /**
     * Set activity
     *
     * @param Ks\ActivityBundle\Entity\Activity $activity
     */
    public function setActivity(\Ks\ActivityBundle\Entity\Activity $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Get activity
     *
     * @return Ks\ActivityBundle\Entity\Activity 
     */
    public function getActivity()
    {
        return $this->activity;
    }
    
    /**
     * Set isByDefault
     *
     * @param boolean $isByDefault
     */
    public function setIsByDefault($isByDefault)
    {
        $this->isByDefault = $isByDefault;
    }

    /**
     * Get isByDefault
     *
     * @return boolean 
     */
    public function getIsByDefault()
    {
        return $this->isByDefault;
    }
}