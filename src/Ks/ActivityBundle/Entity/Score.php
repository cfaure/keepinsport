<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ActivityBundle\Entity\Score
 *
 * @ORM\Table(name="ks_score")
 * @ORM\Entity
 */
class Score
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
     * @var integer $score1
     *
     * @ORM\Column(name="score1", type="integer", nullable=true)
     */
    private $score1;
    
    /**
     * @var integer $score2
     *
     * @ORM\Column(name="score2", type="integer", nullable=true)
     */
    private $score2;
    
    /**
     * @var integer $order
     *
     * @ORM\Column(name="roundOrder", type="integer")
     */
    private $roundOrder;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\ActivitySessionTeamSport", inversedBy="scores")
     * @ORM\JoinColumn(name="activity_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     * @var Ks\ActivityBundle\Entity\ActivitySessionTeamSport
     */
    protected $activity;


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
     * Set score1
     *
     * @param integer $score1
     */
    public function setScore1($score1)
    {
        $this->score1 = $score1;
    }

    /**
     * Get score1
     *
     * @return integer 
     */
    public function getScore1()
    {
        return $this->score1;
    }

    /**
     * Set score2
     *
     * @param integer $score2
     */
    public function setScore2($score2)
    {
        $this->score2 = $score2;
    }

    /**
     * Get score2
     *
     * @return integer 
     */
    public function getScore2()
    {
        return $this->score2;
    }

    /**
     * Set roundOrder
     *
     * @param integer $roundOrder
     */
    public function setRoundOrder($roundOrder)
    {
        $this->roundOrder = $roundOrder;
    }

    /**
     * Get roundOrder
     *
     * @return integer 
     */
    public function getRoundOrder()
    {
        return $this->roundOrder;
    }

    /**
     * Set activity
     *
     * @param Ks\ActivityBundle\Entity\ActivitySessionTeamSport $activity
     */
    public function setActivity(\Ks\ActivityBundle\Entity\ActivitySessionTeamSport $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Get activity
     *
     * @return Ks\ActivityBundle\Entity\ActivitySessionTeamSport 
     */
    public function getActivity()
    {
        return $this->activity;
    }
}