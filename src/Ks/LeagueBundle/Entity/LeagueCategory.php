<?php

namespace Ks\LeagueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\LeagueBundle\Entity\LeagueCategory
 *
 * @ORM\Table(name="ks_league_category")
 * @ORM\Entity(repositoryClass="Ks\LeagueBundle\Entity\LeagueCategoryRepository")
 */
class LeagueCategory
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
     * @ORM\OneToMany(targetEntity="Ks\LeagueBundle\Entity\LeagueLevel", mappedBy="category", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
    private $levels;
    
    /*
     * @ORM\OneToMany(targetEntity="Ks\LeagueBundle\Entity\Historic", mappedBy="leagueCategory")
     * 
     * @var ArrayCollection
     */
    private $leaguesHistorics;
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    public function __construct()
    {
        $this->levels = new \Doctrine\Common\Collections\ArrayCollection();
        $this->leaguesHistorics = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add levels
     *
     * @param Ks\LeagueBundle\Entity\LeagueLevel $levels
     */
    public function addLeagueLevel(\Ks\LeagueBundle\Entity\LeagueLevel $levels)
    {
        $this->levels[] = $levels;
    }

    /**
     * Get levels
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getLevels()
    {
        return $this->levels;
    }
}