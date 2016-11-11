<?php
namespace Ks\ActivityBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Ks\ActivityBundle\Entity\Sport;
use Ks\ActivityBundle\Form\SportType;

class SportController extends Controller
{      
     
    /**
     * @Route("/getSportSessionForm/{sportId}", defaults={"clubId" = null, "tournamentId" = null})
     * @Route("/getSportSessionForm/{sportId}/{clubId}", defaults={"tournamentId" = null})
     * @Route("/getSportSessionForm/{sportId}/{clubId}/{tournamentId}", requirements={"sportId" = "\d+", "clubId" = "\d+", "tournamentId" = "\d+"}, name = "ksActivity_getSportSessionForm", options={"expose"=true} )
     */
    public function getSportSessionFormAction($sportId, $clubId, $tournamentId )
    {
        $em                         = $this->getDoctrine()->getEntityManager();
        $sportRep                   = $em->getRepository('KsActivityBundle:Sport');
        $clubRep                    = $em->getRepository('KsClubBundle:Club');
        $tournamentRep              = $em->getRepository('KsTournamentBundle:Tournament');
        $equipmentRep               = $em->getRepository('KsUserBundle:Equipment');
        $characterRep               = $em->getRepository('KsCanvasDrawingBundle:Character');
        $userHasSportFrequencyRep   = $em->getRepository('KsUserBundle:UserHasSportFrequency');
        $request                    = $this->getRequest();
        $user                       = $this->get('security.context')->getToken()->getUser();
        
        $parameters     = $request->request->all();
        
        $sport = $sportRep->find($sportId);
        
        if (!is_object($sport) ) {
            throw $this->createNotFoundException("Impossible de trouver le sport " . $sportId .".");
        }
        
        if( !empty( $clubId )) {
            $club = $clubRep->find( $clubId );
            if (!is_object( $club ) ) {
                throw $this->createNotFoundException("Impossible de trouver le club " . $clubId .".");
            }
        }
        
        if( !empty( $tournamentId )) {
            $tournament = $tournamentRep->find( $tournamentId );
            if (!is_object( $tournament ) ) {
                throw $this->createNotFoundException("Impossible de trouver le tournoi " . $tournamentId .".");
            }
        }
        
        switch($sport->getSportType()->getLabel()) {
            //case default:
            case "team_sport" :
                
                if( isset( $club ) && is_object( $club )) {
                    $teamSportSession = new \Ks\ActivityBundle\Entity\ActivitySessionTeamSport();
                    $teamSportSession->setClub( $club );
                    $teamSportSession->setSport( $sport );
                    
                    $form = $this->createForm(new \Ks\ActivityBundle\Form\ActivitySessionType($sport, null, $club), $teamSportSession)->createView();
                    
                } else {
                    $teamSportSession = new \Ks\ActivityBundle\Entity\ActivitySessionTeamSport($user);
                    $teamSportSession->setSport( $sport );
                    
                    if( isset( $tournament ) && is_object( $tournament ) ) {
                        $teamSportSession->setTournament( $tournament );
                        
                        if( $tournament->getClub() != null ) {
                            $teamSportSession->setClub( $tournament->getClub() );
                        }
                    }
                    
                    $form = $this->createForm(new \Ks\ActivityBundle\Form\ActivitySessionType($sport, $user), $teamSportSession)->createView();    
                }
                
                 break;
            
            case "endurance" :
                
                if( isset( $club ) && is_object( $club )) {
                    $enduranceSession = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth();
                    $enduranceSession->setClub( $club );
                    $enduranceSession->setSport( $sport );
                    
                    if( isset( $tournament ) && is_object( $tournament ) ) {
                        $enduranceSession->setTournament( $tournament );
                    }
                    
                    $form = $this->createForm(new \Ks\ActivityBundle\Form\ActivitySessionType($sport, null, $club), $enduranceSession)->createView();  
                    
                } else {
                    $enduranceSession = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceOnEarth( $user );
                    $enduranceSession->setSport( $sport );
                    
                    $form = $this->createForm(new \Ks\ActivityBundle\Form\ActivitySessionType($sport, $enduranceSession->getUser(), null), $enduranceSession)->createView();
                }
                 break;
                
                
            case "endurance_under_water" :
                
                if( isset( $club ) && is_object( $club )) {
                    $enduranceSession = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceUnderWater();
                    $enduranceSession->setClub( $club );
                    $enduranceSession->setSport( $sport );
                    
                    if( isset( $tournament ) && is_object( $tournament ) ) {
                        $enduranceSession->setTournament( $tournament );
                    }
                    
                    $form = $this->createForm(new \Ks\ActivityBundle\Form\ActivitySessionType($sport, null, $club), $enduranceSession)->createView();  
                    
                } else {
                    $enduranceSession = new \Ks\ActivityBundle\Entity\ActivitySessionEnduranceUnderWater( $user );
                    $enduranceSession->setSport( $sport );
                    $form = $this->createForm(new \Ks\ActivityBundle\Form\ActivitySessionType($sport, $user), $enduranceSession)->createView();   
                }
                
                 break;
                
            case "other" :

                if( isset( $club ) && is_object( $club )) {
                    $activitySession = new \Ks\ActivityBundle\Entity\ActivitySession();
                    $activitySession->setClub( $club );
                    $activitySession->setSport( $sport );
                    
                    if( isset( $tournament ) && is_object( $tournament ) ) {
                        $activitySession->setTournament( $tournament );
                    }
                    
                    $form = $this->createForm(new \Ks\ActivityBundle\Form\ActivitySessionType($sport, null, $club), $activitySession)->createView();  
                    
                } else {
                    $activitySession = new \Ks\ActivityBundle\Entity\ActivitySession( $user );
                    $activitySession->setSport( $sport );
                    $form = $this->createForm(new \Ks\ActivityBundle\Form\ActivitySessionType($sport, $user), $activitySession)->createView();   
                }
                
                 break;
        }
        
        //Récupération des équipments activés par défaut selon le sport sélectionné
        //FIXME : remplacer avec le findEquipments ci-dessous mais nécessite traitement sur _activitySessionForm.html.twig :(
        $equipmentsIds = $equipmentRep->getMyEquipmentsIdsByDefault( $user->getId(), $sport->getId() );
        
        $response = new Response(json_encode($equipmentsIds));
        $response->headers->set('Content-Type', 'application/json');
        
        //Récupération du personnage pour affichage sur le canvas
        $character = $characterRep->findOneCharacter(array(
            "userId" => $user->getId()
        ));
        
        //Récupération des équipments activés par défaut selon le sport sélectionné
        $selectedEquipments = $equipmentRep->findEquipments(array(
            "isByDefault"   => true,
            "sportId"       => $sport->getId(),
            "allEquipments" => false,
            "userId"        => $user->getId()
        ));
        
        //On récupère aussi tous les équipements pour le sport sélectionné pour permettre l'affichage dynamique du canvas
        $allEquipments = $equipmentRep->findEquipments(array(
            "sportId"       => $sport->getId(),
            "userId"        => $user->getId(),
            "allEquipments" => false
        ));

        //var_dump($this->var_to_js('selectedEquipments',$equipments));
        
        $userHasSportFrequencyType = $userHasSportFrequencyRep->findOneBy(array(
            "user"  => $user->getId(),
            "sport" => $sport->getId()
        ));
        $frequencyForm = $this->createForm(new \Ks\UserBundle\Form\UserHasSportFrequencyType(), $userHasSportFrequencyType)->createView();
        
        $coachingPlanForm = $this->createForm(new \Ks\CoachingBundle\Form\CoachingPlanEventsType($user, $sport))->createView();
        
        return $this->render('KsActivityBundle:Sport:_activitySessionForm.html.twig', array(
            'form'                  => $form,
            'frequencyForm'         => $frequencyForm,
            'coachingPlanForm'      => $coachingPlanForm,
            'equipmentsIds'         => $response,
            'selectedEquipments'    => $this->var_to_js('selectedEquipments', $selectedEquipments),
            'allEquipments'         => $this->var_to_js('allEquipments', $allEquipments),
            'character'             => $character,
            'sportId'               => $sportId,
            'eventId'               => isset($parameters["eventId"]) ? $parameters["eventId"] : -1,
            'clubId'                => $clubId,
            'sport_category_code'   => $sport->getSportType()->getLabel(),
            'sport_codeSport'       => $sport->getCodeSport()
        ));
               
    }
    
