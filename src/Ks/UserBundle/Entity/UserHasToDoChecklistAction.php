<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\UserBundle\Entity\UserHasToDoChecklistAction
 *
 * @ORM\Table(name="ks_user_has_checklist_action")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\UserHasToDoChecklistActionRepository")
 */
class UserHasToDoChecklistAction
{
        
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="checklistActions")
     */
    private $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\ChecklistAction", inversedBy="users")
     */
    private $checklistAction;

    
    /**
     * @ORM\Column(type="datetime",nullable="true")
     * @Assert\DateTime()
     * @var datetime
     */
    protected $date;
    
    public function __construct()
    {
    }

    /**
     * Set date
     *
     * @param datetime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return datetime 
     */
    public function getDate()
    {
        return $this->date;
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

    /**
     * Set checklistAction
     *
     * @param Ks\UserBundle\Entity\ChecklistAction $checklistAction
     */
    public function setChecklistAction(\Ks\UserBundle\Entity\ChecklistAction $checklistAction)
    {
        $this->checklistAction = $checklistAction;
    }

    /**
     * Get checklistAction
     *
     * @return Ks\UserBundle\Entity\ChecklistAction 
     */
    public function getChecklistAction()
    {
        return $this->checklistAction;
    }
}