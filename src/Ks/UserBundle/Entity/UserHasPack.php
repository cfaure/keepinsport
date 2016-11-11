<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\UserBundle\Entity\UserHasPack
 *
 * @ORM\Table(name="ks_user_has_pack")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\UserHasPackRepository")
 */
class UserHasPack
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\Pack", inversedBy="usersWhoHavePack")
     */
    private $pack;
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="packs")
     */
    private $user;
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     * 
     * @var datetime
     */
    private $startDate;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * 
     * @var datetime
     */
    private $endDate;
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     * 
     * @var datetime
     */
    private $modifiedAt;
    
    public function __construct(\Ks\UserBundle\Entity\Pack $pack, \Ks\UserBundle\Entity\User $user)
    {
        $this->pack            = $pack;
        $this->user            = $user;
        $this->startDate       = new \DateTime();
        $this->modifiedAt      = new \DateTime();
    }

    /**
     * Set startDate
     *
     * @param datetime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Get startDate
     *
     * @return datetime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }
    
    /**
     * Set endDate
     *
     * @param datetime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Get endDate
     *
     * @return datetime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set pack
     *
     * @param Ks\UserBundle\Entity\Pack $pack
     */
    public function setPack(\Ks\UserBundle\Entity\Pack $pack)
    {
        $this->pack = $pack;
    }

    /**
     * Get pack
     *
     * @return Ks\UserBundle\Entity\Pack 
     */
    public function getPack()
    {
        return $this->pack;
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