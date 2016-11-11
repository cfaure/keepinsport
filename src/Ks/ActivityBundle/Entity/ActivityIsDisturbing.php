<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\ActivityBundle\Entity\ActivityIsDisturbing
 *
 * @ORM\Table(name="ks_activity_is_disturbing")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\ActivityIsDisturbingRepository")
 */
class ActivityIsDisturbing
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Activity", inversedBy="usersWhoWarnedLikeDisturbing")
     * @ORM\JoinColumn(onDelete="cascade", onUpdate="cascade")
     */
    private $activity;
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="reportedDisturbingActivities")
     * @ORM\JoinColumn(onDelete="cascade", onUpdate="cascade")
     */
    private $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\MaxLength(255)
     * @var text
     */
    private $reason;

    /**
     * Set reason
     *
     * @param text $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * Get reason
     *
     * @return text 
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set activity
     *
     * @param Ks\ActivityBundle\Entity\Activity $activity
     */
    public function setActivity(\Ks\ActivityBundle\Entity\Activity $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Get activity
     *
     * @return Ks\ActivityBundle\Entity\Activity 
     */
    public function getActivity()
    {
        return $this->activity;
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