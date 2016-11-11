<?php

namespace Ks\EvolutionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\EvolutionBundle\Entity\EvolutionHasVotes
 *
 * @ORM\Table(name="ks_evolution_has_votes")
 * @ORM\Entity(repositoryClass="Ks\EvolutionBundle\Entity\EvolutionHasVotesRepository")
 */
class EvolutionHasVotes
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\EvolutionBundle\Entity\Evolution")
     * @ORM\JoinColumn(onDelete="cascade", onUpdate="cascade", nullable=false)
     */
    private $evolution;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $voter;
    
    public function __construct(\Ks\EvolutionBundle\Entity\Evolution $evolution, \Ks\UserBundle\Entity\User $voter)
    {
        $this->evolution     = $evolution;
        $this->voter        = $voter;
    }

    /**
     * Set evolution
     *
     * @param Ks\EvolutionBundle\Entity\Evolution $evolution
     */
    public function setEvolution(\Ks\EvolutionBundle\Entity\Evolution $evolution)
    {
        $this->evolution = $evolution;
    }

    /**
     * Get evolution
     *
     * @return Ks\EvolutionBundle\Entity\Evolution 
     */
    public function getEvolution()
    {
        return $this->evolution;
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