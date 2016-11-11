<?php

namespace Ks\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\EventBundle\Entity\Place
 *
 * @ORM\Table(name="ks_place")
 * @ORM\Entity(repositoryClass="Ks\EventBundle\Entity\PlaceRepository")
 */
class Place
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
     * @var string $full_adress
     *
     * @ORM\Column(name="full_adress", type="string", length=255, nullable=true)
     */
    private $fullAdress;

    /**
     * @var string $country_code
     *
     * @ORM\Column(name="country_code", type="string", length=5, nullable=true)
     */
    private $countryCode;
    
    /**
     * @var string $country_label
     *
     * @ORM\Column(name="country_label", type="string", length=100, nullable=true)
     */
    private $countryLabel;

    /**
     * @var string $region_code
     *
     * @ORM\Column(name="region_code", type="string", length=100, nullable=true)
     */
    private $regionCode;
    
    /**
     * @var string $region_label
     *
     * @ORM\Column(name="region_label", type="string", length=100, nullable=true)
     */
    private $regionLabel;
    
    /**
     * @var string $county_code
     *
     * @ORM\Column(name="county_code", type="string", length=30, nullable=true)
     */
    private $countyCode;
    
    /**
     * @var string $county_label
     *
     * @ORM\Column(name="county_label", type="string", length=100, nullable=true)
     */
    private $countyLabel;

    /**
     * @var string $town_code
     *
     * @ORM\Column(name="town_code", type="string", length=100, nullable=true)
     */
    private $townCode;
    
    /**
     * @var string $town
     *
     * @ORM\Column(name="town_label", type="string", length=100, nullable=true)
     */
    private $townLabel;

     /**
     * @var decimal $longitude
     *
     * @ORM\Column(name="longitude", type="float", scale="8", nullable=true)
     */
    private $longitude;
    
    
    /**
     * @var decimal $latitude
     *
     * @ORM\Column(name="latitude", type="float", scale="8",nullable=true)
     */
    private $latitude;
    
    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set fullAdress
     *
     * @param string $fullAdress
     */
    public function setFullAdress($fullAdress)
    {
        $this->fullAdress = $fullAdress;
    }

    /**
     * Get fullAdress
     *
     * @return string 
     */
    public function getFullAdress()
    {
        return $this->fullAdress;
    }

    /**
     * Set countryCode
     *
     * @param string $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * Get countryCode
     *
     * @return string 
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set countryLabel
     *
     * @param string $countryLabel
     */
    public function setCountryLabel($countryLabel)
    {
        $this->countryLabel = $countryLabel;
    }

    /**
     * Get countryLabel
     *
     * @return string 
     */
    public function getCountryLabel()
    {
        return $this->countryLabel;
    }

    /**
     * Set regionCode
     *
     * @param string $regionCode
     */
    public function setRegionCode($regionCode)
    {
        $this->regionCode = $regionCode;
    }

    /**
     * Get regionCode
     *
     * @return string 
     */
    public function getRegionCode()
    {
        return $this->regionCode;
    }

    /**
     * Set regionLabel
     *
     * @param string $regionLabel
     */
    public function setRegionLabel($regionLabel)
    {
        $this->regionLabel = $regionLabel;
    }

    /**
     * Get regionLabel
     *
     * @return string 
     */
    public function getRegionLabel()
    {
        return $this->regionLabel;
    }

    /**
     * Set countyCode
     *
     * @param string $countyCode
     */
    public function setCountyCode($countyCode)
    {
        $this->countyCode = $countyCode;
    }

    /**
     * Get countyCode
     *
     * @return string 
     */
    public function getCountyCode()
    {
        return $this->countyCode;
    }

    /**
     * Set countyLabel
     *
     * @param string $countyLabel
     */
    public function setCountyLabel($countyLabel)
    {
        $this->countyLabel = $countyLabel;
    }

    /**
     * Get countyLabel
     *
     * @return string 
     */
    public function getCountyLabel()
    {
        return $this->countyLabel;
    }

    /**
     * Set townCode
     *
     * @param string $townCode
     */
    public function setTownCode($townCode)
    {
        $this->townCode = $townCode;
    }

    /**
     * Get townCode
     *
     * @return string 
     */
    public function getTownCode()
    {
        return $this->townCode;
    }

    /**
     * Set townLabel
     *
     * @param string $townLabel
     */
    public function setTownLabel($townLabel)
    {
        $this->townLabel = $townLabel;
    }

    /**
     * Get townLabel
     *
     * @return string 
     */
    public function getTownLabel()
    {
        return $this->townLabel;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * Get longitude
     *
     * @return float 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set latitude
     *
     * @param float $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * Get latitude
     *
     * @return float 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }
}