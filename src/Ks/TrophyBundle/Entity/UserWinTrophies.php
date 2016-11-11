<?php

namespace Ks\TrophyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\TrophyBundle\Entity\UserWinTrophies
 *
 * @ORM\Table(name="ks_user_win_trophies")
 * @ORM\Entity(repositoryClass="Ks\TrophyBundle\Entity\UserWinTrophiesRepository")
 */
class UserWinTrophies
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\TrophyBundle\Entity\Trophy", inversedBy="usersWhoHaveWon")
     */
    private $trophy;
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="trophies")
     */
    private $user;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * 
     * @var datetime
     */
    private $unlockedAt;
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     * 
     * @var datetime
     */
    private $modifiedAt;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     * 
     * @var integer
     */
    private $timesSinceBegin;
            

    public function __construct(\Ks\TrophyBundle\Entity\Trophy $trophy, \Ks\UserBundle\Entity\User $user)
    {
        $this->trophy           = $trophy;
        $this->user             = $user;
        $this->timesSinceBegin  = 1;
        $this->modifiedAt       = new \DateTime();
    }

    /**
     * Set unlockedAt
     *
     * @param datetime $unlockedAt
     */
    public function setUnlockedAt($unlockedAt)
    {
        $this->unlockedAt = $unlockedAt;
    }

    /**
     * Get unlockedAt
     *
     * @return datetime 
     */
    public function getUnlockedAt()
    {
        return $this->unlockedAt;
    }

    /**
     * Set trophy
     *
     * @param Ks\TrophyBundle\Entity\Trophy $trophy
     */
    public function setTrophy(\Ks\TrophyBundle\Entity\Trophy $trophy)
    {
        $this->trophy = $trophy;
    }

    /**
     * Get trophy
     *
     * @return Ks\TrophyBundle\Entity\Trophy 
     */
    public function getTrophy()
    {
        return $this->trophy;
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
     * Set timesSinceBegin
     *
     * @param integer $timesSinceBegin
     */
    public function setTimesSinceBegin($timesSinceBegin)
    {
        $this->timesSinceBegin = $timesSinceBegin;
    }

    /**
     * Get timesSinceBegin
     *
     * @return integer 
     */
    public function getTimesSinceBegin()
    {
        return $this->timesSinceBegin;
    }

    /**
     * Set modifiedAt
     *
     * @param datetime $modifiedAt
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
    }

    /**
     * Get modifiedAt
     *
     * @return datetime 
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }
}