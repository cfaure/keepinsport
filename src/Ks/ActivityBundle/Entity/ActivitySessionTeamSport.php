<?php

namespace Ks\ActivityBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of ActivitySessionTeamSport
 *
 * @ORM\Entity
 * @ORM\Table(name="ks_activity_session_team_sport")
 */
class ActivitySessionTeamSport extends ActivitySession
{   
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\Score", mappedBy="activity", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
     protected $scores;
    
    public function __construct(\Ks\UserBundle\Entity\User $user = null)
    {
        $time = new \DateTime();
        $time->setTime(1, 30);
        
        parent::__construct($user);
        
        $this->scores = new \Doctrine\Common\Collections\ArrayCollection();
        
        $this->type = 'session_team_sport';
    }
  
    /**
     * Add scores
     *
     * @param Ks\ActivityBundle\Entity\Score $scores
     */
    public function addScore(\Ks\ActivityBundle\Entity\Score $scores)
    {
        $this->scores[] = $scores;
    }

    /**
     * Get scores
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getScores()
    {
        return $this->scores;
    }
}