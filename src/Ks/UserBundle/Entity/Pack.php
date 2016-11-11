<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\UserBundle\Entity\Pack
 *
 * @ORM\Table(name="ks_pack")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\UserRepository")
 */
class Pack
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
     * @ORM\Column(name="code", type="string", length=128)
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\UserHasPack", mappedBy="pack", cascade={"remove", "persist"})
     */
    private $usersWhoHavePack;  
    
    
    public function __construct()
    {
        $this->usersWhoHavePack              = new \Doctrine\Common\Collections\ArrayCollection();
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
    
    /**
     * Add usersWhoHavePack
     *
     * @param Ks\UserBundle\Entity\UserHasPack $usersWhoHavePack
     */
    public function addUserHasPack(\Ks\UserBundle\Entity\UserHasPack $usersWhoHavePack)
    {
        $this->usersWhoHavePack[] = $usersWhoHavePack;
    }

    /**
     * Get usersWhoHavePack
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUserWhoHavePack()
    {
        return $this->usersWhoHavePack;
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