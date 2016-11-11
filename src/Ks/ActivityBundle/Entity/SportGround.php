<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Sport
 *
 * @ORM\Table(name="ks_sport_ground")
 * @ORM\Entity()
 * 
 */
class SportGround
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @var int
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=10,nullable="true")
     * 
     * @var string
     */
    protected $code;
    
    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @var string
     */
    protected $label;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\ActivitySession", mappedBy="sportGround")
     */
    protected $activities;
    
    /**
     * 
     */
    public function __construct()
    {
    }
    
    /**
     * Get id
     *
     * @return string 
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
}