    /**
     * @Route("/getActivitySessionForm/{activityId}", defaults={"eventId" = "NC"})
     * @Route("/getActivitySessionForm/{activityId}/{eventId}", requirements={"offset" = "\d+"}, name = "ksActivity_getActivitySessionForm", options={"expose"=true} )
     */
    public function getActivitySessionFormAction($activityId,$eventId)
    {
        //
        $em                         = $this->getDoctrine()->getEntityManager();
        $activityRep                = $em->getRepository('KsActivityBundle:Activity');
        $eventRep                   = $em->getRepository('KsEventBundle:Event');
        $equipmentRep               = $em->getRepository('KsUserBundle:Equipment');
        $characterRep               = $em->getRepository('KsCanvasDrawingBundle:Character');
        $userHasSportFrequencyRep   = $em->getRepository('KsUserBundle:UserHasSportFrequency');
        $user                       = $this->get('security.context')->getToken()->getUser();
        
        $activity = $activityRep->find($activityId);

        if (!is_object($activity) ) {
            throw $this->createNotFoundException("Impossible de trouver l'activity " . $activityId .".");
        }
        
        $user = $activity->getUser();
        
        $sport = $activity->getSport();
        switch($sport->getSportType()->getLabel()) {
            case "team_sport" :
                $teamSportSession   = $activity;
                $coachingEvent = null;
                if (!is_null($teamSportSession->getEvent()) && $teamSportSession->getEvent()->getTypeEvent()->getId() == 5) $coachingEvent = $teamSportSession->getEvent();
                $form               = $this->createForm(
                    new \Ks\ActivityBundle\Form\ActivitySessionType($sport, $user, null, $coachingEvent),
                    $teamSportSession
                )->createView();
                
                break;
            
            case "endurance" :
                $enduranceSession   = $activity;
                $coachingEvent = null;
                if (!is_null($enduranceSession->getEvent()) && $enduranceSession->getEvent()->getTypeEvent()->getId() == 5) $coachingEvent = $enduranceSession->getEvent();
                $form               = $this->createForm(
                    new \Ks\ActivityBundle\Form\ActivitySessionType($sport, $user, null, $coachingEvent),
                    $enduranceSession
                )->createView();
                break;
                
            case "endurance_under_water" :
                $enduranceSession   = $activity;
                $coachingEvent = null;
                if (!is_null($enduranceSession->getEvent()) && $enduranceSession->getEvent()->getTypeEvent()->getId() == 5) $coachingEvent = $enduranceSession->getEvent();
                $form               = $this->createForm(
                    new \Ks\ActivityBundle\Form\ActivitySessionType($sport, $user, null, $coachingEvent),
                    $enduranceSession
                )->createView();
                
                break;
            
            case "other" :
                $activitySession    = $activity;
                $coachingEvent = null;
                if (!is_null($activitySession->getEvent()) && $activitySession->getEvent()->getTypeEvent()->getId() == 5) $coachingEvent = $activitySession->getEvent();
                $form               = $this->createForm(
                    new \Ks\ActivityBundle\Form\ActivitySessionType($sport, $user, null, $coachingEvent),
                    $activitySession
                )->createView();
                break;
        }
        //Récupération des équipments sauvegardés lors de la création de l'activité
        $equipmentsIds = $equipmentRep->getMyEquipmentsIdsByActivity( $user->getId(), $activityId );
        
        $response = new Response(json_encode($equipmentsIds));
        $response->headers->set('Content-Type', 'application/json');
        
        //Récupération du personnage pour affichage sur le canvas
        $character = $characterRep->findOneCharacter(array(
            "userId" => $user->getId()
        ));
        
        //Récupération des équipments activés par défaut selon le sport sélectionné
        $selectedEquipments = $equipmentRep->findEquipments(array(
            "isByDefault"   => true,
            "sportId"       => $sport->getId(),
            "allEquipments" => false,
            "userId"        => $user->getId()
        ));
        
        //On récupère aussi tous les équipements pour le sport sélectionné pour permettre l'affichage dynamique du canvas
        $allEquipments = $equipmentRep->findEquipments(array(
            "sportId"       => $sport->getId(),
            "userId"        => $user->getId(),
            "allEquipments" => false
        ));
        
        $userHasSportFrequencyType = $userHasSportFrequencyRep->findOneBy(array(
            "user"  => $user->getId(),
            "sport" => $sport->getId()
        ));
        $frequencyForm = $this->createForm(new \Ks\UserBundle\Form\UserHasSportFrequencyType(), $userHasSportFrequencyType)->createView();
        
        $eventId = null;
        if (!is_null($activity->getEvent()) && $activity->getEvent()->getTypeEvent()->getId() == 5) $eventId = $activity->getEvent()->getId();
        
        return $this->render('KsActivityBundle:Sport:_activitySessionForm.html.twig', array(
            'form'                  => $form,
            'eventId'               => $eventId,
            'frequencyForm'         => $frequencyForm,
            'equipmentsIds'         => $response,
            'selectedEquipments'    => $this->var_to_js('selectedEquipments', $selectedEquipments),
            'allEquipments'         => $this->var_to_js('allEquipments', $allEquipments),
            'character'             => $character,
            'activity'              => $activity,
            'sport_category_code'   => $sport->getSportType()->getLabel(),
            'sport_codeSport'       => $sport->getCodeSport(),
            'sportId'               => $activity->getSport()->getId()
        ));
    }
    
