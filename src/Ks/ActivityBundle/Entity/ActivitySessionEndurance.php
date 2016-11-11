<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of ActivitySessionEndurence
 *
 * @ORM\Entity
 * @ORM\Table(name="ks_activity_session_endurance")
 */
abstract class ActivitySessionEndurance extends ActivitySession
{   
    /**
     * @ORM\Column(name="distance", type="decimal", precision="6", scale="2", nullable=true)
     * @Assert\Min(limit = "0", message = "La distance ne peut pas être inférieur à 0")
     * @var decimal
     */
    protected $distance;
    
    /**
     * @ORM\Column(name="speedMin", type="integer", nullable=true)
     * @Assert\Min(limit = "0", message = "La vitesse minimale ne peut pas être inférieur à 0")
     * @var integer
     */
    protected $speedMin;
    
    /**
     * @ORM\Column(name="speedMax", type="integer", nullable=true)
     * @Assert\Min(limit = "0", message = "La vitesse maximale ne peut pas être inférieur à 0")
     * @var integer
     */
    protected $speedMax;
    
    /**
     * @ORM\Column(name="speedAverage", type="decimal", nullable=true)
     * @Assert\Min(limit = "0", message = "La vitesse moyenne ne peut pas être inférieur à 0")
     * @var decimal
     */
    protected $speedAverage;

    public function __construct(\Ks\UserBundle\Entity\User $user = null)
    {        
        parent::__construct($user);
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
     * Set speedMin
     *
     * @param integer $speedMin
     */
    public function setSpeedMin($speedMin)
    {
        $this->speedMin = $speedMin;
    }

    /**
     * Get speedMin
     *
     * @return integer 
     */
    public function getSpeedMin()
    {
        return $this->speedMin;
    }

    /**
     * Set speedMax
     *
     * @param integer $speedMax
     */
    public function setSpeedMax($speedMax)
    {
        $this->speedMax = $speedMax;
    }

    /**
     * Get speedMax
     *
     * @return integer 
     */
    public function getSpeedMax()
    {
        return $this->speedMax;
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
     * Get speedAverage
     *
     * @return integer 
     */
    public function getSpeedAverage()
    {
        return $this->speedAverage;
    }
}