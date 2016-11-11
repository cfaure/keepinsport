<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of AbstractActivity
 *
 * @ORM\Entity
 */
class AbstractActivity extends Activity
{
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Activity", inversedBy="connectedActivities")
     * @ORM\JoinColumn(name="connectedActivity_id", referencedColumnName="id", nullable=true, onDelete="cascade", onUpdate="cascade")
     */
    protected $connectedActivity;
    
    /**
     * @var Ks\NotificationBundle\Entity\NotificationType $type
     * 
     * @ORM\ManyToOne(targetEntity="Ks\NotificationBundle\Entity\NotificationType", inversedBy="abstractActivitiesWithThisType")
     */
    protected $notificationType;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\TeamComposition", inversedBy="abstractActivities")
     * @ORM\JoinColumn(name="teamComposition_id", referencedColumnName="id", nullable=true, onDelete="cascade", onUpdate="cascade")
     */
    protected $teamComposition;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\EventBundle\Entity\Event", inversedBy="abstractActivities")
     * @ORM\JoinColumn(name="linked_event_id", referencedColumnName="id", nullable=true, onDelete="cascade", onUpdate="cascade")
     */
    protected $linkedEvent;
    
    
    public function __construct(\Ks\NotificationBundle\Entity\NotificationType $notificationType, \Ks\UserBundle\Entity\User $user = null)
    {
        parent::__construct($user);

        $this->notificationType = $notificationType;
        
        $this->type = "abstract_activity";
    }
  

    /**
     * Set connectedActivity
     *
     * @param Ks\ActivityBundle\Entity\Activity $connectedActivity
     */
    public function setConnectedActivity(\Ks\ActivityBundle\Entity\Activity $connectedActivity)
    {
        $this->connectedActivity = $connectedActivity;
    }

    /**
     * Get connectedActivity
     *
     * @return Ks\ActivityBundle\Entity\Activity 
     */
    public function getConnectedActivity()
    {
        return $this->connectedActivity;
    }

    /**
     * Set notificationType
     *
     * @param Ks\NotificationBundle\Entity\NotificationType $notificationType
     */
    public function setNotificationType(\Ks\NotificationBundle\Entity\NotificationType $notificationType)
    {
        $this->notificationType = $notificationType;
    }

    /**
     * Get notificationType
     *
     * @return Ks\NotificationBundle\Entity\NotificationType 
     */
    public function getNotificationType()
    {
        return $this->notificationType;
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
     * Set linkedEvent
     *
     * @param Ks\EventBundle\Entity\Event $linkedEvent
     */
    public function setLinkedEvent(\Ks\EventBundle\Entity\Event $linkedEvent)
    {
        $this->linkedEvent = $linkedEvent;
    }

    /**
     * Get linkedEvent
     *
     * @return Ks\EventBundle\Entity\Event 
     */
    public function getLinkedEvent()
    {
        return $this->linkedEvent;
    }

}