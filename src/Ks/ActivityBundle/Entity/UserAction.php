<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Ks\ActivityBundle\Entity\UserAction
 *
 * @ORM\Table(name="ks_user_action")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\UserActionRepository")
 */
class UserAction
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
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\Column(name="user_id", type="integer")
     * @ORM\JoinColumn(onDelete="cascade", onUpdate="cascade")
     */
    protected $userId;
    
    /**
     * @ORM\Column(type="string", length=255, name="action")
     *
     * @var string $action
     */
    private $action;
    
    /**
     * @ORM\Column(type="string", length=255, name="type")
     *
     * @var string $type
     */
    private $type;
    
    /**
     * @ORM\Column(type="string", length=25, name="result")
     *
     * @var string $result
     */
    private $result;
    
    /**
     * @ORM\Column(type="string", length=255, name="error", nullable=true)
     *
     * @var string $error
     */
    private $error;
    
    /**
     * @ORM\Column(name="done_at", type="datetime")
     */
    protected $doneAt;
    
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
     * Set action
     *
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
    
    public function getUserId() { return $this->userId; }
    public function setUserId($userId) { $this->userId = $userId; }
    public function getDoneAt() { return $this->doneAt; }
    public function setDoneAt(\DateTime $doneAt) { $this->doneAt = $doneAt; }
    public function getType() { return $this->type; }
    public function setType($type) { $this->type = $type; }
    public function getResult() { return $this->result; }
    public function setResult($result) { $this->result = $result; }
    public function getError() { return $this->error; }
    public function setError($error) { $this->error = $error; }
}