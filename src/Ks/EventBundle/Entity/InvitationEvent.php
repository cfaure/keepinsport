<?php

namespace Ks\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\EventBundle\Entity\InvitationEvent
 *
 * @ORM\Table(name="ks_invitation_event")
 * @ORM\Entity
 */
class InvitationEvent
{
     /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="userInvitingEvent")
     */
    private $userInviting;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="userInvitedEvent")
     */
    private $userInvited;
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\EventBundle\Entity\Event", inversedBy="invitationEvent")
     */
    private $event;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\EventBundle\Entity\Status")
    */
    private $status;
    
    
    

    /**
     * Set userInviting
     *
     * @param Ks\UserBundle\Entity\User $userInviting
     */
    public function setUserInviting(\Ks\UserBundle\Entity\User $userInviting)
    {
        $this->userInviting = $userInviting;
    }

    /**
     * Get userInviting
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getUserInviting()
    {
        return $this->userInviting;
    }

    /**
     * Set userInvited
     *
     * @param Ks\UserBundle\Entity\User $userInvited
     */
    public function setUserInvited(\Ks\UserBundle\Entity\User $userInvited)
    {
        $this->userInvited = $userInvited;
    }

    /**
     * Get userInvited
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getUserInvited()
    {
        return $this->userInvited;
    }

    /**
     * Set event
     *
     * @param Ks\EventBundle\Entity\Event $event
     */
    public function setEvent(\Ks\EventBundle\Entity\Event $event)
    {
        $this->event = $event;
    }

    /**
     * Get event
     *
     * @return Ks\EventBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set status
     *
     * @param Ks\EventBundle\Entity\Status $status
     */
    public function setStatus(\Ks\EventBundle\Entity\Status $status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return Ks\EventBundle\Entity\Status 
     */
    public function getStatus()
    {
        return $this->status;
    }
}