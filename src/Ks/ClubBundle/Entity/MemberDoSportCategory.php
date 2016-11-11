<?php

namespace Ks\ClubBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ClubBundle\Entity\MemberDoSportCategory
 *
 * @ORM\Table(name="ks_member_do_sport_category")
 * @ORM\Entity(repositoryClass="Ks\ClubBundle\Entity\MemberDoSportCategoryRepository")
 */
class MemberDoSportCategory
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}