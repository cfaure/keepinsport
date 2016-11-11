<?php

namespace Ks\TrophyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\TrophyBundle\Entity\TrophyCategory
 *
 * @ORM\Table(name="ks_trophy_category")
 * @ORM\Entity(repositoryClass="Ks\TrophyBundle\Entity\TrophyCategoryRepository")
 */
class TrophyCategory
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
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=255)
     */
    private $label;
    
    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\TrophyBundle\Entity\Trophy", mappedBy="category", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
    private $trophies;
    
    public function __construct()
    {
        $this->trophies = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add trophies
     *
     * @param Ks\TrophyBundle\Entity\Trophy $trophies
     */
    public function addTrophy(\Ks\TrophyBundle\Entity\Trophy $trophies)
    {
        $this->trophies[] = $trophies;
    }

    /**
     * Get trophies
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTrophies()
    {
        return $this->trophies;
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