<?php
namespace Ks\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Description of ServiceController
 *
 * @author Clem
 */
class ServiceController extends Controller
{
    /**
     * @Route("/runkeeper", name="service_runkeeper")
     * @Template
     */
    public function runkeeperAction()
    {   
        // get runkeeper's access token from authenticated user
        //$this->get('ks_job.background_process')->queueJob('ks:user:runkeepersync 103f7bdd2c7c405aa352be1e5b4aff4d');
        $this->get('ks_job.background_process')->queueJob(
            'ks_user.runkeeper',
            'synchronizeActivitiesWihtUser',
            array('access_token' => '103f7bdd2c7c405aa352be1e5b4aff4d') //FIXME: la chaine retournée par serialize de ce tableau, ne doit pas dépasser 255 caractères !
        );
                
        return array();
    }
    
    /**
     * @Route("/suunto", name="service_suunto")
     * @Template
     */
    public function suuntoAction()
    {   
        // get suunto's access token from authenticated user
        $this->get('ks_job.background_process')->queueJob(
            'ks_user.suunto',
            'synchronizeActivitiesWihtUser',
            array('access_token' => 'qmCP849josEevAPdko09124dghJLOpo6srRPIl7MrahSZ4z7HzguGgzyszI1uxF9') //FIXME: la chaine retournée par serialize de ce tableau, ne doit pas dépasser 255 caractères !
        );
                
        return array();
    }
    
    
    /**
     * @Route("/RunkeepercreateJob", name="ksyncRunkeeper_createJob", options={"expose"=true})
     * @Template()
     */
    public function RunkeepercreateJobAction()
    {
        $user   = $this->get('security.context')->getToken()->getUser();
        $em     = $this->getDoctrine()->getEntityManager();
        $responseDatas["syncResponse"] = 0;
        $responseDatas["errorMessage"] ="";
        
        //On enlève la limite d'execution de 30 secondes
        //set_time_limit(0);
 

        //Récupération du jeton de l'utilisateur courant 
        if ($user != null){
            $service = $em->getRepository('KsUserBundle:Service')->findOneByName("Runkeeper");
            $idService = $service->getId();
            $userHasService = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array("service"=>$idService,"user"=>$user->getId()));
            //$userHasService = $em->getRepository('KsUserBundle:UserHasServices')->findOneByService($idService);
            $accessToken = $userHasService->getToken();
            $isActive    = $userHasService->getIsActive();
            
            if ($isActive) {
                if(!empty($accessToken)){
                    if(file_exists($this->container->get('kernel')->getRootdir().'/jobs/pending/runkeeper/'.$accessToken.'.job')){
                        $responseDatas["errorMessage"] = "Vous avez déjà effectué une demande de synchronisation de vos activités, le traitement est en cours veuillez patienter";
                    }else{
                        $file = fopen ($this->container->get('kernel')->getRootdir().'/jobs/pending/runkeeper/'.$accessToken.'.job', "a+");
                        fwrite($file, "php ".$this->container->get('kernel')->getRootdir()."/console ks:user:runkeepersync $accessToken");
                        fclose($file);

                        $responseDatas["syncResponse"] = 1;
                        $responseDatas["successMessage"]    = "Synchronisation des activités en cours, veuillez patienter svp";

                        //Mise a jour de userHasAService
                        $userHasService->setStatus("pending");
                        $em->persist($userHasService);
                        $em->flush();
                    }


                }else{
                   $responseDatas["syncResponse"] = -1;
                   $responseDatas["errorMessage"] = "Impossible de récupérer le compte Runkeeper !";
                }
            }
            else {
                $responseDatas["syncResponse"] = 0;
                $responseDatas["errorMessage"] = "Service non actif !";
            }
            
        }else{
            $responseDatas["errorMessage"] = "Impossible de récupérer le compte Runkeeper !";
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/SuuntocreateJob", name="ksyncSuunto_createJob", options={"expose"=true})
     * @Template()
     */
    public function SuuntocreateJobAction()
    {
        $user   = $this->get('security.context')->getToken()->getUser();
        $em     = $this->getDoctrine()->getEntityManager();
        $responseDatas["syncResponse"] = 0;
        $responseDatas["errorMessage"] ="";
        
        //On enlève la limite d'execution de 30 secondes
        //set_time_limit(0);
 

        //Récupération du jeton de l'utilisateur courant 
        if ($user != null){
            $service = $em->getRepository('KsUserBundle:Service')->findOneByName("Suunto");
            $idService = $service->getId();
            $userHasService = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array("service"=>$idService,"user"=>$user->getId()));
            //$userHasService = $em->getRepository('KsUserBundle:UserHasServices')->findOneByService($idService);
            $accessToken = $userHasService->getToken();
            $isActive    = $userHasService->getIsActive();
            
            if ($isActive) {
                if(!empty($accessToken)){
                    if(file_exists($this->container->get('kernel')->getRootdir().'/jobs/pending/suunto/'.$accessToken.'.job')){
                        $responseDatas["errorMessage"] = "Vous avez déjà effectué une demande de synchronisation de vos activités, le traitement est en cours veuillez patienter";
                    }else{
                        $file = fopen ($this->container->get('kernel')->getRootdir().'/jobs/pending/suunto/'.$accessToken.'.job', "a+");
                        fwrite($file, "php ".$this->container->get('kernel')->getRootdir()."/console ks:user:suuntosync $accessToken");
                        fclose($file);

                        $responseDatas["syncResponse"] = 1;
                        $responseDatas["successMessage"]    = "Synchronisation des activités en cours, veuillez patienter svp";

                        //Mise a jour de userHasAService
                        $userHasService->setStatus("pending");
                        $em->persist($userHasService);
                        $em->flush();
                    }


                }else{
                   $responseDatas["syncResponse"] = -1;
                   $responseDatas["errorMessage"] = "Impossible de récupérer le compte Suunto !";
                }
            }
            else {
                $responseDatas["syncResponse"] = 0;
                $responseDatas["errorMessage"] = "Service non actif !";
            }
            
        }else{
            $responseDatas["errorMessage"] = "Impossible de récupérer le compte Suunto !";
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/EndomondocreateJob", name="ksyncEndomondo_createJob", options={"expose"=true})
     * @Template()
     */
    public function EndomondocreateJobAction()
    {
        $user       = $this->get('security.context')->getToken()->getUser();
        $em         = $this->getDoctrine()->getEntityManager();
        $jobPath    = $this->container->get('kernel')->getRootdir().'/jobs/';
        $responseDatas["syncResponse"] = 0;
        $responseDatas["errorMessage"] = '';
        
        //Récupération du jeton de l'utilisateur courant 
        if ($user != null){
            $service        = $em->getRepository('KsUserBundle:Service')->findOneByName('Endomondo');
            $idService      = $service->getId();
            $userHasService = $em
                ->getRepository('KsUserBundle:UserHasServices')
                ->findOneBy(
                    array('service' => $idService, 'user' => $user->getId())
                );
            $accessToken    = $userHasService->getToken();
            $isActive       = $userHasService->getIsActive();
            
            // FIXME: factoriser le code !!!!
            if ($isActive) {
                if(!empty($accessToken)){
                    if (file_exists($jobPath.'pending/endomondo/'.$accessToken.'.job')){
                        $responseDatas["errorMessage"] = "Vous avez déjà effectué une demande de synchro, le traitement est en cours veuillez patienter svp";
                    } else {
                        $file = fopen($jobPath.'pending/endomondo/'.$accessToken.'.job', "a+");
                        fwrite($file, "php ".$this->container->get('kernel')->getRootdir()."/console ks:user:endomondosync $accessToken");
                        fclose($file);

                        $responseDatas["syncResponse"]      = 1;
                        $responseDatas["successMessage"]    = "Synchronisation des activités en cours, veuillez patienter svp";

                        //Mise a jour de userHasAService
                        $userHasService->setStatus("pending");
                        $em->persist($userHasService);
                        $em->flush();
                    }               
                } else {
                   $responseDatas["syncResponse"] = -1;
                   $responseDatas["errorMessage"] = "Impossible de récupérer le compte Endomondo !";
                }
            }
            else {
                $responseDatas["syncResponse"] = 0;
                $responseDatas["errorMessage"] = "Service non actif !";
            }
        } else {
            $responseDatas["errorMessage"] = "Impossible de récupérer le compte Endomondo !";
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/ksRevokeServiceToken", name="ksRevokeServiceToken", options={"expose"=true} )
     */
    public function ksRevokeServiceToken()
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $serviceRep         = $em->getRepository('KsUserBundle:Service');
        $user       = $this->get('security.context')->getToken()->getUser();
        $request    = $this->get('request');
        $parameters = $request->request->all();
        
        $responseDatas                  = array();
        $responseDatas['removeToken']   = 0;
        $responseDatas['errorMessage']  = '';

        $service = $serviceRep->find($parameters['idService']);
        
        if( is_object( $service )) {
            $userHasService = $em
                ->getRepository('KsUserBundle:UserHasServices')
                ->findOneBy(array(
                    'service'   => $service->getId(),
                    'user'      => $user->getId())
                );

            if (!is_object($userHasService)) {
                /*$responseDatas["errorMessage"] = "Impossible de trouver le service "
                    .$idService." de l'utilisateur ".$user->getId()." ";*/
                $userHasService = new \Ks\UserBundle\Entity\UserHasServices();
                $userHasService->setService($service);
                $userHasService->setUser($user);
            } 

            $userHasService->setToken('');
            $userHasService->setIsActive(0);
            $em->persist($userHasService);
            $em->flush();

            $responseDatas['removeToken'] = 1;
        } else {
            $responseDatas['removeToken'] = -1;
        }

        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     *             
     * FIXME: on devrait faire un service "abstract" de type "OAuth"
     *        pour mettre en commun ces différentes fonctions.
     * 
     * @Route("/ksSetServiceStrava", name = "ksSetServiceStrava" )
     */
    public function ksSetServiceStravaAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $user       = $this->get('security.context')->getToken()->getUser();
        $request    = $this->get('request');
        $parameters = $request->query->all();
        
        if (!empty($parameters["code"])) {
            
            $stravaApi  = $this->get('ks_user.strava');
            $request    = $this->getRequest();
            
            if ($request->query->has('error')) {
                // TODO: gérer le message flash ou faire une page de rendu
                $this->get('session')->setFlash('alert alert-error', 'users.service_error_request_query_error');
                return $this->redirect($this->generateUrl('ks_set_services'));
            }
            $authCode       = $parameters["code"];
            $redirectUri    = $this->generateUrl('ksSetServiceStrava', array(), true);
            try {
                $accessToken = $stravaApi->getAccessToken($authCode, $redirectUri);
            } catch (Exception $e) {
                // TODO: faire quelque chose de plus intelligent
                throw $e;
            }
            
            //Si c'est la première fois (pas de jeton)
            $service        = $em->getRepository('KsUserBundle:Service')->findOneByName("Strava");
            $idService      = $service->getId();
            $userHasService = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array("service" => $idService, "user" => $user->getId()));
            $accessTokenBDD = $userHasService->getToken();
            $firstsync      = false;
            if ($accessTokenBDD == null) {
                //Vérification de l'existence du Token
                $uas = $em->getRepository('KsUserBundle:UserHasServices')->findOneByToken($accessToken);
                if (!isset($uas)) {
                    $userHasService->setToken($accessToken);
                    $userHasService->setIsActive(true);
                } else {
                    $this->get('session')->setFlash('alert alert-error', 'users.token_already_used');
                    return $this->redirect($this->generateUrl('ks_set_services')); 
                }
            }
            
            /*ancienne méthode avec fichier job
            if (file_exists($this->container->get('kernel')->getRootdir().'/jobs/pending/strava/'.$accessToken.'.job')) {
                $this->get('session')->setFlash('alert alert-error', 'users.service_sync_already_inprogress');
            } else {
                $file = fopen ($this->container->get('kernel')->getRootdir().'/jobs/pending/strava/'.$accessToken.'.job', "a+");
                fwrite($file, "php ".$this->container->get('kernel')->getRootdir()."/console ks:user:stravasync $accessToken");
                fclose($file);
                $this->get('session')->setFlash('alert alert-success', 'users.sync-strava');
                $userHasService->setStatus("pending");
                
                //on coche l'action correspondante dans la checklist
                $em->getRepository('KsUserBundle:ChecklistAction')->checkParamService($user->getId());
            }*/
            
            $em->persist($userHasService);
            $em->flush();
            
            //$importActivityService->getActivitiesToSyncFromRUNKEEPER($user);
        }
        
        return $this->redirect($this->generateUrl('ks_set_services'));
    }
        
    /**
     * 
     * @Route("/ksSetServiceRunkeeper", name = "ksSetServiceRunkeeper" )
     */
    public function ksSetServiceRunkeeperAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $user       = $this->get('security.context')->getToken()->getUser();
        $request    = $this->get('request');
        $parameters = $request->query->all();
        $importActivityService  = $this->container->get('ks_activity.importActivityService');
        
        if(!empty($parameters["code"])){
            
            $rkApi              = $this->get('ks_user.runkeeper');
            $request            = $this->getRequest();
            if ($request->query->has('error')) {
                // TODO: gérer le message flash ou faire une page de rendu
                $this->get('session')->setFlash('alert alert-error', 'users.service_error_request_query_error');
                return $this->redirect($this->generateUrl('ks_set_services'));
            }
            $authCode           = $parameters["code"];
            $redirectUri        = $this->generateUrl('ksSetServiceRunkeeper', array(), true);
            try {
                $accessToken    = $rkApi->getAccessToken($authCode, $redirectUri);
            } catch (Exception $e) {
                // TODO: faire quelque chose de plus intelligent
                throw $e;
            }
            //Si c'est la première fois (pas de jeton)
            $service        = $em->getRepository('KsUserBundle:Service')->findOneByName("Runkeeper");
            $idService      = $service->getId();
            $userHasService = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array("service"=>$idService,"user"=>$user->getId()));
            $accessTokenBDD = $userHasService->getToken();
            $firstsync      = false;
            if ($accessTokenBDD == null) {
                //Vérification de l'existence du Token
                $uas = $em->getRepository('KsUserBundle:UserHasServices')->findOneByToken($accessToken);
                if (!isset($uas)) {
                    $userHasService->setToken($accessToken);
                    $userHasService->setIsActive(true);
                } else {
                    $this->get('session')->setFlash('alert alert-error', 'users.token_already_used');
                    return $this->redirect($this->generateUrl('ks_set_services')); 
                }
               
            }
            
            /*ancienne méthode avec fichier job
            if (file_exists($this->container->get('kernel')->getRootdir().'/jobs/pending/runkeeper/'.$accessToken.'.job')) {
                $this->get('session')->setFlash('alert alert-error', 'users.service_sync_already_inprogress');
                //$responseDatas["errorMessage"] = "Vous avez déjà effectué une demande de synchronisation de vos activités, le traitement est en cours veuillez patienter";
            } else {
                $file = fopen ($this->container->get('kernel')->getRootdir().'/jobs/pending/runkeeper/'.$accessToken.'.job', "a+");
                fwrite($file, "php ".$this->container->get('kernel')->getRootdir()."/console ks:user:runkeepersync $accessToken");
                fclose($file);
                $this->get('session')->setFlash('alert alert-success', 'users.sync-rk-OK');
                $userHasService->setStatus("pending");
                
                //on coche l'action correspondante dans la checklist
                $em->getRepository('KsUserBundle:ChecklistAction')->checkParamService($user->getId());
            }*/
            
            $em->persist($userHasService);
            $em->flush();
            
            $importActivityService->getActivitiesToSyncFromRUNKEEPER($user);
            
        }
            
        return $this->redirect($this->generateUrl('ks_set_services')); 
    }
    
