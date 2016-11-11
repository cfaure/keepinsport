<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\UserBundle\Entity\Invitation
 *
 * @ORM\Table(name="ks_invitation")
 * @ORM\Entity
 */
class Invitation
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
     * @var string $email_guest
     *
     * @ORM\Column(name="email_guest", type="string", length=255)
     */
    private $email_guest;

    /**
     * @var smallint $pending_friend_request
     *
     * @ORM\Column(name="pending_friend_request", type="smallint")
     */
    private $pending_friend_request;
    
     /**
     * @var string $salt
     *
     * @ORM\Column(name="salt", type="string", length=13)
     */
    private $salt;

    /**
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="myInvitation")
     */
    private $userInviting;


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
     * Set email_guest
     *
     * @param string $emailGuest
     */
    public function setEmailGuest($emailGuest)
    {
        $this->email_guest = $emailGuest;
    }

    /**
     * Get email_guest
     *
     * @return string 
     */
    public function getEmailGuest()
    {
        return $this->email_guest;
    }
    
    /**
     * Set salt
     *
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * Get salt
     *
     * @return salt 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set pending_friend_request
     *
     * @param smallint $pendingFriendRequest
     */
    public function setPendingFriendRequest($pendingFriendRequest)
    {
        $this->pending_friend_request = $pendingFriendRequest;
    }

    /**
     * Get pending_friend_request
     *
     * @return smallint 
     */
    public function getPendingFriendRequest()
    {
        return $this->pending_friend_request;
    }
    
    
    /**
     * Set userInviting
     *
     * @param Ks\UserBundle\Entity\User $user
     */
    public function setUserInviting(\Ks\UserBundle\Entity\User $userInviting)
    {
        $this->userInviting = $userInviting;
    }

    /**
     * Get userInviting
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getUserInviting()
    {
        return $this->userInviting;
    }
    
    
    
}