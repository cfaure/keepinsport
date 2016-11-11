<?php

namespace Ks\TournamentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\TournamentBundle\Entity\Match
 *
 * @ORM\Table(name="ks_tournament_match")
 * @ORM\Entity(repositoryClass="Ks\TournamentBundle\Entity\MatchRepository")
 */
class Match
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
     * @var Ks\UserBundle\Entity\User $user1
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user1_id", referencedColumnName="id", nullable=true)
     */
    private $user1;
    
    /**
     * @var string $username1
     *
     * @ORM\Column(name="username1", type="string", length=255, nullable=true)
     */
    private $username1;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     * 
     * @var boolean
     */
    private $user1Won;
    
    /**
     * @var Ks\UserBundle\Entity\User $user2
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user2_id", referencedColumnName="id", nullable=true)
     */
    private $user2;
    
    /**
     * @var string $username2
     *
     * @ORM\Column(name="username2", type="string", length=255, nullable=true)
     */
    private $username2;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     * 
     * @var boolean
     */
    private $user2Won;
    
    /**
     * @var string $score
     *
     * @ORM\Column(name="score", type="string", length=255, nullable=true)
     */
    private $score;
    
    /**
     * @var Ks\TournamentBundle\Entity\Round $round
     * 
     * @ORM\ManyToOne(targetEntity="Ks\TournamentBundle\Entity\Round", inversedBy="matches")
     * @ORM\JoinColumn(name="round_id", referencedColumnName="id", nullable=true)
     */
    protected $round;

    public function __construct(\Ks\TournamentBundle\Entity\Round $round)
    {
        $this->round = $round;
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
     * Set score
     *
     * @param string $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * Get score
     *
     * @return string 
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set user1
     *
     * @param Ks\UserBundle\Entity\User $user1
     */
    public function setUser1( $user1)
    {
        $this->user1 = $user1;
    }

    /**
     * Get user1
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getUser1()
    {
        return $this->user1;
    }

    /**
     * Set user2
     *
     * @param Ks\UserBundle\Entity\User $user2
     */
    public function setUser2( $user2)
    {
        $this->user2 = $user2;
    }

    /**
     * Get user2
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getUser2()
    {
        return $this->user2;
    }

    /**
     * Set round
     *
     * @param Ks\TournamentBundle\Entity\Round $round
     */
    public function setRound(\Ks\TournamentBundle\Entity\Round $round)
    {
        $this->round = $round;
    }

    /**
     * Get round
     *
     * @return Ks\TournamentBundle\Entity\Round 
     */
    public function getRound()
    {
        return $this->round;
    }

    /**
     * Set username1
     *
     * @param string $username1
     */
    public function setUsername1($username1)
    {
        $this->username1 = $username1;
    }

    /**
     * Get username1
     *
     * @return string 
     */
    public function getUsername1()
    {
        return $this->username1;
    }

    /**
     * Set username2
     *
     * @param string $username2
     */
    public function setUsername2($username2)
    {
        $this->username2 = $username2;
    }

    /**
     * Get username2
     *
     * @return string 
     */
    public function getUsername2()
    {
        return $this->username2;
    }

    /**
     * Set user1Won
     *
     * @param boolean $user1Won
     */
    public function setUser1Won($user1Won)
    {
        $this->user1Won = $user1Won;
    }

    /**
     * Get user1Won
     *
     * @return boolean 
     */
    public function getUser1Won()
    {
        return $this->user1Won;
    }

    /**
     * Set user2Won
     *
     * @param boolean $user2Won
     */
    public function setUser2Won($user2Won)
    {
        $this->user2Won = $user2Won;
    }

    /**
     * Get user2Won
     *
     * @return boolean 
     */
    public function getUser2Won()
    {
        return $this->user2Won;
    }
}