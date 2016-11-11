<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * Description of ActivitySession
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\ActivitySessionRepository")
 */
class ActivitySession extends Activity
{    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Sport", inversedBy="sportActivities")
     * 
     * @var Ks\ActivityBundle\Entity\Sport
     */
    protected $sport;
    
    /**
     * @ORM\Column(name="duration", type="time", nullable=true)
     * @Assert\NotBlank(message="Le temps doit être renseigné !")
     * @Assert\Time()
     * @var time
     */
    protected $duration;
    
    /**
     * @ORM\Column(name="movingDuration", type="integer", nullable=true)
     * @var int
     */
    protected $timeMoving;
    
    /**
     * @ORM\OneToOne(targetEntity="Ks\ActivityBundle\Entity\ExternalSource")
     * @ORM\JoinColumn(name="external_source_id", referencedColumnName="id")
     * 
     * @var Ks\ActivityBundle\Entity\ExternalSource
     */
    protected $externalSource;
    
    /**
     * @ORM\Column(type="integer")
     * @Assert\Min(limit = "0", message = "Les calories ne peuvent pas être inférieur à 0")
     * @var integer
     */
    protected $calories;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var integer
     */
    protected $achievement; 
    
    /**
     * @var boolean $wasOfficial
     * 
     * @ORM\Column(name="wasOfficial", type="boolean")
     */
    protected $wasOfficial;
        
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\StateOfHealth")
    */
    private $stateOfHealth;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Weather")
    */
    private $weather;
    
    /**
     * TODO: passer en enum...
     * 
     * @ORM\Column(type="string", length="32")
     * @var type 
     */
    protected $source;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\ActivityComeFromService", mappedBy="activity", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
    protected $services;
        
    /**
     * Données de tracking de la session, au format json.
     * (contient les waypoints avec coordonnées gps, l'altitude, la vitesse etc.
     * Voir sur google docs pour plus d'infos.
     * 
     * @ORM\Column(type="text", nullable=true)
     * @var json
     */
    protected $trackingDatas;
    
    /**
     * @ORM\ManyToMany(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\JoinTable(name="ks_user_participates_activity")
     */
    protected $usersWhoHaveParticipated;
    
    /**
     * @ORM\ManyToMany(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\JoinTable(name="ks_opponents_participates_activity")
     */
    protected $opponentsWhoHaveParticipated;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Result")
    */
    protected $result;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Intensity")
    */
    protected $intensity;
    
    /**
    * @var decimal $points
    *
    * @ORM\Column(name="points", type="decimal", nullable=true)
    */
    protected $pointsWon;
    
    /**
     * @ORM\ManyToMany(targetEntity="Ks\UserBundle\Entity\Equipment")
     * @ORM\JoinTable(name="ks_equipment_used_in_activity")
     */
    protected $equipments;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\SportGround", inversedBy="activities", fetch="LAZY")
     * 
     * @var Ks\ActivityBundle\Entity\SportGround
     */
    protected $sportGround;

