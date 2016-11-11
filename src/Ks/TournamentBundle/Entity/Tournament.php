<?php

namespace Ks\TournamentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\TournamentBundle\Entity\Tournament
 *
 * @ORM\Table(name="ks_tournament")
 * @ORM\Entity(repositoryClass="Ks\TournamentBundle\Entity\TournamentRepository")
 */
class Tournament
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
     * @var Ks\ClubBundle\Entity\Club $club
     * 
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Club", inversedBy="tournaments")
     * @ORM\JoinColumn(name="club_id", referencedColumnName="id", nullable=true)
     */
    protected $club;
    
    /**
     * @var Ks\ActivityBundle\Entity\Sport $sport
     * 
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Sport")
     * @ORM\JoinColumn(name="sport_id", referencedColumnName="id", nullable=true)
     */
    protected $sport;
    
    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var datetime $startDate
     *
     * @ORM\Column(name="startDate", type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @var datetime $endDate
     *
     * @ORM\Column(name="endDate", type="datetime", nullable=true)
     */
    private $endDate;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\TournamentBundle\Entity\Round", mappedBy="tournament", cascade={"remove", "persist"})
     */
    protected $rounds;
    
    /**
     * @var Ks\UserBundle\Entity\User $firstUser
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="firstUser_id", referencedColumnName="id", nullable=true)
     */
    private $firstUser;
    
    /**
     * @var string $firstUsername
     *
     * @ORM\Column(name="firstUsername", type="string", length=255, nullable=true)
     */
    private $firstUsername;
    
    /**
     * @var Ks\UserBundle\Entity\User $user1
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="secondUser_id", referencedColumnName="id", nullable=true)
     */
    private $secondUser;
    
    /**
     * @var string $secondUsername
     *
     * @ORM\Column(name="secondUsername", type="string", length=255, nullable=true)
     */
    private $secondUsername;
    
    /**
     * @var Ks\UserBundle\Entity\User $user1
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="thirdUser_id", referencedColumnName="id", nullable=true)
     */
    private $thirdUser;
    
    /**
     * @var string $thirdUsername
     *
     * @ORM\Column(name="thirdUsername", type="string", length=255, nullable=true)
     */
    private $thirdUsername;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\Activity", mappedBy="tournament", cascade={"remove", "persist"})
     */
    protected $activities; 


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
     * Set startDate
     *
     * @param datetime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Get startDate
     *
     * @return datetime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param datetime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Get endDate
     *
     * @return datetime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
    public function __construct()
    {
        $this->rounds = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add rounds
     *
     * @param Ks\TournamentBundle\Entity\Round $rounds
     */
    public function addRound(\Ks\TournamentBundle\Entity\Round $rounds)
    {
        $this->rounds[] = $rounds;
    }

    /**
     * Get rounds
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getRounds()
    {
        return $this->rounds;
    }

    /**
     * Set club
     *
     * @param Ks\ClubBundle\Entity\Club $club
     */
    public function setClub(\Ks\ClubBundle\Entity\Club $club)
    {
        $this->club = $club;
    }

    /**
     * Get club
     *
     * @return Ks\ClubBundle\Entity\Club 
     */
    public function getClub()
    {
        return $this->club;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set firstUsername
     *
     * @param string $firstUsername
     */
    public function setFirstUsername($firstUsername)
    {
        $this->firstUsername = $firstUsername;
    }

    /**
     * Get firstUsername
     *
     * @return string 
     */
    public function getFirstUsername()
    {
        return $this->firstUsername;
    }

    /**
     * Set secondUsername
     *
     * @param string $secondUsername
     */
    public function setSecondUsername($secondUsername)
    {
        $this->secondUsername = $secondUsername;
    }

    /**
     * Get secondUsername
     *
     * @return string 
     */
    public function getSecondUsername()
    {
        return $this->secondUsername;
    }

    /**
     * Set thirdUsername
     *
     * @param string $thirdUsername
     */
    public function setThirdUsername($thirdUsername)
    {
        $this->thirdUsername = $thirdUsername;
    }

    /**
     * Get thirdUsername
     *
     * @return string 
     */
    public function getThirdUsername()
    {
        return $this->thirdUsername;
    }

    /**
     * Set firstUser
     *
     * @param Ks\UserBundle\Entity\User $firstUser
     */
    public function setFirstUser($firstUser)
    {
        $this->firstUser = $firstUser;
    }

    /**
     * Get firstUser
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getFirstUser()
    {
        return $this->firstUser;
    }

    /**
     * Set secondUser
     *
     * @param Ks\UserBundle\Entity\User $secondUser
     */
    public function setSecondUser($secondUser)
    {
        $this->secondUser = $secondUser;
    }

    /**
     * Get secondUser
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getSecondUser()
    {
        return $this->secondUser;
    }

    /**
     * Set thirdUser
     *
     * @param Ks\UserBundle\Entity\User $thirdUser
     */
    public function setThirdUser($thirdUser)
    {
        $this->thirdUser = $thirdUser;
    }

    /**
     * Get thirdUser
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getThirdUser()
    {
        return $this->thirdUser;
    }

    /**
     * Set sport
     *
     * @param Ks\ActivityBundle\Entity\Sport $sport
     */
    public function setSport(\Ks\ActivityBundle\Entity\Sport $sport)
    {
        $this->sport = $sport;
    }

    /**
     * Get sport
     *
     * @return Ks\ActivityBundle\Entity\Sport 
     */
    public function getSport()
    {
        return $this->sport;
    }

    /**
     * Add activities
     *
     * @param Ks\ActivityBundle\Entity\Activity $activities
     */
    public function addActivity(\Ks\ActivityBundle\Entity\Activity $activities)
    {
        $this->activities[] = $activities;
    }

    /**
     * Get activities
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getActivities()
    {
        return $this->activities;
    }
}