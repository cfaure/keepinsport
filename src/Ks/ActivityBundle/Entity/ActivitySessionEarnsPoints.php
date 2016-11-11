<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ActivityBundle\Entity\ActivitySessionEarnsPoints
 *
 * @ORM\Table(name="ks_activity_earns_points")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\ActivitySessionEarnsPointsRepository")
 */
class ActivitySessionEarnsPoints
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Activity", inversedBy="points")
     * @ORM\JoinColumn(onDelete="cascade", onUpdate="cascade")
     */
    private $activitySession;
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="myPoints")
     */
    private $user;
    
    /**
     * @ORM\Column(type="integer")
     * 
     * @var integer
     */
    private $points;

    public function __construct(\Ks\ActivityBundle\Entity\Activity $activity, \Ks\UserBundle\Entity\User $user, $points)
    {
        $this->activitySession  = $activity;
        $this->user             = $user;
        $this->points           = $points;
    }
    
    /**
     * Set points
     *
     * @param integer $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
    }

    /**
     * Get points
     *
     * @return integer 
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set activitySession
     *
     * @param Ks\ActivityBundle\Entity\ActivitySession $activitySession
     */
    public function setActivitySession(\Ks\ActivityBundle\Entity\ActivitySession $activitySession)
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
}
