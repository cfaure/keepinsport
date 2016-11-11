<?php

namespace Ks\LeagueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\LeagueBundle\Entity\LeagueLevel
 *
 * @ORM\Table(name="ks_league_level")
 * @ORM\Entity(repositoryClass="Ks\LeagueBundle\Entity\LeagueLevelRepository")
 */
class LeagueLevel
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
     * @var integer
     * 
     * @ORM\Column(name="rank", type="integer")
     */
    private $rank;
    
    /**
     * @var integer
     * 
     * @ORM\Column(name="starNumber", type="integer")
     */
    private $starNumber;

    /**
     * @ORM\ManyToOne(targetEntity="Ks\LeagueBundle\Entity\LeagueCategory", inversedBy="levels")
     * 
     * @var Ks\TrophyBundle\Entity\TrophyCategory
     */
    private $category;
    
    /*
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\User", mappedBy="leagueLevel")
     * 
     * @var ArrayCollection
     */
    private $users;
    
    /*
     * @ORM\OneToMany(targetEntity="Ks\LeagueBundle\Entity\Historic", mappedBy="leagueLevel")
     * 
     * @var ArrayCollection
     */
    private $leaguesHistorics;
    
    /*
     * @ORM\OneToMany(targetEntity="Ks\TrophyBundle\Entity\Trophy", mappedBy="leagueLevel")
     * 
     * @var ArrayCollection
     */
    private $trophies;
    
    
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->seasonsHistoric  = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set category
     *
     * @param Ks\LeagueBundle\Entity\LeagueCategory $category
     */
    public function setCategory(\Ks\LeagueBundle\Entity\LeagueCategory $category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return Ks\LeagueBundle\Entity\LeagueCategory 
     */
    public function getCategory()
    {
        return $this->category;
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
     * Set rank
     *
     * @param integer $rank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
    }

    /**
     * Get rank
     *
     * @return integer 
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set starNumber
     *
     * @param integer $starNumber
     */
    public function setStarNumber($starNumber)
    {
        $this->starNumber = $starNumber;
    }

    /**
     * Get starNumber
     *
     * @return integer 
     */
    public function getStarNumber()
    {
        return $this->starNumber;
    }
}