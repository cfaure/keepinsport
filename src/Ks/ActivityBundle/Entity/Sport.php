<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Sport
 *
 * @ORM\Table(name="ks_sport")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\SportRepository")
 * 
 */
class Sport
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
     * @ORM\Column(type="string", length=10,nullable="true")
     * 
     * @var string
     */
    protected $code;
    
    
    /**
     * @ORM\Column(type="string", length=64)
     * 
     * @var string
     */
    protected $label;
    
    /**
     * @ORM\Column(type="string", length=255, nullable="true")
     * 
     * @var string
     */
    protected $site;
     
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\SportType", inversedBy="sports", fetch="LAZY")
     * 
     * @var Ks\ActivityBundle\Entity\SportType
     */
    protected $sportType;
    
    /**
     * @ORM\Column(type="string", length=45, nullable="true")
     * 
     * @var string
     */
    protected $codeSport;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\ActivitySession", mappedBy="sport")
     */
    protected $sportActivities;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\EventBundle\Entity\Event", mappedBy="sport")
     */
    protected $events;
    
    /**
     * @ORM\ManyToMany(targetEntity="Ks\ActivityBundle\Entity\SportGround")
     * @ORM\JoinTable(name="ks_sport_has_ground")
     */
    protected $sportsGrounds;
    
    /**
     * @ORM\Column(type="boolean")
     * 
     * @var boolean
     */
    protected $sportsGroundsEnabled;
    
    /**
     * 
     */
    public function __construct()
    {
    }
    
    /**
     * Get id
     *
     * @return string 
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
     * Set site
     *
     * @param string $site
     */
    public function setSite($site)
    {
        $this->site = $site;
    }

    /**
     * Get site
     *
     * @return string 
     */
    public function getSite()
    {
        return $this->site;
    }
 

    public function __toString(){
       return  $this->label;

    }

    /**
     * Set sportType
     *
     * @param Ks\ActivityBundle\Entity\SportType $sportType
     */
    public function setSportType(\Ks\ActivityBundle\Entity\SportType $sportType)
    {
        $this->sportType = $sportType;
    }

    /**
     * Get sportType
     *
     * @return Ks\ActivityBundle\Entity\SportType 
     */
    public function getSportType()
    {
        return $this->sportType;
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
     * Set codeSport
     *
     * @param string $codeSport
     */
    public function setCodeSport($codeSport)
    {
        $this->codeSport = $codeSport;
    }

    /**
     * Get codeSport
     *
     * @return string 
     */
    public function getCodeSport()
    {
        return $this->codeSport;
    }

    /**
     * Add sportActivities
     *
     * @param Ks\ActivityBundle\Entity\ActivitySession $sportActivities
     */
    public function addActivitySession(\Ks\ActivityBundle\Entity\ActivitySession $sportActivities)
    {
        $this->sportActivities[] = $sportActivities;
    }

    /**
     * Get sportActivities
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getSportActivities()
    {
        return $this->sportActivities;
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
     * Set sportsGroundsEnabled
     *
     * @param boolean $sportsGroundsEnabled
     */
    public function setSportsGroundsEnabled($sportsGroundsEnabled)
    {
        $this->sportsGroundsEnabled = $sportsGroundsEnabled;
    }

    /**
     * Get sportsGroundsEnabled
     *
     * @return boolean 
     */
    public function getSportsGroundsEnabled()
    {
        return $this->sportsGroundsEnabled;
    }

    
    public function setSportsGrounds(\Doctrine\Common\Collections\Collection $sportsGrounds)
    {
        foreach($sportsGrounds as $sportGround) {
            $this->addSportGround($sportGround);
        }
    }

    /**
     * Add sportsGrounds
     *
     * @param Ks\ActivityBundle\Entity\SportGround $sportsGrounds
     */
    public function addSportGround(\Ks\ActivityBundle\Entity\SportGround $sportsGrounds)
    {
        $this->sportsGrounds[] = $sportsGrounds;
    }

    /**
     * Get sportsGrounds
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getSportsGrounds()
    {
        return $this->sportsGrounds;
    }
}