<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\UserBundle\Entity\Service
 *
 * @ORM\Table(name="ks_netaffiliation")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\NetaffiliationRepository")
 */
class Netaffiliation
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
     * @ORM\Column(name="label", type="string", length=255)
     */
    private $label;
    
    /**
     * @var string $name
     *
     * @ORM\Column(name="reference", type="string", length=255)
     */
    private $reference;
    
    /**
     * @ORM\ManyToMany(targetEntity="Ks\ActivityBundle\Entity\Sport")
     * @ORM\JoinTable(name="ks_netaffiliation_for_sport")
     */
    private $sports;
    
    /**
     * @var Ks\UserBundle\Entity\User $user
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="netaffiliations")
     */
    private $user;
    
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
     * Set reference
     *
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * Get reference
     *
     * @return string 
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Add sports
     *
     * @param Ks\ActivityBundle\Entity\Sport $sports
     */
    public function addSport(\Ks\ActivityBundle\Entity\Sport $sport)
    {
        $this->sports[] = $sport;
    }
    
    public function setSports(\Doctrine\Common\Collections\Collection $sports)
    {
        foreach($sports as $sport) {
            $this->addSport($sport);
        }
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
}