<?php

namespace Ks\UserBundle\Entity;
namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ActivityBundle\Entity\ActivityHasNotes
 *
 * @ORM\Table(name="ks_activity_has_notes")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\ActivityHasNotesRepository")
 */
class ActivityHasNotes
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Activity", inversedBy="noters")
     * @ORM\JoinColumn(onDelete="cascade", onUpdate="cascade")
     */
    private $activity;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="myNotes")
     */
    private $noter;
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\ActivityNote", fetch="LAZY")
     * 
     * @var Ks\ActivityBundle\Entity\ActivityNote
     */
    protected $activityNote;
    
    /**
     * @var float $val
     *
     * @ORM\Column(name="val", type="float", nullable=true)
     */
    private $val;
    
    public function __construct(\Ks\ActivityBundle\Entity\Activity $activity, \Ks\UserBundle\Entity\User $noter, \Ks\ActivityBundle\Entity\ActivityNote $activityNote)
    {
        $this->activity         = $activity;
        $this->noter            = $noter;
        $this->activityNote     = $activityNote;
    }

    /**
     * Set activity
     *
     * @param Ks\ActivityBundle\Entity\Activity $activity
     */
    public function setActivity(\Ks\ActivityBundle\Entity\Activity $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Get activity
     *
     * @return Ks\ActivityBundle\Entity\Activity 
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Set noter
     *
     * @param Ks\UserBundle\Entity\User $noter
     */
    public function setNoter(\Ks\UserBundle\Entity\User $noter)
    {
        $this->noter = $noter;
    }

    /**
     * Get noter
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getNoter()
    {
        return $this->noter;
    }
    
    /**
     * Set noteType
     *
     * @param Ks\ActivityBundle\Entity\ActivityNote $activityNote
     */
    public function setActivityNote(\Ks\ActivityBundle\Entity\ActivityNote $activityNote)
    {
        $this->activityNote = $activityNote;
    }

    /**
     * Get noteType
     *
     * @return Ks\ActivityBundle\Entity\ActivityNote 
     */
    public function getActivityNote()
    {
        return $this->activityNote;
    }
    
    /**
     * Set val
     *
     * @param integer $val
     */
    public function setVal($val)
    {
        $this->val = $val;
    }

    /**
     * Get val
     *
     * @return integer 
     */
    public function getVal()
    {
        return $this->val;
    }
}