<?php

namespace Ks\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

//Classe nike plus
//use Ks\ActivityBundle\Entity\NikePlusPHP;

/**
 * Nike controller.
 *
 * 
 */
class NikeController extends Controller
{
    /**
     * 
     * @Route("/configureNikePlusAccount", name = "ksActivity_configureNikePlusAccount", options={"expose"=true} )
     */
    public function configureNikePlusAccountAction()
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $serviceRep         = $em->getRepository('KsUserBundle:Service');
        $request            = $this->get('request');
        $parameters         = $request->request->all();
        
        $responseDatas = array();
        
       
        //On récupère les infos du compte nike plus
        $mailNike = isset( $parameters["mailNike"] ) ? $parameters["mailNike"] : "" ;
        $mdpMail = isset( $parameters["mdpMail"] ) ? $parameters["mdpMail"] : "" ;
        
        if ( $user ) {          
            $cookiesNikeDirRelative = $this->container->get('templating.helper.assets')->getUrl('nikeCookies');
            $cookiesNikeDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $cookiesNikeDirRelative ."/";
            
            //On crée le dossier qui contient les cookies de nikeplus s'il n'existe pas
            if (! is_dir( $cookiesNikeDirAbsolute ) ) mkdir($cookiesNikeDirAbsolute);
        
            $nikePlus = new \Ks\ActivityBundle\Entity\NikePlusPHP($mailNike, $mdpMail);
            //$nikePlus = new \Ks\ActivityBundle\Entity\NikePlusPHP($user->getId(), $cookiesNikeDirAbsolute);
            
            $serviceResponseHeader = $nikePlus->loginCookies->serviceResponse->header;
            
            if ( $serviceResponseHeader->success == "true" ) {
                $responseDatas["configureResponse"] = 1;
                
                $service = $serviceRep->findOneByName("NikePlus");
                if (!is_object($service) ) {
                        throw $this->createNotFoundException("Impossible de trouver le service 'NikePus' ");
                }
                
                //On regarde si le service existe déjà pour cet utilisateur
                $userHasService = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array("service"=>$service->getId(),"user"=>$user->getId()));
                if (!is_object($userHasService) ) {
                        $userHasService = new \Ks\UserBundle\Entity\UserHasServices();
                }
                
                $userHasService->setIsActive(true);
                $userHasService->setSyncServiceToUser(false);
                $userHasService->setUserSyncToService(false);
                $userHasService->setFirstSync(true);
                $userHasService->setUser($user);
                $userHasService->setService($service);
                $userHasService->setConnectionId($mailNike);
                $userHasService->setConnectionPassword(base64_encode($mdpMail));
                $userHasService->setCollectedActivities(json_encode(array()));
                $em->persist($userHasService);
                $em->flush();
                    
                //$runs = $nikePlus->activities(true);
                
                //var_dump($runs->activities);
                //var_dump($nikePlus->getAllActivities());
                
                //$run = $nikePlus->run("2006781937");
                //var_dump($run);
                //
                //
                //on coche l'action correspondante dans la checklist
                $em->getRepository('KsUserBundle:ChecklistAction')->checkParamService($user->getId());
                
            } else {
                $responseDatas["configureResponse"] = -1;
                $responseDatas["errorMessage"] = "";
                foreach( $serviceResponseHeader->errorCodes as $error ) {
                    $responseDatas["errorMessage"] .= $error->message."<br>";
                }
            } 
        } else {
            $responseDatas["configureResponse"] = -1;
            $responseDatas["errorMessage"] = "Impossible de récupérer l'utilisateur connecté.";
        }
        
        //var_dump($nikePlus);
        //$runs = $n->activities(true);
        //echo $runs->lifetimeTotals->distance;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * 
     * @Route("/syncNikePlusRuns", name = "ksActivity_syncNikePlusRuns", options={"expose"=true} )
     */
    public function syncNikePlusRunsAction()
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $sportTypeRep       = $em->getRepository('KsActivityBundle:SportType');
        $stateOfHealthRep   = $em->getRepository('KsActivityBundle:StateOfHealth');
        $sportRep           = $em->getRepository('KsActivityBundle:Sport');
        $serviceRep         = $em->getRepository('KsUserBundle:Service');
        $userHasServicesRep = $em->getRepository('KsUserBundle:UserHasServices');
        $user               = $this->get('security.context')->getToken()->getUser();
        
        //appels aux services
        $activityService                = $this->get('ks_activity.activityService');
        $importActivityService          = $this->get('ks_activity.importActivityService');
        
        $responseDatas = array();
        
        $service = $serviceRep->findOneByName("NikePlus");
        if (!is_object($service) ) {
                throw $this->createNotFoundException("Impossible de trouver le service 'NikePlus' ");
        }
        $idService = $service->getId();
        $userHasService = $userHasServicesRep->findOneBy(array(
            "service"   => $idService,
            "user"      => $user->getId()
        ));
        
        if (!is_object($userHasService) ) {
                throw $this->createNotFoundException("Impossible de trouver le service 'NikePlus' pour l'utilisateur ".$user->getId().".");
        }
        
        $mailNike = $userHasService->getConnectionId();
        $mdpMail = base64_decode($userHasService->getConnectionPassword());
        $collectedActivities = $userHasService->getCollectedActivities();
        $collectedActivities = empty( $collectedActivities ) ? array() : json_decode( $collectedActivities ) ;
        $oldCollectedActivitiesNumber = count( $collectedActivities );
        $newCollectedActivitiesNumber = 0;
        
        $nikePlus = new \Ks\ActivityBundle\Entity\NikePlusPHP($mailNike, $mdpMail);
        
        //$activities = $nikePlus->getLastActivities();
        $activities = $nikePlus->activities(0, false);
        //$activities = $nikePlus->allTime();
        //$responseDatas["activities"] = $activities;
        $activity = $nikePlus->run( 2038920089 );
