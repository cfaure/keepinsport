<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Preference
 *
 * @ORM\Table(name="ks_preference")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\PreferenceRepository")
 * 
 */
class Preference
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
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\PreferenceType", inversedBy="preferences", fetch="LAZY")
     * 
     * @var Ks\UserBundle\Entity\PreferenceType
     */
    protected $preferenceType;
    
    /**
     * @ORM\Column(type="string", length=25,nullable="true")
     * 
     * @var string
     */
    protected $code;
    
    /**
     * @ORM\Column(type="string", length=45, nullable="true")
     * 
     * @var string
     */
    protected $description;
    
    /**
     *
     * @ORM\Column(name="val1", type="smallint", nullable=true)
     */
    private $val1;
    
    /**
     *
     * @ORM\Column(name="val2", type="smallint", nullable=true)
     */
    private $val2;
    
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

    public function __toString(){
       return  $this->description;

    }

    /**
     * Set preferenceType
     *
     * @param Ks\UserBundle\Entity\PreferenceType $preferenceType
     */
    public function setPreferenceType(\Ks\UserBundle\Entity\PreferenceType $preferenceType)
    {
        $this->preferenceType = $preferenceType;
    }

    /**
     * Get preferenceType
     *
     * @return Ks\UserBundle\Entity\PreferenceType 
     */
    public function getPreferenceType()
    {
        return $this->preferenceType;
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
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Set val1
     *
     * @param int $val1
     */
    public function setVal1($val1)
    {
        $this->val1 = $val1;
    }

    /**
     * Get val1
     *
     * @return int 
     */
    public function getVal1()
    {
        return $this->val1;
    }
    
    /**
     * Set val2
     *
     * @param int $val2
     */
    public function setVal2($val2)
    {
        $this->val2 = $val2;
    }

    /**
     * Get val2
     *
     * @return int 
     */
    public function getVal2()
    {
        return $this->val2;
    }
}