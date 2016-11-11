<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of ActivitySessionDetail
 * NOTE CF: désactivée
 *
 * ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\ActivityRepository")
 * ORM\InheritanceType("JOINED")
 * ORM\DiscriminatorColumn(name="activity_session_type", type="string")
 * ORM\DiscriminatorMap({
 *      "endurance" = "ActivitySessionEndurance",
 *      "endurance_on_earth" = "ActivitySessionEnduranceOnEarth",
 *      "endurance_under_water" = "ActivitySessionEnduranceUnderWater",
 *      "team_sport" = "ActivitySessionTeamSport"
 * })
 * ORM\Table(name="ks_activity_session_detail")
 */
abstract class ActivitySessionDetail
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\OneToOne(targetEntity="Ks\ActivityBundle\Entity\ActivitySession")
     * @ORM\JoinColumn(name="activity_session_id", referencedColumnName="id", nullable=false)
     * 
     * @var Ks\ActivityBundle\Entity\ActivitySession
     */
    protected $activitySession;
    
    public function __construct(\Ks\ActivityBundle\Entity\ActivitySession $activitySession)
    {
        $this->activitySession = $activitySession;
    }
    
    /**
     * Set activitySession
     *
     * @param Ks\ActivityBundle\Entity\ActivitySession $activitySession
     */
    public function setActivitySession(\Ks\ActivityBundle\Entity\ActivitySession $activitySession)
    {
        $this->activitySession = $activitySession;
    }

    /**
     * Get activitySession
     *
     * @return Ks\ActivityBundle\Entity\ActivitySession
     */
    public function getActivitySession()
    {
        return $this->activitySession;
    }
}