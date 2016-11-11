<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\UserBundle\Entity\UserHasServices
 *
 * @ORM\Table(name="ks_user_has_services")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\UserHasServicesRepository")
 */
class UserHasServices
{
        
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="services")
     */
    private $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\Service", inversedBy="users")
     */
    private $service;

    /**
     * @var boolean $is_active
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $is_active;
    
    /**
     * @var boolean $sync_service_to_user
     *
     * @ORM\Column(name="sync_service_to_user", type="boolean")
     */
    private $sync_service_to_user;
    
     /**
     * @var boolean $user_sync_to_service
     *
     * @ORM\Column(name="user_sync_to_service", type="boolean")
     */
    private $user_sync_to_service;
    
    /**
     * @var boolean $first_sync
     *
     * @ORM\Column(name="first_sync", type="boolean")
     */
    private $first_sync;
    
    /**
     * @ORM\Column(type="datetime",nullable="true")
     * @Assert\DateTime()
     * @var datetime
     */
    protected $lastSyncAt;
    
    /**
     * @var decimal $town
     *
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $connectionId;
    
    /**
     * @var decimal $town
     *
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $connectionPassword;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * @var text
     */
    private $collectedActivities;

    /**
     * @var string $token
     *
     * @ORM\Column(name="token", type="string", length=64,nullable="true")
     */
    private $token;
    
    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=45,nullable="true")
     */
    private $status;

    
    public function __construct()
    {
    }

    /**
     * Set service
     *
     * @param integer $service
     */
    public function setService(\Ks\UserBundle\Entity\Service $service)
    {
        $this->service = $service;
    }

    /**
     * Get service
     *
     * @return integer 
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set is_active
     *
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->is_active = $isActive;
    }

    /**
     * Get is_active
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set token
     *
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }
    


    /**
     * Set sync_service_to_user
     *
     * @param boolean $syncServiceToUser
     */
    public function setSyncServiceToUser($syncServiceToUser)
    {
        $this->sync_service_to_user = $syncServiceToUser;
    }

    /**
     * Get sync_service_to_user
     *
     * @return boolean 
     */
    public function getSyncServiceToUser()
    {
        return $this->sync_service_to_user;
    }

    /**
     * Set user_sync_to_service
     *
     * @param boolean $userSyncToService
     */
    public function setUserSyncToService($userSyncToService)
    {
        $this->user_sync_to_service = $userSyncToService;
    }

    /**
     * Get user_sync_to_service
     *
     * @return boolean 
     */
    public function getUserSyncToService()
    {
        return $this->user_sync_to_service;
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
     * Set first_sync
     *
     * @param boolean $firstSync
     */
    public function setFirstSync($firstSync)
    {
        $this->first_sync = $firstSync;
    }

    /**
     * Get first_sync
     *
     * @return boolean 
     */
    public function getFirstSync()
    {
        return $this->first_sync;
    }

    /**
     * Set lastSyncAt
     *
     * @param datetime $lastSyncAt
     */
    public function setLastSyncAt($lastSyncAt)
    {
        $this->lastSyncAt = $lastSyncAt;
    }

    /**
     * Get lastSyncAt
     *
     * @return datetime 
     */
    public function getLastSyncAt()
    {
        return $this->lastSyncAt;
    }

    /**
     * Set connectionId
     *
     * @param string $connectionId
     */
    public function setConnectionId($connectionId)
    {
        $this->connectionId = $connectionId;
    }

    /**
     * Get connectionId
     *
     * @return string 
     */
    public function getConnectionId()
    {
        return $this->connectionId;
    }

    /**
     * Set connectionPassword
     *
     * @param string $connectionPassword
     */
    public function setConnectionPassword($connectionPassword)
    {
        $this->connectionPassword = $connectionPassword;
    }

    /**
     * Get connectionPassword
     *
     * @return string 
     */
    public function getConnectionPassword()
    {
        return $this->connectionPassword;
    }

    /**
     * Set collectedActivities
     *
     * @param text $collectedActivities
     */
    public function setCollectedActivities($collectedActivities)
    {
        $this->collectedActivities = $collectedActivities;
    }

    /**
     * Get collectedActivities
     *
     * @return text 
     */
    public function getCollectedActivities()
    {
        return $this->collectedActivities;
    }

    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }
}