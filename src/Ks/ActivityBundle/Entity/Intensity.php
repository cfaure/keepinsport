<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ActivityBundle\Entity\Intensity
 *
 * @ORM\Table(name="ks_activity_intensity")
 * @ORM\Entity
 */
class Intensity
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
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;
    
    /**
     * @ORM\OneToMany(targetEntity="\Ks\ActivityBundle\Entity\ActivitySession", mappedBy="intensity", cascade={"remove", "persist"})
     */
    private $activities;

    public function __construct()
    {
        $this->activities = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add activities
     *
     * @param Ks\ActivityBundle\Entity\ActivitySession $activities
     */
    public function addActivitySession(\Ks\ActivityBundle\Entity\ActivitySession $activities)
    {
        $this->activities[] = $activities;
    }

    /**
     * Get activities
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getActivities()
    {
        return $this->activities;
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
        
        return "intensity." . $this->getCode();
    } 
}