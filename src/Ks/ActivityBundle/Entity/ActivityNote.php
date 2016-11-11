<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ActivityBundle\Entity\ActivityNote
 *
 * @ORM\Table(name="ks_activity_note")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\ActivityNoteRepository")
 */
class ActivityNote
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
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\ActivityNoteType")
     */
    private $activityNoteType;
    
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
     * Set noteType
     *
     * @param Ks\ActivityBundle\Entity\ActivityNoteType $activityNoteType
     */
    public function setActivityNoteType(\Ks\ActivityBundle\Entity\ActivityNoteType $activityNoteType)
    {
        $this->activityNoteType = $activityNoteType;
    }

    /**
     * Get noteType
     *
     * @return Ks\ActivityBundle\Entity\ActivityNoteType 
     */
    public function getActivityNoteType()
    {
        return $this->activityNoteType;
    }
    
    
     public function __toString(){
      return $this->label;
    }
}