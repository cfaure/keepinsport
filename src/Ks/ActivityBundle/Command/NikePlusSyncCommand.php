<?php

namespace Ks\ActivityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NikePlusSyncCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        
        $this
            ->setName('ks:activity:nikeplussync')
            ->setDescription('sync nike plus')
            ->setDefinition(array(
                new InputArgument('userId', InputArgument::REQUIRED, 'User Id'),
                new InputArgument('serviceId', InputArgument::REQUIRED, 'Service Id'),
            ))
            ->setHelp(<<<EOT
La commande <info>ks:activity:nikeplussync</info> permet de synchroniser les activités d'un utilisateur :

  <info>php app/console ks:activity:ks:activity:nikeplussync</info>
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userId                     = $input->getArgument('userId');
        $serviceId                  = $input->getArgument('serviceId');
        $em                         = $this->getContainer()->get('doctrine')->getEntityManager();
        $userHasServicesRep         = $em->getRepository('KsUserBundle:UserHasServices');
        $activityComeFromServiceRep = $em->getRepository('KsActivityBundle:ActivityComeFromService');
        $activityRep                = $em->getRepository('KsActivityBundle:Activity');
        $sportTypeRep               = $em->getRepository('KsActivityBundle:SportType');
        $sportRep                   = $em->getRepository('KsActivityBundle:Sport');
        $stateOfHealthRep           = $em->getRepository('KsActivityBundle:StateOfHealth');
        $preferenceRep              = $em->getRepository('KsUserBundle:Preference');
        
        //Appel des services
        $activityService            = $this->getContainer()->get('ks_activity.activityService');
        $importActivityService      = $this->getContainer()->get('ks_activity.importActivityService');
        $leagueLevelService         = $this->getContainer()->get('ks_league.leagueLevelService');
        
        $userHasService = $userHasServicesRep->findOneBy(array(
            "service"   => $serviceId,
            "user"      => $userId
        ));
        
        $now = new \DateTime('Now');
        $now = $now->format('Y-m-d-h-i-s');
        
        $fileLog = fopen ($this->getContainer()->get('kernel')->getRootdir().'/jobs/logs/nikeplus/'.$userId."_".$serviceId.'-'.$now.'.log', "a+");
        
        if ( !is_object( $userHasService ) ) {
            $output->writeln( "Impossible de trouver le service " . $serviceId . " appartenant à l'utilisateur ".$userId );
        }
        
        if( file_exists( $this->getContainer()->get('kernel')->getRootdir().'/jobs/pending/nikeplus/'.$userId."_".$serviceId.'.job' ) ){
              rename( $this->getContainer()->get('kernel')->getRootdir().'/jobs/pending/nikeplus/'.$userId."_".$serviceId.'.job', $this->getContainer()->get('kernel')->getRootdir().'/jobs/inprogress/nikeplus/'.$userId."_".$serviceId.'.job' );
        } else {
            $output->writeln("Le fichier : " . $this->getContainer()->get('kernel')->getRootdir().'/jobs/pending/nikeplus/'.$userId."_".$serviceId.".job n existe pas" );
            fputs($fileLog, "Le fichier : " . $this->getContainer()->get('kernel')->getRootdir().'/jobs/pending/nikeplus/'.$userId."_".$serviceId.".job n existe pas");
        }
        
        $user                   = $userHasService->getUser();
        $service                = $userHasService->getService();
        $mailNike               = $userHasService->getConnectionId();
        $mdpMail                = base64_decode( $userHasService->getConnectionPassword() );
        $collectedActivities    = $userHasService->getCollectedActivities();
        $collectedActivities    = empty( $collectedActivities ) ? array() : json_decode( $collectedActivities ) ;
        $newCollectedActivitiesNumber = 0;
        
        
        
        
        
        try {
            
            $userHasService->setStatus('pending');
            $em->persist($userHasService);
            $em->flush();
            
            $nikePlus = new \Ks\ActivityBundle\Entity\NikePlusPHP( $mailNike, $mdpMail );
            //var_dump($nikePlus->loginCookies->serviceResponse);
            //On récupère une liste des activités
            $nikeplusSearchNumber = $preferenceRep->findOneBy( array("code" => "nikeplusSearchNumber"))->getVal1();
            $output->writeln("Recuperation des $nikeplusSearchNumber dernières activites.");
            $activities = $nikePlus->activities($nikeplusSearchNumber, false);
            //$activities = array();
            //var_dump($activities);
            //var_dump( $nikePlus->activity("21329596000") );
            //var_dump($nikePlus->allTime());
            //var_dump($nikePlus->mostRecentActivity());
            
            $sport = $sportRep->findOneByCodeSport("running");
            if (!is_object($sport) ) {
                 $output->writeln("Impossible de trouver le sport Running");
            }

            $enduranceOnEarthType = $sportTypeRep->findOneByLabel("endurance");

            if (!is_object($enduranceOnEarthType) ) {
                $output->writeln("Impossible de trouver le type de sport endurance");
            }
            
            if( is_array( $activities ) ) {
                $output->writeln("Nombre d activites effectuees par l utilisateur depuis sa creation : ". count( array_keys( $activities ) )."");

                foreach( array_keys( $activities ) as $activityId ) {

                    $output->writeln("");

                    //Si l'activité n'as pas déjà été récupérée
                    if ( !in_array($activityId, $collectedActivities) ) {

                        //L'activité est déjà importé mais n'est pas renseigné au niveau du service
                        $importedActivity = $activityComeFromServiceRep->findOneBy( array("service" => $service->getId(), "id_website_activity_service" => $activityId) );
                        if( !is_object( $importedActivity ) ) {

                            $output->writeln("Recuperation des infos sur l'activite ".$activityId."...");
                            $result = $nikePlus->activity( $activityId );

                            if( $result != null ) {
                                $activity = $result->activity;
                                $issuedAt = new \DateTime( $activity->startTimeUtc );

                                $collectedActivities[] = $activityId;

                                $newCollectedActivitiesNumber += 1;

                                $ActivitySessionEnduranceOnEarth = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth($user);
                                $ActivitySessionEnduranceOnEarth->setSource("nikeplus");
                                isset($activity->distance)? $ActivitySessionEnduranceOnEarth->setDistance($activity->distance) : "";
                                isset($activity->duration)? $ActivitySessionEnduranceOnEarth->setDuration( $activityService->millisecondesToTimeDuration($activity->duration) ) : "";
                                if( $issuedAt )  $ActivitySessionEnduranceOnEarth->setIssuedAt($issuedAt);
                                if( $issuedAt )  $ActivitySessionEnduranceOnEarth->setModifiedAt($issuedAt);
                                if( isset( $activity->calories ) ) $ActivitySessionEnduranceOnEarth->setCalories( $activity->calories );
                                if( isset( $activity->tags->note ) ) $ActivitySessionEnduranceOnEarth->setDescription( ( $activity->tags->note ) );
                                //$activity->tags->weather;
                                //$activity->tags->temperature;
                                //$activity->tags->terrain;
                                //$activity->tags->emotion;
                                //unstoppable > great > so_so > tired > injured
                                $stateOfHealthCode = "";
                                if( isset( $activity->tags->emotion ) ) {
                                    switch( $activity->tags->emotion ) {
                                        case "unstoppable":
                                        case "great":
                                            $stateOfHealthCode = "great";
                                            break;

                                        case "so_so":
                                            $stateOfHealthCode = "so_so";
                                            break;

                                        case "tired":
                                        case "injured":
                                            $stateOfHealthCode = "tired";
                                            break;
                                    }
                                }

                                if( !empty( $stateOfHealthCode ) ) {
                                    $stateOfHealth = $stateOfHealthRep->findOneByCode( $stateOfHealthCode );

                                    if ( is_object($stateOfHealth) ) {
                                        $ActivitySessionEnduranceOnEarth->setStateOfHealth( $stateOfHealth );
                                    } else {
                                        $output->writeln("Impossible de trouver l'état de forme ".$stateOfHealth);
                                    }
                                }

                                $ActivitySessionEnduranceOnEarth->setSport($sport);

                                if( isset( $activity->geo ) ) {   

                                    $ActivitySessionEnduranceOnEarth->setElevationMin($activity->geo->elevationMin);
                                    $ActivitySessionEnduranceOnEarth->setElevationMax($activity->geo->elevationMax);
                                    $ActivitySessionEnduranceOnEarth->setElevationLost($activity->geo->elevationLoss);
                                    $ActivitySessionEnduranceOnEarth->setElevationGain($activity->geo->elevationGain);

                                }

                                $em->persist($ActivitySessionEnduranceOnEarth);
                                $em->flush();

                                

                                //Pour chaques activités on a un identifiant relatif au service qu'on synchro
                                $ActivityComeFromService = new \Ks\ActivityBundle\Entity\ActivityComeFromService();
                                $ActivityComeFromService->setActivity($ActivitySessionEnduranceOnEarth);
                                $ActivityComeFromService->setService($service);
                                $ActivityComeFromService->setIdWebsiteActivityService($activityId);

                                //On transforme l'objet std en array
                                $aNikeDatas     = json_decode( json_encode($activity), true );
                                $trackingDatas  = $importActivityService->buildJsonToSave($user, $aNikeDatas, "nikePlus");   
                                $firstWaypoint  = $importActivityService->getFirstWaypointNotEmpty($trackingDatas);

                                if( $firstWaypoint != null ) {
                                    $ActivitySessionEnduranceOnEarth->setPlace(
                                        $importActivityService->findPlace($firstWaypoint["lat"], $firstWaypoint["lon"])
                                    );
                                }

                                if( $trackingDatas != null && is_array($trackingDatas) ) {
                                    $ActivitySessionEnduranceOnEarth->setDistance($trackingDatas['info']['distance']);
                                    $ActivitySessionEnduranceOnEarth->setDuration($trackingDatas['info']['timeDuration']);
                                    $ActivitySessionEnduranceOnEarth->setTimeMoving($trackingDatas['info']['duration']);
                                    $ActivitySessionEnduranceOnEarth->setTrackingDatas($trackingDatas);
                                }
                                
                                $ActivityComeFromService->setSourceDetailsActivity(json_encode($aNikeDatas));
                                $ActivityComeFromService->setTypeSource("JSON");

                                $ActivitySessionEnduranceOnEarth->setSynchronizedAt(new \DateTime());
                                $em->persist( $ActivitySessionEnduranceOnEarth );
                                $em->persist( $ActivityComeFromService );
                                $em->flush();
                                
                                //On lui fait gagner les points qu'il doit
                                $leagueLevelService->activitySessionEarnPoints($ActivitySessionEnduranceOnEarth, $user);
                                
                                $activityRep->subscribeOnActivity($ActivitySessionEnduranceOnEarth, $user);

                                $output->writeln("Import de l activite ". $activityId . " effectue avec success. | " . $newCollectedActivitiesNumber);
                            } else {
                                $output->writeln("Impossible de récupérer les infos de l'activité ". $activityId . ".");
                            }
                        } else {
                            //L'activité est déjà importé mais n'est pas renseigné au niveau du service
                            $output->writeln("L activite ". $activityId ." existe deja. Traitement de l activite suivante.");
                            $collectedActivities[] = $activityId;
                        }
                    } else{
                        $output->writeln("L activite ". $activityId ." existe deja. Ttraitement de l activite suivante.");
                    }
                }
            }
            
            if( $newCollectedActivitiesNumber > 0 ) {
                
                $userHasService->setCollectedActivities( json_encode( $collectedActivities ) );
                $em->persist( $userHasService );
                $em->flush();
                $output->writeln( $newCollectedActivitiesNumber . " nouvelles activites NikePlus ont ete recuperees.");
                fputs($fileLog, $newCollectedActivitiesNumber . " nouvelles activites NikePlus ont ete recuperees \n");
                
                //Mise à jour des étoiles
                $leagueCategoryId = $user->getLeagueLevel()->getCategory()->getId();
                if( is_integer( $leagueCategoryId ) ) $leagueLevelService->leagueRankingUpdate( $leagueCategoryId );
            } else {
                $output->writeln("Pas de nouvelles activites a importer.");
                fputs($fileLog, "Pas de nouvelles activites a importer. \n");
            }
            
        
            $userHasService->setLastSyncAt(new \DateTime('now'));
            $userHasService->setStatus('done');
            $em->persist($userHasService);
            $em->flush();

            //on déplace le fichier qui a servit a faire l'import asynchrone 
           if(file_exists($this->getContainer()->get('kernel')->getRootdir().'/jobs/inprogress/nikeplus/'.$userId."_".$serviceId.'.job')){
                rename($this->getContainer()->get('kernel')->getRootdir().'/jobs/inprogress/nikeplus/'.$userId."_".$serviceId.'.job', $this->getContainer()->get('kernel')->getRootdir().'/jobs/done/nikeplus/'.$userId."_".$serviceId.'.job');
            }
        } catch (\Exception $e) {
            throw $e;
            
            if( $newCollectedActivitiesNumber > 0 ) {
                $userHasService->setCollectedActivities( json_encode( $collectedActivities ) );
            } 
            
            $userHasService->setStatus('done');
            $em->persist( $userHasService );
            $em->flush();
            
            //on déplace le fichier qui a servit a faire l'import asynchrone 
            if(file_exists($this->getContainer()->get('kernel')->getRootdir().'/jobs/inprogress/nikeplus/'.$userId."_".$serviceId.'.job')){
                rename($this->getContainer()->get('kernel')->getRootdir().'/jobs/inprogress/nikeplus/'.$userId."_".$serviceId.'.job', $this->getContainer()->get('kernel')->getRootdir().'/jobs/done/nikeplus/'.$userId."_".$serviceId.'.job');
            }
        }
    }
}
