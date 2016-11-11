<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\UserBundle\Entity\PreferenceType
 *
 * @ORM\Table(name="ks_preference_type")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\PreferenceTypeRepository")
 */
class PreferenceType
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
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\Preference", mappedBy="opposingTeam", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
    private $preferences;

    public function __construct()
    {
        $this->preferences = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add preferences
     *
     * @param Ks\UserBundle\Entity\Preference $preferences
     */
    public function addPreference(\Ks\UserBundle\Entity\Preference $preferences)
    {
        $this->preferences[] = $preferences;
    }

    /**
     * Get preferences
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPreferences()
    {
        return $this->preferences;
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