<?php

namespace Ks\ClubBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\ClubBundle\Entity\TeamComposition
 *
 * @ORM\Table(name="ks_team_composition")
 * @ORM\Entity(repositoryClass="Ks\ClubBundle\Entity\TeamCompositionRepository")
 */
class TeamComposition
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
    protected $name;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime()
     * @var datetime
     */
    private $date;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ClubBundle\Entity\TeamCompositionHasUsers", mappedBy="teamComposition", cascade={"remove", "persist"} )
     */
    private $users;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Team", inversedBy="teamCompositions")
     */
    private $team;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\NotificationBundle\Entity\Notification", mappedBy="teamComposition", cascade={"remove", "persist"} )
     */
    private $notifications;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\AbstractActivity", mappedBy="teamComposition", cascade={"remove", "persist"} )
     */
    private $abstractActivities;



    public function __construct( \Ks\ClubBundle\Entity\Team $team )
    {
        $this->team         = $team;
        $this->users        = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications        = new \Doctrine\Common\Collections\ArrayCollection();
        $this->abstractActivities        = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set date
     *
     * @param datetime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return datetime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Add users
     *
     * @param Ks\ClubBundle\Entity\TeamCompositionHasUsers $users
     */
    public function addTeamCompositionHasUsers(\Ks\ClubBundle\Entity\TeamCompositionHasUsers $users)
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

    /**
     * Set team
     *
     * @param Ks\ClubBundle\Entity\Team $team
     */
    public function setTeam(\Ks\ClubBundle\Entity\Team $team)
    {
        $this->team = $team;
    }

    /**
     * Get team
     *
     * @return Ks\ClubBundle\Entity\Team 
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Add notifications
     *
     * @param Ks\NotificationBundle\Entity\Notification $notifications
     */
    public function addNotification(\Ks\NotificationBundle\Entity\Notification $notifications)
    {
        $this->notifications[] = $notifications;
    }

    /**
     * Get notifications
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Add abstractActivities
     *
     * @param Ks\ActivityBundle\Entity\AbstractActivity $abstractActivities
     */
    public function addAbstractActivity(\Ks\ActivityBundle\Entity\AbstractActivity $abstractActivities)
    {
        $this->abstractActivities[] = $abstractActivities;
    }

    /**
     * Get abstractActivities
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAbstractActivities()
    {
        return $this->abstractActivities;
    }
}