<?php

namespace Ks\TrophyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\TrophyBundle\Entity\Showcase
 *
 * @ORM\Table(name="ks_showcase")
 * @ORM\Entity(repositoryClass="Ks\TrophyBundle\Entity\ShowcaseRepository")
 */
class Showcase
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
     * @ORM\Column(type="string", length=128, nullable=true);
     * 
     * @var string
     */
    private $label;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\TrophyBundle\Entity\ShowcaseExposesTrophies", mappedBy="showcase", cascade={"remove", "persist"})
     */
    private $trophies;  
    
    public function __construct()
    {
        $this->label        = "Vitrine";
        $this->trophies     = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set label
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Add trophies
     *
     * @param Ks\TrophyBundle\Entity\ShowcaseExposesTrophies $trophies
     */
    public function addShowcaseExposesTrophies(\Ks\TrophyBundle\Entity\ShowcaseExposesTrophies $trophies)
    {
        $this->trophies[] = $trophies;
    }

    /**
     * Get trophies
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTrophies()
    {
        return $this->trophies;
    }
}