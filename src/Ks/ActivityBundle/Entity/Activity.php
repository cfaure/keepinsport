<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of Activity
 *
 * @ORM\Entity(repositoryClass="Ks\ActivityBundle\Entity\ActivityRepository") 
 * @ORM\HasLifecycleCallbacks
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="activity_type", type="string")
 * @ORM\DiscriminatorMap({
 *      "status"                        = "ActivityStatus",
 *      "session"                       = "ActivitySession",
 *      "session_endurance"             = "ActivitySessionEndurance",
 *      "session_endurance_on_earth"    = "ActivitySessionEnduranceOnEarth",
 *      "session_endurance_under_water" = "ActivitySessionEnduranceUnderWater",
 *      "session_team_sport"            = "ActivitySessionTeamSport",
 *      "article"                       = "Article",
 *      "abstract"                      = "AbstractActivity",
 *      "photo_album"                   = "PhotoAlbum",
 *      "sportsmen_search"              = "SportsmenSearch"
 * })
 * @ORM\Table(name="ks_activity")
 */
abstract class Activity
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @var int
     */
    protected $id;
    
    /**
     * @var Ks\UserBundle\Entity\User $user
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="myActivities")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $user;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true);
     * 
     * @var string
     */
    protected $label;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=false);
     * 
     * @var string
     */
    protected $type;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\MaxLength(1024)
     * @var text
     */
    protected $description;
    
    /**
     * @ORM\Column(type="integer")
     * 
     * @var integer
     */
    protected $vote;
    
    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     * @var datetime
     */
    protected $issuedAt;
    
    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     * @var datetime
     */
    protected $modifiedAt;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime()
     * @var datetime
     */
    protected $synchronizedAt;
    
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\Comment", mappedBy="activity")
     */
    protected $comments;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\ActivityHasVotes", mappedBy="activity")
     * 
     * @var ArrayCollection
     */
    protected $voters;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\ActivityHasNotes", mappedBy="activity")
     * 
     * @var ArrayCollection
     */
    protected $noters;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\ActivityHasSubscribers", mappedBy="activity")
     * 
     * @var ArrayCollection
     */
    protected $subscribers;
    
    /**
     * @ORM\Column(type="boolean")
     * 
     * @var boolean
     */
    protected $isDisabled;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\NotificationBundle\Entity\Notification", mappedBy="activity")
     */
    protected $notificationsFromThisActivity;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\ActivitySessionEarnsPoints", mappedBy="activitySession", cascade={"persist"})
     */
    protected $points; 
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\AbstractActivity", mappedBy="connectedActivity")
     */
    protected $connectedActivities;
    
    /**
     * @var boolean $isValidate
     * 
     * @ORM\Column(name="isValidate", type="boolean", options={"default" = true})
     */
    protected $isValidate;
    
    /**
     * @ORM\OneToOne(targetEntity="Ks\EventBundle\Entity\Event")
     * @ORM\JoinColumn(onDelete="cascade")
    */
    protected $event;
    
    /**
     * @ORM\OneToOne(targetEntity="Ks\EventBundle\Entity\Place", cascade={"persist"})
     * @Assert\Type(type="Ks\EventBundle\Entity\Place")
     */
    protected $place;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\Photo", mappedBy="activity")
     */
    protected $photos;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\ActivityIsDisturbing", mappedBy="activity")
     */
    protected $usersWhoWarnedLikeDisturbing;
    
    /**
     * @var Ks\ClubBundle\Entity\Club $club
     * 
     * @ORM\ManyToOne(targetEntity="Ks\ClubBundle\Entity\Club", inversedBy="activities")
     * @ORM\JoinColumn(name="club_id", referencedColumnName="id", nullable=true)
     */
    protected $club;
    
    /**
     * @var Ks\TournamentBundle\Entity\Tournament $tournament
     * 
     * @ORM\ManyToOne(targetEntity="Ks\TournamentBundle\Entity\Tournament", inversedBy="activities")
     * @ORM\JoinColumn(name="tournament_id", referencedColumnName="id", nullable=true)
     */
    protected $tournament;
    
    /**
    * @var boolean $isPublic
    *
    * @ORM\Column(name="isPublic", type="boolean")
    */
    protected $isPublic = true;


    public function __construct(\Ks\UserBundle\Entity\User $user = null )
    {
        $this->comments     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->voters       = new \Doctrine\Common\Collections\ArrayCollection();
        $this->subscribers  = new \Doctrine\Common\Collections\ArrayCollection();
        $this->user         = $user;
        $this->issuedAt     = new \DateTime();
        $this->modifiedAt   = new \DateTime();
        $this->vote         = 0;
        $this->isDisabled   = false;
        $this->isValidate   = true;
        $this->points       = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notificationsFromThisActivity    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->connectedActivities              = new \Doctrine\Common\Collections\ArrayCollection();
        $this->usersWhoWarnedLikeDisturbing              = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isPublic     = true;
    }
    
    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        return $this->id = $id;
    }

    /**
     * Set label
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
    }
    
    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set vote
     *
     * @param string $vote
     */
    public function setVote($vote)
    {
        $this->vote = $vote;
    }

    /**
     * Get vote
     *
     * @return string 
     */
    public function getVote()
    {
        return $this->vote;
    }

    /**
     * Set issuedAt
     *
     * @param datetime $issuedAt
     */
    public function setIssuedAt($issuedAt)
    {
        $this->issuedAt = $issuedAt;
    }

    /**
     * Get issuedAt
     *
     * @return datetime 
     */
    public function getIssuedAt()
    {
        return $this->issuedAt;
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
    
    /*public function toArray() {
        return $this->processArray(get_object_vars($this));
    }
   
    private function processArray($array) {
        foreach($array as $key => $value) {
            if (is_object($value)) {
                $array[$key] = $value->toArray();
            }
            if (is_array($value)) {
                $array[$key] = $this->processArray($value);
            }
        }
        // If the property isn't an object or array, leave it untouched
        return $array;
    }*/
   
    /*public function __toString() {
        return json_encode($this->toArray());
    }*/
    
    /*public function __toString()
    {
        return "$this->id";
    }*/


    /**
     * Add comments
     *
     * @param Ks\ActivityBundle\Entity\Comment $comments
     */
    public function addComment(\Ks\ActivityBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;
    }

    /**
     * Get comments
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add voters
     *
     * @param Ks\ActivityBundle\Entity\ActivityHasVotes $voters
     */
    public function addActivityHasVotes(\Ks\ActivityBundle\Entity\ActivityHasVotes $voters)
    {
        $this->voters[] = $voters;
    }

    /**
     * Get voters
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getVoters()
    {
        return $this->voters;
    }

    /**
     * Add subscribers
     *
     * @param Ks\ActivityBundle\Entity\ActivityHasSubscribers $subscribers
     */
    public function addActivityHasSubscribers(\Ks\ActivityBundle\Entity\ActivityHasSubscribers $subscribers)
    {
        $this->subscribers[] = $subscribers;
    }
    
    /**
     * Remove subscribers
     *
     * @param Ks\ActivityBundle\Entity\ActivityHasSubscribers $subscribers
     */
    public function removeUserHasFriends(\Ks\ActivityBundle\Entity\ActivityHasSubscribers $subscribers)
    {
        $removeSucces = false;
        $removeSucces = $this->subscribers->removeElement($subscribers);

        return $removeSucces;
    }

    /**
     * Get subscribers
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getSubscribers()
    {
        return $this->subscribers;
    }

    /**
     * Set isDisabled
     *
     * @param boolean $isDisabled
     */
    public function setIsDisabled($isDisabled)
    {
        $this->isDisabled = $isDisabled;
    }

    /**
     * Get isDisabled
     *
     * @return boolean 
     */
    public function getIsDisabled()
    {
        return $this->isDisabled;
    }
    
        /**
     * Add points
     *
     * @param Ks\ActivityBundle\Entity\ActivitySessionEarnsPoints $points
     */
    public function addActivitySessionEarnsPoints(\Ks\ActivityBundle\Entity\ActivitySessionEarnsPoints $points)
    {
        $this->points[] = $points;
    }

    /**
     * Get points
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Add notificationsFromThisActivity
     *
     * @param Ks\NotificationBundle\Entity\Notification $notificationsFromThisActivity
     */
    public function addNotification(\Ks\NotificationBundle\Entity\Notification $notificationsFromThisActivity)
    {
        $this->notificationsFromThisActivity[] = $notificationsFromThisActivity;
    }

    /**
     * Get notificationsFromThisActivity
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getNotificationsFromThisActivity()
    {
        return $this->notificationsFromThisActivity;
    }

    /**
     * Set modifiedAt
     *
     * @param datetime $modifiedAt
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
    }

    /**
     * Get modifiedAt
     *
     * @return datetime 
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }
    
    /**
     * Set synchronizedAt
     *
     * @param datetime $synchronizedAt
     */
    public function setSynchronizedAt($synchronizedAt)
    {
        $this->synchronizedAt = $synchronizedAt;
    }

    /**
     * Get synchronizedAt
     *
     * @return datetime 
     */
    public function getSynchronizedAt()
    {
        return $this->synchronizedAt;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add connectedActivities
     *
     * @param Ks\ActivityBundle\Entity\AbstractActivity $connectedActivities
     */
    public function addAbstractActivity(\Ks\ActivityBundle\Entity\AbstractActivity $connectedActivities)
    {
        $this->connectedActivities[] = $connectedActivities;
    }

    /**
     * Get connectedActivities
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getConnectedActivities()
    {
        return $this->connectedActivities;
    }

    /**
     * Set isValidate
     *
     * @param boolean $isValidate
     */
    public function setIsValidate($isValidate)
    {
        $this->isValidate = $isValidate;
    }

    /**
     * Get isValidate
     *
     * @return boolean 
     */
    public function getIsValidate()
    {
        return $this->isValidate;
    }

    /**
     * Set event
     *
     * @param Ks\EventBundle\Entity\Event $event
     */
    public function setEvent(\Ks\EventBundle\Entity\Event $event)
    {
        $this->event = $event;
    }

    /**
     * Get event
     *
     * @return Ks\EventBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set place
     *
     * @param Ks\EventBundle\Entity\Place $place
     * 
     */
    public function setPlace(\Ks\EventBundle\Entity\Place $place = null)
    {
        $this->place = $place;
    }

    /**
     * Get place
     *
     * @return Ks\EventBundle\Entity\Place 
     */
    public function getPlace()
    {
        return $this->place;
    }
    
    /** 
     * ORM\PrePersist 
     */
    public function checkPlaceOnPrePersist()
    {
        if ( !$this->place || ( $this->place && ( $this->place->getFullAdress() == null || $this->place->getFullAdress() == "" ) ) ) {
            $this->place = null;
        } 
    }
    
    /**
     * Add photo
     *
     * @param Ks\ActivityBundle\Entity\Photo $photos
     */
    public function addPhoto(\Ks\ActivityBundle\Entity\Photo $photo)
    {
        $this->photos[] = $photo;
    }
    
    /**
     * Remove photo
     *
     * @param \Ks\ActivityBundle\Entity\Photo $photo
     */
    public function removePhoto(\Ks\ActivityBundle\Entity\Photo $photo)
    {
        $removeSucces = false;
        $removeSucces = $this->photos->removeElement($photo);

        return $removeSucces;
    }

    /**
     * Get photos
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * Add usersWhoWarnedLikeDisturbing
     *
     * @param Ks\ActivityBundle\Entity\ActivityIsDisturbing $usersWhoWarnedLikeDisturbing
     */
    public function addActivityIsDisturbing(\Ks\ActivityBundle\Entity\ActivityIsDisturbing $usersWhoWarnedLikeDisturbing)
    {
        $this->usersWhoWarnedLikeDisturbing[] = $usersWhoWarnedLikeDisturbing;
    }

    /**
     * Get usersWhoWarnedLikeDisturbing
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUsersWhoWarnedLikeDisturbing()
    {
        return $this->usersWhoWarnedLikeDisturbing;
    }

    /**
     * Set club
     *
     * @param Ks\ClubBundle\Entity\Club $club
     */
    public function setClub(\Ks\ClubBundle\Entity\Club $club = null)
    {
        $this->club = $club;
    }

    /**
     * Get club
     *
     * @return Ks\ClubBundle\Entity\Club 
     */
    public function getClub()
    {
        return $this->club;
    }

    /**
     * Set tournament
     *
     * @param Ks\TournamentBundle\Entity\Tournament $tournament
     */
    public function setTournament(\Ks\TournamentBundle\Entity\Tournament $tournament)
    {
        $this->tournament = $tournament;
    }

    /**
     * Get tournament
     *
     * @return Ks\TournamentBundle\Entity\Tournament 
     */
    public function getTournament()
    {
        return $this->tournament;
    }
    
    /**
     * Set isPublic
     *
     * @param boolean $isPublic
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    /**
     * Get isPublic
     *
     * @return boolean 
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }
    
    public function __clone() {
        if ($this->id) {
            $this->setId(null);
            $newEvent = clone $this->getEvent();
            $em                 = $this->getDoctrine()->getEntityManager();
            $em->persist($newEvent);
            $em->flush();
            //$this->setEvent($newEvent);
            $this->event = clone $this->event;
        }
    }
}