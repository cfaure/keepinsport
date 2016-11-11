<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ActivityBundle\Entity\StateOfHealth
 *
 * @ORM\Table(name="ks_state_of_health")
 * @ORM\Entity
 */
class StateOfHealth
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=45)
     */
    private $name;
    
    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=45)
     */
    private $code;
    
    /**
     * @ORM\OneToMany(targetEntity="\Ks\ActivityBundle\Entity\ActivitySession", mappedBy="stateOfHealth", cascade={"remove", "persist"})
     */
    private $activities;


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
    public function __construct()
    {
        $this->activities = new \Doctrine\Common\Collections\ArrayCollection();
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
}