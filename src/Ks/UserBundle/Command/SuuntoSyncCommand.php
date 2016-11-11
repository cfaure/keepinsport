<?php

namespace Ks\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class SuuntoSyncCommand extends ContainerAwareCommand
{
    
    protected $_enduranceOnEarthType;
    protected $_enduranceUnderWaterType;
    
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('ks:user:suuntosync')
            ->setDescription('Synchroniser les activités d\'un utilisateur suunto')
            ->setDefinition(array(
                new InputArgument('access_token', InputArgument::REQUIRED, 'Access token'),
            ))
            ->setHelp(<<<EOT
La commande <info>ks:user:suuntosync</info> synchronise les activités Suunto d'un utilisateur avec son profil Keepinsport :

  <info>php app/console ks:user:suuntosync access_token_de_l_utilisateur</info>
EOT
            );
    }
    
    /**
     * 
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //$output->writeln("Memoire start : ".round(memory_get_usage()/1024/1024,2)."Mo");
        //$stime = microtime(true);
     
        $accessToken    = $input->getArgument('access_token');
        $em             = $this->getContainer()->get('doctrine')->getEntityManager();
        $dbh            = $em->getConnection();
        $userService    = $em->getRepository('KsUserBundle:UserHasServices')->findOneByToken($accessToken);
        $preferenceRep  = $em->getRepository('KsUserBundle:Preference');
        $jobPath        = $this->getContainer()->get('kernel')->getRootdir().'/jobs';
        $this->_enduranceOnEarthType    = $em->getRepository('KsActivityBundle:SportType')->findOneByLabel("endurance");
        $this->_enduranceUnderWaterType = $em->getRepository('KsActivityBundle:SportType')->findOneByLabel("endurance_under_water");
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $notificationService = $this->getContainer()->get('ks_notification.notificationService');
        $keepinsportUser = $em->getRepository('KsUserBundle:User')->findOneByUsername( "keepinsport" );

        $output->writeln("START / Sync SUUNTO");
        
        if (!is_object($userService)) {
            $output->writeln("Impossible de trouver l'utilisateur qui possede le jeton ".$accessToken."");
            return;
        }
        
        if (file_exists($jobPath.'/pending/suunto/'.$accessToken.'.job')){
            rename(
                $jobPath.'/pending/suunto/'.$accessToken.'.job',
                $jobPath.'/inprogress/suunto/'.$accessToken.'.job'
            );
        } else {
            $output->writeln('No pending job found');
            return;
        }
        
        $output->writeln("---fichier renommé");
        
        $lastSyncAt = $userService->getLastSyncAt();
        $lastSyncAt = $lastSyncAt != null ? $lastSyncAt->format("Y-m-d") : '1970-01-01';
        $suuntoApi  = $this->getContainer()->get('ks_user.suunto');
        
        //$output->writeln("Memoire init : ".round(memory_get_usage()/1024/1024,2)."Mo");
        
        try {
            $suuntoApi->setAccessToken($accessToken);
            $output->writeln("---avant setAppIntoUse");
            $email = $userService->getConnectionId();
            //$email = "Partner7@movescount.com";
            $startDate = $userService->getLastSyncAt();
            $suuntoSearchNumber = $preferenceRep->findOneBy( array("code" => "suuntoSearchNumber"))->getVal1();
            $lastSyncAt = $startDate != null ? $startDate->sub(new \DateInterval("P".$suuntoSearchNumber."D"))->format("Y-m-d") : '1970-01-01';
            
            //if ($startDate == null) $startDate = new \DateTime('now');
            
            $responseCode = $suuntoApi->setAppIntoUse($email, $lastSyncAt);
            $output->writeln("---setAppIntoUse email/startDate (".$email. "/" . $startDate->format("Y-m-d").") : ". $responseCode);
            
            if ($responseCode == 200) {
                //FMO: demande en cours à valider coté MOVESCOUNT
                $userService->setStatus('to validate on MOVESCOUNT');
                $output->writeln("------to validate on MOVESCOUNT...");
                
                //FMO : pour éviter tout problème avec les autres services on les désactive tous :
                $userHasOtherServices = $em->getRepository('KsUserBundle:UserHasServices')->findByUser($userService->getUser()->getId());
                foreach($userHasOtherServices as $userHasOtherService) {
                    $output->writeln("------service ".$userHasOtherService->getService()->getName());
                    if ($userHasOtherService->getService()->getName() != 'Suunto') {
                        $output->writeln("---------désactivé !");
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

                $output->writeln("---Recherche de nouvelles activités SUUNTO...");
                // FMO : volontairement pour aller chercher l'historique on part sur les 100 dernières par défaut ? $suuntoSearchNumber = $preferenceRep->findOneBy( array("code" => "suuntoSearchNumber"))->getVal1();
                $suuntoActivities = $suuntoApi->getActivities(100);
                
                if (is_bool($suuntoActivities) && ! $suuntoActivities) {
                    $message = "ERREUR curl_exec SUUNTO sync getActivities() with user : " . $userService->getUser()->getId();
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
                }
                else {
                    //var_dump($suuntoActivities);
                    $output->writeln("---(activities = ".count($suuntoActivities).")");
                
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
                    foreach ($suuntoActivities as $suuntoActivity) {
                        $output->writeln("------Traitement MoveId=".$suuntoActivity['MoveID']."...");
                        //$suuntoActivity['MoveID'] = 30652910;

                        if (array_key_exists($suuntoActivity['MoveID'], $activitiesAlreadyImported)) {
                            $output->writeln('------activitée déja importée : '.$suuntoActivity['MoveID']);
                            continue;
                        }
                        $activityDetail = $suuntoApi->getActivity($suuntoActivity['MoveID']);
                        //var_dump($activityDetail);

                        $activityTrackPoints = $suuntoApi->getTrackPoints($suuntoActivity['MoveID']);
                        $activityDetail['trackPoints'] = $activityTrackPoints['TrackPoints'];
                        //var_dump($activityTrackPoints);
                        
                        //FMO :  attente retour SUUNTO
                        $activitySamples = $suuntoApi->getSamples($suuntoActivity['MoveID']);
                        $activityDetail['sampleSets'] = $activitySamples['SampleSets'];
                        //var_dump($activitySamples);
                        //exit;
                        
                        if (is_bool($activityDetail) && ! $activityDetail || is_bool($activityTrackPoints) && ! $activityTrackPoints) { // || is_bool($activitySamples) && ! $activitySamples) {
                            $output->writeln('Erreur lors de la récupération du détail activité : '.$suuntoActivity['MoveID']);
                            $message = "Erreur lors de la récupération du détail activité : ".$suuntoActivity['MoveID'];
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
                        }
                        else {
                            //$activityDetail = json_decode(utf8_encode($activityDetail), true);
                            $output->writeln("---------(before saveUserAcvtivity)");
                            $this->saveUserActivity($activityDetail, $userService, $output);
                            $output->writeln("---------Import effectué avec succès: ".$suuntoActivity['MoveID']);
                        }
                    }
                    $userService->setStatus('done');
                    $output->writeln("-----Toutes les activités ont été importées avec succès !:");
                    $userService->setLastSyncAt(new \DateTime('now'));
                    $em->persist($userService);
                    $em->flush();
                }
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
                $message = "ATTENTION ! Ton adresse mail Keepinsport utilisée pour l'activation de la synchro SUUNTO ne correspond malheureusement pas à un compte MOVESCOUNT valide :( N'hésite pas à modifier ton email au niveau de ton profil sur Keepinsport (qui doit correspondre à ton login sur Movescount) Pour y accéder, clic sur ton avatar tout en haut à droite, puis utilise le bouton 'Editer mon profil' ;)";
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
            
            //FMO : dans les 2 cas on déplace le fichier qui a servi a faire soit la demande d'activation sur MOVESCOUNT soit l'import asynchrone d'activités
            if (file_exists($jobPath.'/inprogress/suunto/'.$accessToken.'.job')) {
                rename(
                    $jobPath.'/inprogress/suunto/'.$accessToken.'.job',
                    $jobPath.'/done/suunto/'.$accessToken.'.job'
                );
            }
            
        } catch (Exception $e) {
            $output->writeln('Exception déclenchée: '.$e->getMessage());
            
            $userService->setStatus('done');
            $em->persist($userService);
            $em->flush();
        }
        
        
        
        //$output->writeln('Memoire fin : '.round(memory_get_usage()/1024/1024,2).'Mo');
        //$duration = microtime(true) - $stime;
        //$output->writeln('Temps d\'exec: '.$duration);
    }
    
    /**
     * Retourne l'intitulé du sport à partir de son id
     * @param int $sportId
     * @return string
     */
    public function getSportLabel($sportId)
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
    
    /**
     *
     * @param array $suuntoActivity
     * @param type $userService 
     */
    protected function saveUserActivity(array $suuntoActivity, $userService, $output)
    {
        $em             = $this->getContainer()->get('doctrine')->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $equipmentRep   = $em->getRepository('KsUserBundle:Equipment');
        $importService  = $this->getContainer()->get('ks_activity.importActivityService');
        $user           = $userService->getUser();
        $activity       = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth($user);
        
        $codeSport      = $this->getSportLabel($suuntoActivity['ActivityID']);
        
        $sport          = $em->getRepository('KsActivityBundle:Sport')->findOneByCodeSport($codeSport);

        $output->writeln("---------(sport = " . $sport->getLabel() .")");
        
        if (!is_object($sport)) {
            //FMo : pas nécessaire pour l'instant
            $sport = new \Ks\ActivityBundle\Entity\Sport();
            $sport->setLabel($codeSport);
            $sport->setCodeSport($codeSport);
            $sport->setSportType($this->_enduranceOnEarthType);
            $em->persist($sport);
            $em->flush();
        }
        $activity->setSport($sport);
        $activity->setSource('suunto');
        
        if (isset($suuntoActivity['Distance'])) {
            $activity->setDistance($suuntoActivity['Distance'] / 1000.0);
        }
        else if (isset($trackingDatas) && $trackingDatas != null) {
            $activity->setDistance($trackingDatas['info']['distance']);
        }
        
        if (isset($suuntoActivity['Duration'])) {
            $activity->setDuration($this->secondesToTimeDuration($suuntoActivity['Duration']));
            $activity->setTimeMoving($suuntoActivity['Duration']);
        }
        else if (isset($trackingDatas) && $trackingDatas != null){
            $activity->setDuration($trackingDatas['info']['timeDuration']);
            $activity->setTimeMoving($trackingDatas['info']['duration']);
        }
        
        if (isset($suuntoActivity['AvgSpeed'])) {
            $activity->setSpeedAverage($suuntoActivity['AvgSpeed']);
        };
        if (isset($suuntoActivity['LocalStartTime'])) {
            $issuedAt = new \DateTime($suuntoActivity['LocalStartTime']);
            //$issuedAt->setTimeZone(new \DateTimeZone("Europe/Paris"));
            $activity->setIssuedAt($issuedAt);
            $activity->setModifiedAt(new \DateTime());
        }
        if (isset($suuntoActivity['Notes'])) {
            $activity->setDescription(html_entity_decode($suuntoActivity['Notes']));
        }
        if (isset($suuntoActivity['Energy'])) {
            $activity->setCalories($suuntoActivity['Energy']);
        }
        if (isset($suuntoActivity['AscentAltitude'])) {
            $activity->setElevationGain($suuntoActivity['AscentAltitude']);
        }
        if (isset($suuntoActivity['DescentAltitude'])) {
            $activity->setElevationLost($suuntoActivity['DescentAltitude']);
        }
        $output->writeln("---------before buildJsonToSave");
        $trackingDatas = $importService->buildJsonToSave($user, $suuntoActivity, 'suunto'); 
        $output->writeln("---------after buildJsonToSave");
        $firstWaypoint = $importService->getFirstWaypointNotEmpty($trackingDatas);

        if ($firstWaypoint != null) {
            $activity->setPlace(
                $importService->findPlace($firstWaypoint["lat"], $firstWaypoint["lon"])
            );
        }

        if (isset($trackingDatas) && $trackingDatas != null) $activity->setTrackingDatas($trackingDatas);
        
        $activity->setSynchronizedAt(new \DateTime());
        
        //Prise en compte des équipments par défaut selon le sport
        $equipmentsIds = $equipmentRep->getMyEquipmentsIdsByDefault($activity->getUser()->getId(), $activity->getSport()->getId());
        foreach ($equipmentsIds as $equipmentId) {
            $activity->addEquipment($equipmentRep->find($equipmentId));
        }
        
        $em->persist($activity);
        $em->flush();

        //Gain de points 
        $leagueLevelService = $this->getContainer()->get('ks_league.leagueLevelService');
        $leagueLevelService->activitySessionEarnPoints($activity, $user);
        
        //On l'abonne à son activité
        $activityRep->subscribeOnActivity($activity, $user);

        //Pour chaques activités on a un identifiant relatif au service qu'on synchro
        $acfs = new \Ks\ActivityBundle\Entity\ActivityComeFromService();
        $acfs->setActivity($activity);
        $acfs->setService($userService->getService());
        $acfs->setIdWebsiteActivityService($suuntoActivity['MoveID']);
        $acfs->setSourceDetailsActivity(json_encode($suuntoActivity));
        $acfs->setTypeSource('JSON');

        $em->persist($acfs);
        $em->flush();
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
    
    
    public function wd_remove_accents($str, $charset='utf-8')
   {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        return $str;
   }
   
   
    public function formatNameSport($sport) {
        return str_replace (" " , "-" , strtolower($this->wd_remove_accents($sport)) );
    }
    
}
