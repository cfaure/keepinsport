<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\UserBundle\Entity\UserHasSportFrequency
 *
 * @ORM\Table(name="ks_user_has_sport_frequency")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\UserHasSportFrequencyRepository")
 */
class UserHasSportFrequency
{
        
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     */
    private $user;
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Sport")
     */
    private $sport;

    /**
     * @ORM\Column(name="mailTime", type="time", nullable=true)
     * @Assert\Time()
     * @var time
     */
    protected $mailTime;

   /**
    * @var boolean $isScheduledOnMon
    *
    * @ORM\Column(name="isScheduledOnMon", type="boolean", nullable=true)
    */
    protected $isScheduledOnMon;

    /**
    * @var boolean $isScheduledOnTue
    *
    * @ORM\Column(name="isScheduledOnTue", type="boolean", nullable=true)
    */
    protected $isScheduledOnTue;
    
    /**
    * @var boolean $isScheduledOnWed
    *
    * @ORM\Column(name="isScheduledOnWed", type="boolean", nullable=true)
    */
    protected $isScheduledOnWed;
    
    /**
    * @var boolean $isScheduledOnThu
    *
    * @ORM\Column(name="isScheduledOnThu", type="boolean", nullable=true)
    */
    protected $isScheduledOnThu;
    
    /**
    * @var boolean $isScheduledOnFri
    *
    * @ORM\Column(name="isScheduledOnFri", type="boolean", nullable=true)
    */
    protected $isScheduledOnFri;
    
    /**
    * @var boolean $isScheduledOnSat
    *
    * @ORM\Column(name="isScheduledOnSat", type="boolean", nullable=true)
    */
    protected $isScheduledOnSat;
    
    /**
    * @var boolean $isScheduledOnSun
    *
    * @ORM\Column(name="isScheduledOnSun", type="boolean", nullable=true)
    */
    protected $isScheduledOnSun;

    
    public function __construct()
    {
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
     * Set mailTime
     *
     * @return time $mailTime
     */
    public function setMailTime($mailTime)
    {
        $this->mailTime = $mailTime;
    }

    /**
     * Get mailTime
     *
     * @return time 
     */
    public function getMailTime()
    {
        return $this->mailTime;
    }

    /**
     * Get isScheduledOnMon
     *
     * @return boolean 
     */
    public function getIsScheduledOnMon()
    {
        return $this->isScheduledOnMon;
    }

    /**
     * Set isScheduledOnMon
     *
     * @param boolean $isScheduledOnMon
     */
    public function setIsScheduledOnMon($isScheduledOnMon)
    {
        $this->isScheduledOnMon = $isScheduledOnMon;
    }

    /**
     * Get isScheduledOnTue
     *
     * @return boolean 
     */
    public function getIsScheduledOnTue()
    {
        return $this->isScheduledOnTue;
    }
    
    /**
     * Set isScheduledOnTue
     *
     * @param boolean $isScheduledOnTue
     */
    public function setIsScheduledOnTue($isScheduledOnTue)
    {
        $this->isScheduledOnTue = $isScheduledOnTue;
    }
    
    /**
     * Get isScheduledOnWed
     *
     * @return boolean 
     */
    public function getIsScheduledOnWed()
    {
        return $this->isScheduledOnWed;
    }
    /**
     * Set isScheduledOnWed
     *
     * @param boolean $isScheduledOnWed
     */
    public function setIsScheduledOnWed($isScheduledOnWed)
    {
        $this->isScheduledOnWed = $isScheduledOnWed;
    }

    /**
     * Get isScheduledOnThu
     *
     * @return boolean 
     */
    public function getIsScheduledOnThu()
    {
        return $this->isScheduledOnThu;
    }
    
    /**
     * Set isScheduledOnThu
     *
     * @param boolean $isScheduledOnThu
     */
    public function setIsScheduledOnThu($isScheduledOnThu)
    {
        $this->isScheduledOnThu = $isScheduledOnThu;
    }

    /**
     * Get isScheduledOnFri
     *
     * @return boolean 
     */
    public function getIsScheduledOnFri()
    {
        return $this->isScheduledOnFri;
    }
    
    /**
     * Set isScheduledOnFri
     *
     * @param boolean $isScheduledOnFri
     */
    public function setIsScheduledOnFri($isScheduledOnFri)
    {
        $this->isScheduledOnFri = $isScheduledOnFri;
    }

    /**
     * Get isScheduledOnSat
     *
     * @return boolean 
     */
    public function getIsScheduledOnSat()
    {
        return $this->isScheduledOnSat;
    }
    
    /**
     * Set isScheduledOnSat
     *
     * @param boolean $isScheduledOnSat
     */
    public function setIsScheduledOnSat($isScheduledOnSat)
    {
        $this->isScheduledOnSat = $isScheduledOnSat;
    }

    /**
     * Get isScheduledOnSun
     *
     * @return boolean 
     */
    public function getIsScheduledOnSun()
    {
        return $this->isScheduledOnSun;
    }
    
    /**
     * Set isScheduledOnSun
     *
     * @param boolean $isScheduledOnSun
     */
    public function setIsScheduledOnSun($isScheduledOnSun)
    {
        $this->isScheduledOnSun = $isScheduledOnSun;
    }
}