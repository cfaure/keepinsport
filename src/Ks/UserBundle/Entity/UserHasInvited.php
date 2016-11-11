<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\UserBundle\Entity\UserHasInvited
 *
 * @ORM\Table(name="ks_user_has_invited")
 * @ORM\Entity
 */
class UserHasInvited
{
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="myInvited")
     */
    private $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="InvitedByMe")
     */
    private $invited;
    
    public function __construct(\Ks\UserBundle\Entity\User $user, \Ks\UserBundle\Entity\User $invited, $saltInvited)
    {
        $this->user                     = $user;
        $this->invited                  = $invited;
        $this->saltInvited              = $saltInvited;
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
     * Set invited
     *
     * @param Ks\UserBundle\Entity\User $invited
     */
    public function setInvited(\Ks\UserBundle\Entity\User $invited)
    {
        $this->invited = $invited;
    }

    /**
     * Get invited
     *
     * @return Ks\UserBundle\Entity\Invited 
     */
    public function getInvited()
    {
        return $this->invited;
    }

    
}