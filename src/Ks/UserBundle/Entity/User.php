<?php

namespace Ks\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Ks\UserBundle\Entity\UserDetail;
use Ks\UserBundle\Entity\Sexe;


/**
 * Ks\UserBundle\Entity\User
 *
 * @ORM\Table(name="ks_user")
 * @ORM\Entity(repositoryClass="Ks\UserBundle\Entity\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime()
     * @var datetime
     */
    protected $inscribedAt;

    /**
     * @var string
     * 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $facebookId;
    
    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    protected $googleId;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\UserHasFriends", mappedBy="user", cascade={"remove", "persist"})
     */
    private $myFriends;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\UserHasFriends", mappedBy="friend", cascade={"remove", "persist"})
     */
    private $friendsWithMe;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\NotificationBundle\Entity\Notification", mappedBy="owner", cascade={"remove", "persist"})
     */
    private $myNotifications;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\NotificationBundle\Entity\Notification", mappedBy="fromUser", cascade={"remove", "persist"})
     */
    private $notificationsFromMe;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\Activity", mappedBy="user", cascade={"remove", "persist"})
     */
    private $myActivities;
    
     /**
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $lastname;
    
    /**
     * @ORM\ManyToMany(targetEntity="Ks\ActivityBundle\Entity\Activity")
     * @ORM\JoinTable(name="ks_user_has_hidden_activities")
     */
    private $activitiesIHaveHidden;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\Comment", mappedBy="user", cascade={"remove", "persist"})
     */
    private $myComments;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\ActivityHasVotes", mappedBy="voter", cascade={"remove", "persist"})
     */
    private $myVotes;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\ActivityHasSubscribers", mappedBy="subscriber", cascade={"remove", "persist"})
     */
    private $mySubscriptions;
       
    /**
     * @ORM\OneToOne(targetEntity="Ks\UserBundle\Entity\UserDetail", cascade={"remove", "persist"})
     */
    private $userDetail;
    
    /*
    * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\Invitation", mappedBy="userInviting", cascade={"remove", "persist"})
    */
    private $myInvitations;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\UserHasInvited", mappedBy="user", cascade={"remove", "persist"})
    */
    private $myInvited;
    
     /**
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\UserHasInvited", mappedBy="invited", cascade={"remove", "persist"})
     */
    private $InvitedByMe;
         
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\ActivitySessionEarnsPoints", mappedBy="user", cascade={"remove", "persist"})
     */
    private $myPoints;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ClubBundle\Entity\ClubHasUsers", mappedBy="user", cascade={"remove", "persist"})
     */
    private $clubs;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\LeagueBundle\Entity\LeagueLevel", inversedBy="users")
     */
    private $leagueLevel;
    

    /**
    * @ORM\OneToOne(targetEntity="Ks\ClubBundle\Entity\Member", cascade={"remove", "persist"})
    */
    private $member;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ClubBundle\Entity\UserManageClub", mappedBy="user", cascade={"remove", "persist"})
     */
    private $myClubsManaged;
    

    /**
     * @ORM\OneToOne(targetEntity="Ks\TrophyBundle\Entity\Showcase", cascade={"remove", "persist"})
     */
    private $showcase;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\LeagueBundle\Entity\Historic", mappedBy="user", cascade={"remove", "persist"})
     */
    private $leaguesHistorics;
    
    /*
    * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\InvitationEmailBeta", mappedBy="userInviting", cascade={"remove", "persist"})
    */
    private $emailsBeta;
    
    /*---------Test -----*/
    
    
    /**
     * @var string proximite
     *
     * @ORM\Column(name="proximite", type="decimal", scale="2" ,nullable=true)
     */
    protected $proximite;
    
    
     /**
     * @ORM\OneToOne(targetEntity="Ks\AgendaBundle\Entity\Agenda", cascade={"remove", "persist"})
     */
    private $agenda;
    
    
    /*
    * @ORM\OneToMany(targetEntity="\Ks\ActivityBundle\Entity\UserModifiesArticle", mappedBy="user", cascade={"remove", "persist"})
    */
    private $myArticles;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\UserHasServices", mappedBy="user", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
    protected $services;
    
    
    
     /**
     * @ORM\OneToMany(targetEntity="Ks\EventBundle\Entity\InvitationEvent", mappedBy="userInviting", cascade={"remove", "persist"})
    */
    private $userInvitingEvent;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\Feedback", mappedBy="user", cascade={"remove", "persist"})
     */
    private $feedbacks;
    
    /**
     * @ORM\ManyToMany(targetEntity="Ks\ActivityBundle\Entity\Article")
     * @ORM\JoinTable(name="ks_user_participates_article_sporting_event")
     */
    //private $articleSportingEventsParticipations;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\EventBundle\Entity\UserParticipatesEvent", mappedBy="user", cascade={"remove", "persist"})
     */
    private $eventsParticipations;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\NotificationBundle\Entity\UserReceivesMailNotifications", mappedBy="user", cascade={"remove", "persist"})
     */
    private $mailNotifications;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\NotificationBundle\Entity\UserReceivesMailNotifications", mappedBy="user", cascade={"remove", "persist"})
     */
    private $reportedDisturbingActivities;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ClubBundle\Entity\TeamCompositionHasUsers", mappedBy="user", cascade={"remove", "persist"})
     */
    private $teamCompositions;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ClubBundle\Entity\TeamHasUsers", mappedBy="user", cascade={"remove", "persist"})
     */
    private $teams;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\UserReadsImportantStatus", mappedBy="user", cascade={"remove", "persist"})
     */
    private $importantStatus;
    
    /**
     * @ORM\OneToMany(targetEntity="\Ks\EventBundle\Entity\Event", mappedBy="user", cascade={"remove", "persist"})
     */
    private $events;
    
    /**
     * @ORM\OneToMany(targetEntity="\Ks\TrophyBundle\Entity\UserWinTrophies", mappedBy="user", cascade={"remove", "persist"})
     */
    private $trophies;
    
    /**
     * @ORM\OneToMany(targetEntity="\Ks\UserBundle\Entity\UserHasPack", mappedBy="user", cascade={"remove", "persist"})
     */
    private $packs;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\MessageBundle\Entity\Message", mappedBy="fromUser", cascade={"remove", "persist"})
     */
    private $sentMessages;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\UserHasToDoChecklistAction", mappedBy="user", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
    private $checklistActions;
    
    /**
     * @ORM\Column(type="boolean")
     * 
     * @var boolean
     */
    private $completedHisProfileRegistration;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\User", inversedBy="godsons")
     */
    private $godFather;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\User", mappedBy="godFather", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
    private $godsons;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\Equipment", mappedBy="user", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     */
    private $equipments;
    
    /**
     * @ORM\OneToOne(targetEntity="Ks\CanvasDrawingBundle\Entity\Character", cascade={"remove", "persist"})
     */
    private $character;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\Netaffiliation", mappedBy="user", cascade={"remove", "persist"})
     */
    private $netaffiliations;

    private $isAllowedPackPremium;
    private $isAllowedPackElite;
    
    /**
     * @ORM\Column(type="string", length=25, name="choosenPack", nullable="true")
     *
     * @var string $choosenPack
     */
    private $choosenPack;
    
    /**
     * @ORM\Column(type="string", length=25, name="choosenWatch", nullable="true")
     *
     * @var string $choosenWatch
     */
    private $choosenWatch;
    
    /**
     * @ORM\Column(type="string", length=25, name="choosenCoach", nullable="true")
     *
     * @var string $choosenCoach
     */
    private $choosenCoach;
    
    /**
     * @ORM\Column(type="string", length=25, name="choosenPackOffer", nullable="true")
     *
     * @var string $choosenPackOffer
     */
    private $choosenPackOffer;
    
    /**
     * @ORM\Column(type="string", length=25, name="choosenWatchOffer", nullable="true")
     *
     * @var string $choosenWatchOffer
     */
    private $choosenWatchOffer;
    
    public function __construct()
    {
        parent::__construct();
        $this->myFriends                = new \Doctrine\Common\Collections\ArrayCollection();
        $this->friendsWithMe            = new \Doctrine\Common\Collections\ArrayCollection();
        $this->myVotes                  = new \Doctrine\Common\Collections\ArrayCollection();
        $this->mySubscriptions          = new \Doctrine\Common\Collections\ArrayCollection();
        $this->activitiesIHaveHidden    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->myPoints                 = new \Doctrine\Common\Collections\ArrayCollection();
        $this->myInvitations            = new \Doctrine\Common\Collections\ArrayCollection();
        $this->trophies                 = new \Doctrine\Common\Collections\ArrayCollection();
        $this->packs                    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->mySeasons                = new \Doctrine\Common\Collections\ArrayCollection();
        $this->inscribedAt              = new \DateTime();
        $this->myClubsManaged           = new \Doctrine\Common\Collections\ArrayCollection();
        $this->myArticles               = new \Doctrine\Common\Collections\ArrayCollection();
        $this->feedbacks                = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sportEventsRegistrations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->mailNotifications        = new \Doctrine\Common\Collections\ArrayCollection();
        $this->clubs                    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->teams                    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->teamCompositions         = new \Doctrine\Common\Collections\ArrayCollection();
        $this->eventsParticipations     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sentMessages             = new \Doctrine\Common\Collections\ArrayCollection();
        $this->checklistActions         = new \Doctrine\Common\Collections\ArrayCollection();
        $this->godFather                = null;
        $this->godsons                  = new \Doctrine\Common\Collections\ArrayCollection();
        $this->equipments               = new \Doctrine\Common\Collections\ArrayCollection();
        $this->character                = new \Ks\CanvasDrawingBundle\Entity\Character(); 
        //$this->showcase                 = new \Ks\TrophyBundle\Entity\Showcase(); Traitement dans le registration controlleur logiquement
        
        $this->completedHisProfileRegistration = false;
        
        $this->isAllowedPackPremium     = 1;
        $this->isAllowedPackElite       = 1;
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
     * Add myFriends
     *
     * @param Ks\UserBundle\Entity\UserHasFriends $myFriends
     */
    public function addUserHasFriends(\Ks\UserBundle\Entity\UserHasFriends $myFriends)
    {
        $this->myFriends[] = $myFriends;
        $this->friendsWithMe[] = $myFriends;
    }
    
     /**
     * Add myInvited
     *
     * @param Ks\UserBundle\Entity\UserHasInvited $myInvited
     */
    public function addUserHasInvited(\Ks\UserBundle\Entity\UserHasInvited $myInvited)
    {
        $this->myInvited[] = $myInvited;
        $this->InvitedWithMe[] = $myInvited;
    }
    
    /**
     * Remove myFriends
     *
     * @param Ks\UserBundle\Entity\UserHasFriends $myFriends
     */
    public function removeUserHasFriends(\Ks\UserBundle\Entity\UserHasFriends $myFriends)
    {
        $removeSucces = false;
        $removeSucces = $this->myFriends->removeElement($myFriends);
        
        if (! $removeSucces ) {
            $removeSucces = $this->friendsWithMe->removeElement($myFriends);
        }
        //$myFriends->removeUser($this);
        //$myFriends->
        //$this->friendsWithMe->r
        return $removeSucces;
    }

    /**
     * Get myFriends
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMyFriends()
    {
        return $this->myFriends;
    }

    /**
     * Get friendsWithMe
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getFriendsWithMe()
    {
        return $this->friendsWithMe;
    }

    /**
     * Add notifications
     *
     * @param Ks\NotificationBundle\Entity\Notification $notifications
     */
    public function addNotification(\Ks\NotificationBundle\Entity\Notification $notifications)
    {
        $this->notifications[] = $notifications;
    }

    /**
     * Get notifications
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Get myNotifications
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMyNotifications()
    {
        return $this->myNotifications;
    }

    /**
     * Get notificationsFromMe
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getNotificationsFromMe()
    {
        return $this->notificationsFromMe;
    }

    /**
     * Add myActivities
     *
     * @param Ks\ActivityBundle\Entity\Activity $myActivities
     */
    public function addActivity(\Ks\ActivityBundle\Entity\Activity $myActivities)
    {
        $this->myActivities[] = $myActivities;
    }

    /**
     * Get myActivities
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMyActivities()
    {
        return $this->myActivities;
    }
    

    /**
     * Add myComments
     *
     * @param Ks\ActivityBundle\Entity\Comment $myComments
     */
    public function addComment(\Ks\ActivityBundle\Entity\Comment $myComments)
    {
        $this->myComments[] = $myComments;
    }

    /**
     * Get myComments
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMyComments()
    {
        return $this->myComments;
    }
    
     // On définit le getter et le setter associé.
    public function getUserDetail()
    {
        return $this->userDetail;
    }

    // Ici, on force le type de l'argument à être une instance de notre entité Adresse.
    public function setUserDetail(\Ks\UserBundle\Entity\UserDetail $userDetail)
    {
        $this->userDetail = $userDetail;
    }

    /**
     * Add myVotes
     *
     * @param Ks\ActivityBundle\Entity\ActivityHasVotes $myVotes
     */
    public function addActivityHasVotes(\Ks\ActivityBundle\Entity\ActivityHasVotes $myVotes)
    {
        $this->myVotes[] = $myVotes;
    }

    /**
     * Get myVotes
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMyVotes()
    {
        return $this->myVotes;
    }

    /**
     * Add mySubscriptions
     *
     * @param Ks\ActivityBundle\Entity\ActivityHasSubscribers $mySubscriptions
     */
    public function addActivityHasSubscribers(\Ks\ActivityBundle\Entity\ActivityHasSubscribers $mySubscriptions)
    {
        $this->mySubscriptions[] = $mySubscriptions;
    }
    
    /**
     * Remove subscribers
     *
     * @param Ks\ActivityBundle\Entity\ActivityHasSubscribers $subscribers
     */
    public function removeActivityHasSubscribers(\Ks\ActivityBundle\Entity\ActivityHasSubscribers $mySubscriptions)
    {
        $removeSucces = false;
        $removeSucces = $this->mySubscriptions->removeElement($mySubscriptions);

        return $removeSucces;
    }

    /**
     * Get mySubscriptions
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMySubscriptions()
    {
        return $this->mySubscriptions;
    }

    public function serialize()
    {
        return serialize(array($this->facebookId, parent::serialize()));
    }

    public function unserialize($data)
    {
        list($this->facebookId, $parentData) = unserialize($data);
        parent::unserialize($parentData);
    }
    
    public function __sleep()
    {
        return array('username'); // add your own fields
    }
   
    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
        * @param string $firstname
        */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
        * @return string
        */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
        * Get the full name of the user (first + last name)
        * @return string
        */
    public function getFullName()
    {
        return $this->getFirstName() . ' ' . $this->getLastname();
    }

    /**
        * @param string $facebookId
        * @return void
        */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
        //$this->setUsername($facebookId);
        // $this->salt = ''; // NOTE CF: désactivé pour la beta: mais pourquoi on faisait ça ?
    }

    /**
        * @return string
        */
    public function getFacebookId()
    {
        return $this->facebookId;
    }
    
    public function setGoogleId( $googleId )
    {
        $this->googleId = $googleId;
    }

    public function getGoogleId( )
    {
        return $this->googleId;
    }


    /**
        * @param Array
        */
    public function setFBData($fbdata, $sexe, $userDetail, $aGeoloc)
    {
        /*Ajout des informations dans la table user ici */
        
        if (isset($fbdata['id'])) {
            $this->setFacebookId($fbdata['id']);
            $this->addRole('ROLE_USER');
        }

        $firstname      = "";
        $lastname       = "";
        $bio            = "";
        $locale         = "";
        $birthday       = "";
        $town           = "";
        $countryarea    = ""; 
        $latitude       = ""; 
        $longitude      = ""; 
        $picture        = "";
        $fullAdress     = "";
        
        
        if ($userDetail == null){
            $userDetail = new UserDetail();
        } else {
            $firstname  = $userDetail->getFirstname();
            $lastname   = $userDetail->getLastname();
            $bio        = $userDetail->getDescription();
            $locale     = $userDetail->getCountryCode();
            $birthday   = $userDetail->getBornedAt();
            $town       = $userDetail->getTown();
            $countryarea = $userDetail->getCountryArea();
            $latitude   = $userDetail->getLatitude();
            $longitude  = $userDetail->getLongitude();
            $picture    = $userDetail->getUrlAvatarFacebook();
            $fullAdress = $userDetail->getFullAddress();
        }

        //Détail utilisateurs 
        if (isset($fbdata['first_name']) && $firstname == "") {
            $userDetail->setFirstname($fbdata['first_name']);
        }

        if (isset($fbdata['last_name']) && $lastname == "" ) {
            $userDetail->setLastname($fbdata['last_name']);
        }
        
        if ($sexe != null) {
            $userDetail->setSexe($sexe);
        }

        if (isset($fbdata['locale']) && $locale == "" ) {
            $userDetail->setCountryCode($fbdata['locale']);
        }
        
        //La récupération de la bio ne se fait pas ???
        if (isset($fbdata['bio']) && $bio == "" ) {
            $userDetail->setDescription($fbdata['bio']['friends_about_me']);
        }
        
       
        if (isset($fbdata['birthday']) && $birthday == "") {
            $dateFormated = date("Y-m-d", strtotime($fbdata['birthday']));
            $dateFormated = new \DateTime($dateFormated);
            $userDetail->setBornedAt($dateFormated);
        }
        
        if (!empty($aGeoloc)){
            if($aGeoloc["town"] && $town==""){
                $userDetail->setTown($aGeoloc["town"]);
            }
            
            if($aGeoloc["country_area"] && $countryarea==""){
                $userDetail->setRegionLabel($aGeoloc["country_area"]);
            }
            
            if($aGeoloc["latitude"] && $latitude==""){
                $userDetail->setLatitude($aGeoloc["latitude"]);
            }
            
            if($aGeoloc["longitude"] && $longitude==""){
                $userDetail->setLongitude($aGeoloc["longitude"]);
            }
        }
        
        if (isset($fbdata['location']['name']) && $fullAdress=="" ) {
           $userDetail->setFullAddress($fbdata['location']['name']); 
        }
        
        
        
        //http://graph.facebook.com/laurent.masforne/picture
        if (isset($fbdata['username']) && $picture == "") {
            $userDetail->setUrlAvatarFacebook("http://graph.facebook.com/".$fbdata['username']."/picture");
        }
        
        $this->setUserDetail($userDetail);
    }

    /**
     * Add activitiesIHaveHidden
     *
     * @param Ks\ActivityBundle\Entity\Activity $activity
     */
    public function addActivitiesIHaveHidden(\Ks\ActivityBundle\Entity\Activity $activity)
    {
        $this->activitiesIHaveHidden[] = $activity;
    }
    
    /**
     * Get activitiesIHaveHidden
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getActivitiesIHaveHidden()
    {
        return $this->activitiesIHaveHidden;
    }

    /**
     * Get myInvited
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMyInvited()
    {
        return $this->myInvited;
    }

    /**
     * Get InvitedByMe
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getInvitedByMe()
    {
        return $this->InvitedByMe;
    }

    /**
     * Add myPoints
     *
     * @param Ks\ActivityBundle\Entity\ActivitySessionEarnsPoints $myPoints
     */
    public function addActivitySessionEarnsPoints(\Ks\ActivityBundle\Entity\ActivitySessionEarnsPoints $myPoints)
    {
        $this->myPoints[] = $myPoints;
    }

    /**
     * Get myPoints
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMyPoints()
    {
        return $this->myPoints;
    }

    /**
     * Set leagueLevel
     *
     * @param Ks\LeagueBundle\Entity\LeagueLevel $leagueLevel
     */
    public function setLeagueLevel(\Ks\LeagueBundle\Entity\LeagueLevel $leagueLevel)
    {
        $this->leagueLevel = $leagueLevel;
    }

    /**
     * Get leagueLevel
     *
     * @return Ks\LeagueBundle\Entity\LeagueLevel 
     */
    public function getLeagueLevel()
    {
        return $this->leagueLevel;
    }

    /**
     * Set showcase
     *
     * @param Ks\TrophyBundle\Entity\Showcase $showcase
     */
    public function setShowcase(\Ks\TrophyBundle\Entity\Showcase $showcase)
    {
        $this->showcase = $showcase;
    }

    /**
     * Get showcase
     *
     * @return Ks\TrophyBundle\Entity\Showcase 
     */
    public function getShowcase()
    {
        return $this->showcase;
    }

    /**
     * Set member
     *
     * @param Ks\ClubBundle\Entity\Member $member
     */
    public function setMember(\Ks\ClubBundle\Entity\Member $member)
    {
        $this->member = $member;
    }

    /**
     * Get member
     *
     * @return Ks\ClubBundle\Entity\Member 
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Add myClubsManaged
     *
     * @param Ks\ClubBundle\Entity\ClubHasUsers $myClubsManaged
     */
    public function addUserManageClub(\Ks\ClubBundle\Entity\ClubHasUsers $myClubsManaged)
    {
        $this->myClubsManaged[] = $myClubsManaged;
    }

    /**
     * Get myClubsManaged
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMyClubsManaged()
    {
        return $this->myClubsManaged;
    }
    
      /**
     * @return string The salt.
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param string $value The salt.
     */
    public function setSalt($value)
    {
        $this->salt = $value;
    }
    


    /**
     * Set inscribedAt
     *
     * @param datetime $inscribedAt
     */
    public function setInscribedAt($inscribedAt)
    {
        $this->inscribedAt = $inscribedAt;
    }

    /**
     * Get inscribedAt
     *
     * @return datetime 
     */
    public function getInscribedAt()
    {
        return $this->inscribedAt;
    }


    /**
     * Set agenda
     *
     * @param Ks\AgendaBundle\Entity\Agenda $agenda
     */
    public function setAgenda(\Ks\AgendaBundle\Entity\Agenda $agenda)
    {
        $this->agenda = $agenda;
    }

    /**
     * Get agenda
     *
     * @return Ks\AgendaBundle\Entity\Agenda 
     */
    public function getAgenda()
    {
        return $this->agenda;
    }


    /**
     * Add services
     *
     * @param Ks\UserBundle\Entity\UserHasServices $services
     */
    public function addUserHasServices(\Ks\UserBundle\Entity\UserHasServices $services)
    {
        $this->services[] = $services;
    }

    /**
     * Get services
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Add userInvitingEvent
     *
     * @param Ks\EventBundle\Entity\InvitationEvent $userInvitingEvent
     */
    public function addInvitationEvent(\Ks\EventBundle\Entity\InvitationEvent $userInvitingEvent)
    {
        $this->userInvitingEvent[] = $userInvitingEvent;
    }

    /**
     * Get userInvitingEvent
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUserInvitingEvent()
    {
        return $this->userInvitingEvent;
    }
    
    /**
     * Add feedbacks
     *
     * @param Ks\UserBundle\Entity\Feedback $feedbacks
     */
    public function addFeedback(\Ks\UserBundle\Entity\Feedback $feedbacks)
    {
        $this->feedbacks[] = $feedbacks;
    }

    /**
     * Get feedbacks
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getFeedbacks()
    {
        return $this->feedbacks;
    }



    /**
     * Add articleSportingEventsParticipations
     *
     * @param Ks\ActivityBundle\Entity\Article $articleSportingEventsParticipations
     */
    public function addArticle(\Ks\ActivityBundle\Entity\Article $articleSportingEventsParticipations)
    {
        $this->articleSportingEventsParticipations[] = $articleSportingEventsParticipations;
    }

    /**
     * Get articleSportingEventsParticipations
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getArticleSportingEventsParticipations()
    {
        return $this->articleSportingEventsParticipations;
    }
    
    public function removeArticleSportingEventParticipation(\Ks\ActivityBundle\Entity\Article $articleSportingEventsParticipations)
    {
        $removeSucces = false;
        $removeSucces = $this->articleSportingEventsParticipations->removeElement($articleSportingEventsParticipations);

        return $removeSucces;
    }



    /**
     * Add mailNotifications
     *
     * @param Ks\NotificationBundle\Entity\UserReceivesMailNotifications $mailNotifications
     */
    public function addUserReceivesMailNotifications(\Ks\NotificationBundle\Entity\UserReceivesMailNotifications $mailNotifications)
    {
        $this->mailNotifications[] = $mailNotifications;
    }

    /**
     * Get mailNotifications
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMailNotifications()
    {
        return $this->mailNotifications;
    }

    /**
     * Get reportedDisturbingActivities
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getReportedDisturbingActivities()
    {
        return $this->reportedDisturbingActivities;
    }

    /**
     * Add clubs
     *
     * @param Ks\ClubBundle\Entity\ClubHasUsers $clubs
     */
    public function addClubHasUsers(\Ks\ClubBundle\Entity\ClubHasUsers $clubs)
    {
        $this->clubs[] = $clubs;
    }

    /**
     * Get clubs
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getClubs()
    {
        return $this->clubs;
    }

    /**
     * Add teamCompositions
     *
     * @param Ks\ClubBundle\Entity\TeamCompositionHasUsers $teamCompositions
     */
    public function addTeamCompositionHasUsers(\Ks\ClubBundle\Entity\TeamCompositionHasUsers $teamCompositions)
    {
        $this->teamCompositions[] = $teamCompositions;
    }

    /**
     * Get teamCompositions
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTeamCompositions()
    {
        return $this->teamCompositions;
    }

    /**
     * Add teams
     *
     * @param Ks\ClubBundle\Entity\TeamHasUsers $teams
     */
    public function addTeamHasUsers(\Ks\ClubBundle\Entity\TeamHasUsers $teams)
    {
        $this->teams[] = $teams;
    }

    /**
     * Get teams
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTeams()
    {
        return $this->teams;
    }

    /**
     * Add importantStatus
     *
     * @param Ks\ActivityBundle\Entity\UserReadsImportantStatus $importantStatus
     */
    public function addUserReadsImportantStatus(\Ks\ActivityBundle\Entity\UserReadsImportantStatus $importantStatus)
    {
        $this->importantStatus[] = $importantStatus;
    }

    /**
     * Get importantStatus
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getImportantStatus()
    {
        return $this->importantStatus;
    }

    /**
     * Add events
     *
     * @param Ks\EventBundle\Entity\Event $events
     */
    public function addEvent(\Ks\EventBundle\Entity\Event $events)
    {
        $this->events[] = $events;
    }

    /**
     * Get events
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Add trophies
     *
     * @param Ks\TrophyBundle\Entity\UserWinTrophies $trophies
     */
    public function addUserWinTrophies(\Ks\TrophyBundle\Entity\UserWinTrophies $trophies)
    {
        $this->trophies[] = $trophies;
    }

    /**
     * Get trophies
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTrophies()
    {
        return $this->trophies;
    }
    
    /**
     * Add packs
     *
     * @param Ks\UserBundle\Entity\UserHasPack $pack
     */
    public function addUserHasPack(\Ks\UserBundle\Entity\UserHasPack $pack)
    {
        $this->packs[] = $pack;
    }

    /**
     * Get packs
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPacks()
    {
        return $this->packs;
    }

    /**
     * Add eventsParticipations
     *
     * @param Ks\EventBundle\Entity\UserParticipatesEvent $eventsParticipations
     */
    public function addUserParticipatesEvent(\Ks\EventBundle\Entity\UserParticipatesEvent $eventsParticipations)
    {
        $this->eventsParticipations[] = $eventsParticipations;
    }

    /**
     * Get eventsParticipations
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getEventsParticipations()
    {
        return $this->eventsParticipations;
    }

    /**
     * Add sentMessages
     *
     * @param Ks\MessageBundle\Entity\Message $sentMessages
     */
    public function addMessage(\Ks\MessageBundle\Entity\Message $sentMessages)
    {
        $this->sentMessages[] = $sentMessages;
    }

    /**
     * Get sentMessages
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getSentMessages()
    {
        return $this->sentMessages;
    }

    /**
     * Add checklistActions
     *
     * @param Ks\UserBundle\Entity\UserHasToDoChecklistAction $checklistActions
     */
    public function addUserHasToDoChecklistAction(\Ks\UserBundle\Entity\UserHasToDoChecklistAction $checklistActions)
    {
        $this->checklistActions[] = $checklistActions;
    }

    /**
     * Get checklistActions
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getChecklistActions()
    {
        return $this->checklistActions;
    }

    /**
     * Set completedHisProfileRegistration
     *
     * @param boolean $completedHisProfileRegistration
     */
    public function setCompletedHisProfileRegistration($completedHisProfileRegistration)
    {
        $this->completedHisProfileRegistration = $completedHisProfileRegistration;
    }

    /**
     * Get completedHisProfileRegistration
     *
     * @return boolean 
     */
    public function getCompletedHisProfileRegistration()
    {
        return $this->completedHisProfileRegistration;
    }
    
        /**
     * @return string
     */
    public function getProximite()
    {
        return $this->proximite;
    }

    /**
        * @param string $firstname
        */
    public function setProximite($proximite)
    {
        $this->proximite = $proximite;
    }

    /**
     * Set godFather
     *
     * @param Ks\UserBundle\Entity\User $godFather
     */
    public function setGodFather( $godFather)
    {
        $this->godFather = $godFather;
    }

    /**
     * Get godFather
     *
     * @return Ks\UserBundle\Entity\User 
     */
    public function getGodFather()
    {
        return $this->godFather;
    }

    /**
     * Add godsons
     *
     * @param Ks\UserBundle\Entity\User $godsons
     */
    public function addUser(\Ks\UserBundle\Entity\User $godsons)
    {
        $this->godsons[] = $godsons;
    }

    /**
     * Get godsons
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getGodsons()
    {
        return $this->godsons;
    }
    
    /**
     *
     * @return type 
     */
    public function __toString(){
        
        if ( $this->getUserDetail() != null && $this->getUserDetail()->getFirstname() != null && $this->getUserDetail()->getLastname() != null ) {
            return $this->getUsername() . " (" . $this->getUserDetail()->getFirstname() . " " . $this->getUserDetail()->getLastname() .")";
        } else {
            return $this->getUsername();
        }
    } 

    /**
     * Add leaguesHistorics
     *
     * @param Ks\LeagueBundle\Entity\Historic $leaguesHistorics
     */
    public function addHistoric(\Ks\LeagueBundle\Entity\Historic $leaguesHistorics)
    {
        $this->leaguesHistorics[] = $leaguesHistorics;
    }

    /**
     * Get leaguesHistorics
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getLeaguesHistorics()
    {
        return $this->leaguesHistorics;
    }

    /**
     * Add equipments
     *
     * @param Ks\UserBundle\Entity\Equipment $equipments
     */
    public function addEquipment(\Ks\UserBundle\Entity\Equipment $equipment)
    {
        $equipment->setUser($this);
        $this->equipments[] = $equipment;
    }
    
    public function removeEquipment(\Ks\UserBundle\Entity\Equipment $equipment)
    {
        $this->equipments->removeElement($equipment);
    }

    /**
     * Get equipments
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getEquipments()
    {
        return $this->equipments;
    }
    
    public function setEquipments(\Doctrine\Common\Collections\Collection $equipments)
    {
        foreach($equipments as $equipment) {
            $this->addEquipment($equipment);
        }
    }

    /**
     * Set character
     *
     * @param Ks\CanvasDrawingBundle\Entity\Character $character
     */
    public function setCharacter(\Ks\CanvasDrawingBundle\Entity\Character $character)
    {
        $this->character = $character;
    }

    /**
     * Get character
     *
     * @return Ks\CanvasDrawingBundle\Entity\Character 
     */
    public function getCharacter()
    {
        return $this->character;
    }
    
    public function removeNetAffiliation(\Ks\UserBundle\Entity\Netaffiliation $netaffiliation)
    {
        $this->netaffiliations->removeElement($netaffiliation);
    }
    
    public function setNetAffiliations(\Doctrine\Common\Collections\Collection $netaffiliations)
    {
        foreach($netaffiliations as $netaffiliation) {
            $this->addNetAffiliation($netaffiliation);
        }
    }

    /**
     * Get netaffiliations
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getNetaffiliations()
    {
        return $this->netaffiliations;
    }

    /**
     * Add netaffiliations
     *
     * @param Ks\UserBundle\Entity\Netaffiliation $netaffiliations
     */
    public function addNetaffiliation(\Ks\UserBundle\Entity\Netaffiliation $netaffiliations)
    {
        $this->netaffiliations[] = $netaffiliations;
    }
    
    public function getIsAllowedPackPremium()
    {
        if ( $this->getUserDetail() != null && $this->getUserDetail()) {
            $userPacks = $this->packs;
            $isAllowedPackPremium = false;
            $now = new \DateTime();
            foreach($userPacks->toArray() as $userHasPack) {
                $pack = $userHasPack->getPack();
                if ($pack->getCode() == 'premium') {
                    if ($userHasPack->getStartDate()->format("y-m-d") <= $now->format("y-m-d") && (is_null($userHasPack->getEndDate()) ||
                        !is_null($userHasPack->getEndDate()) && $userHasPack->getEndDate()->format("y-m-d") >= $now->format("y-m-d"))) $isAllowedPackPremium = true;
                }
                
            }
            return $isAllowedPackPremium;
        } else {
            return false;
        }
    }
    
    public function getIsAllowedPackElite()
    {
        if ( $this->getUserDetail() != null && $this->getUserDetail()) {
            $userPacks = $this->packs;
            $isAllowedPackElite = false;
            $now = new \DateTime();
            foreach($userPacks->toArray() as $userHasPack) {
                $pack = $userHasPack->getPack();
                if ($pack->getCode() == 'elite') {
                    if ($userHasPack->getStartDate()->format("y-m-d") <= $now->format("y-m-d") && (is_null($userHasPack->getEndDate()) ||
                        !is_null($userHasPack->getEndDate()) && $userHasPack->getEndDate()->format("y-m-d") >= $now->format("y-m-d"))) $isAllowedPackElite = true;
                }
            }
            return $isAllowedPackElite;
        } else {
            return false;
        }
    }
    
    public function setChoosenPack($choosenPack) {$this->choosenPack = $choosenPack;}
    public function getChoosenPack() {return $this->choosenPack;}
    
    public function setChoosenWatch($choosenWatch) {$this->choosenWatch = $choosenWatch;}
    public function getChoosenWatch() {return $this->choosenWatch;}
    
    public function setChoosenCoach($choosenCoach) {$this->choosenCoach = $choosenCoach;}
    public function getChoosenCoach() {return $this->choosenCoach;}
    
    public function setChoosenPackOffer($choosenPackOffer) {$this->choosenPackOffer = $choosenPackOffer;}
    public function getChoosenPackOffer() {return $this->choosenPackOffer;}
    
    public function setChoosenWatchOffer($choosenWatchOffer) {$this->choosenWatchOffer = $choosenWatchOffer;}
    public function getChoosenWatchOffer() {return $this->choosenWatchOffer;}
}