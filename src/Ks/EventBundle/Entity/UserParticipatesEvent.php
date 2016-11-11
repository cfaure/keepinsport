<?php

namespace Ks\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\EventBundle\Entity\UserParticipatesEvent
 *
 * @ORM\Table(name="ks_user_participates_event")
 * @ORM\Entity(repositoryClass="Ks\EventBundle\Entity\UserParticipatesEventRepository")
 */
class UserParticipatesEvent
{

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\EventBundle\Entity\Event", inversedBy="users")
    */
    private $event;

     /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="eventsParticipations")
     */
    private $user;
    
    public function __construct(\Ks\EventBundle\Entity\Event $event, \Ks\UserBundle\Entity\User $user)
    {
        $this->event = $event;
        $this->user = $user;
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
}