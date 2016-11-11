<?php

namespace Ks\ClubBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ClubBundle\Entity\UserManageClub
 *
 * @ORM\Table(name="ks_user_manage_clubs")
 * @ORM\Entity(repositoryClass="Ks\ClubBundle\Entity\UserManageClubRepository")
 */
class UserManageClub
{
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Club", inversedBy="myUsersManager")
     */
    private $club;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="myClubsManaged")
     */
    private $user;
    
    /**
     * @var boolean $super_admin
     *
     * @ORM\Column(name="super_admin", type="boolean")
     * Si false => manager de type encadrant
     * Si true => manager type super_admin
     */
    private $superAdmin;
    
    /**
     * @var string $function
     *
     * @ORM\Column(name="function", type="string", nullable=true)
     */
    private $function;
    
    public function __construct(\Ks\ClubBundle\Entity\Club $club = null, \Ks\UserBundle\Entity\User $user = null, $superAdmin = false)
    {
        $this->club = $club;
        $this->user = $user;
        $this->superAdmin = $superAdmin;
    }
   

    /**
     * Set super_admin
     *
     * @param boolean $superAdmin
     */
    public function setSuperAdmin($superAdmin)
    {
        $this->superAdmin = $superAdmin;
    }

    /**
     * Get super_admin
     *
     * @return boolean 
     */
    public function getSuperAdmin()
    {
        return $this->superAdmin;
    }
    
    /**
     * Set function
     *
     * @param string function
     */
    public function setFunction($function)
    {
        $this->function = $function;
    }

    /**
     * Get function
     *
     * @return string 
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Set club
     *
     * @param Ks\ClubBundle\Entity\Club $club
     */
    public function setClub(\Ks\ClubBundle\Entity\Club $club)
    {
        $this->club = $club;
    }

    /**
     * Get club
     *
     * @return Ks\ClubBundle\Entity\Club 
     */
    public function getClub()
    {
        return $this->club;
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
}