<?php

namespace Ks\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\EventBundle\Entity\Status
 *
 * @ORM\Table(name="ks_status")
 * @ORM\Entity
 */
class Status
{
        /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=45)
     */
    private $name;
    
     /**
     * @ORM\OneToMany(targetEntity="\Ks\EventBundle\Entity\InvitationEvent", mappedBy="status", cascade={"remove", "persist"})
     */
    private $events;

   
    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add events
     *
     * @param Ks\EventBundle\Entity\InvitationEvent $events
     */
    public function addInvitationEvent(\Ks\EventBundle\Entity\InvitationEvent $events)
    {
        $this->events[] = $events;
    }

    /**
     * Get events
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getEvents()
    {
        return $this->events;
    }
}