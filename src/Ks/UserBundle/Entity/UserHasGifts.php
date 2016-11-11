<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ks\UserBundle\Entity\UserHasGifts
 *
 * @ORM\Table(name="ks_user_has_gifts")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\UserHasGiftsRepository")
 */
class UserHasGifts
{
        
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     */
    private $user;
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ShopBundle\Entity\Shop")
     */
    private $shop;

    /**
     * @ORM\Column(type="datetime",nullable="true")
     * @Assert\DateTime()
     * @var datetime
     */
    protected $winAt;
    
    /**
     * @var string $gift
     *
     * @ORM\Column(name="gift", type="string", length=45, nullable=true)
     */
    private $gift;
    
    /**
     * @var string $sentence
     *
     * @ORM\Column(name="sentence", type="string", length=256, nullable=true)
     */
    private $sentence;

    
    public function __construct()
    {
    }

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
    
    /**
     * Set shop
     *
     * @param Ks\ShopBundle\Entity\Shop $shop
     */
    public function setShop(\Ks\ShopBundle\Entity\Shop $shop)
    {
        $this->shop = $shop;
    }

    /**
     * Get shop
     *
     * @return Ks\ShopBundle\Entity\Shop 
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * Set winAt
     *
     * @param datetime $winAt
     */
    public function setWinat($winAt)
    {
        $this->winAt = $winAt;
    }

    /**
     * Get winAt
     *
     * @return datetime 
     */
    public function getWinAt()
    {
        return $this->winAt;
    }
    
    /**
     * Set gift
     *
     * @param string $gift
     */
    public function setGift($gift)
    {
        $this->gift = $gift;
    }

    /**
     * Get gift
     *
     * @return string 
     */
    public function getGift()
    {
        return $this->gift;
    }
    
    /**
     * Set sentence
     *
     * @param string $sentence
     */
    public function setSentence($sentence)
    {
        $this->sentence = $sentence;
    }

    /**
     * Get sentence
     *
     * @return string 
     */
    public function getSentence()
    {
        return $this->sentence;
    }

    
}