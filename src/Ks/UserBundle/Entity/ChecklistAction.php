<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\UserBundle\Entity\ChecklistAction
 *
 * @ORM\Table(name="ks_checklist_action")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\ChecklistActionRepository")
 */
class ChecklistAction
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
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=255)
     */
    private $label;
    
    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\UserHasToDoChecklistAction", mappedBy="checklistAction", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
    private $users;
    
    /**
     * @var int $order
     *
     * @ORM\Column(name="rank", type="integer")
     */
    private $rank;
    

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
     * Set label
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
    }
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add users
     *
     * @param Ks\UserBundle\Entity\UserHasToDoChecklistAction $users
     */
    public function addUserHasToDoChecklistAction(\Ks\UserBundle\Entity\UserHasToDoChecklistAction $users)
    {
        $this->users[] = $users;
    }

    /**
     * Get users
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set rank
     *
     * @param integer $rank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
    }

    /**
     * Get rank
     *
     * @return integer 
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }
}