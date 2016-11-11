<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ActivityBundle\Entity\UserReadsImportantStatus
 *
 * @ORM\Table(name="ks_user_reads_important_status")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\UserReadsImportantStatusRepository")
 */
class UserReadsImportantStatus
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="importantStatus")
     * @ORM\JoinColumn(onDelete="cascade", onUpdate="cascade")
     */
    private $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\ActivityStatus", inversedBy="importantStatus")
     * @ORM\JoinColumn(onDelete="cascade", onUpdate="cascade")
     */
    private $activity;
    
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * 
     * @var boolean
     */
    private $isRead;
    
    public function __construct(\Ks\ActivityBundle\Entity\Activity $activity, \Ks\UserBundle\Entity\User $user)
    {
        $this->activity     = $activity;
        $this->user         = $user;
        $this->isRead       = false;
    }

    

    /**
     * Set isRead
     *
     * @param boolean $isRead
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;
    }

    /**
     * Get isRead
     *
     * @return boolean 
     */
    public function getIsRead()
    {
        return $this->isRead;
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
     * Set activity
     *
     * @param Ks\ActivityBundle\Entity\ActivityStatus $activity
     */
    public function setActivity(\Ks\ActivityBundle\Entity\ActivityStatus $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Get activity
     *
     * @return Ks\ActivityBundle\Entity\ActivityStatus 
     */
    public function getActivity()
    {
        return $this->activity;
    }
}