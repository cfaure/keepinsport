<?php

namespace Ks\CoachingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\CoachingBundle\Entity\CoachingPlanType
 *
 * @ORM\Table(name="ks_coaching_plan_type")
 * @ORM\Entity(repositoryClass="Ks\CoachingBundle\Entity\CoachingPlanTypeRepository")
 */
class CoachingPlanType
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
    
    
     public function __toString(){
      return $this->label;
    }
}