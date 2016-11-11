<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\ActivityBundle\Entity\Article
 *
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\ArticleRepository")
 */
class Article extends Activity
{
    
    /**
     * @ORM\OneToMany(targetEntity="\Ks\ActivityBundle\Entity\UserModifiesArticle", mappedBy="article", cascade={"remove", "persist"})
     */
    protected $modifications;
    
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * 
     * @var boolean
     */
    protected $isBeingEdited;  
    
    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     * @var datetime
     */
    protected $isBeingEditedDate;
    
    /**
     * @ORM\OneToOne(targetEntity="Ks\EventBundle\Entity\Event", cascade={"remove", "persist"})
    */
    protected $event;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\ArticleTag", inversedBy="articles")
     */
    protected $categoryTag;
    
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
     * @ORM\Column(name="distance", type="decimal", precision="6", scale="2", nullable=true)
     * @Assert\Min(limit = "0", message = "La distance ne peut pas être inférieur à 0")
     * @var decimal
     */
    protected $distance;
    
    /**
     * @ORM\Column(name="elevationGain", type="decimal", nullable=true)
     * @Assert\Min(limit = "0", message = "Le gain d'altitude ne peut pas être inférieur à 0")
     * @var decimal
     */
    protected $elevationGain;
    
    /**
     * @ORM\Column(name="elevationLost", type="decimal", nullable=true)
     * @Assert\Min(limit = "0", message = "La perte d'altitude ne peut pas être inférieur à 0")
     * @var decimal
     */
    protected $elevationLost;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Sport", inversedBy="sportActivities")
     * 
     * @var Ks\ActivityBundle\Entity\Sport
     */
    protected $sport;
    
    /**
     * @var Ks\UserBundle\Entity\EquipmentType $type
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\EquipmentType", inversedBy="equipments")
     */
    protected $equipmentType;
    
    /**
     * @var string $brand
     *
     * @ORM\Column(name="brand", type="string", length=255, nullable=true)
     */
    protected $brand;
    
    /**
     * @var Ks\CoachingBundle\Entity\CoachingPlan $coachingPlan
     * 
     * @ORM\ManyToOne(targetEntity="Ks\CoachingBundle\Entity\CoachingPlan")
     */
    protected $coachingPlan;
    
    public function __construct(\Ks\UserBundle\Entity\User $user = null)
    {
        parent::__construct($user);
        $this->modifications    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->description = null;
        $this->isBeingEdited = false;
        $this->isBeingEditedDate = new \DateTime();
        $this->type = "article";
    }  

    /**
     * Add modifications
     *
     * @param Ks\ActivityBundle\Entity\UserModifiesArticle $modification
     */
    public function addModification(\Ks\ActivityBundle\Entity\UserModifiesArticle $modification)
    {
        $this->modifications[] = $modification;
    }

    /**
     * Get modifications
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getModifications()
    {
        return $this->modifications;
    }
 
    /**
     * Set isBeingEdited
     *
     * @param boolean $isBeingEdited
     */
    public function setIsBeingEdited($isBeingEdited)
    {
        $this->isBeingEdited = $isBeingEdited;
    }

    /**
     * Get isBeingEdited
     *
     * @return boolean 
     */
    public function getIsBeingEdited()
    {
        return $this->isBeingEdited;
    }
    
    /**
     * Set event
     *
     * @param Ks\EventBundle\Entity\Event $event
     */
    public function setEvent(\Ks\EventBundle\Entity\Event $event)
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
     * Set isBeingEditedDate
     *
     * @param datetime $isBeingEditedDate
     */
    public function setIsBeingEditedDate($datetime)
    {
        $this->isBeingEditedDate = $datetime;
    }

    /**
     * Get isBeingEditedDate
     *
     * @return datetime 
     */
    public function getIsBeingEditedDate()
    {
        return $this->isBeingEditedDate;
    }



    /**
     * Set categoryTag
     *
     * @param Ks\ActivityBundle\Entity\ArticleTag $categoryTag
     */
    public function setCategoryTag(\Ks\ActivityBundle\Entity\ArticleTag $categoryTag)
    {
        $this->categoryTag = $categoryTag;
    }

    /**
     * Get categoryTag
     *
     * @return Ks\ActivityBundle\Entity\ArticleTag 
     */
    public function getCategoryTag()
    {
        return $this->categoryTag;
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
     * Set distance
     *
     * @param decimal $distance
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
    }

    /**
     * Get distance
     *
     * @return decimal 
     */
    public function getDistance()
    {
        return $this->distance;
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
     * Get elevationGain
     *
     * @return float 
     */
    public function getElevationGain()
    {
        return $this->elevationGain;
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
     * Get elevationLost
     *
     * @return float 
     */
    public function getElevationLost()
    {
        return $this->elevationLost;
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
     * Set equipmentType
     *
     * @param Ks\UserBundle\Entity\EquipmentType $equipmentType
     */
    public function setEquipmentType(\Ks\UserBundle\Entity\EquipmentType $equipmentType)
    {
        $this->equipmentType = $equipmentType;
    }

    /**
     * Get equipmentType
     *
     * @return Ks\UserBundle\Entity\EquipmentType 
     */
    public function getEquipmentType()
    {
        return $this->equipmentType;
    }
    
    /**
     * Set brand
     *
     * @param string $brand
     */
    public function setBrand($brand)
    {
        if( $brand == null) $brand == "";
        $this->brand = $brand;
    }

    /**
     * Get brand
     *
     * @return string 
     */
    public function getBrand()
    {
        return $this->brand;
    }
    
    /**
     * Set coachingPlan
     *
     * @param Ks\CoachingBundle\Entity\CoachingPlan $coachingPlan
     */
    public function setCoachingPlan(\Ks\CoachingBundle\Entity\CoachingPlan $coachingPlan)
    {
        $this->coachingPlan = $coachingPlan;
    }

    /**
     * Get coachingType
     *
     * @return Ks\CoachingBundle\Entity\CoachingPlan 
     */
    public function getCoachingPlan()
    {
        return $this->coachingPlan;
    }
}