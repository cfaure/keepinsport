<?php

namespace Ks\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\DateTime;


class StravaSyncCommand extends ContainerAwareCommand
{
    
    protected $_enduranceOnEarthType;
    protected $_enduranceUnderWaterType;
    
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('ks:user:stravasync')
            ->setDescription('Synchroniser les activités d\'un utilisateur Strava')
            ->setDefinition(array(
                new InputArgument('token', InputArgument::REQUIRED, 'Token de l\'utilisateur à synchroniser'),
            ))
            ->setHelp(<<<EOT
La commande <info>ks:user:stravasync</info> synchronise les activités Strava d'un utilisateur avec son profil Keepinsport :

  <info>php app/console ks:user:stravasync token</info>
EOT
        );
    }
    
    /**
     * 
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $token      = $input->getArgument('token');
        $em         = $this->getContainer()->get('doctrine')->getEntityManager();
        $dbh        = $em->getConnection();
        $service    = $em->getRepository('KsUserBundle:Service')->findOneByName('Strava');
        
        //Appel des services
        $leagueLevelService         = $this->getContainer()->get('ks_league.leagueLevelService');
        
        $userService = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array(
            'token'     => $token,
            'service'   => $service->getId(),
            'is_active' => 1
        ));

        if (!is_object($userService)) {
            $output->writeln("Impossible de trouver le service Strava pour l'utilisateur token: ".$token."");
            return;
        }
        
        $jobPath = $this->getContainer()->get('kernel')->getRootdir().'/jobs';
        $this->_enduranceOnEarthType = $em
            ->getRepository('KsActivityBundle:SportType')
            ->findOneByLabel("endurance");
        
        if (file_exists($jobPath.'/pending/strava/'.$token.'.job')){
            rename(
                $jobPath.'/pending/strava/'.$token.'.job',
                $jobPath.'/inprogress/strava/'.$token.'.job'
            );
        } else {
            $output->writeln('No pending job found');
            return;
        }
        
        $stravaApi = $this->getContainer()->get('ks_user.strava');
        
        try {
            $after = $userService->getLastSyncAt();
            if ($after == null) {
                $query = 'select issuedAt from ks_activity'
                    .' where user_id = :userId'
                    .' order by issuedAt desc'
                    .' limit 1';
                $res = $dbh->executeQuery($query, array(
                    'userId' => $userService->getUser()->getId()
                ));
                if (count($res) == 1) {
                    foreach ($res as $val) {
                        $after = $val['issuedAt'];
                    }
                } else {
                    $now    = new \DateTime();
                    $after  = $now->format('Y-m-d H:i:s');
                }
            } else {
                $after = $after->format('Y-m-d H:i:s');
            }

            $stravaApi->setAccessToken($userService->getToken());
            $activities = $stravaApi->getFitnessActivities($after);


            // Traitement des activités
            // On commence par récupérer toutes les uri d'activités Strava déjà importées en bdd
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
            // on parcourt le tableau des activity Ids récupérées sur Strava
            foreach ($activities as &$activity) {
                $activityDatas = array();
                $activityDatas['uniqId']                = $activity->external_id;
                $activityDatas['distance_km']           = (float)$activity->distance / 1000.0;
                $activityDatas['moving_duration_sec']   = (int)$activity->moving_time;
                $activityDatas['elapsed_duration_sec']  = (int)$activity->elapsed_time;
                $activityDatas['start_time']            = $activity->start_date;
                $activityDatas['speed_kmh_avg']         = (float)$activity->average_speed * 3.6; // conversion m/s en km/h.
                $activityDatas['total_elevation_gain']  = (float)$activity->total_elevation_gain; // D+
                $activityDatas['calories']              = (int)$activity->kilojoules; // NOTE CF: Strava exprime en kilojoules mais ça correspond plus à notre calcul de point que de faire la conversion en Calories
                
                if (array_key_exists($activityDatas['uniqId'], $activitiesAlreadyImported)) {
                    $output->writeln('Déja importé, id: '.$activityDatas['uniqId']);
                    continue;
                }
                $activityDatas['trackPoints']   = isset($activity->map) && isset($activity->map->summary_polyline) ?
                    $stravaApi->decodePolylineToArray($activity->map->summary_polyline)
                    : array();
                $activityDatas['type']   = $activity->type == 'Ride' ? 'cycling' : 'running'; // FIXME: à revoir
                $this->saveUserActivity($activityDatas, $userService);
            }
            
            //Mise à jour des étoiles
            $leagueCategoryId = $userService->getUser()->getLeagueLevel()->getCategory()->getId();
            if (is_integer($leagueCategoryId)) {
                $leagueLevelService->leagueRankingUpdate($leagueCategoryId);
            }
                
            // on déplace le fichier qui a servi a faire l'import asynchrone 
            if (file_exists($jobPath.'/inprogress/strava/'.$token.'.job')) {
                rename(
                    $jobPath.'/inprogress/strava/'.$token.'.job',
                    $jobPath.'/done/strava/'.$token.'.job'
                );
                $userService->setStatus('done');
                $userService->setLastSyncAt(new \Datetime());
                $userService->setFirstSync(0);
                $em->persist($userService);
                $em->flush();
            }
        } catch (Exception $e) {
            $output->writeln('Exception déclenchée: '.$e->getMessage());
        }
    }
    
    /**
     *
     * @param array $stravaActivity
     * @param type $userService 
     */
    protected function saveUserActivity(array $stravaActivity, \Ks\UserBundle\Entity\UserHasServices $userService)
    {
        $em             = $this->getContainer()->get('doctrine')->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $equipmentRep   = $em->getRepository('KsUserBundle:Equipment');
        $importService  = $this->getContainer()->get('ks_activity.importActivityService');
        $activity       = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth($userService->getUser());
        
        // FIXME: code en double
        $codeSport      = $stravaActivity['type'];
        $sport          = $em
            ->getRepository('KsActivityBundle:Sport')
            ->findOneByCodeSport($codeSport);

        if (!is_object($sport)) {
            $sport = new \Ks\ActivityBundle\Entity\Sport();
            $sport->setLabel($codeSport);
            $sport->setCodeSport($codeSport);
            $sport->setSportType($this->_enduranceOnEarthType);
            $em->persist($sport);
            $em->flush();
        }
        $activity->setSport($sport);
        // FIN code en double
        
        $activity->setSource('Strava');
        // FIXME: on refait la moitié du travail 2x .... :'(
        
        $activity->setDistance($stravaActivity['distance_km']);
                
        $duration = $stravaActivity['moving_duration_sec'];
        $activity->setDuration($this->secondesToTimeDuration($duration));
        $activity->setTimeMoving($duration);
        
        $issuedAt = new \DateTime($stravaActivity['start_time']);
        $issuedAt->setTimeZone(new \DateTimeZone("Europe/Paris"));
        $activity->setIssuedAt($issuedAt);
        $activity->setModifiedAt(new \DateTime());
        
        if (isset($stravaActivity['calories'])) {
            $activity->setCalories($stravaActivity['calories']);
        }
        if (isset($stravaActivity['altitude_m_max'])) {
            $activity->setElevationMax((int)$stravaActivity['altitude_m_max']);
        }
        if (isset($stravaActivity['altitude_m_min'])) {
            $activity->setElevationMax((int)$stravaActivity['altitude_m_min']);
        }
        if (isset($stravaActivity['speed_kmh_avg'])) {
            $activity->setSpeedAverage((float)$stravaActivity['speed_kmh_avg']);
        }
        if (isset($stravaActivity['total_elevation_gain'])) {
            $activity->setElevationGain($stravaActivity['total_elevation_gain']);
        }
        
        if (array_key_exists('start_latlng', $stravaActivity) && !empty($stravaActivity['start_latlng'])) {
            $activity->setPlace(
                $importService->findPlace($stravaActivity['start_latlng'][0], $stravaActivity['start_latlng'][1])
            );
        }
        
        if (array_key_exists('trackPoints', $stravaActivity) && !empty($stravaActivity['trackPoints'])) {
            $endedAt    = $this->calculEndDate($issuedAt, $duration);
            $tDuration  = $this->secondesToTimeDuration($duration);
            $distance   = $stravaActivity['distance_km'];
            $jsonToSave = array(
                "info" => array(
                    'source'            => 'strava',
                    "startDate"         => $issuedAt,
                    "endDate"           => $endedAt,
                    "D+"                => null,
                    "D-"                => null,
                    'minEle'            => null,
                    'maxEle'            => null,
                    "distance"          => round($distance, 2),
                    'timeDuration'      => $tDuration, // FIXME: il faut se débarasser de ce format
                    "duration"          => (int)$duration,
                    "durationMoving"    => (int)$duration,  // FIXME: pas toujours la même chose, à calculer 
                    "issetHeartRates"   => false,
                    "issetTemperatures" => false,
                    'minTemp'           => null,
                    'maxTemp'           => null
                ),
                "waypoints" => $stravaActivity['trackPoints']
            );
            $activity->setTrackingDatas($jsonToSave);
        }
        
        $activity->setSynchronizedAt(new \DateTime());
        
        //Prise en compte des équipments par défaut selon le sport
        $equipmentsIds = $equipmentRep->getMyEquipmentsIdsByDefault($activity->getUser()->getId(), $activity->getSport()->getId());
        foreach ($equipmentsIds as $equipmentId) {
            $activity->addEquipment($equipmentRep->find($equipmentId));
        }
        
        $em->persist($activity);
        $em->flush();
        
        $activityRep->subscribeOnActivity($activity, $activity->getUser());

        //Gain de points 
        $leagueLevelService = $this->getContainer()->get('ks_league.leagueLevelService');
        $leagueLevelService->activitySessionEarnPoints($activity, $userService->getUser());

        //Pour chaques activités on a un identifiant relatif au service qu'on synchro
        $acfs = new \Ks\ActivityBundle\Entity\ActivityComeFromService();
        $acfs->setActivity($activity);
        $acfs->setService($userService->getService());
        $acfs->setIdWebsiteActivityService($stravaActivity['uniqId']);
        $acfs->setSourceDetailsActivity(json_encode($stravaActivity));
        $acfs->setTypeSource('JSON');

        $em->persist($acfs);
        $em->flush();
    }
    
    /**
     * FIXME: Code en double avec ActivityService
     * 
     * @param type $duration
     * @return \DateTime
     */
    public function secondesToTimeDuration($duration)
    {
        $heure      = intval(abs($duration / 3600));
        $duration   = $duration - ($heure * 3600);
        $minute     = intval(abs($duration / 60));
        $duration   = $duration - ($minute * 60);
        $seconde    = round($duration);
        
        return new \DateTime("$heure:$minute:$seconde");
    }
    
    /**
     * FIXME: Code en double avec ActivityService
     * 
     * @param type $startDate
     * @param type $duration
     * @return \DateTime
     */
    public function calculEndDate($startDate, $duration)
    {
        $endDate    = new \DateTime($startDate->format("Y-m-d H:i:s"));
        $duration   = round($duration);
        if ( $duration > 0 ) {
            $i = new \DateInterval('PT'.$duration.'S');
            $endDate->add($i);
        }
        
        return $endDate;
    }
}
