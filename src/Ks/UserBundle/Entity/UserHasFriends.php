<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\UserBundle\Entity\UserHasFriends
 *
 * @ORM\Table(name="ks_user_has_friends")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\UserHasFriendsRepository")
 */
class UserHasFriends
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="myFriends")
     */
    private $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="friendsWithMe")
     */
    private $friend;

    /**
     * @var boolean $pending_request
     *
     * @ORM\Column(name="pending_friend_request", type="boolean")
     */
    private $pending_friend_request;

    
    public function __construct(\Ks\UserBundle\Entity\User $user, \Ks\UserBundle\Entity\User $friend)
    {
        $this->user                     = $user;
        $this->friend                   = $friend;
        $this->pending_friend_request   = true;
    }
    
    /**
     * Set pending_friend_request
     *
     * @param boolean $pendingFriendRequest
     */
    public function setPendingFriendRequest($pendingFriendRequest)
    {
        $this->pending_friend_request = $pendingFriendRequest;
    }

    /**
     * Get pending_friend_request
     *
     * @return boolean 
     */
    public function getPendingFriendRequest()
    {
        return $this->pending_friend_request;
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
     * Set friend
     *
     * @param Ks\UserBundle\Entity\User $friend
     */
    public function setFriend(\Ks\UserBundle\Entity\User $friend)
    {
        $this->friend = $friend;
    }

    /**
     * Get friend
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getFriend()
    {
        return $this->friend;
    }
    
    /**
     * Remove friend
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function removeFriend()
    {
        return $this->friend;
    }
}