<?php

namespace Ks\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\MessageBundle\Entity\Message
 *
 * @ORM\Table(name="ks_message")
 * @ORM\Entity(repositoryClass="Ks\MessageBundle\Entity\MessageRepository")
 */
class Message
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
     * @var datetime $sentAt
     *
     * @ORM\Column(name="sentAt", type="datetime")
     */
    private $sentAt;
    
    /**
     * @var string $content
     *
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private $subject;

    /**
     * @var string $content
     *
     * @ORM\Column(name="content", type="string", length=2000)
     */
    private $content;
    
    /**
     * @var Ks\UserBundle\Entity\User $user
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="sentMessages")
     * @ORM\JoinColumn(name="from_user_id", referencedColumnName="id", nullable=true)
     */
    private $fromUser;
    
     /**
     * @var Ks\MessageBundle\Entity\Message $message
     * 
     * @ORM\ManyToOne(targetEntity="Ks\MessageBundle\Entity\Message", inversedBy="myActivities")
     * @ORM\JoinColumn(name="previous_message_id", referencedColumnName="id", nullable=true)
     */
    private $previousMessage;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\MessageBundle\Entity\Message", mappedBy="previousMessage", cascade={"remove", "persist"})
     */
    private $answers;
    
    /**
     * @ORM\ManyToMany(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\JoinTable(name="ks_user_receives_message")
     */
    private $toUsers;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\NotificationBundle\Entity\Notification", mappedBy="message", cascade={"remove", "persist"} )
     */
    private $notifications;

    public function __construct(\Ks\UserBundle\Entity\User $user )
    {
        $this->fromUser         = $user;
        $this->content          = "";
        $this->sentAt           = new \DateTime();
        $this->previousMessage  = null;
        $this->answers          = new \Doctrine\Common\Collections\ArrayCollection();
        $this->toUsers          = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications          = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set sentAt
     *
     * @param datetime $sentAt
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;
    }

    /**
     * Get sentAt
     *
     * @return datetime 
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * Set content
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Set fromUser
     *
     * @param Ks\UserBundle\Entity\User $fromUser
     */
    public function setFromUser(\Ks\UserBundle\Entity\User $fromUser)
    {
        $this->fromUser = $fromUser;
    }

    /**
     * Get fromUser
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getFromUser()
    {
        return $this->fromUser;
    }

    /**
     * Set previousMessage
     *
     * @param Ks\MessageBundle\Entity\Message $previousMessage
     */
    public function setPreviousMessage(\Ks\MessageBundle\Entity\Message $previousMessage)
    {
        $this->previousMessage = $previousMessage;
    }

    /**
     * Get previousMessage
     *
     * @return Ks\MessageBundle\Entity\Message 
     */
    public function getPreviousMessage()
    {
        return $this->previousMessage;
    }

    /**
     * Add answers
     *
     * @param Ks\MessageBundle\Entity\Message $answers
     */
    public function addMessage(\Ks\MessageBundle\Entity\Message $answers)
    {
        $this->answers[] = $answers;
    }

    /**
     * Get answers
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAnswers()
    {
        return $this->answers;
    }



    /**
     * Add user
     *
     * @param Ks\UserBundle\Entity\User $user
     */
    public function addUser(\Ks\UserBundle\Entity\User $user)
    {
        $this->toUsers[] = $user;
    }
    
    /**
     * Remove user
     *
     * @param Ks\UserBundle\Entity\User $user
     */
    public function removeUser(\Ks\UserBundle\Entity\User $user)
    {
        return $this->toUsers->removeElement($user);
    }

    /**
     * Get toUsers
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getToUsers()
    {
        return $this->toUsers;
    }

    /**
     * Set subject
     *
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Add notifications
     *
     * @param Ks\NotificationBundle\Entity\Notification $notifications
     */
    public function addNotification(\Ks\NotificationBundle\Entity\Notification $notifications)
    {
        $this->notifications[] = $notifications;
    }

    /**
     * Get notifications
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getNotifications()
    {
        return $this->notifications;
    }
}