<?php

namespace Ks\ClubBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/*use Vich\UploaderBundle\Mapping\Annotation as Vich;

use Symfony\Component\HttpFoundation\File\File;*/

/**
 * Ks\ClubBundle\Entity\Club
 * 
 * @ORM\Table(name="ks_club")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Ks\ClubBundle\Entity\ClubRepository")
 */
class Club
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var string $country_area
     *
     * @ORM\Column(name="country_area", type="string", length=100,nullable="true")
     */
    private $country_area;

    /**
     * @var string $longitude
     *
     * @ORM\Column(name="longitude", type="string", length=255,nullable="true")
     */
    private $longitude;

    /**
     * @var string $latitude
     *
     * @ORM\Column(name="latitude", type="string", length=255,nullable="true")
     */
    private $latitude;

    /**
     * @var string $country_code
     *
     * @ORM\Column(name="country_code", type="string", length=2,nullable="true")
     */
    private $country_code;
    
      /**
     * @var decimal $town
     *
     * @ORM\Column(name="town", type="string", length=45, nullable=true,nullable="true")
     */
    private $town;

    /**
     * @var text $adress_name
     *
     * @ORM\Column(name="adress_name", type="text",nullable="true")
     */
    private $adress_name;

    /**
     * @var string $tel_number
     *
     * @ORM\Column(name="tel_number", type="string", length=15,nullable="true")
     */
    private $tel_number;

    /**
     * @var string $mobile_number
     *
     * @ORM\Column(name="mobile_number", type="string", length=15,nullable="true")
     */
    private $mobile_number;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255,nullable="true")
     */
    private $email;
    
    /**
     * @var string $url_site_web
     *
     * @ORM\Column(name="url_site_web", type="string", length=255,nullable="true")
     */
    private $url_site_web;
     
     /**
     * @ORM\ManyToMany(targetEntity="\Ks\ActivityBundle\Entity\Sport")
     * @ORM\JoinTable(name="ks_club_has_sport")
     */
    private $sports;
    
     /**
     * @ORM\OneToMany(targetEntity="\Ks\EventBundle\Entity\Event", mappedBy="club", cascade={"remove", "persist"})
     */
    private $events;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ClubBundle\Entity\UserManageClub", mappedBy="club", cascade={"remove", "persist"})
     */
    private $managers;
    
     /*
     * @ORM\OneToMany(targetEntity="Ks\ClubBundle\Entity\ClubHasMembers", mappedBy="club", cascade={"remove", "persist"})
     
    private $members;*/
    
    /**
     * @ORM\OneToMany(targetEntity="\Ks\ClubBundle\Entity\Team", mappedBy="club", cascade={"remove", "persist"})
     */
    private $teams;
    
     /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatar; 
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ActivityBundle\Entity\Activity", mappedBy="club", cascade={"remove", "persist"})
     */
    private $activities;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\ClubBundle\Entity\ClubHasUsers", mappedBy="club", cascade={"remove", "persist"})
     */
    private $users;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\NotificationBundle\Entity\Notification", mappedBy="fromUser", cascade={"remove", "persist"})
     */
    private $notificationsFromMe;
    
    /**
     * @ORM\ManyToMany(targetEntity="\Ks\UserBundle\Entity\User")
     * @ORM\JoinTable(name="ks_club_has_president")
     */
    private $presidents;
    
    /**
     * @ORM\OneToOne(targetEntity="Ks\AgendaBundle\Entity\Agenda", cascade={"remove", "persist"})
     */
    private $agenda;
    
    /**
     * @ORM\OneToMany(targetEntity="Ks\TournamentBundle\Entity\Tournament", mappedBy="club", cascade={"remove", "persist"})
     */
    protected $tournaments;
    
    /**
     * @var integer $delayWarning
     *
     * @ORM\Column(name="delayWarning", type="integer", nullable=true, options={"default" = 7})
     */
    private $delayWarning;
    
    /**
     * @var integer $isCoach
     *
     * @ORM\Column(name="isCoach", type="integer", nullable=true, options={"default" = 0})
     */
    private $isCoach;
    

    public function __construct()
    {
        $this->sports               = new \Doctrine\Common\Collections\ArrayCollection;
        $this->events               = new \Doctrine\Common\Collections\ArrayCollection;
        $this->activities           = new \Doctrine\Common\Collections\ArrayCollection;
        $this->users                = new \Doctrine\Common\Collections\ArrayCollection;
        $this->notificationsFromMe  = new \Doctrine\Common\Collections\ArrayCollection;
        $this->presidents           = new \Doctrine\Common\Collections\ArrayCollection;
        $this->tournaments           = new \Doctrine\Common\Collections\ArrayCollection;
        $this->agenda               = new \Ks\AgendaBundle\Entity\Agenda();
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
     * Get $delayWarning
     *
     * @return integer 
     */
    public function getDelayWarning()
    {
        return $this->delayWarning;
    }
    
    /**
     * Set $delayWarning
     *
     * @return integer $delayWarning
     */
    public function setId($delayWarning)
    {
        return $this->id = $delayWarning;
    }
    
    /**
     * Get $isCoach
     *
     * @return integer 
     */
    public function getIsCoach()
    {
        return $this->isCoach;
    }
    
    /**
     * Set $isCoach
     *
     * @return integer $isCoach
     */
    public function setIsCoach($isCoach)
    {
        return $this->isCoach = $isCoach;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
     /**
     * Set url_site_web
     *
     * @param string $url_site_web
     */
    public function setUrlSiteWeb($url_site_web)
    {
        $this->url_site_web = $url_site_web;
    }

    /**
     * Get url_site_web
     *
     * @return string 
     */
    public function getUrlSiteWeb()
    {
        return $this->url_site_web;
    }

    /**
     * Set country_area
     *
     * @param string $countryArea
     */
    public function setCountryArea($countryArea)
    {
        $this->country_area = $countryArea;
    }

    /**
     * Get country_area
     *
     * @return string 
     */
    public function getCountryArea()
    {
        return $this->country_area;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * Get longitude
     *
     * @return string 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * Get latitude
     *
     * @return string 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set country_code
     *
     * @param string $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->country_code = $countryCode;
    }

    /**
     * Get country_code
     *
     * @return string 
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }
    
    
     /**
     * Set town
     *
     * @param string $town
     */
    public function setTown($town)
    {
        $this->town = $town;
    }

    /**
     * Get town
     *
     * @return string 
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * Set adress_name
     *
     * @param text $adressName
     */
    public function setAdressName($adressName)
    {
        $this->adress_name = $adressName;
    }

    /**
     * Get adress_name
     *
     * @return text 
     */
    public function getAdressName()
    {
        return $this->adress_name;
    }

    /**
     * Set tel_number
     *
     * @param string $telNumber
     */
    public function setTelNumber($telNumber)
    {
        $this->tel_number = $telNumber;
    }

    /**
     * Get tel_number
     *
     * @return string 
     */
    public function getTelNumber()
    {
        return $this->tel_number;
    }

    /**
     * Set mobile_number
     *
     * @param string $mobileNumber
     */
    public function setMobileNumber($mobileNumber)
    {
        $this->mobile_number = $mobileNumber;
    }

    /**
     * Get mobile_number
     *
     * @return string 
     */
    public function getMobileNumber()
    {
        return $this->mobile_number;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Set sport
     *
     * @param \Ks\ActivityBundle\Entity\Sport $sport
     */
    public function setSport(\Ks\ActivityBundle\Entity\Sport $sport)
    {
        $this->sports[] = $sport;
    }

    /**
     * Get sport
     *
     * @return Ks\ActivityBundle\Entity\Sport 
     */
    public function getSports()
    {
        return $this->sports;
    }
    
     /**
     * Set event
     *
     * @param \Ks\EventBundle\Entity\Event $event
     */
    public function setEvent(\Ks\EventBundle\Entity\Event $event)
    {
        $this->events[] = $events;
    }

    /**
     * Get events
     *
     * @return \Ks\EventBundle\Entity\Event 
     */
    public function getEvents()
    {
        return $this->events;
    }
    
     /**
     * Set $teams
     *
     * @param \Ks\ClubBundle\Entity\Team $teams
     */
    public function setTeams(\Ks\ClubBundle\Entity\Team $teams)
    {
        $this->teams[] = $teams;
    }

    /**
     * Get $teams
     *
     * @return \Ks\ClubBundle\Entity\Team
     */
    public function getTeams()
    {
        return $this->teams;
    }
    
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Add sports
     *
     * @param Ks\ActivityBundle\Entity\Sport $sports
     */
    public function addSport(\Ks\ActivityBundle\Entity\Sport $sports)
    {
        $this->sports[] = $sports;
    }

    /**
     * Add events
     *
     * @param \Ks\EventBundle\Entity\Event $events
     */
    public function addEvent(\Ks\EventBundle\Entity\Event $events)
    {
        $this->events[] = $events;
    }


    /**
     * Add teams
     *
     * @param \Ks\ClubBundle\Entity\Team $teams
     */
    public function addTeam(\Ks\ClubBundle\Entity\Team $teams)
    {
        $this->teams[] = $teams;
    }

    /**
     * Add managers
     *
     * @param Ks\ClubBundle\Entity\UserManageClub $managers
     */
    public function addUserManageClub(\Ks\ClubBundle\Entity\UserManageClub $managers)
    {
        $this->managers[] = $managers;
    }

    /**
     * Get managers
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getManagers()
    {
        return $this->managers;
    }



    /**
     * Add members
     *
     * @param Ks\ClubBundle\Entity\ClubHasMembers $members
     */
    public function addClubHasMembers(\Ks\ClubBundle\Entity\ClubHasMembers $members)
    {
        $this->members[] = $members;
    }

    /**
     * Get members
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Add activities
     *
     * @param Ks\ActivityBundle\Entity\Activity $activities
     */
    public function addActivity(\Ks\ActivityBundle\Entity\Activity $activities)
    {
        $this->activities[] = $activities;
    }

    /**
     * Get activities
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * Add users
     *
     * @param Ks\ClubBundle\Entity\ClubHasUsers $users
     */
    public function addClubHasUsers(\Ks\ClubBundle\Entity\ClubHasUsers $users)
    {
        $this->users[] = $users;
    }

    /**
     * Get users
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add notificationsFromMe
     *
     * @param Ks\NotificationBundle\Entity\Notification $notificationsFromMe
     */
    public function addNotification(\Ks\NotificationBundle\Entity\Notification $notificationsFromMe)
    {
        $this->notificationsFromMe[] = $notificationsFromMe;
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
     * Add presidents
     *
     * @param Ks\UserBundle\Entity\User $presidents
     */
    public function addPresident(\Ks\UserBundle\Entity\User $presidents)
    {
        $this->presidents[] = $presidents;
    }

    /**
     * Get presidents
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPresidents()
    {
        return $this->presidents;
    }
    
    /**
     * Add presidents
     *
     * @param Ks\UserBundle\Entity\User $presidents
     */
    public function addUser(\Ks\UserBundle\Entity\User $presidents)
    {
        $this->presidents[] = $presidents;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }
    
    public function getFullAvatarPath() {
        return null === $this->avatar ? null : $this->getUploadRootDir(). $this->avatar;
    }
    
    public function getResizeAvatarPath() {
        return null === $this->avatar ? null : $this->getResizeRootDir(). $this->avatar;
    }
    
    protected function getUploadRootDir() {
        // the absolute directory path where uploaded documents should be saved
        return $this->getClubUploadRootDir()."original/";
    }
    
    protected function getResizeRootDir() {
        // the absolute directory path where uploaded documents should be saved
        return $this->getClubUploadRootDir()."resize_48x48/";
    }
    
    protected function getClubUploadRootDir() {
        // the absolute directory path where uploaded documents should be saved
        return $this->getTmpUploadRootDir().$this->getId()."/";
    }

    protected function getTmpUploadRootDir() {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__ . '/../../../../web/uploads/images/clubs/';
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
    
    public function uploadAvatar() {
        // the file property can be empty if the field is not required
        if (null === $this->avatar) {
            return;
        }
        
        if(!is_dir($this->getTmpUploadRootDir())){
            mkdir($this->getTmpUploadRootDir());
        }
        
        if(!is_dir($this->getClubUploadRootDir())){
            mkdir($this->getClubUploadRootDir());
        }
        
        if(!is_dir($this->getUploadRootDir())){
            mkdir($this->getUploadRootDir());
        }
        
        if(!is_dir($this->getResizeRootDir())){
            mkdir($this->getResizeRootDir());
        }
        
        if(!$this->id){
            $this->avatar->move($this->getTmpUploadRootDir(), $this->avatar->getClientOriginalName());
        }else{
            $this->avatar->move($this->getUploadRootDir(), $this->avatar->getClientOriginalName());
        }
        $this->setAvatar($this->avatar->getClientOriginalName());
    } */
    
    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function moveAvatar()
    {
        /*if (null === $this->avatar) {
            return;
        }
        if(!is_dir($this->getUploadRootDir())){
            mkdir($this->getUploadRootDir());
        }
        
        if(!is_dir($this->getResizeRootDir())){
            mkdir($this->getResizeRootDir());
        }*/
        
        //copy($this->getTmpUploadRootDir().$this->avatar, $this->getFullAvatarPath());
        //unlink($this->getTmpUploadRootDir().$this->avatar);
    }

    /**
     * @ORM\PreRemove()
     */
    public function removeAvatar()
    {
        //unlink($this->getFullAvatarPath());
        //rmdir($this->getUploadRootDir());
    }
    
    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     * @param string $pathAvatar original avatar path which will be resize
     * @param string $pathAvatarResize the path of the resized avatar
     * @param string $width the width of the resized avatar
     * @param string $height the height of the resized avatar
     * @return void
     * 
    
    public function resizeAvatar()
    {
   
        if (null === $this->avatar) {
            return;
        }
        
        $extTab = explode('.', $this->getFullAvatarPath());
        $ext = array_pop($extTab);

        switch( strtolower( $ext ) ) {
            case "png";
                $source = imagecreatefrompng($this->getFullAvatarPath());
                break;
            case "jpeg";
            case "jpg";
                $source = imagecreatefromjpeg($this->getFullAvatarPath());
                break;
            case "gif";
                $source = imagecreatefromgif($this->getFullAvatarPath());
                break;
            case "bmp";
                $source = imagecreatefromwbmp($this->getFullAvatarPath());
                break;
            default :
                $source = imagecreatefromjpeg($this->getFullAvatarPath());
        }
        
        $destination = imagecreatetruecolor(48, 48); // On crée la miniature vide

        // Les fonctions imagesx et imagesy renvoient la largeur et la hauteur d'une image
        $largeur_source = imagesx($source);
        $hauteur_source = imagesy($source);
        $largeur_destination = imagesx($destination);
        $hauteur_destination = imagesy($destination);

        // On crée la miniature
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $largeur_destination, $hauteur_destination, $largeur_source, $hauteur_source);

        if(!is_dir($this->getResizeRootDir())){
            mkdir($this->getResizeRootDir());
        }
        
        // On enregistre la miniature sous le nom "mini_couchersoleil.jpg"
        imagejpeg($destination, $this->getResizeAvatarPath());
    }*/

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
     * Add tournaments
     *
     * @param Ks\TournamentBundle\Entity\Tournament $tournaments
     */
    public function addTournament(\Ks\TournamentBundle\Entity\Tournament $tournaments)
    {
        $this->tournaments[] = $tournaments;
    }

    /**
     * Get tournaments
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTournaments()
    {
        return $this->tournaments;
    }
}