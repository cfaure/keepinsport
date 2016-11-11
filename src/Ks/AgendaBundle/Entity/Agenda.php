<?php

namespace Ks\AgendaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\AgendaBundle\Entity\Agenda
 *
 * @ORM\Table(name="ks_agenda")
 * @ORM\Entity(repositoryClass="Ks\AgendaBundle\Entity\AgendaRepository")
 */
class Agenda
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
     * @ORM\Column(name="name", type="string", length=45, nullable="true")
     */
    private $name;

    /**
     * @var date $createdAt
     *
     * @ORM\Column(name="createdAt", type="date")
     */
    private $createdAt;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\AgendaBundle\Entity\AgendaHasEvents", mappedBy="agenda", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
    protected $events;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
        $this->name = "";
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
     * Set createdAt
     *
     * @param date $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return date 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    
    
    /**
     * Add events
     *
     * @param Ks\AgendaBundle\Entity\AgendaHasEvents $events
     */
    public function addAgendaHasEvents(\Ks\AgendaBundle\Entity\AgendaHasEvents $events)
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