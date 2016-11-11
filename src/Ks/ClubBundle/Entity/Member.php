<?php

namespace Ks\ClubBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ClubBundle\Entity\Member
 *
 * @ORM\Table(name="ks_member")
 * @ORM\Entity(repositoryClass="Ks\ClubBundle\Entity\MemberRepository")
 */
class Member
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
    * @ORM\OneToOne(targetEntity="Ks\UserBundle\Entity\User")
    */
    private $user;

    /**
     * @var string $firstname
     *
     * @ORM\Column(name="firstname", type="string", length=45, nullable=true)
     */
    private $firstname;

    /**
     * @var string $lastname
     *
     * @ORM\Column(name="lastname", type="string", length=45, nullable=true)
     */
    private $lastname;
    
    /**
    * @ORM\OneToMany(targetEntity="Ks\ClubBundle\Entity\ClubHasMembers", mappedBy="member" , cascade={"remove", "persist"})
    */
    private $clubs;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ClubBundle\Entity\MemberHasTeams", mappedBy="member" )
     */
    private $teams;
    
   public function __construct()
    {
        $this->myMembersTeam = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set firstname
     *
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }
    
    public function __toString(){
        return $this->lastname." ".$this->firstname;
    }
    
     /**
     * Get lastname and firstname
     *
     * @return string 
     */
    public function getLastnameFirstname()
    {
        return $this->lastname." ".$this->firstname;
    }
    

    /**
     * Get clubMembers
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getClubMembers()
    {
        return $this->clubMembers;
    }

    /**
     * Get teamMembers
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTeamMembers()
    {
        return $this->teamMembers;
    }

    /**
     * Add clubMembers
     *
     * @param Ks\ClubBundle\Entity\ClubHasMembers $clubMembers
     */
    public function addClubHasMembers(\Ks\ClubBundle\Entity\ClubHasMembers $clubMembers)
    {
        $this->clubMembers[] = $clubMembers;
    }

    /**
     * Get clubs
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getClubs()
    {
        return $this->clubs;
    }

    /**
     * Add teams
     *
     * @param Ks\ClubBundle\Entity\MemberHasTeams $teams
     */
    public function addMemberHasTeams(\Ks\ClubBundle\Entity\MemberHasTeams $teams)
    {
        $this->teams[] = $teams;
    }

    /**
     * Get teams
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTeams()
    {
        return $this->teams;
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