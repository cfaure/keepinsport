<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of ActivitySessionEnduranceOnEarth
 *
 * @ORM\Entity
 * @ORM\Table(name="ks_activity_session_endurance_on_earth")
 */
class ActivitySessionEnduranceOnEarth extends ActivitySessionEndurance
{      
    /**
     * @ORM\Column(name="elevationMin", type="decimal", nullable=true)
     * @var decimal
     */
    protected $elevationMin;
    
    /**
     * @ORM\Column(name="elevationMax", type="decimal", nullable=true)
     * @var decimal
     */
    protected $elevationMax;
    
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
    
    public function __construct(\Ks\UserBundle\Entity\User $user = null)
    {
        parent::__construct($user);
        $this->type = "session_endurance_on_earth";
    }
    
    /**
     * Set elevationMin
     *
     * @param float $elevationMin
     */
    public function setElevationMin($elevationMin)
    {
        $this->elevationMin = $elevationMin;
    }

    /**
     * Get elevationMin
     *
     * @return float 
     */
    public function getElevationMin()
    {
        return $this->elevationMin;
    }

    /**
     * Set elevationMax
     *
     * @param float $elevationMax
     */
    public function setElevationMax($elevationMax)
    {
        $this->elevationMax = $elevationMax;
    }

    /**
     * Get elevationMax
     *
     * @return float 
     */
    public function getElevationMax()
    {
        return $this->elevationMax;
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
 
}