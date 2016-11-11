<?php

namespace Ks\AgendaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\AgendaBundle\Entity\AgendaHasEvents
 *
 * @ORM\Table(name="ks_agenda_has_events")
 * @ORM\Entity(repositoryClass="Ks\AgendaBundle\Entity\AgendaHasEventsRepository")
 */
class AgendaHasEvents
{
     /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\AgendaBundle\Entity\Agenda", inversedBy="events")
     */
    private $agenda;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\EventBundle\Entity\Event", inversedBy="agendas")
     * @ORM\JoinColumn(onDelete="cascade")
     */
    private $event;
    
    public function __construct(\Ks\AgendaBundle\Entity\Agenda $agenda, \Ks\EventBundle\Entity\Event $event)
    {
        $this->agenda  = $agenda;
        $this->event   = $event;
    }

    /**
     * Set agenda
     *
     * @param Ks\AgendaBundle\Entity\Agenda $agenda
     */
    public function setAgenda(\Ks\AgendaBundle\Entity\Agenda $agenda)
    {
        $this->agenda = $agenda;
    }

    /**
     * Get agenda
     *
     * @return Ks\AgendaBundle\Entity\Agenda 
     */
    public function getAgenda()
    {
        return $this->agenda;
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
}