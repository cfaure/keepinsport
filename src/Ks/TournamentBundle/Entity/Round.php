<?php

namespace Ks\TournamentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\TournamentBundle\Entity\Round
 *
 * @ORM\Table(name="ks_tournament_round")
 * @ORM\Entity
 */
class Round
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
     * @var integer $num
     *
     * @ORM\Column(name="num", type="integer")
     */
    private $num;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;
    
    /**
     * @var Ks\TournamentBundle\Entity\Tournament $round
     * 
     * @ORM\ManyToOne(targetEntity="Ks\TournamentBundle\Entity\Tournament", inversedBy="rounds")
     * @ORM\JoinColumn(name="tournament_id", referencedColumnName="id", nullable=true)
     */
    protected $tournament;
    
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\TournamentBundle\Entity\Match", mappedBy="round", cascade={"remove", "persist"})
     */
    protected $matches;


    public function __construct(\Ks\TournamentBundle\Entity\Tournament $tournament, $number )
    {
        $this->tournament = $tournament;
        $this->num = $number;
        $this->matches = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set num
     *
     * @param integer $num
     */
    public function setNum($num)
    {
        $this->num = $num;
    }

    /**
     * Get num
     *
     * @return integer 
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Set tournament
     *
     * @param Ks\TournamentBundle\Entity\Tournament $tournament
     */
    public function setTournament(\Ks\TournamentBundle\Entity\Tournament $tournament)
    {
        $this->tournament = $tournament;
    }

    /**
     * Get tournament
     *
     * @return Ks\TournamentBundle\Entity\Tournament 
     */
    public function getTournament()
    {
        return $this->tournament;
    }

    /**
     * Add matches
     *
     * @param Ks\TournamentBundle\Entity\Match $matches
     */
    public function addMatch(\Ks\TournamentBundle\Entity\Match $matches)
    {
        $this->matches[] = $matches;
    }

    /**
     * Get matches
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMatches()
    {
        return $this->matches;
    }
}