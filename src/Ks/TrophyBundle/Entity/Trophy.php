<?php

namespace Ks\TrophyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\TrophyBundle\Entity\Trophy
 *
 * @ORM\Table(name="ks_trophy")
 * @ORM\Entity(repositoryClass="Ks\TrophyBundle\Entity\TrophyRepository")
 */
class Trophy
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
     * @ORM\ManyToOne(targetEntity="Ks\TrophyBundle\Entity\TrophyCategory", inversedBy="trophies")
     * 
     * @var Ks\TrophyBundle\Entity\TrophyCategory
     */
    private $category;

    /**
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=255)
     */
    private $label;
    
    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=128)
     */
    private $code;

    /**
     * @ORM\Column(type="integer", options={"default" = 0})
     * 
     * @var integer
     */
    private $pointsNumber;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\TrophyBundle\Entity\UserWinTrophies", mappedBy="trophy", cascade={"remove", "persist"})
     */
    private $usersWhoHaveWon;  
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\TrophyBundle\Entity\ShowcaseExposesTrophies", mappedBy="trophy", cascade={"remove", "persist"})
     */
    private $showcasesWhereAreExposedIt;
    
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * 
     * @var boolean
     */
    private $inSeveralTimes;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     * 
     * @var integer
     */
    private $timesToComplete;
    
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
     * @ORM\ManyToOne(targetEntity="Ks\LeagueBundle\Entity\LeagueLevel", inversedBy="trophies")
     * @ORM\JoinColumn(name="leagueLevel_id", referencedColumnName="id", nullable=true)
     */
    private $leagueLevel;

    public function __construct()
    {
        $this->usersWhoHaveWon              = new \Doctrine\Common\Collections\ArrayCollection();
        $this->showcasesWhereAreExposedIt   = new \Doctrine\Common\Collections\ArrayCollection();
        $this->inSeveralTimes               = false;
        $this->timesToComplete              = 1;
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
     * @param Ks\TrophyBundle\Entity\TrophyCategory $category
     */
    public function setCategory(\Ks\TrophyBundle\Entity\TrophyCategory $category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return Ks\TrophyBundle\Entity\TrophyCategory 
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
     * Set pointsNumber
     *
     * @param integer $pointsNumber
     */
    public function setPointsNumber($pointsNumber)
    {
        $this->pointsNumber = $pointsNumber;
    }

    /**
     * Get pointsNumber
     *
     * @return integer 
     */
    public function getPointsNumber()
    {
        return $this->pointsNumber;
    }

    /**
     * Add usersWhoHaveWon
     *
     * @param Ks\TrophyBundle\Entity\UserWinTrophies $usersWhoHaveWon
     */
    public function addUserWinTrophies(\Ks\TrophyBundle\Entity\UserWinTrophies $usersWhoHaveWon)
    {
        $this->usersWhoHaveWon[] = $usersWhoHaveWon;
    }

    /**
     * Get usersWhoHaveWon
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUsersWhoHaveWon()
    {
        return $this->usersWhoHaveWon;
    }

    /**
     * Add showcasesWhereAreExposedIt
     *
     * @param Ks\TrophyBundle\Entity\ShowcaseExposesTrophies $showcasesWhereAreExposedIt
     */
    public function addShowcaseExposesTrophies(\Ks\TrophyBundle\Entity\ShowcaseExposesTrophies $showcasesWhereAreExposedIt)
    {
        $this->showcasesWhereAreExposedIt[] = $showcasesWhereAreExposedIt;
    }

    /**
     * Get showcasesWhereAreExposedIt
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getShowcasesWhereAreExposedIt()
    {
        return $this->showcasesWhereAreExposedIt;
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
     * Set inSeveralTimes
     *
     * @param boolean $inSeveralTimes
     */
    public function setInSeveralTimes($inSeveralTimes)
    {
        $this->inSeveralTimes = $inSeveralTimes;
    }

    /**
     * Get inSeveralTimes
     *
     * @return boolean 
     */
    public function getInSeveralTimes()
    {
        return $this->inSeveralTimes;
    }

    /**
     * Set timesToComplete
     *
     * @param integer $timesToComplete
     */
    public function setTimesToComplete($timesToComplete)
    {
        $this->timesToComplete = $timesToComplete;
    }

    /**
     * Get timesToComplete
     *
     * @return integer 
     */
    public function getTimesToComplete()
    {
        return $this->timesToComplete;
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
}