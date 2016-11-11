<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\ActivityBundle\Entity\Comment
 *
 * @ORM\Table(name="ks_comment")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\CommentRepository")
 */
class Comment
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
     * @var Ks\UserBundle\Entity\User $user
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;
    
    /**
     * @var Ks\UserBundle\Entity\User $user
     * 
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Activity")
     * @ORM\JoinColumn(name="activity_id", referencedColumnName="id", nullable=false, onDelete="cascade", onUpdate="cascade")
     */
    private $activity;

    /**
     * @var text $comment
     * @Assert\NotBlank(message="Le commentaire ne peut pas être vide")
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;

    /**
     * @var datetime $commentedAt
     * @Assert\DateTime()
     * @ORM\Column(name="commentedAt", type="datetime")
     */
    private $commentedAt;

    public function __construct()
    {
        $this->commentedAt = new \DateTime();
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
     * Set comment
     *
     * @param text $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get comment
     *
     * @return text 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set commentedAt
     *
     * @param datetime $commentedAt
     */
    public function setCommentedAt($commentedAt)
    {
        $this->commentedAt = $commentedAt;
    }

    /**
     * Get commentedAt
     *
     * @return datetime 
     */
    public function getCommentedAt()
    {
        return $this->commentedAt;
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
}