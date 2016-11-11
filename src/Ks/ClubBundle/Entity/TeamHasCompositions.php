<?php

namespace Ks\ClubBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\ClubBundle\Entity\TeamHasCompositions
 *
 * @ORM\Table(name="ks_team_has_compositions")
 * @ORM\Entity
 */
class TeamHasCompositions
{    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Team", inversedBy="teamCompositions")
     */
    private $team;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="teams")
     */
    private $teamComposition;   
    
    public function __construct(\Ks\ClubBundle\Entity\Team $team)
    {
        $this->team = $team;
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
     * Set teamComposition
     *
     * @param Ks\UserBundle\Entity\User $teamComposition
     */
    public function setTeamComposition(\Ks\UserBundle\Entity\User $teamComposition)
    {
        $this->teamComposition = $teamComposition;
    }

    /**
     * Get teamComposition
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getTeamComposition()
    {
        return $this->teamComposition;
    }
}