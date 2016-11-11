<?php

namespace Ks\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\NotificationBundle\Entity\UserReceivesMailNotifications
 *
 * @ORM\Table(name="ks_user_receives_mail_notifications")
 * @ORM\Entity(repositoryClass="Ks\NotificationBundle\Entity\UserReceivesMailNotificationsRepository")
 */
class UserReceivesMailNotifications
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
     * @var Ks\UserBundle\Entity\User $owner
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="mailNotifications")
     */
    private $user;
    
    /**
     * @var Ks\NotificationBundle\Entity\NotificationType $type
     * 
     * @ORM\ManyToOne(targetEntity="Ks\NotificationBundle\Entity\NotificationType", inversedBy="usersWhoReceivesThisTypeByMail")
     */
    private $type;

    /**
     * @var boolean $wantsReceive
     *
     * @ORM\Column(name="wantsReceive", type="boolean")
     */
    private $wantsReceive;


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
     * Set wantsReceive
     *
     * @param boolean $wantsReceive
     */
    public function setWantsReceive($wantsReceive)
    {
        $this->wantsReceive = $wantsReceive;
    }

    /**
     * Get wantsReceive
     *
     * @return boolean 
     */
    public function getWantsReceive()
    {
        return $this->wantsReceive;
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
     * Set type
     *
     * @param Ks\NotificationBundle\Entity\NotificationType $type
     */
    public function setType(\Ks\NotificationBundle\Entity\NotificationType $type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return Ks\NotificationBundle\Entity\NotificationType 
     */
    public function getType()
    {
        return $this->type;
    }
}