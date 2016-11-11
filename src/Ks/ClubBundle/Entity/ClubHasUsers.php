<?php

namespace Ks\ClubBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\ClubBundle\Entity\ClubHasUsers
 *
 * @ORM\Table(name="ks_club_has_users")
 * @ORM\Entity(repositoryClass="Ks\ClubBundle\Entity\ClubHasUsersRepository")
 */
class ClubHasUsers
{
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Club", inversedBy="users")
     */
    private $club;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="clubs")
     */
    private $user;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime()
     * @var datetime
     */
    private $memberSince;
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @Assert\DateTime()
     * @var datetime
     */
    private $hasJoinedClubSince;
    
    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" = 0})
     * @var boolean
     */
    private $membershipAskInProgress;
    
    /**
    * @var boolean $isEnabled
    *
    * @ORM\Column(name="isEnabled", type="boolean")
    */
    private $isEnabled = true;
    
    
    public function __construct(\Ks\ClubBundle\Entity\Club $club, \Ks\UserBundle\Entity\User $user = null)
    {
        $this->club = $club;
        $this->user = $user;
        $this->hasJoinedClubSince = new \DateTime();
        $this->membershipAskInProgress = false;
    }
 

    /**
     * Set memberSince
     *
     * @param datetime $memberSince
     */
    public function setMemberSince($memberSince)
    {
        $this->memberSince = $memberSince;
    }

    /**
     * Get memberSince
     *
     * @return datetime 
     */
    public function getMemberSince()
    {
        return $this->memberSince;
    }

    /**
     * Set hasJoinedClubSince
     *
     * @param datetime $hasJoinedClubSince
     */
    public function setHasJoinedClubSince($hasJoinedClubSince)
    {
        $this->hasJoinedClubSince = $hasJoinedClubSince;
    }

    /**
     * Get hasJoinedClubSince
     *
     * @return datetime 
     */
    public function getHasJoinedClubSince()
    {
        return $this->hasJoinedClubSince;
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
     * Set membershipAskInProgress
     *
     * @param boolean $membershipAskInProgress
     */
    public function setMembershipAskInProgress($membershipAskInProgress)
    {
        $this->membershipAskInProgress = $membershipAskInProgress;
    }

    /**
     * Get membershipAskInProgress
     *
     * @return boolean 
     */
    public function getMembershipAskInProgress()
    {
        return $this->membershipAskInProgress;
    }
    
    /**
     * Set isEnabled
     *
     * @param boolean $isEnabled
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    /**
     * Get isEnabled
     *
     * @return boolean 
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }
}