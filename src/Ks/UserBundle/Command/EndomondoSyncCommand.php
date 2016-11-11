<?php

namespace Ks\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class EndomondoSyncCommand extends ContainerAwareCommand
{
    
    protected $_enduranceOnEarthType;
    protected $_enduranceUnderWaterType;
    
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('ks:user:endomondosync')
            ->setDescription('Synchroniser les activités d\'un utilisateur endomondo')
            ->setDefinition(array(
                new InputArgument('token', InputArgument::REQUIRED, 'Token de l\'utilisateur a synchroniser'),
            ))
            ->setHelp(<<<EOT
La commande <info>ks:user:endomondosync</info> synchronise les activités Endomondo d'un utilisateur avec son profil Keepinsport :

  <info>php app/console ks:user:endomondo token</info>
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
     
        $token          = $input->getArgument('token');
        $em             = $this->getContainer()->get('doctrine')->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $dbh            = $em->getConnection();
        $service        = $em->getRepository('KsUserBundle:Service')->findOneByName('Endomondo');
        $preferenceRep  = $em->getRepository('KsUserBundle:Preference');
        
        //Appel des services
        $leagueLevelService         = $this->getContainer()->get('ks_league.leagueLevelService');
        
        $userService= $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array(
            'token'     => $token,
            'service'   => $service->getId(),
            'is_active' => 1
        ));
        
        if (!is_object($userService)) {
            $output->writeln("Impossible de trouver le service Endomondo pour l'utilisateur id: ".$userId."");
            return;
        }
        
        $jobPath = $this->getContainer()->get('kernel')->getRootdir().'/jobs';
        $this->_enduranceOnEarthType = $em
            ->getRepository('KsActivityBundle:SportType')
            ->findOneByLabel("endurance");
        
        if (file_exists($jobPath.'/pending/endomondo/'.$token.'.job')){
            rename(
                $jobPath.'/pending/endomondo/'.$token.'.job',
                $jobPath.'/inprogress/endomondo/'.$token.'.job'
            );
        } else {
            $output->writeln('No pending job found');
            return;
        }
        
        //$lastSyncAt     = $userService->getLastSyncAt();
        //$lastSyncAt     = $lastSyncAt != null ? $lastSyncAt->format("Y-m-d") : '1970-01-01';
        $endomondoApi   = $this->getContainer()->get('ks_user.endomondo');
        
        //$output->writeln("Memoire init : ".round(memory_get_usage()/1024/1024,2)."Mo");
        
        try {
            $endomondoApi->setAuthToken($userService->getToken());
            //$output->writeln(print_r($ret, true));exit;
            $endomondoSearchNumber = $preferenceRep->findOneBy( array("code" => "endomondoSearchNumber"))->getVal1();
            $activities = $endomondoApi->fetchWorkouts($endomondoSearchNumber);
                        
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
            foreach ($activities as $key => &$activity) {
                if (array_key_exists($key, $activitiesAlreadyImported)) {
                    $output->writeln('Déja importé, id: '.$key);
                    continue;
                }
                $activity['path']   = isset($activity['has_points']) && $activity['has_points'] == 1 ?
                    $endomondoApi->fetchTrackPoints($key)
                    : array();
                $activity['type']   = $endomondoApi->getSportLabel($activity['sport']);
                $this->saveUserActivity($activity, $userService);
            }
                        
            // on déplace le fichier qui a servi a faire l'import asynchrone 
            if (file_exists($jobPath.'/inprogress/endomondo/'.$token.'.job')) {
                rename(
                    $jobPath.'/inprogress/endomondo/'.$token.'.job',
                    $jobPath.'/done/endomondo/'.$token.'.job'
                );
                $userService->setStatus('done');
                $userService->setLastSyncAt(new \Datetime());
                $userService->setFirstSync(0);
                $em->persist($userService);
                $em->flush();
                
                //Mise à jour des étoiles
                $leagueCategoryId = $userService->getUser()->getLeagueLevel()->getCategory()->getId();
                if( is_integer( $leagueCategoryId ) ) $leagueLevelService->leagueRankingUpdate( $leagueCategoryId );
            }
        } catch (Exception $e) {
            $output->writeln('Exception déclenchée: '.$e->getMessage());
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
    protected function saveUserActivity(array $endoActivity, \Ks\UserBundle\Entity\UserHasServices $userService)
    {
        $em             = $this->getContainer()->get('doctrine')->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $equipmentRep   = $em->getRepository('KsUserBundle:Equipment');
        $importService  = $this->getContainer()->get('ks_activity.importActivityService');
        $activity       = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth($userService->getUser());
        
        // FIXME: code en double
        $codeSport      = $this->formatNameSport($endoActivity['type']);
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
        
        $activity->setSource('Endomondo');
        // FIXME: on refait la moitié du travail 2x .... :'(
        if (isset($endoActivity['distance_km'])) {
            $activity->setDistance($endoActivity['distance_km']);
        }
        if (isset($endoActivity['duration_sec'])) {
            $activity->setDuration($this->secondesToTimeDuration($endoActivity['duration_sec']));
            $activity->setTimeMoving($endoActivity['duration_sec']);
        };
        if (isset($endoActivity['start_time'])) {
            $issuedAt = new \DateTime($endoActivity['start_time']);
            $issuedAt->setTimeZone(new \DateTimeZone("Europe/Paris"));
            $activity->setIssuedAt($issuedAt);
            $activity->setModifiedAt(new \DateTime());
        }
        if (isset($endoActivity['calories'])) {
            $activity->setCalories($endoActivity['calories']);
        }
        if (isset($endoActivity['altitude_m_max'])) {
            $activity->setElevationMax((int)$endoActivity['altitude_m_max']);
        }
        if (isset($endoActivity['altitude_m_min'])) {
            $activity->setElevationMax((int)$endoActivity['altitude_m_min']);
        }
        if (isset($endoActivity['speed_kmh_avg'])) {
            $activity->setSpeedAverage((float)$endoActivity['speed_kmh_avg']);
        }

        $trackingDatas = $importService->buildJsonToSave($endoActivity, 'endomondo'); 
        $firstWaypoint = $importService->getFirstWaypointNotEmpty($trackingDatas);
        if ($firstWaypoint != null) {
            $activity->setPlace(
                $importService->findPlace($firstWaypoint['lat'], $firstWaypoint['lon'])
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
        
        $activityRep->subscribeOnActivity($activity, $activity->getUser());

        //Gain de points 
        $leagueLevelService = $this->getContainer()->get('ks_league.leagueLevelService');
        $leagueLevelService->activitySessionEarnPoints($activity, $userService->getUser());

        //Pour chaques activités on a un identifiant relatif au service qu'on synchro
        $acfs = new \Ks\ActivityBundle\Entity\ActivityComeFromService();
        $acfs->setActivity($activity);
        $acfs->setService($userService->getService());
        $acfs->setIdWebsiteActivityService($endoActivity['id']);
        $acfs->setSourceDetailsActivity(json_encode($endoActivity));
        $acfs->setTypeSource('JSON');

        $em->persist($acfs);
        $em->flush();
    }
    
    /**
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
     * 
     * @param type $str
     * @param type $charset
     * @return type
     */
    public function wd_remove_accents($str, $charset='utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        return $str;
   }
   
   
    public function formatNameSport($sport)
    {
        return strtolower($this->wd_remove_accents($sport));
    }
    
}
