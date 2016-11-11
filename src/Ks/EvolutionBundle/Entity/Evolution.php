<?php

namespace Ks\EvolutionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\EvolutionBundle\Entity\Evolution
 *
 * @ORM\Table(name="ks_evolution")
 * @ORM\Entity(repositoryClass="Ks\EvolutionBundle\Entity\EvolutionRepository")
 */
class Evolution
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
     * @var string $descriptionShort
     *
     * @ORM\Column(name="descriptionShort", type="string", length=50, nullable=false)
     */
    private $descriptionShort;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * @var date $creationDate
     *
     * @ORM\Column(name="creationDate", type="date", nullable=false)
     */
    private $creationDate;
    
    /**
     * @var date $releaseDate
     *
     * @ORM\Column(name="releaseDate", type="date", nullable=true)
     */
    private $releaseDate;


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
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Set descriptionShort
     *
     * @param string $descriptionShort
     */
    public function setDescriptionShort($descriptionShort)
    {
        $this->descriptionShort = $descriptionShort;
    }

    /**
     * Get descriptionShort
     *
     * @return string 
     */
    public function getDescriptionShort()
    {
        return $this->descriptionShort;
    }

    /**
     * Set releaseDate
     *
     * @param date $releaseDate
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;
    }

    /**
     * Get releaseDate
     *
     * @return date 
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }
    
    /**
     * Set creationate
     *
     * @param date $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * Get creationDate
     *
     * @return date 
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\EvolutionBundle\Entity\EvolutionHasVotes", mappedBy="evolution")
     * 
     * @var ArrayCollection
     */
    protected $voters;
}