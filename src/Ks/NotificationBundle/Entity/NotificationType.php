<?php

namespace Ks\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\NotificationBundle\Entity\NotificationType
 *
 * @ORM\Table(name="ks_notification_type")
 * @ORM\Entity(repositoryClass="Ks\NotificationBundle\Entity\NotificationTypeRepository")
 */
class NotificationType
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Ks\NotificationBundle\Entity\Notification", mappedBy="type", cascade={"remove", "persist"})
     */
    private $notificationsWithThisType;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\AbstractActivity", mappedBy="type", cascade={"remove", "persist"})
     */
    private $abstractActivitiesWithThisType;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\AbstractActivity", mappedBy="type", cascade={"remove", "persist"})
     */
    private $usersWhoReceivesThisTypeByMail;
    
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
    
    public function __construct()
    {
        $this->notificationsWithThisType = new \Doctrine\Common\Collections\ArrayCollection();
        $this->abstractActivitiesWithThisType = new \Doctrine\Common\Collections\ArrayCollection();
        $this->usersWhoReceivesThisTypeByMail = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add notificationsWithThisType
     *
     * @param Ks\NotificationBundle\Entity\Notification $notificationsWithThisType
     */
    public function addNotification(\Ks\NotificationBundle\Entity\Notification $notificationsWithThisType)
    {
        $this->notificationsWithThisType[] = $notificationsWithThisType;
    }

    /**
     * Get notificationsWithThisType
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getNotificationsWithThisType()
    {
        return $this->notificationsWithThisType;
    }
    
    public function __toString()
    {
        return strval($this->name);
    }

    /**
     * Add abstractActivitiesWithThisType
     *
     * @param Ks\ActivityBundle\Entity\AbstractActivity $abstractActivitiesWithThisType
     */
    public function addAbstractActivity(\Ks\ActivityBundle\Entity\AbstractActivity $abstractActivitiesWithThisType)
    {
        $this->abstractActivitiesWithThisType[] = $abstractActivitiesWithThisType;
    }

    /**
     * Get abstractActivitiesWithThisType
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAbstractActivitiesWithThisType()
    {
        return $this->abstractActivitiesWithThisType;
    }

    /**
     * Get usersWhoReceivesThisTypeByMail
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUsersWhoReceivesThisTypeByMail()
    {
        return $this->usersWhoReceivesThisTypeByMail;
    }
}