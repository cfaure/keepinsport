<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of ActivitySessionEnduranceUnderWater
 *
 * @ORM\Entity
 * @ORM\Table(name="ks_activity_session_endurance_under_water")
 */
class ActivitySessionEnduranceUnderWater extends ActivitySessionEndurance
{      
    /**
     * @ORM\Column(name="depthGain", type="integer", nullable=true)
     * @Assert\Min(limit = "0", message = "Le gain de profondeur ne peut pas être inférieur à 0")
     * @var int
     */
    protected $depthGain;
    
    /**
     * @ORM\Column(name="depthMax", type="integer", nullable=true)
     * @Assert\Min(limit = "0", message = "La profondeur maximale ne peut pas être inférieur à 0")
     * @var int
     */
    protected $depthMax;

    /*public function __construct(\Ks\ActivityBundle\Entity\ActivitySession $activitySession)
    {
        parent::__construct($activitySession);
    }*/
    
    public function __construct(\Ks\UserBundle\Entity\User $user = null)
    {
        parent::__construct($user);
        $this->type = "session_endurance_under_water";
    }
    
    /**
     * Set depthGain
     *
     * @param int $depthGain
     */
    public function setDepthGain($depthGain)
    {
        $this->depthGain = $depthGain;
    }

    /**
     * Get depthGain
     *
     * @return int 
     */
    public function getDepthGain()
    {
        return $this->depthGain;
    }

    /**
     * Set depthMax
     *
     * @param int $depthMax
     */
    public function setDepthMax($depthMax)
    {
        $this->depthMax = $depthMax;
    }

    /**
     * Get depthMax
     *
     * @return int 
     */
    public function getDepthMax()
    {
        return $this->depthMax;
    }

}