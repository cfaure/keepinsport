<?php
namespace Ks\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Ks\UserBundle\Entity\UserHasFriends;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/*Pour executer une commande du controller*/
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class UserController extends Controller
{
    /**
     * @Route("/", name="_ks_index")
     * @Template()
     */
    public function indexAction()
    {
        return $this->render(
            'KsUserBundle:User:index.html.twig',
            array()
        );  
    }
    
    /**
     * @Route("/legal_mentions", name="ks_legal_mentions")
     * @Template()
     */
    public function legalMentionsAction()
    {
        return $this->render(
            'KsUserBundle:User:legalMentions.html.twig',
            array()
        );  
    }
    
    /**
     * @Route("/general_conditions", name="ks_general_conditions")
     * @Template()
     */
    public function generalConditionsAction()
    {
        return $this->render(
            'KsUserBundle:User:generalConditions.html.twig',
            array()
        );  
    }
    
    /**
     * @Route("/sales_conditions", name="ks_sales_conditions")
     * @Template()
     */
    public function salesConditionsAction()
    {
        return $this->render(
            'KsUserBundle:User:salesConditions.html.twig',
            array()
        );  
    }
    
    /**
     * @Route("/change_language/{locale}", requirements={"langue" = "fr|en|ru"},
     *   defaults= {"langue" = "de"}, name = "ks_choisir_langue" )
     */
    public function choisirLangueAction($locale = null) {
        if($locale != null)
        {
            // On enregistre la langue en session
            $this->container->get('session')->setLocale($locale);
            $this->get('session')->set('locale', $locale);
            $this->get('session')->set('_locale', $locale);
        }
       
        

        // on tente de rediriger vers la page d'origine
        $url = $this->container->get('request')->headers->get('referer');
        if(empty($url)) {
            $url = $this->container->get('router')->generate('fos_user_security_login');
        }
        return new RedirectResponse($url);
    }
    
    /**
     * @Route("/users/{numPage}", name = "ks_user_UsersList" )
     */
    public function usersListAction($numPage)
    {
        // On récupère le repository
        $repository = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('KsUserBundle:User');

        // On récupère le nombre total d'utilisateurs
        $nb_users = $repository->getTotal();

        // On définit le nombre d'utilisateurs par page
        $nb_users_page = 5;

        // On calcule le nombre total de pages
        $nb_pages = ceil($nb_users/$nb_users_page);
        $nb_pages = $nb_pages > 0 ? $nb_pages : 1;
        
        // On va récupérer les utilisateurs à partir du N-ième événement :
        $offset = ($numPage-1) * $nb_users_page;

        // Ici on a changé la condition pour déclencher une erreur 404
        // lorsque la page est inférieur à 1 ou supérieur au nombre max.
        if( $numPage < 1 OR $numPage > $nb_pages )
        {
            throw $this->createNotFoundException('Page inexistante (page = '. $numPage .')');
        }

        // On récupère les utilisateurs qu'il faut grâce à findBy() :
        $users = $repository->findBy(
            array(),                 // Pas de critère
            array('username' => 'asc'), // On tri par date décroissante
            $nb_users_page,       // On sélectionne $nb_users_page articles
            $offset                  // A partir du $offset ième
        );
        
        //On récupère l'utilisateur connecté
        $user1 = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($user1) )
        {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }
        
        $aUsers = array();
        //On parcours les utilisateurs et on defini une variable "areFriends"
        foreach($users as $key => $user) {
            $aUsers[$key]["friendWithMe"]                       = $repository->areFriends($user1->getId(), $user->getId()); 
            $aUsers[$key]["mustGiveRequestFriendResponse"]      = $repository->mustGiveRequestFriendResponse($user1->getId(), $user->getId());
            $aUsers[$key]["isAwaitingRequestFriendResponse"]    = $repository->isAwaitingRequestFriendResponse($user1->getId(), $user->getId());
        }

        return $this->render('KsUserBundle:User:usersList.html.twig', array(
            'connectedUserId'   => $user1->getId(),
            'users'             => $users,
            'aUsers'            => $aUsers,
            'page'              => $numPage,
            'nb_pages'          => $nb_pages
        ));
    }
    
    /**
     * Finds and displays a Club entity.
     *
     * @Route("/{id}/show", name="ksUser_show", options={"expose"=true})
     * @ParamConverter("user", class="KsUserBundle:User")
     * @Template()
     */
    public function showAction(\Ks\UserBundle\Entity\User $user)
    {
        return $this->redirect($this->generateUrl('ks_user_public_profile', array("username" => $user->getUsername())));
    }

    /**
     * @Route("/public_profile/{username}", name="ks_user_public_profile", options={"expose"=true})
     * @Template()
     */
    public function publicProfileAction($username)
    {   
        $em                 = $this->getDoctrine()->getEntityManager();  
        $userRep            = $em->getRepository('KsUserBundle:User');
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $activitySessionRep = $em->getRepository('KsActivityBundle:ActivitySession');
        $sportRep           = $em->getRepository('KsActivityBundle:Sport');
        
        $me = $this->container->get('security.context')->getToken()->getUser();

        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'profile');
        
        if (!is_object($me)) {
            //throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
            $me = $userRep->findById(1);
            var_dump($me->getId());
            exit;
        }
        
        $user = $userRep->findOneByUsername($username);
        
        if (!is_object($user))
        {
            throw new AccessDeniedException('Impossible de récupérer l\'utilisateur ' . $username .".");
        }

        //$trophiesInShowcase = $showcaseETRep->findByShowcase($user->getShowcase()->getId());
        $lastActivities     = $activitySessionRep->findLastActivitiesSession($user);
        
        //Récupération de mes Amis 
        $friendsIds    = $userRep->getFriendIds($user->getId());
        if( count( $friendsIds ) == 0 ) $friendsIds[] = 0;
        $friends    = $userRep->findUsers(array("usersIds" => $friendsIds), $this->get('translator'));
        $clubs      = $user->getClubs();
     
        
       
        $links              = $activityRep->findLinksFromUser($user, 5);
        $activitySessions   = $activityRep->findActivitySessionsFromUser($user, 0, 5, "all");
        $sports             = $sportRep->findAll();
        
        $friendWithMe                       = $userRep->areFriends($me->getId(), $user->getId()); 
        $mustGiveRequestFriendResponse      = $userRep->mustGiveRequestFriendResponse($me->getId(), $user->getId());
        $isAwaitingRequestFriendResponse    = $userRep->isAwaitingRequestFriendResponse($me->getId(), $user->getId());
        
        $userH = $userRep->findOneUser(array("userId" => $user->getId()), $this->get('translator'));
        $godsons = $userRep->findGodsonsAndLittlesGodsons($user->getId());
        
        //Pour le moment on redirige vers le talbeau de bord de l'utilisateur
        return $this->redirect($this->generateUrl('ksAgenda_dashboard', array("id" => $user->getId())));
        
        /*return $this->render(
            'KsUserBundle:User:publicProfile.html.twig', array(
                'profileUser'           => $user,
                'user'                  => $user,
                'lastActivitiesSession' => $lastActivities,
                'friends'               => $friends,
                'clubs'                 => $clubs,
                'links'                 => $links,
                'activitySessions'      => $activitySessions,
                'sports'                => $sports,
                'friendWithMe'          => $friendWithMe,
                'mustGiveRFResponse'    => $mustGiveRequestFriendResponse,
                'isAwaitingRFResponse'  => $isAwaitingRequestFriendResponse,
                'godsons'               => $godsons,
                'userH'                 => $userH,
                'session'               => $session->get('page')
            )
        );*/
    }
    
    /**
     * @Route("/services", name="ks_set_services", options={"expose"=true})
     * @Template()
     */
    public function servicesAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $user       = $this->get('security.context')->getToken()->getUser();
        $clubRep    = $em->getRepository('KsClubBundle:Club');

        $services   = null;
        $request    = $this->getRequest();

        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'services');
        
        ini_set('memory_limit', '1024M');

        //Si club = GEEP alors synchro possible entre Google Agenda et Agenda KS
        $myClubs   = $clubRep->findMyClubs( $user );

        $isAllowed = false;

        foreach($myClubs as $myclub) {
            if ($myclub->getName() == "GEEP!") $isAllowed = true;
        }

        $userHasServices = $user->getServices();
        $userServices    = array();
        foreach ($userHasServices as $userHasService) {
            $userServices[$userHasService->getService()->getName()] = $userHasService;
        }

        return array(
            'services'              => $em->getRepository('KsUserBundle:Service')->findAll(),
            'userHasServices'       => $userServices,
            'isAllowed'             => $user->getIsAllowedPackPremium(),
            "isAllowedPackElite"    => $user->getIsAllowedPackElite(),
        );

    }
    
    /**
     * @Route("/activeService/{userId}_{serviceId}", name="ksUser_activeService", options={"expose"=true})
     * @Template()
     */
    public function activeServiceAction($userId, $serviceId)
    {
       $em              = $this->getDoctrine()->getEntityManager();
       $serviceRep      = $em->getRepository('KsUserBundle:Service');
       $userRep         = $em->getRepository('KsUserBundle:User');
       
       $responseDatas = array(
           "code" => 1
       );
       
       $user = $userRep->find($userId);
       $service = $serviceRep->find($serviceId);
       
       if( is_object($user) && is_object($service) ) {
           $serviceRep->activateService( $serviceId, $userId );
           
           $responseDatas["html"] = $this->render('KsUserBundle:User:_activeServiceButton.html.twig', array(
                'service'        => $service,
                "user"       => $user
            ))->getContent();
       }

        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/deactiveService/{userId}_{serviceId}", name="ksUser_deactiveService", options={"expose"=true})
     * @Template()
     */
    public function deactiveServiceAction($userId, $serviceId)
    {
       $em              = $this->getDoctrine()->getEntityManager();
       $serviceRep      = $em->getRepository('KsUserBundle:Service');
       $userRep         = $em->getRepository('KsUserBundle:User');
       
       $responseDatas = array(
           "code" => 1
       );
       
       $user = $userRep->find($userId);
       $service = $serviceRep->find($serviceId);
       
       if( is_object($user) && is_object($service) ) {
           $serviceRep->deactivateService( $serviceId, $userId );
           
           $responseDatas["html"] = $this->render('KsUserBundle:User:_activeServiceButton.html.twig', array(
                'service'        => $service,
                "user"       => $user
            ))->getContent();
       }

        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/manage_agenda", name="ks_manage_agenda")
     * @Template()
     */
    public function manageAgendaAction()
    {
        $em     = $this->getDoctrine()->getEntityManager();
        $user   = $this->get('security.context')->getToken()->getUser();
        $lang   = $this->container->get('session')->getLocale();
        
        $translationCalendar = "";
        if ($lang=="fr") {
            $translationCalendar = "monthNames:['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
                            monthNamesShort:['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'],
                            dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
                            dayNamesShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
                            titleFormat: {
                                month: 'MMMM yyyy',
                                week: 'd[ MMMM][ yyyy]{ - d MMMM yyyy}',
                            day: 'dddd d MMMM yyyy'
                            },
                            columnFormat: {
                                month: 'ddd',
                            week: 'ddd d',
                            day: ''
                            },
                            axisFormat: 'H:mm', 
                            timeFormat: {
                                '': 'H:mm', 
                            agenda: 'H:mm{ - H:mm}'
                            },
                            firstDay:1,
                            buttonText: {
                                today: 'aujourd\'hui',
                                day: 'jour',
                                week:'semaine',
                                month:'mois'
                            },";
        }
        
        /*---------Gmap--------------------*/
        // on recupére le service geocode
        $geocoder = $this->get('ivory_google_map.geocoder');
        // Geocode a location ici Paris
        $response = $geocoder->geocode('Paris, France');
        // on recupére le service google map
        $map = $this->get('ivory_google_map.map');
        //on enlève le prefix d'appel de la variable Javascript
        $map->setPrefixJavascriptVariable('');
        //on nomme la vaiable map ; traitment en js avec ce nom la 
        $map->setJavascriptVariable('map');
        //Zoom par défaut de la carte 
        $map->setMapOption('zoom', 7);
        $map->setLanguage('fr');
        //on récupère un marker
        $marker = $this->get('ivory_google_map.marker');
         //on enlève le prefix d'appel de la variable Javascript
        $marker->setPrefixJavascriptVariable('');
         //on nomme la vaiable map ; traitment en js avec ce nom la 
        $marker->setJavascriptVariable('marker');
        //Normalement un seul résultat pour Paris
        if(count($response->getResults())==1){
            foreach($response->getResults() as $result){
                $marker->setPosition($result->getGeometry()->getLocation());
                $latitude = $result->getGeometry()->getLocation()->getLatitude();
                $longitude = $result->getGeometry()->getLocation()->getLongitude();
                $map->setCenter($latitude, $longitude, true);
            }
        }
        //on ajoute le marker 
        $map->addMarker($marker);
        /*---------------------*/
        
        /* Récupération des différentes listes */
        $statesOfHealth = $em->getRepository('KsActivityBundle:StateOfHealth')->findAll();
        $typeEvents     = $em->getRepository('KsEventBundle:TypeEvent')->findAll();
        $weathers = $em->getRepository('KsActivityBundle:Weather')->findAll();
        $sports = $em->getRepository('KsActivityBundle:Sport')->getSportsASC();
        
        
        $user       = $this->get('security.context')->getToken()->getUser();
        $idUser     = $user->getId();
        $repository = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('KsUserBundle:User');

        $friends    = $repository->getFriendList($idUser);
        $aFriends   = array();
        
        //On récupéres les infos des amis 
        // FIXME: pourquoi récupérer un tableau d'objets si on a besoin que de name et id ?
        foreach ($friends as $key => $friend) {
            $aFriends[$key]["name"] = $friend->getUsername();
            $aFriends[$key]["id"]   = $friend->getId();
            $userDetail             = $friend->getUserDetail();
            if ($userDetail != null) {
               $lastname    =  $userDetail->getLastname();
               $firstname   = $userDetail->getFirstname();
               if (!empty($lastname) && !empty($firstname)){
                   $aFriends[$key]["name"] = $lastname." ".$firstname;
               }
            }
        }
        
        return array(
            'user'              => $user,
            'translationCalendar' => $translationCalendar,
            'map'               => $map,
            'statesOfHealth'    => $statesOfHealth,
            'typeEvents'        => $typeEvents,
            'weathers'          => $weathers,
            'sports'            => $sports,
            'aFriends'          => $aFriends
        ); 
        
        
    }
    
    /**
     * @Route("/synchro_google_agenda", name="ks_synchro_google_agenda")
     * @Template()
     */
    public function syncWithGoogleAgendaAction()
    {
        $user               = $this->get('security.context')->getToken()->getUser();
        $em                 = $this->getDoctrine()->getEntityManager();
        
        $my_calendar        = 'http://www.google.com/calendar/feeds/default/private/full';
        $googleUri          = null;
        $listFeed           = null;
        $eventFeed          = null;
        $aEventInfos        = array();
        $accessToken        = null;
        $serviceIsActive    = false;
        /*$firstSync          = false;*/

        $service            = $em->getRepository('KsUserBundle:Service')->findOneByName("Google-Agenda");
        if (!is_object($service) ) {
            throw new AccessDeniedException("Impossible de trouver de trouver le service Google-Agenda ");
        }

        $idService          = $service->getId(); 
        $session            = $this->get('session');
        
        if ($session->get('cal_token')==null) {
            $userHasService = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array('service'=>$idService,'user'=>$user->getId()));
            $accessToken = $userHasService->getToken();
            $serviceIsActive = $userHasService->getIsActive();
        }   
        
        if($accessToken!=null && $serviceIsActive){
            $session->set( 'cal_token', $accessToken );
        }else{
             if($session->get('cal_token')==null) {
                 
                if (isset($_GET['token'])) {
                    // Vous pouvez convertir le jeton unique en jeton de session.
                    //$session_token = \Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
                    //Enregistre le jetton en BDD 
                    $userHasService->setToken($session_token);
                    $userHasService->setIsActive(true);
                    $em->persist($userHasService);
                    $em->flush();
                    $session->set( 'cal_token', $session_token );
                    
                } else {
                    // Affiche le lien permettant la génération du jeton unique.
                    /*$googleUri = \Zend_Gdata_AuthSub::getAuthSubTokenUri(
                        'http://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'],
                        $my_calendar, 0, 1);*/
                }
            }
        }
       
        
       $idAgenda = $user->getAgenda()->getId();
       $agendaHasEvents = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array("service"=>$idService,"user"=>$user->getId()));
       if (!is_object($agendaHasEvents) ) {
            throw new AccessDeniedException("Impossible de trouver le service google associé a l'utilisateur ".$user->getId()."");
       }
       
       $firstSync = $agendaHasEvents->getFirstSync();
      
       if ($session->get('cal_token')!=null) {
             
             // Création d'un client HTTP authentifié
            // pour les échanges avec les serveurs Google.
            $client = \Zend_Gdata_AuthSub::getHttpClient($session->get('cal_token'));

            // Création d'un objet Gdata utilisant le client HTTP authentifié :
            $cal = new \Zend_Gdata_Calendar($client);

            try {
                $listFeed= $cal->getCalendarListFeed();
            } catch (\Zend_Gdata_App_Exception $e) {
                echo "Error: " .
                        $e->getMessage();
            }
            
            //un mois avant 
            $between =  new \Datetime('Now');
            //$between = $datetime->format("Y-m-d");
 
            $interval = 'P30D';
            $i = new \DateInterval( $interval );
            date_sub($between, $i);

            $query = $cal->newEventQuery();
            $query->setUser('default');
            $query->setVisibility('private');
            $query->setProjection('full');
            $query->setOrderby('starttime');
            $query->setMaxResults(100);
            $query->setStartMin($between->format("Y-m-d"));
            
            //si premiere fois on prends les évent anterieur 
            //sinon unnquement posterieur
            if($firstSync){
               $query->setFutureevents(false); 
            }else{
               $query->setFutureevents(true); 
            }
            
            // Retrieve the event list from the calendar server
            try {
                $eventFeed = $cal->getCalendarEventFeed($query);
            } catch (\Zend_Gdata_App_Exception $e) {
                echo "Error: " . $e->getMessage();
            }
            
            if(!empty($eventFeed)){
                // Ici affichage uniquement des événements 
                // qui n'ont pas été synchro 
                foreach($eventFeed as $key => $event){
                    $aEventInfos[$key]['idEventBDD'] = null;
                    $aEventInfos[$key]["title"] = $event->title;
                    $aEventInfos[$key]["id"] = $event->id;
                    $aEventInfos[$key]["content"] = $event->content;
                    if(isset($event->when) && isset($event->when[0]) && isset($event->when[0]->startTime)){
                        $aEventInfos[$key]["startTime"] = $event->when[0]->startTime;
                    }else{
                        $aEventInfos[$key]["startTime"] = "NC";
                    }
                    
                    if(isset($event->when) && isset($event->when[0]) && isset($event->when[0]->endTime)){
                        $aEventInfos[$key]["endTime"] = $event->when[0]->endTime;
                    }else{
                         $aEventInfos[$key]["endTime"] = "NC";
                    }
                    
                    
                    
                    $aEventInfos[$key]['stateEvent'] = "Déja synchronisé dans votre agenda KeepInSport";
                    $aEventInfos[$key]['tobesyncwithgoogle'] = false;
                    $googleEventBDD = $em->getRepository('KsEventBundle:GoogleEvent')->findOneBy(array("id_url_event"=>$event->id));
                    if($googleEventBDD != null){
                        $eventBDD = $em->getRepository('KsEventBundle:Event')->findOneByIsGoogleEvent($googleEventBDD->getId());
                        $dateStartBDD = $eventBDD->getStartDate();
                        $dateStartBDD = $eventBDD->getStartDate()->format("Y-m-d H:i:s");
                        $dateEndBDD = $eventBDD->getEndDate();
                        $dateEndBDD = $eventBDD->getEndDate()->format("Y-m-d H:i:s");
                        $nameBDD = $eventBDD->getName();
                        $contentBDD = $eventBDD->getContent();
                        //google infos
                        if(isset($event->when) && isset($event->when[0]) && isset($event->when[0]->startTime)){
                             $startTime = $event->when[0]->startTime;
                             $startTime = new \DateTime($startTime);
                             $dateStartGoogle = $startTime->format("Y-m-d H:i:s");
                        }
                    
                        if(isset($event->when) && isset($event->when[0]) && isset($event->when[0]->endTime)){
                            $endTime = $event->when[0]->endTime;
                            $endTime = new \DateTime($endTime);
                            $dateEndGoogle = $endTime->format("Y-m-d H:i:s");
                        }

                        $nameGoogle = $event->title;
                        $contentGoogle = $event->content;
                        //Détection ici des changements 
                        if($dateStartBDD != $dateStartGoogle || 
                            $dateEndBDD != $dateEndGoogle ||
                            $nameBDD != $nameGoogle ||
                            $contentBDD != $contentGoogle   
                        ){
                            $dateGoogle = new \DateTime($event->updated);
                            $dateLastModificationsGoogle = $dateGoogle->setTimeZone(new \DateTimeZone("Europe/Paris"));
                            $dateLastModificationsGoogle = $dateLastModificationsGoogle->format("Y-m-d H:i:s");
                            $dateLastModificationsBDD = $eventBDD->getLastModificationDate()->format("Y-m-d H:i:s");
                            //l'événement doit etre synchronisé si la date de modification de l'événement keepin est > date google 
                            if($dateLastModificationsBDD < $dateLastModificationsGoogle){
                                $aEventInfos[$key]['stateEvent'] = "Prêt à être synchronisé";
                                $aEventInfos[$key]['tobesyncwithgoogle'] = true;
                                $aEventInfos[$key]['idEventBDD'] = $eventBDD->getId();
                            }
                        }


                        //$aEventInfos[$key]["stateEvent"] = "Déja synchronisé dans votre agenda Keeinsport";
                    }else{
                        $aEventInfos[$key]["stateEvent"] = "Prêt à être synchronisé";
                        $aEventInfos[$key]['tobesyncwithgoogle'] = true;
                    }
                }
            }else{
               $aEventInfos = false; 
            }
            
            
            
  

        }

        return array(
            'user'        => $user,
            'googleUri'   => $googleUri,
            'listFeed'    => $listFeed,
            'eventFeed'   => $aEventInfos,
        ); 
        
        
    }
    
    
    /**
     * @Route("/add_google_events_to_keepin_agenda", name="ks_add_google_events_to_keepin_agenda")
     * @Template()
     */
    public function addGoogleEventsToKeepinAgendaAction()
    {
        $user       = $this->get('security.context')->getToken()->getUser();
        $em         = $this->getDoctrine()->getEntityManager();
        $request    = $this->getRequest();
        $params     = $request->request->all();
        
        $nbEvents   = $request->request->get('nbEvents');
        $addAnEvent = false;
        
        for ($i = 0; $i < $nbEvents; ++$i) {
            $toBeSyncWithGoogle = $request->request->get('tobesyncwithgoogle_'.$i.'');
            $idEventUrl = $request->request->get('id_event_url_'.$i.'');
            $title = $request->request->get('title_'.$i.'');
            $startTime = $request->request->get('startTime_'.$i.'');
            $endTime = $request->request->get('endTime_'.$i.'');
            $content = $request->request->get('content_event_'.$i.'');
            $idEventBdd = $request->request->get('idEventBDD_'.$i.'');
            $agenda = $user->getAgenda();
            
            //Signifie que c'est un événement que l'on doit mettre à jour 
            if($idEventBdd!=null){
                $event = $em->getRepository('KsEventBundle:Event')->find($idEventBdd);
                if (!is_object($event) ) {
                    throw new AccessDeniedException("Impossible de trouver l'événement ".$idEventBdd." ");
                }
                $googleEvent = $event->getIsGoogleEvent();
                $agendaHasEvent = $em->getRepository('KsAgendaBundle:AgendaHasEvents')->findOneBy(array("agenda"=>$agenda->getId(),"event"=>$idEventBdd));
                if (!is_object($agendaHasEvent) ) {
                    throw new AccessDeniedException("Impossible de trouver l'événement ".$idEventBdd." de votre agenda ".$agenda->getId()." ");
                }
            }else{
                $googleEvent = $em->getRepository('KsEventBundle:GoogleEvent')->findOneBy(array("id_url_event"=>$idEventUrl));
                $event = new \Ks\EventBundle\Entity\Event();
                $agendaHasEvent = new \Ks\AgendaBundle\Entity\AgendaHasEvents($agenda, $event);
            }

            if($toBeSyncWithGoogle){
                    
                    $addAnEvent = true;
                    if($googleEvent==null) {
                        $googleEvent = new \Ks\EventBundle\Entity\GoogleEvent();  
                    }

                    if(isset($title) && isset($startTime) && isset($endTime) && isset($idEventUrl) ){

                        $googleEvent->setName($title);
                        $googleEvent->setIdUrlEvent($idEventUrl);
                        $em->persist($googleEvent);
                        $em->flush();

                        $event->setName($title);
                        if(isset($content)){
                            $event->setContent($content);  
                        }
                        if($idEventBdd==null){
                            $event->setCreationDate(new \DateTime('now'));
                        }
                        $startTime = new \DateTime($startTime);
                        $event->setStartDate($startTime);
                        $endTime = new \DateTime($endTime);
                        $event->setEndDate($endTime);
                        $event->setLastModificationDate(new \DateTime('now'));
                        $event->setUser($user);
                        $typeEvent = $em->getRepository('KsEventBundle:TypeEvent')->findOneBy(array("nom_type"=>"event_google"));
                        if($typeEvent){
                            $event->setTypeEvent($typeEvent);
                        }
                        $event->setIsGoogleEvent($googleEvent);
                        $em->persist($event);
                        $em->flush();
                        /*$agendaHasEvent->setAgenda($agenda);
                        $agendaHasEvent->setEvent($event);*/
                        $em->persist($agendaHasEvent);
                        $em->flush();
                        //$aEventAdded[] = $title;
                }

            }

        }
        
        if($addAnEvent){
            //First Synchro = false 
            $service            = $em->getRepository('KsUserBundle:Service')->findOneByName("Google-Agenda");
            if (!is_object($service) ) {
                throw new AccessDeniedException("Impossible de trouver de trouver le service Google-Agenda ");
            }
            $idService          = $service->getId();
            $idAgenda = $user->getAgenda()->getId();
            $agendaHasEvents = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array("service"=>$idService,"user"=>$user->getId()));
            if (!is_object($agendaHasEvents) ) {
                    throw new AccessDeniedException("Impossible de trouver le service google associé a l'utilisateur ".$user->getId()."");
            }
            $firstSync = $agendaHasEvents->getFirstSync();
            if($firstSync){
                $agendaHasEvents->setFirstSync(false);
                $em->persist($agendaHasEvents);
                $em->flush();
            }
            
            
            $this->get('session')->setFlash('alert alert-success', 'users.update_keepin_agenda_success');
        }else{
            $this->get('session')->setFlash('alert alert-error', 'users.event_not_added');
        }
        
        return $this->redirect($this->generateUrl('ks_manage_agenda'));
    }
    
    
    /**
     * @Route("/synchro_keepin_agenda", name="ks_synchro_keepin_agenda")
     * @Template()
     */
    public function syncKeepinAgendaAction()
    {   
        //TODO décallage horaire calcqué uniquement sur la France ; 
        // Rendre dynamique et adapté a chaque pays        
        $user               = $this->get('security.context')->getToken()->getUser();
        $em                 = $this->getDoctrine()->getEntityManager();
        $agenda             = $user->getAgenda();
        $agendaHasEvents    = null;
        $googleUri          = null;
        $my_calendar        = 'http://www.google.com/calendar/feeds/default/private/full';
        $accessToken        = null;
        $serviceIsActive    = false;
        
        $session            = $this->get('session');

        $service            = $em->getRepository('KsUserBundle:Service')->findOneByName("Google-Agenda");
        if (!is_object($service) ) {
            throw new AccessDeniedException("Impossible de trouver de trouver le service Google-Agenda ");
        }
        $idService          = $service->getId();
        $idAgenda = $user->getAgenda()->getId();
        $agendaHasEvents = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array("service"=>$idService,"user"=>$user->getId()));
        if (!is_object($agendaHasEvents) ) {
                throw new AccessDeniedException("Impossible de trouver le service google associé a l'utilisateur ".$user->getId()."");
        }
        
        $firstSync = $agendaHasEvents->getFirstSync();
        
        if ($session->get('cal_token')==null) {
            $service = $em->getRepository('KsUserBundle:Service')->findOneByName("Google-Agenda");
            $idService = $service->getId();
            $userHasService = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array('service'=>$idService,'user'=>$user->getId()));
            $accessToken = $userHasService->getToken();
            $serviceIsActive = $userHasService->getIsActive();
        }   

        if($accessToken!=null && $serviceIsActive){
           $session->set('cal_token', $accessToken );
        }else{
             if ( $session->get('cal_token')==null) {
                 
                if (isset($_GET['token'])) {
                    // Vous pouvez convertir le jeton unique en jeton de session.
                    $session_token =
                        \Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
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
            }
        }
        
        
        if ( $session->get('cal_token')!=null) {
            $client = \Zend_Gdata_AuthSub::getHttpClient($session->get('cal_token'));
            $cal = new \Zend_Gdata_Calendar($client);
        }  
        
        //Récupération des éléments postérieurs à aujourd'hui 
        //Si c'est pas la première synchro
        if($firstSync){
            $agendaHasEvents = $em->getRepository('KsAgendaBundle:AgendaHasEvents')->findBy(array("agenda"=>$agenda->getId()));
        }else{
            //récupération de agendaHasEvent uniquement posterieur
            $userRep            = $em->getRepository('KsUserBundle:User');
            $agendaHasEvents    = $userRep->getAgendaHasEventOnFuture($agenda->getId());
        }
        
        
        
        //Pour savoir si l'événement existe toujours 
        //il faut parcourir tous les événements et regarder coté google
        $aEventInfos = array();
        foreach($agendaHasEvents as $key => $agendaHasEvent){
            $event = $agendaHasEvent->getEvent();
            $aEventInfos[$key]['name'] = $event->getName();
            $aEventInfos[$key]['startDate'] = $event->getStartDate();
            $aEventInfos[$key]['endDate'] = $event->getEndDate();
            $aEventInfos[$key]['content'] = $event->getContent();
            $aEventInfos[$key]['id'] = $event->getId();
            $aEventInfos[$key]['state'] = "Prêt à être synchronisé";
            $aEventInfos[$key]['idUrlEvent'] = false;
            $aEventInfos[$key]['tobesyncwithgoogle'] = true;
            //on vérifie si c'est un événement de type google
            if($event->getIsGoogleEvent()){
              $eventGoogle = null;  
              $googleEventBDD = $event->getIsGoogleEvent();
              $idUrlEvent = $googleEventBDD->getIdUrlEvent();
              $aEventInfos[$key]['idUrlEvent'] = $idUrlEvent;
              try {
                 $eventGoogle = $cal->getCalendarEventEntry($idUrlEvent);
                 //il faut vérifier si l'événement n'as pas subit de modifications
                 $startTime = $eventGoogle->when[0]->startTime;
                 $endTime = $eventGoogle->when[0]->endTime;
                 $startDateBDD = $event->getStartDate()->format("Y-m-d H:i:s");
                 $endDateBDD = $event->getEndDate()->format("Y-m-d H:i:s");
                 $startTime = new \DateTime($startTime);
                 $startDateGoogle = $startTime->format("Y-m-d H:i:s");
                 $endTime = new \DateTime($endTime);
                 $endDateGoogle = $endTime->format("Y-m-d H:i:s");
                 $eventNameBDD = $event->getName();
                 $eventNameGoogle = $eventGoogle->title;
                 $eventContentBDD = $event->getContent();
                 $eventContentGoogle = $eventGoogle->content;
                 //il y a une différence entre le bdd et la calendirer google
                 if($startDateBDD != $startDateGoogle || 
                    $endDateBDD != $endDateGoogle ||
                    $eventNameBDD != $eventNameGoogle ||
                    $eventContentBDD != $eventContentGoogle   
                    ){
                    $dateGoogle = new \DateTime($eventGoogle->updated);
                    $dateLastModificationsGoogle = $dateGoogle->setTimeZone(new \DateTimeZone("Europe/Paris"));
                    $dateLastModificationsGoogle = $dateLastModificationsGoogle->format("Y-m-d H:i:s");
                    $dateLastModificationsBDD = $event->getLastModificationDate()->format("Y-m-d H:i:s");
                    //l'événement doit etre synchronisé si la date de modification de l'événement keepin est > date google 
                    if($dateLastModificationsBDD > $dateLastModificationsGoogle){
                        $aEventInfos[$key]['state'] = "Prêt à être synchronisé";
                        $aEventInfos[$key]['tobesyncwithgoogle'] = true;
                        //Supression de l'evenement Keepin pour le remplacer coté google 
                        $eventGoogle->delete();
                    }
                 }else{
                    $aEventInfos[$key]['tobesyncwithgoogle'] = false; 
                    $aEventInfos[$key]['state'] = "Déja synchronisé dans votre agenda Google";
                 }                 
              }catch (\Zend_Gdata_App_Exception $e) {
                 // c'est un événement google enregistré coté keepin
                 // Mais non trouvé coté google
                 // donc on le recréé
                 $aEventInfos[$key]['state'] = "Prêt à être synchronisé";
                 $aEventInfos[$key]['tobesyncwithgoogle'] = true; 
              }
            }

        }

        
        return array(
            'events'    => $aEventInfos,
            'googleUri' => $googleUri,
        );
        
        
    }
    
    
    /**
     * @Route("/add_keepin_events_to_google_agenda", name="ks_add_keepin_events_to_google_agenda")
     * @Template()
     */
    public function addKeepinEventsToGoogleAgendaAction()
    {
        $user           = $this->get('security.context')->getToken()->getUser();
        $em             = $this->getDoctrine()->getEntityManager();
        $request        = $this->getRequest();
        $params         = $request->request->all();
        $newEvent       = null;
        $eventGoogle    = null;
        
        $nbEvents       = $request->request->get('nbEvents');
        $addAnEvent     = false;
        
        if (isset($_SESSION['cal_token'])) {
            $client = \Zend_Gdata_AuthSub::getHttpClient($_SESSION['cal_token']);
            $cal    = new \Zend_Gdata_Calendar($client);
        }   

        for($i=0;$i<$nbEvents;$i++){
            $toBeSyncWithGoogle = $request->request->get('tobesyncwithgoogle_'.$i.'');
            $title = $request->request->get('title_'.$i.'');
            $startDateTime = $request->request->get('startTime_'.$i.'');
            $endDateTime = $request->request->get('endTime_'.$i.'');
            $content = $request->request->get('content_event_'.$i.'');
            $idEvent = $request->request->get('id_event_'.$i.'');
            $idEventUrl = $request->request->get('id_event_url_'.$i.'');
            if($toBeSyncWithGoogle){
                $addAnEvent = true;
                // Create a new entry using the calendar service's magic factory method
                $event = $cal->newEventEntry();
                // Populate the event with the desired information
                // Note that each attribute is crated as an instance of a matching class
                $event->title = $cal->newTitle($title);
                //$event->where = array($cal->newWhere("Mountain View, California"));
                if(isset($content)){
                    $event->content = $cal->newContent($content);
                }
                
                $startDateTime = new \DateTime($startDateTime);
                $startDate = $startDateTime->format("Y-m-d");
                $startTime = $startDateTime->format("H:i");
                $endDateTime = new \DateTime($endDateTime);
                $endDate = $endDateTime->format("Y-m-d");
                $endTime = $endDateTime->format("H:i");
                $tzOffset = "+02";
                

                $when = $cal->newWhen();
                $when->startTime = "{$startDate}T{$startTime}:00.000{$tzOffset}:00";
                $when->endTime = "{$endDate}T{$endTime}:00.000{$tzOffset}:00";

                $event->when = array($when);

                $newEvent = $cal->insertEvent($event);
            }

            $eventGoogle = $em->getRepository('KsEventBundle:GoogleEvent')->findOneBy(array("id_url_event"=>$idEventUrl));
            
            if($eventGoogle!=null){
                $googleEvent = $eventGoogle;
            }else{
                $googleEvent = new \Ks\EventBundle\Entity\GoogleEvent(); 
            }
            
            if(isset($title) && isset($startTime) && isset($endTime)){
                //on met a jour notre bdd pour dire que 
                //c'est désormais une èvenemtn de type google Evetn
                
                if($toBeSyncWithGoogle){
                    $googleEvent->setName($title);
                    $googleEvent->setIdUrlEvent($newEvent->id);
                    $em->persist($googleEvent);
                    $em->flush();
                    $eventK = null;
                    //on le lis a l'événement courant
                    $eventK = $em->getRepository('KsEventBundle:Event')->find($idEvent);
                    if($eventK!=null){
                        $isGoogleEvent  = $eventK->getIsGoogleEvent();
                        if($isGoogleEvent==null){
                            $eventK->setIsGoogleEvent($googleEvent);
                        }
                        $em->persist($googleEvent);
                        $em->flush();
                    }
                }
                
                
            }

         }   
     
        if($addAnEvent){
            
            
            //First Synchro = false 
            $service            = $em->getRepository('KsUserBundle:Service')->findOneByName("Google-Agenda");
            if (!is_object($service) ) {
                throw new AccessDeniedException("Impossible de trouver de trouver le service Google-Agenda ");
            }
            $idService          = $service->getId();
            $idAgenda = $user->getAgenda()->getId();
            $agendaHasEvents = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array("service"=>$idService,"user"=>$user->getId()));
            if (!is_object($agendaHasEvents) ) {
                    throw new AccessDeniedException("Impossible de trouver le service google associé a l'utilisateur ".$user->getId()."");
            }
            $firstSync = $agendaHasEvents->getFirstSync();
            if($firstSync){
                $agendaHasEvents->setFirstSync(false);
                $em->persist($agendaHasEvents);
                $em->flush();
            }
            
            
            $this->get('session')->setFlash('alert alert-success', 'users.update_google_agenda_success');
            
        }else{
            $this->get('session')->setFlash('alert alert-error', 'users.event_not_added_google');
        }
        
        return $this->redirect($this->generateUrl('ks_synchro_keepin_agenda'));

    }
    
    
     /**
     * @Route("/activity_session_list_in_stand_by", name="ks_activity_session_list_in_stand_by")
     * @Template()
     */
    public function activitySessionListInStandByAction()
    {   
       //récupération de la liste des activités à valider 
       $user               = $this->get('security.context')->getToken()->getUser();
       $em                 = $this->getDoctrine()->getEntityManager();
       $userRep            = $em->getRepository('KsUserBundle:User');
       $agendaHasEvents    = $userRep->getActivitySessionInStandByOfAUser($user->getAgenda()->getId());
      
       return array(
            'agendaHasEvents'    => $agendaHasEvents,
       );
        
    }
    
     /**
     * @Route("/get_all_friends", name = "ksgetAllFriends" )
    */
    public function getAllFriends() {
        
        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {
            $user   = $this->get('security.context')->getToken()->getUser();
            $idUser     = $user->getId();
            $repository = $this->getDoctrine()
                ->getEntityManager()
                ->getRepository('KsUserBundle:User');

            $friends    = $repository->getFriendList($idUser);
            $aFriends   = array();
            // On récupéres les infos des amis 
            // FIXME: Code en double !
            foreach($friends as $key => $friend) {
                $aFriends[$key]["name"] = $friend->getUsername();
                $aFriends[$key]["id"]   = $friend->getId();
                $userDetail             = $friend->getUserDetail();
                if ($userDetail!=null) {
                    $aFriends[$key]["name"] = $userDetail->getLastname()." ".$userDetail->getFirstname();
                }
            }
            $response = new Response(json_encode(array('aFriends' => $aFriends)));
            
             //« Content-type » json pour le retour
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }
        
    }
    
    
     /**
     * @Route("/invitation_event_list_in_stand_by", name="ks_invitation_event_in_stand_by")
     * @Template()
     */
    public function invitationEventListInStandByAction()
    {   
       //récupération de la liste des activités à valider 
       $em      = $this->getDoctrine()->getEntityManager();
       $user    = $this->get('security.context')->getToken()->getUser();
       
       //Récupération des invitations à un événement
       $status = $em->getRepository('KsEventBundle:Status')->findOneByName("en-attente"); 
       if (!is_object($status) ) {
           throw new AccessDeniedException("Impossible de trouver le statut 'en-attente' ");
       }
       $invitationEvents = $em->getRepository('KsEventBundle:InvitationEvent')->findBy(array("userInvited"=>$user->getId(),"status"=>$status->getId()));
      
       return array(
            'invitationEvents'    => $invitationEvents,
       );
        
    }
    
    /**
     * @Route("/user_accept_invitation_event/{eventId}", name="ks_user_accept_invitation_event")
     * @Template()
     */
    public function userAcceptInvitationEventAction($eventId = null)
    {   
        if ($eventId == null) {
            throw $this->createNotFoundException("L'identifiant de l'événement a été mal transmis");
        }

        $em     = $this->getDoctrine()->getEntityManager();
        $user   = $this->get('security.context')->getToken()->getUser();

        //Récupération de l'événement 
        $event = $em->getRepository('KsEventBundle:Event')->find($eventId);
        if (!is_object($event) ) {
            throw $this->createNotFoundException("L'événement ".$eventId." n'existe pas");
        }
        $agenda     = $user->getAgenda();
        $agendaId   = $agenda->getId();

        $agendaHasEvent = $em
            ->getRepository('KsAgendaBundle:AgendaHasEvents')
            ->findOneBy(array("agenda"=>$agendaId ,"event"=> $eventId));
        if ($agendaHasEvent!=null){
            $this->get('session')->setFlash('alert alert-error', 'users.event_already_exist_in_your_agenda');
        } else {
            $agendaHasEvent = new \Ks\AgendaBundle\Entity\AgendaHasEvents();
            $agendaHasEvent->setAgenda($agenda);
            $agendaHasEvent->setEvent($event);
            $em->persist($agendaHasEvent);
            $em->flush();
            $invitationEvent = $em->getRepository('KsEventBundle:invitationEvent')->findOneBy(array("userInvited"=>$user->getId(),"event"=>$eventId));
            if (!is_object($invitationEvent) ) {
                throw $this->createNotFoundException("Impossible de trouver l'inivtation à l'événement ".$eventId." ayant pour utilisateur invité ".$user->getId()."  ");
            }
            $status = $em->getRepository('KsEventBundle:Status')->findOneByName("accepte"); 
            if (!is_object($status) ) {
                throw new AccessDeniedException("Impossible de trouver le statut 'en-attente' ");
            }
            $invitationEvent->setStatus($status);
            $em->persist($invitationEvent);
            $em->flush();
            $this->get('session')->setFlash('alert alert-success', 'users.event_added_to_calendar_with_success');
        }

        return $this->redirect($this->generateUrl('ks_invitation_event_in_stand_by'));
    }
    
    
     /**
     * @Route("/user_refuse_invitation_event/{eventId}", name="ks_user_refuse_invitation_event")
     * @Template()
     */
    public function userRefuseInvitationEventAction($eventId = null)
    {   

        if($eventId == null) {
            throw $this->createNotFoundException("L'identifiant de l'événement a été mal transmis");
        }

        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();

        $event = $em->getRepository('KsEventBundle:Event')->find($eventId);
        if (!is_object($event) ) {
            throw $this->createNotFoundException("L'événement ".$eventId." n'existe pas");
        }

        $invitationEvent = $em->getRepository('KsEventBundle:invitationEvent')->findOneBy(array("userInvited"=>$user->getId(),"event"=>$eventId));
        if (!is_object($invitationEvent) ) {
            throw $this->createNotFoundException("Impossible de trouver l'inivtation à l'événement ".$eventId." ayant pour utilisateur invité ".$user->getId()."  ");
        }
        $status = $em->getRepository('KsEventBundle:Status')->findOneByName("refuse"); 
        if (!is_object($status) ) {
            throw new AccessDeniedException("Impossible de trouver le statut 'refuse' ");
        }
        $invitationEvent->setStatus($status);
        $em->persist($invitationEvent);
        $em->flush();
        $this->get('session')->setFlash('alert alert-success', 'users.event_refuse_to_calendar_with_success');
       
       
       return $this->redirect($this->generateUrl('ks_invitation_event_in_stand_by'));
       

    }
    
    /**
     * @Route("/user_upload_gpx_file", name="ks_user_upload_gpx_file")
     * @Template()
     */
    public function userUploadGpxFileAction()
    {
        $em     = $this->getDoctrine()->getEntityManager();
        $user        = $this->get('security.context')->getToken()->getUser();
        $sportRep    = $em->getRepository('KsActivityBundle:Sport');
        $gpx    = new \Ks\ActivityBundle\Entity\Gpx();
        $form   = $this->createFormBuilder($gpx)
            ->add('sport', 'entity', array(
                'class'         => 'Ks\ActivityBundle\Entity\Sport',
                'property'      => "label",
                'query_builder' => function(\Ks\ActivityBundle\Entity\SportRepository $rep) {
                    return $rep->findSportsQB();
                }
            ))
            ->add('file', 'file', array("required" => true))
            ->getForm();
            
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                
                $gpx->setUploadedBy($this->container->get('security.context')->getToken()->getUser()->getId());
                $gpx->setUploadedAt(new \DateTime("now"));
                $gpx->upload();
                
                $em->persist($gpx);
                $em->flush();

                $this->get('session')->setFlash('alert alert-success', 'users.upload_gpx_with_success');
                
                return $this->redirect($this->generateUrl('ks_parse_gpx_to_add_activity', array('id' => $gpx->getId())));
                
            } else {
                $this->get('session')->setFlash('alert alert-error', 'Le fichier GPX est erroné, merci de nous contacter par mail avec le feedback ci-dessous !');
                //var_dump(\Form\FormValidator::getErrorMessages($form));
            }
        }
        
        $userSports = array();
        if ( $user->getUserDetail() != null ) {
            foreach( $user->getUserDetail()->getSports() as $sport ) {
                $userSports[] = $sport->getCodeSport();
            }
        }
        
        $sports = $sportRep->getSportsASC();
        
        $favoriteSports = $otherSports = array();
        foreach($sports as $sport){
            if( in_array($sport->getCodeSport(), $userSports ) ) {
                $favoriteSports[] = array(
                    "id"    => $sport->getId(),
                    "label" => $sport->getLabel(),
                    "code"  => $sport->getCodeSport()
                );
            }
            else {
                //$typeSport = $sport->getSportType()->getCode();
                $otherSports[] = array(
                    "id"    => $sport->getId(),
                    "label" => $sport->getLabel(),
                    "code"  => $sport->getCodeSport()
                );
            }
        }
        
        $aSports = array(
            0 => $favoriteSports,
            1 => $otherSports
        );

        return array(
            'form' => $form->createView(),
            'sportsGroups'     => $aSports
        );
    }
 
    /**
     * @Route("/parse_gpx_to_add_activity/{id}", name="ks_parse_gpx_to_add_activity")
     * @Template()
     */
    public function parseGpxToAddActivityAction($id)
    {   
        $importService = $this->get('ks_activity.importActivityService');
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $user       = $this->get('security.context')->getToken()->getUser();   
        $em         = $this->getDoctrine()->getEntityManager();
        $gpx        = $em->getRepository('KsActivityBundle:Gpx')->find($id);
        $gpxPath    = $this->container->get('kernel')->getRootdir()
            .DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR.'web'
            .DIRECTORY_SEPARATOR.'uploads'
            .DIRECTORY_SEPARATOR.'gpx'
            .DIRECTORY_SEPARATOR.$gpx->getName();
       
        list($activityDatas, $error) = $importService->buildJsonToSave($user, array('fileName' => $gpxPath), 'gpx');
        
        if (!$activityDatas || count($activityDatas) == 0) {
            $this->get('session')->setFlash('alert alert-warning', 'Format du fichier GPX incorrect');
            return $this->redirect($this->generateUrl('ks_upload_import_gpx_file'));
        }
        
        $activityDatas['codeSport'] = $gpx->getSport();
        $session = $importService->saveUserSessionFromActivityDatas($activityDatas, $user);
        
        $gpx->setActivity($session);
        $em->persist($gpx);
        
        //On abonne le user à son activité importée
        $activityRep->subscribeOnActivity($session, $user);
                
        //On lui fait gagner les points qu'il doit
        $leagueLevelService = $this->get('ks_league.leagueLevelService');
        $leagueLevelService->activitySessionEarnPoints($session, $user);
        
        // Flush de la session doctrine
        $em->flush();
        
        //Mise à jour des étoiles
        $leagueCategoryId = $user->getLeagueLevel()->getCategory()->getId();
        if( is_integer( $leagueCategoryId ) ) $leagueLevelService->leagueRankingUpdate( $leagueCategoryId );
        

        //redirection
        $this->get('session')
            ->setFlash('alert alert-success', 'users.add_activity_with_success');
        
        return $this->redirect($this->generateUrl('ksActivity_activitiesList'));
    }
    
    /**
     * @Route("/verifUsername", name = "ksVerifUsernameExist" )
    */
    public function verifUsername() {
        
        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {
            $em                 = $this->getDoctrine()->getEntityManager();
            $username = $request->request->get('username');
            $currentUser = $this->get('security.context')->getToken()->getUser();   
            $user = $em->getRepository('KsUserBundle:User')->findOneByUsername($username);

            $exist = false;
            if(isset($user) && $user->getId() != $currentUser->getId()){
                $exist = true;
            }

            $response = new Response(json_encode(array('exist' => $exist)));
            
             //« Content-type » json pour le retour
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;

        }
        
    }
    
    /**
     * @Route("/feedback", name="ksUser_feedbackBlock" )
     */
    public function feedbackBlocAction() {
        $em = $this->getDoctrine()->getEntityManager();
        
        $repository = $em->getRepository('KsNotificationBundle:Notification');
        
        //On récupère l'utilisateur connecté
        $user = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($user) )
        {
            $user = new \Ks\UserBundle\Entity\User();
            $user->setUsername("Invité");
        }
        
        $feedback = new \Ks\UserBundle\Entity\Feedback($user);
        
        $feedbackForm = $this->createForm(new \Ks\UserBundle\Form\FeedbackType(), $feedback);

        return $this->render('KsUserBundle:User:FeedbackBlock.html.twig', array(
            'feedbackForm'             => $feedbackForm->createView(),
        ));
    }
    
    public function feedbackFormAction() {
        $em = $this->getDoctrine()->getEntityManager();
        
        $repository = $em->getRepository('KsNotificationBundle:Notification');
        
        //On récupère l'utilisateur connecté
        $user = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($user) )
        {
            $user = new \Ks\UserBundle\Entity\User();
            $user->setUsername("Invité");
        }
        
        $feedback = new \Ks\UserBundle\Entity\Feedback($user);
        
        $feedbackForm = $this->createForm(new \Ks\UserBundle\Form\FeedbackType(), $feedback);

        return $this->render('KsUserBundle:User:_feedback_form.html.twig', array(
            'feedbackForm'             => $feedbackForm->createView(),
        ));
    }
    
    /**
     * @Route("/postFeedback", name = "ksUser_postFeedback", options={"expose"=true} )
     */
    public function postFeedbackAction()
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $commentRep         = $em->getRepository('KsActivityBundle:Comment');
        $user               = $this->get('security.context')->getToken()->getUser();
        
        $host       = $this->container->getParameter('host');
        $pathWeb    = $this->container->getParameter('path_web');
        $mailer     = $this->container->get('mailer');

        $responseDatas = array(
            "code" => 1
        );
        
        $parameters = $request->request->all();
        $feedback = isset( $parameters["ks_user_feedback"]["description"] ) && !empty( $parameters["ks_user_feedback"]["description"] ) ? $parameters["ks_user_feedback"]["description"] : '';
        
        /*$feedback = new \Ks\UserBundle\Entity\Feedback($user);
 
        $feedbackForm = $this->createForm(new \Ks\UserBundle\Form\FeedbackType(), $feedback);
        
         // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $formHandler = new \Ks\UserBundle\Form\FeedbackHandler($feedbackForm, $request, $em);

        //$responseDatas = $formHandler->process();*/
        
        //Si le commentaire est publié, on envoi une notification au propriétaire de l'activité
        if ($feedback != "") {
            //Envoi d'email à l'admin 
            $contentMail = $this->container
                ->get('templating')
                ->render(
                    'KsUserBundle:User:_feedback_mail.html.twig',
                    array(
                        'user'          => is_object( $user ) ? $user : null,
                        'feedback'      => $feedback
                    ),
                    'text/html'
                );
            $body = $this->container->get('templating')->render('KsUserBundle:User:template_mail.html.twig', 
                array(
                    'host'      => $host,
                    'pathWeb'   => $pathWeb,
                    'content'   => $contentMail,
                    'user'      => is_object( $user ) ? $user : null
                ), 
            'text/html');

            $message = \Swift_Message::newInstance()
                ->setContentType('text/html')
                ->setSubject(is_object( $user ) ? "Feedback de " . $user->getUsername().' ('.$user->getEmail().')' : "Feedback")
                ->setFrom('contact@keepinsport.com')
                ->setTo('contact@keepinsport.com')
                ->setBody($body);
            
            //var_dump($body);
            //exit;
            
            $mailer->getTransport()->start();
            $mailer->send($message);
            $mailer->getTransport()->stop();

            
            if( is_object( $user )) {
                //Envoi d'un mail à l'utilisateur pour lui signaler le traitement de son problème           
                $contentMail = $this->container
                    ->get('templating')
                    ->render(
                        'KsUserBundle:User:_feedback_process_mail.html.twig',
                        array(
                            'user'          => $user,
                        ),
                        'text/html'
                    );
                $body = $this->container->get('templating')->render('KsUserBundle:User:template_mail.html.twig', 
                    array(
                        'host'      => $host,
                        'pathWeb'   => $pathWeb,
                        'content'   => $contentMail,
                        'user'      => is_object( $user ) ? $user : null
                    ), 
                'text/html');

                $message = \Swift_Message::newInstance()
                    ->setContentType('text/html')
                    ->setSubject("Feedback bien reçu, merci !")
                    ->setFrom("contact@keepinsport.com")
                    ->setTo($user->getEmail())
                    ->setBody($body);

                $mailer->getTransport()->start();
                $mailer->send($message);
                $mailer->getTransport()->stop();;
            }
            
        } 
        
        //throw new HttpException('Submit failed');
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');
        return $response;    
    }
    
    /**
     * @Route("/postNewSport", name = "ksUser_postNewSport", options={"expose"=true} )
     */
    public function postNewSportAction()
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        
        $host       = $this->container->getParameter('host');
        $pathWeb    = $this->container->getParameter('path_web');
        $mailer     = $this->container->get('mailer');

        $responseDatas = array(
            "code" => 1
        );
        
        $parameters = $request->request->all();
        $newSport = isset( $parameters["sportForm"]["sportLabel"]) && !empty( $parameters["sportForm"]["sportLabel"] ) ? $parameters["sportForm"]["sportLabel"] : 'sport vraiment pas trouvé :(';
        
        //Envoi d'un mail à l'utilisateur pour lui signaler le traitement de son problème           
        if( is_object( $user )) {
            $contentMail = $this->container
                ->get('templating')
                ->render(
                    'KsUserBundle:User:_new_sport_process_mail.html.twig',
                    array(
                        'user'          => $user,
                        'newSport'      => $newSport
                    ),
                    'text/html'
                );
            $body = $this->container->get('templating')->render('KsUserBundle:User:template_mail.html.twig', 
                array(
                    'host'      => $host,
                    'pathWeb'   => $pathWeb,
                    'content'   => $contentMail,
                    'user'      => is_object( $user ) ? $user : null
                ), 
            'text/html');

            $message = \Swift_Message::newInstance()
                ->setContentType('text/html')
                ->setSubject("Propostion de sport bien reçue, merci !")
                ->setFrom("contact@keepinsport.com")
                ->setTo($user->getEmail())
                ->setBody($body);

            $mailer->getTransport()->start();
            $mailer->send($message);
            $mailer->getTransport()->stop();
        }
        
        //Envoi d'email à l'admin 
        $contentMail = $this->container
            ->get('templating')
            ->render(
                'KsUserBundle:User:_new_sport_mail.html.twig',
                array(
                    'user'          => is_object( $user ) ? $user : null,
                    'sport'         => $newSport
                ),
                'text/html'
            );
        $body = $this->container->get('templating')->render('KsUserBundle:User:template_mail.html.twig', 
            array(
                'host'      => $host,
                'pathWeb'   => $pathWeb,
                'content'   => $contentMail,
                'user'      => is_object( $user ) ? $user : null
            ), 
        'text/html');

        $message = \Swift_Message::newInstance()
            ->setContentType('text/html')
            ->setSubject(is_object( $user ) ? "Nouveau sport proposé de " . $user->getUsername() : "Feedback")
            ->setFrom("contact@keepinsport.com")
            ->setTo("contact@keepinsport.com")
            ->setBody($body);

        $mailer->getTransport()->start();
        $mailer->send($message);
        $mailer->getTransport()->stop();
        
        //TOFIX : FMO je sais pas quoi mettre en retour pour gérer l'erreur lors de l'envoi du mail, pour le moment on part du principe que l'envoi de mail ce fait !
        //$response = new Response(json_encode($responseDatas));
        //$response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    
    /**
     * @Route("/userSyncGoogleAgendaUri", name = "userSyncGoogleAgendaUri" )
    */
    public function userSyncGoogleAgendaUriAction() {        

       $user            = $this->get('security.context')->getToken()->getUser();
       $my_calendar     = 'http://www.google.com/calendar/feeds/default/private/full';
       
       $route = 'ksSetServiceGoogleAgenda';
       $url = $this->container->get('router')->generate($route);
       
       // Affiche le lien permettant la génération du jeton unique.
       $googleUri = \Zend_Gdata_AuthSub::getAuthSubTokenUri('http://'. $_SERVER['SERVER_NAME'] . $url, $my_calendar, 0, 1);
       
       //var_dump($googleUri);
       
       return $this->render('KsUserBundle:User:userSyncGoogleAgendaUri.html.twig', array(
            'googleUri'  =>  $googleUri,
            'email'      =>  $user->getEmail()
        ));

    }
    
    /**
     * @Route("/sportifsBlocList", name = "ksNotification_sportifsBlockList" )
     */
    public function sportifsBlocListAction() {
        $em                 = $this->getDoctrine()->getEntityManager();
        $invitationMailRep  = $em->getRepository('KsUserBundle:InvitationEmailBeta');
        $user               = $this->container->get('security.context')->getToken()->getUser();
         
        $couldInvit = false;
    
        $invitationEmailBeta = $invitationMailRep->findOneByEmail($user->getEmail());
        if( is_object( $invitationEmailBeta ) ){
            $couldInvit = $invitationEmailBeta->getCouldInvit();
        }
        
        $redirectUri = $this->container->get('router')->generate('send_an_invit_to_friend_by_address_book', array(), true);
        //Si pas trouvé requet vers l'api pour le recup 
        $urlGoogleContact = 'https://accounts.google.com/o/oauth2/auth?client_id=221396145208.apps.googleusercontent.com&redirect_uri='.$redirectUri.'&scope=https://www.google.com/m8/feeds/&response_type=code';
    
        return $this->render('KsUserBundle:User:sportifsBlockList.html.twig', array(
            'couldInvit'            => $couldInvit,
            'urlGoogleContact'      => $urlGoogleContact
        ));
    }
    
    /**
     * @Route("/community/{userId}", name = "ks_user_communityDynamicList" )
     */
    public function communityDynamicListAction($userId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();  
        $userRep            = $em->getRepository('KsUserBundle:User');
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'friends');
        
        //On récupère l'utilisateur connecté
        $user = $userRep->find($userId);

        if( ! is_object($user) )
        {
            throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $userId );
        }
        
        $friendsIds = $userRep->getFriendIds( $user->getId() );
        //$usersOfMyCommunity = $userRep->getCommunityOf($user);
        if( count($friendsIds) < 1 ) $friendsIsd[] = 0;
        $users = $userRep->findUsers(array("usersIds" => $friendsIds), $this->get('translator'));
        
        //On cherche les liaison d'amitié
        foreach( $users as $key => $u ) {
            $users[$key]['areFriends']                          = $userRep->areFriends($user->getId(), $u['id']);
            $users[$key]['mustGiveRequestFriendResponse']       = $userRep->mustGiveRequestFriendResponse($user->getId(), $u['id']);
            $users[$key]['isAwaitingRequestFriendResponse']     = $userRep->isAwaitingRequestFriendResponse($user->getId(), $u['id']);
        }
        
        return $this->render('KsUserBundle:User:community.html.twig', array(
            'users'    => $users,
        ));
    }
    
    /**
     * @Route("/sportifs", name = "ks_sportifs_all", options={"expose"=true} )
     */
    public function sportifsAction()
    {
        $em                 = $this->getDoctrine()->getEntityManager();  
        $userRep            = $em->getRepository('KsUserBundle:User'); 
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'friends');
        $session->set('page', 'sportsmenList');
        
        //$user               = $this->container->get('security.context')->getToken()->getUser();   

        $users = $userRep->findUsers(array(), $this->get('translator'));
        
        //On cherche les liaison d'amitié
        /*foreach( $users as $key => $u ) {
            $users[$key]['areFriends']                          = $userRep->areFriends($user->getId(), $u['id']);
            $users[$key]['mustGiveRequestFriendResponse']       = $userRep->mustGiveRequestFriendResponse($user->getId(), $u['id']);
            $users[$key]['isAwaitingRequestFriendResponse']     = $userRep->isAwaitingRequestFriendResponse($user->getId(), $u['id']);
        }*/
        
        return $this->render('KsUserBundle:User:sportifs.html.twig', array(
            'users'    => $users,
        ));
        
        /*return $this->render('KsUserBundle:User:communityDynamicList_objects.html.twig', array(
            'users'    => $users,
        ));*/
    }
    
    /**
     * @Route("/consultBlog", name = "ksUser_consultBlog" )
     */
    public function consultBlogAction() {
        $em                 = $this->getDoctrine()->getEntityManager();
        $securityContext    = $this->container->get('security.context');
           
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $securityContext->getToken()->getUser();

            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkConsultBlog($user->getId());
        } 

        return new RedirectResponse( 'http://blog.keepinsport.com' );
    }
    
    /**
     * @Route("/checklist", name = "ksUser_showChecklist" )
     * @Template()
     */
    public function checklistAction() {
        $em                     = $this->getDoctrine()->getEntityManager();
        $userRep                = $em->getRepository('KsUserBundle:User');
        $userChecklistActionRep = $em->getRepository('KsUserBundle:UserHasToDoChecklistAction');
        $securityContext        = $this->container->get('security.context');
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $securityContext->getToken()->getUser();
        }
        else {
            //$this->get('session')->setFlash('alert alert-error', 'La session a expirée. Merci de vous identifier.');
            //return $this->redirect($this->generateUrl('fos_user_security_login'));
            $user = $userRep->find(1);
        }
         
        $checklistActions = $userChecklistActionRep->findActionsToDo($user->getId());
        
        $checklistActionsWithLabelsOK = array();
        foreach( $checklistActions as $checklistAction ) {
            $checklistAction["label"] = $this->get('translator')->trans("checklist.".$checklistAction["code"]);
            //var_dump($checklistAction["label"]);
            $checklistActionsWithLabelsOK[] = array("id" => $checklistAction["id"], "label" => $checklistAction["label"], "code" => $checklistAction["code"], "date" => $checklistAction["date"]);
        }
        
        return array(
            "checklistActions" => $checklistActionsWithLabelsOK
        );
    }
    
    public function getUserRankData($userId)
    {
        //Récupération du classement en temps réel
        $em         = $this->getDoctrine()->getEntityManager();     
        $userRep    = $em->getRepository('KsUserBundle:User');
        
        $user =$userRep->find($userId);
        $leagueCategoryId = $user->getLeagueLevel()->getCategory()->getId();
        
        $users      = $userRep->findUsers(array(
            "withPoints"                    => true,
            "leagueCategoryId"              => $leagueCategoryId,
            "activitiesStartOn"             => date("Y-m-01"),
            "activitiesEndOn"               => date("Y-m-t"),
        ), $this->get('translator'));

        //Tri par points décroissants
        //var_dump($leagueCategoryId);
        usort( $users, array( "Ks\UserBundle\Entity\UserRepository", "orderUsersByPointsDesc" ) );
        
        $userRank =1;
        $found = false;
        foreach( $users as $userArray) {
            if ($userArray['id'] != $userId && !$found) {
                $userRank ++;
            }
            else $found = true;
        }
        
        $rankData = array("league" => $leagueCategoryId, "rank" => $userRank, "total" => count($users));
        
        //var_dump($rank);
        return $rankData;
    }
    
    /**
     * @Route("/bubbleInfos/{userId}}", name = "ksUser_bubbleInfos", options={"expose"=true} )
     */
    public function bubbleInfosAction($userId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();     
        $securityContext    = $this->container->get('security.context');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $shopRep            = $em->getRepository('KsShopBundle:Shop');
        $me                 = $this->container->get('security.context')->getToken()->getUser();
        
        $user = $userRep->findOneUser(array("usersIds" => array($userId)), $this->get('translator'));
        
        if( !$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            //Visitor
            $me = $userRep->find(1);
            //$this->addUserAction('newsFeed', 'visite', 'OK', null);
        }
        
        if( is_object($me) )
        {
            $user['areFriends']                      = $userRep->areFriends($me->getId(), $user['id']);
            $user['mustGiveRequestFriendResponse']   = $userRep->mustGiveRequestFriendResponse($me->getId(), $user['id']);
            $user['isAwaitingRequestFriendResponse'] = $userRep->isAwaitingRequestFriendResponse($me->getId(), $user['id']);
            
            //if ($shopId !=  '-1') $shop = $shopRep->find($shopId);
            
            //Récupération des données de parrainage
            $params = array(
                "activitiesStartOn"             => date("Y-m-01"),
                "activitiesEndOn"               => date("Y-m-t"),
                "withPoints"                    => true,
                "withGodsonsPoints"             => true, 
                //"sports"                => $shopId == -1 ? -1 : $shop->getSports(),
                //"countryCode"           => $shopId == -1 ? -1 : $shop->getCountryCode(),
                "withFriendsNumber"             => true,
                "withActivitiesNumber"          => true,
                "usersWith0points"              => false,
                "userId"                        => $userId,
                "withOnlyGodSonsFromThisMonth"  => true,
            );

            $godFatherData      = $userRep->findUsers( $params, $this->get('translator') );
            
            //var_dump($godFatherData);
            
            $rankData = $this->getUserRankData($userId);
            
        }
        
        //FIXME : pble d'encodage pas réussi à afficher correctement les mois en français...
        setlocale (LC_TIME, 'fr_FR.utf8','fra');
        $monthFR = mb_convert_encoding(strftime("%B", strtotime("- 0 month")), 'utf-8');
        if ($monthFR == 'aoÃ»t') $monthFR = 'aout';
        if ($monthFR == 'dÃ©cembre') $monthFR = 'décembre';
        if ($monthFR == 'fÃ©vrier') $monthFR = 'février';
        
        return $this->render('KsUserBundle:User:_bubbleInfos.html.twig', array(
            "month"         => $monthFR,
            "user"          => $user,
            "rankData"      => $rankData,
            "godFatherData" => ($godFatherData != null ? $godFatherData[0] : null)
        ));
    }
    
    /**
     * @Route("/updateGodFather", name = "ksUser_updateGodFather", options={"expose"=true} )
     */
    public function updateGodFatherAction( )
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->container->get('security.context')->getToken()->getUser();
        
        //sevices
        $notificationService  = $this->get('ks_notification.notificationService');
        
        $godFatherForm = $this->createForm(new \Ks\UserBundle\Form\GodFatherType($user), $user);
        $formHandler = new \Form\FormHandler( $godFatherForm, $request, $em);

        $responseDatas = $formHandler->process();

        //Si le parrain a été modifié
        if ($responseDatas['code'] == 1) {
            $user      = $this->container->get('security.context')->getToken()->getUser();
            $godFather = $user->getGodFather();
            if( is_object( $godFather ) ) {
                //On prévient le parrain
                $notificationService->sendNotification(
                        null,
                        $user,
                        $godFather,
                        "user",
                        $user->getUsername() . " a indiqué être ton filleul."
                );
            }
            $this->get('session')->setFlash('alert alert-success', "Ton parrain a été mis à jour avec succès.");
        } else {
            $this->get('session')->setFlash('alert alert-error', "Une erreur s'est produite");
        }
        
        return $this->redirect($this->generateUrl('ksContest_coach'));
    }
    
    /**
     * @Route("/userImage_league/{userId}", name = "ksUser_userImage_league" )
     */
    public function userImage_leagueAction($userId)
    {
        $em         = $this->getDoctrine()->getEntityManager();     
        $userRep    = $em->getRepository('KsUserBundle:User');
        
        $user       = $userRep->findUsers(array("usersIds" => array($userId)), $this->get('translator'));
        
        return $this->render('KsUserBundle:User:_userImage_league.html.twig', array(
            'user_id'               => $user[0]["id"], 
            'user_username'         => $user[0]["username"], 
            'user_imageName'        => $user[0]["imageName"],  
            'user_league_category'  => $user[0]["leagueCategoryLabel"], 
            'user_league_stars'     => $user[0]["leagueLevelStarNumber"],
            'pack_id'               => $user[0]["pack_id"],
            'isActive'              => $user[0]["isActive"],
            'isCoach'               => $user[0]["isCoach"],
            'withBubble'            => false
        ));
    }
    
    /**
     * @Route("/userImage_league/{userId}", name = "ksUser_userImage_league" )
     */
    public function userImage_league_miniAction($userId)
    {
        $em         = $this->getDoctrine()->getEntityManager();     
        $userRep    = $em->getRepository('KsUserBundle:User');
        
        $user       = $userRep->findUsers(array("usersIds" => array($userId)), $this->get('translator'));
        
        return $this->render('KsUserBundle:User:_userImage_league_mini.html.twig', array(
            'user_id'               => $user[0]["id"], 
            'user_username'         => $user[0]["username"], 
            'user_imageName'        => $user[0]["imageName"],  
            'user_league_category'  => $user[0]["leagueCategoryLabel"], 
            'user_league_stars'     => $user[0]["leagueLevelStarNumber"],
            'pack_id'               => $user[0]["pack_id"],
            'isCoach'               => $user[0]["isCoach"],
            'isActive'              => $user[0]["isActive"],            
        ));
    }
    
    /**
     * @Route("/sponsors", name = "ksUser_sponsors" )
     * @Template()
     */
    public function sponsorsAction() {
        $em                     = $this->getDoctrine()->getEntityManager();
        $user                   = $this->container->get('security.context')->getToken()->getUser();
        
         /*Sponsors */
        $refSponsors = array();
        
        $refSponsors["running"] = array(
            "S4572B555C4D1424",
            "S3786555C4D2151",
            "S483CF555C4D156",
            "S44C45555C4D112"
        );
        
        $refSponsors["cycling"] = array(
            "S414E5555C4D113"
        );
        
        $refSponsors["musculation"] = array(
            "S431B5555C4D118",
            "S3291555C4D1A1",
            "S2B0555C4D2191"
        );
        
        $refSponsors["judo"] = array(
        );
        
        $refSponsors["karate"] = $refSponsors["judo"];
        
        $refSponsors["scuba-diving"] = array(
            "S418FD555C4D1112",
        );
        
        $refSponsors["ski"] = array(
            "S41684555C4D2111",
            "S3C03555C4D21C3",
            "S3C02555C4D2211",
            "S4142B555C4D21122",
            "S41A15555C4D111"
        );
        
        //$refSponsors["snowboard"] = $refSponsors["ski"];
        
        $refSponsors["all"] = array(
            "S3CE1555C4D21C2",
            "S447F1555C4D148"
        );
        
        return array(
            "refSponsors" => $refSponsors
        );
    }    
    
    /**
     * @Route("/netaffiliation_728x90_form", name = "ksUser_netaffiliation_728x90_form" )
     * @Template()
     */
    public function netaffiliation_728x90_formAction() {
        $request            = $this->getRequest();
        $em                 = $this->getDoctrine()->getEntityManager();
        $userRep    = $em->getRepository('KsUserBundle:User');
        
        $netaffiliationRep  = $em->getRepository('KsUserBundle:Netaffiliation');
        
        //On récupère la liste des équipements avant la soumission du formulaire (pour effacer ceux qui ne servent plus)
        $netaffiliations = $netaffiliationRep->findAll();
        $previousNetaffiliations = array();
        foreach( $netaffiliations as $net) { $previousNetaffiliations[] = $net; }
        
        $keepinsportUser               = $userRep->findOneByUsername("keepinsport");
        
        $form = $this->createForm(new \Ks\UserBundle\Form\NetaffiliationsType($keepinsportUser), $keepinsportUser);
        
        if( $request->getMethod() == 'POST' ) {
            
            $formHandler = new \Ks\UserBundle\Form\NetaffiliationsHandler($form, $request, $em, $previousNetaffiliations);
            $responsedatas = $formHandler->process();
            if( $responsedatas["response"] == 1 ){
               $this->get('session')->setFlash('alert alert-success', 'Les références netaffiliation ont été mis à jour.');            
           } else {
               $errors = $responsedatas["errors"];
               va_dump($errors);
           }
        } 
        
        return array(
            "form" => $form->createView()
        );
    }    

    public function netAffiliation_728x90Action() {
        $securityContext    = $this->container->get('security.context');
        $user               = $securityContext->getToken()->getUser();
        $em                 = $this->getDoctrine()->getEntityManager();
        $netaffiliationRep  = $em->getRepository('KsUserBundle:Netaffiliation');
        
        $refSponsors = $netaffiliationRep->findReferencesOrberBySports();
        
        if (!empty( $refSponsors )) {
            /*Sponsors */
           /*$refSponsors = array();

           $refSponsors["running"] = array(
               "S4572B555C4D1411",
               "S4572B555C4D1423",
               "S3786555C4D1D248",
           );

           $refSponsors["spinning"] = array(
               "S414E5555C4D1112"
           );

           $refSponsors["cycling"] = $refSponsors["spinning"];
           $refSponsors["cycling"][] = "S3786555C4D1D108";

           $refSponsors["musculation"] = array(
               "S431B5555C4D111",
               "S3291555C4D1712"
           );


           $refSponsors["scuba-diving"] = array(
               "S418FD555C4D1111",
           );

           $refSponsors["ski"] = array(
               "S4142B555C4D1415",

           );

           $refSponsors["snowboard"] = $refSponsors["ski"];

           $refSponsors["all"] = array(
               "S3CE1555C4D2166",
           );*/
        
            $aCodeSports = array();

            if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
                if ( $user->getUserDetail() != null ) {
                    foreach( $user->getUserDetail()->getSports() as $sport ) {
                        $aCodeSports[] = $sport->getCodeSport();
                    }
                }
            }

            $codeSport = null;

            //Si l'utilisateur a renseigné au moins 1 sport
            if( count( $aCodeSports ) > 0 ) {
                $rand = array_rand( $aCodeSports, 1);
                $codeSport = $aCodeSports[$rand];
            } 

            //On verifie si on a des references pour ce sport là, sinon on prend une valeur au hazard
            if( $codeSport == null || !array_key_exists( $codeSport, $refSponsors ) ) { 
                $sportsSponsors = array_keys( $refSponsors );
                $rand = array_rand( $sportsSponsors, 1);  
                $codeSport = $sportsSponsors[$rand]; 
            }


            $rand = array_rand( $refSponsors[$codeSport], 1);  
            $refToAffich = $refSponsors[$codeSport][$rand];
        } else {
            $refToAffich = null;
        }
        return $this->render('KsUserBundle:User:_sponsor.html.twig', array(
            "refSponsor" => $refToAffich
        ));
    }
    
    /**
     * @Route("/searchUsers", name = "ksUser_search", options={"expose"=true} )
     */
    public function searchUsersAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();     
        $request         = $this->getRequest();
        $userRep    = $em->getRepository('KsUserBundle:User');
        $me       = $this->container->get('security.context')->getToken()->getUser();
        
        $parameters = $request->request->all();
        
        $responseDatas = array(
           "code" => 1
        );
        
        $searchTerms = $parameters["terms"] != "" ? explode(' ', $parameters["terms"]) : array();
        $leagues = isset( $parameters["leagues"] ) && !empty( $parameters["leagues"] ) ? $parameters["leagues"] : array();
        $sexes = isset( $parameters["sexes"] ) && !empty( $parameters["sexes"] ) ? $parameters["sexes"] : array();
        $offset = isset( $parameters["offset"] ) && !empty( $parameters["offset"] ) ? intval($parameters["offset"]) : 0;
        $limit = isset( $parameters["limit"] ) && !empty( $parameters["limit"] ) ? intval($parameters["limit"]) : 10; 
        $myFriends = isset( $parameters["myFriends"] ) && $parameters["myFriends"] == "true" ? true : false; 
        
        $params = array(
            'searchTerms'   => $searchTerms,
            'leagues'       => $leagues,
            'sexes'         => $sexes,
            'myFriends'     => $myFriends,
            'searchOffset'  => $offset,
            'searchLimit'   => $limit
        );
        
        if( is_object( $me ) && $myFriends) {
        
            $friendsIds = $userRep->getFriendIds( $me->getId() );
            if( count($friendsIds) >= 1 ) {
                $params["usersIds"] = $friendsIds;
            }
        }       
        
        $result = $userRep->findUsers($params, $this->get('translator'));
        
        $users = $result["users"];
        //var_dump($parameters["search"]);
        
        $responseDatas["users_number_not_loaded"] = $result["usersNumberNotLoaded"];
        $responseDatas["users_number"] = count( $users );
       
       
        $responseDatas["html"] = $this->render('KsUserBundle:User:_users_grid.html.twig', array(
            "users" => $users
        ))->getContent();;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/setNoobMode/", name = "ksUser_setNoobMode", options={"expose"=true}  )
     */
    public function setNoobModeAction()
    {    
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $preferenceRep      = $em->getRepository('KsUserBundle:Preference');

        /* On supprimer les préférences de type 1 du user
        * 1- Menu
        * 2- Filtres page actu
        */
        $userRep->deleteUserDetailHasPreferenceByType($user->getId(), 1);
        
        $preference = $preferenceRep->find(1);
        
        $user->getUserDetail()->addPreference($preference);
        $em->flush();
        
        $responseDatas = array(
            'response' => true
        ); 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/setExpertMode/", name = "ksUser_setExpertMode", options={"expose"=true}  )
     */
    public function setExpertModeAction()
    {    
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $preferenceRep      = $em->getRepository('KsUserBundle:Preference');

        /* On supprimer les préférences de type 1 du user
        * 1- Menu
        * 2- Filtres page actu
        */
        $userRep->deleteUserDetailHasPreferenceByType($user->getId(), 1);
        
        $preference = $preferenceRep->find(2);
        
        $user->getUserDetail()->addPreference($preference);
        $em->flush();
        
        $em->getRepository('KsUserBundle:ChecklistAction')->checkExpertMode($user->getId());
                
        $responseDatas = array(
            'response' => true
        ); 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/setVisitSeen/", name = "ksUser_setVisitSeen", options={"expose"=true}  )
     */
    public function setVisitSeenAction()
    {    
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $preferenceRep      = $em->getRepository('KsUserBundle:Preference');

        $em->getRepository('KsUserBundle:ChecklistAction')->checkVisitSeen($user->getId());
        
        $responseDatas = array(
            'response' => true
        ); 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/setCompetitionsSeen/", name = "ksUser_setCompetitionsSeen", options={"expose"=true}  )
     */
    public function setCompetitionsSeenAction()
    {    
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $preferenceRep      = $em->getRepository('KsUserBundle:Preference');

        $em->getRepository('KsUserBundle:ChecklistAction')->checkCompetitionsSeen($user->getId());
        
        $responseDatas = array(
            'response' => true
        ); 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/setDashboardSeen/", name = "ksUser_setDashboardSeen", options={"expose"=true}  )
     */
    public function setDashboardSeenAction()
    {    
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $preferenceRep      = $em->getRepository('KsUserBundle:Preference');

        $em->getRepository('KsUserBundle:ChecklistAction')->checkDashboardSeen($user->getId());
        
        $responseDatas = array(
            'response' => true
        ); 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/setActivityDetailSeen/", name = "ksUser_setActivityDetailSeen", options={"expose"=true}  )
     */
    public function setActivityDetailSeenAction()
    {    
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $preferenceRep      = $em->getRepository('KsUserBundle:Preference');

        $em->getRepository('KsUserBundle:ChecklistAction')->checkActivityDetailSeen($user->getId());
        
        $responseDatas = array(
            'response' => true
        ); 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/unsetVisitSeen/", name = "ksUser_unsetVisitSeen", options={"expose"=true}  )
     */
    public function unsetVisitSeenAction()
    {    
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $preferenceRep      = $em->getRepository('KsUserBundle:Preference');

        $em->getRepository('KsUserBundle:ChecklistAction')->uncheckVisitSeen($user->getId());
        
        $responseDatas = array(
            'response' => true
        ); 
        
        return new RedirectResponse($this->generateUrl('ksActivity_activitiesList'));
    }
    
    /**
     * @Route("/setUserPremium/", name = "ksUser_setPremium", options={"expose"=true}  )
     */
    public function setUserPremiumAction()
    {    
        $em                 = $this->getDoctrine()->getEntityManager();
        $request            = $this->getRequest();
        $user               = $this->get('security.context')->getToken()->getUser();
        $packRep            = $em->getRepository('KsUserBundle:Pack');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $userHasPackRep     = $em->getRepository('KsUserBundle:UserHasPack');
        
        $userHasPackAlready = $userHasPackRep->findByUser($user->getId());
        if (count($userHasPackAlready) == 0) {
            $userHasPack = new \Ks\UserBundle\Entity\UserHasPack($packRep->find(2), $user);
            $endDate = new \Datetime('Now');
            $endDate->add(new \DateInterval('P30D'));
            $userHasPack->setEndDate($endDate);
            $em->persist( $userHasPack );
            $em->flush();
            
            //Envoi de mail pour être au courant coté admin
            $notificationService   = $this->container->get('ks_notification.notificationService');
            $message = $user->__toString() . " est passé PREMIUM !";
            $notificationService->sendNotification(null, $userRep->find(1), $userRep->find(7), 'setPack', $message, null, null);
            
            //Envoi de mail pour auto pour que l'utilisateur soit au courant des fonctions possibles
            $message = "Félicitations tu es dorénavant un sportif Premium :)<br><br>Pour être sur que tu profites au maximum de cette offre pour ce 1er mois gratuit, n'hésite pas à revoir les différentes fonctionnalités qu'elle peut t'apporter en cliquant <a href='http://www.keepinsport.com/profile/offers#decouvrez' target='_blank'>en cliquant ici</a>.<br><br>Il y a aussi des tutoriaux ou des 'visites guidées' accessibles via les boutons du type : '?'  Ils te donneront plus d'infos sur comment bien utiliser telle ou telle fonction, <a href='http://www.keepinsport.com/activities/25974/show' target='_blank'>exemple ici.</a>.<br><br>Et pour finir, sache que pour 1 an d'abonnement à cette offre Premium <b>nous t'offrons 15% de réduction</b> sur une montre spécialisée ! <a href='http://www.keepinsport.com/profile/offers#bloc4-montres' target='_blank'>(voir nos&nbsp;<span><span class='il'>offres</span></span>)</a><br><br>N'hésite pas à nous solliciter par mail ou via le système de chat du site si tu as des questions ou remarques.";
            $notificationService->sendNotification(null, $userRep->find(1), $user, 'setPack', $message, null, null);
            
            $responseDatas = array(
                'response' => 1
            );
        }
        else {
            $endDate = $userHasPackAlready[0]->getEndDate();
            if (!is_null($endDate)) $delay = $userHasPackAlready[0]->getEndDate()->diff(new \DateTime('now'))->format('%R%a');
            else $delay = -1;
            if ($delay >0) {
                $responseDatas = array(
                    'response' => -1 // Période premium terminée
                ); 
            }
            else {
                $responseDatas = array(
                    'response' => 0
                );
            }
        }
        
        $parameters = $request->request->all();
        
        $choosenPack = isset( $parameters["pack"] ) && !empty( $parameters["pack"] ) ? $parameters["pack"] : null;
        $choosenWatch = isset( $parameters["watch"] ) && !empty( $parameters["watch"] ) ? $parameters["watch"] : null;
        $choosenCoach = isset( $parameters["coach"] ) && !empty( $parameters["coach"] ) ? $parameters["coach"] : null;
        $choosenPackOffer = isset( $parameters["packOfferChoice"] ) && !empty( $parameters["packOfferChoice"] ) ? $parameters["packOfferChoice"] : null;
        $choosenWatchOffer = isset( $parameters["watchOfferChoice"] ) && !empty( $parameters["watchOfferChoice"] ) ? $parameters["watchOfferChoice"] : null;
        
        if (!is_null($choosenPack)) $user->setChoosenPack($choosenPack);
        if (!is_null($choosenWatch)) $user->setChoosenWatch($choosenWatch);
        if (!is_null($choosenCoach)) $user->setChoosenCoach($choosenCoach);
        if (!is_null($choosenPackOffer)) $user->setChoosenPackOffer($choosenPackOffer);
        if (!is_null($choosenWatchOffer)) $user->setChoosenWatchOffer($choosenWatchOffer);
        
        $em->persist($user);
        $em->flush();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/setUserPrefPack/", name = "ksUser_setPrefPack", options={"expose"=true}  )
     */
    public function setUserPrefPackAction()
    {    
        $em                 = $this->getDoctrine()->getEntityManager();
        $request            = $this->container->get('request');
        $user               = $this->get('security.context')->getToken()->getUser();
        $packRep            = $em->getRepository('KsUserBundle:Pack');
        
        $parameters = $request->request->all();
        
        $choosenPack = isset( $parameters["pack"] ) && !empty( $parameters["pack"] ) ? $parameters["pack"] : null;
        $choosenWatch = isset( $parameters["watch"] ) && !empty( $parameters["watch"] ) ? $parameters["watch"] : null;
        $choosenCoach = isset( $parameters["coach"] ) && !empty( $parameters["coach"] ) ? $parameters["coach"] : null;
        $choosenPackOffer = isset( $parameters["packOfferChoice"] ) && !empty( $parameters["packOfferChoice"] ) ? $parameters["packOfferChoice"] : null;
        $choosenWatchOffer = isset( $parameters["watchOfferChoice"] ) && !empty( $parameters["watchOfferChoice"] ) ? $parameters["watchOfferChoice"] : null;
        
        if (!is_null($choosenPack)) $user->setChoosenPack($choosenPack);
        if (!is_null($choosenWatch)) $user->setChoosenWatch($choosenWatch);
        if (!is_null($choosenCoach)) $user->setChoosenCoach($choosenCoach);
        if (!is_null($choosenPackOffer)) $user->setChoosenPackOffer($choosenPackOffer);
        if (!is_null($choosenWatchOffer)) $user->setChoosenWatchOffer($choosenWatchOffer);
        
        $em->persist($user);
        $em->flush();
        
        $responseDatas = array(
            'response' => 1
        ); 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/savePreferenceNewsFeedFilters/", name = "ksUser_savePreferenceNewsFeedFiltres", options={"expose"=true}  )
     */
    public function savePreferenceNewsFeedFilters()
    {    
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $preferenceRep      = $em->getRepository('KsUserBundle:Preference');
        $preferenceTypeRep  = $em->getRepository('KsUserBundle:PreferenceType');
        $sportRep           = $em->getRepository('KsActivityBundle:Sport');
        $request            = $this->getRequest();
        
        $parameters         = $request->request->all();
        
        $activitiesTypes    = isset( $parameters["activitiesTypes"] ) ? $parameters["activitiesTypes"] : array();
        $activitiesFrom     = isset( $parameters["activitiesFrom"] ) && is_array( $parameters["activitiesFrom"] ) ? $parameters["activitiesFrom"] : array();
        $sports             = isset( $parameters["sports"] ) && is_array( $parameters["sports"] ) ? $parameters["sports"] : array();
        $userId             = isset( $parameters["activitiesFrom"] ) && is_numeric( $parameters["activitiesFrom"] ) ? (int)$parameters["activitiesFrom"] : 0;
        $lastModified       = ( isset( $parameters["lastModified"] ) && $parameters["lastModified"] == "true" ) ? 'update_sort' : 'creation_sort';

        
        /* On supprimer les préférences de type 2, 3, 4, 5 du user
        */
        $userRep->deleteUserDetailHasPreferenceByType($user->getId(), 2);
        $userRep->deleteUserDetailHasPreferenceByType($user->getId(), 3);
        $userRep->deleteUserDetailHasPreferenceByType($user->getId(), 4);
        $userRep->deleteUserDetailHasPreferenceByType($user->getId(), 5);
        
        foreach($activitiesTypes as $activitiesType) {
            $preference = $preferenceRep->findOneByCode(array($activitiesType));
            $user->getUserDetail()->addPreference($preference);
        }
        
        foreach($activitiesFrom as $activityFrom) {
            $preference = $preferenceRep->findOneByCode(array($activityFrom));
            $user->getUserDetail()->addPreference($preference);
        }
        
        foreach($sports as $sportId) {
            $preference = $preferenceRep->findOneByCode(array($sportId));
            var_dump($sportId);
            if (!isset($preference)) {
                //Nouveau sport détecté on doit l'ajouter dans la tables des préférences
                $preference = new \Ks\UserBundle\Entity\Preference();
                
                if ($sportId == "") {
                    $code = '';
                    $$description = 'Mes sports';
                }
                else {
                    $code = $sportId;
                    $$description = $sportRep->find($sportId)->getCodeSport();
                }
                $preference->setCode($code);
                $preference->setCodePreference($$description);
                $preference->setPreferenceType($preferenceTypeRep->find(5));
                $em->persist($preference);
            }
            $user->getUserDetail()->addPreference($preference);
        }
        
        $preference = $preferenceRep->findOneByCode(array($lastModified));
        $user->getUserDetail()->addPreference($preference);
        
        $em->flush();
                
        $responseDatas = array(
            'response' => true
        ); 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
}
