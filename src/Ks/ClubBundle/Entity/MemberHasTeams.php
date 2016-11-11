<?php

namespace Ks\ClubBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ClubBundle\Entity\MemberHasTeams
 *
 * @ORM\Table(name="ks_member_has_teams")
 * @ORM\Entity
 */
class MemberHasTeams
{
     /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Member", inversedBy="teams" )
     */
    private $member;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Team", inversedBy="members" )
     */
    private $team;
   


    /**
     * Set member
     *
     * @param Ks\ClubBundle\Entity\Member $member
     */
    public function setMember(\Ks\ClubBundle\Entity\Member $member)
    {
        $this->member = $member;
    }

    /**
     * Get member
     *
     * @return Ks\ClubBundle\Entity\Member 
     */
    public function getMember()
    {
        return $this->member;
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
}