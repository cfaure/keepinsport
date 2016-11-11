<?php

namespace Ks\ClubBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ClubBundle\Entity\ClubHasMembers
 *
 * @ORM\Table(name="ks_club_has_members")
 * @ORM\Entity(repositoryClass="Ks\ClubBundle\Entity\ClubHasMembersRepository")
 */
class ClubHasMembers
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Member", inversedBy="clubs")
     */
    private $member;
    
     /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Club", inversedBy="members")
     */
    private $club;

    

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
     * Set club
     *
     * @param Ks\ClubBundle\Entity\Club $club
     */
    public function setClub(\Ks\ClubBundle\Entity\Club $club)
    {
        $this->club = $club;
    }

    /**
     * Get club
     *
     * @return Ks\ClubBundle\Entity\Club 
     */
    public function getClub()
    {
        return $this->club;
    }
}