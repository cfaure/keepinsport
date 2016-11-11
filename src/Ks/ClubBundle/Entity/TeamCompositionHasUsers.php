<?php

namespace Ks\ClubBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\ClubBundle\Entity\TeamCompositionHasUsers
 *
 * @ORM\Table(name="ks_team_composition_has_users")
 * @ORM\Entity
 */
class TeamCompositionHasUsers
{   
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\TeamComposition", inversedBy="users")
     */
    private $teamComposition;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="teamCompositions")
     */
    private $user;
    
    
    public function __construct(\Ks\ClubBundle\Entity\teamComposition $teamComposition, \Ks\UserBundle\Entity\User $user = null)
    {
        $this->teamComposition = $teamComposition;
        $this->user = $user;
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
     * Set teamComposition
     *
     * @param Ks\ClubBundle\Entity\TeamComposition $teamComposition
     */
    public function setTeamComposition(\Ks\ClubBundle\Entity\TeamComposition $teamComposition)
    {
        $this->teamComposition = $teamComposition;
    }

    /**
     * Get teamComposition
     *
     * @return Ks\ClubBundle\Entity\TeamComposition 
     */
    public function getTeamComposition()
    {
        return $this->teamComposition;
    }
}