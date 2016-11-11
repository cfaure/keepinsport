<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of External Source
 *
 * @ORM\Entity
 * @ORM\Table(name="ks_external_source")
 */
class ExternalSource
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @var int
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=64)
     * 
     * @var string
     */
    protected $label;
    
    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @var string
     */
    protected $siteUrl;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\ActivitySession", mappedBy="externalSource"))
     * 
     * @var DoctrineCollection
     */
    protected $externalSourceActivities;
    
    public function __construct()
    {
        $this->externalSourceActivities = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return string 
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
     * Set siteUrl
     *
     * @param string $siteUrl
     */
    public function setSiteUrl($siteUrl)
    {
        $this->siteUrl = $siteUrl;
    }

    /**
     * Get siteUrl
     *
     * @return string 
     */
    public function getSiteUrl()
    {
        return $this->siteUrl;
    }

    /**
     * Add externalSourceActivities
     *
     * @param Ks\ActivityBundle\Entity\Activity $externalSourceActivities
     */
    public function addActivity(\Ks\ActivityBundle\Entity\Activity $externalSourceActivities)
    {
        $this->externalSourceActivities[] = $externalSourceActivities;
    }

    /**
     * Get externalSourceActivities
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getExternalSourceActivities()
    {
        return $this->externalSourceActivities;
    }

    /**
     * Add externalSourceActivities
     *
     * @param Ks\ActivityBundle\Entity\ActivitySession $externalSourceActivities
     */
    public function addActivitySession(\Ks\ActivityBundle\Entity\ActivitySession $externalSourceActivities)
    {
        $this->externalSourceActivities[] = $externalSourceActivities;
    }
}