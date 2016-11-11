<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

use Symfony\Component\HttpFoundation\File\File;



/**
 * Ks\UserBundle\Entity\UserDetail
 *
 * @ORM\Table(name="ks_user_detail")
 * @ORM\Entity
 * @Vich\Uploadable
 */
class UserDetail
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
     * @var smallint $country_code
     *
     * @ORM\Column(name="country_code", type="string", length=2, nullable=true)
     */
    private $country_code;

    /**
     * @var string $firstname
     *
     * @ORM\Column(name="firstname", type="string", length=45, nullable=true)
     */
    private $firstname;

    /**
     * @var string $lastname
     *
     * @ORM\Column(name="lastname", type="string", length=45, nullable=true)
     */
    private $lastname;

    /**
     * @var text $description
     * @Assert\MaxLength(255)
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;
    
    /**
     * @var string $country_area
     *
     * @ORM\Column(name="country_area", type="string", length=45 ,nullable=true)
     */
    private $country_area;

    /**
     * @var decimal $town
     *
     * @ORM\Column(name="town", type="string", length=45, nullable=true)
     */
    private $town;

    /**
     * @var decimal $longitude
     *
     * @ORM\Column(name="longitude", type="decimal", scale="8", nullable=true)
     */
    private $longitude;
    
    
    /**
     * @var decimal $latitude
     *
     * @ORM\Column(name="latitude", type="decimal", scale="8",nullable=true)
     */
    private $latitude;
    
    /**
     * @var string $weight
     *
     * @ORM\Column(name="weight", type="smallint", nullable=true)
     */
    private $weight;
    
     /**
     * @var Ks\UserBundle\Entity\UserDetail $sexe
     * 
     * @ORM\ManyToOne(targetEntity="Ks\UserBundle\Entity\Sexe", inversedBy="sexeUser")
     */
    protected $sexe;
    
    
     /**
     * @var string $height
     *
     * @ORM\Column(name="height", type="decimal", scale="2" ,nullable=true)
     */
    private $height;
    
    /**
     * @ORM\Column(type="string", length=255, name="image_name",nullable="true")
     *
     * @var string $imageName
     */
    protected $imageName;
    
    /**
     * @ORM\Column(type="date",nullable="true")
     * @Assert\Date()
     * @var date
     */
    protected $bornedAt;
        
    
    /**
     * @ORM\Column(type="string", length=255, name="url_avatar_facebook",nullable="true")
     *
     * @var string $urlAvatarFacebook
     */
    protected $urlAvatarFacebook;
    
    /**
     * @var text $fullAddress
     * @Assert\MaxLength(255)
     *
     * @ORM\Column(name="full_address", type="text", nullable=true)
     */
    private $fullAddress;
    
    
     /*
     * @ORM\OneToMany(targetEntity="Ks\UserBundle\Entity\UserDetailHaveSports", mappedBy="userDetail", cascade={"remove", "persist"})
     * 
     * @var ArrayCollection
     
    protected $sports;*/
    
    /**
     * @ORM\ManyToMany(targetEntity="\Ks\ActivityBundle\Entity\Sport", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="ks_user_detail_has_sport")
     */
    private $sports;
    
    /**
     * @ORM\ManyToMany(targetEntity="\Ks\UserBundle\Entity\Preference", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="ks_user_detail_has_preference")
     */
    private $preferences;
    
    /**
     * @var boolean $could_invit
     *
     * @ORM\Column(name="receivesDailyEmail", type="boolean", options={"default" = 1})
     */
    private $receivesDailyEmail = true;
    
    //Données questionnaire coach
    
    /**
     * @ORM\Column(type="string", length=255, name="phone", nullable="true")
     *
     * @var string $phone
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, name="occupation", nullable="true")
     *
     * @var string $occupation
     */
    private $occupation;
    
    /**
     * @ORM\Column(type="string", length=255, name="familyConstraint", nullable="true")
     *
     * @var string $familyConstraint
     */
    private $familyConstraint;
    
    /**
     * @ORM\Column(type="string", length=255, name="occupationConstraint", nullable="true")
     *
     * @var string $occupationConstraint
     */
    private $occupationConstraint;
    
    /**
     * @ORM\Column(type="string", length=255, name="contactEmergency", nullable="true")
     *
     * @var string $contactEmergency
     */
    private $contactEmergency;
    
    /**
     * @var boolean $medicalFollow
     *
     * @ORM\Column(name="medicalFollow", type="boolean", nullable="true")
     */
    private $medicalFollow;
    
    /**
     * @var boolean $medicalFollowingOK
     *
     * @ORM\Column(name="medicalFollowingOK", type="boolean", nullable="true")
     */
    private $medicalFollowingOK;
    
    /**
     * @var boolean $sportDoctor
     *
     * @ORM\Column(name="sportDoctor", type="boolean", nullable="true")
     */
    private $sportDoctor;
    
    /**
     * @var boolean $osteo
     *
     * @ORM\Column(name="osteo", type="boolean", nullable="true")
     */
    private $osteo;
    
    /**
     * @var boolean $kine
     *
     * @ORM\Column(name="kine", type="boolean", nullable="true")
     */
    private $kine;
    
    /**
     * @var boolean $podo
     *
     * @ORM\Column(name="podo", type="boolean", nullable="true")
     */
    private $podo;
    
    /**
     * @var boolean $medicalMonitoring
     *
     * @ORM\Column(name="medicalMonitoring", type="boolean", nullable="true")
     */
    private $medicalMonitoring;
    
    /**
     * @var boolean $allergies
     *
     * @ORM\Column(name="allergies", type="boolean", nullable="true")
     */
    private $allergies;
    
    /**
     * @var boolean $effortTest
     *
     * @ORM\Column(name="efforTest", type="boolean", nullable="true")
     */
    private $effortTest;
    
    /**
     *
     * @ORM\Column(name="HRMax", type="smallint", nullable=true)
     */
    private $HRMax;
    
    /**
     *
     * @ORM\Column(name="HRRest", type="smallint", nullable=true)
     */
    private $HRRest;
    
    /**
     *
     * @ORM\Column(name="VMASpeed", type="decimal", scale="1" ,nullable=true)
     */
    private $VMASpeed;
    
    /**
     * @ORM\Column(type="string", length=255, name="medicalMisc", nullable="true")
     *
     * @var string $medicalMisc
     */
    private $medicalMisc;
    
    /**
     * @ORM\Column(type="string", length=255, name="shoes", nullable="true")
     *
     * @var string $shoes
     */
    private $shoes;
    
    /**
     * @ORM\Column(type="string", length=255, name="bags", nullable="true")
     *
     * @var string $bags
     */
    private $bags;
    
    /**
     * @ORM\Column(type="string", length=255, name="HR", nullable="true")
     *
     * @var string $HR
     */
    private $HR;
    
    /**
     * @ORM\Column(type="string", length=255, name="bikes", nullable="true")
     *
     * @var string $bikes
     */
    private $bikes;
    
    /**
     * @ORM\Column(type="string", length=255, name="equipmentMisc", nullable="true")
     *
     * @var string $equipmentMisc
     */
    private $equipmentMisc;
    
    /**
     * @ORM\Column(type="string", length=255, name="license", nullable="true")
     *
     * @var string $license
     */
    private $license;
    
    /**
     * @ORM\Column(type="string", length=255, name="mySports", nullable="true")
     *
     * @var string $mySports
     */
    private $mySports;
    
    /**
     * @ORM\Column(type="string", length=255, name="sportLevel", nullable="true")
     *
     * @var string $sportLevel
     */
    private $sportLevel;
    
    /**
     * @ORM\Column(type="string", length=255, name="achievement", nullable="true")
     *
     * @var string $achievement
     */
    private $achievement;
    
    /**
     * @var boolean $injuries
     *
     * @ORM\Column(name="injuries", type="boolean", nullable="true")
     */
    private $injuries;
    
    /**
     * @ORM\Column(type="string", length=255, name="injuriesTreat", nullable="true")
     *
     * @var string $injuriesTreat
     */
    private $injuriesTreat;
    
    /**
     * @ORM\Column(type="string", length=255, name="workoutHours", nullable="true")
     *
     * @var string $workoutHours
     */
    private $workoutHours;
    
    /**
     * @var boolean $crossWorkout
     *
     * @ORM\Column(name="crossWorkout", type="boolean", nullable="true")
     */
    private $crossWorkout;
    
    /**
     * @ORM\Column(type="string", length=255, name="crossWorkouts", nullable="true")
     *
     * @var string $crossWorkouts
     */
    private $crossWorkouts;
    
    /**
     * @ORM\Column(type="string", length=255, name="workoutDesc", nullable="true")
     *
     * @var string $workoutDesc
     */
    private $workoutDesc;
    
    /**
     * @ORM\Column(type="string", length=255, name="workoutWeek", nullable="true")
     *
     * @var string $workoutWeek
     */
    private $workoutWeek;
    
    /**
     * @ORM\Column(type="string", length=255, name="strongPoints", nullable="true")
     *
     * @var string $strongPoints
     */
    private $strongPoints;
    
    /**
     * @ORM\Column(type="string", length=255, name="weakPoints", nullable="true")
     *
     * @var string $weakPoints
     */
    private $weakPoints;
    
    /**
     * @ORM\Column(type="string", length=255, name="regularInjuries", nullable="true")
     *
     * @var string $regularInjuries
     */
    private $regularInjuries;
    
    /**
     * @var boolean $gotCoach
     *
     * @ORM\Column(name="gotCoach", type="boolean", nullable="true")
     */
    private $gotCoach;
    
    /**
     * @var boolean $wantCoach
     *
     * @ORM\Column(name="wantCoach", type="boolean", nullable="true")
     */
    private $wantCoach;
    
    /**
     * @ORM\Column(type="string", length=255, name="whyCoach", nullable="true")
     *
     * @var string $whyCoach
     */
    private $whyCoach;
    
    /**
     * @ORM\Column(type="string", length=255, name="goalsImpediment", nullable="true")
     *
     * @var string $goalsImpediment
     */
    private $goalsImpediment;
    
    /**
     * @var boolean $goalsImpedimentDrop
     *
     * @ORM\Column(name="goalsImpedimentDrop", type="boolean", nullable="true")
     */
    private $goalsImpedimentDrop;
    
    /**
     * @ORM\Column(type="string", length=255, name="goalsImpedimentManage", nullable="true")
     *
     * @var string $goalsImpedimentManage
     */
    private $goalsImpedimentManage;
    
    /**
     * @ORM\Column(type="string", length=255, name="goalsRank", nullable="true")
     *
     * @var string $goalsRank
     */
    private $goalsRank;
    
    /**
     * @var boolean $coachHasSponsors
     *
     * @ORM\Column(name="coachHasSponsors", type="boolean", nullable="true")
     */
    private $coachHasSponsors;
    
    public function __construct()
    {
        $this->sports   = new \Doctrine\Common\Collections\ArrayCollection;
        $this->preferences = new \Doctrine\Common\Collections\ArrayCollection;
        $this->services = new \Doctrine\Common\Collections\ArrayCollection;
        $this->receivesDailyEmail = true;
    }
    
     /**
     * Set urlAvatarFacebook
     *
     * @param string $urlAvatarFacebook
     */
    public function setUrlAvatarFacebook($urlAvatarFacebook)
    {
        $this->urlAvatarFacebook = $urlAvatarFacebook;
    }

    /**
     * Get urlAvatarFacebook
     *
     * @return urlAvatarFacebook 
     */
    public function getUrlAvatarFacebook()
    {
        return $this->urlAvatarFacebook;
    }
    
     /**
     * Set image
     *
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * Get image
     *
     * @return image 
     */
    public function getImage()
    {
        return $this->image;
    }
    
    
    
     /**
     * Set imagePath
     *
     * @param string $imagePath
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
    }

    /**
     * Get imagePath
     *
     * @return imagePath 
     */
    public function getImagePath()
    {
        return $this->ImagePath;
    }
    
    
    
     /**
     * Set imageResizePath
     *
     * @param string $imageResizePath
     */
    public function setImageResizePath($imageResizePath)
    {
        $this->imageResizePath = $imageResizePath;
    }

    /**
     * Get imageResizePath
     *
     * @return imageResizePath 
     */
    public function getImageResizePath()
    {
        return $this->imageResizePath;
    }
    
    
    /**
     * Set imageName
     *
     * @param string imageName
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
    }

    /**
     * Get imageName
     *
     * @return imageName 
     */
    public function getImageName()
    {
        return $this->imageName;
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
     * Set country_code
     *
     * @param smallint $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->country_code = $countryCode;
    }

    /**
     * Get country_code
     *
     * @return smallint 
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
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
     * Set town
     *
     * @param decimal $town
     */
    public function setTown($town)
    {
        $this->town = $town;
    }

    /**
     * Get town
     *
     * @return decimal 
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * Set longitude
     *
     * @param decimal $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * Get longitude
     *
     * @return decimal 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
    
    /**
     * Set latitude
     *
     * @param decimal $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * Get latitude
     *
     * @return decimal 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }
    
    
    /**
     * Set weight
     *
     * @param smallint $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * Get weight
     *
     * @return smallint 
     */
    public function getWeight()
    {
        return $this->weight;
    }
    
    /**
     * Set HRRest
     *
     * @param smallint $HRRest
     */
    public function setHRRest($HRRest)
    {
        $this->HRRest = $HRRest;
    }

    /**
     * Get HRRest
     *
     * @return smallint 
     */
    public function getHRRest()
    {
        return $this->HRRest;
    }
    
    /**
     * Set HRMax
     *
     * @param smallint $HRMax
     */
    public function setHRMax($HRMax)
    {
        $this->HRMax = $HRMax;
    }

    /**
     * Get HRMax
     *
     * @return smallint 
     */
    public function getHRMax()
    {
        return $this->HRMax;
    }
    
    /**
     * Set VMASpeed
     *
     * @param smallint VMASpeed
     */
    public function setVMASpeed($VMASpeed)
    {
        $this->VMASpeed = $VMASpeed;
    }

    /**
     * Get VMASpeed
     *
     * @return smallint 
     */
    public function getVMASpeed()
    {
        return $this->VMASpeed;
    }
    
    /**
     * Set height
     *
     * @param smallint $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * Get height
     *
     * @return float (,2) 
     */
    public function getHeight()
    {
        return $this->height;
    }
    
    /**
     * Set height
     *
     * @param string $sexe
     */
    public function setSexe($sexe)
    {
        $this->sexe = $sexe;
    }

    /**
     * Get sexe
     *
     * @return string 
     */
    public function getSexe()
    {
        return $this->sexe;
    }
    
    public function setPhone($phone) {$this->phone = $phone;}
    public function getPhone() {return $this->phone;}

    public function setOccupation($occupation) {$this->occupation = $occupation;}
    public function getOccupation() {return $this->occupation;}
    
    public function setFamilyConstraint($familyConstraint) {$this->familyConstraint = $familyConstraint;}
    public function getFamilyConstraint() {return $this->familyConstraint;}
    
    public function setOccupationConstraint($occupationConstraint) {$this->occupationConstraint = $occupationConstraint;}
    public function getoccupationConstraint() {return $this->occupationConstraint;}
    
    public function setContactEmergency($contactEmergency) {$this->contactEmergency = $contactEmergency;}
    public function getcontactEmergency() {return $this->contactEmergency;}
    
    public function setMedicalFollow($medicalFollow) {$this->medicalFollow = $medicalFollow;}
    public function getmedicalFollow() {return $this->medicalFollow;}
    
    public function setMedicalFollowingOK($medicalFollowingOK) {$this->medicalFollowingOK = $medicalFollowingOK;}
    public function getmedicalFollowingOK() {return $this->medicalFollowingOK;}
    
    public function setSportDoctor($sportDoctor) {$this->sportDoctor = $sportDoctor;}
    public function getsportDoctor() {return $this->sportDoctor;}
    
    public function setOsteo($osteo) {$this->osteo = $osteo;}
    public function getosteo() {return $this->osteo;}
    
    public function setKine($kine) {$this->kine = $kine;}
    public function getkine() {return $this->kine;}

    public function setPodo($podo) {$this->podo = $podo;}
    public function getpodo() {return $this->podo;}

    public function setMedicalMonitoring($medicalMonitoring) {$this->medicalMonitoring = $medicalMonitoring;}
    public function getmedicalMonitoring() {return $this->medicalMonitoring;}
    
    public function setAllergies($allergies) {$this->allergies = $allergies;}
    public function getallergies() {return $this->allergies;}
    
    public function setEffortTest($effortTest) {$this->effortTest = $effortTest;}
    public function geteffortTest() {return $this->effortTest;}
    
    public function setMedicalMisc($medicalMisc) {$this->medicalMisc = $medicalMisc;}
    public function getmedicalMisc() {return $this->medicalMisc;}
    
    public function setShoes($shoes) {$this->shoes = $shoes;}
    public function getshoes() {return $this->shoes;}
    
    public function setBags($bags) {$this->bags = $bags;}
    public function getbags() {return $this->bags;}
    
    public function setHR($HR) {$this->HR = $HR;}
    public function getHR() {return $this->HR;}
    
    public function setBikes($bikes) {$this->bikes = $bikes;}
    public function getbikes() {return $this->bikes;}

    public function setEquipmentMisc($equipmentMisc) {$this->equipmentMisc = $equipmentMisc;}
    public function getequipmentMisc() {return $this->equipmentMisc;}
    
    public function setLicense($license) {$this->license = $license;}
    public function getlicense() {return $this->license;}
    
    public function setMySports($mySports) {$this->mySports = $mySports;}
    public function getmySports() {return $this->mySports;}
    
    public function setSportLevel($sportLevel) {$this->sportLevel = $sportLevel;}
    public function getsportLevel() {return $this->sportLevel;}
    
    public function setAchievement($achievement) {$this->achievement = $achievement;}
    public function getachievement() {return $this->achievement;}

    public function setInjuries($injuries) {$this->injuries = $injuries;}
    public function getinjuries() {return $this->injuries;}
    
    public function setInjuriesTreat($injuriesTreat) {$this->injuriesTreat = $injuriesTreat;}
    public function getinjuriesTreat() {return $this->injuriesTreat;}
    
    public function setWorkoutHours($workoutHours) {$this->workoutHours = $workoutHours;}
    public function getworkoutHours() {return $this->workoutHours;}
    
    public function setCrossWorkout($crossWorkout) {$this->crossWorkout = $crossWorkout;}
    public function getcrossWorkout() {return $this->crossWorkout;}
    
    public function setCrossWorkouts($crossWorkouts) {$this->crossWorkouts = $crossWorkouts;}
    public function getcrossWorkouts() {return $this->crossWorkouts;}
    
    public function setWorkoutDesc($workoutDesc) {$this->workoutDesc = $workoutDesc;}
    public function getworkoutDesc() {return $this->workoutDesc;}

    public function setWorkoutWeek($workoutWeek) {$this->workoutWeek = $workoutWeek;}
    public function getworkoutWeek() {return $this->workoutWeek;}
    
    public function setStrongPoints($strongPoints) {$this->strongPoints = $strongPoints;}
    public function getstrongPoints() {return $this->strongPoints;}
    
    public function setWeakPoints($weakPoints) {$this->weakPoints = $weakPoints;}
    public function getweakPoints() {return $this->weakPoints;}
    
    public function setRegularInjuries($regularInjuries) {$this->regularInjuries = $regularInjuries;}
    public function getregularInjuries() {return $this->regularInjuries;}
    
    public function setGotCoach($gotCoach) {$this->gotCoach = $gotCoach;}
    public function getgotCoach() {return $this->gotCoach;}
    
    public function setWantCoach($wantCoach) {$this->wantCoach = $wantCoach;}
    public function getwantCoach() {return $this->wantCoach;}
    
    public function setWhyCoach($whyCoach) {$this->whyCoach = $whyCoach;}
    public function getwhyCoach() {return $this->whyCoach;}

    public function setGoalsImpediment($goalsImpediment) {$this->goalsImpediment = $goalsImpediment;}
    public function getgoalsImpediment() {return $this->goalsImpediment;}
    
    public function setGoalsImpedimentDrop($goalsImpedimentDrop) {$this->goalsImpedimentDrop = $goalsImpedimentDrop;}
    public function getgoalsImpedimentDrop() {return $this->goalsImpedimentDrop;}
    
    public function setGoalsImpedimentManage($goalsImpedimentManage) {$this->goalsImpedimentManage = $goalsImpedimentManage;}
    public function getgoalsImpedimentManage() {return $this->goalsImpedimentManage;}
    
    public function setGoalsRank($goalsRank) {$this->goalsRank = $goalsRank;}
    public function getgoalsRank() {return $this->goalsRank;}
    
    public function setCoachHasSponsors($coachHasSponsors) {$this->coachHasSponsort = $coachHasSponsors;}
    public function getCoachHasSponsors() {return $this->coachHasSponsors;}
    
    /**
     * @param string $pathFileToRemove path of the file to remove
     * @return void
     */
    public function removeUpload($pathFileToRemove)
    {
        if (file_exists($pathFileToRemove)) {
            unlink($pathFileToRemove);
        }
    }
    
    /**
     * @param string $pathImage original picture path which will be resize
     * @param string $pathImageResize the path of the resized picture
     * @param string $width the width of the resized picture
     * @param string $height the height of the resized picture
     * @return void
     * 
    */
    public function resizeImage($pathImage,$pathImageResize,$width, $height)
    {
        //$pathImage = $this->getAbsolutePath();
        //$pathImageResize = $this->getAbsoluteResizePath();
        $imagine = new \Imagine\Gd\Imagine();
        $box = new \Imagine\Image\Box($width,$height);
        
        $imagine->open($pathImage)
                ->resize($box)
                ->save($pathImageResize,array("format"=>"jpg"));
    }

    /**
     * Set bornedAt
     *
     * @param date $bornedAt
     */
    public function setBornedAt($bornedAt)
    {
        $this->bornedAt = $bornedAt;
    }

    /**
     * Get bornedAt
     *
     * @return date 
     */
    public function getBornedAt()
    {
        return $this->bornedAt;
    }

    /**
     * Set fullAddress
     *
     * @param text $fullAddress
     */
    public function setFullAddress($fullAddress)
    {
        $this->fullAddress = $fullAddress;
    }

    /**
     * Get fullAddress
     *
     * @return text 
     */
    public function getFullAddress()
    {
        return $this->fullAddress;
    }



    
    

    /**
     * Set receivesDailyEmail
     *
     * @param boolean $receivesDailyEmail
     */
    public function setReceivesDailyEmail($receivesDailyEmail)
    {
        $this->receivesDailyEmail = $receivesDailyEmail;
    }

    /**
     * Get receivesDailyEmail
     *
     * @return boolean 
     */
    public function getReceivesDailyEmail()
    {
        return $this->receivesDailyEmail;
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
     * Get sports
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getSports()
    {
        return $this->sports;
    }
    
    /**
     * Set sport
     *
     * @param \Ks\ActivityBundle\Entity\Sport $sport
     */
    public function setSports($sports)
    {
        foreach( $sports as $sport ) {
            $this->addSport( $sport );
        }
    }
    
    /**
     * Add preferences
     *
     * @param Ks\UserBundle\Entity\Preference $preferences
     */
    public function addPreference(\Ks\UserBundle\Entity\Preference $preferences)
    {
        $this->preferences[] = $preferences;
    }

    /**
     * Get preferences
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPreferences()
    {
        return $this->preferences;
    }
    
    /**
     * Set preference
     *
     * @param \Ks\UserBundle\Entity\Preference $preference
     */
    public function setPreferences($preferences)
    {
        foreach( $preferences as $preference ) {
            $this->addPreference( $preference );
        }
    }
}