    /**
     * 
     * @Route("/ksSetServiceSuunto", name = "ksSetServiceSuunto" )
     */
    public function ksSetServiceSuuntoAction()
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $importActivityService  = $this->container->get('ks_activity.importActivityService');
        $user                   = $this->get('security.context')->getToken()->getUser();
        $request                = $this->get('request');
        $parameters             = $request->query->all();
        
        $suuntoApi              = $this->get('ks_user.suunto');
        $request                = $this->getRequest();
        if ($request->query->has('error')) {
            // TODO: gérer le message flash ou faire une page de rendu
            $this->get('session')->setFlash('alert alert-error', 'users.service_error_request_query_error');
            return $this->redirect($this->generateUrl('ks_set_services'));
        }
        
        //Si c'est la première fois (pas de jeton)
        $service = $em->getRepository('KsUserBundle:Service')->findOneByName("Suunto");
        $idService = $service->getId();
        $userHasService = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array("service"=>$idService,"user"=>$user->getId()));
        
        if (!is_object($userHasService)) {
            $userHasService = new \Ks\UserBundle\Entity\UserHasServices();
            $userHasService->setIsActive(false);
            $userHasService->setSyncServiceToUser(false);
            $userHasService->setUserSyncToService(false);
            $userHasService->setFirstSync(true);
            $userHasService->setUser($user);
            $userHasService->setService($service);
            $userHasService->setConnectionId($user->getEmail());
            $em->persist($userHasService);
            $em->flush();
        }
        
        $accessTokenBDD = $userHasService->getToken();
        $firstsync = false;
        if($accessTokenBDD == null){
            try {
                $accessToken    = $user->getId() == '7' ? 'WM5CNnlPaHzEAhmmZag7diLHlpfkwmZgev1MKDQiIh0lZx8UIDs5NWzPC6eaMXPQ': $suuntoApi->getAccessToken();
            } catch (Exception $e) {
                // TODO: faire quelque chose de plus intelligent
                throw $e;
            }
            
            //Vérification de l'existence du Token
            $uas = $em->getRepository('KsUserBundle:UserHasServices')->findOneByToken($accessToken);
            if(!isset($uas)){
                $userHasService->setToken($accessToken);
                $userHasService->setIsActive(false); //FMO : l'utilisateur doit d'abord aller valider l'appli KS sur MOVESCOUNT
            }else{
                $this->get('session')->setFlash('alert alert-error', 'users.token_already_used');
                return $this->redirect($this->generateUrl('ks_set_services')); 
            }

        }

        $em->persist($userHasService);
        $em->flush();
        
        /*ancienne méthode avec fichier job
        if(file_exists($this->container->get('kernel')->getRootdir().'/jobs/pending/suunto/'.$accessToken.'.job')){
            $this->get('session')->setFlash('alert alert-error', 'users.service_sync_already_inprogress');
            //$responseDatas["errorMessage"] = "Vous avez déjà effectué une demande de synchronisation de vos activités, le traitement est en cours veuillez patienter";
        }else{
            $file = fopen ($this->container->get('kernel')->getRootdir().'/jobs/pending/suunto/'.$accessToken.'.job', "a+");
            fwrite($file, "php ".$this->container->get('kernel')->getRootdir()."/console ks:user:suuntosync $accessToken");
            fclose($file);
            $this->get('session')->setFlash('alert alert-success', 'users.sync-suunto-OK');
            $userHasService->setStatus("to validate on MOVESCOUNT"); //FMO : l'utilisateur doit d'abord aller valider l'appli KS sur MOVESCOUNT

            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkParamService($user->getId());
        }*/
        
        $importActivityService->getActivitiesToSyncFromSUUNTO($user);

        return $this->redirect($this->generateUrl('ks_set_services')); 
    }
  
   /**
     * 
     * @Route("/ksSetServiceGoogleAgenda", name = "ksSetServiceGoogleAgenda", options={"expose"=true})
     */
    public function ksSetServiceGoogleAgendaAction()
    {
        $user               = $this->get('security.context')->getToken()->getUser();
        $em                 = $this->getDoctrine()->getEntityManager();
        $agenda             = $user->getAgenda();
        $agendaHasEvents    = null;
        $googleUri          = null;
        $my_calendar        = 'http://www.google.com/calendar/feeds/default/private/full';
        $accessToken        = null;
        $serviceIsActive    = false;
        $session            = $this->get('session');
        //Récupération du service
        $service            = $em->getRepository('KsUserBundle:Service')->findOneByName("Google-Agenda");
        if (!is_object($service) ) {
            throw new AccessDeniedException("Impossible de trouver de trouver le service Google-Agenda ");
        }
        //Récupération de l'utilisateur associé à ce service
        $idService          = $service->getId();
        $idAgenda           = $user->getAgenda()->getId();
        $userHasService     = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array("service"=>$idService,"user"=>$user->getId()));
        if (!is_object($userHasService) ) {
                throw new AccessDeniedException("Impossible de trouver le service google associé a l'utilisateur ".$user->getId()."");
        }
        //on vérifie si c'est la première synchro
        $firstSync = $userHasService->getFirstSync();
        //On essaye de récupérer le jeton
        if (!$firstSync && $session->get('cal_token')==null) {
            $service = $em->getRepository('KsUserBundle:Service')->findOneByName("Google-Agenda");
            $idService = $service->getId();
            $userHasService = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array('service'=>$idService,'user'=>$user->getId()));
            $accessToken = $userHasService->getToken();
            $serviceIsActive = $userHasService->getIsActive();
        }   
        //on le met en session
        if($accessToken!=null && $serviceIsActive){
           $session->set('cal_token', $accessToken );
        }else{
             //if($session->get('cal_token')==null){
                 
                if (isset($_GET['token'])) {
                    // Vous pouvez convertir le jeton unique en jeton de session.
                    $session_token = \Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
                    //Enregistre le jetton en BDD 
                    $userHasService->setToken($session_token);
                    $userHasService->setIsActive(true);
                    $em->persist($userHasService);
                    $em->flush();
                    // Enregistre le jeton de session, dans la session PHP.
                    $session->set('cal_token',$session_token );
                    
                } else {
                    // Affiche le lien permettant la génération du jeton unique.
                    $googleUri = \Zend_Gdata_AuthSub::getAuthSubTokenUri(
                        'http://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'],
                        $my_calendar, 0, 1);
                }
            //}
        }
        
        //$accessToken = $session->get('cal_token');
        $userId = $userHasService->getUser()->getId();
        $serviceId = $userHasService->getService()->getId();
        
        $nameFile = "userid-".$userId."_serviceid-".$serviceId."";

        //On vérifie que le fichier n'existe pas déjà (signifie synchro déjà lancée)
        if (file_exists($this->container->get('kernel')->getRootdir().'/jobs/pending/googleagenda/'.$nameFile.'.job')){
            $this->get('session')->setFlash('alert alert-error', 'users.service_sync_google_already_inprogress');
        } else {
            //On créé un fichier avec l'identificant du service et de l'utilisateur
            $file = fopen ($this->container->get('kernel')->getRootdir().'/jobs/pending/googleagenda/'.$nameFile.'.job', "a+");
            fwrite($file, "php ".$this->container->get('kernel')->getRootdir()."/console ks:user:googleagendasync $nameFile");
            fclose($file);
            $this->get('session')->setFlash('alert alert-success', 'users.sync_googleagenda_inprogress_done_with_success');
            //FMO : before Les événements supprimés uniquement coté Google Agenda seront automatiquement recréés (suppression effective uniquement si elle est opéré du coté de l'agenda Keepinsport)
            $this->get('session')->setFlash('alert alert-info', 'users.delete_google_agenda_event_info');
            $userHasService->setStatus("pending");
            $em->persist($userHasService);
            $em->flush();
            
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkParamService($user->getId());
        }
        
        return $this->redirect($this->generateUrl('ks_set_services'));
    }
}