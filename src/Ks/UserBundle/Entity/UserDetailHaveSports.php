<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\UserBundle\Entity\UserDetailHaveSports
 *
 * @ORM\Table(name="ks_user_detail_have_sports")
 * @ORM\Entity
 */
class UserDetailHaveSports
{
     /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\UserDetail", inversedBy="sports")
     */
    private $userDetail;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\ActivityBundle\Entity\Sport", inversedBy="userDetails")
     */
    private $sport;

    /**
     * Set userDetail
     *
     * @param Ks\UserBundle\Entity\UserDetail $userDetail
     */
    public function setUserDetail(\Ks\UserBundle\Entity\UserDetail $userDetail)
    {
        $this->userDetail = $userDetail;
    }

    /**
     * Get userDetail
     *
     * @return Ks\UserBundle\Entity\UserDetail 
     */
    public function getUserDetail()
    {
        return $this->userDetail;
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
}