<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ActivityBundle\Entity\ActivityHasSubscribers
 *
 * @ORM\Table(name="ks_activity_has_subscribers")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\ActivityHasSubscribersRepository")
 */
class ActivityHasSubscribers
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="mySubscriptions")
     */
    private $subscriber;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Activity", inversedBy="subscribers")
     * @ORM\JoinColumn(onDelete="cascade", onUpdate="cascade")
     */
    private $activity;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     * 
     * @var boolean
     */
    private $hasUnsubscribed;
    
    public function __construct(\Ks\ActivityBundle\Entity\Activity $activity, \Ks\UserBundle\Entity\User $subscriber)
    {
        $this->activity     = $activity;
        $this->subscriber   = $subscriber;
    }

    /**
     * Set subscriber
     *
     * @param Ks\UserBundle\Entity\User $subscriber
     */
    public function setSubscriber(\Ks\UserBundle\Entity\User $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * Get subscriber
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getSubscriber()
    {
        return $this->subscriber;
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
     * Set hasUnsubscribed
     *
     * @param boolean $hasUnsubscribed
     */
    public function setHasUnsubscribed($hasUnsubscribed)
    {
        $this->hasUnsubscribed = $hasUnsubscribed;
    }

    /**
     * Get hasUnsubscribed
     *
     * @return boolean 
     */
    public function getHasUnsubscribed()
    {
        return $this->hasUnsubscribed;
    }
}