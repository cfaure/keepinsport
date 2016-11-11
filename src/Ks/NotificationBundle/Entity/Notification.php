<?php

namespace Ks\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\NotificationBundle\Entity\Notification
 *
 * @ORM\Table(name="ks_notification")
 * @ORM\Entity(repositoryClass="Ks\NotificationBundle\Entity\NotificationRepository")
 */
class Notification
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
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="myNotifications")
     */
    private $owner;

    /**
     * @var Ks\UserBundle\Entity\User $fromUser
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="notificationsFromMe")
     */
    private $fromUser;
    
    /**
     * @var Ks\UserBundle\Entity\User $fromUser
     * 
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Club", inversedBy="notificationsFromMe")
     */
    private $fromClub;

    /**
     * @var Ks\NotificationBundle\Entity\NotificationType $type
     * 
     * @ORM\ManyToOne(targetEntity="Ks\NotificationBundle\Entity\NotificationType", inversedBy="notificationsWithThisType")
     */
    private $type;
    
    /**
     * @var text $text
     * 
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * @var boolean $read
     * 
     * @ORM\Column(name="isRead", type="boolean")
     */
    private $isRead = false;

    /**
     * @var boolean $read
     * 
     * @ORM\Column(name="needAnAnswer", type="boolean")
     */
    private $needAnAnswer = false;
    
    /**
     * @var boolean $read
     * 
     * @ORM\Column(name="gotAnAnswer", type="boolean", nullable="true")
     */
    private $gotAnAnswer = false;

    /**
     * @var DateTime $createdAt
     * 
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;
    
    /**
     * @var DateTime $readAt
     * 
     * @ORM\Column(name="readAt", type="datetime", nullable="true")
     */
    private $readAt;

    
    
     /**
      * @ORM\ManyToOne(targetEntity="Ks\EventBundle\Entity\Event")
     *  @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="cascade")
     */
    private $event;
    
    /**
     * @var boolean $possibleMerge
     * 
     * @ORM\Column(name="possibleMerge", type="boolean")
     */
    private $possibleMerge = false;
    
    /**
     * @var boolean $isMerged
     * 
     * @ORM\Column(name="isMerged", type="boolean")
     */
    private $isMerged = false;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\NotificationBundle\Entity\Notification", mappedBy="mergedNotifications")
     */
    protected $mergedNotifications;
    
    /**
     * @var Ks\UserBundle\Entity\User $user
     * 
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Activity")
     * @ORM\JoinColumn(name="activity_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     */
    private $activity;
    
    /**
     * @var Ks\UserBundle\Entity\User $user
     * 
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\TeamComposition", inversedBy="notifications")
     * @ORM\JoinColumn(name="teamComposition_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     */
    private $teamComposition;
    
    /**
     * @var Ks\MessageBundle\Entity\Message $message
     * 
     * @ORM\ManyToOne(targetEntity="Ks\MessageBundle\Entity\Message", inversedBy="notifications")
     * @ORM\JoinColumn(name="message_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     */
    private $message;

    public function __construct()
    {
        $this->createdAt            = new \DateTime();
        $this->readAt               = null;
        $this->isRead               = false;
        $this->needAnAnswer         = false;
        $this->possibleMerge        = false;
        $this->isMerged             = false;
        $this->mergedNotifications  = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set the owner of the notification (who the notification is to)
     *
     * @param UserInterface $owner
     */
    public function setOwner(\Ks\UserBundle\Entity\User $owner)
    {
        $this->owner = $owner;
    }

    /**
     * Get the owner of the notification
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set the sender of the notification (who the notification is from)
     *
     * @param UserInterface $fromUser
     */
    public function setFromUser(\Ks\UserBundle\Entity\User $fromUser)
    {
        $this->fromUser = $fromUser;
    }

    /**
     * Get the sender of the notification
     *
     * @return User
     */
    public function getFromUser()
    {
        return $this->fromUser;
    }

    /**
     * Set the notification type
     *
     * @param integer $type
     */
    public function setType(\Ks\NotificationBundle\Entity\NotificationType $type)
    {
        $this->type = $type;
        
        //Si on traite une demande d'ami, le "fromUser" attend une reponse
        if ($type->getName() == "ask_friend_request")  {
            $this->gotAnAnswer = false;
        }   
    }

    /**
     * Get the notification type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * Set isRead
     *
     * @param boolean $isRead
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;
    }

    /**
     * Get isRead
     *
     * @return boolean 
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return datetime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set readAt
     *
     * @param datetime $readAt
     */
    public function setReadAt($readAt)
    {
        $this->readAt = $readAt;
    }

    /**
     * Get readAt
     *
     * @return datetime 
     */
    public function getReadAt()
    {
        return $this->readAt;
    }

    /**
     * Set gotAnAnswer
     *
     * @param boolean $gotAnAnswer
     */
    public function setGotAnAnswer($gotAnAnswer)
    {
        $this->gotAnAnswer = $gotAnAnswer;
    }

    /**
     * Get gotAnAnswer
     *
     * @return boolean 
     */
    public function getGotAnAnswer()
    {
        return $this->gotAnAnswer;
    }

    /**
     * Set needAnAnswer
     *
     * @param boolean $needAnAnswer
     */
    public function setNeedAnAnswer($needAnAnswer)
    {
        $this->needAnAnswer = $needAnAnswer;
    }

    /**
     * Get needAnAnswer
     *
     * @return boolean 
     */
    public function getNeedAnAnswer()
    {
        return $this->needAnAnswer;
    }



    /**
     * Set activity
     *
     * @param Ks\ActivityBundle\Entity\Activity $activity
     */
    public function setActivity(\Ks\ActivityBundle\Entity\Activity $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Get activity
     *
     * @return Ks\ActivityBundle\Entity\Activity 
     */
    public function getActivity()
    {
        return $this->activity;
    }



    /**
     * Set event
     *
     * @param Ks\EventBundle\Entity\Event $event
     */
    public function setEvent(\Ks\EventBundle\Entity\Event $event)
    {
        $this->event = $event;
    }

    /**
     * Get event
     *
     * @return Ks\EventBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set possibleMerge
     *
     * @param boolean $possibleMerge
     */
    public function setPossibleMerge($possibleMerge)
    {
        $this->possibleMerge = $possibleMerge;
    }

    /**
     * Get possibleMerge
     *
     * @return boolean 
     */
    public function getPossibleMerge()
    {
        return $this->possibleMerge;
    }

    /**
     * Set isMerged
     *
     * @param boolean $isMerged
     */
    public function setIsMerged($isMerged)
    {
        $this->isMerged = $isMerged;
    }

    /**
     * Get isMerged
     *
     * @return boolean 
     */
    public function getIsMerged()
    {
        return $this->isMerged;
    }

    /**
     * Add notification
     *
     * @param Ks\NotificationBundle\Entity\Notification $notification
     */
    public function addNotification(\Ks\NotificationBundle\Entity\Notification $notification)
    {
        $this->mergedNotifications[] = $notification;
    }

    /**
     * Get mergedNotifications
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMergedNotifications()
    {
        return $this->mergedNotifications;
    }

    /**
     * Set fromClub
     *
     * @param Ks\ClubBundle\Entity\Club $fromClub
     */
    public function setFromClub(\Ks\ClubBundle\Entity\Club $fromClub)
    {
        $this->fromClub = $fromClub;
    }

    /**
     * Get fromClub
     *
     * @return Ks\ClubBundle\Entity\Club 
     */
    public function getFromClub()
    {
        return $this->fromClub;
    }

    /**
     * Set teamComposition
     *
     * @param Ks\ClubBundle\Entity\TeamComposition $teamComposition
     */
    public function setTeamComposition(\Ks\ClubBundle\Entity\TeamComposition $teamComposition)
    {
        $this->teamComposition = $teamComposition;
    }

    /**
     * Get teamComposition
     *
     * @return Ks\ClubBundle\Entity\TeamComposition 
     */
    public function getTeamComposition()
    {
        return $this->teamComposition;
    }

    /**
     * Set text
     *
     * @param text $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return text 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set message
     *
     * @param Ks\MessageBundle\Entity\Message $message
     */
    public function setMessage(\Ks\MessageBundle\Entity\Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get message
     *
     * @return Ks\MessageBundle\Entity\Message 
     */
    public function getMessage()
    {
        return $this->message;
    }
}