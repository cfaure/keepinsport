<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\UserBundle\Entity\Service
 *
 * @ORM\Table(name="ks_service")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\ServiceRepository")
 */
class Service
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
     * @ORM\Column(name="name", type="string", length=45)
     */
    private $name;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\UserHasServices", mappedBy="service", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
    protected $users;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\ActivityComeFromService", mappedBy="service", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
    protected $activities;
    
    
    

    
     public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection;
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
     * Add users
     *
     * @param Ks\UserBundle\Entity\UserHasServices $users
     */
    public function setUsers(\Ks\UserBundle\Entity\UserHasServices $users)
    {
        $this->users[] = $users;
    }

    /**
     * Get users
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->services;
    }

    /**
     * Add users
     *
     * @param Ks\UserBundle\Entity\UserHasServices $users
     */
    public function addUserHasServices(\Ks\UserBundle\Entity\UserHasServices $users)
    {
        $this->users[] = $users;
    }

    /**
     * Add activities
     *
     * @param Ks\ActivityBundle\Entity\ActivityComeFromService $activities
     */
    public function addActivityComeFromService(\Ks\ActivityBundle\Entity\ActivityComeFromService $activities)
    {
        $this->activities[] = $activities;
    }

    /**
     * Get activities
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getActivities()
    {
        return $this->activities;
    }
}