<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\ActivityBundle\Entity\SportsmenSearch
 *
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\SportsmenSearchRepository")
 */
class SportsmenSearch extends Activity
{

    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Sport", inversedBy="sportSportsmenSearches")
     * 
     * @var Ks\ActivityBundle\Entity\Sport
     */
    protected $sport;
    
    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     * @var datetime
     */
    protected $scheduledAt;
    
    /**
     * @ORM\OneToOne(targetEntity="Ks\EventBundle\Entity\Place", cascade={"persist"})
     * @Assert\Type(type="Ks\EventBundle\Entity\Place")
     */
    protected $programmedPlace;
    
    /**
     *
     * @param \Ks\UserBundle\Entity\User $user
     */
    public function __construct(\Ks\UserBundle\Entity\User $user = null)
    {
        parent::__construct($user);
        $this->type = "sportsmen_search";
    }
  
    /**
     * Set scheduledAt
     *
     * @param datetime $scheduledAt
     */
    public function setScheduledAt($scheduledAt)
    {
        $this->scheduledAt = $scheduledAt;
    }

    /**
     * Get scheduledAt
     *
     * @return datetime 
     */
    public function getScheduledAt()
    {
        return $this->scheduledAt;
    }

    /**
     * Set sport
     *
     * @param Ks\ActivityBundle\Entity\Sport $sport
     */
    public function setSport(\Ks\ActivityBundle\Entity\Sport $sport)
    {
        $this->sport = $sport;
    }

    /**
     * Get sport
     *
     * @return Ks\ActivityBundle\Entity\Sport 
     */
    public function getSport()
    {
        return $this->sport;
    }
    
    /**
     * Set place
     *
     * @param Ks\EventBundle\Entity\Place $place
     * 
     */
    public function setProgrammedPlace(\Ks\EventBundle\Entity\Place $place = null)
    {
        $this->programmedPlace = $place;
    }

    /**
     * Get place
     *
     * @return Ks\EventBundle\Entity\Place 
     */
    public function getProgrammedPlace()
    {
        return $this->programmedPlace;
    }
}