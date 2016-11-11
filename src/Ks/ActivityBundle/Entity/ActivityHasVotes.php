<?php

namespace Ks\UserBundle\Entity;
namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ActivityBundle\Entity\ActivityHasVotes
 *
 * @ORM\Table(name="ks_activity_has_votes")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\ActivityHasVotesRepository")
 */
class ActivityHasVotes
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Activity", inversedBy="voters")
     * @ORM\JoinColumn(onDelete="cascade", onUpdate="cascade")
     */
    private $activity;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="myVotes")
     */
    private $voter;
    
    public function __construct(\Ks\ActivityBundle\Entity\Activity $activity, \Ks\UserBundle\Entity\User $voter)
    {
        $this->activity     = $activity;
        $this->voter        = $voter;
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
     * Set voter
     *
     * @param Ks\UserBundle\Entity\User $voter
     */
    public function setVoter(\Ks\UserBundle\Entity\User $voter)
    {
        $this->voter = $voter;
    }

    /**
     * Get voter
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getVoter()
    {
        return $this->voter;
    }
}