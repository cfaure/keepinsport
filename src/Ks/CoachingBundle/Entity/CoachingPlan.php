<?php

namespace Ks\CoachingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\CoachingBundle\Entity\CoachingPlan
 *
 * @ORM\Table(name="ks_coaching_plan")
 * @ORM\Entity(repositoryClass="Ks\CoachingBundle\Entity\CoachingPlanRepository")
 */
class CoachingPlan
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
     * @ORM\ManyToOne(targetEntity="Ks\CoachingBundle\Entity\CoachingPlanType", fetch="LAZY")
     * 
     * @var Ks\CoachingBundle\Entity\CoachingPlanType
     */
    protected $coachingPlanType;
    
    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Club", inversedBy="users")
     */
    private $club;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="users")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\CoachingBundle\Entity\CoachingPlan")
     */
    private $father;
    
    /**
     * @var string $color
     *
     * @ORM\Column(name="color", type="string", length=10, nullable="true")
     */
    private $color;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\EventBundle\Entity\Event", mappedBy="coachingPlan")
     */
    protected $events;
    
    public function __construct(\Ks\ClubBundle\Entity\Club $club =null, \Ks\UserBundle\Entity\User $user =null)
    {
        $this->club = $club;
        $this->user = $user;
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
     * @param text $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return text 
     */
    public function getName()
    {
        return $this->name;
    }
    
    public function __toString(){
        return $this->type;
    }
    
    /**
     * Set color
     *
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * Get color
     *
     * @return string 
     */
    public function getColor()
    {
        return $this->color;
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
     * Set father
     *
     * @param Ks\CoachingBundle\Entity\CoachingPlan $father
     */
    public function setFather(\Ks\CoachingBundle\Entity\CoachingPlan $father)
    {
        $this->father = $father;
    }

    /**
     * Get father
     *
     * @return Ks\CoachingBundle\Entity\CoachingPlan
     */
    public function getFather()
    {
        return $this->father;
    }
    
    /**
     * Add events
     *
     * @param Ks\EventBundle\Entity\Event $events
     */
    public function addEvent(\Ks\EventBundle\Entity\Event $events)
    {
        $this->events[] = $events;
    }

    /**
     * Get events
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getEvents()
    {
        return $this->events;
    }
    
    /**
     * Set sportType
     *
     * @param Ks\CoachingBundle\Entity\CoachingPlanType $coachingPlanType
     */
    public function setCoachingPlanType(\Ks\CoachingBundle\Entity\CoachingPlanType $coachingPlanType)
    {
        $this->coachingPlanType = $coachingPlanType;
    }

    /**
     * Get sportType
     *
     * @return Ks\CoachingBundle\Entity\CoachingPlanType 
     */
    public function getCoachingPlanType()
    {
        return $this->coachingPlanType;
    }
}