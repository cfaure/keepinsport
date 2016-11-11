<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Ks\ActivityBundle\Entity\ArticleImage
 *
 * @ORM\Table(name="ks_article_image")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\ArticleImageRepository")
 */
class ArticleImage
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
     * @ORM\ManyToOne(targetEntity="\Ks\ActivityBundle\Entity\Article", inversedBy="images")
     */
    private $article_version;

    /**
     * @ORM\Column(name="url", type="text")
     */
    private $url;
    
    public function __construct($url)
    {
        $this->url = $url;
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
     * Set url
     *
     * @param text $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return text 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set article_version
     *
     * @param Ks\ActivityBundle\Entity\Article $articleVersion
     */
    public function setArticleVersion(\Ks\ActivityBundle\Entity\Article $articleVersion)
    {
        $this->article_version = $articleVersion;
    }

    /**
     * Get article_version
     *
     * @return Ks\ActivityBundle\Entity\Article 
     */
    public function getArticleVersion()
    {
        return $this->article_version;
    }
}