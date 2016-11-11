<?php

namespace Ks\CanvasDrawingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\CanvasDrawingBundle\Entity\Character
 *
 * @ORM\Table(name="ks_canvas_drawing_character")
 * @ORM\Entity(repositoryClass="Ks\CanvasDrawingBundle\Entity\CharacterRepository")
 */
class Character
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
     * @var string $skinColor
     *
     * @ORM\Column(name="skinColor", type="string", length=255)
     */
    private $skinColor;

    /**
     * @var string $hairColor
     *
     * @ORM\Column(name="hairColor", type="string", length=255)
     */
    private $hairColor;

    /**
     * @var string $eyesColor
     *
     * @ORM\Column(name="eyesColor", type="string", length=255)
     */
    private $eyesColor;

    /**
     * @var string $shirtColor
     *
     * @ORM\Column(name="shirtColor", type="string", length=255)
     */
    private $shirtColor;

    /**
     * @var string $shortColor
     *
     * @ORM\Column(name="shortColor", type="string", length=255)
     */
    private $shortColor;

    /**
     * @var string $shoesPrimaryColor
     *
     * @ORM\Column(name="shoesPrimaryColor", type="string", length=255)
     */
    private $shoesPrimaryColor;

    /**
     * @var string $shoesSecondaryColor
     *
     * @ORM\Column(name="shoesSecondaryColor", type="string", length=255)
     */
    private $shoesSecondaryColor;

    /**
     * @var string $sexeCode
     *
     * @ORM\Column(name="sexeCode", type="string", length=255)
     */
    private $sexeCode;

    public function __construct()
    {
        $this->setSexeCode("male");
        $this->setSkinColor("#fcb275");
        $this->setHairColor("#000000");
        $this->setEyesColor("#a52a2a");
        $this->setShirtColor("#001eff");
        $this->setShortColor("#000000");
        $this->setShoesPrimaryColor("#f5f6f5");
        $this->setShoesSecondaryColor("#3fc13f");
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
     * Set skinColor
     *
     * @param string $skinColor
     */
    public function setSkinColor($skinColor)
    {
        $this->skinColor = $skinColor;
    }

    /**
     * Get skinColor
     *
     * @return string 
     */
    public function getSkinColor()
    {
        return $this->skinColor;
    }

    /**
     * Set hairColor
     *
     * @param string $hairColor
     */
    public function setHairColor($hairColor)
    {
        $this->hairColor = $hairColor;
    }

    /**
     * Get hairColor
     *
     * @return string 
     */
    public function getHairColor()
    {
        return $this->hairColor;
    }

    /**
     * Set eyesColor
     *
     * @param string $eyesColor
     */
    public function setEyesColor($eyesColor)
    {
        $this->eyesColor = $eyesColor;
    }

    /**
     * Get eyesColor
     *
     * @return string 
     */
    public function getEyesColor()
    {
        return $this->eyesColor;
    }

    /**
     * Set shirtColor
     *
     * @param string $shirtColor
     */
    public function setShirtColor($shirtColor)
    {
        $this->shirtColor = $shirtColor;
    }

    /**
     * Get shirtColor
     *
     * @return string 
     */
    public function getShirtColor()
    {
        return $this->shirtColor;
    }

    /**
     * Set shortColor
     *
     * @param string $shortColor
     */
    public function setShortColor($shortColor)
    {
        $this->shortColor = $shortColor;
    }

    /**
     * Get shortColor
     *
     * @return string 
     */
    public function getShortColor()
    {
        return $this->shortColor;
    }

    /**
     * Set shoesPrimaryColor
     *
     * @param string $shoesPrimaryColor
     */
    public function setShoesPrimaryColor($shoesPrimaryColor)
    {
        $this->shoesPrimaryColor = $shoesPrimaryColor;
    }

    /**
     * Get shoesPrimaryColor
     *
     * @return string 
     */
    public function getShoesPrimaryColor()
    {
        return $this->shoesPrimaryColor;
    }

    /**
     * Set shoesSecondaryColor
     *
     * @param string $shoesSecondaryColor
     */
    public function setShoesSecondaryColor($shoesSecondaryColor)
    {
        $this->shoesSecondaryColor = $shoesSecondaryColor;
    }

    /**
     * Get shoesSecondaryColor
     *
     * @return string 
     */
    public function getShoesSecondaryColor()
    {
        return $this->shoesSecondaryColor;
    }

    /**
     * Set sexeCode
     *
     * @param string $sexeCode
     */
    public function setSexeCode($sexeCode)
    {
        $this->sexeCode = $sexeCode;
    }

    /**
     * Get sexeCode
     *
     * @return string 
     */
    public function getSexeCode()
    {
        return $this->sexeCode;
    }
}