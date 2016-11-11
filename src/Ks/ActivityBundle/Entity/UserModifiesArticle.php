<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\ActivityBundle\Entity\UserModifiesArticle
 *
 * @ORM\Table(name="ks_user_modifies_article")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\UserModifiesArticleRepository")
 */
class UserModifiesArticle
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @var int
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Ks\ActivityBundle\Entity\Article", inversedBy="modifications")
     */
    private $article;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Ks\UserBundle\Entity\User", inversedBy="myArticles")
     */
    private $user;
    
    /**
     * @ORM\Column(type="text", nullable=false)
     * @var text
     */
    private $content;
    
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var boolean
     */
    private $titleWasChanged;
    
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var boolean
     */
    private $descriptionWasChanged;
    
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var boolean
     */
    private $elementsWereChanged;
    
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var boolean
     */
    private $photosWereChanged;
    
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var boolean
     */
    private $tagsWereChanged;
    
     /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var boolean
     */
    private $trainingPlanWasChanged;
    
    /**
     * @ORM\Column(type="datetime")
     * 
     * @var datetime
     */
    private $modifiedAt;
   

    public function __construct(\Ks\ActivityBundle\Entity\Article $article, \Ks\UserBundle\Entity\User $user)
    {
        $this->article          = $article;
        $this->user             = $user;
        $this->content          = "";
        $this->tags             = new \Doctrine\Common\Collections\ArrayCollection();
        $this->images           = new \Doctrine\Common\Collections\ArrayCollection();
        $this->modifiedAt       = new \DateTime();
        $this->descriptionWasChanged    = false;
        $this->elementsWereChanged      = false;
        $this->photosWereChanged        = false;
        $this->tagsWereChanged          = false;
        $this->trainingPlanWasChanged   = false;
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
     * Set content
     *
     * @param text $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return text 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set modifiedAt
     *
     * @param datetime $modifiedAt
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
    }

    /**
     * Get modifiedAt
     *
     * @return datetime 
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * Set article
     *
     * @param Ks\ActivityBundle\Entity\Article $article
     */
    public function setArticle(\Ks\ActivityBundle\Entity\Article $article)
    {
        $this->article = $article;
    }

    /**
     * Get article
     *
     * @return Ks\ActivityBundle\Entity\Article 
     */
    public function getArticle()
    {
        return $this->article;
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
     * Set elementsWereChanged
     *
     * @param boolean $elementsWereChanged
     */
    public function setElementsWereChanged($elementsWereChanged)
    {
        $this->elementsWereChanged = $elementsWereChanged;
    }

    /**
     * Get elementsWereChanged
     *
     * @return boolean 
     */
    public function getElementsWereChanged()
    {
        return $this->elementsWereChanged;
    }

    /**
     * Set photosWereChanged
     *
     * @param boolean $photosWereChanged
     */
    public function setPhotosWereChanged($photosWereChanged)
    {
        $this->photosWereChanged = $photosWereChanged;
    }

    /**
     * Get photosWereChanged
     *
     * @return boolean 
     */
    public function getPhotosWereChanged()
    {
        return $this->photosWereChanged;
    }

    /**
     * Set tagsWereChanged
     *
     * @param boolean $tagsWereChanged
     */
    public function setTagsWereChanged($tagsWereChanged)
    {
        $this->tagsWereChanged = $tagsWereChanged;
    }

    /**
     * Get tagsWereChanged
     *
     * @return boolean 
     */
    public function getTagsWereChanged()
    {
        return $this->tagsWereChanged;
    }

    /**
     * Set descriptionWasChanged
     *
     * @param boolean $descriptionWasChanged
     */
    public function setDescriptionWasChanged($descriptionWasChanged)
    {
        $this->descriptionWasChanged = $descriptionWasChanged;
    }

    /**
     * Get descriptionWasChanged
     *
     * @return boolean 
     */
    public function getDescriptionWasChanged()
    {
        return $this->descriptionWasChanged;
    }


    /**
     * Set trainingPlanWasChanged
     *
     * @param boolean $trainingPlanWasChanged
     */
    public function setTrainingPlanWasChanged($trainingPlanWasChanged)
    {
        $this->trainingPlanWasChanged = $trainingPlanWasChanged;
    }

    /**
     * Get trainingPlanWasChanged
     *
     * @return boolean 
     */
    public function getTrainingPlanWasChanged()
    {
        return $this->trainingPlanWasChanged;
    }

    /**
     * Set titleWasChanged
     *
     * @param boolean $titleWasChanged
     */
    public function setTitleWasChanged($titleWasChanged)
    {
        $this->titleWasChanged = $titleWasChanged;
    }

    /**
     * Get titleWasChanged
     *
     * @return boolean 
     */
    public function getTitleWasChanged()
    {
        return $this->titleWasChanged;
    }
}