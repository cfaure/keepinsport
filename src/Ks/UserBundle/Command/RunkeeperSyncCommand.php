<?php

namespace Ks\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class RunkeeperSyncCommand extends ContainerAwareCommand
{
    
    protected $_enduranceOnEarthType;
    protected $_enduranceUnderWaterType;
    
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('ks:user:runkeepersync')
            ->setDescription('Synchroniser les activités d\'un utilisateur runkeeper')
            ->setDefinition(array(
                new InputArgument('access_token', InputArgument::REQUIRED, 'Access token'),
            ))
            ->setHelp(<<<EOT
La commande <info>ks:user:runkeepersync</info> synchronise les activités Runkeeper d'un utilisateur avec son profil Keepinsport :

  <info>php app/console ks:user:runkeepersync access_token_de_l_utilisateur</info>
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
        $jobPath        = $this->getContainer()->get('kernel')->getRootdir().'/jobs';
        $this->_enduranceOnEarthType    = $em->getRepository('KsActivityBundle:SportType')->findOneByLabel("endurance");
        $this->_enduranceUnderWaterType = $em->getRepository('KsActivityBundle:SportType')->findOneByLabel("endurance_under_water");
        
        if (!is_object($userService)) {
            $output->writeln("Impossible de trouver l'utilisateur qui possede le jeton ".$accessToken."");
            return;
        }
        
        if (file_exists($jobPath.'/pending/runkeeper/'.$accessToken.'.job')){
            rename(
                $jobPath.'/pending/runkeeper/'.$accessToken.'.job',
                $jobPath.'/inprogress/runkeeper/'.$accessToken.'.job'
            );
        } else {
            $output->writeln('No pending job found');
            return;
        }
        
        $lastSyncAt = $userService->getLastSyncAt();
        $lastSyncAt = $lastSyncAt != null ? $lastSyncAt->format("Y-m-d") : '1970-01-01';
        $rkApi      = $this->getContainer()->get('ks_user.runkeeper');
        
        //$output->writeln("Memoire init : ".round(memory_get_usage()/1024/1024,2)."Mo");
        
        try {
            $userService->setStatus('pending');
            $em->persist($userService);
            $em->flush();
            
            $rkApi->setAccessToken($accessToken);
            $rkActivities = $rkApi->getFitnessActivities();
            
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
            foreach ($rkActivities as $rkActivity) {
                if (array_key_exists($rkActivity['uri'], $activitiesAlreadyImported)) {
                    //$output->writeln('Déja importé: '.$rkActivity['uri']);
                    continue;
                }
                $activityDetail = $rkApi->getFitnessActivity($rkActivity['uri']);
                $activityDetail = json_decode(utf8_encode($activityDetail), true);
                $this->saveUserActivity($activityDetail, $userService);
                //$output->writeln("Import effectué avec succès: ".$rkActivity['uri']);
            }
            
            // on déplace le fichier qui a servi a faire l'import asynchrone 
            if (file_exists($jobPath.'/inprogress/runkeeper/'.$accessToken.'.job')) {
                rename(
                    $jobPath.'/inprogress/runkeeper/'.$accessToken.'.job',
                    $jobPath.'/done/runkeeper/'.$accessToken.'.job'
                );
            }
            
            $userService->setLastSyncAt(new \DateTime('now'));
            $userService->setStatus('done');
            $em->persist($userService);
            $em->flush();
            
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
     *
     * @param array $rkActivity
     * @param type $userService 
     */
    protected function saveUserActivity(array $rkActivity, $userService)
    {
        $em             = $this->getContainer()->get('doctrine')->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $equipmentRep   = $em->getRepository('KsUserBundle:Equipment');
        $importService  = $this->getContainer()->get('ks_activity.importActivityService');
        $user           = $userService->getUser();
        $activity       = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth($user);
        $codeSport      = $this->formatNameSport($rkActivity['type']);
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
        $activity->setSource('runkeeper');
        if (isset($rkActivity['total_distance'])) {
            $activity->setDistance($rkActivity['total_distance'] / 1000.0);
        }
        if (isset($rkActivity['duration'])) {
            $activity->setDuration($this->secondesToTimeDuration($rkActivity['duration']));
            $activity->setTimeMoving($rkActivity['duration']);
        };
        if (isset($rkActivity['start_time'])) {
            $issuedAt = new \DateTime($rkActivity['start_time']);
            //$issuedAt->setTimeZone(new \DateTimeZone("Europe/Paris"));
            $activity->setIssuedAt($issuedAt);
            $activity->setIssuedAt(new \DateTime($rkActivity['start_time']));
            $activity->setModifiedAt(new \DateTime());
        }
        if (isset($rkActivity['notes'])) {
            $activity->setDescription(html_entity_decode($rkActivity['notes']));
        }
        if (isset($rkActivity['total_calories'])) {
            $activity->setCalories($rkActivity['total_calories']);
        }
        if (isset($rkActivity['climb'])) {
            $activity->setElevationGain($rkActivity['climb']);
        }

        $trackingDatas = $importService->buildJsonToSave($rkActivity, 'runkeeper'); 
        $firstWaypoint = $importService->getFirstWaypointNotEmpty($trackingDatas);

        if ($firstWaypoint != null) {
            $activity->setPlace(
                $importService->findPlace($firstWaypoint["lat"], $firstWaypoint["lon"])
            );
        }

        if( $trackingDatas != null && is_array($trackingDatas) ) {
            $activity->setDistance($trackingDatas['info']['distance']);
            $activity->setDuration($trackingDatas['info']['timeDuration']);
            $activity->setTimeMoving($trackingDatas['info']['duration']);
            $activity->setTrackingDatas($trackingDatas);
        }

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
        $acfs->setIdWebsiteActivityService($rkActivity['uri']);
        $acfs->setSourceDetailsActivity(json_encode($rkActivity));
        $acfs->setTypeSource('JSON');

        $em->persist($acfs);
        $em->flush();
    }

    /**
     * @see Command
     */
    protected function execute3(InputInterface $input, OutputInterface $output)
    {
        $accessToken        = $input->getArgument('access_token');
        $em                 = $this->getContainer()->get('doctrine')->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $UserHasServices    = $em->getRepository('KsUserBundle:UserHasServices')->findOneByToken($accessToken);
        
        $importActivityService = $this->getContainer()->get('ks_activity.importActivityService');
        
        if(file_exists($this->getContainer()->get('kernel')->getRootdir().'/jobs/pending/runkeeper/'.$accessToken.'.job')){
              rename($this->getContainer()->get('kernel')->getRootdir().'/jobs/pending/runkeeper/'.$accessToken.'.job', $this->getContainer()->get('kernel')->getRootdir().'/jobs/inprogress/runkeeper/'.$accessToken.'.job');
        }
        
        $lastSyncAt = $UserHasServices->getLastSyncAt();
        if($lastSyncAt!=null){
             $lastSyncAt = $lastSyncAt->format("Y-m-d");
        }else{
            $lastSyncAt = '1970-01-01';
        }
        
        if (!is_object($UserHasServices) ) {
            $output->writeln("Impossible de trouver l'utilisateur qui possede le jeton ".$accessToken."");
        }
        
        $user    = $UserHasServices->getUser();
        $service = $UserHasServices->getService();

        
        $output->writeln("Memoire avant de requeter sur l'api : ".round(memory_get_usage()/1024/1024,2)."Mo");
        
        $now = new \DateTime('Now');
        $now = $now->format('Y-m-d-h-i-s');
        

        $fileLog = fopen ($this->getContainer()->get('kernel')->getRootdir().'/jobs/logs/runkeeper/'.$accessToken.'-'.$now.'.log', "a+");

        
        try {
            $rkApi          = $this->getContainer()->get('ks_user.runkeeper');
            $rkApi->setAccessToken($accessToken);
            
            $nbParPages = 1;
            if(!empty($lastSyncAt)){
                $activities  = $rkApi->getFitnessActivities(0,$nbParPages,$lastSyncAt); 
            }else{
                $activities  = $rkApi->getFitnessActivities(0,$nbParPages);
            }
            
            $nomberActivitites = $activities->size;
            
            if($nomberActivitites==0){

                $output->writeln("Pas de nouvelles activites a importer depuis $lastSyncAt");
                
            }else{
                
                $output->writeln("Recuperation de $nomberActivitites activite(s) a partir de : $lastSyncAt ");

                $aActivities = $activities->items;
                //$nbPages     =  floor($nomberActivitites/$nbParPages);
                $nbPages = 1;
                $aEndurance = array("Running", "Cycling", "Mountain Biking", "Walking","Hiking", "Downhill Skiing", "Cross-Country Skiing", "Snowboarding", "Skating","Wheelchair", "Rowing","Elliptical","Other");
                $aEnduranceUnderWater = array("Swimming");
                $enduranceOnEarthType = $em->getRepository('KsActivityBundle:SportType')->findOneByLabel("endurance");

                if (!is_object($enduranceOnEarthType) ) {
                    $output->writeln("Impossible de trouver le type de sport endurance");
                }

                $enduranceUnderWaterType = $em->getRepository('KsActivityBundle:SportType')->findOneByLabel("endurance_under_water");
                if (!is_object($enduranceUnderWaterType) ) {
                    $output->writeln("Impossible de trouver le type de sport endurance sous l eau ");
                }

                $output->writeln("Memoire apres avoir recup $nbParPages activites : ".round(memory_get_usage()/1024/1024,2)."Mo");

                $output->writeln("Traitement des activites par packets de $nbParPages ");

                $a = 0;

                for ($i = 0; $i <= $nbPages; ++$i) {

                    $output->writeln("Traitement du packet $i ");

                    if ($i != 0){

                        $activities  = $rkApi->getFitnessActivities($i,$nbParPages);
                        $aActivities = $activities->items;
                    }
                    $activityComeFromServiceRepo = $em->getRepository('KsActivityBundle:ActivityComeFromService');
                    foreach ($aActivities as $activity) {

                        $activityAlreadyExist = $activityComeFromServiceRepo->findOneBy(array("id_website_activity_service"=>$activity->uri));

                        $a = $a + 1;
                        
                        if ($activityAlreadyExist == null) {

                            $now = new \DateTime('Now');
                            $now = $now->format('Y/m/d h:i:s');
                            
                            $codeSport  = $this->formatNameSport($activity->type);
                            $sport      = $em->getRepository('KsActivityBundle:Sport')
                                ->findOneByCodeSport($codeSport);

                            if (!is_object($sport)) {
                                $sport = new \Ks\ActivityBundle\Entity\Sport();
                                $sport->setLabel($codeSport);
                                $sport->setCodeSport($codeSport);
                                $sport->setSportType($enduranceOnEarthType);
                                $em->persist($sport);
                                $em->flush();
                            }
                            $activityDetail = json_decode($rkApi->requestJSONHealthGraph($activity->uri, 0, 500, '1970-01-01', date('Y-m-d'), '1970-01-01', date('Y-m-d') ));
                            
                            if (in_array($activity->type, $aEndurance)) {

                                $ActivitySessionEnduranceOnEarth = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth($user);
                                $ActivitySessionEnduranceOnEarth->setSource("runkeeper");
                                if (isset($activity->total_distance)) {
                                    $ActivitySessionEnduranceOnEarth->setDistance($activity->total_distance / 1000.0);
                                }
                                if (isset($activity->duration)) {
                                    $ActivitySessionEnduranceOnEarth->setDuration($this->secondesToTimeDuration($activity->duration));
                                    $ActivitySessionEnduranceOnEarth->setTimeMoving($activity->duration);
                                };
                                isset($activity->start_time)?  $ActivitySessionEnduranceOnEarth->setIssuedAt(new \DateTime($activity->start_time)) : "";
                                isset($activity->start_time)?  $ActivitySessionEnduranceOnEarth->setModifiedAt(new \DateTime($activity->start_time)) : "";
                                isset($activityDetail->notes)?  $ActivitySessionEnduranceOnEarth->setDescription(utf8_encode(html_entity_decode($activityDetail->notes))) : "";

                                $ActivitySessionEnduranceOnEarth->setSport($sport);
                                isset($activityDetail->total_calories)?  $ActivitySessionEnduranceOnEarth->setCalories($activityDetail->total_calories) : "";
                                isset($activityDetail->climb)?  $ActivitySessionEnduranceOnEarth->setElevationGain($activityDetail->climb) : "";
                                $maxElevation = 0;
                                $minElevation = 10000;
                                if(isset($activityDetail->path)){
                                    foreach($activityDetail->path as $gpsPoint){
                                        if($gpsPoint->altitude > $maxElevation){
                                            $maxElevation = $gpsPoint->altitude;
                                        }

                                        if($gpsPoint->altitude < $minElevation){
                                            $minElevation = $gpsPoint->altitude;
                                        }
                                    }
                                    $ActivitySessionEnduranceOnEarth->setElevationMin($minElevation);
                                    $ActivitySessionEnduranceOnEarth->setElevationMax($maxElevation);
                                }



                                $em->persist($ActivitySessionEnduranceOnEarth);
                                $em->flush();

                                //Gain de points 
                                //$parameters = array("calories" => $activityDetail->total_calories);
                                $leagueLevelService   =  $this->getContainer()->get('ks_league.leagueLevelService');
                                $leagueLevelService->activitySessionEarnPoints($ActivitySessionEnduranceOnEarth, $user);

                                //Pour chaques activités on a un identifiant relatif au service qu'on synchro
                                $ActivityComeFromService = new \Ks\ActivityBundle\Entity\ActivityComeFromService();
                                $ActivityComeFromService->setActivity($ActivitySessionEnduranceOnEarth);


                                $ActivityComeFromService->setService($service);
                                $ActivityComeFromService->setIdWebsiteActivityService($activity->uri);
                                //$output->writeln("Memoire avant ecriture du JSON : ".round(memory_get_usage()/1024/1024,2)."Mo");
                                
                                //On transforme l'objet std en array
                                $aRunkeeperDatas = json_decode( $rkApi->requestJSONHealthGraph($activity->uri, 0, 500, '1970-01-01', date('Y-m-d'), '1970-01-01', date('Y-m-d') ), true );
                                
                                if( $aRunkeeperDatas != null && is_array($aRunkeeperDatas) ) {
                                    $trackingDatas  = $importActivityService->buildJsonToSave($aRunkeeperDatas, "runkeeper"); 
                                    $firstWaypoint  = $importActivityService->getFirstWaypointNotEmpty($trackingDatas);
                                    
                                    if( $firstWaypoint != null ) {
                                        $ActivitySessionEnduranceOnEarth->setPlace(
                                            $importActivityService->findPlace($firstWaypoint["lat"], $firstWaypoint["lon"])
                                        );
                                    }
                                    
                                    $ActivitySessionEnduranceOnEarth->setTrackingDatas( $trackingDatas );
                                }

                                
                                $ActivityComeFromService->setSourceDetailsActivity(json_encode(
                                    $aRunkeeperDatas
                                ));
                                
                
                                //$ActivityComeFromService->setSourceDetailsActivity($rkApi->requestJSONHealthGraph($activity->uri));
                                //$output->writeln("Memoire après ecriture du JSON : ".round(memory_get_usage()/1024/1024,2)."Mo");
                                $ActivityComeFromService->setTypeSource("JSON");

                                $ActivitySessionEnduranceOnEarth->setSynchronizedAt(new \DateTime());
                                $em->persist($ActivitySessionEnduranceOnEarth);
                                $em->persist($ActivityComeFromService);
                                $em->flush();

                                $output->writeln("Import de l'activite num ".$a." type :".$activity->type." effectue avec success");

                                unset($ActivitySessionEnduranceOnEarth);
                                unset($ActivityComeFromService);


                            } else if (in_array($activity->type, $aEnduranceUnderWater)) {

                                $ActivitySessionEnduranceUnderWater = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceUnderWater($user);
                                $ActivitySessionEnduranceUnderWater->setSource("runkeeper");
                                isset($activity->total_distance)? $ActivitySessionEnduranceUnderWater->setDistance($activity->total_distance) : "";
                                isset($activity->duration)? $ActivitySessionEnduranceUnderWater->setDuration($this->secondesToTimeDuration($activity->duration)) : "";
                                isset($activity->start_time) && !empty($activity->start_time)?  $ActivitySessionEnduranceUnderWater->setIssuedAt(new \DateTime($activity->start_time)) : "";
                                isset($activity->start_time) && !empty($activity->start_time)?  $ActivitySessionEnduranceUnderWater->setModifiedAt(new \DateTime($activity->start_time)) : "";

                                $ActivitySessionEnduranceUnderWater->setSport($sport);
                                isset($activityDetail->total_calories)?  $ActivitySessionEnduranceUnderWater->setCalories($activityDetail->total_calories) : "";
                                isset($activityDetail->notes)?  $ActivitySessionEnduranceUnderWater->setDescription(utf8_encode(html_entity_decode($activityDetail->notes))) : "";

                                $em->persist($ActivitySessionEnduranceUnderWater);
                                $em->flush();
                                
                                $activityRep->subscribeOnActivity($ActivitySessionEnduranceUnderWater, $user);

                                //$parameters = array("calories" => $activityDetail->total_calories);
                                $leagueLevelService   =  $this->getContainer()->get('ks_league.leagueLevelService');
                                $leagueLevelService->activitySessionEarnPoints($ActivitySessionEnduranceUnderWater, $user);

                                $ActivityComeFromService = new \Ks\ActivityBundle\Entity\ActivityComeFromService();
                                $ActivityComeFromService->setActivity($ActivitySessionEnduranceUnderWater);
                                $ActivityComeFromService->setService($service);
                                $ActivityComeFromService->setIdWebsiteActivityService($activity->uri);

                                //On transforme l'objet std en array
                                
                                $aRunkeeperDatas = json_decode( $rkApi->requestJSONHealthGraph($activity->uri), true );

                                if( $aRunkeeperDatas != null && is_array($aRunkeeperDatas) ) {
                                    $trackingDatas  = $importActivityService->buildJsonToSave($aRunkeeperDatas, "runkeeper"); 
                                    $firstWaypoint  = $importActivityService->getFirstWaypointNotEmpty($trackingDatas);

                                    if( $firstWaypoint != null ) {
                                        $ActivitySessionEnduranceUnderWater->setPlace(
                                            $importActivityService->findPlace($firstWaypoint["lat"], $firstWaypoint["lon"])
                                        );
                                    }

                                    $ActivitySessionEnduranceUnderWater->setTrackingDatas( $trackingDatas );
                                }
                
                                $ActivityComeFromService->setSourceDetailsActivity(json_encode(
                                    $aRunkeeperDatas
                                ));
                                $ActivityComeFromService->setTypeSource("JSON");

                                $em->persist($ActivityComeFromService);
                                $em->flush();

                                $output->writeln("Import de l activite num ".$a." type :".$activity->type." effectue avec success"); 

                                unset($ActivitySessionEnduranceUnderWater);
                                unset($ActivityComeFromService);

                            }


                            unset($sport);

                            unset($activityDetail);



                            fputs($fileLog, "Import de l'activite num ".$a." type :".$activity->type." effectue avec success : $now \n");

                            $output->writeln("Memoire ".round(memory_get_usage()/1024/1024,2)."Mo");


                        }else{
                            $output->writeln("L activite num ".$a." type :".$activity->type." avec l id : $activity->uri existe deja - traitement de l activite suivante ");
                        }
                    }
                    
                    $output->writeln("Import des $nbParPages dernieres activites effectues avec success"); 
                }
            }

            //on déplace le fichier qui a servit a faire l'import asynchrone 
            if(file_exists($this->getContainer()->get('kernel')->getRootdir().'/jobs/inprogress/runkeeper/'.$accessToken.'.job')){
                rename($this->getContainer()->get('kernel')->getRootdir().'/jobs/inprogress/runkeeper/'.$accessToken.'.job', $this->getContainer()->get('kernel')->getRootdir().'/jobs/done/runkeeper/'.$accessToken.'.job');
            }


        } catch (\Exception $e) {
            throw $e;
           
        }
        
        
      
        
        
    }
    
    
    
    public function secondesToTimeDuration($duration){
        $heure = intval(abs($duration / 3600));
        $duration = $duration - ($heure * 3600);
        $minute = intval(abs($duration / 60));
        $duration = $duration - ($minute * 60);
        $seconde = round($duration);
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