    /**
     *
     * @param \Ks\UserBundle\Entity\User $user
     * @param type $duration 
     */
    public function __construct(\Ks\UserBundle\Entity\User $user = null, $duration = null)
    {
        parent::__construct($user);
        $this->type = "session";
        $this->wasOfficial  = false;
        $this->duration     = $duration;
        $this->services     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->usersWhoHaveParticipated     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->opponentsWhoHaveParticipated = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set externalSource
     *
     * @param Ks\ActivityBundle\Entity\ExternalSource $externalSource
     */
    public function setExternalSource(\Ks\ActivityBundle\Entity\ExternalSource $externalSource)
    {
        $this->externalSource = $externalSource;
    }

    /**
     * Get externalSource
     *
     * @return Ks\ActivityBundle\Entity\ExternalSource 
     */
    public function getExternalSource()
    {
        return $this->externalSource;
    }
    
    /**
     * Set calories
     *
     * @param string $calories
     */
    public function setCalories($calories)
    {
        $this->calories = $calories;
    }

    /**
     * Get calories
     *
     * @return string 
     */
    public function getCalories()
    {
        return $this->calories;
    }
    
    /**
     * Set achievement
     *
     * @param integer $achievement
     */
    public function setAchievement($achievement)
    {
        $this->achievement = $achievement;
    }

    /**
     * Get achievement
     *
     * @return integer
     */
    public function getAchievement()
    {
        return $this->achievement;
    }

    /**
     * Set wasOfficial
     *
     * @param boolean $wasOfficial
     */
    public function setWasOfficial($wasOfficial)
    {
        $this->wasOfficial = $wasOfficial;
    }

    /**
     * Get wasOfficial
     *
     * @return boolean 
     */
    public function getWasOfficial()
    {
        return $this->wasOfficial;
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
     * Get duration
     *
     * @return time 
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set stateOfHealth
     *
     * @param Ks\ActivityBundle\Entity\StateOfHealth $stateOfHealth
     */
    public function setStateOfHealth($stateOfHealth)
    {
        $this->stateOfHealth = $stateOfHealth;
    }

    /**
     * Get stateOfHealth
     *
     * @return Ks\ActivityBundle\Entity\StateOfHealth 
     */
    public function getStateOfHealth()
    {
        return $this->stateOfHealth;
    }

    /**
     * Set weather
     *
     * @param Ks\ActivityBundle\Entity\Weather $weather
     */
    public function setWeather(\Ks\ActivityBundle\Entity\Weather $weather)
    {
        $this->weather = $weather;
    }

    /**
     * Get weather
     *
     * @return Ks\ActivityBundle\Entity\Weather 
     */
    public function getWeather()
    {
        return $this->weather;
    }
    
    /**
     * Add services
     *
     * @param Ks\ActivityBundle\Entity\ActivityComeFromService $services
     */
    public function addActivityComeFromService(\Ks\ActivityBundle\Entity\ActivityComeFromService $services)
    {
        $this->services[] = $services;
    }

    /**
     * Get services
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getServices()
    {
        return $this->services;
    }
    
    /**
     *
     * @param array $trackingDatas 
     */
    public function setTrackingDatas(array $trackingDatas)
    {
        $this->trackingDatas = json_encode($trackingDatas);
    }
    
    /**
     *
     * @return array
     */
    public function getTrackingDatas()
    {
        if( $this->trackingDatas != null )
            return json_decode($this->trackingDatas, true);
        else 
            return null;
    }
    
    public function getRawTrackingDatas()
    {
        return $this->trackingDatas;
    }
 
    /**
     * Add addUserWhoHasParticipated
     *
     * @param Ks\UserBundle\Entity\User $user
     */
    public function addUserWhoHasParticipated(\Ks\UserBundle\Entity\User $user)
    {
        $this->usersWhoHaveParticipated[] = $user;
    }
    
    /**
     * Remove usersWhoHaveParticipated
     *
     */
    public function resetUsersWhoHaveParticipated()
    {
        $this->usersWhoHaveParticipated = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add setUsersWhoHaveParticipated
     *
     * @param Ks\UserBundle\Entity\User $user
     */
    public function setUsersWhoHaveParticipated($usersWhoHaveParticipated)
    {
        $this->usersWhoHaveParticipated = $usersWhoHaveParticipated;
    }

    /**
     * Get usersWhoHaveParticipated
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUsersWhoHaveParticipated()
    {
        return $this->usersWhoHaveParticipated;
    }
    
    /**
     * Add addOpponentWhoHasParticipated
     *
     * @param Ks\UserBundle\Entity\User $user
     */
    public function addOpponentWhoHasParticipated(\Ks\UserBundle\Entity\User $user)
    {
        $this->opponentsWhoHaveParticipated[] = $user;
    }
    
    /**
     * Remove opponentsWhoHaveParticipated
     *
     */
    public function resetOpponentsWhoHaveParticipated()
    {
        $this->opponentsWhoHaveParticipated = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add setOpponentsWhoHaveParticipated
     *
     * @param Ks\UserBundle\Entity\User $user
     */
    public function setOpponentsWhoHaveParticipated($opponentsWhoHaveParticipated)
    {
        $this->opponentsWhoHaveParticipated = $opponentsWhoHaveParticipated;
    }
 

    /**
     * Get opponentsWhoHaveParticipated
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getOpponentsWhoHaveParticipated()
    {
        return $this->opponentsWhoHaveParticipated;
    }

   

    /**
     * Set result
     *
     * @param Ks\ActivityBundle\Entity\Result $result
     */
    public function setResult(\Ks\ActivityBundle\Entity\Result $result)
    {
        $this->result = $result;
    }

    /**
     * Get result
     *
     * @return Ks\ActivityBundle\Entity\Result 
     */
    public function getResult()
    {
        return $this->result;
    }
    
    /**
     * Set isValidate
     *
     * @param boolean $isValidate
     */
    public function setIsValidate($isValidate)
    {
        $this->isValidate = $isValidate;
    }

    /**
     * Get isValidate
     *
     * @return boolean 
     */
    public function getIsValidate()
    {
        return $this->isValidate;
    }
  
    /**
     * Set event
     *
     * @param Ks\EventBundle\Entity\Event $event
     */
    public function setEvent(\Ks\EventBundle\Entity\Event $event =null)
    {
        $this->event = $event;
    }

    /**
     * Get event
     *
     * @return Ks\EventBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }
    
    /**
     * 
     */
    public function getSource()
    {
        return $this->source;
    }
    
    public function setSource($source)
    {
        $this->source = $source;
    }
    
    /**
     * Set timeMoving
     *
     * @param int $timeMoving in sec.
     */
    public function setTimeMoving($timeMoving)
    {
        $this->timeMoving = $timeMoving;
    }

    /**
     * Get timeMoving
     *
     * @return int
     */
    public function getTimeMoving()
    {
        return $this->timeMoving;
    }
    
    /**
     * Set points
     *
     * @param decimal $points
     */
    public function setPointsWon($points)
    {
        $this->pointsWon = $points;
    }

    /**
     * Get points
     *
     * @return decimal 
     */
    public function getPointsWon()
    {
        return $this->pointsWon;
    }
    

    /**
     * Set intensity
     *
     * @param Ks\ActivityBundle\Entity\Intensity $intensity
     */
    public function setIntensity($intensity)
    {
        $this->intensity = $intensity;
    }

    /**
     * Get intensity
     *
     * @return Ks\ActivityBundle\Entity\Intensity 
     */
    public function getIntensity()
    {
        return $this->intensity;
    }
    
    /**
     * Add equipments
     *
     * @param Ks\UserBundle\Entity\Equipment $equipments
     */
    public function addEquipment(\Ks\UserBundle\Entity\Equipment $equipment)
    {
        $this->equipments[] = $equipment;
    }

    /**
     * Get equipments
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getEquipments()
    {
        return $this->equipments;
    }
    
    public function setEquipments(\Doctrine\Common\Collections\Collection $equipments)
    {
        foreach($equipments as $equipment) {
            $this->addEquipment($equipment);
        }
    }
    
    /**
     * Set sportGround
     *
     * @param Ks\ActivityBundle\Entity\SportGround $sportGround
     */
    public function setSportGround($sportGround)
    {
        $this->sportGround = $sportGround;
    }

    /**
     * Get sportGround
     *
     * @return Ks\ActivityBundle\Entity\SportGround 
     */
    public function getSportGround()
    {
        return $this->sportGround;
    }
    
    /**
     *
     * @return type 
     */
    public function __toString(){
        return $this->sport->getLabel()." - ".$this->duration->format("H:i:s")." - le ".$this->issuedAt->format("d/m/Y")."";
    } 
    

    

   
}