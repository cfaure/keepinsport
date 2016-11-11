<?php

namespace Ks\ClubBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ClubBundle\Entity\CategorySportMembre
 *
 * @ORM\Table(name="ks_category_sport_membre")
 * @ORM\Entity(repositoryClass="Ks\ClubBundle\Entity\CategorySportMembreRepository")
 */
class CategorySportMembre
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
    
    
    /**
     * @var Ks\ActivityBundle\Entity\Sport $sport
     * 
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Sport")
     * @ORM\JoinColumn(name="sport_id", referencedColumnName="id", nullable=false)
     */
    protected $sport;
    
    /**
     * @var Ks\ClubBundle\Entity\Member $member
     * 
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Member")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", nullable=false)
     */
    protected $member;
    
    
    /**
     * @var Ks\ClubBundle\Entity\CategoryMembre $category
     * 
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\CategoryMembre")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false)
     */
    protected $category;


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
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
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
    
    /**
     * Set member
     *
     * @param Ks\ClubBundle\Entity\Member $member
     */
    public function setMember(\Ks\ClubBundle\Entity\Member $member)
    {
        $this->member = $member;
    }

    /**
     * Get member
     *
     * @return Ks\ClubBundle\Entity\Member 
     */
    public function getMember()
    {
        return $this->member;
    }
    
       /**
     * Set category
     *
     * @param Ks\ClubBundle\Entity\CategoryMembre $category
     */
    public function setCategory(\Ks\ClubBundle\Entity\CategoryMembre $category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return Ks\ClubBundle\Entity\CategoryMembre
     */
    public function getCategory()
    {
        return $this->category;
    }
    
    
    
    
}