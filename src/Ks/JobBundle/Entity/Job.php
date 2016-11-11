<?php

namespace Ks\JobBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\JobBundle\Entity\Job
 *
 * @ORM\Table(name="ks_job",uniqueConstraints={@ORM\UniqueConstraint(name="job_idx", columns={"service", "callback", "params"})})
 * @ORM\Entity(repositoryClass="Ks\JobBundle\Entity\JobRepository")
 */
class Job
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
     * @var string $service
     *
     * @ORM\Column(name="service", type="string", length=255)
     */
    private $service;
    
    /**
     * @var string $callback
     *
     * @ORM\Column(name="callback", type="string", length=255)
     */
    private $callback;

    /**
     * @var string $content
     *
     * @ORM\Column(name="params", type="string", length=255)
     */
    private $params;

    /**
     * @var datetime $creationDate
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var datetime $lastModificationDate
     *
     * @ORM\Column(name="lastModificationDate", type="datetime")
     */
    private $lastModificationDate;

    public function __construct()
    {
        // Par défaut, la date de l'event est la date d'aujourd'hui
        $this->creationDate = new \Datetime(); 
        $this->lastModificationDate = new \Datetime();
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
     * Set service
     *
     * @param text $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * Get service
     *
     * @return text 
     */
    public function getService()
    {
        return $this->service;
    }
    
    /**
     * Set callback
     *
     * @param text $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * Get callback
     *
     * @return text 
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Set params
     *
     * @param text $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Get content
     *
     * @return text 
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set creationDate
     *
     * @param datetime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * Get creationDate
     *
     * @return datetime 
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set lastModificationDate
     *
     * @param datetime $lastModificationDate
     */
    public function setLastModificationDate($lastModificationDate)
    {
        $this->lastModificationDate = $lastModificationDate;
    }

    /**
     * Get lastModificationDate
     *
     * @return datetime 
     */
    public function getLastModificationDate()
    {
        return $this->lastModificationDate;
    }
}