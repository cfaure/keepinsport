<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\UserBundle\Entity\InvitationEmailBeta
 *
 * @ORM\Table(name="ks_invitation_email_beta")
 * @ORM\Entity
 */
class InvitationEmailBeta
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
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="emailsBeta")
     */
    private $userInviting;
    
     /**
     * @var boolean $could_invit
     *
     * @ORM\Column(name="could_invit", type="boolean")
     */
    private $could_invit;
    
     /**
     * @var boolean $nomber_invitation
     *
     * @ORM\Column(name="nomber_invitation", type="integer")
     */
    private $nomber_invitation;
    
    

     public function __construct($email = null)
    {
        $this->email         = $email;
     
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
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set could_invit
     *
     * @param boolean $couldInvit
     */
    public function setCouldInvit($couldInvit)
    {
        $this->could_invit = $couldInvit;
    }

    /**
     * Get could_invit
     *
     * @return boolean 
     */
    public function getCouldInvit()
    {
        return $this->could_invit;
    }

    /**
     * Set nomber_invitation
     *
     * @param integer $nomberInvitation
     */
    public function setNomberInvitation($nomberInvitation)
    {
        $this->nomber_invitation = $nomberInvitation;
    }

    /**
     * Get nomber_invitation
     *
     * @return integer 
     */
    public function getNomberInvitation()
    {
        return $this->nomber_invitation;
    }

    /**
     * Set userInviting
     *
     * @param Ks\UserBundle\Entity\User $userInviting
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