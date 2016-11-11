<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\ActivityBundle\Entity\TrainingPlan
 *
 * @ORM\Table(name="ks_training_plan")
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\TrainingPlanRepository")
 */
class TrainingPlan
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
     * @var integer $numberOfSessionsPerWeek
     *
     * @ORM\Column(name="numberOfSessionsPerWeek", type="integer")
     */
    private $numberOfSessionsPerWeek;

    /**
     * @var integer $numberOfWeeks
     *
     * @ORM\Column(name="numberOfWeeks", type="integer")
     */
    private $numberOfWeeks;

    /**
     * @var integer $numberMaxOfRestDaysBetweenTwoSessions
     *
     * @ORM\Column(name="numberMaxOfRestDaysBetweenTwoSessions", type="integer")
     */
    private $numberMaxOfRestDaysBetweenTwoSessions;
    
    /**
     * @var integer $numberMinOfRestDaysBetweenTwoSessions
     *
     * @ORM\Column(name="numberMinOfRestDaysBetweenTwoSessions", type="integer")
     */
    private $numberMinOfRestDaysBetweenTwoSessions;


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
     * Set numberOfSessionsPerWeek
     *
     * @param integer $numberOfSessionsPerWeek
     */
    public function setNumberOfSessionsPerWeek($numberOfSessionsPerWeek)
    {
        $this->numberOfSessionsPerWeek = $numberOfSessionsPerWeek;
    }

    /**
     * Get numberOfSessionsPerWeek
     *
     * @return integer 
     */
    public function getNumberOfSessionsPerWeek()
    {
        return $this->numberOfSessionsPerWeek;
    }

    /**
     * Set numberOfWeeks
     *
     * @param integer $numberOfWeeks
     */
    public function setNumberOfWeeks($numberOfWeeks)
    {
        $this->numberOfWeeks = $numberOfWeeks;
    }

    /**
     * Get numberOfWeeks
     *
     * @return integer 
     */
    public function getNumberOfWeeks()
    {
        return $this->numberOfWeeks;
    }

    /**
     * Set numberMaxOfRestDaysBetweenTwoSessions
     *
     * @param integer $numberMaxOfRestDaysBetweenTwoSessions
     */
    public function setNumberMaxOfRestDaysBetweenTwoSessions($numberMaxOfRestDaysBetweenTwoSessions)
    {
        $this->numberMaxOfRestDaysBetweenTwoSessions = $numberMaxOfRestDaysBetweenTwoSessions;
    }

    /**
     * Get numberMaxOfRestDaysBetweenTwoSessions
     *
     * @return integer 
     */
    public function getNumberMaxOfRestDaysBetweenTwoSessions()
    {
        return $this->numberMaxOfRestDaysBetweenTwoSessions;
    }

    /**
     * Set numberMinOfRestDaysBetweenTwoSessions
     *
     * @param integer $numberMinOfRestDaysBetweenTwoSessions
     */
    public function setNumberMinOfRestDaysBetweenTwoSessions($numberMinOfRestDaysBetweenTwoSessions)
    {
        $this->numberMinOfRestDaysBetweenTwoSessions = $numberMinOfRestDaysBetweenTwoSessions;
    }

    /**
     * Get numberMinOfRestDaysBetweenTwoSessions
     *
     * @return integer 
     */
    public function getNumberMinOfRestDaysBetweenTwoSessions()
    {
        return $this->numberMinOfRestDaysBetweenTwoSessions;
    }
}