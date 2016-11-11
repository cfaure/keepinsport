<?php

namespace Ks\TrophyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\TrophyBundle\Entity\ShowcaseExposesTrophies
 *
 * @ORM\Table(name="ks_showcase_exposes_trophies")
 * @ORM\Entity(repositoryClass="Ks\TrophyBundle\Entity\ShowcaseExposesTrophiesRepository")
 */
class ShowcaseExposesTrophies
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\TrophyBundle\Entity\Showcase", inversedBy="trophies")
     */
    private $showcase;
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\TrophyBundle\Entity\Trophy", inversedBy="showcasesWhereAreExposedIt")
     */
    private $trophy;
    
    /**
     * @ORM\Column(type="datetime")
     * 
     * @var datetime
     */
    private $exposedSince;

    public function __construct( \Ks\TrophyBundle\Entity\Showcase $showcase, \Ks\TrophyBundle\Entity\Trophy $trophy)
    {
        $this->trophy       = $trophy;
        $this->showcase     = $showcase;
        $this->exposedSince = new \DateTime();
    }

    /**
     * Set exposedSince
     *
     * @param datetime $exposedSince
     */
    public function setExposedSince($exposedSince)
    {
        $this->exposedSince = $exposedSince;
    }

    /**
     * Get exposedSince
     *
     * @return datetime 
     */
    public function getExposedSince()
    {
        return $this->exposedSince;
    }

    /**
     * Set showcase
     *
     * @param Ks\TrophyBundle\Entity\Showcase $showcase
     */
    public function setShowcase(\Ks\TrophyBundle\Entity\Showcase $showcase)
    {
        $this->showcase = $showcase;
    }

    /**
     * Get showcase
     *
     * @return Ks\TrophyBundle\Entity\Showcase 
     */
    public function getShowcase()
    {
        return $this->showcase;
    }

    /**
     * Set trophy
     *
     * @param Ks\TrophyBundle\Entity\Trophy $trophy
     */
    public function setTrophy(\Ks\TrophyBundle\Entity\Trophy $trophy)
    {
        $this->trophy = $trophy;
    }

    /**
     * Get trophy
     *
     * @return Ks\TrophyBundle\Entity\Trophy 
     */
    public function getTrophy()
    {
        return $this->trophy;
    }
}