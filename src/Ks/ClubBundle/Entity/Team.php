<?php

namespace Ks\ClubBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ClubBundle\Entity\Team
 *
 * @ORM\Table(name="ks_team")
 * @ORM\Entity(repositoryClass="Ks\ClubBundle\Entity\TeamRepository")
 */
class Team
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
     * @ORM\Column(type="string", length=64)
     * 
     * @var string
     */
    protected $label;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Sport", inversedBy="teams")
     * 
     * @var Ks\ActivityBundle\Entity\Sport
     */
    protected $sport;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\ActivitySessionTeamSport", mappedBy="ourTeam", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
    //protected $homeGames;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\ActivitySessionTeamSport", mappedBy="opposingTeam", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
    //protected $awayGames;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ClubBundle\Entity\TeamHasUsers", mappedBy="team", cascade={"remove", "persist"} )
     */
    private $users;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ClubBundle\Entity\TeamComposition", mappedBy="team", cascade={"remove", "persist"} )
     */
    private $teamCompositions;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Club", inversedBy="teams")
    */
    private $club;


    public function __construct()
    {
        $this->homeGames    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->awayGames    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->players      = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users        = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add homeGames
     *
     * @param Ks\ActivityBundle\Entity\ActivitySessionTeamSport $homeGames
     */
    public function addActivitySessionTeamSport(\Ks\ActivityBundle\Entity\ActivitySessionTeamSport $homeGames)
    {
        $this->homeGames[] = $homeGames;
    }

    /**
     * Get homeGames
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getHomeGames()
    {
        return $this->homeGames;
    }

    /**
     * Get awayGames
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAwayGames()
    {
        return $this->awayGames;
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
     * Add teamCompositions
     *
     * @param Ks\ClubBundle\Entity\TeamComposition $teamCompositions
     */
    public function addTeamComposition(\Ks\ClubBundle\Entity\TeamComposition $teamCompositions)
    {
        $this->teamCompositions[] = $teamCompositions;
    }

    /**
     * Get teamCompositions
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTeamCompositions()
    {
        return $this->teamCompositions;
    }

    /**
     * Add users
     *
     * @param Ks\ClubBundle\Entity\TeamHasUsers $users
     */
    public function addTeamHasUsers(\Ks\ClubBundle\Entity\TeamHasUsers $users)
    {
        $this->users[] = $users;
    }

    /**
     * Get users
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }
}