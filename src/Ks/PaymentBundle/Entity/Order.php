<?php

namespace Ks\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\PaymentBundle\Entity\Order
 *
 * @ORM\Table(name="ks_order")
 * @ORM\Entity(repositoryClass="Ks\PaymentBundle\Entity\PaymentRepository")
 */
class Order
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
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=50, nullable=false)
     */
    private $status;
    
    /**
     * @var string $watch
     *
     * @ORM\Column(name="watch", type="string", length=50, nullable=false)
     */
    private $watch;
    
    /**
     * @var string $pack
     *
     * @ORM\Column(name="pack", type="string", length=50, nullable=false)
     */
    private $pack;
    
    /**
     * @var string $amounts
     *
     * @ORM\Column(name="amounts", type="string", length=255, nullable=false)
     */
    private $amounts;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;
    
    
    /**
     * @var string $payboxAnswer
     *
     * @ORM\Column(name="paybox_answer", type="text", nullable=false)
     */
    private $payboxAnswer;
    
    /**
     * @var string $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;
    
    /**
     * @var string $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    private $updatedAt;
    
    
    // getters & setters
    
    public function getId() {
        return $this->id;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getWatch() {
        return $this->watch;
    }

    public function getPack() {
        return $this->pack;
    }

    public function getAmounts() {
        return $this->amounts;
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

    public function getPayboxAnswer() {
        return $this->payboxAnswer;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setWatch($watch) {
        $this->watch = $watch;
    }

    public function setPack($pack) {
        $this->pack = $pack;
    }

    public function setAmounts($amounts) {
        $this->amounts = $amounts;
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

    public function setPayboxAnswer($payboxAnswer) {
        $this->payboxAnswer = $payboxAnswer;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;
    }
}