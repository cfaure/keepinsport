<?php

namespace Ks\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\EventBundle\Entity\GoogleEvent
 *
 * @ORM\Table(name="ks_google_event")
 * @ORM\Entity
 */
class GoogleEvent
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $id_url_event
     *
     * @ORM\Column(name="id_url_event", type="string", length=255)
     */
    private $id_url_event;


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
     * Set id_url_event
     *
     * @param string $idUrlEvent
     */
    public function setIdUrlEvent($idUrlEvent)
    {
        $this->id_url_event = $idUrlEvent;
    }

    /**
     * Get id_url_event
     *
     * @return string 
     */
    public function getIdUrlEvent()
    {
        return $this->id_url_event;
    }
}