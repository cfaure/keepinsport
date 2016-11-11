<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ActivityBundle\Entity\ActivityToSync
 *
 * @ORM\Table(name="ks_activity_to_sync")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\ActivityToSyncRepository")
 */
class ActivityToSync
{
     /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\Service", inversedBy="activities")
     */
    private $service;

    /**
     * @var string $id_website_activity_service
     * @ORM\Id
     * @ORM\Column(name="id_website_activity_service", type="string", length=255)
     */
    private $id_website_activity_service;
    
     /**
     * @var string $source_details_activity
     *
     * @ORM\Column(name="source_details_activity", type="text", nullable="true")
     */
    private $source_details_activity;
    
    /**
     * @var string $type_source
     *
     * @ORM\Column(name="type_source", type="string", nullable="true", length=10)
     */
    private $type_source;
    
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
     * Set id_website_activity_service
     *
     * @param string $idWebsiteActivityService
     */
    public function setIdWebsiteActivityService($idWebsiteActivityService)
    {
        $this->id_website_activity_service = $idWebsiteActivityService;
    }

    /**
     * Get id_website_activity_service
     *
     * @return string 
     */
    public function getIdWebsiteActivityService()
    {
        return $this->id_website_activity_service;
    }

    /**
     * Set user
     *
     * @param Ks\UserBundle\Entity\User $user
     */
    public function setUser(\Ks\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Ks\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set service
     *
     * @param Ks\UserBundle\Entity\Service $service
     */
    public function setService(\Ks\UserBundle\Entity\Service $service)
    {
        $this->service = $service;
    }

    /**
     * Get service
     *
     * @return Ks\UserBundle\Entity\Service 
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set source_details_activity
     *
     * @param text $sourceDetailsActivity
     */
    public function setSourceDetailsActivity($sourceDetailsActivity)
    {
        $this->source_details_activity = $sourceDetailsActivity;
    }

    /**
     * Get source_details_activity
     *
     * @return text 
     */
    public function getSourceDetailsActivity()
    {
        return $this->source_details_activity;
    }

    /**
     * Set type_source
     *
     * @param string $typeSource
     */
    public function setTypeSource($typeSource)
    {
        $this->type_source = $typeSource;
    }

    /**
     * Get type_source
     *
     * @return string 
     */
    public function getTypeSource()
    {
        return $this->type_source;
    }
}