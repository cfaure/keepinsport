<?php

namespace Ks\ClubBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\ClubBundle\Entity\TeamHasUsers
 *
 * @ORM\Table(name="ks_team_has_users")
 * @ORM\Entity
 */
class TeamHasUsers
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Team", inversedBy="users")
     */
    private $team;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="teams")
     */
    private $user;
    
    public function __construct(\Ks\ClubBundle\Entity\Team $team, \Ks\UserBundle\Entity\User $user = null)
    {
        $this->team = $team;
        $this->user = $user;
    }
 
    /**
     * Set team
     *
     * @param Ks\ClubBundle\Entity\Team $team
     */
    public function setTeam(\Ks\ClubBundle\Entity\Team $team)
    {
        $this->team = $team;
    }

    /**
     * Get team
     *
     * @return Ks\ClubBundle\Entity\Team 
     */
    public function getTeam()
    {
        return $this->team;
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