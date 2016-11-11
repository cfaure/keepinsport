<?php

namespace Ks\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class SyncRunraidCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('ks:user:syncRunraid')
            ->setDescription('Synchronises les données RUNRAID')
            ->setHelp(<<<EOT
La commande <info>ks:user:syncRunrai</info> permet de synchroniser les données de RUNRAID avec KS :

  <info>php app/console ks:user:syncRunraid</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em                         = $this->getContainer()->get('doctrine')->getEntityManager();
        $dbh                        = $em->getConnection();
        $articleTagRep              = $em->getRepository('KsActivityBundle:ArticleTag');
        $articleRep                 = $em->getRepository('KsActivityBundle:Article');
        $activityComeFromServiceRep = $em->getRepository('KsActivityBundle:ActivityComeFromService');
        $userRep                    = $em->getRepository('KsUserBundle:User');
        $runraidApi                 = $this->getContainer()->get('ks_user.runraid');
        
        $user = $userRep->find(1);
        
        // On commence par récupérer toutes les références déjà importées
        $query = 'select acfs.id_website_activity_service as uniqid'
            .' from ks_activity_come_from_service acfs'
            .' left join ks_activity a on (a.id = acfs.activity_id)'
            .' where acfs.service_id = :serviceId '
            .' and a.user_id = :userId';
        $res = $dbh->executeQuery($query, array(
            'serviceId' => 9,
            'userId'    => $user->getId()
        ));
        $activitiesAlreadyImported = array();
        foreach ($res as $val) {
            $activitiesAlreadyImported[$val['uniqid']] = true;
        }
        
        for ($i= 0; $i<= 1500; $i++) {
        //for ($i= 1220; $i<= 1220; $i++) {
            //$output->write("getCalendrierFromId(".$i.") :");
            
            if (array_key_exists($i, $activitiesAlreadyImported)) {
                //$output->writeln('  Deja importee !');
                
                /*FMO : ONE-SHOT / Mise à jour des données Distance / D+ / D-
                
                $activities = $activityComeFromServiceRep->findByWebsiteId($i);
                
                if (count($activities) == 1) {
                    $json = $runraidApi->getCalendrierFromId($i);
                    $article = $articleRep->find($activities[0]["activity_id"]);
                    $article->setDistance((float)str_replace("km", "", $json['distance']));
                    $article->setElevationGain((float)str_replace("m", "", $json['denivplus']));
                    $article->setElevationLost((float)str_replace("m", "", $json['denivmoins']));
                    
                    $em->persist($article);
                    $em->flush();
                    
                    $output->writeln($json['course'] . " : données mises à jour avec succès !");
                }
                */
                continue;
            }    
            $json = $runraidApi->getCalendrierFromId($i);
                
            if ($json['course'] != "") {
                $date2016 = new \DateTime($json['date2016']);
                $now = new \DateTime("2015-01-01");
                $interval = $now->diff($date2016);
                
                if ($json['date2016'] != "0000-00-00" && $interval->format('%R%a') > 0) {
                    $output->writeln($i." : ".$json['date2016']. " / " . htmlspecialchars_decode($json['course']));
                    
                    //Traitement uniquement si date > date du jour
                    //var_dump($date2016);
                    //var_dump($interval->format('%R%a'));exit;
                    
                    //Création d'un article de type compétition avec les données de RUNRAID
                    $article = new \Ks\ActivityBundle\Entity\Article($user);
                    $categoryTag = $articleTagRep->find(2);
                    $article->setCategoryTag($categoryTag);
                    $article->setLabel(htmlspecialchars_decode($json['course']));
                
                    $article->setIssuedAt($date2016);
                    $article->setType("article");
                    $article->setSport($em->getRepository('KsActivityBundle:Sport')->find(14));
                    $article->setDistance((float)str_replace("km", "", $json['distance']));
                    $article->setElevationGain((float)str_replace("m", "", $json['denivplus']));
                    $article->setElevationLost((float)str_replace("m", "", $json['denivmoins']));
                    
                    $description = "";
                    if ($json['commen'] != "") $description .= htmlspecialchars_decode($json['commen']);
                    if ($json['site'] != "") $description .= " <br><br> Site organisateur : <a href=\"". htmlspecialchars_decode($json['site']) . "\" target=_blank>" . htmlspecialchars_decode($json['site']) . "</a>";
                    if ($json['mail'] != "") $description .= " <br><br> " . htmlspecialchars_decode($json['mail']);
                    if ($json['vain2016'] != "") $description .= " <br><br> " . htmlspecialchars_decode($json['vain2016']);
                    if ($json['commen2016'] != "") $description .= " <br><br> " . htmlspecialchars_decode($json['commen2016']);

                    $em->persist($article);
                    $em->flush();
                    
                    $openrunner = htmlspecialchars_decode(html_entity_decode($json['openrunner']));
                    if ($openrunner != "") {
                        //Récupération du fichier GPX correspondant au ID openrunner éventuellement utilisé
                        //var_dump($openrunner);
                        $start = stripos($openrunner, "id=");
                        $end = stripos($openrunner, "w=");
                        //var_dump("start=".$start);
                        //var_dump("end=".$end);

                        $gpxId = (integer)substr($openrunner,$start+3, $end-$start-4);
                        //var_dump($gpxId);

                        if (is_integer($gpxId)) {
                            if ($gpxId != 0) { // =0 si mauvais lien openrunner (ex : trace de trail utilisé pour le tour des glaciers de la vanoise
                                $gpx = $runraidApi->getGPXFromOpenrunner($gpxId);
                                if ($gpx != "") { //Cas ou le fichier n'est plus dispo ou est privé sur OPENRUNNER et donc inaccessible
                                    $activityInfos = array("fileName" => $gpx);

                                    $importService = $this->getContainer()->get('ks_activity.importActivityService');
                                    list($activityDatas, $error) = $importService->buildJsonToSave($user, $activityInfos, 'gpx', false);

                                    //Ajout du trackingDatas à l'activité correspondante
                                    $article->setTrackingDatas($activityDatas);

                                    //Uniquement si les données issues de runraid ne sont pas présentes ou mal renseignées, on prend celles issues du fichier gpx
                                    if (is_null($article->getDistance()) || !is_float($article->getDistance()) || $article->getDistance() == 0) $article->setDistance($activityDatas['info']['distance']);
                                    if (is_null($article->getElevationGain()) || !is_float($article->getElevationGain()) || $article->getElevationGain() == 0) $article->setElevationGain($activityDatas['info']['D+']);
                                    if (is_null($article->getElevationLost()) || !is_float($article->getElevationLost()) || $article->getElevationLost() == 0) $article->setElevationLost($activityDatas['info']['D-']);

                                    $firstWaypoint = $importService->getFirstWaypointNotEmpty($activityDatas);
                                    if ($firstWaypoint != null) {
                                        $article->setPlace($importService->findPlace($firstWaypoint["lat"], $firstWaypoint["lon"]));
                                    }
                                    unset($firstWaypoint);
                                }
                            }
                            else {
                                //On ajoute le lien directement à la description pour permettre l'affichage de l'iframe (cas de trace de trail)
                                $description .= " <br> <center> " . $openrunner . " <center>";
                            }
                        }
                    }
                    //var_dump($description);exit;
                    $article->setDescription($description);
                    $em->persist($article);
                    $em->flush();
                    
                    $modification = array(
                        "title"         => base64_encode(htmlspecialchars_decode($json['course'])),
                        "description"   => base64_encode($description),
                        "elements"      => array(),
                        "photos"        => array(),
                        "tags"          => array($categoryId),
                        "trainingPlan"  => array()
                    );

                    //Tableau qui dira si les choses ont changés
                    $thingsWereChanged = array(
                        "title"         => false,
                        "description"   => true,
                        "elements"      => false,
                        "photos"        => false,
                        "tags"          => false,
                        "trainingPlan"  => false
                    );

                    //$breaks = array("<br />","<br>","<br/>");  
                    //$description = str_ireplace($breaks, "\r\n", htmlspecialchars_decode(html_entity_decode(utf8_decode($json['commen2016']), ENT_NOQUOTES, 'UTF-8')));
                    //echo html_entity_decode($description, ENT_NOQUOTES, 'UTF-8');exit;
                    //echo();exit;

                    //On enregistre les modifications sur le contenu
                    $articleRep->modificationOnArticle($article, $user, json_encode($modification), $thingsWereChanged);

                    $em->persist($article);
                    $em->flush();
                    
                    //Sauvegarde dans ks_activity_comes_from_service pour avoir l'historique des imports
                    $acfs = new \Ks\ActivityBundle\Entity\ActivityComeFromService();
                    $acfs->setActivity($em->getRepository('KsActivityBundle:Activity')->find($article->getId()));
                    $acfs->setService($em->getRepository('KsUserBundle:Service')->findOneByName('Runraid'));
                    $acfs->setIdWebsiteActivityService($i);
                    if ($gpxId !=0) {
                        $acfs->setSourceDetailsActivity($gpx);
                        $acfs->setTypeSource('GPX:'.$gpxId);
                    }
                    else {
                        $acfs->setSourceDetailsActivity($openrunner);
                        $acfs->setTypeSource('TDT');
                    }
                    

                    $em->persist($acfs);
                    $em->flush();

                    $eventName = htmlspecialchars_decode($json['course']);

                    $event = $articleRep->createWikisportEvent( $eventName );
                    $event->setIsAllDay("1");
                    $event->setIsPublic("1");
                    $event->setTypeEvent($em->getRepository('KsEventBundle:TypeEvent')->find(2));
                    $event->setStartDate($date2016);
                    $event->setEndDate($date2016);
                    $event->setDistance($article->getDistance());
                    $event->setElevationGain($article->getElevationGain());
                    $event->setElevationLost($article->getElevationLost());
                    $event->setSport($em->getRepository('KsActivityBundle:Sport')->find(14));
                    $event->setCompetition($article);
                    if (isset($firstWaypoint) && $firstWaypoint != null) {
                        $event->setPlace($importService->findPlace($firstWaypoint["lat"], $firstWaypoint["lon"]));
                    }
                    else {
                        //Si pas de fichier GPX on prend le champ region de RUNRAID pour préciser le pays FR ou RE
                        $place = new \Ks\EventBundle\Entity\Place();
                        if ($json['region'] == 'Ile de la Réunion') $place->setCountryCode("RE");
                        else if (($json['region'] == 'Etranger' || $json['region'] == 'Autres DOM-TOM') && isset($json['lieu']) && !is_null($json['lieu'])) $place->setFullAdress($json['lieu']);
                        else $place->setCountryCode("FR");
                        $event->setPlace($place);
                    }
                    
                    $article->setEvent($event);

                    $em->persist($article);
                    $em->persist($event);
                    $em->flush();
                    
                    $output->writeln("-- ".htmlspecialchars_decode($json['course']). " OK !" );
                }
                else $output->writeln($i." : Course deja terminee, " . $json['date2016']);
            }
            else $output->writeln($i." : Pas de donnees 'course'...");
        }
        
        $output->writeln("TOTAL : ". 1302-$i + 1 . " OK !" );
        
        exit;
    }   
}