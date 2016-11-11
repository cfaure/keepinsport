<?php

namespace Ks\LeagueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\LeagueBundle\Entity\Historic
 *
 * @ORM\Table(name="ks_league_historic")
 * @ORM\Entity(repositoryClass="Ks\LeagueBundle\Entity\HistoricRepository")
 */
class Historic
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
     * @var Ks\UserBundle\Entity\User $user
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="leaguesHistorics")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;
    
    /**
     * @var string $month
     *
     * @ORM\Column(name="month", type="string")
     */
    private $month;
    
    /**
     * @var string $year
     *
     * @ORM\Column(name="year", type="string")
     */
    private $year;
    
    /**
     * @var Ks\LeagueBundle\Entity\LeagueLevel $leagueLevel
     * 
     * @ORM\ManyToOne(targetEntity="Ks\LeagueBundle\Entity\LeagueLevel", inversedBy="leaguesHistorics")
     * @ORM\JoinColumn(name="leagueLevel_id", referencedColumnName="id", nullable=false)
     */
    private $leagueLevel;
    
    /**
     * @var Ks\LeagueBundle\Entity\LeagueCategory $leagueCategory
     * 
     * @ORM\ManyToOne(targetEntity="Ks\LeagueBundle\Entity\LeagueCategory", inversedBy="leaguesHistorics")
     * @ORM\JoinColumn(name="leagueCategory_id", referencedColumnName="id", nullable=false)
     */
    private $leagueCategory;
    
    /**
     * @ORM\Column(type="integer")
     * 
     * @var integer
     */
    private $points;
    
    /**
     * @var integer $rank
     *
     * @ORM\Column(name="rank", type="integer")
     */
    private $rank;

    public function __construct()
    {
        /*$now = new \DateTime('now');
        $this->month       = $now->format("m");
        $this->year        = $now->format("Y");*/
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
     * Set month
     *
     * @param string $month
     */
    public function setMonth($month)
    {
        $this->month = $month;
    }

    /**
     * Get month
     *
     * @return string 
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set year
     *
     * @param string $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * Get year
     *
     * @return string 
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set points
     *
     * @param integer $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
    }

    /**
     * Get points
     *
     * @return integer 
     */
    public function getPoints()
    {
        return $this->points;
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

    /**
     * Set leagueLevel
     *
     * @param Ks\LeagueBundle\Entity\LeagueLevel $leagueLevel
     */
    public function setLeagueLevel(\Ks\LeagueBundle\Entity\LeagueLevel $leagueLevel)
    {
        $this->leagueLevel = $leagueLevel;
    }

    /**
     * Get leagueLevel
     *
     * @return Ks\LeagueBundle\Entity\LeagueLevel 
     */
    public function getLeagueLevel()
    {
        return $this->leagueLevel;
    }

    /**
     * Set leagueCategory
     *
     * @param Ks\LeagueBundle\Entity\LeagueCategory $leagueCategory
     */
    public function setLeagueCategory(\Ks\LeagueBundle\Entity\LeagueCategory $leagueCategory)
    {
        $this->leagueCategory = $leagueCategory;
    }

    /**
     * Get leagueCategory
     *
     * @return Ks\LeagueBundle\Entity\LeagueCategory 
     */
    public function getLeagueCategory()
    {
        return $this->leagueCategory;
    }
}