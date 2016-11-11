<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ActivityBundle\Entity\SportType
 *
 * @ORM\Table(name="ks_sport_type")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\SportTypeRepository")
 */
class SportType
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
     * @ORM\Column(type="string", length=255)
     * 
     * @var string
     */
    private $code;
    
    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @var string
     */
    private $label;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\Sport", mappedBy="opposingTeam", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
    private $sports;

    /**
     * @ORM\Column(type="string", length=20)
     * 
     * @var string
     */
    private $hexadecimalColor;

    public function __construct()
    {
        $this->sports = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set label
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
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
     * Set hexadecimalColor
     *
     * @param string $hexadecimalColor
     */
    public function setHexadecimalColor($hexadecimalColor)
    {
        $this->hexadecimalColor = $hexadecimalColor;
    }

    /**
     * Get hexadecimalColor
     *
     * @return string 
     */
    public function getHexadecimalColor()
    {
        return $this->hexadecimalColor;
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
    
    
     public function __toString(){
      return $this->label;
    }
}