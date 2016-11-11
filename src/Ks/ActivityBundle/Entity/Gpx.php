<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Ks\ActivityBundle\Entity\Gpx
 *
 * @ORM\Table(name="ks_gpx")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\GpxRepository")
 */
class Gpx
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
     * @ORM\OneToOne(targetEntity="Ks\ActivityBundle\Entity\ActivitySession", mappedBy="gpx")
     * @ORM\JoinColumn(name="activity_id", onDelete="cascade", onUpdate="cascade")
     */
    protected $activity;
    
    /**
     * @ORM\Column(type="string", length=255, name="name")
     *
     * @var string $name
     */
    private $name;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\Column(name="user_id", type="integer")
     * @ORM\JoinColumn(onDelete="cascade", onUpdate="cascade")
     */
    protected $uploadedBy;
    
    /**
     * @ORM\Column(name="uploaded_at", type="datetime")
     */
    protected $uploadedAt;
    
    protected $path;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Sport")
     * @ORM\JoinColumn(onDelete="set null")
     */
    protected $sport;
    
    /**
     * @Assert\File(mimeTypes={"application/xml"})
     */
    protected $file;
    
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
     * Set name
     *
     * @param datetime $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return datetime 
     */
    public function getName()
    {
        return $this->name;
    }
    
    
    
    /**
     * 
     * @return type
     */
    public function getFileContent()
    {
        return $this->fileContent;
    }
    
    /**
     * Set sport
     *
     * @param Ks\ActivityBundle\Entity\Sport $sport
     */
    public function setSport(\Ks\ActivityBundle\Entity\Sport $sport)
    {
        $this->sport = $sport;
    }

    /**
     * Get sport
     *
     * @return Ks\ActivityBundle\Entity\Sport
     */
    public function getSport()
    {
        return $this->sport;
    }
    
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return sys_get_temp_dir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return '';
    }
    
    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->file) {
            return;
        }
        $filename = uniqid().$this->file->getClientOriginalName();
        $path = __DIR__.'/../../../../web/uploads/gpx/';
        $this->getFile()->move($path, $filename);
        
        $this->setName($filename);
        
        // clean up the file property as you won't need it anymore
        $this->file = null;
    }
    
    public function getFile()
    {
        return $this->file;
    }
    
    public function setFile($file)
    {
        $this->file = $file;
    }
    
    public function getUploadedBy() { return $this->uploadedBy; }
    public function setUploadedBy($uploadedBy) { $this->uploadedBy = $uploadedBy; }
    public function getUploadedAt() { return $this->uploadedAt; }
    public function setUploadedAt(\DateTime $uploadedAt) { $this->uploadedAt = $uploadedAt; }
    public function setActivity($activity) {
        $this->activity = $activity;
    }
}