$responseDatas["activity"] = $activity;
        $sport = $sportRep->findOneByLabel("Running");
        if (!is_object($sport) ) {
             throw $this->createNotFoundException("Impossible de trouver le sport Running");
        }
        
        $enduranceOnEarthType = $sportTypeRep->findOneByLabel("endurance");
                    
        if (!is_object($enduranceOnEarthType) ) {
            throw $this->createNotFoundException("Impossible de trouver le type de sport endurance");
        }
        
        foreach($activities as $activityId => $activityPiece ) {
            //$activity = $nikePlus->run( $activityId );
            //var_dump($activityId);
            /*$issuedAt = new \DateTime($activity->startTimeUtc);
            $activityId = $activity->activityId;
            //Si l'activité n'as pas déjà été récupérée
            if ( !in_array($activityId, $collectedActivities) ) {
                $collectedActivities[] = $activityId;

                $responseDatas["collectedActivities"][] = $activityId;
                $newCollectedActivitiesNumber += 1;

                $ActivitySessionEnduranceOnEarth = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth($user);
                $ActivitySessionEnduranceOnEarth->setSource("nikeplus");
                isset($activity->distance)? $ActivitySessionEnduranceOnEarth->setDistance($activity->distance) : "";
                isset($activity->duration)? $ActivitySessionEnduranceOnEarth->setDuration( $activityService->millisecondesToTimeDuration($activity->duration) ) : "";
                if( $issuedAt )  $ActivitySessionEnduranceOnEarth->setIssuedAt($issuedAt);
                if( $issuedAt )  $ActivitySessionEnduranceOnEarth->setModifiedAt($issuedAt);
                if( isset( $activity->calories ) ) $ActivitySessionEnduranceOnEarth->setCalories( $activity->calories );
                if( isset( $activity->tags->note ) ) $ActivitySessionEnduranceOnEarth->setDescription( $activity->tags->note );
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
                        //throw $this->createNotFoundException("Impossible de trouver l'état de forme ".$stateOfHealth);
                    }
                }
                
                $ActivitySessionEnduranceOnEarth->setModifiedAt(new \DateTime('Now'));
                $ActivitySessionEnduranceOnEarth->setSport($sport);
                
                

                if( isset( $activity->geo ) ) {   
                    
                    $ActivitySessionEnduranceOnEarth->setElevationMin($activity->geo->elevationMin);
                    $ActivitySessionEnduranceOnEarth->setElevationMax($activity->geo->elevationMax);
                    $ActivitySessionEnduranceOnEarth->setElevationLost($activity->geo->elevationLoss);
                    $ActivitySessionEnduranceOnEarth->setElevationGain($activity->geo->elevationGain);

                }
                
                $em->persist($ActivitySessionEnduranceOnEarth);
                $em->flush();
                
                //On lui fait gagner les points qu'il doit
                $leagueLevelService   = $this->get('ks_league.leagueLevelService');
                $leagueLevelService->activitySessionEarnPoints($ActivitySessionEnduranceOnEarth, $user);

                //Pour chaques activités on a un identifiant relatif au service qu'on synchro
                $ActivityComeFromService = new \Ks\ActivityBundle\Entity\ActivityComeFromService();
                $ActivityComeFromService->setActivity($ActivitySessionEnduranceOnEarth);
                $ActivityComeFromService->setService($service);
                $ActivityComeFromService->setIdWebsiteActivityService($activityId);
                
                //On transforme l'objet std en array
                $aNikeDatas     = json_decode( json_encode($activity), true );
                $trackingDatas  = $importActivityService->buildJsonToSave($aNikeDatas, "nikePlus");   
                $firstWaypoint  = $importActivityService->getFirstWaypointNotEmpty($trackingDatas);
                
                if( $firstWaypoint != null ) {
                    $ActivitySessionEnduranceOnEarth->setPlace(
                        $importActivityService->findPlace($firstWaypoint["lat"], $firstWaypoint["lon"])
                    );
                }
                                
                $ActivitySessionEnduranceOnEarth->setTrackingDatas($trackingDatas);
                $ActivityComeFromService->setSourceDetailsActivity(json_encode($aNikeDatas));
                $ActivityComeFromService->setTypeSource("JSON");
                
                $em->persist($ActivitySessionEnduranceOnEarth);
                $em->persist($ActivityComeFromService);
                $em->flush();
            }*/
        }
        
        $userHasService->setCollectedActivities( json_encode( $collectedActivities ) );
        $em->persist($userHasService);
        $em->flush();
        
        if( $newCollectedActivitiesNumber > 0 ) {
            $responseDatas["syncResponse"] = 1;
            //$syncActivitiesNumbert = $newCollectedActivitiesNumber - $oldCollectedActivitiesNumber;
            $responseDatas["successMessage"] = $newCollectedActivitiesNumber . " activités ont été récupérées.";
        } else {
            $responseDatas["syncResponse"] = -1;
            $responseDatas["errorMessage"] = "Aucune nouvelle activité n'a été récupérée.";
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }   
    
    /**
     * @Route("/createNikePlusJob", name="ksActivity_createNikePlusJob", options={"expose"=true})
     * @Template()
     */
    public function createNikePlusJobAction()
    {
        $user               = $this->get('security.context')->getToken()->getUser();
        $em                 = $this->getDoctrine()->getEntityManager();
        $serviceRep         = $em->getRepository('KsUserBundle:Service');
        $userHasServicesRep = $em->getRepository('KsUserBundle:UserHasServices');
        
        $responseDatas["syncResponse"] = 0;
        $responseDatas["errorMessage"] ="";
        
        //On enlève la limite d'execution de 30 secondes
        //set_time_limit(0);
 

        $service = $serviceRep->findOneByName("NikePlus");
        if (!is_object($service) ) {
                throw $this->createNotFoundException("Impossible de trouver le service 'NikePlus' ");
        }
        
        $serviceId  = $service->getId();
        $userId     = $user->getId();
        $userHasService = $userHasServicesRep->findOneBy(array(
            "service"   => $serviceId,
            "user"      => $userId
        ));
        
        if (!is_object($userHasService) ) {
                throw $this->createNotFoundException("Impossible de trouver le service 'NikePlus' pour l'utilisateur ".$user->getId().".");
        }
        
        $mailNike = $userHasService->getConnectionId();
        $mdpMail = base64_decode($userHasService->getConnectionPassword());
        
        if( $mailNike != null && $mdpMail != null ){
            if(file_exists($this->container->get('kernel')->getRootdir().'/jobs/pending/nikeplus/'.$userId."_".$serviceId.'.job')){
                $responseDatas["errorMessage"] = "Vous avez déjà effectué une demande de synchronisation de vos activités, le traitement est en cours veuillez patienter";
            }else{
                $file = fopen ($this->container->get('kernel')->getRootdir().'/jobs/pending/nikeplus/'.$userId."_".$serviceId.'.job', "a+");
                fwrite($file, "php ".$this->container->get('kernel')->getRootdir()."/console ks:activity:nikeplussync $userId $serviceId");
                fclose($file);
                
                $responseDatas["syncResponse"] = 1;
                $responseDatas["successMessage"] = "Mise en attente de la synchronisation des activités effectuée avec succès";
                
                //Mise a jour de userHasAService
                $userHasService->setStatus("pending");
                $em->persist($userHasService);
                $em->flush();
            }
        }else{
            $responseDatas["errorMessage"] = "Impossible de récupérer les informations de connexion";
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