    /**
     * @Route("/loadSportChoiceForm/", name = "ksActivity_loadSportChoiceForm", options={"expose"=true} )
     */
    public function loadSportChoiceFormAction()
    {   
        $em             = $this->getDoctrine()->getEntityManager();
        $user           = $this->get('security.context')->getToken()->getUser();

        $responseDatas = array();
        
        $activitySession = new \Ks\ActivityBundle\Entity\ActivitySession($user);
        $activitySportChoiceForm = $this->createForm(new \Ks\ActivityBundle\Form\SportType(null), $activitySession);
        
        $responseDatas['activitySportChoiceForm_html'] = $this->render('KsActivityBundle:Sport:_sportChoiceForm.html.twig', array(
            'form'        => $activitySportChoiceForm->createView(),
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/all_sports", name = "ksAllSports" )
     */
    public function getAllSports() {
        
        $request = $this->container->get('request');
        
        if ($request->isXmlHttpRequest()) {
            
            $em = $this->getDoctrine()->getEntityManager();
            
            $sports = $em->getRepository('KsActivityBundle:Sport')->getSportsASC();
            $aSports = array();
            foreach($sports as $sport){
                $aSports[$sport->getId()] = $sport->getLabel();
            }

            $response = new Response(json_encode(array('sports' => $aSports)));
            
             //« Content-type » json pour le retour
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }
        
    }
    
    /**
     * @Route("/activitySessionForm", defaults={"activityId" = "new"})
     * @Route("/activitySessionForm/{activityId}/{sportId}/{eventId}/{isDone}", defaults={"sportId" = null, "eventId" = null, "isDone" = 1}, name = "ksSport_activitySessionForm", options={"expose"=true})
     */
    public function activitySessionFormAction($activityId, $eventId, $isDone) {
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $sportRep           = $em->getRepository('KsActivityBundle:Sport');
        $userHasServicesRep = $em->getRepository('KsUserBundle:UserHasServices');
        
        $user               = $this->get('security.context')->getToken()->getUser();
        
        if ($activityId == 'forceNew' || $activityId == 'new' && isset($eventId) && isset($isDone) && !is_null($eventId) && !is_null($isDone) || $activityId != 'new') {
            //on ne fait rien si l'utilisateur a un service et force la création manuelle, pareil s'il publie une activité à partir de son tableau de bord et donc liée à une de ses séances,
            //et enfin pareil aussi s'il tente de modifier une de ses activités
        }
        else {
            //Si un service de synchro est actif on débranche sur l'écran de synchro uniquement dans le cas d'une publication manuelle d'activité (hors lien avec plan)
            $userServices = $userHasServicesRep->findBy(array("user" => $user->getId(), "is_active" => true));
            $serviceId =null;
            foreach ($userServices as $userService) {
                if ($userService->getService()->getName() == 2) $serviceId =null; //On ne synchronise pas manuellement son agenda avec Google Agenda
                else {
                    $serviceId = $userService->getService()->getId();
                    $serviceName = $userService->getService()->getName();
                }
            }
            if (!is_null($serviceId)) return new RedirectResponse($this->container->get('router')->generate('ksActivity_syncFromList'));
        }
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'publishActivity');
        
        $activitySession = new \Ks\ActivityBundle\Entity\ActivitySession($user);
        $activitySportChoiceForm = $this->createForm(new \Ks\ActivityBundle\Form\SportType(null), $activitySession);
        
        $activity = $activityRep->find($activityId);
  
        return $this->render('KsActivityBundle:Sport:activitySessionForm.html.twig', array(
            'activitySportChoiceForm' => $activitySportChoiceForm->createView(),
            'activity'    => $activity,
            'eventId'     => $eventId,
            'isDone'      => $isDone
        )); 
    }
    
    /**
     * @Route("/customSelectSports", defaults={"multiple" = "0"} )
     * @Route("/customSelectSports/{multiple}", name = "ksSport_customSelectSports", options={"expose"=true})
     */
    public function customSelectSportsAction( $multiple, $context=null ) {
        $securityContext    = $this->container->get('security.context');
        
        $em             = $this->getDoctrine()->getEntityManager();
        $sportRep    = $em->getRepository('KsActivityBundle:Sport');
        
        
        $userSports = array();
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user           = $this->get('security.context')->getToken()->getUser();
            if ( $user->getUserDetail() != null ) {
                foreach( $user->getUserDetail()->getSports() as $sport ) {
                    $userSports[] = $sport->getCodeSport();
                }
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
        
        return $this->render('KsActivityBundle:Sport:_customSelectSports.html.twig', array(
            'sportsGroups'  => $aSports,
            "multiple"      => $multiple,
            "context"       => $context
        )); 
    }
    
    /**
     * @Route("/customSelectSportsNotAll", defaults={"multiple" = "0"} )
     * @Route("/customSelectSportsNotAll/{multiple}", name = "ksSport_customSelectSportsNotAll", options={"expose"=true})
     */
    public function customSelectSportsNotAllAction( $multiple ) {
        $securityContext    = $this->container->get('security.context');
        
        $em             = $this->getDoctrine()->getEntityManager();
        $sportRep    = $em->getRepository('KsActivityBundle:Sport');
        
        
        $userSports = array();
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user           = $this->get('security.context')->getToken()->getUser();
            if ( $user->getUserDetail() != null ) {
                foreach( $user->getUserDetail()->getSports() as $sport ) {
                    $userSports[] = $sport->getCodeSport();
                }
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
        
        return $this->render('KsActivityBundle:Sport:_customSelectSportsNotAll.html.twig', array(
            'sportsGroups'     => $aSports,
            "multiple"          => $multiple
        )); 
    }
    
    public function customSelectSportsGroundsAction( $sportId ) {
        $em             = $this->getDoctrine()->getEntityManager();
        $sportRep    = $em->getRepository('KsActivityBundle:Sport');
        
        $sportsGrounds = $sportRep->getSportsGroundsBySport( $sportId );
        
        
        return $this->render('KsActivityBundle:Sport:_customSelectSportsGrounds.html.twig', array(
            'sportsGrounds'     => $sportsGrounds,
        )); 
    }
    
    public function html_to_js_var($t){
        return str_replace('</script>','<\/script>',addslashes(str_replace("\r",'',str_replace("\n","",$t))));
    }
    
    public function var_to_js($jsname,$a){
        $ret='';
        if (is_array($a)) {
            $ret.=$jsname.'= new Array();
            ';

            foreach ($a as $k => $a) {
                if (is_int($k) || is_integer($k))
                    $ret.= $this->var_to_js($jsname.'['.$k.']',$a);
                else
                    $ret.= $this->var_to_js($jsname."['".$k."']",$a);
            }

        }
        elseif (is_bool($a)) {
            $v=$a ? "true" : "false";
            $ret.=$jsname.'='.$v.';
            ';
        }
        elseif (is_int($a) || is_integer($a) || is_double($a) || is_float($a)) {
           $ret.=$jsname.'='.$a.';
            ';
        }
        elseif (is_string($a)) {
           $ret.=$jsname.'=\''.$this->html_to_js_var($a).'\';
            ';
        }
        return $ret;
    }
}
