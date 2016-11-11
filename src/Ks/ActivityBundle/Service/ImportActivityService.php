<?php
namespace Ks\ActivityBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ks\ActivityBundle\Activity;
use Ks\ActivityBundle\Service\ActivityService;

/**
 *
 * @author CF, Ced, FMO
 */
class ImportActivityService
    extends \Twig_Extension
{
    protected $doctrine;
    protected $container;
    protected $activityService;
    
    const EARTH_RADIUS = 6371; // utilisé pour les calculs de distance, valeur en km
    
    /**
     *
     * @param Registry $doctrine
     * @param ActivityService $activityService 
     */
    public function __construct(Registry $doctrine, $container, ActivityService $activityService)
    {
        $this->doctrine         = $doctrine;
        $this->container        = $container;
        $this->activityService  = $activityService;
    }
    
    /**
     * Nom de la classe du service.
     * 
     * @return string 
     */
    public function getName()
    {
        return 'ImportActivityService';
    }
    
    /**
     * 
     * @param array $activityDatas
     * @param \Ks\UserBundle\Entity\User $user
     */
    public function saveUserSessionFromActivityDatas(array $activityDatas, \Ks\UserBundle\Entity\User $user)
    {
        $em          = $this->doctrine->getEntityManager();
        $session     = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth($user);
        $activityRep = $em->getRepository('KsActivityBundle:Activity');
        
        $session->setSource($activityDatas['info']['source']);
        $session->setDistance($activityDatas['info']['distance']);
        $session->setDuration($activityDatas['info']['timeDuration']);
        $session->setTimeMoving($activityDatas['info']['duration']);
        $session->setIssuedAt($activityDatas['info']['startDate']);
        $session->setModifiedAt(new \DateTime('Now'));
        $session->setElevationGain($activityDatas['info']['D+']);
        $session->setElevationLost($activityDatas['info']['D-']);
        $session->setElevationMin($activityDatas['info']['minEle']);
        $session->setElevationMax($activityDatas['info']['maxEle']);
        //
        $codeSport  = !isset($activityDatas['codeSport']) || $activityDatas['codeSport'] == '' ?
            'running'
            : $activityDatas['codeSport'];
        
        $sport      = $em->getRepository('KsActivityBundle:Sport')->findOneByCodeSport($codeSport);
        
        if (!is_object($sport)) {
            $sportType = new \Ks\ActivityBundle\Entity\SportType();
            $sportType->setCode(2);
            $sport = new \Ks\ActivityBundle\Entity\Sport();
            $sport->setLabel($codeSport);
            $sport->setCodeSport($codeSport);
            $sport->setSportType($sportType);
            $em->persist($sport);
        }
        //
        $session->setSport($sport);
        $session->setTrackingDatas($activityDatas);
        
        $firstWaypoint = $this->getFirstWaypointNotEmpty($activityDatas);
        if ($firstWaypoint != null) {
            $session->setPlace(
                $this->findPlace($firstWaypoint["lat"], $firstWaypoint["lon"])
            );
        }
        $em->persist($session);
        
        $em->flush(); // NOTE CF: flush pour donner un id à $activity, nécessaire pour $earnPoints
        
        return $session;
    }
    
    /**
     * Construction du tableau json normalisé pour stocker le détail d'une activité de type "tracking"
     * 
     * @param array $activityInfos
     * @param string $serviceName
     * @return json 
     */
    public function buildJsonToSave($user, array $activityInfos, $serviceName, $checkboxSRTM=null)
    {
        $serviceName        = strtolower($serviceName);
        $distance               = null;
        $duration               = null;
        $tDuration              = null;
        $endDate                = null;
        $issetHR                = false;
        $issetTemperatures      = false;
        $issetPedalingFrequency = false;
        $issetPower             = false;
        $samples                = array();
        $laps                   = array();
        $error                  = null;
        
        //Récupération spécifiques en fonction des services
        switch ($serviceName) {
            case 'nikeplus' :
                $startDate  = new \DateTime($activityInfos["startTimeUtc"]);
                $waypoints  = $this->getFullWaypointsFromNike($activityInfos);
                break;
            
            case 'runkeeper':
                $startDate      = new \DateTime($activityInfos["start_time"]);
                $waypoints      = $this->getFullWaypointsFromRunKeeper($activityInfos);
                if (isset($activityInfos['total_distance']) && !empty($activityInfos['total_distance'])) {
                    $distance   = round($activityInfos['total_distance'] / 1000.0, 3);
                }
                if (isset($activityInfos['duration']) && !empty($activityInfos['duration'])) {
                    $duration   = $activityInfos['duration'];
                    $tDuration  = $this->activityService->secondesToTimeDuration($activityInfos['duration']);
                }
                if (isset( $activityInfos['heart_rate'] ) && count($activityInfos['heart_rate']) > 0) {
                    $issetHR    = true;
                }
                break;
            
            case 'suunto':
                //$startDate      = new \DateTime($activityInfos["LocalStartTime"]);
                $startDate      = new \DateTime($activityInfos["LastModifiedDate"]);
                $tempPoints     = $this->getFullWaypointsFromSuunto($activityInfos);
                $laps           = $this->getFullLapsFromSuunto($activityInfos);
                $samples        = $this->getFullSamplesFromSuunto($activityInfos);
   
                if (isset($samples) && !is_null($samples) && count($samples) > 0) {
                    $waypoints = array();
                    foreach ($samples as $sample) {
                        unset($point);
                        $point = array();
                        $point['interval']              = $sample['interval'];
                        $point['fullDistances']         = $sample['fullDistances'];
                        $point['fullDurationMoving']    = $sample['fullDurationMoving'];
                        $point['ele']                   = $sample['ele'];
                        $point['heartRate']             = $sample['heartRate'];
                        $point['temperature']           = $sample['temperature'];
                        $point['pedalingFrequency']     = $sample['pedalingFrequency'];
                        $point['power']                 = $sample['power'];
                        
                        if (!is_null($sample['heartRate'])) $issetHR = true;
                        if (!is_null($sample['temperature'])) $issetTemperatures = true;
                        if (!is_null($sample['pedalingFrequency'])) $issetPedalingFrequency = true;
                        if (!is_null($sample['power'])) $issetPower = true;
                        
                        foreach ($tempPoints as $tempPoint) {
                            $point['lat']                   = $tempPoint['lat'];
                            $point['lon']                   = $tempPoint['lon'];
                            $point['speed']                 = $tempPoint['speed'];
                            $point['pace']                  = $tempPoint['pace'];
                            if ($tempPoint['fullDurationMoving'] == $sample['fullDurationMoving']) {
                                $waypoints[] = $point;
                                break;
                            }
                            else if ($tempPoint['fullDurationMoving'] > $sample['fullDurationMoving']) {
                                $waypoints[] = $point; //on prend le point précédent si pas de correspondance
                                break;
                            }
                        }
                    }
                    
//                    var_dump("waypoints");
//                    var_dump($waypoints);
                }
                else $waypoints = $tempPoints;
                
                if (isset($activityInfos['Distance']) && !empty($activityInfos['Distance'])) {
                    $distance   = round($activityInfos['Distance'], 3);
                }
                if (isset($activityInfos['Duration']) && !empty($activityInfos['Duration'])) {
                    $duration   = $activityInfos['Duration'];
                    $tDuration  = $this->activityService->secondesToTimeDuration($activityInfos['Duration']);
                }
                break;
            
            case 'gpx':
                //$stime =  microtime(true);
                list($waypoints, $startDate, $issetHR, $issetTemperatures) = $this->getFullWaypointsAndStartDateFromGpx($activityInfos['fileName'], $checkboxSRTM);
                break;
            
            case 'garmin':
                $em            = $this->doctrine->getEntityManager();
                $garminService = $em->getRepository('KsUserBundle:Service')->findOneByName('Garmin');
                $userService   = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array(
                    'user'       => $user->getId(),
                    'service'    => $garminService->getId()
                ));
                $credentials   = array(
                    'username'   => $userService->getConnectionId(),
                    'password'   => base64_decode($userService->getConnectionPassword()),
                    'identifier' => microtime(true)
                );
                try {
                    $garminApi     = new \dawguk\GarminConnect($credentials);
                    list($waypoints, $issetHR, $issetPedalingFrequency, $issetPower, $error) = $this->getFullWaypointsFromGarmin($garminApi, $activityInfos['activityId']);
                    
                    if (isset($activityInfos['total_duration']) && !empty($activityInfos['total_duration'])) {
                        $duration   = $activityInfos['total_duration'];
                        $tDuration  = $this->activityService->secondesToTimeDuration($activityInfos['total_duration']);
                    }
                    $startDate      = new \DateTime($activityInfos["start_time"]);
                } catch (Exception $e) {
                    var_dump($e->getMessage());
                };
                break;
            
            /*case 'garmin':
                // NOTE CF: DEPRECATED
                $tmpName = tempnam(sys_get_temp_dir(), 'garmin');
                $fh = file_put_contents($tmpName, $activityInfos['xml']);
                list($waypoints, $startDate) = $this->getFullWaypointsAndStartDateFromTcx($tmpName);
                unlink($tmpName);
                break; */
            
            case 'endomondo':
                $waypoints      = isset($activityInfos['path']) && is_array($activityInfos['path']) ?
                    $this->getFullWaypointsFromEndomondo($activityInfos['path'])
                    : array();
                $startDate      = new \DateTime($activityInfos['start_time']);
                $tDuration      = $this->activityService->secondesToTimeDuration($duration);
                $endDate        = $this->activityService->calculEndDate($startDate, $duration);
                if (isset($activityInfos['duration_sec'])) {
                    $duration   = (int)$activityInfos['duration_sec'];
                    $tDuration  = $this->activityService->secondesToTimeDuration($duration);
                    $endDate    = $this->activityService->calculEndDate($startDate, $duration);
                }
                if (isset($activityInfos['distance_km'])) {
                    $distance   = (float)$activityInfos['distance_km'];
                }
                break;
            
            case 'polar':
                //var_dump($activityInfos);exit;
                list($waypoints, $startDate, $issetHR, $issetTemperatures) = $this->getFullWaypointsAndStartDateFromGpx((integer)$activityInfos['activityId'], false);
                break;
                break;
            
            default:
                throw new \Exception('Service name inconnu: '.$serviceName);
                break;
        }
        
        $numPoints = count($waypoints);

        if (($distance === null) && isset($waypoints[$numPoints - 1])) {
            $distance   = $waypoints[$numPoints - 1]['fullDistances'];
        }
        if (($duration === null) && isset($waypoints[$numPoints - 1])) {
            $duration   = $waypoints[$numPoints - 1]['fullDurationMoving'];
            $tDuration  = $this->activityService->secondesToTimeDuration($duration);
            $endDate    = $this->activityService->calculEndDate($startDate, $duration);
        }
        //
        $aThingsToSleeked = array('speed', 'pace', 'ele');
        $waypoints  = $this->sleekArray($waypoints, $aThingsToSleeked);        
        
        //Calcul des min/max des dénivellés, des températures, des HR
        $compute = $this->compute($user, $waypoints);
        
        //Construction du tableau json à sauvegarder en BDD
        $jsonToSave = array(
            "info" => array(
                'source'                    => $serviceName,
                "startDate"                 => $startDate,
                "endDate"                   => $endDate,
                "D+"                        => $compute['+'],
                "D-"                        => $compute['-'],
                'minEle'                    => $compute['minEle'],
                'maxEle'                    => $compute['maxEle'],
                "distance"                  => round($distance, 3),
                'timeDuration'              => $tDuration, // FIXME: il faut se débarasser de ce format
                "duration"                  => (int)$duration,
                "durationMoving"            => (int)$duration,  // FIXME: pas toujours la même chose, à calculer 
                "issetHeartRates"           => $issetHR,
                "issetTemperatures"         => $issetTemperatures,
                "issetPedalingFrequency"    => $issetPedalingFrequency,
                "issetPower"                => $issetPower,
                'minTemp'                   => $compute['minTemp'],
                'maxTemp'                   => $compute['maxTemp'],
                'minHR'                     => $compute['minHR'],
                'maxHR'                     => $compute['maxHR'],
                'minPF'                     => $compute['minPF'],
                'maxPF'                     => $compute['maxPF'],
                'minPower'                  => $compute['minPower'],
                'maxPower'                  => $compute['maxPower'],
                'HRZones'                   => $compute['HRZones']
            ),
            "waypoints" => $waypoints,
            "laps" => $laps
        );
        //var_dump($jsonToSave);exit;
        return array($jsonToSave, $error);
    }
    
    /**
     * Permet de formaliser les données wypoint provenant de chaque service
     * 
     * @param $lat, $lon, $ele, $speed, $pace, $fullDistances, $fullDurationMoving, $pedalingFrequency, $power, $datetime
     * @return array 
     */
    protected function getFormatWaypoint($lat, $lon, $ele, $fullDistances, $interval=0, $fullDurationMoving, $speed, $pace, $heartRate = null, $temperature = null, $pedalingFrequency = null, $power = null, $datetime= null)
    {
        //BUG altitude <0 rencontré avec GARMIN notamment
        //if($ele <0) $ele = 0;
        
        return array(
                "lat"                   => $lat,
                "lon"                   => $lon,
                "ele"                   => $ele,
                "fullDistances"         => $fullDistances,
                "interval"              => $interval,
                "fullDurationMoving"    => $fullDurationMoving,
                "speed"                 => $speed,
                "pace"                  => $pace,
                "heartRate"             => $heartRate,
                "temperature"           => $temperature,
                "pedalingFrequency"     => $pedalingFrequency,
                "power"                 => $power,
                'datetime'              => $datetime
            );
    }
    
    /**
     * Permet de formaliser les données laps provenant de chaque service
     * 
     * @param $duration, $distance, $elevationGain, $elevationLost, $averageSpeed, $minSpeed, $maxSpeed, $averageHR, $minHR, $maxHR
     * @return array 
     */
    protected function getFormatLap($duration, $distance, $elevationGain=null, $elevationLost=null, $averageSpeed=null, $minSpeed=null, $maxSpeed=null, $averageHR=null, $minHR=null, $maxHR=null)
    {
        return array(
                "duration"          => $duration,
                "distance"          => round($distance, 3),
                "elevationGain"     => round($elevationGain, 0),
                "elevationLost"     => round($elevationLost, 0),
                "averageSpeed"      => round($averageSpeed, 1),
                "minSpeed"          => round($minSpeed, 1),
                "maxSpeed"          => round($maxSpeed, 1),
                "averageHR"         => round($averageHR, 0),
                "minHR"             => round($minHR, 0),
                "maxHR"             => round($maxHR, 0)
            );
    }
    
    /**
     *
     * @param array $activityInfos
     * @return array
     */
    protected function getFullWaypointsFromNike(array $activityInfos)
    {
        $waypointsToReturn = array();
        
        //Recherche du tableau SPEED
        $keySpeedInHistoryTable         = null;
        $speedIntervalInHistoryTable    = null;
        if ( isset($activityInfos['geo']['history']) ) {
            foreach ($activityInfos['geo']['history'] as $key => $history) {
                if ( $history["type"] == "SPEED" ) {
                    $keySpeedInHistoryTable         = $key;
                    $speedIntervalInHistoryTable    = $history['intervalMetric'];
                }
            }
        }

        if ( !isset($activityInfos['geo']['waypoints']) ) {
            return array(); // TODO: cas à gérer ! reprendre les valeurs de vitesse de la table history par exemple
        }
        
        $waypoints          = $activityInfos['geo']['waypoints'];
        $numWaypoints       = count($waypoints);
        $duration           = $activityInfos['durationInMS'] / 1000.0;
        $interval           = (float)bcdiv($duration, $numWaypoints, 4);
        $fullDistances      = 0.0;
        $fullDurationMoving = 0;

        for ($i = 0; $i < $numWaypoints; ++$i) {
            $wp1 = array(
                "lat" => $waypoints[$i]["lat"],
                "lon" => $waypoints[$i]["lon"]
            );
            if ( $i == $numWaypoints - 1 ) { // traitement spécifique pour le dernier point
                $wp2 = $wp1;
            } else {
                $wp2 = array(
                    "lat" => $waypoints[$i + 1]["lat"],
                    "lon" => $waypoints[$i + 1]["lon"]
                );
            }
            $distance           = $this->haversineDistanceBetweenWaypoints($wp1, $wp2);
            $fullDistances      += $distance;
            $fullDurationMoving += $interval;
            
            //var_dump("fullDurationMoving=".$fullDurationMoving);
            
            if ( $keySpeedInHistoryTable != null ) {
                $timeSinceStart     = $i * $interval;
                $historySpeedOffset = round($timeSinceStart / $speedIntervalInHistoryTable, 0); // FIXME: il faut aussi tenir compte de l'unité...
                $speed              = (float)$jsonDatas['history'][$keySpeedInHistoryTable]['values'][$historySpeedOffset]; 
            } else {
                $speed              = $interval > 0 ? round(($distance / $interval) * 3600, 2) : 0;
            }

            $pace = $speed > 0 ? (1 / $speed) * 60 : 0;

            $waypointsToReturn[] = $this->getFormatWaypoint(
                $waypoints[$i]["lat"],
                $waypoints[$i]["lon"],
                (int)$waypoints[$i]["ele"],
                round((float)$fullDistances, 2),
                $interval,
                $fullDurationMoving,
                $speed,
                $pace
            );
        }
        
        return $waypointsToReturn;
    }
    
    /**
     * 
     * @param array $activityInfos
     * @return type
     */
    protected function getFullWaypointsFromEndomondo(array $waypoints)
    {
        $waypointsToReturn  = array();
        $numWaypoints       = count($waypoints);
        $fullDistances      = 0.0;
        $fullDurationMoving = 0.0;
        
        for ($i = 0; $i < $numWaypoints; ++$i) {
            if ($waypoints[$i]['ele'] == 0) {
                continue;
            }
            $wp1 = $waypoints[$i];
            if ($i == $numWaypoints - 1) { // traitement spécifique pour le dernier point
                $wp2 = $wp1;
            } else {
                $wp2 = $waypoints[$i + 1];
            }
            $distance           = $this->haversineDistanceBetweenWaypoints($wp2, $wp1);
            $interval           = $wp2['timestamp'] - $wp1['timestamp'];
            $fullDistances      += $distance;
            $fullDurationMoving += $interval;
            $speed              = $interval > 0 ? round(($distance / $interval) * 3600, 2) : 0;
            $pace               = $speed > 0    ? round(1 / $speed * 60.0, 2) : 0;
            $heartRate          = '';
            $waypointsToReturn[] = $this->getFormatWaypoint(
                $wp1['timestamp'],
                $waypoints[$i]['lat'],
                $waypoints[$i]['lon'],
                (int)$waypoints[$i]['ele'],
                round((float)$fullDistances, 2),
                $interval,
                $fullDurationMoving,
                $speed,
                $pace,
                $heartRate
            );
        }
        
        return $waypointsToReturn;
    }
    
    /**
     *
     * @param array $activityInfos
     * @return array 
     */
    protected function getFullWaypointsFromRunKeeper(array $activityInfos)
    {
        $waypointsToReturn = array();
        
        if ( isset($activityInfos['path']) ) {
            $waypoints          = $activityInfos['path'];
            $heartRates         = ( isset( $activityInfos['heart_rate'] ) && !empty( $activityInfos['heart_rate'] ) ) ? $activityInfos['heart_rate'] : array();
            $fullDistances      = 0.0;
            $fullDurationMoving = 0.0;
            $numWaypoints       = count($waypoints);
            
            for ($i = 0; $i < $numWaypoints; ++$i) {
                $wp1 = array(
                    'lat'       => $waypoints[$i]['latitude'],
                    'lon'       => $waypoints[$i]['longitude'],
                    'timestamp' => $waypoints[$i]['timestamp']
                );
                if ($i == $numWaypoints - 1) { // traitement spécifique pour le dernier point
                    $wp2 = $wp1;
                } else {
                    $wp2 = array(
                        'lat'       => $waypoints[$i + 1]['latitude'],
                        'lon'       => $waypoints[$i + 1]['longitude'],
                        'timestamp' => $waypoints[$i + 1]['timestamp']
                    );
                }
                
                $distance           = $this->haversineDistanceBetweenWaypoints($wp2, $wp1);
                $interval           = $wp2['timestamp'] - $wp1['timestamp'];
                $fullDistances      += $distance;
                $fullDurationMoving += $interval;
                $speed              = $interval > 0 ? round(($distance / $interval) * 3600, 2) : 0;
                $pace               = $speed > 0    ? round(1 / $speed * 60.0, 2) : 0;
                $heartRate          = isset( $heartRates[$i] ) ? $heartRates[$i]["heart_rate"] : "";

                $waypointsToReturn[] = $this->getFormatWaypoint(
                    $waypoints[$i]["latitude"],
                    $waypoints[$i]["longitude"],
                    (int)$waypoints[$i]["altitude"],
                    round((float)$fullDistances, 2),
                    $interval,
                    $fullDurationMoving,
                    $speed,
                    $pace,
                    $heartRate
                );
            }
        } else {
            // NOTE CF: et on fait quoi sinon ? :o
        }
        
        return $waypointsToReturn;
    }
    
    /**
     * 
     * @param \dawguk\GarminConnect $garminApi
     * @param type $activityId
     * @return type
     */
    protected function getFullWaypointsFromGarmin(\dawguk\GarminConnect $garminApi, $activityId)
    {
        $waypointsToReturn = array();
        try {
            $activityDetails   = $garminApi->getActivityDetails2($activityId);
            //if ($activityId == 800446104) var_dump($activityDetails);exit;
        } catch (\dawguk\GarminConnect\exceptions\UnexpectedResponseCodeException $e) {
            return array(array(), false, 403);
        };
        
        if (!is_object($activityDetails)) {
            return array(array(), false, 0);
        }
        
        //var_dump($activityDetails);exit;
        //if (!is_object($activityDetails) || !property_exists($activityDetails, $garminRef)) {
        
        $measurements = array();
        /*pour utilisation de getActivityDetails
        $activityDetails = $activityDetails->{"com.garmin.activity.details.json.ActivityDetails"};
        //var_dump($activityDetails);exit;
        // On commence par réindexer les "measurements"
        foreach ($activityDetails->measurements as $measurement) {
            $measurements[$measurement->key] = $measurement->metricsIndex;
        }
        */
        
        //Pour utilisation de getActivityDetails2
        foreach ($activityDetails->metricDescriptors as $measurement) {
            $measurements[$measurement->key] = $measurement->metricsIndex;
        }
        
        //var_dump($measurements);exit;
        
//        exemple sur un enregistrement forerunner 405 : [
//            "sumDuration",            // seconde
//            "directLatitude",         // degré décimal
//            "directLongitude",        // degré décimal
//            "directTimestamp",        // timestamp gmt
//            "directElevation",        // foot
//            "directPace",             // min per mile
//            "sumElapsedDuration",     // seconde
//            "sumMovingDuration",      // seconde
//            "sumDistance",            // mile
//            "directSpeed"             // mile per hour
//        ]
        
        // 1ère passe : échantillonage + réindex // FIXME: NOTE CF: passer en fonction
        $waypoints        = array();
        $numFullWaypoints = $activityDetails->metricsCount;
        
        $iStep            = round($numFullWaypoints / 500);
        if ($iStep == 0) {
            $iStep = 1;
        }
        
        for ($i = 0; $i < $numFullWaypoints; ++$i) {
            if ($i % $iStep == 0) {
                // NOTE CF: pour l'instant c'est la seule entrée que j'ai dans ce tableau
                // $waypoints[] = $activityDetails->metrics[$i]->metrics; //Pour utilisation de getActivityDetails
                $waypoints[] = $activityDetails->activityDetailMetrics[$i]->metrics; //Pour utilisation de getActivityDetails2
            }
        }
        
        // 2ème passe : on converti les points dans un format identique à Suntoo
        // NOTE CF: il faudrait s'assurer de la présence des index des différentes mesures
        // avant de commencer...
        // TODO: il faut également faire les conversions en fonction des unités trouvées...
        
        $issetHR = false;
        $issetPedalingFrequency = false;
        $issetPower = false;
        $numWaypoints = count($waypoints);
        
        for ($i = 0; $i < $numWaypoints; ++$i) {
            $heartRate = isset($measurements['directHeartRate']) ? $waypoints[$i][$measurements['directHeartRate']] : null;
            if (!$issetHR && isset($heartRate) && !is_null($heartRate) && $heartRate !="") $issetHR = true;
            
            $pedalingFrequency = isset($measurements['directBikeCadence']) ? $waypoints[$i][$measurements['directBikeCadence']] : null;
            if (!$issetPedalingFrequency && isset($pedalingFrequency) && !is_null($pedalingFrequency) && $pedalingFrequency !="") $issetPedalingFrequency = true;
            
            $power = isset($measurements['directPower']) ? $waypoints[$i][$measurements['directPower']] : null;
            if (!$issetPower && isset($power) && !is_null($power) && $power !="") $issetPower = true;

            //Attention avec getActivityDetails on a plus l'altitude "directElevation"
            //          avec getActivityDetails2 on plus le rythme "directPace"
            // * 1.609,      // FIXME: conversion mile/h vers km/h en dur
            // * 0.62137      // FIXME: conversion min/mile vers min/km en dur

            $speed = isset($measurements['directSpeed']) ? round($waypoints[$i][$measurements['directSpeed']] * 3600/1000) : 0;
            $pace = isset($measurements['directPace']) ? round($waypoints[$i][$measurements['directPace']]) : -1;
            if ($pace == -1) $pace = $speed > 0 ? round(1 / $speed * 60.0, 2) : 0;

            $directLatitude = isset($measurements['directLatitude']) ? $waypoints[$i][$measurements['directLatitude']] : null;
            $directLongitude = isset($measurements['directLongitude']) ? $waypoints[$i][$measurements['directLongitude']] : null;
                        
            /*
            04/06/2015 / FMO : on ne peut pas se fier au champ directTimestamp !!!
            $wp1 = array(
                'timestamp' => new \DateTime(date("Y-m-d H:i:s", $waypoints[$i][$measurements['directTimestamp']]))
            );
            if ($i == $numWaypoints - 1) { // traitement spécifique pour le dernier point
                $wp2 = $wp1;
            } else {
                $wp2 = array(
                    'timestamp' => new \DateTime(date("Y-m-d H:i:s", $waypoints[$i][$measurements['directTimestamp']]))
                );
            }
            var_dump(date("Y-m-d H:i:s", $waypoints[$i][$measurements['directTimestamp']]));
            $time1 = $wp1['timestamp']->format('H') * 3600 + $wp1['timestamp']->format('i') * 60 + $wp1['timestamp']->format('s');
            $time2 = $wp2['timestamp']->format('H') * 3600 + $wp2['timestamp']->format('i') * 60 + $wp2['timestamp']->format('s');

            $interval           = $time2 - $time1;
            var_dump($interval);
            */
            
            if ($directLatitude != 0 && $directLongitude !=0) {
                $waypointsToReturn[] = $this->getFormatWaypoint(
                    $directLatitude,
                    $directLongitude,
                    isset($measurements['directElevation']) ? round($waypoints[$i][$measurements['directElevation']]) : 0,// * 0.3048, // FIXME: conversion foot vers metre en dur
                    $waypoints[$i][$measurements['sumDistance']]/1000,// * 1.609,      // FIXME: conversion mile vers km en dur
                    $i > 0 ? $waypoints[$i][$measurements['sumDuration']] - $waypoints[$i - 1][$measurements['sumDuration']] : 0,
                    //$i > 0 ? $waypoints[$i][$measurements['directTimestamp']] - $waypoints[$i - 1][$measurements['directTimestamp']] : 0, FMO : y'a n'imp dans le champ de GARMIN !!
                    //$i > 0 ? $interval : 0,
                    $waypoints[$i][$measurements['sumMovingDuration']],
                    $speed,
                    $pace,
                    $heartRate,
                    null,
                    $pedalingFrequency,
                    $power
                );
            }
        }
        
        //var_dump($waypointsToReturn);exit;
        return array($waypointsToReturn, $issetHR, $issetPedalingFrequency, $issetPower, 1);
    }
    
    /**
     * 
     * @param array $activityInfos
     * @return type
     */
    protected function getFullWaypointsFromPolar(array $waypoints)
    {
        $waypointsToReturn  = array();
        $numWaypoints       = count($waypoints);
        $fullDistances      = 0.0;
        $fullDurationMoving = 0.0;
        
        var_dump(count($waypoints));exit;
        //2705 points
        
        for ($i = 0; $i < $numWaypoints; ++$i) {
            $wp1 = $waypoints[$i];
            if ($i == $numWaypoints - 1) { // traitement spécifique pour le dernier point
                $wp2 = $wp1;
            } else {
                $wp2 = $waypoints[$i + 1];
            }
            $distance           = $this->haversineDistanceBetweenWaypoints($wp2, $wp1);
            $interval           = $wp2['timestamp'] - $wp1['timestamp'];
            $fullDistances      += $distance;
            $fullDurationMoving += $interval;
            $speed              = $interval > 0 ? round(($distance / $interval) * 3600, 2) : 0;
            $pace               = $speed > 0    ? round(1 / $speed * 60.0, 2) : 0;
            $heartRate          = '';
            $waypointsToReturn[] = $this->getFormatWaypoint(
                $wp1['timestamp'],
                $waypoints[$i]['lat'],
                $waypoints[$i]['lon'],
                (int)$waypoints[$i]['ele'],
                round((float)$fullDistances, 2),
                $interval,
                $fullDurationMoving,
                $speed,
                $pace,
                $heartRate
            );
        }
        
        return $waypointsToReturn;
    }
    
    /**
     *
     * @param array $activityInfos
     * @return array 
     */
    protected function getFullWaypointsFromSuunto(array $activityInfos)
    {
        $waypointsToReturn  = array();
        $fullWaypoints      = $activityInfos['trackPoints']; // FMO : contient X, Y et le temps
        $fullDistances      = 0.0;
        $fullDurationMoving = 0.0;
        
        //var_dump("------------- buildJsonToSave => traitement trackPoints");
        if ( isset($fullWaypoints)) {
            $waypoints = array();
            $numFullWaypoints = count($fullWaypoints);
            $iStep = round($numFullWaypoints / 1000);
            if ($iStep == 0) $iStep = 1;
            for ($i = 0; $i < $numFullWaypoints; ++$i) {
                if ($i % $iStep == 0) {
                    $waypoints[] = $fullWaypoints[$i];
                }
            }
            
            $numWaypoints = count($waypoints);
            for ($i = 0; $i < $numWaypoints; ++$i) {
                $wp1 = array(
                    'lat'  => $waypoints[$i]['Latitude'],
                    'lon' => $waypoints[$i]['Longitude'],
                    'timestamp' => new \DateTime(date("Y-m-d H:i:s", strtotime($waypoints[$i]['LocalTime'])))
                );
                if ($i == $numWaypoints - 1) { // traitement spécifique pour le dernier point
                    $wp2 = $wp1;
                } else {
                    $wp2 = array(
                        'lat'  => $waypoints[$i + 1]['Latitude'],
                        'lon' => $waypoints[$i + 1]['Longitude'],
                        'timestamp' => new \DateTime(date("Y-m-d H:i:s", strtotime($waypoints[$i + 1]['LocalTime'])))
                    );
                }

                //var_dump($wp1['timestamp']->format("Y-m-d H:i:s"));
                $time1 = $wp1['timestamp']->format('H') * 3600 + $wp1['timestamp']->format('i') * 60 + $wp1['timestamp']->format('s');
                $time2 = $wp2['timestamp']->format('H') * 3600 + $wp2['timestamp']->format('i') * 60 + $wp2['timestamp']->format('s');

                $distance           = $this->haversineDistanceBetweenWaypoints($wp2, $wp1);
                $interval           = $time2 - $time1;
                $fullDistances      += $distance;
                
                if ($interval >0) { //Pour gérer le cas d'une activité qui commence un jour et fini sur le lendemain
                    $fullDurationMoving += $interval;
                    $speed              = $interval > 0 ? round(($distance / $interval) * 3600, 2) : 0;
                    $pace               = $speed > 0    ? round(1 / $speed * 60.0, 2) : 0;

                    $altitude = 0;//$this->getElevationFromSRTM($waypoints[$i]['Latitude'], $waypoints[$i]['Longitude'], 0);

                    //var_dump("time/interval/lat/lon/alt/speed/pace=".$waypoints[$i]['LocalTime']."/".$interval."/".$waypoints[$i]['Latitude']."/".$waypoints[$i]['Longitude']."/".$altitude."/".$speed."/".$pace);

                    $waypointsToReturn[] = $this->getFormatWaypoint(
                        $waypoints[$i]['Latitude'],
                        $waypoints[$i]['Longitude'],
                        $altitude,
                        round((float)$fullDistances, 2),
                        $interval,
                        $fullDurationMoving,
                        $speed,
                        $pace
                    );
                }
            }
        }
        return $waypointsToReturn;
    }
    
    /**
     *
     * @param array $activityInfos
     * @return array 
     */
    protected function getFullSamplesFromSuunto(array $activityInfos)
    {
        $samplesToReturn    = array();
        $fullSamples        = $activityInfos['sampleSets'];// FMO : contient Z, HR, température et le temps (merci SUUNTO de faire ça en 2 tableaux non liés !)
        $fullDurationMoving = 0.0;
        
        //var_dump("------------- buildJsonToSave => traitement sampleSets");
        //var_dump($fullSamples);exit;
        if ( isset($fullSamples)) {
            $samples = array();
            $numFullSamples = count($fullSamples);
            $iStep = round($numFullSamples / 1000);
            if ($iStep == 0) $iStep = 1;
            for ($i = 0; $i < $numFullSamples; ++$i) {
                if ($i % $iStep == 0) {
                    $samples[] = $fullSamples[$i];
                }
            }
            $numSamples = count($samples);
            for ($i = 0; $i < $numSamples; ++$i) {
                $wp1 = array(
                    'timestamp' => new \DateTime(date("Y-m-d H:i:s", strtotime($samples[$i]['LocalTime'])))
                );
                if ($i == $numSamples - 1) { // traitement spécifique pour le dernier point
                    $wp2 = $wp1;
                } else {
                    $wp2 = array(
                        'timestamp' => new \DateTime(date("Y-m-d H:i:s", strtotime($samples[$i + 1]['LocalTime'])))
                    );
                }

                $time1 = $wp1['timestamp']->format('H') * 3600 + $wp1['timestamp']->format('i') * 60 + $wp1['timestamp']->format('s');
                $time2 = $wp2['timestamp']->format('H') * 3600 + $wp2['timestamp']->format('i') * 60 + $wp2['timestamp']->format('s');
                $interval           = $time2 - $time1;
                
                if ($interval >0) { //Pour gérer le cas d'une activité qui commence un jour et fini sur le lendemain
                   $fullDurationMoving += $interval;

                    //var_dump("temps/interval/alt/hr/temp=".$samples[$i]['LocalTime']."/".$fullDurationMoving."/".$samples[$i]['Altitude']."/".$samples[$i]['HeartRate']."/".$samples[$i]['Temperature']);
                    $samplesToReturn[] = $this->getFormatWaypoint(
                        null,
                        null,
                        $samples[$i]['Altitude'],
                        $samples[$i]['Distance']/1000,
                        $interval,
                        $fullDurationMoving,
                        $samples[$i]['Speed']*1.609344,//1 mile = 1.609344 Km
                        null,
                        $samples[$i]['HeartRate'],
                        $samples[$i]['Temperature']
                    );
                }
            }
        }
        
        return $samplesToReturn;
    }
    
    /**
     * 
     * @param type $tcxFileName
     * @throws \Exception
     */
    protected function getFullWaypointsAndStartDateFromTcx($tcxFileName)
    {
        $waypointsToReturn = array();
                
        if (!file_exists($tcxFileName)) {
            throw new \Exception('Fichier tcx garmin non trouvé : '.$tcxFileName);
        }
        
        $xml        = simplexml_load_file($tcxFileName, 'SimpleXMLElement', LIBXML_COMPACT);
        $activity   = $xml->Activities->Activity;
        $startDate  = new \DateTime($activity->Id);
        $fullDurationMoving = $fullDistance = $calories = 0.0;
        
        $distanceSinceStart = $timeSinceStartInSec = 0.0;
        $prevTrackpointTime = $prevTrackpointDistance = null;
        foreach ($activity->Lap as $lap) {
            $fullDistance       += (float)$lap->DistanceMeters;
            $fullDurationMoving += (float)$lap->TotalTimeSeconds;
            $calories           += (int)$lap->Calories;
            foreach ($lap->Track as $track) {
                foreach ($track->Trackpoint as $trackpoint) {
                    if (!isset($trackpoint->DistanceMeters) || !isset($trackpoint->Position)) {
                        // FIXME: problématique pour les fichiers sans données GPS
                        continue;
                    }
                    $curTrackpointTime      = new \DateTime($trackpoint->Time);
                    $timeIntervalInSec      = $prevTrackpointTime != null ?
                        $curTrackpointTime->getTimestamp() - $prevTrackpointTime->getTimestamp()
                        : 0;
                    $curTrackpointDistance  = (float)$trackpoint->DistanceMeters;
                    $distanceInterval       = $prevTrackpointDistance != null ?
                        ($curTrackpointDistance - $prevTrackpointDistance) / 1000.0
                        : 0.0;
                    $timeSinceStartInSec    += $timeIntervalInSec;
                    $distanceSinceStart     += $distanceInterval;
                    $speed                  =  $timeIntervalInSec > 0 ? round(($distanceInterval / $timeIntervalInSec) * 3600, 2) : 0;
                    $pace                   = $speed > 0 ? round((1 / $speed) * 60.0, 2) : 0;
                    $waypointsToReturn[]    = $this->getFormatWaypoint(
                        (float)$trackpoint->Position->LatitudeDegrees,
                        (float)$trackpoint->Position->LongitudeDegrees,
                        (int)$trackpoint->AltitudeMeters,
                        $distanceSinceStart,
                        (int)$timeIntervalInSec,
                        (int)$timeSinceStartInSec,
                        (float)$speed,
                        (float)$pace
                        //$heartRate
                    );
                    $prevTrackpointTime     = $curTrackpointTime;
                    $prevTrackpointDistance = $curTrackpointDistance;
                }
            }
        }
        
        return array($waypointsToReturn, $startDate);
    }
    
    /**
     *
     * @param string $gpx
     * @return array
     */
    protected function getFullWaypointsAndStartDateFromGpx($gpx, $checkboxSRTM) {
        //On enlève la limite d'execution de 30 secondes pour la passer à 10 minutes
        set_time_limit(600);
        
        $waypointsToReturn = array();
        
        //var_dump($gpx);
        
        if (is_integer($gpx)) {
            //Mode xml (cas de POLAR)
            $em = $this->doctrine->getEntityManager();
            $activityToSyncRep = $em->getRepository('KsActivityBundle:ActivityToSync');
            $activity = $activityToSyncRep->findOneBy(array("id_website_activity_service" => $gpx));
            $activityDetails = json_decode($activity->getSourceDetailsActivity(), true);
            //var_dump($activityDetails['filename']);exit;
            $xml = simplexml_load_string($activityDetails['filename']);
        }
        else if (file_exists($gpx)) {
            //Mode fichier (cas de l'import par fichier GPX)
            $xml = simplexml_load_file($gpx, 'SimpleXMLElement', LIBXML_COMPACT);
        }
        else {    
            //Cas du fichier gpx issu d'openrunner pour les compétitions synchronisées par RUNRAID
            $xml = simplexml_load_string($gpx);
//            if (!file_exists($gpx)) {
//                throw new \Exception('Fichier gpx non trouvé : '.$gpx);
//            }
        }
        
        $fullDistances      = 0.0;
        $fullDurationMoving = 0.0;
        $fullDurationOnActivity = 0.0; //TODO : afficher la durée totale + durée en activité
        $heartRates         = array();
        
        $startDate = null;
        //FMO : bug récupération de la date sur certains fichiers, plus sur de prendre le 1er élément des données
        //$startDate      = new \DateTime($xml->metadata->time);
        
        $heartRateArray = null;
        $heartRate = null;
        $issetHR = false;
        
        $temperatureArray = null;
        $temperature = null;
        $issetTemperatures  = false;
        
        //Récupération des hr :
        foreach( (($tmp = @$xml->xpath('//gpxdata:hr')) ? $tmp : array()) as $hr ) {
            //Si SUUNTO on est là
            $heartRateArray[] = $hr;
        }
        
        if (!isset($heartRateArray)) {
            //Si GARMIN on est là
            foreach( (($tmp = @$xml->xpath('//tp1:hr')) ? $tmp : array()) as $hr ) {
                $heartRateArray[] = $hr;
            }
        }
        
        //Récupération des températures
        foreach( (($tmp = @$xml->xpath('//gpxdata:temp')) ? $tmp : array()) as $temp ) {
            //Si SUUNTO on est là
            $temperatureArray[] = $temp;
        }
        
        $numSegments    = count($xml->trk->trkseg);

        //var_dump($numSegments);
        
        for ($seg = 0; $seg < $numSegments; ++$seg) {
            $segment        = $xml->trk->trkseg[$seg];
            $numTrackpoints = count($segment->trkpt);
            
            //var_dump($numTrackpoints);
            
            //FMO : pour les gros fichiers on prend moins de segments que le fichier ne contient sinon c'est trop long...
            // Pour un fichier de 10Mo, soit environ 66000 trkpt, le coeff vaut 66, donc au lieu d'1 tag/sec, on ne prend en compte qu'1 tag/min environ
            $coeff = ceil($numTrackpoints/1000);
            
            //var_dump($coeff);
            $changeCurPt = false;
            $curPt = $segment->trkpt[0];
            $nexPt = null;
            
            for ($i = 0; $i < $numTrackpoints; ++$i) {
                //var_dump($i);
                if ($changeCurPt && $i>0) {
                    $curPt = $segment->trkpt[$i];
                }
                
                if ($seg == ($numSegments - 1) && ($i == $numTrackpoints - 1)) {
                    $nextPt = $curPt;
                } else if ($i == $numTrackpoints - 1) {
                    $nextPt = $xml->trk->trkseg[$seg + 1]->trkpt[0];
                } else {
                    $nextPt = $segment->trkpt[$i + 1];
                }

                if ($i  % $coeff != 0 || $i == 0 ) {
                    $changeCurPt = false;
                }
                else {
                    if ($nextPt != null) { //FMO : $nextPt != null à laisser pour le cas du bug d'un segment <trkseg></trkseg> sans <trkpt> ! $i % $coeff
                        $changeCurPt = true;

                        $distance           = $this->haversineDistanceBetweenWaypoints(
                            array('lat' => (float)$curPt['lat'], 'lon' => (float)$curPt['lon']),
                            array('lat' => (float)$nextPt['lat'], 'lon' => (float)$nextPt['lon'])
                        );
                        //var_dump($curPt);
                        //var_dump($nextPt);
                        //var_dump($distance);

                        if ($startDate == null) {
                            $startDate = new \DateTime($curPt->time);
                        }

                        $wp1On              = new \DateTime($curPt->time);
                        $wp2On              = new \DateTime($nextPt->time);
                        $interval           = $wp2On->getTimestamp() - $wp1On->getTimestamp();
                        $fullDistances      += $distance;
                        $fullDurationMoving += $interval;
                        $fullDurationOnActivity       += $interval;
                        if (($seg % $numSegments) == 0) {
                            //var_dump($seg);
                            //var_dump($numSegments);
                            $fullDurationOnActivity   -= $interval;
                        }
                        $speed              = $interval > 0 ? round(($distance / $interval) * 3600, 2) : 0;
                        $pace               = $speed > 0    ? round((1 / $speed) * 60.0, 2) : 0;

                        //On cherche les données complémentaires
                        if(isset( $curPt->extensions)) {
                            //Fréquence cardiaque
                            $heartRate = (string)$heartRateArray[$i];
                            if (isset($heartRate) && $heartRate != null) {
                                $issetHR = true;
                            }
                            
                            //Température
                            $temperature = (string)$temperatureArray[$i];
                            if (isset($temperature) && $temperature != null) {
                                $issetTemperatures = true;
                            }
                        }
                        
                        $waypointsToReturn[] = $this->getFormatWaypoint(
                            (float)$curPt['lat'],
                            (float)$curPt['lon'],
                            ($checkboxSRTM === 'true' ? $this->getElevationFromSRTM((float)$curPt['lat'], (float)$curPt['lon'], (int)$curPt->ele) : (int)$curPt->ele), ////FMO récupération de l'altitude en fonction des données topographiques SRTM
                            round((float)$fullDistances, 4),
                            (int)$interval,
                            (int)$fullDurationMoving,
                            (float)$speed,
                            (float)$pace,
                            (float)$heartRate,
                            (float)$temperature
                        );
                    }
                }
            }
        }
        return array($waypointsToReturn, $startDate, $issetHR, $issetTemperatures); // récupérer la date de début sans passer par waypoints[0]
    }
    
    /**
     *
     * @param array $activityInfos
     * @return array 
     */
    public function getFullLapsFromSuunto(array $activityInfos)
    {
        $lapsToReturn       = array();
        $fullLaps           = $activityInfos['laps']; // FMO : contient les laps éventuellement saisi par le sportif
        $fullDistances      = 0.0;
        $fullDurationMoving = 0.0;
        if ( isset($fullLaps)) {
            $laps = array();
            $numFullLaps = count($fullLaps);
            $iStep = round($numFullLaps / 1000);
            if ($iStep == 0) $iStep = 1;
            for ($i = 0; $i < $numFullLaps; ++$i) {
                if ($i % $iStep == 0) {
                    $laps[] = $fullLaps[$i];
                }
            }
            
            $numLaps = count($laps);
            for ($i = 0; $i < $numLaps; ++$i) {
                if ($laps[$i]['Distance'] !=0) 
                    $lapsToReturn[] = $this->getFormatLap(
                        $laps[$i]['Duration'],
                        $laps[$i]['Distance']/1000,
                        $laps[$i]['Ascent'],
                        $laps[$i]['Descent'],
                        $laps[$i]['AvgSpeed'] * 3.6, //FMO : m/s en km/h
                        $laps[$i]['MinSpeed'] * 3.6,
                        $laps[$i]['MaxSpeed'] * 3.6,
                        $laps[$i]['AvgHR'],
                        $laps[$i]['MinHR'],
                        $laps[$i]['MaxHR']
                    );
            }
        }
        //var_dump($lapsToReturn);exit;
        
        return $lapsToReturn;
    }
    
    protected function getElevationFromSRTM($latitude, $longitude, $default) {
        $url = 'http://api.geonames.org/srtm3JSON';
        
        $params = array(
            'username' => 'ziiicos',
            'lat'     => $latitude,
            'lng'     => $longitude
        );
        
        $url .= '?'.http_build_query($params);
        
        //var_dump($url);
        
        $options = array(
            CURLOPT_URL            	=> $url,
            CURLOPT_RETURNTRANSFER 	=> true,       // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
            CURLOPT_HEADER         	=> false,      // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
            CURLOPT_HTTPHEADER		=> array("Content-type: application/json"),
            CURLOPT_FAILONERROR         => true,       // Gestion des codes d'erreur HTTP supérieurs ou égaux à 400
            CURLOPT_POST                => false,       // Effectuer une requête de type GET
            CURLOPT_HTTPGET             => true,
            //CURLOPT_POSTFIELDS            => $postFields // Le tableau associatif contenant les variables envoyées par POST au serveur
	);
                
	//open connection
	$curl = curl_init();
    
	curl_setopt_array($curl, $options);
        
	$response 	= curl_exec($curl);
	$responsecode 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	//var_dump($response, $responsecode);
        
	if (curl_errno($curl)) {
		// Le message d'erreur correspondant est affiché
		var_dump("ERREUR curl_exec : ".curl_error($curl));
	}
        
	//close connection
	curl_close($curl);
        
        //var_dump("responsecode=".$responsecode);
        //var_dump("response=".$response);
        
        if ($responsecode === 200) {
            $response       = htmlentities($response,ENT_NOQUOTES);
            $decoderesponse = json_decode($response, true);
            //var_dump("decoderesponse['srtm3']=".$decoderesponse['srtm3']);
            
            if (isset($decoderesponse['srtm3']) && $decoderesponse['srtm3'] != null) {
                return $decoderesponse['srtm3'] <0 ? 0 : $decoderesponse['srtm3'];
            }
            else return 0;
        } elseif (in_array($responsecode, array('201','204','301','304'))) {
            //$this->api_request_log[] = array('name' => $name, 'type' => $type, 'result' => $responsecoce, 'time' => microtime(true)-$orig);
            return $default;
        } else {
            //return $response;
            // $this->api_last_error = "doRunkeeperRequest: request error => 'name' : ".$name.", 'type' : ".$type.", 'result' : ".$responsecode.", '".$name."' => ".$url;
            // $this->api_request_log[] = array('name' => $name, 'type' => $type, 'result' => 'error : '.$responsecode, 'time' => microtime(true)-$orig);
            return $default;
        }
    }
    
    /**
     * Calcul de la distance entre 2 points gps (coordonnées exprimées en degrés)
     * NOTE CF: beaucoup moins précise que la fonction haversine, même pour les petites distances :(
     * 
     * @param array $waypoint1
     * @param array $waypoint2
     * @return float Distance entre $waypoint1 et $waypoint2 en m
     */
    protected function distanceBetweenWaypoints(array $waypoint1, array $waypoint2) 
    {
        $lat1 = $this->degreeToRadian( $waypoint1['lat'] );
        $lat2 = $this->degreeToRadian( $waypoint2['lat'] );
        $lon1 = $this->degreeToRadian( $waypoint1['lon'] );
        $lon2 = $this->degreeToRadian( $waypoint2['lon'] );
        
        $x = ( $lon2 - $lon1 ) * cos( ( $lat2 - $lat1 ) / 2 );
        $y = ( $lat2 - $lat1 );
        $distance = sqrt( $x * $x + $y * $y ) * self::EARTH_RADIUS;

        return $distance;
    }
    
    /**
     * Calcul précis pour obtenir la distance entre 2 coordonnées géographiques
     * lat et lon sont exprimés en degrés
     * 
     * @param array $waypoint1
     * @param array $waypoint2
     * @return float Distance entre $waypoint1 et $waypoint2 en m
     */
    protected function haversineDistanceBetweenWaypoints(array $waypoint1, array $waypoint2)
    {        
        $dLat   = $this->degreeToRadian($waypoint2['lat'] - $waypoint1['lat']);
        $dLon   = $this->degreeToRadian($waypoint2['lon'] - $waypoint1['lon']);
        $lat1   = $this->degreeToRadian($waypoint1['lat']);
        $lat2   = $this->degreeToRadian($waypoint2['lat']);

        $a = sin($dLat/2.0) * sin($dLat/2.0) +
            sin($dLon/2.0) * sin($dLon/2.0) * cos($lat1) * cos($lat2); 
        $c = 2.0 * atan2(sqrt($a), sqrt(1.0-$a)); 
        $d = self::EARTH_RADIUS * $c;

        return $d;
    }
    
    /**
     * @param type $waypoints
     */
    protected function compute($user, array $waypoints)
    {
        $em    = $this->doctrine->getEntityManager();
        $preferenceRep      = $em->getRepository('KsUserBundle:Preference');
        $preferenceTypeRep  = $em->getRepository('KsUserBundle:PreferenceType');
        
        $compute = array(
            '+'         => 0,
            '-'         => 0,
            'minEle'    => 99999,
            'maxEle'    => -99999,
            'minTemp'   => 99999,
            'maxTemp'   => -99999,
            'minHR'     => 99999,
            'maxHR'     => -99999,
            'minPower'  => 99999,
            'maxPower'  => -99999,
            'minPF'     => 99999,
            'maxPF'     => -99999,
            'HRZones'   => array()
        );
        
        $HRZones = array();
        $HRZonesPreference = $preferenceTypeRep->findOneByCode("hr");
        $preferences = $preferenceRep->findBy(array("preferenceType" => $HRZonesPreference->getId()));
        foreach($preferences as $key => $preference) {
            $compute['HRZones'][$preference->getCode()] = array("val1"  => $preference->getVal1(),
                                                                "val2"  => $preference->getVal2(),
                                                                "duration" => 0);
        }
        $numWaypoints = count($waypoints);
        if ( $numWaypoints > 0 ) {
            //Une première fois pour déterminer le seuil $threshold
            $eleMin = 99999;
            $eleMax = -99999;
            
            $HRRest = $user->getUserDetail()->getHRRest();
            $HRMax = $user->getUserDetail()->getHRMax();
            
            foreach ($waypoints as $waypoint) {
                //var_dump($waypoint);
                if ($waypoint["temperature"] < $compute['minTemp']) {
                    $compute['minTemp'] = $waypoint["temperature"];
                }
                if ($waypoint["temperature"] > $compute['maxTemp']) {
                    $compute['maxTemp'] = $waypoint["temperature"];
                }
                if (!is_null($waypoint["pedalingFrequency"])) {
                    if ($waypoint["pedalingFrequency"] < $compute['minPF']) {
                        $compute['minPF'] = $waypoint["pedalingFrequency"];
                    }
                    if ($waypoint["pedalingFrequency"] > $compute['maxPF']) {
                        $compute['maxPF'] = $waypoint["pedalingFrequency"];
                    }
                }
                if (!is_null($waypoint["power"])) {
                    if ($waypoint["power"] < $compute['minPower']) {
                        $compute['minPower'] = $waypoint["power"];
                    }
                    if ($waypoint["power"] > $compute['maxPower']) {
                        $compute['maxPower'] = $waypoint["power"];
                    }
                }
                
                //On garde une élévation de référence pour ne pas prendre en compte les petites erreurs de dénivellés
                if ($waypoint["ele"] < $eleMin) {
                    $eleMin = $waypoint["ele"];
                }
                if ($waypoint["ele"] > $eleMax) {
                    $eleMax = $waypoint["ele"];
                }
                if (!is_null($waypoint["heartRate"])) {
                    if ($waypoint["heartRate"] < $compute['minHR']) $compute['minHR'] = $waypoint["heartRate"];
                    if ($waypoint["heartRate"] > $compute['maxHR']) $compute['maxHR'] = $waypoint["heartRate"];
                }
                
                is_null($HRRest) ? 0 : $HRRest;
                is_null($HRMax) ? 0 : $HRMax;
                if ($HRRest != 0 && $HRMax !=0) {
                    foreach($preferences as $key => $preference) {
                        if (is_null($preference->getVal1()) && is_null($preference->getVal2()) && is_null($waypoint["heartRate"])) $compute['HRZones'][$preference->getCode()]["duration"] += $waypoint['interval'];
                        else if ($HRRest + $preference->getVal1() /100 * ($HRMax - $HRRest) <= $waypoint["heartRate"] && $waypoint["heartRate"] < $HRRest + $preference->getVal2() /100 * ($HRMax - $HRRest)) {
                            $compute['HRZones'][$preference->getCode()]["duration"] += $waypoint['interval']; 
                        }
                    }
                }
            }
            
            //Seuil à dépasser pour prendre en compte la mesure
            $threshold = ($eleMax - $eleMin) > 50 ? 10 : 5; 
            
            $eleRef = $waypoints[0]["ele"];

            foreach ($waypoints as $waypoint) {
                if ( ( $waypoint["ele"] - $eleRef ) > $threshold ) {
                    $compute['+'] += $waypoint["ele"] - $eleRef;
                    $eleRef = $waypoint["ele"];
                };
                if ( ( $eleRef - $waypoint["ele"] ) > $threshold ) {
                    $compute['-'] += $eleRef - $waypoint["ele"];
                    $eleRef = $waypoint["ele"];
                };
                            
                if (!is_null($waypoint["ele"]) && $waypoint["ele"] < $compute['minEle']) {
                    $compute['minEle'] = $waypoint["ele"];
                }
                if (!is_null($waypoint["ele"]) && $waypoint["ele"] > $compute['maxEle']) {
                    $compute['maxEle'] = $waypoint["ele"];
                }
            }
        } else {
            $temp['minTemp'] = $temp['maxTemp'] = 0;
            $compute['minEle'] = $compute['maxEle'] = 0;
        }
        
        return $compute;
    }
    
    /**
     * On récupère le premier waypoint du tableau json déjà formaté (identique à tous les services)
     * On cherche le premier waypoint non vide
     * 
     * @param array $trackingDatas
     * @return waypoint 
     */
    public function getFirstWaypointNotEmpty(array $trackingDatas)
    {
        if (isset($trackingDatas['waypoints']) && count($trackingDatas['waypoints']) > 0) {
            foreach ($trackingDatas['waypoints'] as $waypoint) {
                if ($waypoint['lat'] != 0 && $waypoint['lon'] != 0) {
                    return $waypoint;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Interroge l'api google map et essaye d'otenir le lieu
     * 
     * @param float $lat, float $lon
     * @return Place or null 
     */
     public function findPlace($lat, $lon)
    {
        $em    = $this->doctrine->getEntityManager();
        //$url = 'http://maps.google.com/maps/geo?q=' . $lat .',' . $lon .'&output=json&sensor=false';
        $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' . $lat .',' . $lon .'&sensor=false';

        // make the HTTP request
        $data = @file_get_contents($url);
        // parse the json response
        $jsondata = json_decode($data, true);
        
        /*if(is_array($jsondata )&& $jsondata['Status']['code']==200)
        {
              $full_address = $jsondata['Placemark'][0]['address'];
              $country_code = $jsondata['Placemark'][0]['AddressDetails']["Country"]["CountryNameCode"];
              $country_area = null;
              $town = null;
              
              if( isset( $jsondata['Placemark'][0]['AddressDetails']["Country"]['AdministrativeArea']["SubAdministrativeArea"]['SubAdministrativeAreaName'] )) {
                $country_area = $jsondata['Placemark'][0]['AddressDetails']["Country"]['AdministrativeArea']["SubAdministrativeArea"]['SubAdministrativeAreaName'];
              } 
              if( isset( $jsondata['Placemark'][0]['AddressDetails']["Country"]['AdministrativeArea']["SubAdministrativeArea"]["Locality"]["LocalityName"] )) {
                $town = $jsondata['Placemark'][0]['AddressDetails']["Country"]['AdministrativeArea']["SubAdministrativeArea"]["Locality"]["LocalityName"]; 
              } 
              
              
              $longitude = $jsondata['Placemark'][0]["Point"]["coordinates"][0];
              $latitude = $jsondata['Placemark'][0]["Point"]["coordinates"][1];
              
              $place = new \Ks\EventBundle\Entity\Place(); 
              $place->setFullAdress($full_address);
              $place->setCountryCode($country_code);
              $place->setRegionLabel($country_area);
              $place->setTownLabel($town);
              $place->setLongitude($longitude);
              $place->setLatitude($latitude);
              
              $em->persist($place);
              
              return $place;
        } else {
            return null;
        }
     }*/
        
        if(is_array($jsondata ) && $jsondata['status'] == "OK" && isset ( $jsondata["results"][0] ))
        {
            //var_dump($jsondata["results"]);
            $result = $jsondata["results"][0];
            $adress = $result["address_components"];

            //On prend juste en compte le premier résultat
            //$adress = $arrayAdressComponents[0];

            $adressInfos = array(
                "fullAdress" => $result["formatted_address"],
                //Latitude et longitude
                "latitude"  => $result["geometry"]["location"]["lat"],
                "longitude" => $result["geometry"]["location"]["lng"],
                //ville
                "townCode" => "",
                "townLabel" => "",
                //département
                "countyCode" => "",
                "countyLabel" => "",
                //région
                "regionCode" => "",
                "regionLabel" => "",
                //pays
                "countryCode" => "",
                "countryLabel" => ""
            );

            foreach( $adress as $value) {
                //Récupération de la ville
                if( $value["types"][0] == "locality" ){

                    if( $value["long_name"] != "" ){
                        $adressInfos["townLabel"] = $value["long_name"];
                    }

                    if( $value["short_name"] != "" ){
                        $adressInfos["townCode"] = $value["short_name"];
                    }
                }

                //Récupération de la région
                if( $value["types"][0] == "administrative_area_level_1" ){
                    if( $value["long_name"] != "" ){
                        $adressInfos["regionLabel"] = $value["long_name"];
                    }
                    if( $value["short_name"] != "" ){
                        $adressInfos["regionCode"] = $value["short_name"];
                    }
                }

                //Récupération du département
                if( $value["types"][0] == "administrative_area_level_2" ){
                    if( $value["long_name"] != "" ){
                        $adressInfos["countyLabel"] = $value["long_name"];
                    }
                    if( $value["short_name"] != "" ){
                        $adressInfos["countyCode"] = $value["short_name"];
                    }
                }

                if( $value["types"][0] == "country" ){ 
                    if( $value["long_name"] != "" ){
                        $adressInfos["countryLabel"] = $value["long_name"];
                    }

                    if( $value["short_name"] != "" ) {
                        $adressInfos["countryCode"] = $value["short_name"];
                    }
                }
            }

            $place = new \Ks\EventBundle\Entity\Place(); 
            $place->setFullAdress($adressInfos["fullAdress"]);
            $place->setCountryCode($adressInfos["countryCode"]);
            $place->setCountryLabel($adressInfos["countryLabel"]);
            $place->setRegionCode($adressInfos["regionCode"]);
            $place->setRegionLabel($adressInfos["regionLabel"]);
            $place->setCountyCode($adressInfos["countyCode"]);
            $place->setCountyLabel($adressInfos["countyLabel"]);
            $place->setTownCode($adressInfos["townCode"]);
            $place->setTownLabel($adressInfos["townLabel"]);
            $place->setLongitude($adressInfos["longitude"]);
            $place->setLatitude($adressInfos["latitude"]);

            $em->persist($place);
              
            return $place;
        } else {
            return null;
        }
    }
        
    
     /**
     * Lissage tableau
      * NOTE CF: les résultats semblent meilleurs avec la moyenne mobile qu'avec la médiane...
     * 
     * @param type $waypoints
     * @return type 
     */
    protected function sleekArray(array $waypoints, array $aThingsToSleeked)
    {
        $waypointsToReturn = array();
        
        for ($i = 0; $i < count($waypoints) ; ++$i) {
            foreach($waypoints[$i] as $key => $waypointData) {
                if(0&& in_array($key, $aThingsToSleeked) && isset($waypoints[$i - 2]) && isset($waypoints[$i + 2]) ) {
                    $movingMedian = array(
                        $waypoints[$i - 2][$key],
                        $waypoints[$i - 1][$key],
                        $waypoints[$i][$key],
                        $waypoints[$i - 1][$key],
                        $waypoints[$i + 2][$key]
                    );
                    //sort($movingMedian);
                    //$waypointsToReturn[$i][$key] = $movingMedian[2];
                    $waypointsToReturn[$i][$key] = array_sum($movingMedian) / count($movingMedian); // moyenne mobile
                    //$waypoints[$i][$key] = round(array_sum($movingMedian) / count($movingMedian), 2); // moyenne mobile avec reprise des données précédentes
                } else {
                    $waypointsToReturn[$i][$key] = $waypointData;
                }
            }
        }
        
        return $waypointsToReturn;
    }
    
    /**
     * Conversion degré en radian
     * 
     * @param float $degree
     * @return float 
     */
    protected static function degreeToRadian( $degree )
    {
        return $degree * pi() / 180.0;
    }
    
    public function getActivitiesToSyncFromService($code, \Ks\UserBundle\Entity\User $user) {
        if ($code == 1) return $this->getActivitiesToSyncFromRUNKEEPER($user);
        else if ($code == 3) return $this->getActivitiesToSyncFromNIKEPLUS($user);
        else if ($code == 4) return $this->getActivitiesToSyncFromENDOMONDO($user);
        else if ($code == 5) return $this->getActivitiesToSyncFromSUUNTO($user);
        else if ($code == 7) return $this->getActivitiesToSyncFromGARMIN($user);
        else if ($code == 8) return $this->getActivitiesToSyncFromPOLAR($user);
        else return null;
    }
    
    /**
     * 
     * @param \Ks\UserBundle\Entity\User $user
     */
    public function getActivitiesToSyncFromGARMIN(\Ks\UserBundle\Entity\User $user)
    {
        $em            = $this->doctrine->getEntityManager();
        $dbh           = $em->getConnection();
        $garminService = $em->getRepository('KsUserBundle:Service')->findOneByName('Garmin');
        $preferenceRep = $em->getRepository('KsUserBundle:Preference');
        $userService   = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array(
            'user'       => $user->getId(),
            'service'    => $garminService->getId()
        ));
        $credentials   = array(
            'username'   => $userService->getConnectionId(),
            'password'   => base64_decode($userService->getConnectionPassword()),
            'identifier' => microtime(true)
        );
        
        $activities = array();
        
        try {
            $garminApi     = new \dawguk\GarminConnect($credentials);
            $numActivities = $preferenceRep->findOneBy(array( // NOTE CF: encore ?!
                'code' => "garminSearchNumber"
            ))->getVal1();
            $results          = $garminApi->getActivityList(0, $numActivities);
            
            //var_dump($results);exit;
            
            $garminActivities = is_object($results) ? $results->results->activities : array();
            
            // Traitement des activités
            // On commence par récupérer toutes les uri d'activités RK déjà importées en bdd
            $query = 'select acfs.id_website_activity_service as uniqid'
                .' from ks_activity_come_from_service acfs'
                .' left join ks_activity a on (a.id = acfs.activity_id)'
                .' where acfs.service_id = :serviceId '
                .' and a.user_id = :userId';
            $res = $dbh->executeQuery($query, array(
                'serviceId' => $garminService->getId(),
                'userId'    => $user->getId()
            ));
            $activitiesAlreadyImported = array();
            foreach ($res as $val) {
                $activitiesAlreadyImported[$val['uniqid']] = true;
            }
            
            // on parcourt le tableau des uri récupérées sur RK
            foreach ($garminActivities as $syncActivity) {
                $activity = $syncActivity->activity;
                if (array_key_exists($activity->activityId, $activitiesAlreadyImported)) {
                    continue;
                }
                $activityDetail = array();
                
                $activityType = strtolower($activity->activityType->key);
                // if ($activityType !== 'running') {
                //     continue;
                // }
                
                $startDate = new \DateTime($activity->beginTimestamp->value);
                
                if (isset($activity->sumDistance)) {
                    $distance  = (float)$activity->sumDistance->value;
                    if ($activity->sumDistance->uom == 'kilometer') {
                    }
                    elseif ($activity->sumDistance->uom == 'mile') {
                        $distance *= 1.609;
                    }
                    elseif ($activity->sumDistance->uom == 'meter') {
                        $distance *= 1000;
                    }
                    else {
                        throw new \Exception('Uom non supportée pour la distance : '.$activity->sumDistance->uom);
                    }
                    if ((int)$distance == 0) {
                        continue;
                    }
                }
                else $distance = 0;
                
                if (isset($activity->gainElevation)) {
                    $elevationGain = (float)$activity->gainElevation->value;
                    if ($activity->gainElevation->uom == 'meter') {
                    }
                    elseif ($activity->gainElevation->uom == 'foot') {
                        $elevationGain *= 0.30480;
                    } else {
                        throw new \Exception('Uom non supportée pour le D+ : '.$activity->gainElevation->uom);
                    }
                }
                else $elevationGain = 0;
                
                if (isset($activity->lossElevation)) {
                    $elevationLoss = (float)$activity->lossElevation->value;
                    if ($activity->lossElevation->uom == 'meter') {
                    }
                    elseif ($activity->lossElevation->uom == 'foot') {
                        $elevationLoss *= 0.30480;
                    } else {
                        throw new \Exception('Uom non supportée pour le D- : '.$activity->lossElevation->uom);
                    }
                }
                else $elevationLoss = 0;
                
                if (isset($activity->sumElapsedDuration)) {
                    $elapsedDuration = $activity->sumElapsedDuration->value;
                    if ($activity->sumElapsedDuration->uom !== 'second') {
                        throw new \Exception('Uom non supportée pour la durée totale : '.$activity->sumElapsedDuration->uom);
                    }
                    //FMO : FIXME bizarrement sumMovingDuration existe bien mais fait planter le tout...
                    $movingDuration = $activity->sumElapsedDuration->value;
                    if ($activity->sumElapsedDuration->uom !== 'second') {
                        throw new \Exception('Uom non supportée pour la durée en mouvement : '.$activity->sumElapsedDuration->uom);
                    }
                }
                else {
                    $elapsedDuration =0;
                    $movingDuration =0;
                }
                
                $activityDetail['total_distance'] = round($distance, 3);
                $activityDetail['climb']          = round($elevationGain, 0);
                $activityDetail['descent']        = round($elevationLoss, 0);
                $activityDetail['start_time']     = $startDate->format("d-m-Y");
                $activityDetail['duration']       = $this->activityService->secondesToTimeDuration($movingDuration)->format("H:i:s");
                $activityDetail['total_duration'] = $this->activityService->secondesToTimeDuration($elapsedDuration)->format("H:i:s");
                
                $codeSport = $this->formatNameSport($activityType);
                $sport     = $em->getRepository('KsActivityBundle:Sport')->findOneByCodeSport($codeSport);
                if (is_null($sport)) $sport = $em->getRepository('KsActivityBundle:Sport')->findOneByCodeSport('other');
                $activityDetail['sport']  = $sport->getLabel();
                $activityDetail['codeSport']  = $sport->getCodeSport();
                
                $activityDetail['activityId']     = $activity->activityId;
                
                $activities[] = $activityDetail;
                
                //Sauvegarde des données récupérées pour utilisation lors de la création de l'activité si import validé
                // NOTE CF: j'ai l'impression que le "si import validé" n'est pas pris en compte ?!
                $acfs = new \Ks\ActivityBundle\Entity\ActivityToSync();
                $acfs->setUser($user);
                $acfs->setService($garminService);
                $acfs->setIdWebsiteActivityService($activityDetail['activityId']);
                $acfs->setSourceDetailsActivity(json_encode($activityDetail));
                $acfs->setTypeSource('JSON');

                $em->persist($acfs);
                $em->flush();
            }
            $userService->setStatus('done');
            $em->persist($userService);
            $em->flush();
        } catch (Exception $e) {
            var_dump($e->getMessage());
        };
        
        return $activities;
    }
    
    /**
     * 
     * @param \Ks\UserBundle\Entity\User $user
     * @return type
     */
    public function getActivitiesToSyncFromRUNKEEPER(\Ks\UserBundle\Entity\User $user)
    {
        $em             = $this->doctrine->getEntityManager();
        $dbh            = $em->getConnection();
        $code           = $em->getRepository('KsUserBundle:Service')->find(1);
        $preferenceRep  = $em->getRepository('KsUserBundle:Preference');
        
        $userService = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array("user" => $user->getId(), "service" => $code->getId()));
        //return $userServiceArray;
        $accessToken    = $userService->getToken();
        $this->_enduranceOnEarthType    = $em->getRepository('KsActivityBundle:SportType')->findOneByLabel("endurance");
        $this->_enduranceUnderWaterType = $em->getRepository('KsActivityBundle:SportType')->findOneByLabel("endurance_under_water");
        
        $lastSyncAt = $userService->getLastSyncAt();
        $lastSyncAt = $lastSyncAt != null ? $lastSyncAt->format("Y-m-d") : '1970-01-01';
        $rkApi      = $this->container->get('ks_user.runkeeper');
        
        $activities = array();
        
        try {
            $userService->setStatus('pending');
            $em->persist($userService);
            $em->flush();
            
            $rkApi->setAccessToken($accessToken);
            $runkeeperSearchNumber =$preferenceRep->findOneBy( array("code" => "runkeeperSearchNumber"))->getVal1();
            $rkActivities = $rkApi->getFitnessActivities(0, $runkeeperSearchNumber);
            
            //FMO : pour éviter tout problème avec les autres services on les désactive tous :
            $userHasOtherServices = $em->getRepository('KsUserBundle:UserHasServices')->findByUser($userService->getUser()->getId());
            foreach($userHasOtherServices as $userHasOtherService) {
                if ($userHasOtherService->getService()->getName() != 'Runkeeper') {
                    $userHasOtherService->setIsActive(false);
                    $em->persist($userHasOtherService);
                    $em->flush();
                }
            }
            
            // Traitement des activités
            // On commence par récupérer toutes les uri d'activités RK déjà importées en bdd
            $query = 'select acfs.id_website_activity_service as uniqid'
                .' from ks_activity_come_from_service acfs'
                .' left join ks_activity a on (a.id = acfs.activity_id)'
                .' where acfs.service_id = :serviceId '
                .' and a.user_id = :userId';
            $res = $dbh->executeQuery($query, array(
                'serviceId' => $userService->getService()->getId(),
                'userId'    => $userService->getUser()->getId()
            ));
            $activitiesAlreadyImported = array();
            foreach ($res as $val) {
                $activitiesAlreadyImported[$val['uniqid']] = true;
            }
            
            // on parcourt le tableau des uri récupérées sur RK
            foreach ($rkActivities as $syncActivity) {
                if (array_key_exists($syncActivity['uri'], $activitiesAlreadyImported)) {
                    //$output->writeln('Déja importé: '.$syncActivity['uri']);
                    continue;
                }
                $activityDetail = $rkApi->getFitnessActivity($syncActivity['uri']);
                $activityDetail = json_decode(utf8_encode($activityDetail), true);
                
                $startDate = new \DateTime($activityDetail['start_time']);
                $activityDetail['total_distance'] = round($activityDetail['total_distance']/1000, 3);
                if (!is_object($activityDetail['climb']) || is_null($activityDetail['climb'])) $activityDetail['climb'] = round($activityDetail['climb'], 0);
                $activityDetail['start_time'] = $startDate->format("d-m-Y");
                $activityDetail['duration'] = $this->activityService->secondesToTimeDuration($activityDetail['duration'])->format("H:i:s");
                $codeSport = $this->formatNameSport($syncActivity['type']);
                $sport = $em->getRepository('KsActivityBundle:Sport')->findOneByCodeSport($codeSport);
                $activityDetail['sport'] = $sport;
                $activities[] = $activityDetail;
                
                //Sauvegarde des données fetchées pour utilisation lors de la création de l'activité si import validé
                $acfs = new \Ks\ActivityBundle\Entity\ActivityToSync();
                $acfs->setUser($user);
                $acfs->setService($userService->getService());
                $acfs->setIdWebsiteActivityService($syncActivity['uri']);
                $acfs->setSourceDetailsActivity(json_encode($activityDetail));
                $acfs->setTypeSource('JSON');

                $em->persist($acfs);
                $em->flush();
                
                //$this->saveUserActivity($activityDetail, $userService);
            }
            
            //$userService->setLastSyncAt(new \DateTime('now'));
            $userService->setStatus('done');
            $em->persist($userService);
            $em->flush();
            
            return $activities;
            
        } catch (Exception $e) {
            //$output->writeln('Exception déclenchée: '.$e->getMessage());
            
            $userService->setStatus('done');
            $em->persist($userService);
            $em->flush();
        }
    }
    
    public function getActivitiesToSyncFromNIKEPLUS(\Ks\UserBundle\Entity\User $user) {
        $em             = $this->doctrine->getEntityManager();
        $service        = $em->getRepository('KsUserBundle:Service')->findOneByName('NikePlus');
        $dbh            = $em->getConnection();
        $preferenceRep  = $em->getRepository('KsUserBundle:Preference');
        
        $userService= $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array(
            'user'      => $user->getId(),
            'service'   => $service->getId(),
            'is_active' => 1
        ));
        
        $now = new \DateTime('Now');
        $now = $now->format('Y-m-d-h-i-s');
        
        $mailNike               = $userService->getConnectionId();
        $mdpMail                = base64_decode( $userService->getConnectionPassword() );
        $collectedActivities    = $userService->getCollectedActivities();
        $collectedActivities    = empty( $collectedActivities ) ? array() : json_decode( $collectedActivities ) ;
        $newCollectedActivitiesNumber = 0;
        
        $activities = array();
        
        try {
            $userService->setStatus('pending');
            $em->persist($userService);
            $em->flush();
            
            $nikePlus = new \Ks\ActivityBundle\Entity\NikePlusPHP( $mailNike, $mdpMail );
            //var_dump($nikePlus->loginCookies);exit;
            if (!is_null($nikePlus->loginCookies)) {
            
                //On récupère une liste des activités
                $nikeplusSearchNumber = $preferenceRep->findOneBy( array("code" => "nikeplusSearchNumber"))->getVal1();
                $nikeActivities = $nikePlus->activities($nikeplusSearchNumber, false);
                $activities = array();
                //var_dump($nikeActivities);exit;
                //var_dump( $nikePlus->activity("21329596000") );
                //var_dump($nikePlus->allTime());
                //var_dump($nikePlus->mostRecentActivity());

                // On commence par récupérer toutes les uri d'activités déjà importées en bdd
                $query = 'select acfs.id_website_activity_service as uniqid'
                    .' from ks_activity_come_from_service acfs'
                    .' left join ks_activity a on (a.id = acfs.activity_id)'
                    .' where acfs.service_id = :serviceId '
                    .' and a.user_id = :userId';
                $res = $dbh->executeQuery($query, array(
                    'serviceId' => $userService->getService()->getId(),
                    'userId'    => $userService->getUser()->getId()
                ));
                $activitiesAlreadyImported = array();
                foreach ($res as $key => $val) {
                    $activitiesAlreadyImported[$val['uniqid']] = true;
                }
            
                if( is_array( $nikeActivities ) ) {
                    //$output->writeln("Nombre d activites effectuees par l utilisateur depuis sa creation : ". count( array_keys( $activities ) )."");

                    foreach( array_keys( $nikeActivities ) as $activityId ) {

                        if (array_key_exists($activityId, $activitiesAlreadyImported)) {
                           //$output->writeln('Déja importé, id: '.$key);
                            continue;
                        }
                        else {
                            //var_dump($service->getId());
                            //var_dump($activityId);
                            //var_dump("Recuperation des infos sur l'activite ".$activityId."...");
                            $result = $nikePlus->activity( $activityId );

                            if( $result != null ) {
                                $activityDetail = (array)$result->activity;

                                $collectedActivities[] = $activityId;

                                $startDate = new \DateTime($activityDetail['startTimeUtc']);
                                $activityDetail['startTimeUtc'] = $startDate->format("d-m-Y");
                                $activityDetail['distance'] = round($activityDetail['distance'], 3);
                                $activityDetail['durationInMS'] = $activityDetail['duration'];
                                $activityDetail['duration'] = $this->activityService->millisecondesToTimeDuration($activityDetail['duration'])->format("H:i:s");
                                if(isset($activityDetail->calories)) $activityDetail['calories'] = $activityDetail->calories;
                                if(isset($activityDetail->tags->note)) $activityDetail['description'] = $activityDetail->tags->note;
                                if(isset( $activityDetail->geo)) {   
                                    //$ActivitySessionEnduranceOnEarth->setElevationMin($activityDetail->geo->elevationMin);
                                    //$ActivitySessionEnduranceOnEarth->setElevationMax($activityDetail->geo->elevationMax);
                                    $activityDetail['elevationLost'] = $activityDetail->geo->elevationLoss;
                                    $activityDetail['elevationGain'] = $activityDetail->geo->elevationGain;
                                }
                                else {
                                    $activityDetail['elevationLost'] = null;
                                    $activityDetail['elevationGain'] = null;
                                }

                                //var_dump($activityDetail); exit;
                                $activities[] = $activityDetail;

                                $newCollectedActivitiesNumber += 1;

                                //Sauvegarde des données fetchées pour utilisation lors de la création de l'activité si import validé
                                $acfs = new \Ks\ActivityBundle\Entity\ActivityToSync();
                                $acfs->setUser($user);
                                $acfs->setService($userService->getService());
                                $acfs->setIdWebsiteActivityService($activityId);
                                $acfs->setSourceDetailsActivity(json_encode($activityDetail));
                                $acfs->setTypeSource('JSON');

                                $em->persist($acfs);
                                $em->flush();
                            } else {
                                //L'activité est déjà importé mais n'est pas renseigné au niveau du service
                                //$output->writeln("L activite ". $activityId ." existe deja. Traitement de l activite suivante.");
                                $collectedActivities[] = $activityId;
                            }
                        }
                    }
                }
            }
            else {
                //Serveur en maintenance coté Nike plus
                $keepinsportUser = $em->getRepository('KsUserBundle:User')->findOneByUsername( "keepinsport" );
                $notificationService = $this->container->get('ks_notification.notificationService');
                
                $message = "ERREUR nike+ sync : 503 with user : " . $userService->getUser()->getId();
                    $notification = array(
                        "fromUser"              => $keepinsportUser,
                        "toUser"                => $keepinsportUser,
                        "message"               => $message,
                        "activity"              => null
                    );
                $notificationService->sendNotification(
                    $notification["activity"], 
                    $notification["fromUser"], 
                    $notification["toUser"], 
                    "message", 
                    $notification["message"]
                );
                //FMO : et on envoie un mail à l'utilisateur + notification
                $message = "Serveur Nike+ en maintenance ! Indépendant de KS, merci de revenir un peu plus tard lorsque leur serveur sera de nouveau disponible ;)";
                    $notification = array(
                        "fromUser"              => $keepinsportUser,
                        "toUser"                => $userService->getUser(),
                        "message"               => $message,
                        "activity"              => null
                    );
                $notificationService->sendNotification(
                    $notification["activity"], 
                    $notification["fromUser"], 
                    $notification["toUser"], 
                    "message", 
                    $notification["message"]
                );
                $userService->setStatus('done');
                $em->persist($userService);
                $em->flush();
                
                return 503;
            }
            
            $userService->setLastSyncAt(new \DateTime('now'));
            $userService->setStatus('done');
            $em->persist($userService);
            $em->flush();
            
            return $activities;

        } catch (\Exception $e) {
            throw $e;
            
            if( $newCollectedActivitiesNumber > 0 ) {
                $userService->setCollectedActivities( json_encode( $collectedActivities ) );
            } 
            
            $userService->setStatus('done');
            $em->persist( $userService );
            $em->flush();
        }
    }
    
    public function getActivitiesToSyncFromENDOMONDO(\Ks\UserBundle\Entity\User $user) {
        $em             = $this->doctrine->getEntityManager();
        $dbh            = $em->getConnection();
        $service        = $em->getRepository('KsUserBundle:Service')->findOneByName('Endomondo');
        $preferenceRep  = $em->getRepository('KsUserBundle:Preference');
        
        $userService= $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array(
            'user'      => $user->getId(),
            'service'   => $service->getId(),
            'is_active' => 1
        ));
        
        //$lastSyncAt     = $userService->getLastSyncAt();
        //$lastSyncAt     = $lastSyncAt != null ? $lastSyncAt->format("Y-m-d") : '1970-01-01';
        $endomondoApi   = $this->container->get('ks_user.endomondo');
        
        $activities = array();
        
        try {
            $endomondoApi->setAuthToken($userService->getToken());
            //$output->writeln(print_r($ret, true));exit;
            $endomondoSearchNumber = $preferenceRep->findOneBy( array("code" => "endomondoSearchNumber"))->getVal1();
            $endoActivities = $endomondoApi->fetchWorkouts($endomondoSearchNumber);
            
            // Traitement des activités
            // On commence par récupérer toutes les uri d'activités Endomondo déjà importées en bdd
            $query = 'select acfs.id_website_activity_service as uniqid'
                .' from ks_activity_come_from_service acfs'
                .' left join ks_activity a on (a.id = acfs.activity_id)'
                .' where acfs.service_id = :serviceId '
                .' and a.user_id = :userId';
            $res = $dbh->executeQuery($query, array(
                'serviceId' => $userService->getService()->getId(),
                'userId'    => $userService->getUser()->getId()
            ));
            $activitiesAlreadyImported = array();
            foreach ($res as $key => $val) {
                $activitiesAlreadyImported[$val['uniqid']] = true;
            }
            
            // on parcourt le tableau des activity Ids récupérées sur Endomondo
            foreach ($endoActivities as $activityDetail) {
                if (array_key_exists($activityDetail['id'], $activitiesAlreadyImported)) {
                    //$output->writeln('Déja importé, id: '.$key);
                    continue;
                }
                $startDate = new \DateTime($activityDetail['start_time']);
                $activityDetail['start_time'] = $startDate->format("d-m-Y");
                $activityDetail['distance_km'] = round($activityDetail['distance_km'], 3);
                $activityDetail['duration_sec'] = $this->activityService->secondesToTimeDuration($activityDetail['duration_sec'])->format("H:i:s");
                $activityDetail['path'] = isset($activityDetail['has_points']) && $activityDetail['has_points'] == 1 ? $endomondoApi->fetchTrackPoints($activityDetail['id']) : array();
                $activityDetail['type'] = $endomondoApi->getSportLabel($activityDetail['sport']);
                $activityDetail['sport'] = $em->getRepository('KsActivityBundle:Sport')->findOneByCodeSport($activityDetail['type']);
                
                $activities[] = $activityDetail;
                
                //Sauvegarde des données fetchées pour utilisation lors de la création de l'activité si import validé
                $acfs = new \Ks\ActivityBundle\Entity\ActivityToSync();
                $acfs->setUser($user);
                $acfs->setService($userService->getService());
                $acfs->setIdWebsiteActivityService($activityDetail['id']);
                $acfs->setSourceDetailsActivity(json_encode($activityDetail));
                $acfs->setTypeSource('JSON');

                $em->persist($acfs);
                $em->flush();
            }
                        
            $userService->setStatus('done');
            $userService->setLastSyncAt(new \Datetime());
            $userService->setFirstSync(0);
            $em->persist($userService);
            $em->flush();

            return $activities;
            
        } catch (Exception $e) {
            $output->writeln('Exception déclenchée: '.$e->getMessage());
        }
    }

    public function getActivitiesToSyncFromSUUNTO(\Ks\UserBundle\Entity\User $user) {
        
        $em             = $this->doctrine->getEntityManager();
        $dbh            = $em->getConnection();
        $code           = $em->getRepository('KsUserBundle:Service')->find(5);
        $preferenceRep  = $em->getRepository('KsUserBundle:Preference');
        
        $userServiceArray = $em->getRepository('KsUserBundle:UserHasServices')->findBy(array("user" => $user->getId(), "service" => $code->getId()));
        $userService = $userServiceArray[0];
        //return $userServiceArray;
        $accessToken    = $userService->getToken();
        $this->_enduranceOnEarthType    = $em->getRepository('KsActivityBundle:SportType')->findOneByLabel("endurance");
        $this->_enduranceUnderWaterType = $em->getRepository('KsActivityBundle:SportType')->findOneByLabel("endurance_under_water");
        $notificationService = $this->container->get('ks_notification.notificationService');
        $keepinsportUser = $em->getRepository('KsUserBundle:User')->findOneByUsername( "keepinsport" );

        $lastSyncAt = $userService->getLastSyncAt();
        $suuntoApi  = $this->container->get('ks_user.suunto');
        
        $activities = array();
        
        try {
            $suuntoApi->setAccessToken($accessToken);
            $email = $userService->getConnectionId();
            //$startDate = $userService->getLastSyncAt();
            /*if ($startDate == null) */$startDate = new \DateTime('now');
            $suuntoSearchNumber = $preferenceRep->findOneBy( array("code" => "suuntoSearchNumber"))->getVal1();
            $lastSyncAt = $startDate->sub(new \DateInterval("P".$suuntoSearchNumber."D"))->format("Y-m-d");
            
            $responseCode = $suuntoApi->setAppIntoUse($email, $lastSyncAt);
            
            if ($responseCode == 200) {
                //FMO: demande en cours à valider coté MOVESCOUNT
                $userService->setStatus('to validate on MOVESCOUNT !');
                
                //FMO : pour éviter tout problème avec les autres services on les désactive tous :
                $userHasOtherServices = $em->getRepository('KsUserBundle:UserHasServices')->findByUser($userService->getUser()->getId());
                foreach($userHasOtherServices as $userHasOtherService) {
                    if ($userHasOtherService->getService()->getName() != 'Suunto') {
                        $userHasOtherService->setIsActive(false);
                        $em->persist($userHasOtherService);
                        $em->flush();
                    }
                }
            }
            else if ($responseCode == 201) {
                //FMO : l'utilisateur a déjà validé son compte sur MOVESCOUNT et active son service
                $userService->setStatus('pending');
                $em->persist($userService);
                $em->flush();

                $suuntoActivities = $suuntoApi->getActivities();
                if (is_integer($suuntoActivities)) {
                    $message = "ERREUR curl_exec SUUNTO sync getActivities() with user : " . $userService->getUser()->getId() . " error=" . $suuntoActivities;
                    $notification = array(
                        "fromUser"              => $keepinsportUser,
                        "toUser"                => $keepinsportUser,
                        "message"               => $message,
                        "activity"              => null
                    );
                    $notificationService->sendNotification(
                        $notification["activity"], 
                        $notification["fromUser"], 
                        $notification["toUser"], 
                        "message", 
                        $notification["message"]
                    );
                    return $suuntoActivities;
                }
                else {
                    // Traitement des activités
                    // On commence par récupérer toutes les uri d'activités RK déjà importées en bdd
                    $query = 'select acfs.id_website_activity_service as uniqid'
                        .' from ks_activity_come_from_service acfs'
                        .' left join ks_activity a on (a.id = acfs.activity_id)'
                        .' where acfs.service_id = :serviceId '
                        .' and a.user_id = :userId';
                    $res = $dbh->executeQuery($query, array(
                        'serviceId' => $userService->getService()->getId(),
                        'userId'    => $userService->getUser()->getId()
                    ));
                    $activitiesAlreadyImported = array();
                    foreach ($res as $val) {
                        $activitiesAlreadyImported[$val['uniqid']] = true;
                    }
                    
                    // on parcourt le tableau des moves récupérées sur movescount
                    foreach ($suuntoActivities as $syncActivity) {
                        if (array_key_exists($syncActivity['MoveID'], $activitiesAlreadyImported)) {
                            continue;
                        }
                        //$syncActivity['MoveID'] = 74005098;//57035539;//52890750;//57035539//74005098
                        $activityDetail = $suuntoApi->getActivity($syncActivity['MoveID']);
                        //var_dump($activityDetail);exit;
                        $startDate = new \DateTime($activityDetail['LocalStartTime']);
                        $activityDetail['issuedAt'] = $activityDetail['LocalStartTime'];
                        //$activityDetail['issuedAt'] = $activityDetail['LastModifiedDate'];
                        $activityDetail['LocalStartTime'] = $startDate->format("d-m-Y");
                        $activityDetail['Distance'] = round($activityDetail['Distance']/1000, 3);
                        $activityDetail['Duration'] = $this->activityService->secondesToTimeDuration($activityDetail['Duration'])->format("H:i:s");
                        $codeSport = $this->getSportLabelFromSUUNTO($activityDetail['ActivityID']);
                        $activityDetail['sport'] = $sport = $em->getRepository('KsActivityBundle:Sport')->findOneByCodeSport($codeSport);
                        
                        $activityTrackPoints = $suuntoApi->getTrackPoints($syncActivity['MoveID']);
                        $activityDetail['trackPoints'] = $activityTrackPoints['TrackPoints'];
                        
                        $activitySamples = $suuntoApi->getSamples($syncActivity['MoveID']);
                        $activityDetail['sampleSets'] = $activitySamples['SampleSets'];
                        
                        $activityLaps = $suuntoApi->getMarks($syncActivity['MoveID']);
                        $activityDetail['laps'] = $activityLaps;
                        
                        //var_dump($activityTrackPoints);
                        //var_dump($activitySamples);
                        
                        $activities[] = $activityDetail;
                        //var_dump($activityDetail);

                        if (is_bool($activityDetail) && ! $activityDetail || is_bool($activityTrackPoints) && ! $activityTrackPoints) { // || is_bool($activitySamples) && ! $activitySamples) {
                            $message = "Erreur lors de la récupération du détail activité : ".$syncActivity['MoveID'];
                            $notification = array(
                                "fromUser"  => $keepinsportUser,
                                "toUser"    => $keepinsportUser,
                                "message"   => $message,
                                "activity"  => null
                            );
                            $notificationService->sendNotification(
                                $notification["activity"], 
                                $notification["fromUser"], 
                                $notification["toUser"], 
                                "message", 
                                $notification["message"]
                            );
                        }
                        else {
                            //Sauvegarde des données fetchées pour utilisation lors de la création de l'activité si import validé
                            $acfs = new \Ks\ActivityBundle\Entity\ActivityToSync();
                            $acfs->setUser($user);
                            $acfs->setService($userService->getService());
                            $acfs->setIdWebsiteActivityService($syncActivity['MoveID']);
                            $acfs->setSourceDetailsActivity(json_encode($activityDetail));
                            $acfs->setTypeSource('JSON');

                            $em->persist($acfs);
                            $em->flush();
                        }
                    }
                }
                $userService->setStatus('done');
                $em->persist($userService);
                $em->flush();
            }
            else if ($responseCode == 503) {
                //Erreur HTTP 503 Service unavailable (Service indisponible)
                //Cas d'une mise à jour sur movescount
                $message = "ERREUR setAppIntoUse SUUNTO sync : " . $responseCode . " with user : " . $userService->getUser()->getId();
                    $notification = array(
                        "fromUser"              => $keepinsportUser,
                        "toUser"                => $keepinsportUser,
                        "message"               => $message,
                        "activity"              => null
                    );
                $notificationService->sendNotification(
                    $notification["activity"], 
                    $notification["fromUser"], 
                    $notification["toUser"], 
                    "message", 
                    $notification["message"]
                );
                //FMO : et on envoie un mail à l'utilisateur + notification
                $message = "Serveur MOVESCOUNT indisponible ! Indépendant de KS, merci de patienter que SUUNTO ouvre à nouveau leur serveur ;)";
                    $notification = array(
                        "fromUser"              => $keepinsportUser,
                        "toUser"                => $userService->getUser(),
                        "message"               => $message,
                        "activity"              => null
                    );
                $notificationService->sendNotification(
                    $notification["activity"], 
                    $notification["fromUser"], 
                    $notification["toUser"], 
                    "message", 
                    $notification["message"]
                );
                $userService->setStatus('done');
                $em->persist($userService);
                $em->flush();
            }
            else {
                $userService->setStatus('error setAppIntoUse');
                //FMO : Autre erreur suite au test setAppIntoUse, on envoie un mail à contact@keepinsport.com
                $message = "ERREUR setAppIntoUse SUUNTO sync : " . $responseCode . " with user : " . $userService->getUser()->getId();
                    $notification = array(
                        "fromUser"              => $keepinsportUser,
                        "toUser"                => $keepinsportUser,
                        "message"               => $message,
                        "activity"              => null
                    );
                $notificationService->sendNotification(
                    $notification["activity"], 
                    $notification["fromUser"], 
                    $notification["toUser"], 
                    "message", 
                    $notification["message"]
                );
                //FMO : et on envoie un mail à l'utilisateur + notification pour qu'il change son email sur son profil !
                $message = "ATTENTION ! Ton adresse mail Keepinsport utilisée pour l'activation de la synchro SUUNTO ne correspond malheureusement pas à un compte MOVESCOUNT valide :( N'hésite pas à modifier ton email au niveau de ton profil sur Keepinsport (qui doit correspondre à ton login sur Movescount) Pour y accéder, clic sur ton pseudo tout en haut à droite ;)";
                    $notification = array(
                        "fromUser"              => $keepinsportUser,
                        "toUser"                => $userService->getUser(),
                        "message"               => $message,
                        "activity"              => null
                    );
                $notificationService->sendNotification(
                    $notification["activity"], 
                    $notification["fromUser"], 
                    $notification["toUser"], 
                    "message", 
                    $notification["message"]
                );
                //$userService->setToken(NULL);
                $em->persist($userService);
                $em->flush();
            }
            
            $em->persist($userService);
            $em->flush();
            
        } catch (Exception $e) {
            $userService->setStatus('done');
            $em->persist($userService);
            $em->flush();
        }
        
        if ($responseCode != 201) return $responseCode;
        else return $activities;
    }
    
    /**
     * Retourne l'intitulé du sport à partir de son id
     * @param int $sportId
     * @return string
     */
    public function getSportLabelFromSUUNTO($sportId)
    {
        $sportsMap = array(
            1 => 'other',// 1=  NOT_SPECIFIED_SPORT
            2 => 'other',//2 = MULTISPORT
            3 => 'running',//3 = RUN
            4 => 'cycling',//4 = CYCLING
            5 => 'mountain-biking',//5 = MOUNTAIN_BIKING
            6 => 'swimming',//6 = SWIMMING
            8 => 'skateboard',//8 = SKATING
            9 => 'other',//9 = AEROBICS
            10 => 'other',//10 = YOGA_PILATES
            11 => 'hiking',//11 = TREKKING
            12 => 'walking',//12 = WALKING
            13 => 'other',//13 = SAILING
            14 => 'canoe-kayak',//14 = KAYAKING
            15 => 'rowing',//15 = ROWING
            16 => 'climbing',//16 = CLIMBING
            17 => 'spinning',//17 = INDOOR_CYCLING
            18 => 'running',//18 = CIRCUIT_TRAINING
            19 => 'triathlon',//19 = TRIATHLON
            20 => 'downhill-skiing',//20 = ALPINE_SKIING
            21 => 'snwoboard',//21 = SNOWBOARDING
            22 => 'crossCountrySkiing',//22 = CROSSCOUNTRY_SKIING
            23 => 'weight training',//23 = WEIGHT_TRAINING
            24 => 'basketball',//24 = BASKETBALL
            25 => 'football',//25 = SOCCER
            26 => 'other',//26 = ICE_HOCKEY
            27 => 'volley',//27 = VOLLEYBALL
            28 => 'football',//28 = FOOTBALL
            29 => 'other',//29 = SOFTBALL
            30 => 'other',//30 = CHEERLEADING
            31 => 'other',//31 = BASEBALL
            33 => 'tennis',//33 = TENNIS
            34 => 'badminton',//34 = BADMINTON
            35 => 'tennis-table',//35 = TABLE_TENNIS
            36 => 'other',//36 = RACQUET_BALL
            37 => 'squash',//37 = SQUASH
            38 => 'mma',//38 = COMBAT_SPORT
            39 => 'boxing',//39 = BOXING
            40 => 'other',//40 = FLOORBALL
            51 => 'scuba-diving',//51 = SCUBA_DIVING
            52 => 'other',//52 = FREE_DIVING
            61 => 'other',//61 = ADVENTURE_RACING
            62 => 'other',//62 = BOWLING
            63 => 'other',//63 = CRICKET
            64 => 'other',//64 = CROSSTRAINER
            65 => 'other',//65 = DANCING
            66 => 'golf',//66 = GOLF
            67 => 'other',//67 = GYMNASTICS
            68 => 'handball',//68 = HANDBALL
            69 => 'horse_riding',//69 = HORSEBACK_RIDING
            70 => 'ice_skate',//70 = ICE_SKATING
            71 => 'rowing',//71 = INDOOR_ROWING
            72 => 'canoe-kayak',//72 = CANOEING
            73 => 'other',//73 = MOTORSPORTS
            74 => 'other',//74 = MOUNTAINEERING
            75 => 'other',//75 = ORIENTEERING
            76 => 'rugby',//76 = RUGBY
            78 => 'touringSki',//78 = SKI_TOURING
            79 => 'other',//79 = STRETCHING
            80 => 'other',//80 = TELEMARK_SKIING
            81 => 'other',//81 = TRACK_AND_FIELD
            82 => 'running',//82 = TRAIL_RUNNING
            83 => 'swimming',//83 = OPENWATER_SWIMMING
        );
        
        if (array_key_exists($sportId, $sportsMap)) {
            return $sportsMap[$sportId];
        }
        
        return $sportsMap[1]; // 'other';
    }
    
    public function getActivitiesToSyncFromPOLAR(\Ks\UserBundle\Entity\User $user) {
        
        $em             = $this->doctrine->getEntityManager();
        $dbh            = $em->getConnection();
        $code           = $em->getRepository('KsUserBundle:Service')->find(8);
        $preferenceRep  = $em->getRepository('KsUserBundle:Preference');
        
        $userServiceArray = $em->getRepository('KsUserBundle:UserHasServices')->findBy(array("user" => $user->getId(), "service" => $code->getId()));
        $userService = $userServiceArray[0];
        //return $userServiceArray;
        $mailPolar  = $userService->getConnectionId();
        $mdpPolar   = base64_decode($userService->getConnectionPassword());
                
        $this->_enduranceOnEarthType    = $em->getRepository('KsActivityBundle:SportType')->findOneByLabel("endurance");
        $this->_enduranceUnderWaterType = $em->getRepository('KsActivityBundle:SportType')->findOneByLabel("endurance_under_water");
        $notificationService = $this->container->get('ks_notification.notificationService');
        $keepinsportUser = $em->getRepository('KsUserBundle:User')->findOneByUsername( "keepinsport" );

        $lastSyncAt = $userService->getLastSyncAt();
        $polarApi  = $this->container->get('ks_user.polar');
        
        $activities = array();
        
        try {
            $identifier = microtime(true);
            $polarApi->authorize($identifier, $mailPolar, $mdpPolar);
            $startDate = $userService->getLastSyncAt();
            if ($startDate == null) $startDate = new \DateTime('now');
            $polarSearchNumber = $preferenceRep->findOneBy( array("code" => "polarSearchNumber"))->getVal1();
            $lastSyncAt = $startDate->sub(new \DateInterval("P".$polarSearchNumber."D"))->format('d.m.Y');
            
            //FMO : pour éviter tout problème avec les autres services on les désactive tous :
            $userHasOtherServices = $em->getRepository('KsUserBundle:UserHasServices')->findByUser($userService->getUser()->getId());
            foreach($userHasOtherServices as $userHasOtherService) {
                if ($userHasOtherService->getService()->getName() != 'Polar') {
                    $userHasOtherService->setIsActive(false);
                    $em->persist($userHasOtherService);
                    $em->flush();
                }
            }
            
            $polarActivities = $polarApi->getActivities($mailPolar, $mdpPolar, $lastSyncAt);
            //var_dump($polarActivities);exit;
            if (is_integer($polarActivities)) {
                if ($polarActivities == 503) {
                    //Erreur HTTP 503 Service unavailable (Service indisponible)
                    $message = "ERREUR getActivities POLAR sync : " . $responseCode . " with user : " . $userService->getUser()->getId();
                        $notification = array(
                            "fromUser"              => $keepinsportUser,
                            "toUser"                => $keepinsportUser,
                            "message"               => $message,
                            "activity"              => null
                        );
                    $notificationService->sendNotification(
                        $notification["activity"], 
                        $notification["fromUser"], 
                        $notification["toUser"], 
                        "message", 
                        $notification["message"]
                    );
                    //FMO : et on envoie un mail à l'utilisateur + notification
                    $message = "Serveur POLARPERSONALTRAINER indisponible ! Indépendant de KS, merci de patienter que POLAR ouvre à nouveau leur serveur ;)";
                        $notification = array(
                            "fromUser"              => $keepinsportUser,
                            "toUser"                => $userService->getUser(),
                            "message"               => $message,
                            "activity"              => null
                        );
                    $notificationService->sendNotification(
                        $notification["activity"], 
                        $notification["fromUser"], 
                        $notification["toUser"], 
                        "message", 
                        $notification["message"]
                    );
                    $userService->setStatus('done');
                    $em->persist($userService);
                    $em->flush();
                }
                else {
                    $message = "ERREUR curl_exec POLAR sync getActivities() with user : " . $userService->getUser()->getId() . " error=" . $polarActivities;
                    $notification = array(
                        "fromUser"              => $keepinsportUser,
                        "toUser"                => $keepinsportUser,
                        "message"               => $message,
                        "activity"              => null
                    );
                    $notificationService->sendNotification(
                        $notification["activity"], 
                        $notification["fromUser"], 
                        $notification["toUser"], 
                        "message", 
                        $notification["message"]
                    );
                    return $polarActivities;
                }
            }
            else {
                // Traitement des activités
                // On commence par récupérer toutes les uri d'activités RK déjà importées en bdd
                $query = 'select acfs.id_website_activity_service as uniqid'
                    .' from ks_activity_come_from_service acfs'
                    .' left join ks_activity a on (a.id = acfs.activity_id)'
                    .' where acfs.service_id = :serviceId '
                    .' and a.user_id = :userId';
                $res = $dbh->executeQuery($query, array(
                    'serviceId' => $userService->getService()->getId(),
                    'userId'    => $userService->getUser()->getId()
                ));
                $activitiesAlreadyImported = array();
                foreach ($res as $val) {
                    $activitiesAlreadyImported[$val['uniqid']] = true;
                }

                //var_dump($polarActivities);exit;
                // on parcourt le tableau des id récupérées sur polarpersonaltrainer
                foreach ($polarActivities as $polarActivityId) {
                    if (array_key_exists($polarActivityId, $activitiesAlreadyImported)) {
                        continue;
                    }
                    //var_dump($polarActivityId);exit;
                    $activityDetail = $polarApi->getActivity($polarActivityId);
                    
                    $activityDetail["activityId"] = $polarActivityId;
                    $activityDetail["filename"] = $polarApi->getGPX($polarActivityId);
                    
                    //$activityDetail["filename"] = $polarApi->getGPX($polarActivityId);
                    
                    //var_dump($activityDetail);exit;
                    $activityDetail["activityId"] = $polarActivityId;
                    $startDate = new \DateTime($activityDetail['LocalStartTime']);
                    $activityDetail['issuedAt'] = $activityDetail['LocalStartTime'];
                    //$activityDetail['issuedAt'] = $activityDetail['LastModifiedDate'];
                    $activityDetail['LocalStartTime'] = $startDate->format("d-m-Y");
                    $activityDetail['Distance'] = round($activityDetail['Distance']/1000, 3);
                    
                    $activityTrackPoints = $polarApi->getTrackPoints($polarActivityId);
                    //var_dump($activityTrackPoints['waypoints']);exit;
                    //$activityDetail['trackPoints']  = $activityTrackPoints['waypoints'];
                    $activityDetail['Source']       = $activityTrackPoints['watch'];
                    //var_dump($activityTrackPoints['hr']);
                    //exit;
                    
                    //var_dump($activityTrackPoints);
                    //var_dump($activitySamples);

                    $activities[] = $activityDetail;
                    //var_dump($activityDetail);

                    if (is_bool($activityDetail) && ! $activityDetail) {
                        $message = "Erreur lors de la récupération du détail activité : ".$polarActivityId;
                        $notification = array(
                            "fromUser"  => $keepinsportUser,
                            "toUser"    => $keepinsportUser,
                            "message"   => $message,
                            "activity"  => null
                        );
                        $notificationService->sendNotification(
                            $notification["activity"], 
                            $notification["fromUser"], 
                            $notification["toUser"], 
                            "message", 
                            $notification["message"]
                        );
                    }
                    else {
                        //Sauvegarde des données fetchées pour utilisation lors de la création de l'activité si import validé
                        $acfs = new \Ks\ActivityBundle\Entity\ActivityToSync();
                        $acfs->setUser($user);
                        $acfs->setService($userService->getService());
                        $acfs->setIdWebsiteActivityService($polarActivityId);
                        $acfs->setSourceDetailsActivity(json_encode($activityDetail));
                        $acfs->setTypeSource('JSON');

                        $em->persist($acfs);
                        $em->flush();
                    }
                }
            }
            $userService->setStatus('done');
            $em->persist($userService);
            $em->flush();
            
        } catch (Exception $e) {
            $userService->setStatus('done');
            $em->persist($userService);
            $em->flush();
        }
        return $activities;
    }
    
    public function toSeconds($time) {
        return intval($time / 1000) % 60;
    }
    
    public function secondesToTimeDuration($duration){
        $heure = intval(abs($duration / 3600));
        $duration = $duration - ($heure * 3600);
        $minute = intval(abs($duration / 60));
        $duration = $duration - ($minute * 60);
        $seconde = round($duration);
        
        //Si activité de plus de 24H, le DateTime aime pas, on sauvegarde donc le temps -24H dans le champ DURATION mais c'est le champ MOVINGduration qui sera utilisé coté affichage
        if ($heure >= 24) $heure -= 24;
        $time = new \DateTime("$heure:$minute:$seconde");
        //$time = "$heure:$minute:$seconde";
        return $time;
    }  
    
    public function formatNameSport($sport) {
        return str_replace (" " , "-" , strtolower($this->wd_remove_accents($sport)) );
    }
    
    public function wd_remove_accents($str, $charset='utf-8') {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        return $str;
   }
    
   
        
    /**
     *
     * @param array $syncActivity
     * @param type $userService 
     */
    public function saveUserActivityFromService($translator, $serviceName, array $syncActivity, $userService, $stateOfHealthId, $intensityId, $achievement, $eventId, $description, $equipments)
    {
        $em                     = $this->container->get('doctrine')->getEntityManager();
        $activityRep            = $em->getRepository('KsActivityBundle:Activity');
        $equipmentRep           = $em->getRepository('KsUserBundle:Equipment');
        $importService          = $this->container->get('ks_activity.importActivityService');
        $leagueLevelService     = $this->container->get('ks_league.leagueLevelService');
        
        $user           = $userService->getUser();
        
        // Construction du tableau trackingDatas
        //var_dump($syncActivity);exit;
        list($trackingDatas, $error) = $importService->buildJsonToSave($user, $syncActivity, $serviceName);
        
        if ($serviceName == "Polar") {
            //Cas particulier pour Polar, création de l'activité par fichier GPX
            $activity = $importService->saveUserSessionFromActivityDatas($trackingDatas, $user);
        }
        else {
            $activity       = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth($user);
        }
        
        if ($serviceName == "Runkeeper")        $codeSport = $this->formatNameSport($syncActivity['type']);
        else if ($serviceName == "NikePlus")    $codeSport = $this->formatNameSport("running");
        else if ($serviceName == "Endomondo")   $codeSport = $this->formatNameSport($syncActivity['type']);
        else if ($serviceName == "Suunto")      $codeSport = $this->getSportLabelFromSUUNTO($syncActivity['ActivityID']);
        else if ($serviceName == "Garmin")      $codeSport = $syncActivity['codeSport'];
        else if ($serviceName == "Polar")       $codeSport = $this->formatNameSport($syncActivity['Sport']);
        
        $sport     = $em->getRepository('KsActivityBundle:Sport')->findOneByCodeSport($codeSport);
        if (!is_object($sport) || is_null($sport)) $sport = $em->getRepository('KsActivityBundle:Sport')->findOneByCodeSport('other');
        
        /*if (!is_object($sport)) {
            //FMo : pas nécessaire pour l'instant
            $sport = new \Ks\ActivityBundle\Entity\Sport();
            $sport->setLabel($codeSport);
            $sport->setCodeSport($codeSport);
            $sport->setSportType($this->_enduranceOnEarthType);
            $em->persist($sport);
            $em->flush();
        }*/
        $activity->setSport($sport);
        $activity->setSource($serviceName);
        
        
        $firstWaypoint = $importService->getFirstWaypointNotEmpty($trackingDatas);
        if ($firstWaypoint != null) {
            $activity->setPlace(
                $importService->findPlace($firstWaypoint["lat"], $firstWaypoint["lon"])
            );
        }
         
        if( $trackingDatas != null && is_array($trackingDatas) ) {
            $activity->setTrackingDatas($trackingDatas);
        }
            
        if ($serviceName == "Runkeeper") {
            if (isset($syncActivity['total_distance'])) {
                $activity->setDistance($syncActivity['total_distance']);
            }
            if (isset($syncActivity['duration'])) {
                $timeMoving = new \DateTime($syncActivity['duration']);
                $activity->setDuration($timeMoving);
                $activity->setTimeMoving($timeMoving->format('H') * 3600 + $timeMoving->format('i') * 60 + $timeMoving->format('s'));
            };
            if (isset($syncActivity['start_time'])) {
                $issuedAt = new \DateTime($syncActivity['start_time']);
                //$issuedAt->setTimeZone(new \DateTimeZone("Europe/Paris"));
                $activity->setIssuedAt($issuedAt);
                $activity->setIssuedAt(new \DateTime($syncActivity['start_time']));
                $activity->setModifiedAt(new \DateTime());
            }
            if (isset($syncActivity['notes'])) {
                $activity->setDescription(html_entity_decode($syncActivity['notes']));
            }
            if (isset($syncActivity['total_calories'])) {
                $activity->setCalories($syncActivity['total_calories']);
            }
            if (isset($syncActivity['climb'])) {
                $activity->setElevationGain($syncActivity['climb']);
            }
        }
        else if ($serviceName == 'NikePlus') {
            if (isset($syncActivity['distance'])) {
                $activity->setDistance($syncActivity['distance']);
            }
            if (isset($syncActivity['duration'])) {
                $timeMoving = new \DateTime($syncActivity['duration']);
                $activity->setDuration($timeMoving);
                $activity->setTimeMoving($timeMoving->format('H') * 3600 + $timeMoving->format('i') * 60 + $timeMoving->format('s'));
            };
            if (isset($syncActivity['startTimeUtc'])) {
                $issuedAt = new \DateTime($syncActivity['startTimeUtc']);
                //$issuedAt->setTimeZone(new \DateTimeZone("Europe/Paris"));
                $activity->setIssuedAt($issuedAt);
                $activity->setModifiedAt(new \DateTime());
            }
            if (isset($syncActivity['calories'])) $activity->setCalories($syncActivity['calories']);
            //Si pas de D+/D- issues des données de l'API on prend celles calculées en interne via le trackingDatas
            if ((!isset($syncActivity['elevationGain']) || is_null($syncActivity['elevationGain'])) && isset($trackingDatas['info']['D+'])) {
                $activity->setElevationGain((float)$trackingDatas['info']['D+']);
            }
            else {
                $activity->setElevationGain($syncActivity['elevationGain']);
            }
            if ((!isset($syncActivity['elevationLost']) || is_null($syncActivity['elevationLost'])) && isset($trackingDatas['info']['D-'])) {
                $activity->setElevationLost((float)$trackingDatas['info']['D-']);
            }
            else {
                $activity->setElevationLost($syncActivity['elevationLost']);
            }
        }
        else if ($serviceName == 'Endomondo') {
            if (isset($syncActivity['distance_km'])) {
                $activity->setDistance($syncActivity['distance_km']);
            }
            if (isset($syncActivity['duration_sec'])) {
                $timeMoving = new \DateTime($syncActivity['duration_sec']);
                $activity->setDuration($timeMoving);
                $activity->setTimeMoving($timeMoving->format('H') * 3600 + $timeMoving->format('i') * 60 + $timeMoving->format('s'));
            };
            if (isset($syncActivity['start_time'])) {
                $issuedAt = new \DateTime($syncActivity['start_time']);
                $issuedAt->setTimeZone(new \DateTimeZone("Europe/Paris"));
                $activity->setIssuedAt($issuedAt);
                $activity->setModifiedAt(new \DateTime());
            }
            if (isset($syncActivity['calories'])) $activity->setCalories($syncActivity['calories']);
            /*if (isset($syncActivity['altitude_m_max'])) $activity->setElevationMax((int)$syncActivity['altitude_m_max']);
            if (isset($syncActivity['altitude_m_min'])) $activity->setElevationMax((int)$syncActivity['altitude_m_min']);
            if (isset($syncActivity['speed_kmh_avg'])) $activity->setSpeedAverage((float)$syncActivity['speed_kmh_avg']);*/
            //Si pas de D+/D- issues des données de l'API on prend celles calculées en interne via le trackingDatas
            if (isset($trackingDatas['info']['D+'])) $activity->setElevationGain((float)$trackingDatas['info']['D+']);
            if (isset($trackingDatas['info']['D-'])) $activity->setElevationLost((float)$trackingDatas['info']['D-']);
        }
        else if ($serviceName == "Suunto") {
            if (isset($syncActivity['Distance'])) {
                $activity->setDistance($syncActivity['Distance']);
            }
            else if (isset($trackingDatas) && $trackingDatas != null) {
                $activity->setDistance($trackingDatas['info']['distance']);
            }
            if (isset($syncActivity['Duration'])) {
                $timeMoving = new \DateTime($syncActivity['Duration']);
                $activity->setDuration($timeMoving);
                $activity->setTimeMoving($timeMoving->format('H') * 3600 + $timeMoving->format('i') * 60 + $timeMoving->format('s'));
            }
            else if (isset($trackingDatas) && $trackingDatas != null){
                $activity->setDuration($trackingDatas['info']['timeDuration']);
                $activity->setTimeMoving($trackingDatas['info']['duration']);
            }

            if (isset($syncActivity['AvgSpeed'])) {
                $activity->setSpeedAverage($syncActivity['AvgSpeed']);
            };
            if (isset($syncActivity['issuedAt'])) {
                $issuedAt = new \DateTime($syncActivity['issuedAt']);
                //$issuedAt->setTimeZone(new \DateTimeZone("Europe/Paris"));
                $activity->setIssuedAt($issuedAt);
                $activity->setModifiedAt(new \DateTime());
            }
            if (isset($syncActivity['Notes'])) {
                $activity->setDescription(html_entity_decode($syncActivity['Notes']));
            }
            if (isset($syncActivity['Energy'])) {
                $activity->setCalories($syncActivity['Energy']);
            }
            if (isset($syncActivity['AscentAltitude'])) {
                $activity->setElevationGain($syncActivity['AscentAltitude']);
            }
            if (isset($syncActivity['DescentAltitude'])) {
                $activity->setElevationLost($syncActivity['DescentAltitude']);
            }
        }
        else if ($serviceName == "Garmin") {
            if (isset($syncActivity['total_distance'])) {
                $activity->setDistance($syncActivity['total_distance']);
            }
            else if (isset($trackingDatas) && $trackingDatas != null) {
                $activity->setDistance($trackingDatas['info']['total_distance']);
            }
            if (isset($syncActivity['total_duration'])) {
                $timeMoving = new \DateTime($syncActivity['total_duration']);
                $activity->setDuration($timeMoving);
                $activity->setTimeMoving($timeMoving->format('H') * 3600 + $timeMoving->format('i') * 60 + $timeMoving->format('s'));
            }
            else if (isset($trackingDatas) && $trackingDatas != null){
                $activity->setDuration($trackingDatas['info']['total_duration']);
                $activity->setTimeMoving($trackingDatas['info']['duration']);
            }

            if (isset($syncActivity['start_time'])) {
                $issuedAt = new \DateTime($syncActivity['start_time']);
                //$issuedAt->setTimeZone(new \DateTimeZone("Europe/Paris"));
                $activity->setIssuedAt($issuedAt);
                $activity->setModifiedAt(new \DateTime());
            }
            if (isset($syncActivity['climb'])) {
                $activity->setElevationGain($syncActivity['climb']);
            }
            if (isset($syncActivity['descent'])) {
                $activity->setElevationLost($syncActivity['descent']);
            }
        }
        $activity->setSynchronizedAt(new \DateTime());

        /*Prise en compte des équipments par défaut selon le sport => plus maintenant car c'est l'utilisateur qui choisi son matériel utilisé
        $equipmentsIds = $equipmentRep->getMyEquipmentsIdsByDefault($activity->getUser()->getId(), $activity->getSport()->getId());
        foreach ($equipmentsIds as $equipmentId) {
            $activity->addEquipment($equipmentRep->find($equipmentId));
        }*/
        
        //Prise en compte du statut de forme, de l'intensité ressentie, de l'auto-évaluation, de la séance rattachée, du commentaire et du matériel utilisé
        if (isset($stateOfHealthId) && !is_null($stateOfHealthId)) {
            $stateOfHealth    = $em->getRepository('KsActivityBundle:StateOfHealth')->find($stateOfHealthId);
            $activity->setStateOfHealth($stateOfHealth);
        }
        if (isset($intensityId) && !is_null($intensityId)) {
            $intensity    = $em->getRepository('KsActivityBundle:Intensity')->find($intensityId);
            $activity->setIntensity($intensity);
        }
        if (isset($achievement) && !is_null($achievement)) {
            $activity->setAchievement($achievement);
        }
        if (isset($eventId) && !is_null($eventId) && $eventId != "") {
            $event    = $em->getRepository('KsEventBundle:Event')->find($eventId);
            $activity->setEvent($event);
            $em->persist($activity);
            $em->flush();
            
            //FIXME : FMO soucis lien entre event et activity...
            //$em->getRepository('KsEventBundle:Event')->updateOddLinks($activity->getId());
            
            $event->setActivitySession($activity);
            $em->persist($event);
            $em->flush();
            
            $activity->setIsPublic($event->getIsPublic());
            
            $club = $event->getClub();
            if ($club != null) {
                $activity->setClub($club);
                
                //Envoi notif + mail au coach pour qu'il prenne connaissance de l'activité postée par l'utilisateur
                //Pas d'envoi de mail si user qui s'est fait son propre plan
                $user = $activity->getUser();
                $notificationService   = $this->container->get('ks_notification.notificationService');
                $message = $user->__toString() . " " . $translator->trans('coaching.mail-activity-done');
                
                foreach($club->getManagers() as $clubHasManagers) {
                    $manager = $clubHasManagers->getUser();
                    //var_dump($manager->getId());
                    $notificationService->sendNotification($activity, $user, $manager, 'coaching', $message, $club);
                }
            }
        }
        if (isset($description) && !is_null($description)) {
            $activity->setDescription($description);
        }
        if (isset($equipments) && !is_null($equipments)) {
            foreach ($equipments as $equipmentId) {
                $activity->addEquipment($equipmentRep->find($equipmentId));
            }
        }
        
        $em->persist($activity);
        $em->flush();
        
        //Gain de points 
        $leagueLevelService->activitySessionEarnPoints($activity, $user);
        
        //Mise à jour des étoiles
        $leagueCategoryId = $userService->getUser()->getLeagueLevel()->getCategory()->getId();
        if( is_integer( $leagueCategoryId ) ) $leagueLevelService->leagueRankingUpdate( $leagueCategoryId );
        
        //On l'abonne à son activité
        $activityRep->subscribeOnActivity($activity, $user);
        
        //Pour chaques activité on a un identifiant relatif au service qu'on synchro
        $acfs = new \Ks\ActivityBundle\Entity\ActivityComeFromService();
        $acfs->setActivity($activity);
        $acfs->setService($userService->getService());
        if ($serviceName == "Suunto")           $acfs->setIdWebsiteActivityService($syncActivity['MoveID']);
        else if ($serviceName == "NikePlus")    $acfs->setIdWebsiteActivityService($syncActivity['activityId']);
        else if ($serviceName == "Endomondo")   $acfs->setIdWebsiteActivityService($syncActivity['id']);
        else if ($serviceName == "Runkeeper")   $acfs->setIdWebsiteActivityService($syncActivity['uri']);
        else if ($serviceName == "Garmin")      $acfs->setIdWebsiteActivityService($syncActivity['activityId']);
        else if ($serviceName == "Polar")       $acfs->setIdWebsiteActivityService($syncActivity['activityId']);
        
        $acfs->setSourceDetailsActivity(json_encode($syncActivity));
        $acfs->setTypeSource('JSON');

        $em->persist($acfs);
        $em->flush();
        
        $userService->setLastSyncAt(new \DateTime('now'));
        $em->persist($userService);
        $em->flush();
        
        return array($activity->getId(), $error);
    }
}
