<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\UserBundle\Entity\Sexe
 *
 * @ORM\Table(name="ks_user_detail_sexe")
 * @ORM\Entity
 */
class Sexe
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
     * @var string $nom
     *
     * @ORM\Column(name="nom", type="string", length=20)
     */
    private $nom;
    
    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=20)
     */
    private $code;
    
    
    /**
    * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\UserDetail", mappedBy="sexe", cascade={"remove", "persist"})
    */
    private $sexeUser;

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
     * Set nom
     *
     * @param string $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }
    
     public function __toString()
    {
        return $this->nom;
    }
    
    public function __construct()
    {
        $this->sexeUser = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Add sexeUser
     *
     * @param Ks\UserBundle\Entity\UserDetail $sexeUser
     */
    public function addUserDetail(\Ks\UserBundle\Entity\UserDetail $sexeUser)
    {
        $this->sexeUser[] = $sexeUser;
    }

    /**
     * Get sexeUser
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getSexeUser()
    {
        return $this->sexeUser;
    }
}