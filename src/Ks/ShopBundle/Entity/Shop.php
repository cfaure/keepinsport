<?php

namespace Ks\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ShopBundle\Entity\Shop
 *
 * @ORM\Table(name="ks_shop")
 * @ORM\Entity(repositoryClass="Ks\ShopBundle\Entity\ShopRepository")
 */
class Shop
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
     */
    private $user;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
    
    /**
     * @var boolean $status
     *
     * @ORM\Column(name="status", type="boolean", options={"default" = false})
     */
    private $status;
    
    /**
     * @var boolean $webShop
     *
     * @ORM\Column(name="webShop", type="boolean", options={"default" = false})
     */
    private $webShop;
    

    /**
     * @var string $country_area
     *
     * @ORM\Column(name="country_area", type="string", length=255)
     */
    private $country_area;

    /**
     * @var string $longitude
     *
     * @ORM\Column(name="longitude", type="string", length=255)
     */
    private $longitude;

    /**
     * @var string $latitude
     *
     * @ORM\Column(name="latitude", type="string", length=255)
     */
    private $latitude;

    /**
     * @var string $country_code
     *
     * @ORM\Column(name="country_code", type="string", length=5)
     */
    private $country_code;

    /**
     * @var string $town
     *
     * @ORM\Column(name="town", type="string", length=45)
     */
    private $town;

    /**
     * @var text $address
     *
     * @ORM\Column(name="address", type="string", length=500, nullable=true)
     */
    private $address;

    /**
     * @var string $tel_number
     *
     * @ORM\Column(name="tel_number", type="string", length=15, nullable=true)
     */
    private $tel_number;

    /**
     * @var string $mobile_number
     *
     * @ORM\Column(name="mobile_number", type="string", length=15, nullable=true)
     */
    private $mobile_number;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string $url_site_web
     *
     * @ORM\Column(name="url_site_web", type="string", length=255, nullable=true)
     */
    private $url_site_web;
    
    /**
     * @ORM\ManyToMany(targetEntity="\Ks\ActivityBundle\Entity\Sport")
     * @ORM\JoinTable(name="ks_shop_has_sport")
     */
    private $sports;

    /**
     * @var string $avatar
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
     */
    private $avatar;
    
    /**
     * @var string $conditions
     *
     * @ORM\Column(name="conditions", type="text", nullable=true)
     */
    private $conditions;


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
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Set webShop
     *
     * @param string $webShop
     */
    public function setWebShop($webShop)
    {
        $this->webShop = $webShop;
    }

    /**
     * Get webShop
     *
     * @return string 
     */
    public function getWebShop()
    {
        return $this->webShop;
    }

    /**
     * Set country_area
     *
     * @param string $countryArea
     */
    public function setCountryArea($countryArea)
    {
        $this->country_area = $countryArea;
    }

    /**
     * Get country_area
     *
     * @return string 
     */
    public function getCountryArea()
    {
        return $this->country_area;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * Get longitude
     *
     * @return string 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * Get latitude
     *
     * @return string 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set country_code
     *
     * @param string $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->country_code = $countryCode;
    }

    /**
     * Get country_code
     *
     * @return string 
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * Set town
     *
     * @param string $town
     */
    public function setTown($town)
    {
        $this->town = $town;
    }

    /**
     * Get town
     *
     * @return string 
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * Set address
     *
     * @param text $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Get address
     *
     * @return text 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set tel_number
     *
     * @param string $telNumber
     */
    public function setTelNumber($telNumber)
    {
        $this->tel_number = $telNumber;
    }

    /**
     * Get tel_number
     *
     * @return string 
     */
    public function getTelNumber()
    {
        return $this->tel_number;
    }

    /**
     * Set mobile_number
     *
     * @param string $mobileNumber
     */
    public function setMobileNumber($mobileNumber)
    {
        $this->mobile_number = $mobileNumber;
    }

    /**
     * Get mobile_number
     *
     * @return string 
     */
    public function getMobileNumber()
    {
        return $this->mobile_number;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set url_site_web
     *
     * @param string $urlSiteWeb
     */
    public function setUrlSiteWeb($urlSiteWeb)
    {
        $this->url_site_web = $urlSiteWeb;
    }

    /**
     * Get url_site_web
     *
     * @return string 
     */
    public function getUrlSiteWeb()
    {
        return $this->url_site_web;
    }

    /**
     * Set sport
     *
     * @param \Ks\ActivityBundle\Entity\Sport $sport
     */
    public function setSport(\Ks\ActivityBundle\Entity\Sport $sport)
    {
        $this->sports[] = $sport;
    }

    /**
     * Get sport
     *
     * @return Ks\ActivityBundle\Entity\Sport 
     */
    public function getSports()
    {
        return $this->sports;
    }
    
    /**
     * Set avatar
     *
     * @param string $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }
    
    /**
     * Set conditions
     *
     * @param string $conditions
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * Get conditions
     *
     * @return string 
     */
    public function getConditions()
    {
        return $this->conditions;
    }
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ShopBundle\Entity\ShopHasVotes", mappedBy="shop")
     * 
     * @var ArrayCollection
     */
    protected $voters;
    
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
    
}