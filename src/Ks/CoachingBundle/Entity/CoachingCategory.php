<?php

namespace Ks\CoachingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\CoachingBundle\Entity\CoachingCategory
 *
 * @ORM\Table(name="ks_coaching_category")
 * @ORM\Entity(repositoryClass="Ks\CoachingBundle\Entity\CoachingCategoryRepository")
 */
class CoachingCategory
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
     * @ORM\Column(name="name", type="string", length=25)
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
     * @var string $color
     *
     * @ORM\Column(name="color", type="string", length=10, nullable="true")
     */
    private $color;
    
    /**
    * @var boolean $isCompetition
    *
    * @ORM\Column(name="isCompetition", type="boolean")
    */
    private $isCompetition = false;
    
    /**
    * @var boolean $isEnabled
    *
    * @ORM\Column(name="isEnabled", type="boolean")
    */
    private $isEnabled = true;
    
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
        return $this->name;
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
     * Set isCompetition
     *
     * @param boolean $isCompetition
     */
    public function setIsCompetition($isCompetition)
    {
        $this->isCompetition = $isCompetition;
    }

    /**
     * Get isCompetition
     *
     * @return boolean 
     */
    public function getIsCompetition()
    {
        return $this->isCompetition;
    }
    
    /**
     * Set isEnabled
     *
     * @param boolean $isEnabled
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    /**
     * Get isEnabled
     *
     * @return boolean 
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }
    
    /**
     * Get isCompetition + name
     *
     * @return text 
     */
    public function getIsCompetitionAndName()
    {
        if ($this->isCompetition) return 'COMPETITIONS';
        else return $this->name;
    }
}