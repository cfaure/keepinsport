<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\ActivityBundle\Entity\ArticleTag
 *
 * @ORM\Table(name="ks_article_tag")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\ArticleTagRepository")
 */
class ArticleTag
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
     * @ORM\Column(name="label", type="string", nullable=false)
     */
    private $label;
    
    /**
     * @ORM\Column(name="isCategory", type="boolean", nullable=false)
     */
    private $isCategory;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\Article", mappedBy="categoryTag", cascade={"remove", "persist"})
     */
    private $articles;
    
    public function __construct($label, $isCategory = false)
    {
        $this->label = $label;
        $this->isCategory = $isCategory;
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set label
     *
     * @param text $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get label
     *
     * @return text 
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set isCategory
     *
     * @param boolean $isCategory
     */
    public function setIsCategory($isCategory)
    {
        $this->isCategory = $isCategory;
    }

    /**
     * Get isCategory
     *
     * @return boolean 
     */
    public function getIsCategory()
    {
        return $this->isCategory;
    }

    /**
     * Add articles
     *
     * @param Ks\ActivityBundle\Entity\Article $articles
     */
    public function addArticle(\Ks\ActivityBundle\Entity\Article $articles)
    {
        $this->articles[] = $articles;
    }

    /**
     * Get articles
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getArticles()
    {
        return $this->articles;
    }
}