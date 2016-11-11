<?php

namespace Ks\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ShopBundle\Entity\ShopHasVotes
 *
 * @ORM\Table(name="ks_shop_has_votes")
 * @ORM\Entity(repositoryClass="Ks\ShopBundle\Entity\ShopHasVotesRepository")
 */
class ShopHasVotes
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ShopBundle\Entity\Shop")
     * @ORM\JoinColumn(onDelete="cascade", onUpdate="cascade", nullable=false)
     */
    private $shop;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $voter;
    
    public function __construct(\Ks\ShopBundle\Entity\Shop $shop, \Ks\UserBundle\Entity\User $voter)
    {
        $this->shop     = $shop;
        $this->voter    = $voter;
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
     * Set voter
     *
     * @param Ks\UserBundle\Entity\User $voter
     */
    public function setVoter(\Ks\UserBundle\Entity\User $voter)
    {
        $this->voter = $voter;
    }

    /**
     * Get voter
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getVoter()
    {
        return $this->voter;
    }
}