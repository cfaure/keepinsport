<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of ActivityStatus
 *
 * @ORM\Entity
 */
class ActivityStatus extends Activity
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @var string
     */
    protected $photo;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @var string
     */
    protected $link;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @var string
     */
    protected $viewLink;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * 
     * @var string
     */
    protected $linkDescription;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\UserReadsImportantStatus", mappedBy="activity", cascade={"remove", "persist"})
     */
    private $importantStatus;
    
    public function __construct(\Ks\UserBundle\Entity\User $user = null)
    {
        parent::__construct($user);

        $this->type = "status";
    }
    
    /**
     * Set photo
     *
     * @param string $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
        if( $this->photo != null ) {
            $this->type = "photo";
        }
    }

    /**
     * Get photo
     *
     * @return string 
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set link
     *
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
        if( $this->link != null ) {
            $this->type = "link";
        }
    }

    /**
     * Get link
     *
     * @return string 
     */
    public function getLink()
    {
        return $this->link;
    }
    
    /**
     * Set linkDescription
     *
     * @param text $linkDescription
     */
    public function setLinkDescription($linkDescription)
    {
        $this->linkDescription = $linkDescription;
    }

    /**
     * Get linkDescription
     *
     * @return text 
     */
    public function getLinkDescription()
    {
        return $this->linkDescription;
    }


    /**
     * Set viewLink
     *
     * @param string $viewLink
     */
    public function setViewLink($viewLink)
    {
        $this->viewLink = $viewLink;
        if( $this->viewLink != null ) {
            $this->type = "video";
        }
    }

    /**
     * Get viewLink
     *
     * @return string 
     */
    public function getViewLink()
    {
        return $this->viewLink;
    }
    

    /**
     * Add importantStatus
     *
     * @param Ks\ActivityBundle\Entity\UserReadsImportantStatus $importantStatus
     */
    public function addUserReadsImportantStatus(\Ks\ActivityBundle\Entity\UserReadsImportantStatus $importantStatus)
    {
        $this->importantStatus[] = $importantStatus;
    }

    /**
     * Get importantStatus
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getImportantStatus()
    {
        return $this->importantStatus;
    }

    
}