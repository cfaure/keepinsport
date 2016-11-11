<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\UserBundle\Entity\UserHasSportPackageFromClub
 *
 * @ORM\Table(name="ks_user_has_sport_package_from_club")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\UserHasSportPackageFromClubRepository")
 */
class UserHasSportPackageFromClub
{
        
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     */
    private $user;
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Sport")
     */
    private $sport;
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Club")
     */
    private $club;
    
    /**
    * @var integer $remainingSessions
    *
    * @ORM\Column(name="remainingSessions", type="integer", nullable=true)
    */
    private $remainingSessions;

    
    public function __construct()
    {
    }

    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        return $this->id = $id;
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
     * Set sport
     *
     * @param Ks\ActivityBundle\Entity\Sport $sport
     */
    public function setSport(\Ks\ActivityBundle\Entity\Sport $sport)
    {
        $this->sport = $sport;
    }

    /**
     * Get sport
     *
     * @return Ks\ActivityBundle\Entity\Sport 
     */
    public function getSport()
    {
        return $this->sport;
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
     * @return Ks\ClubBundle\Entity\Club $club
     */
    public function getClub()
    {
        return $this->club;
    }
    
    /**
     * Set remainingSessions
     *
     * @param 
     */
    public function setRemainingSessions($remainingSessions)
    {
        $this->remainingSessions = $remainingSessions;
    }

    /**
     * Get remainingSessions
     *
     * @return $remainingSessions
     */
    public function getRemainingSessions()
    {
        return $this->remainingSessions;
    }
}