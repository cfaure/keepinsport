<?php

namespace Ks\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\EventBundle\Entity\Event
 *
 * @ORM\Table(name="ks_event")
 * @ORM\Entity(repositoryClass="Ks\EventBundle\Entity\EventRepository")
 */
class Event
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var text $content
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var datetime $creationDate
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;
    
    /**
     * @var datetime $startDate
     *
     * @ORM\Column(name="startDate", type="datetime")
     */
    private $startDate;
    
    /**
     * @var datetime $endDate
     *
     * @ORM\Column(name="endDate", type="datetime")
     */
    private $endDate;
    
    
    
    /**
     * @var datetime $lastModificationDate
     *
     * @ORM\Column(name="lastModificationDate", type="datetime")
     */
    private $lastModificationDate;

    /**
     * @var Ks\UserBundle\Entity\User $author
     *
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Ks\ClubBundle\Entity\Club", inversedBy="events")
    */
    private $club;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Ks\CoachingBundle\Entity\CoachingPlan", inversedBy="events")
     * @ORM\JoinColumn(name="coachingPlan_id", referencedColumnName="id", nullable=true)
    */
    private $coachingPlan;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Ks\CoachingBundle\Entity\CoachingCategory", inversedBy="events")
     * @ORM\JoinColumn(name="coachingCategory_id", referencedColumnName="id", nullable=true)
    */
    private $coachingCategory;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Ks\CoachingBundle\Entity\CoachingSession", inversedBy="events")
     * @ORM\JoinColumn(name="coachingSession_id", referencedColumnName="id", nullable=true)
    */
    private $coachingSession;
    
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
     * @ORM\Column(name="distanceMin", type="decimal", precision="6", scale="2", nullable=true)
     * @var decimal
     */
    protected $distanceMin;
    
    /**
     * @ORM\Column(name="distanceMax", type="decimal", precision="6", scale="2", nullable=true)
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
     * @ORM\ManyToOne(targetEntity="Ks\EventBundle\Entity\TypeEvent", inversedBy="events")
     * @ORM\JoinColumn(name="typeEvent_id", referencedColumnName="id", nullable=true)
    */
    private $typeEvent;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\AgendaBundle\Entity\AgendaHasEvents", mappedBy="event", cascade={"persist"})
     * 
     * @var ArrayCollection
     */
    protected $agendas;
    
    /**
     * @ORM\OneToOne(targetEntity="Ks\EventBundle\Entity\GoogleEvent", cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="isGoogleEvent_id", referencedColumnName="id", onDelete="cascade")
    */
    private $isGoogleEvent;
    
    /**
     * @ORM\OneToOne(targetEntity="Ks\ActivityBundle\Entity\ActivitySession")
     * @ORM\JoinColumn(name="activitySession_id", referencedColumnName="id", onDelete="cascade")
    */
    private $activitySession;
    
    
     /**
     * @ORM\OneToMany(targetEntity="Ks\EventBundle\Entity\InvitationEvent", mappedBy="event", cascade={"remove", "persist"})
     */
    private $invitationEvent;
       
    /**
    * @var string $isConfrontation
    *
    * @ORM\Column(name="isConfrontation", type="boolean", nullable=true)
    */
    private $isConfrontation = false;
    
    /**
     * @var Ks\NotificationBundle\Entity\Notification $notification
     * @ORM\OneToMany(targetEntity="Ks\NotificationBundle\Entity\Notification", mappedBy="event", cascade={"remove", "persist"})
     * 
     */
    private $notifications;
    
    /**
     * @ORM\ManyToMany(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\JoinTable(name="ks_event_has_users")
     */
    protected $usersParticipations;
    
    /**
     * @var integer $maxParticipations
     *
     * @ORM\Column(name="maxParticipations", type="integer", nullable=true)
     */
    private $maxParticipations;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\AbstractActivity", mappedBy="linkedEvent", cascade={"remove", "persist"} )
     */
    private $abstractActivities;
    
    /**
    * @var boolean $isAllDay
    *
    * @ORM\Column(name="isAllDay", type="boolean")
    */
    private $isAllDay = false;
    
    /**
    * @var boolean $isPublic
    *
    * @ORM\Column(name="isPublic", type="boolean")
    */
    private $isPublic = false;
    
    /**
    * @var boolean $isWarning
    *
    * @ORM\Column(name="isWarning", type="boolean")
    */
    private $isWarning = false;
    
    /**
     * @ORM\OneToOne(targetEntity="Ks\EventBundle\Entity\Place", cascade={"persist"})
     * @Assert\Type(type="Ks\EventBundle\Entity\Place")
     */
    private $place;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Sport", inversedBy="events")
     * @ORM\JoinColumn(name="sport_id", referencedColumnName="id", nullable=true)
     * 
     * @var Ks\ActivityBundle\Entity\Sport
    */
    private $sport;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Intensity")
    */
    protected $difficulty;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Activity")
     * @ORM\JoinColumn(name="competition", referencedColumnName="id", nullable=true)
    */
    private $competition;
    
    public function __construct()
    {
        // Par défaut, la date de l'event est la date d'aujourd'hui
        $this->creationDate = new \Datetime(); 
        $this->lastModificationDate = new \Datetime();
        $this->startDate = new \Datetime();
        $this->endDate = new \Datetime();
        $this->usersParticipations  = new \Doctrine\Common\Collections\ArrayCollection();
        $this->abstractActivities  = new \Doctrine\Common\Collections\ArrayCollection();
        $this->typeEvent = null;
        $this->isAllDay = false;
        $this->isPublic = false;
        $this->isWarning = false;
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
    
    public function setId($id)
    {
        return $this->id = $id;
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
     * Get name + date
     *
     * @return text 
     */
    public function getNameAndDate()
    {
        return $this->startDate->format('d-m-y') . ' : ' . $this->name;
    }
    
    /**
     * Get date + Sport + nom
     *
     * @return text 
     */
    public function getDateSportAndDate()
    {
        if (!isset($this->sport) || is_null($this->sport)) return $this->startDate->format('d-m-y') . ' : ' . $this->name;
        else return $this->startDate->format('d-m-y') . ' : ' . $this->name . " (" . $this->getSport()->getLabel() . ")";
    }

    /**
     * Set content
     *
     * @param text $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return text 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set creationDate
     *
     * @param datetime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * Get creationDate
     *
     * @return datetime 
     */
    public function getCreationDate()
    {
        return $this->creationDate;
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
     * Get $endDate
     *
     * @return datetime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
    

    /**
     * Set lastModificationDate
     *
     * @param datetime $lastModificationDate
     */
    public function setLastModificationDate($lastModificationDate)
    {
        $this->lastModificationDate = $lastModificationDate;
    }

    /**
     * Get lastModificationDate
     *
     * @return datetime 
     */
    public function getLastModificationDate()
    {
        return $this->lastModificationDate;
    }
    
    /**
     * Set $typeEvent
     *
     * @param Ks\EventBundle\Entity\TypeEvent $typeEvent
     */
    public function setTypeEvent(/*\Ks\EventBundle\Entity\TypeEvent*/ $typeEvent = null)
    {
        $this->typeEvent = $typeEvent;
    }

    /**
     * Get $typeEvent
     *
     * @return Ks\EventBundle\Entity\TypeEvent
     */
    public function getTypeEvent()
    {
        return $this->typeEvent;
    }
    
    /**
     * Set $club
     *
     * @param Ks\ClubBundle\Entity\Club $myClub
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
     * Set coachingPlan
     *
     * @param Ks\CoachingBundle\Entity\CoachingPlan $coachingPlan
     */
    public function setCoachingPlan(\Ks\CoachingBundle\Entity\CoachingPlan $coachingPlan =null)
    {
        $this->coachingPlan = $coachingPlan;
    }

    /**
     * Get coachingPlan
     *
     * @return Ks\CoachingBundle\Entity\CoachingPlan
     */
    public function getCoachingPlan()
    {
        return $this->coachingPlan;
    }
    
    /**
     * Set coachingCategory
     *
     * @param Ks\CoachingBundle\Entity\CoachingCategory $coachingCategory
     */
    public function setCoachingCategory(\Ks\CoachingBundle\Entity\CoachingCategory $coachingCategory =null)
    {
        $this->coachingCategory = $coachingCategory;
    }

    /**
     * Get coachingCategory
     *
     * @return Ks\CoachingBundle\Entity\CoachingCategory
     */
    public function getCoachingCategory()
    {
        return $this->coachingCategory;
    }
    
    /**
     * Set coachingSession
     *
     * @param Ks\CoachingBundle\Entity\CoachingSession $coachingSession
     */
    public function setcoachingSession(\Ks\CoachingBundle\Entity\CoachingSession $coachingSession =null)
    {
        $this->coachingSession = $coachingSession;
    }

    /**
     * Get coachingSession
     *
     * @return Ks\CoachingBundle\Entity\CoachingSession
     */
    public function getcoachingSession()
    {
        return $this->coachingSession;
    }

    /**
     * Set coachingSessionDetail
     *
     * @param Ks\CoachingBundle\Entity\CoachingSession $coachingSession
     */
    public function setcoachingSessionDetail(\Ks\CoachingBundle\Entity\CoachingSession $coachingSession =null)
    {
        $this->coachingSession->setDetail($coachingSession->getDetail());
    }

    /**
     * Get coachingSessionDetail
     *
     * @return Ks\CoachingBundle\Entity\CoachingSession
     */
    public function getcoachingSessionDetail()
    {
        if ($this->coachingSession != null) return $this->coachingSession->getDetail();
        else return '';
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
    
    /**
     * Add agendas
     *
     * @param Ks\AgendaBundle\Entity\AgendaHasEvents $agendas
     */
    public function addAgendaHasEvents(\Ks\AgendaBundle\Entity\AgendaHasEvents $agendas)
    {
        $this->agendas[] = $agendas;
    }

    /**
     * Get agendas
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAgendas()
    {
        return $this->agendas;
    }

    /**
     * Set isGoogleEvent
     *
     * @param Ks\EventBundle\Entity\GoogleEvent $isGoogleEvent
     */
    public function setIsGoogleEvent(\Ks\EventBundle\Entity\GoogleEvent $isGoogleEvent)
    {
        $this->isGoogleEvent = $isGoogleEvent;
    }

    /**
     * Get isGoogleEvent
     *
     * @return Ks\EventBundle\Entity\GoogleEvent 
     */
    public function getIsGoogleEvent()
    {
        return $this->isGoogleEvent;
    }

    /**
     * Set activitySession
     *
     * @param Ks\ActivityBundle\Entity\ActivitySession $activitySession
     */
    public function setActivitySession(\Ks\ActivityBundle\Entity\ActivitySession $activitySession = null)
    {
        $this->activitySession = $activitySession;
    }

    /**
     * Get activitySession
     *
     * @return Ks\ActivityBundle\Entity\ActivitySession 
     */
    public function getActivitySession()
    {
        return $this->activitySession;
    }
    
    /**
     * Set isConfrontation
     *
     * @param boolean $isConfrontation
     */
    public function setIsConfrontation($isConfrontation)
    {
        $this->isConfrontation = $isConfrontation;
    }

    /**
     * Get isConfrontation
     *
     * @return boolean 
     */
    public function getIsConfrontation()
    {
        return $this->isConfrontation;
    }

    /**
     * Add invitationEvent
     *
     * @param Ks\EventBundle\Entity\InvitationEvent $invitationEvent
     */
    public function addInvitationEvent(\Ks\EventBundle\Entity\InvitationEvent $invitationEvent)
    {
        $this->invitationEvent[] = $invitationEvent;
    }

    /**
     * Get invitationEvent
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getInvitationEvent()
    {
        return $this->invitationEvent;
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
     * Add usersParticipations
     *
     * @param Ks\EventBundle\Entity\UserParticipatesEvent $usersParticipations
     */
    public function addUserParticipatesEvent(\Ks\EventBundle\Entity\UserParticipatesEvent $usersParticipations)
    {
        $this->usersParticipations[] = $usersParticipations;
    }

    /**
     * Get usersParticipations
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUsersParticipations()
    {
        return $this->usersParticipations;
    }

    /**
     * Add setUsersParticipations
     *
     * @param Ks\UserBundle\Entity\User $user
     */
    public function setUsersParticipations($usersParticipations)
    {
        $this->usersParticipations = $usersParticipations;
    }
    
    /**
     * Set maxParticipations
     *
     * @param integer $maxParticipations
     */
    public function setMaxParticipations($maxParticipations)
    {
        $this->maxParticipations = $maxParticipations;
    }

    /**
     * Get maxParticipations
     *
     * @return maxParticipations
     */
    public function getMaxParticipations()
    {
        return $this->maxParticipations;
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

    /**
     * Set isAllDay
     *
     * @param boolean $isAllDay
     */
    public function setIsAllDay($isAllDay)
    {
        $this->isAllDay = $isAllDay;
    }

    /**
     * Get isAllDay
     *
     * @return boolean 
     */
    public function getIsAllDay()
    {
        return $this->isAllDay;
    }

    /**
     * Set isPublic
     *
     * @param boolean $isPublic
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    /**
     * Get isPublic
     *
     * @return boolean 
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }
    
    /**
     * Set isWarning
     *
     * @param boolean $isWarning
     */
    public function setIsWarning($isWarning)
    {
        $this->isWarning = $isWarning;
    }

    /**
     * Get isWarning
     *
     * @return boolean 
     */
    public function getIsWarning()
    {
        return $this->isWarning;
    }

    /**
     * Set place
     *
     * @param Ks\EventBundle\Entity\Place $place
     */
    public function setPlace($place)
    {
        $this->place = $place;
    }

    /**
     * Get place
     *
     * @return Ks\EventBundle\Entity\Place 
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set sport
     *
     * @param Ks\ActivityBundle\Entity\Sport $sport
     */
    public function setSport($sport)
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
     * Set difficulty
     *
     * @param integer $difficulty
     */
    public function setDifficulty($difficulty)
    {
        $this->difficulty = $difficulty;
    }

    /**
     * Get difficulty
     *
     * @return integer
     */
    public function getDifficulty()
    {
        return $this->difficulty;
    }
    
    /**
     * Set competition
     *
     * @param Ks\ActivityBundle\Entity\Activity $competition
     */
    public function setCompetition(\Ks\ActivityBundle\Entity\Activity $competition = null)
    {
        $this->competition = $competition;
    }

    /**
     * Get competition
     *
     * @return Ks\ActivityBundle\Entity\Activity 
     */
    public function getCompetition()
    {
        return $this->competition;
    }
}