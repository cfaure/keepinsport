<?php

namespace Ks\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\EventBundle\Entity\TypeEvent
 *
 * @ORM\Table(name="ks_type_event")
 * @ORM\Entity
 */
class TypeEvent
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
     * @var string $nom_type
     *
     * @ORM\Column(name="nom_type", type="string", length=100)
     */
    private $nom_type;
    
    /**
     * @var string $color
     *
     * @ORM\Column(name="color", type="string", length=10, nullable="true")
     */
    private $color;
    

    /**
     * @ORM\OneToMany(targetEntity="\Ks\EventBundle\Entity\Event", mappedBy="typeEvent", cascade={"remove", "persist"})
     */
    private $events;
    
    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection;
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
     * Set nom_type
     *
     * @param string $nomType
     */
    public function setNomType($nomType)
    {
        $this->nom_type = $nomType;
    }

    /**
     * Get nom_type
     *
     * @return string 
     */
    public function getNomType()
    {
        return $this->nom_type;
    }
    
     /**
     * Set event
     *
     * @param Ks\EventBundle\Entity\Event $event
     */
    public function setEvent(\Ks\EventBundle\Entity\Event $event)
    {
        $this->events[] = $events;
    }

    /**
     * Get events
     *
     * @return Ks\EventBundle\Entity\Event 
     */
    public function getEvents()
    {
        return $this->events;
    }
    
    public function __toString(){
        return $this->nom_type;
    }
    
    /**
     * Set color
     *
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * Get color
     *
     * @return string 
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Add events
     *
     * @param Ks\EventBundle\Entity\Event $events
     */
    public function addEvent(\Ks\EventBundle\Entity\Event $events)
    {
        $this->events[] = $events;
    }
}