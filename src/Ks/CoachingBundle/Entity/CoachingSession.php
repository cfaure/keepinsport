<?php

namespace Ks\CoachingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\CoachingBundle\Entity\CoachingSession
 *
 * @ORM\Table(name="ks_coaching_session")
 * @ORM\Entity(repositoryClass="Ks\CoachingBundle\Entity\CoachingSessionRepository")
 */
class CoachingSession
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
     * @ORM\ManyToOne(targetEntity="Ks\CoachingBundle\Entity\CoachingCategory")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true)
     */
    private $category;
    
    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
    
    /**
     * @var string $detail
     *
     * @ORM\Column(name="detail", type="string", length=512, nullable=true)
     */
    private $detail;
    
    /**
     * @ORM\Column(name="elevationGainMin", type="decimal", nullable=true)
     * @var decimal
     */
    protected $elevationGainMin;
    
    /**
     * @ORM\Column(name="elevationGainMax", type="decimal", nullable=true)
     * @var decimal
     */
    protected $elevationGainMax;
    
    /**
     * @ORM\Column(name="elevationLostMin", type="decimal", nullable=true)
     * @var decimal
     */
    protected $elevationLostMin;
    
    /**
     * @ORM\Column(name="elevationLostMax", type="decimal", nullable=true)
     * @var decimal
     */
    protected $elevationLostMax;
    
    /**
     * @ORM\Column(name="powMin", type="decimal", nullable=true)
     * @var decimal
     */
    protected $powMin;
    
    /**
     * @ORM\Column(name="powMax", type="decimal", nullable=true)
     * @var decimal
     */
    protected $powMax;
    
    /**
     * @ORM\Column(name="distanceMin", type="decimal", precision="6", nullable=true)
     * @var decimal
     */
    protected $distanceMin;
    
    /**
     * @ORM\Column(name="distanceMax", type="decimal", precision="6", nullable=true)
     * @var decimal
     */
    protected $distanceMax;
    
    /**
     * @ORM\Column(name="intervalDistance", type="decimal", precision="6", nullable=true)
     * @var decimal
     */
    protected $intervalDistance;
    
    /**
     * @ORM\Column(name="VMACoeff", type="decimal", precision="6", nullable=true)
     * @var decimal
     */
    protected $VMACoeff;
    
    /**
     * @ORM\Column(name="hrCoeffMin", type="integer", nullable=true)
     * @var string $hrCoeffMin
     */
    protected $hrCoeffMin;
    
    /**
     * @ORM\Column(name="hrCoeffMax", type="integer", nullable=true)
     * @var string $hrCoeffMax
     */
    protected $hrCoeffMax;
    
    /**
     * @ORM\Column(name="hrType", type="integer", nullable=true)
     * @var string $hrType
     */
    protected $hrType;
    
    /**
     * @ORM\Column(name="speedAverageMin", type="decimal", nullable=true)
     * @var decimal
     */
    protected $speedAverageMin;
    
    /**
     * @ORM\Column(name="speedAverageMax", type="decimal", nullable=true)
     * @var decimal
     */
    protected $speedAverageMax;
    
    /**
     * @ORM\Column(name="durationMin", type="time", nullable=true)
     * @var time
     */
    protected $durationMin;
    
    /**
     * @ORM\Column(name="durationMax", type="time", nullable=true)
     * @var time
     */
    protected $durationMax;
    
    /**
     * @ORM\Column(name="intervalDuration", type="time", nullable=true)
     * @var time
     */
    protected $intervalDuration;
    
    /**
     * @ORM\Column(name="elevationGain", type="decimal", nullable=true)
     * @var decimal
     */
    protected $elevationGain;
    
    /**
     * @ORM\Column(name="elevationLost", type="decimal", nullable=true)
     * @var decimal
     */
    protected $elevationLost;
    
    /**
     * @ORM\Column(name="distance", type="decimal", precision="6", scale="2", nullable=true)
     * @var decimal
     */
    protected $distance;
    
    /**
     * @ORM\Column(name="hrAverage", type="string", length="15", nullable=true)
     * @var string $hrAverage
     */
    protected $hrAverage;
    
    /**
     * @ORM\Column(name="speedAverage", type="decimal", nullable=true)
     * @var decimal
     */
    protected $speedAverage;
    
    /**
     * @ORM\Column(name="duration", type="time", nullable=true)
     * @var time
     */
    protected $duration;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Club", inversedBy="users")
     * @ORM\JoinColumn(name="club_id", referencedColumnName="id", nullable=true)
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
     * Set category
     *
     * @param Ks\CoachingBundle\Entity\CoachingCategory $category
     */
    public function setCategory(\Ks\CoachingBundle\Entity\CoachingCategory $category =null)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return string 
     */
    public function getCategory()
    {
        return $this->category;
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
    
    /**
     * Set detail
     *
     * @param text $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }

    /**
     * Get detail
     *
     * @return text 
     */
    public function getDetail()
    {
        return $this->detail;
    }
    
    /**
     * Set elevationGain
     *
     * @param float $elevationGain
     */
    public function setElevationGain($elevationGain)
    {
        $this->elevationGain = $elevationGain;
    }
    
    /**
     * Set elevationGainMin
     *
     * @param float $elevationGainMin
     */
    public function setElevationGainMin($elevationGainMin)
    {
        $this->elevationGainMin = $elevationGainMin;
    }
    
    /**
     * Set elevationGain
     *
     * @param float $elevationGain
     */
    public function setElevationGainMax($elevationGainMax)
    {
        $this->elevationGainMax = $elevationGainMax;
    }

    /**
     * Get elevationGain
     *
     * @return float 
     */
    public function getElevationGain()
    {
        return $this->elevationGain;
    }
    
    /**
     * Get elevationGainMin
     *
     * @return float 
     */
    public function getElevationGainMin()
    {
        return $this->elevationGainMin;
    }
    
    /**
     * Get elevationGainMax
     *
     * @return float 
     */
    public function getElevationGainMax()
    {
        return $this->elevationGainMax;
    }

    /**
     * Set elevationLost
     *
     * @param float $elevationLost
     */
    public function setElevationLost($elevationLost)
    {
        $this->elevationLost = $elevationLost;
    }
    
    /**
     * Set elevationLostMin
     *
     * @param float $elevationLostMin
     */
    public function setElevationLostMin($elevationLostMin)
    {
        $this->elevationLostMin = $elevationLostMin;
    }
    
    /**
     * Set elevationLostMax
     *
     * @param float $elevationLostMax
     */
    public function setElevationLostMax($elevationLostMax)
    {
        $this->elevationLostMax = $elevationLostMax;
    }

    /**
     * Get elevationLost
     *
     * @return float 
     */
    public function getElevationLost()
    {
        return $this->elevationLost;
    }
    
    /**
     * Get elevationLostMin
     *
     * @return float 
     */
    public function getElevationLostMin()
    {
        return $this->elevationLostMin;
    }
    
    /**
     * Get elevationLostMax
     *
     * @return float 
     */
    public function getElevationLostMax()
    {
        return $this->elevationLostMax;
    }
    
    /**
     * Set distance
     *
     * @param decimal $distance
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
    }
    
    /**
     * Set distanceMin
     *
     * @param decimal $distanceMin
     */
    public function setDistanceMin($distanceMin)
    {
        $this->distanceMin = $distanceMin;
    }
    
    /**
     * Set distanceMax
     *
     * @param decimal $distanceMax
     */
    public function setDistanceMax($distanceMax)
    {
        $this->distanceMax = $distanceMax;
    }
    
    /**
     * Set intervalDistance
     *
     * @param decimal $intervalDistance
     */
    public function setIntervalDistance($intervalDistance)
    {
        $this->intervalDistance = $intervalDistance;
    }

    /**
     * Get intervalDistance
     *
     * @return decimal 
     */
    public function getIntervalDistance()
    {
        return $this->intervalDistance;
    }
    
    /**
     * Set VMACoeff
     *
     * @param decimal $VMACoeff
     */
    public function setVMACoeff($VMACoeff)
    {
        $this->VMACoeff = $VMACoeff;
    }

    /**
     * Get VMACoeff
     *
     * @return decimal 
     */
    public function getVMACoeff()
    {
        return $this->VMACoeff;
    }
    
    /**
     * Get distanceMin
     *
     * @return decimal 
     */
    public function getDistanceMin()
    {
        return $this->distanceMin;
    }
    
    /**
     * Get distanceMax
     *
     * @return decimal 
     */
    public function getDistanceMax()
    {
        return $this->distanceMax;
    }
    
    /**
     * Set speedAverage
     *
     * @param integer $speedAverage
     */
    public function setSpeedAverage($speedAverage)
    {
        $this->speedAverage = $speedAverage;
    }
    
    /**
     * Set speedAverageMin
     *
     * @param integer $speedAverageMin
     */
    public function setSpeedAverageMin($speedAverageMin)
    {
        $this->speedAverageMin = $speedAverageMin;
    }
    
    /**
     * Set speedAverageMax
     *
     * @param integer $speedAverageMax
     */
    public function setSpeedAverageMax($speedAverageMax)
    {
        $this->speedAverageMax = $speedAverageMax;
    }

    /**
     * Get speedAverage
     *
     * @return integer 
     */
    public function getSpeedAverage()
    {
        return $this->speedAverage;
    }
    
    /**
     * Get speedAverageMin
     *
     * @return integer 
     */
    public function getSpeedAverageMin()
    {
        return $this->speedAverageMin;
    }
    
    /**
     * Get speedAverageMax
     *
     * @return integer 
     */
    public function getSpeedAverageMax()
    {
        return $this->speedAverageMax;
    }
    
    /**
     * Set hrAverage
     *
     * @param text $hrAverage
     */
    public function setHrAverage($hrAverage)
    {
        $this->hrAverage = $hrAverage;
    }
    
    /**
     * Set hrCoeffMin
     *
     * @param decimal $hrCoeffMin
     */
    public function setHrCoeffMin($hrCoeffMin)
    {
        $this->hrCoeffMin = $hrCoeffMin;
    }
    
    /**
     * Set hrCoeffMax
     *
     * @param decimal $hrCoeffMax
     */
    public function setHrCoeffMax($hrCoeffMax)
    {
        $this->hrCoeffMax = $hrCoeffMax;
    }
    
    /**
     * Set hrType
     *
     * @param integer $hrType
     */
    public function setHrType($hrType)
    {
        $this->hrType = $hrType;
    }

    /**
     * Get hrAverage
     *
     * @return text 
     */
    public function getHrAverage()
    {
        return $this->hrAverage;
    }
    
    /**
     * Get hrCoeffMin
     *
     * @return decimal
     */
    public function getHrCoeffMin()
    {
        return $this->hrCoeffMin;
    }
    
    /**
     * Get hrCoeffMax
     *
     * @return decimal
     */
    public function getHrCoeffMax()
    {
        return $this->hrCoeffMax;
    }
    
    /**
     * Get htType
     *
     * @return integer
     */
    public function getHrType()
    {
        return $this->hrType;
    }
    
    /**
     * Set powMin
     *
     * @param integer $powMin
     */
    public function setPowMin($powMin)
    {
        $this->powMin = $powMin;
    }
    
    /**
     * Set powMax
     *
     * @param integer $powMax
     */
    public function setPowMax($powMax)
    {
        $this->powMax = $powMax;
    }
    
    /**
     * Get powMin
     *
     * @return integer
     */
    public function getPowMin()
    {
        return $this->powMin;
    }
    
    /**
     * Get powMax
     *
     * @return integer
     */
    public function getPowMax()
    {
        return $this->powMax;
    }
    
    /**
     * Set duration
     *
     * @param time $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }
    
    /**
     * Set durationMin
     *
     * @param time $durationMin
     */
    public function setDurationMin($durationMin)
    {
        $this->durationMin = $durationMin;
    }
    
    /**
     * Set durationMax
     *
     * @param time $durationMax
     */
    public function setDurationMax($durationMax)
    {
        $this->durationMax = $durationMax;
    }
    
    /**
     * Set intervalDuration
     *
     * @param time $intervalDuration
     */
    public function setIntervalDuration($intervalDuration)
    {
        $this->intervalDuration = $intervalDuration;
    }

    /**
     * Get duration
     *
     * @return time 
     */
    public function getDuration()
    {
        return $this->duration;
    }
    
    /**
     * Get durationMin
     *
     * @return time 
     */
    public function getDurationMin()
    {
        return $this->durationMin;
    }
    
    /**
     * Get durationMax
     *
     * @return time 
     */
    public function getDurationMax()
    {
        return $this->durationMax;
    }
    
    /**
     * Get intervalDuration
     *
     * @return time 
     */
    public function getIntervalDuration()
    {
        return $this->intervalDuration;
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
}