<?php

namespace Ks\CoachingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;


class CoachingController extends Controller
{

    /**
     * @Route("/getSessionsFromCategory/{clubId}", name = "ksCoaching_getSessionsFromCategory", requirements={"clubId" = "\d+"}, options={"expose"=true} )
     */
    public function getSessionsFromCategoryAction($clubId)
    {
        $request                = $this->get('request');
        $em                     = $this->getDoctrine()->getEntityManager();
        $clubRep                = $em->getRepository('KsClubBundle:Club');
        $coachingSessionRep       = $em->getRepository('KsCoachingBundle:CoachingSession');
        $coachingCategoryRep    = $em->getRepository('KsCoachingBundle:CoachingCategory');
        $articleRep             = $em->getRepository('KsActivityBundle:Article');
        $articleTagRep          = $em->getRepository('KsActivityBundle:ArticleTag');
        $securityContext        = $this->container->get('security.context');
        $userRep                = $em->getRepository('KsUserBundle:User');
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user       = $this->container->get('security.context')->getToken()->getUser();
        }
        else {
            //Visitor
            $user = $userRep->find(1);
        }
        
        $parameters = $request->request->all();
        $categoryId  = isset( $parameters['categoryId'] ) ? $parameters['categoryId'] : -1;
        $category = $coachingCategoryRep->find($categoryId);
        
        if (isset($category) && $category != null && $category->getIsCompetition()) {
            //FMO : on recherche toutes les compétitions disponibles dans le futur
            $result = $articleRep->findCompetitionsFromNow();
            $coachingSessions = array();
            foreach($result as $coachingSession) {
                $startDate = new \DateTime($coachingSession['startDate']);
                $coachingSessions[] = array(
                    'id' => $coachingSession['id'], 
                    'name' => $coachingSession['name'], //." (".$startDate->format("d-m-Y").")", 
                    'detail' => null,
                    'date' => $startDate->format("d/m/Y"),
                    'sport'   => $coachingSession['sport_id'], 
                    'hrCoeffMin' => null,
                    'hrCoeffMax' => null,
                    'hrType' => null,
                    'intervalDuration' => null,
                    'intervalDistance' => null,
                    'VMACoeff'  => null,
                    'distanceMin' => $coachingSession['distance'],
                    'distanceMax' => $coachingSession['distance'],
                    'durationMin' => $coachingSession['duration'] == null ? '' : $coachingSession['duration']->format("H:i"),
                    'durationMax' => $coachingSession['duration'] == null ? '' : $coachingSession['duration']->format("H:i"),
                    'elevationGainMin' => $coachingSession['elevationGain'],
                    'elevationGainMax' => $coachingSession['elevationGain'],
                    'elevationLostMin' => $coachingSession['elevationLost'],
                    'elevationLostMax' => $coachingSession['elevationLost'],
                    'speedAverageMin' => null,
                    'speedAverageMax' => null,
                    'powMin' => null,
                    'powMax' => null,
                );
            }
        }
        else {
            if ($clubId != 0) {
                $club = $clubRep->find($clubId);
                $result = $coachingSessionRep->findBy(array('club' => $club->getId(), 'category' => $category->getId()));
            }
            else {
                //$result = $coachingSessionRep->findBy(array('user' => $user->getId(), 'category' => $category->getId())); FMO : pour récupérer les séances partagées, on enlève le lien user
                $result = $coachingSessionRep->findBy(array('category' => $category->getId()));
            }
            $coachingSessions = array();
            foreach($result as $coachingSession) {
                $coachingSessions[] = array(
                    'id' => $coachingSession->getId(), 
                    'name' => $coachingSession->getName(), 
                    'detail' => $coachingSession->getDetail(),
                    'hrCoeffMin' => $coachingSession->getHRCoeffMin(),
                    'hrCoeffMax' => $coachingSession->getHRCoeffMax(),
                    'hrType' => $coachingSession->getHRType(),
                    'intervalDuration' => $coachingSession->getIntervalDuration() == null ? '' : $coachingSession->getIntervalDuration()->format("H:i:s"),
                    'intervalDistance' => $coachingSession->getIntervalDistance(),
                    'VMACoeff'  => $coachingSession->getVMACoeff(),
                    'distanceMin' => $coachingSession->getDistanceMin(),
                    'distanceMax' => $coachingSession->getDistanceMax(),
                    'durationMin' => $coachingSession->getDurationMin() == null ? '' : $coachingSession->getDurationMin()->format("H:i"),
                    'durationMax' => $coachingSession->getDurationMax() == null ? '' : $coachingSession->getDurationMax()->format("H:i"),
                    'elevationGainMin' => $coachingSession->getElevationGainMin(),
                    'elevationGainMax' => $coachingSession->getElevationGainMax(),
                    'elevationLostMin' => $coachingSession->getElevationLostMin(),
                    'elevationLostMax' => $coachingSession->getElevationLostMax(),
                    'speedAverageMin' => $coachingSession->getSpeedAverageMin(),
                    'speedAverageMax' => $coachingSession->getSpeedAverageMax(),
                    'powMin' => $coachingSession->getPowMin(),
                    'powMax' => $coachingSession->getPowMax()
                    );
            }
        }
        
        $responseDatas['publishResponse'] = 1;
        $responseDatas['coachingSessions'] = $coachingSessions;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{clubId}/createNewPlan", name = "ksCoaching_createNewPlan", requirements={"clubId" = "\d+"}, options={"expose"=true} )
     */
    public function createNewPlanAction($clubId)
    {
        $request                = $this->get('request');
        $em                     = $this->getDoctrine()->getEntityManager();
        $clubRep                = $em->getRepository('KsClubBundle:Club');
        $userRep                = $em->getRepository('KsUserBundle:User');
        $coachingPlanTypeRep    = $em->getRepository('KsCoachingBundle:CoachingPlanType');
        $user                   = $this->get('security.context')->getToken()->getUser();
        
        $parameters = $request->request->all();
        
        $newPlan    = isset( $parameters['newPlan'] ) && $parameters['newPlan'][0] != "" ? $parameters['newPlan'] : array();
        $memberId   = isset( $parameters['memberId'] ) ? $parameters['memberId'] : null;
        
        if ($clubId != 0) $club = $clubRep->find($clubId);
        
        $coachingPlan = new \Ks\CoachingBundle\Entity\CoachingPlan();
        $coachingPlan->setName($newPlan);
        if ($clubId != 0) {
            $coachingPlan->setCoachingPlanType($coachingPlanTypeRep->find(2));
            $coachingPlan->setClub($club);
            $coachingPlan->setUser($userRep->find($memberId));
        }
        else {
            $coachingPlan->setCoachingPlanType($coachingPlanTypeRep->find(1));
            $coachingPlan->setUser($user);

        }
        $coachingPlan->setColor("");
        $em->persist( $coachingPlan );
        $em->flush();
        
        $responseDatas['publishResponse'] = 1;
        $responseDatas['newPlanId'] = $coachingPlan->getId();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{planId}/updateSelectedPlan", name = "ksCoaching_updateSelectedPlan", requirements={"planId" = "\d+"}, options={"expose"=true} )
     */
    public function updateSelectedPlanAction($planId)
    {
        $request                = $this->get('request');
        $em                     = $this->getDoctrine()->getEntityManager();
        $clubRep                = $em->getRepository('KsClubBundle:Club');
        $userRep                = $em->getRepository('KsUserBundle:User');
        $coachingPlanRep        = $em->getRepository('KsCoachingBundle:CoachingPlan');
        $user                   = $this->get('security.context')->getToken()->getUser();
        
        $parameters = $request->request->all();
        
        $newLabel    = isset( $parameters['newLabel'] ) && $parameters['newLabel'][0] != "" ? $parameters['newLabel'] : array();
        
        $coachingPlan = $coachingPlanRep->find($planId);
        $coachingPlan->setName($newLabel);
        $em->persist( $coachingPlan );
        $em->flush();
        
        $responseDatas['publishResponse'] = 1;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * 
     * @Route("/sharePlan/{planId}", requirements={"clubId" = "\d+"}, name = "ksCoaching_sharePlan", options={"expose"=true} )
     * @param int $planId 
     */
    public function sharePlanAction($planId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $coachingPlanRep        = $em->getRepository('KsCoachingBundle:CoachingPlan');
        $coachingPlanTypeRep    = $em->getRepository('KsCoachingBundle:CoachingPlanType');
        $user                   = $this->get('security.context')->getToken()->getUser();
        
        if (!is_object($user) ) {
            throw new AccessDeniedException("Vous devez être identifié sur le site !");
        }
        
        $responseDatas = array();
        
        $plan = $coachingPlanRep->find($planId);
        
        $coachingPlanType = $coachingPlanTypeRep->find(3);
        
        $plan->setCoachingPlanType($coachingPlanType);
        
        $em->persist($plan);
        $em->flush();
        
        $responseDatas["response"] = 1;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/isLengthPlanValid/{planId}", requirements={"clubId" = "\d+"}, name = "ksCoaching_isLengthPlanValid", options={"expose"=true} )
     * @param int $planId 
     */
    public function isLengthPlanValidAction($planId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $coachingPlanRep        = $em->getRepository('KsCoachingBundle:CoachingPlan');
        $coachingPlanTypeRep    = $em->getRepository('KsCoachingBundle:CoachingPlanType');
        $user                   = $this->get('security.context')->getToken()->getUser();
        $clubRep                = $em->getRepository('KsClubBundle:Club');
        $preferenceRep          = $em->getRepository('KsUserBundle:Preference');
        
        if (!is_object($user) ) {
            throw new AccessDeniedException("Vous devez être identifié sur le site !");
        }
        
        $responseDatas = array();
        
        $plan = $coachingPlanRep->find($planId);
        
        $days = $clubRep->getCoachingPlanLength($planId);
        
        $weeks = $preferenceRep->findOneBy(array('code' => "minLengthPlanPremium"))->getVal1();
        
        if ($days >= ($weeks * 7 -1)) $responseDatas["response"] = 1;
        else $responseDatas["response"] = -1;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/deletePlan/{planId}", requirements={"clubId" = "\d+"}, name = "ksCoaching_deletePlan", options={"expose"=true} )
     * @param int $planId 
     */
    public function deletePlanAction($planId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $articleRep             = $em->getRepository('KsActivityBundle:Article');
        $activityRep            = $em->getRepository('KsActivityBundle:Activity');
        $coachingPlanRep        = $em->getRepository('KsCoachingBundle:CoachingPlan');
        $eventRep               = $em->getRepository('KsEventBundle:Event');
        $agendaRep              = $em->getRepository('KsAgendaBundle:Agenda');
        $user                   = $this->get('security.context')->getToken()->getUser();
        
        if (!is_object($user) ) {
            throw new AccessDeniedException("Vous devez être identifié sur le site !");
        }
        
        $responseDatas = array();
        
        $plan = $coachingPlanRep->find($planId);
        $isShared = $plan->getCoachingPlanType()->getCode()=="shared";
        $importedPlans = $coachingPlanRep->findByFather($planId);
        
        if (count($importedPlans) > 0) {
            //Cas d'un plan partagé donc lié à un article type plan
            $responseDatas["response"] = -1;
        }
        else {
            $eventsNotLinked = $agendaRep->findAgendaEvents(array(
                "planId"                    => $plan->getId(),
                "getUserSessionForFlatView" => false,
                "notLinkedToActivity"       => true
            ), $this->get('translator'));

            $eventsAll = $agendaRep->findAgendaEvents(array(
                "planId"                    => $plan->getId(),
                "getUserSessionForFlatView" => false,
            ), $this->get('translator'));

            if (count($eventsAll) == count($eventsNotLinked)) {
                foreach ($eventsNotLinked as $event) {
                    $eventEntity = $eventRep->find($event['id']);
                    $em->remove($eventEntity);
                    $em->flush();
                }
                
                if ($isShared) {
                    $activityDatas = $activityRep->findActivities(array(
                        'coachingPlanId' => $planId
                    ));
                    //var_dump($activityDatas[0]['activity']['id']);
                    $activity = $activityRep->find($activityDatas[0]['activity']['id']);
                    if (isset($activity) && !is_null($activity)) {
                        $em->remove($activity);
                        $em->flush();
                    }
                }

                $em->remove($plan);
                $em->flush();
                $responseDatas["response"] = 1;
            }
            else {
                $responseDatas["response"] = -2;
            }
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * @Route("/{clubId}/createNewCategory", name = "ksCoaching_createNewCategory", requirements={"clubId" = "\d+"}, options={"expose"=true} )
     */
    public function createNewCategoryAction($clubId)
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $user               = $this->get('security.context')->getToken()->getUser();
        
        $parameters = $request->request->all();
        
        $newCategory    = isset( $parameters['newCategory'] ) && $parameters['newCategory'][0] != "" ? $parameters['newCategory'] : array();
        
        if ($clubId != 0) $club = $clubRep->find($clubId);
        
        $coachingCategory = new \Ks\CoachingBundle\Entity\CoachingCategory();
        $coachingCategory->setName($newCategory);
        if ($clubId != 0) $coachingCategory->setClub($club);
        else $coachingCategory->setUser($user);
        $coachingCategory->setColor("");
        $em->persist( $coachingCategory );
        $em->flush();
        
        $responseDatas['publishResponse'] = 1;
        $responseDatas['value'] = $coachingCategory->getId();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{categoryId}/enableCategory", name = "ksCoaching_enableCategory", requirements={"categoryId" = "\d+"}, options={"expose"=true} )
     */
    public function enableCategoryAction($categoryId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $coachingCategoryRep    = $em->getRepository('KsCoachingBundle:CoachingCategory');
        
        $coachingCategory = $coachingCategoryRep->find($categoryId);
        $coachingCategory->setIsEnabled(true);
        $em->flush();
        $responseDatas['publishResponse'] = 1;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{categoryId}/editCategory", name = "ksCoaching_editCategory", requirements={"categoryId" = "\d+"}, options={"expose"=true} )
     */
    public function editCategoryAction($categoryId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $coachingCategoryRep    = $em->getRepository('KsCoachingBundle:CoachingCategory');
        $request            = $this->get('request');
        
        $parameters = $request->request->all();
        
        $editCategory    = isset( $parameters['editCategory'] ) && $parameters['editCategory'][0] != "" ? $parameters['editCategory'] : array();
        
        $coachingCategory = $coachingCategoryRep->find($categoryId);
        $coachingCategory->setName($editCategory);
        $em->flush();
        $responseDatas['publishResponse'] = 1;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{categoryId}/disableCategory", name = "ksCoaching_disableCategory", requirements={"categoryId" = "\d+"}, options={"expose"=true} )
     */
    public function disableCategoryAction($categoryId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $eventRep               = $em->getRepository('KsEventBundle:Event');
        $coachingCategoryRep    = $em->getRepository('KsCoachingBundle:CoachingCategory');
        $coachingSessionRep       = $em->getRepository('KsCoachingBundle:CoachingSession');
        
        $events = $eventRep->findBy(array('coachingCategory' => $categoryId));
        $coachingSessions = $coachingSessionRep->findBy(array('category' => $categoryId));
        $coachingCategory = $coachingCategoryRep->find($categoryId);
        $coachingCategory->setIsEnabled(false);
        $em->flush();
        $responseDatas['publishResponse'] = 1;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{categoryId}/deleteCategory", name = "ksCoaching_deleteCategory", requirements={"categoryId" = "\d+"}, options={"expose"=true} )
     */
    public function deleteCategoryAction($categoryId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $eventRep               = $em->getRepository('KsEventBundle:Event');
        $coachingCategoryRep    = $em->getRepository('KsCoachingBundle:CoachingCategory');
        $coachingSessionRep       = $em->getRepository('KsCoachingBundle:CoachingSession');
        
        $events = $eventRep->findBy(array('coachingCategory' => $categoryId));
        $coachingSessions = $coachingSessionRep->findBy(array('category' => $categoryId));
        if (count($events) == 0 && count($coachingSessions) == 0) {
            $coachingCategory = $coachingCategoryRep->find($categoryId);
            $em->remove( $coachingCategory );
            $em->flush();
            $responseDatas['publishResponse'] = 1;
        }
        else {
            if (count($events) != 0) $responseDatas['publishResponse'] = -1;
            if (count($coachingSessions) != 0) $responseDatas['publishResponse'] = -2;
        }
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/disableMember/{clubId}/{userId}", name = "ksCoaching_disableMember", requirements={"clubId" = "\d+", "userId" = "\d+"}, options={"expose"=true} )
     */
    public function disableMemberAction($clubId, $userId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $eventRep               = $em->getRepository('KsEventBundle:Event');
        $clubHasUsersRep        = $em->getRepository('KsClubBundle:ClubHasUsers');
        
        $clubHasUsers = $clubHasUsersRep->findOneBy( array(
            "club" => $clubId,
            "user" => $userId
        ));
        
        if(is_object($clubHasUsers)) {
            $clubHasUsers->setIsEnabled(false);
            $em->flush();
            $responseDatas['publishResponse'] = 1;
        }
        else $responseDatas['publishResponse'] = -1;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/enableMember/{clubId}/{userId}", name = "ksCoaching_enableMember", requirements={"clubId" = "\d+", "userId" = "\d+"}, options={"expose"=true} )
     */
    public function enableMemberAction($clubId, $userId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $eventRep               = $em->getRepository('KsEventBundle:Event');
        $clubHasUsersRep        = $em->getRepository('KsClubBundle:ClubHasUsers');
        
        $clubHasUsers = $clubHasUsersRep->findOneBy( array(
            "club" => $clubId,
            "user" => $userId
        ));
        
        if(is_object($clubHasUsers)) {
            $clubHasUsers->setIsEnabled(true);
            $em->flush();
            $responseDatas['publishResponse'] = 1;
        }
        else $responseDatas['publishResponse'] = -1;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    
    /**
     * @Route("/{clubId}/createNewSession", name = "ksCoaching_createNewSession", requirements={"clubId" = "\d+"}, options={"expose"=true} )
     */
    public function createNewSessionAction($clubId)
    {
        $request                = $this->get('request');
        $em                     = $this->getDoctrine()->getEntityManager();
        $clubRep                = $em->getRepository('KsClubBundle:Club');
        $coachingCategoryRep    = $em->getRepository('KsCoachingBundle:CoachingCategory');
        $user                   = $this->get('security.context')->getToken()->getUser();
        
        $parameters = $request->request->all();
        
        $newSession    = isset( $parameters['newSession'] ) && $parameters['newSession'] != "" ? $parameters['newSession'] : null;
        $newDetail   = isset( $parameters['newDetail'] ) && $parameters['newDetail'] != "" ? $parameters['newDetail'] : null;
        $categoryId  = isset( $parameters['categoryId'] ) && $parameters['categoryId'] != "" ? $parameters['categoryId'] : null;
        $hrCoeffMin  = isset( $parameters['hrCoeffMin'] ) && $parameters['hrCoeffMin'] != "" ? $parameters['hrCoeffMin'] : null;
        $hrCoeffMax  = isset( $parameters['hrCoeffMax'] ) && $parameters['hrCoeffMax'] != "" ? $parameters['hrCoeffMax'] : null;
        $hrType  = isset( $parameters['hrType'] ) && $parameters['hrType'] != "" ? $parameters['hrType'] : null;
        $intervalDuration  = isset( $parameters['intervalDuration'] ) && $parameters['intervalDuration'] != "" ? $parameters['intervalDuration'] : null;
        $intervalDistance  = isset( $parameters['intervalDistance'] ) && $parameters['intervalDistance'] != "" ? $parameters['intervalDistance'] : null;
        $VMACoeff  = isset( $parameters['VMACoeff'] ) && $parameters['VMACoeff'] != "" ? $parameters['VMACoeff'] : null;
        $durationMin  = isset( $parameters['durationMin'] ) && $parameters['durationMin'] != "" ? $parameters['durationMin'] : null;
        $durationMax  = isset( $parameters['durationMax'] ) && $parameters['durationMax'] != "" ? $parameters['durationMax'] : null;
        $distanceMin  = isset( $parameters['distanceMin'] ) && $parameters['distanceMin'] != "" ? $parameters['distanceMin'] : null;
        $distanceMax  = isset( $parameters['distanceMax'] ) && $parameters['distanceMax'] != "" ? $parameters['distanceMax'] : null;
        $elevationGainMin  = isset( $parameters['elevationGainMin'] ) && $parameters['elevationGainMin'] != "" ? $parameters['elevationGainMin'] : null;
        $elevationGainMax  = isset( $parameters['elevationGainMax'] ) && $parameters['elevationGainMax'] != "" ? $parameters['elevationGainMax'] : null;
        $elevationLostMin  = isset( $parameters['elevationLostMin'] ) && $parameters['elevationLostMin'] != "" ? $parameters['elevationLostMin'] : null;
        $elevationLostMax  = isset( $parameters['elevationLostMax'] ) && $parameters['elevationLostMax'] != "" ? $parameters['elevationLostMax'] : null;
        $speedAverageMin  = isset( $parameters['speedAverageMin'] ) && $parameters['speedAverageMin'] != "" ? $parameters['speedAverageMin'] : null;
        $speedAverageMax  = isset( $parameters['speedAverageMax'] ) && $parameters['speedAverageMax'] != "" ? $parameters['speedAverageMax'] : null;
        $powMin  = isset( $parameters['powMin'] ) && $parameters['powMin'] != "" ? $parameters['powMin'] : null;
        $powMax  = isset( $parameters['powMax'] ) && $parameters['powMax'] != "" ? $parameters['powMax'] : null;
        
        $category = $coachingCategoryRep->find($categoryId);
        
        if ($clubId != 0) $club = $clubRep->find($clubId);
        
        if (!$user->getIsAllowedPackPremium() && is_null($durationMin) && is_null($durationMax)) {
            //Anciennement : pour les sportifs premium qui souhaitent se créer des catégories/séances sans suivre de plan pas besoin d'avoir au moins une de ses données
            $responseDatas['publishResponse'] = -1;
        } 
        else {
            $coachingSession = new \Ks\CoachingBundle\Entity\CoachingSession();
            $coachingSession->setName($newSession);
            $coachingSession->setDetail($newDetail);
            $coachingSession->setCategory($category);
            $coachingSession->setHrCoeffMin($hrCoeffMin);
            $coachingSession->setHrCoeffMax($hrCoeffMax);
            $coachingSession->setHrType($hrType);
            if ($intervalDuration == null) $coachingSession->setIntervalDuration(null); else $coachingSession->setIntervalDuration(new \DateTime(date("H:i", strtotime($intervalDuration))));
            $coachingSession->setIntervalDistance($intervalDistance);
            $coachingSession->setVMACoeff($VMACoeff);
            if ($durationMin == null) $coachingSession->setDurationMin(null); else $coachingSession->setDurationMin(new \DateTime(date("H:i", strtotime($durationMin))));
            if ($durationMax == null) $coachingSession->setDurationMax(null); else $coachingSession->setDurationMax(new \DateTime(date("H:i", strtotime($durationMax))));
            $coachingSession->setDistanceMin($distanceMin);
            $coachingSession->setDistanceMax($distanceMax);
            $coachingSession->setElevationGainMin($elevationGainMin);
            $coachingSession->setElevationGainMax($elevationGainMax);
            $coachingSession->setElevationLostMin($elevationLostMin);
            $coachingSession->setElevationLostMax($elevationLostMax);
            $coachingSession->setSpeedAverageMin($speedAverageMin);
            $coachingSession->setSpeedAverageMax($speedAverageMax);
            $coachingSession->setPowMin($powMin);
            $coachingSession->setPowMax($powMax);
            
            if ($clubId != 0) $coachingSession->setClub($club);
            else $coachingSession->setUser($user);
            $coachingSession->setColor("");
            $em->persist( $coachingSession );
            $em->flush();

            $responseDatas['publishResponse'] = 1;
            $responseDatas['value'] = $coachingSession->getId();
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{clubId}/updateSession", name = "ksCoaching_updateSession", requirements={"clubId" = "\d+"}, options={"expose"=true} )
     */
    public function updateSessionAction($clubId)
    {
        $request                = $this->get('request');
        $em                     = $this->getDoctrine()->getEntityManager();
        $clubRep                = $em->getRepository('KsClubBundle:Club');
        $coachingCategoryRep    = $em->getRepository('KsCoachingBundle:CoachingCategory');
        $coachingSessionRep       = $em->getRepository('KsCoachingBundle:CoachingSession');
        $user                   = $this->get('security.context')->getToken()->getUser();
        
        $parameters = $request->request->all();
        
        $sessionId    = isset( $parameters['sessionId'] ) && $parameters['sessionId'] != "" ? $parameters['sessionId'] : null;
        $newSession   = isset( $parameters['newSession'] ) && $parameters['newSession'] != "" ? $parameters['newSession'] : null;
        $detail   = isset( $parameters['detail'] ) && $parameters['detail'] != "" ? $parameters['detail'] : null;
        $hrCoeffMin  = isset( $parameters['hrCoeffMin'] ) && $parameters['hrCoeffMin'] != "" ? $parameters['hrCoeffMin'] : null;
        $hrCoeffMax  = isset( $parameters['hrCoeffMax'] ) && $parameters['hrCoeffMax'] != "" ? $parameters['hrCoeffMax'] : null;
        $hrType  = isset( $parameters['hrType'] ) && $parameters['hrType'] != "" ? $parameters['hrType'] : null;
        $intervalDuration  = isset( $parameters['intervalDuration'] ) && $parameters['intervalDuration'] != "" ? $parameters['intervalDuration'] : null;
        $intervalDistance  = isset( $parameters['intervalDistance'] ) && $parameters['intervalDistance'] != "" ? $parameters['intervalDistance'] : null;
        $VMACoeff  = isset( $parameters['VMACoeff'] ) && $parameters['VMACoeff'] != "" ? $parameters['VMACoeff'] : null;
        $durationMin  = isset( $parameters['durationMin'] ) && $parameters['durationMin'] != "" ? $parameters['durationMin'] : null;
        $durationMax  = isset( $parameters['durationMax'] ) && $parameters['durationMax'] != "" ? $parameters['durationMax'] : null;
        $distanceMin  = isset( $parameters['distanceMin'] ) && $parameters['distanceMin'] != "" ? $parameters['distanceMin'] : null;
        $distanceMax  = isset( $parameters['distanceMax'] ) && $parameters['distanceMax'] != "" ? $parameters['distanceMax'] : null;
        $elevationGainMin  = isset( $parameters['elevationGainMin'] ) && $parameters['elevationGainMin'] != "" ? $parameters['elevationGainMin'] : null;
        $elevationGainMax  = isset( $parameters['elevationGainMax'] ) && $parameters['elevationGainMax'] != "" ? $parameters['elevationGainMax'] : null;
        $elevationLostMin  = isset( $parameters['elevationLostMin'] ) && $parameters['elevationLostMin'] != "" ? $parameters['elevationLostMin'] : null;
        $elevationLostMax  = isset( $parameters['elevationLostMax'] ) && $parameters['elevationLostMax'] != "" ? $parameters['elevationLostMax'] : null;
        $speedAverageMin  = isset( $parameters['speedAverageMin'] ) && $parameters['speedAverageMin'] != "" ? $parameters['speedAverageMin'] : null;
        $speedAverageMax  = isset( $parameters['speedAverageMax'] ) && $parameters['speedAverageMax'] != "" ? $parameters['speedAverageMax'] : null;
        $powMin  = isset( $parameters['powMin'] ) && $parameters['powMin'] != "" ? $parameters['powMin'] : null;
        $powMax  = isset( $parameters['powMax'] ) && $parameters['powMax'] != "" ? $parameters['powMax'] : null;
        
        if (!$user->getIsAllowedPackPremium() && is_null($durationMin) && is_null($durationMax)) {
            //Anciennement : pour les sportifs premium qui souhaitent se créer des catégories/séances sans suivre de plan pas besoin d'avoir au moins une de ses données
            $responseDatas['publishResponse'] = -1;
        } 
        else if (! is_null($sessionId)) {
            $coachingSession = $coachingSessionRep->find($sessionId);
            if (! is_null($newSession)) $coachingSession->setName($newSession);
            $coachingSession->setDetail($detail);
            $coachingSession->setHrCoeffMin($hrCoeffMin);
            $coachingSession->setHrCoeffMax($hrCoeffMax);
            $coachingSession->setHrType($hrType);
            if ($intervalDuration == null) $coachingSession->setIntervalDuration(null); else $coachingSession->setIntervalDuration(new \DateTime(date("H:i:s", strtotime($intervalDuration))));
            $coachingSession->setIntervalDistance($intervalDistance);
            $coachingSession->setVMACoeff($VMACoeff);
            if ($durationMin == null) $coachingSession->setDurationMin(null); else $coachingSession->setDurationMin(new \DateTime(date("H:i", strtotime($durationMin))));
            if ($durationMax == null) $coachingSession->setDurationMax(null); else $coachingSession->setDurationMax(new \DateTime(date("H:i", strtotime($durationMax))));
            $coachingSession->setDistanceMin($distanceMin);
            $coachingSession->setDistanceMax($distanceMax);
            $coachingSession->setElevationGainMin($elevationGainMin);
            $coachingSession->setElevationGainMax($elevationGainMax);
            $coachingSession->setElevationLostMin($elevationLostMin);
            $coachingSession->setElevationLostMax($elevationLostMax);
            $coachingSession->setSpeedAverageMin($speedAverageMin);
            $coachingSession->setSpeedAverageMax($speedAverageMax);
            $coachingSession->setPowMin($powMin);
            $coachingSession->setPowMax($powMax);
            
            $em->persist( $coachingSession );
            $em->flush();

            $responseDatas['publishResponse'] = 1;
            $responseDatas['value'] = $coachingSession->getId();
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{sessionId}/deleteSession", name = "ksCoaching_deleteSession", requirements={"sessionId" = "\d+"}, options={"expose"=true} )
     */
    public function deleteSessionAction($sessionId)
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $eventRep           = $em->getRepository('KsEventBundle:Event');
        $coachingSessionRep    = $em->getRepository('KsCoachingBundle:CoachingSession');
        
        $events = $eventRep->findBy(array('coachingSession' => $sessionId));
        if (count($events) == 0) {
            $coachingSession = $coachingSessionRep->find($sessionId);
            $em->remove( $coachingSession );
            $em->flush();
            $responseDatas['publishResponse'] = 1;
        }
        else {
            $responseDatas['publishResponse'] = -1;
        }
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * 
     * @Route("/unlinkSession/{activityId}/{eventId}", requirements={"clubId" = "\d+", "eventId" = "\d+"}, name = "ksCoaching_unlinkSession", options={"expose"=true} )
     * @param int $clubId 
     */
    public function unlinkSessionAction($activityId, $eventId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $clubRep        = $em->getRepository('KsClubBundle:Club');
        
        $responseDatas = array();
        
        $clubRep->unlinkSession($activityId, $eventId);
        
        $responseDatas["code"] = 1;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * @Route("/getCoachingGraph/planId}", defaults={"startOn" = null, "endOn" = null} )
     * @Route("/getCoachingGraph/{context}/{planId}/{userId}/{startOn}/{endOn}", defaults={"endOn" = null}, name = "ksCoaching_getCoachingGraph", options={"expose"=true}  )
     */
    public function getCoachingGraphAction($context, $planId, $userId, $startOn, $endOn)
    {
        $em                             = $this->getDoctrine()->getEntityManager();
        $securityContext                = $this->container->get('security.context');
        $agendaRep                      = $em->getRepository('KsAgendaBundle:Agenda');
        $activityRep                    = $em->getRepository('KsActivityBundle:Activity');
        $coachingPlanRep                = $em->getRepository('KsCoachingBundle:CoachingPlan');
        $preferenceRep                  = $em->getRepository('KsUserBundle:Preference');
        $preferenceTypeRep              = $em->getRepository('KsUserBundle:PreferenceType');
        $userRep                        = $em->getRepository('KsUserBundle:User');
        $clubRep                        = $em->getRepository('KsClubBundle:Club');
        $userHasToDoChecklistActionRep  = $em->getRepository('KsUserBundle:UserHasToDoChecklistAction');
        $checklistActionRep             = $em->getRepository('KsUserBundle:ChecklistAction');
        
        $visitSeenPreference = array();
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user                   = $this->container->get('security.context')->getToken()->getUser();
            $visitSeenPreference    = $userHasToDoChecklistActionRep->findByUserAndChecklistAction($user->getId(), $checklistActionRep->findOneByCode("dashboardSeen")->getId());
        }
        else {
            //Visitor
            $user = $userRep->find(1);
        }
                
        $session = $this->get('session');
        
        $getUserSessionForFlatView  = false;
        $userIdForFlatView          = $userId;
        $isManagerFromAClub         = null;
        if ($planId != -1) {
            //Pour gérer le cas ou un sportif premium affiche sa page sans choisir de plan d'entrainement
            $plan = $coachingPlanRep->find($planId);
            if (!is_null($plan->getUser())) $userIdForFlatView = $plan->getUser()->getId();
            else $userIdForFlatView = null;
            
            //Si l'utilisateur est un coach il doit pouvoir créer des plans premiums à partager
            $isManagerFromAClub = $userRep->isManagerFromAClub($user->getId());

            if ($plan->getUser()->getIsAllowedPackElite()) $getUserSessionForFlatView = true;
            if ($plan->getUser()->getIsAllowedPackPremium()) $getUserSessionForFlatView = false;
            if (is_null($plan->getClub()) && $isManagerFromAClub >0 ) $getUserSessionForFlatView = false; //Si l'utilisateur est un coach on ne lui affiche pas ses activités lorsqu'il choisi un plan premium
        }
        else $getUserSessionForFlatView = true;
        
        if ($context == 'KPIView' || $context == "KPIViewWithNoPlan") {
            $startOn = "";
            $endOn = "";
        }
        
        if ($context == 'weekView') $getUserSessionForFlatView = false; //Pour éviter bug si un coach partage un plan premium
        
        if ($startOn == null || $startOn == 'null') {
            $startOn = "";
        }
            //$now = new \DateTime();
            //$startOn = $now->format("Y-m-01");
        
        if ($endOn == null || $endOn == 'null') {
            $endOn = "";
        }
        
        $events = $agendaRep->findAgendaEvents(array(
            "planId"                    => $planId,
            "getUserSessionForFlatView" => $getUserSessionForFlatView,
            "userIdForFlatView"         => $userIdForFlatView,
            "order"                     => array("DATE(e.startDate)" => "ASC"),
            "startOn"                   => $startOn,
            "endOn"                     => $endOn
        ), $this->get('translator'));
        
        //var_dump($getUserSessionForFlatView);exit;
        //var_dump($events);exit;
        
        $lastDay =null;
        
        if (count($events) >0 ) {
            //Récupération de la 1ère et dernière date du plan + nombre de semaines
            $firstDay = new \DateTime($events[0]['start']);
            $lastDay = new \DateTime($events[count($events)-1]['start']);
            $weeksNumber = ceil(abs($firstDay->diff($lastDay)->format('%R%a')) / 7);

            $firstWeek = $events[0]['weekNumber'];

            foreach($events as $event) {
                if ($event['weekNumber'] != $firstWeek) {
                    $firstWeek = $event['weekNumber'];
                    $weeks[] = $week;
                    unset($week);
                    $week[] = $event;
                }
                else {
                    $week[] = $event;
                }
            }
            $weeks[] = $week;
        }
        
        //var_dump($weeks);
        //exit;
        
        $sportsCoaching = array();
        $categories = array();
        $difficulties = array();
        $timeZones = array();
        $timeZones[1] = array(
            "id"    => 1,
            "coachingNumber"     => 0,
            "number"     => 0,
            "label" => $this->get('translator')->trans('coaching.time-zone-1'),
            "coachingDuration" => 0,
            "duration" => 0
        );
        $timeZones[2] = array(
            "id"        => 2,
            "coachingNumber"     => 0,
            "number"     => 0,
            "label"  => $this->get('translator')->trans('coaching.time-zone-2'),
            "coachingDuration" => 0,
            "duration" => 0
        );
        $timeZones[3] = array(
            "id"        => 3,
            "coachingNumber"     => 0,
            "number"     => 0,
            "label"  => $this->get('translator')->trans('coaching.time-zone-3'),
            "coachingDuration" => 0,
            "duration" => 0
        );
        $timeZones[4] = array(
            "id"        => 4,
            "coachingNumber"     => 0,
            "number"     => 0,
            "label"  => $this->get('translator')->trans('coaching.time-zone-4'),
            "coachingDuration" => 0,
            "duration" => 0
        );
        $timeZones[5] = array(
            "id"        => 5,
            "coachingNumber"     => 0,
            "number"     => 0,
            "label"  => $this->get('translator')->trans('coaching.time-zone-5'),
            "coachingDuration" => 0,
            "duration" => 0
        );
        $HRZones = array();
        $HRZonesPreference = $preferenceTypeRep->findOneByCode("hr");
        $preferences = $preferenceRep->findBy(array("preferenceType" => $HRZonesPreference->getId()));
        if ($planId != -1) {
            $HRRest = $plan->getUser()->getUserDetail()->getHRRest();
            $HRMax  = $plan->getUser()->getUserDetail()->getHRMax();
        }
        else {
            $HRRest = $userRep->find($userId)->getUserDetail()->getHRRest();
            $HRMax  = $userRep->find($userId)->getUserDetail()->getHRMax();
        }
                
        is_null($HRRest) ? 0 : $HRRest;
        is_null($HRMax) ? 0 : $HRMax;
        if (!is_null($HRRest) && !is_null($HRMax)) {
            foreach($preferences as $key => $preference) {
                $HRZones[$key] = array(
                    "id"    => $key,
                    "y"     => 0,
                    "label" => $this->get('translator')->trans('hr.'.$preference->getCode()),
                    "range" => $this->get('translator')->trans('hr.from') . " ". strval(round($HRRest + $preference->getVal1() /100 * ($HRMax - $HRRest),0)) . " " . $this->get('translator')->trans('hr.to') . " ". strval(round($HRRest + $preference->getVal2() /100 * ($HRMax - $HRRest),0)) . " bpm",
                    "val1"  => $preference->getVal1(),
                    "val2"  => $preference->getVal2(),
                    "duration" => 0
                );
            }
        }
        
        $sportsCoaching = array();
        $sportsDone = array();
        $totalDuration = 0;
        $totalCoachingDuration = 0;
        $totalCoachingDistance = 0;
        $totalCoachingElevationGain = 0;
        $totalCoachingElevationLost = 0;
        $totalCoachingDurationLinked = 0;
        $totalSessionsScheduled = 0;
        $totalSessionsDone = 0;
        $now = new \Datetime();
        $totalSessionsEnded = 0;
        $totalActivitiesDone = 0;
        $totalSessionsDuration = 0;
        $totalActivitiesDuration = 0;
        $totalSessionsDistance = 0;
        $totalSessionsElevationGain = 0;
        $totalSessionsElevationLost = 0;
        $totalCoachingLinkedDuration = 0;
        $totalSessionsNotAchieved = 0;
        $totalAchievement = 0;
        $totalSessionsWithAchievement =0;
        $totalHRDuration =0;
        $waypointsActivities = array();
        foreach( $events as $event ) {
            if ($event['sport_id'] != null && $event['sport_label'] != 'empty') {
                if ($event['activitySession_id'] != null && $event['coachingPlan_id']) {
                    if (!is_null($event['eventDurationMin']) && !is_null($event['eventDurationMax'])) {
                        $totalCoachingDurationLinked += 1/2 * ($this->dateTimeToSeconds(new \DateTime($event['eventDurationMin'])) + $this->dateTimeToSeconds(new \DateTime($event['eventDurationMax'])));
                    }
                    else if (!is_null($event['eventDurationMin'])) $totalCoachingDurationLinked += $this->dateTimeToSeconds(new \DateTime($event['eventDurationMin']));
                    else if (!is_null($event['eventDurationMax'])) $totalCoachingDurationLinked += $this->dateTimeToSeconds(new \DateTime($event['eventDurationMax']));
                    if ($event['duration'] == '-' || $this->dateTimeToSeconds(new \DateTime($event['duration'])) == 0) {
                        $totalSessionsNotAchieved += 1;
                        
                    }
                    else {
                        $totalSessionsDone += 1;
                        $totalSessionsDuration += $this->dateTimeToSeconds(new \DateTime($event['duration']));
                    }
                    if ($event['achievement']) {
                        $totalAchievement += $event['achievement'];
                        $totalSessionsWithAchievement +=1;
                    }
                }
                if ($event['coachingPlan_id']) {
                    $totalSessionsScheduled += 1;
                    if ($event['start'] < $now->format("Y-m-d")) $totalSessionsEnded +=1;
                }
                if ($event['duration'] != '-') $duration = $this->dateTimeToSeconds(new \DateTime($event['duration']));
                else $duration = 0;
                $totalDuration += $duration;
                
                if (!is_null($event['eventDurationMin']) && !is_null($event['eventDurationMax'])) {
                    $coachingDuration = 1/2 * ($this->dateTimeToSeconds(new \DateTime($event['eventDurationMin'])) + $this->dateTimeToSeconds(new \DateTime($event['eventDurationMax'])));
                }
                else if (!is_null($event['eventDurationMin'])) $coachingDuration = $this->dateTimeToSeconds(new \DateTime($event['eventDurationMin']));
                else if (!is_null($event['eventDurationMax'])) $coachingDuration = $this->dateTimeToSeconds(new \DateTime($event['eventDurationMax']));
                    
                else $coachingDuration = 0;
                $totalCoachingDuration += $coachingDuration;
                
                if ($event['duration'] != '-' && $this->dateTimeToSeconds(new \DateTime($event['duration'])) != 0) {
                    $totalActivitiesDone += 1;
                    $totalActivitiesDuration += $this->dateTimeToSeconds(new \DateTime($event['duration']));
                }
                /*$totalActivitiesDistance += $event['distance'];
                $totalActivitiesElevationGain += $event['elevationGain'];
                $totalActivitiesElevationLost += $event['elevationLost'];
                $totalCoachingElevationGain += $event['coachingDistance'];
                $totalCoachingElevationGain += $event['coachingElevationGain'];
                $totalCoachingElevationLost += $event['coachingElevationLost'];*/
                
                if ($event['coachingSession_id']) $totalCoachingLinkedDuration += $coachingDuration;
                
                if (!array_key_exists($event['sport_id'], $sportsCoaching)) {
                    //var_dump($event['sport_label'] . "/" . $event['distance']);
                    //var_dump($event['sport_label'] . "/" . $event['duration'] != '-' ? $this->dateTimeToSeconds(new \DateTime($event['duration'])) : 0);
                    $sportsCoaching[$event['sport_id']] = array(
                        "sport_id"              => $event['sport_id'],
                        "sport_label"           => $event['sport_label'],
                        "number"                => 1,
                        "coachingNumber"        => 1,
                        "coachingDuration"      => $coachingDuration,
                        /*"coachingDistance"      => $event['coachingDistance'] != null ? $event['coachingDistance'] : 0,
                        "coachingElevationGain" => $event['coachingElevationGain'] != null ? $event['coachingElevationGain'] : 0,
                        "coachingElevationLost" => $event['coachingElevationLost'] != null ? $event['coachingElevationLost'] : 0*/
                    );
                }
                else {
                    $sportsCoaching[$event['sport_id']]["coachingNumber"]           += 1;
                    $sportsCoaching[$event['sport_id']]["coachingDuration"]         += $coachingDuration;
                    /*$sportsCoaching[$event['sport_id']]["coachingDistance"]         += $event['coachingDistance'] != null ? $event['coachingDistance'] : 0;
                    $sportsCoaching[$event['sport_id']]["coachingElevationGain"]    += $event['coachingElevationGain'] != null ? $event['coachingElevationGain'] : 0;
                    $sportsCoaching[$event['sport_id']]["coachingElevationLost"]    += $event['coachingElevationLost'] != null ? $event['coachingElevationLost'] : 0;*/
                }
                
                if ($event['activitySession_sport_id'] != null) {
                    if (!array_key_exists($event['activitySession_sport_id'], $sportsDone)) {
                        $sportsDone[$event['activitySession_sport_id']] = array(
                            "sport_id"              => $event['activitySession_sport_id'],
                            "sport_label"           => $event['activitySession_sport_label'],
                            "number"                => 1,
                            "distance"              => $event['distance'] != '-' ? $event['distance'] : 0,
                            "duration"              => $duration,
                            "elevationGain"         => $event['elevationGain'] != '-' ? $event['elevationGain'] : 0,
                            "elevationLost"         => $event['elevationLost'] != '-' ? $event['elevationLost'] : 0,
                        );
                    }
                    else {
                        $sportsDone[$event['activitySession_sport_id']]["number"]                   += 1;
                        $sportsDone[$event['activitySession_sport_id']]["duration"]                 += $duration;
                        /*$sportsDone[$event['activitySession_sport_id']]["distance"]                 += $event['distance'] != '-' ? $event['distance'] : 0;
                        $sportsDone[$event['activitySession_sport_id']]["elevationGain"]            += $event['elevationGain'] != '-' ? $event['elevationGain'] : 0;
                        $sportsDone[$event['activitySession_sport_id']]["elevationLost"]            += $event['elevationLost'] != '-' ? $event['elevationLost'] : 0;*/
                    }
                }
                
                if (!array_key_exists($event['coachingCategory_id'], $categories)) {
                    $categories[$event['coachingCategory_id']] = array(
                        "coachingCategory_id"   => $event['coachingCategory_id'],
                        "category"              => $event['coachingCategory_id'] == null ? ($planId != -1 ? $this->get('translator')->trans('coaching.own-sessions') : $this->get('translator')->trans('coaching.not-defined')) : $event['category'],
                        "coachingNumber"        => 1,
                        "number"                => 1,
                        "distance"              => $event['distance'] != null ? $event['distance'] : 0,
                        "coachingDuration"      => $coachingDuration,
                        "duration"              => $duration,
                        /*"elevationGain"         => $event['elevationGain'] != null ? $event['elevationGain'] : 0,
                        "elevationLost"         => $event['elevationLost'] != null ? $event['elevationLost'] : 0,
                        "coachingDistance"      => $event['coachingDistance'] != null ? $event['coachingDistance'] : 0,
                        "coachingElevationGain" => $event['coachingElevationGain'] != null ? $event['coachingElevationGain'] : 0,
                        "coachingElevationLost" => $event['coachingElevationLost'] != null ? $event['coachingElevationLost'] : 0,
                        */
                    );
                }
                else {
                    $categories[$event['coachingCategory_id']]["coachingNumber"]           += 1;
                    $categories[$event['coachingCategory_id']]["number"]                   += 1;
                    $categories[$event['coachingCategory_id']]["duration"]                 += $duration;
                    $categories[$event['coachingCategory_id']]["coachingDuration"]         += $coachingDuration;
                    /*
                    $categories[$event['coachingCategory_id']]["distance"]                 += $event['distance'] != null ? $event['distance'] : 0;
                    $categories[$event['coachingCategory_id']]["elevationGain"]            += $event['elevationGain'] != null ? $event['elevationGain'] : 0;
                    $categories[$event['coachingCategory_id']]["elevationLost"]            += $event['elevationLost'] != null ? $event['elevationLost'] : 0;
                    $categories[$event['coachingCategory_id']]["coachingDistance"]         += $event['coachingDistance'] != null ? $event['coachingDistance'] : 0;
                    
                    $categories[$event['coachingCategory_id']]["coachingElevationGain"]    += $event['coachingElevationGain'] != null ? $event['coachingElevationGain'] : 0;
                    $categories[$event['coachingCategory_id']]["coachingElevationLost"]    += $event['coachingElevationLost'] != null ? $event['coachingElevationLost'] : 0;
                    */
                }
                
                if (!array_key_exists($event['difficulty_id'], $difficulties)) {
                    $difficulty_label = $this->get('translator')->trans('intensity.low');
                    switch ($event['difficulty_id']) {
                        case 1:
                            $difficulty_label = $this->get('translator')->trans('intensity.low');
                            $color = "#10c857";
                            break;
                        case 2:
                            $difficulty_label = $this->get('translator')->trans('intensity.medium');
                            $color = "#feb323";
                            break;
                        case 3:
                            $difficulty_label = $this->get('translator')->trans('intensity.high');
                            $color = "#da2032";
                            break;
                        case null:
                            $difficulty_label = $this->get('translator')->trans('intensity.nc');
                            $color = "#0ae";
                            break;
                    }
                    $difficulties[$event['difficulty_id']] = array(
                        "difficulty_id"         => $event['difficulty_id'],
                        "difficulty_label"      => $difficulty_label,
                        "color"                 => $color,
                        "coachingNumber"        => 1,
                        "number"                => 1,
                        "coachingDuration"      => $coachingDuration,
                        "duration"              => $duration,
                    );
                }
                else {
                    $difficulties[$event['difficulty_id']]["coachingNumber"]           += 1;
                    $difficulties[$event['difficulty_id']]["number"]                   += 1;
                    $difficulties[$event['difficulty_id']]["coachingDuration"]         += $coachingDuration;
                    $difficulties[$event['difficulty_id']]["duration"]                 += $duration;
                }
                
                
                //Camembert "plage de temps"
                if ($coachingDuration < 3600 && $coachingDuration !=0) {
                    $timeZones[1]["coachingNumber"]         += 1;
                    $timeZones[1]["coachingDuration"]         += $coachingDuration;
                }
                else if ($coachingDuration >= 3600 && $coachingDuration < 3600*1.5) {
                    $timeZones[2]["coachingNumber"]         += 1;
                    $timeZones[2]["coachingDuration"]         += $coachingDuration;
                }
                else if ($coachingDuration >= 3600*1.5 && $coachingDuration < 3600*2) {
                    $timeZones[3]["coachingNumber"]         += 1;
                    $timeZones[3]["coachingDuration"]         += $coachingDuration;
                }
                else if ($coachingDuration >= 3600*2 && $coachingDuration < 3600*3) {
                    $timeZones[4]["coachingNumber"]         += 1;
                    $timeZones[4]["coachingDuration"]         += $coachingDuration;
                }
                else if ($coachingDuration >= 3600*3) {
                    $timeZones[5]["coachingNumber"]         += 1;
                    $timeZones[5]["coachingDuration"]         += $coachingDuration;
                }
                
                //Camembert "plage de temps"
                if ($duration < 3600 && $duration !=0) {
                    $timeZones[1]["number"]         += 1;
                    $timeZones[1]["duration"]                 += $duration;
                }
                else if ($duration >= 3600 && $duration < 3600*1.5) {
                    $timeZones[2]["number"]         += 1;
                    $timeZones[2]["duration"]                 += $duration;
                }
                else if ($duration > 3600*1.5 && $duration < 3600*2) {
                    $timeZones[3]["number"]         += 1;
                    $timeZones[3]["duration"]                 += $duration;
                }
                else if ($duration >= 3600*2 && $duration < 3600*3) {
                    $timeZones[4]["number"]         += 1;
                    $timeZones[4]["duration"]                 += $duration;
                }
                else if ($duration >= 3600*3) {
                    $timeZones[5]["number"]         += 1;
                    $timeZones[5]["duration"]                 += $duration;
                }
                
                if ($event['activitySession_id'] != null) {
                    $activity = $activityRep->find($event['activitySession_id']);
                    if (is_null($activity)) $trackingDatas  = null;
                    else $trackingDatas = $activity->getTrackingDatas();
                    if (!is_null($trackingDatas)) {
                        //Camembert HR
                        if (!is_null($HRRest) && !is_null($HRMax) && isset($trackingDatas['info']['HRZones']) && !is_null($trackingDatas['info']['HRZones'])) {
                            foreach($preferences as $key => $preference) {
                                if (isset($trackingDatas['info']['HRZones'][$preference->getCode()])) {
                                    $HRZones[$key]["duration"] += $trackingDatas['info']['HRZones'][$preference->getCode()]["duration"];
                                    $totalHRDuration += $HRZones[$key]["duration"];
                                }
                            }
                        }
                        //Récupération des waypoints pour afficher une mini map (Google map static)
                        $waypoints = $trackingDatas["waypoints"];
                        if (!is_null($waypoints) && count($waypoints) != 0) {
                            $iStep = round(count($waypoints) / 500); //FIXME : si ça colle il faudrait idéalement stocké un waypoints light pour éviter de le refaire ici à chaque fois
                            if ($iStep == 0) $iStep = 1;
                            $waypointsActivity = array();
                            for ($i = 0; $i < count($waypoints); ++$i) {
                                if ($i % $iStep == 0) $waypointsActivity[] = array('lat' => $waypoints[$i]["lat"], 'lon' => $waypoints[$i]["lon"]);
                            }
                            $waypointsActivities[] = array('id' => $event['activitySession_id'], 'points' => $waypointsActivity);
                        }
                    }
                }
            }
        }
        
        $pieBySportCoaching = array();
        foreach( $sportsCoaching as $sport ) {
            if ($sport['sport_label'] != 'empty') {
                $coachingDuration = $sport["coachingDuration"];
                $pieBySportCoaching[] = array("name" => $this->get('translator')->trans('sports.'.$sport['sport_label']), "duration" => $this->secondesToTimeDuration($coachingDuration), "y" => $totalCoachingDuration !=0 ? round($coachingDuration/$totalCoachingDuration *100, 0) : 0, "sliced" => false);
                $sportsCoaching[$sport['sport_id']]["coachingDuration"] = $this->secondesToTimeDuration($coachingDuration);
            }
        }
        $pieBySportDone = array();
        foreach( $sportsDone as $sport ) {
            if ($sport['sport_label'] != 'empty') {
                $duration = $sport["duration"];
                $pieBySportDone[] = array("name" => $this->get('translator')->trans('sports.'.$sport['sport_label']), "duration" => $this->secondesToTimeDuration($duration), "y" => $totalDuration !=0 ? round($duration/$totalDuration *100, 0) : 0, "sliced" => false);
                $sportsDone[$sport['sport_id']]["duration"] = $this->secondesToTimeDuration($duration);
            }
        }
        
        $pieByCategoryCoaching = array();
        $pieByCategoryDone = array();
        foreach( $categories as $category ) {
            $coachingDuration   = $category["coachingDuration"];
            $duration           = $category["duration"];
            if (!is_null($category["coachingCategory_id"])) $pieByCategoryCoaching[]    = array("name" => $category['category'], "duration" => $this->secondesToTimeDuration($coachingDuration), "y" => $totalCoachingDuration !=0 ? round($coachingDuration/$totalCoachingDuration *100, 0) : 0, "sliced" => false);
            //if(!is_null($duration) && $duration != 0) 
            $pieByCategoryDone[]        = array("name" => $category['category'], "duration" => $this->secondesToTimeDuration($duration), "y" => $totalDuration !=0 ? round($duration/$totalDuration *100, 0) : 0, "sliced" => false);
            //$categories[$category['coachingCategory_id']]["coachingDuration"] = $this->secondesToTimeDuration($coachingDuration);
        }
        $pieByDifficultyCoaching = array();
        $pieByDifficultyDone = array();
        foreach( $difficulties as $difficulty ) {
            $coachingDuration = $difficulty["coachingDuration"];
            $duration = $difficulty["duration"];
            $pieByDifficultyCoaching[]  = array("name" => $difficulty['difficulty_label'], "color" => $difficulty["color"], "duration" => $this->secondesToTimeDuration($coachingDuration), "y" => $totalCoachingDuration !=0 ? round($coachingDuration/$totalCoachingDuration *100, 0) : 0, "sliced" => false);
            if(!is_null($duration) && $duration != 0) $pieByDifficultyDone[]      = array("name" => $difficulty['difficulty_label'], "color" => $difficulty["color"], "duration" => $this->secondesToTimeDuration($duration), "y" => $totalDuration !=0 ? round($duration/$totalDuration *100, 0) : 0, "sliced" => false);
            //$difficulties[$difficulty['difficulty_id']]["coachingDuration"] = $this->secondesToTimeDuration($coachingDuration);
        }
        $pieByTimeZoneCoaching = array();
        $pieByTimeZoneDone = array();
        foreach( $timeZones as $timeZone ) {
            //$pieByTimeZoneCoaching[] = array("name" => $timeZone['label'], "y" => $timeZone["y"], "duration" => strval($this->secondesToTimeDuration($timeZone["coachingDuration"])));
            //$pieByTimeZoneDone[] = array("name" => $timeZone['label'], "y" => $timeZone["y"], "duration" => strval($this->secondesToTimeDuration($timeZone["duration"])));
            $pieByTimeZoneCoaching[] = array("name" => $timeZone['label'], "y" => $this->secondesToDecimalHours($timeZone["coachingDuration"]), "occurences" => $timeZone["coachingNumber"], "duration" => strval($this->secondesToTimeDuration($timeZone["coachingDuration"])));
            $pieByTimeZoneDone[] = array("name" => $timeZone['label'], "y" => $this->secondesToDecimalHours($timeZone["duration"]), "occurences" => $timeZone["number"], "duration" => strval($this->secondesToTimeDuration($timeZone["duration"])));
        }
        $displayPieByHRZone = true;
        $pieByHRZone = array();
        if (!is_null($HRRest) && !is_null($HRMax)) {
            $displayPieByHRZone = true;
            foreach( $HRZones as $HRZone ) {
                $HRDuration = $HRZone["duration"];
                if (!is_null($HRDuration) && $HRDuration !=0) $pieByHRZone[] = array("name" => $HRZone['range'], "label" => $HRZone['label'], "duration" => strval($this->secondesToTimeDuration($HRDuration)), "y" => $totalHRDuration !=0 ? round($HRDuration/$totalHRDuration *100, 0) : 0, "sliced" => false);
            }
        }
        else {
            $pieByHRZone[] = array("id"         => 0, 
                                   "val1"       => 0, 
                                   "val2"       => 0, 
                                   "name"       => $this->get('translator')->trans('hr.zone0'), 
                                   "label"      => "", 
                                   "duration"   => strval($this->secondesToTimeDuration($totalSessionsDuration)), 
                                    "y"         => 100, 
                                   "sliced"     => false);
        }
        
        //Construction des jauges avancement, assiduité, efficacité
        if ($totalSessionsScheduled > 0) $progress = round(100 * $totalSessionsEnded/$totalSessionsScheduled, 0);
        else $progress = 0;
        
        if ($totalSessionsEnded != 0) $attendance = round(100 * $totalSessionsDone / $totalSessionsEnded, 0);
        else $attendance = 0;
        
        if ($totalCoachingDurationLinked !=0) $efficiency = round(100 * $totalSessionsDuration / $totalCoachingDurationLinked, 0);
        else $efficiency = 0;
        if ($totalCoachingDurationLinked ==0) $efficiency = 0;
        
        if ($totalSessionsWithAchievement != 0) $achievement = round($totalAchievement / $totalSessionsWithAchievement, 1);
        else $achievement =0;
        
        $responseDatas = array();
        
        $responseDatas["code"] = 1;
        
        if ($startOn == "") {
            if (isset($firstDay) && !is_null($firstDay)) $responseDatas["startOn"] = $firstDay->format('Y-m-d');
            else $responseDatas["startOn"] = $now->format('Y-m-d');
        }
        else {
            $responseDatas["startOn"] = date('Y-m-d', strtotime($startOn));
        }
        
        if ($endOn == "") $responseDatas["endOn"] = "";
        else $responseDatas["endOn"] = date('Y-m-d', strtotime($endOn));
        
        $member     = null;
        $isManager  = null;
        $manager    = null;
        $code       = null;
        if ($planId != -1) {
            $code = $plan->getCoachingPlanType()->getCode();
            if ($code == 'coach') {
                $clubId = $plan->getClub()->getId();
                if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) $isManager = $clubRep->isManager($plan->getClub()->getId(), $this->get('security.context')->getToken()->getUser()->getId());
                else $isManager = false;
                $manager = $clubRep->findOneManagerByClubAndFunction( $clubId, 'Coach sportif' );
                $member = $userRep->findOneUser(array("userId" => $plan->getUser()->getId()), $this->get('translator'));
                $questionnaireForm  = $this->createForm(new \Ks\UserBundle\Form\ProfileQuestionnaireType(), $plan->getUser());
            }
        }
        
        if ($context == 'KPIView' || $context == 'KPIViewWithNoPlan') {
            
            //Récupère la prochaine compétition si défini en tant que telle pour le sportif
            $competition = $coachingPlanRep->getNextCompetition($planId);
            
            $responseDatas['KPIView'] = $this->render(
                'KsCoachingBundle:Coaching:_coachingKPIView.html.twig',
                array(
                    "planType"                      => $code,
                    "lastDay"                       => $lastDay,
                    "isManager"                     => $isManager ? 1 : 0,
                    "member"                        => $member,
                    "manager"                       => $manager,
                    "competition"                   => $competition,
                    "questionnaireForm"             => $code == 'coach' ? $questionnaireForm->createView() : null,
                    "clubId"                        => $code == 'coach' ? $clubId : null,
                    "progress"                      => $progress,
                    "totalSessionsEnded"            => $totalSessionsEnded,
                    "totalSessionsDone"             => $totalSessionsDone,
                    "totalSessionsScheduled"        => $totalSessionsScheduled,
                    "attendance"                    => $attendance,
                    //"totalSessionsLinked"         => $totalSessionsDone + $totalSessionsNotAchieved,
                    "efficiency"                    => $efficiency,
                    "totalCoachingDuration"         => $this->secondesToTimeDuration($totalCoachingDuration),
                    "totalActivitiesDone"           => $totalActivitiesDone,
                    "totalActivitiesDuration"       => $this->secondesToTimeDuration($totalActivitiesDuration),
                    /*"totalCoachingDistance"         => $totalCoachingDistance,
                    "totalCoachingElevationGain"    => $totalCoachingElevationGain,
                    "totalCoachingElevationLost"    => $totalCoachingElevationLost,
                    "totalSessionsDistance"         => $totalSessionsDistance,
                    "totalSessionsElevationGain"    => $totalSessionsElevationGain,
                    "totalSessionsElevationLost"    => $totalSessionsElevationLost,*/
                    "achievement"                   => $achievement,
                    "isManagerFromAClub"            => $isManagerFromAClub
                )
            )->getContent();
        }
        else if ($context == 'flatView') {
            if (count($pieBySportCoaching) ==0) {
                $responseDatas['KPIDetails'] = "<div class='alert alert-info'>".$this->get('translator')->trans('coaching.no-data-no-graphs')."</div>";
                $responseDatas['flatView'] = "";
            }    
            else {
                $coachingIsBigger = $totalCoachingDuration > $totalActivitiesDuration ? 1 : 0;
                $coeff = 100;
                if ($coachingIsBigger) $coeff = $totalCoachingDuration ==0 ? 100 : 100*$totalActivitiesDuration/$totalCoachingDuration;
                else $totalActivitiesDuration ==0 ? 100 : 100*$totalCoachingDuration/$totalActivitiesDuration;
                
                /*print_r('coachingIsBigger='.$coachingIsBigger);
                print_r('coeff='.$coeff);*/
                
                $responseDatas['KPIDetails'] = $this->render(
                    'KsCoachingBundle:Coaching:_coachingKPIDetailsView.html.twig',
                    array(
                        "planType"                  => $code,
                        "coachingIsBigger"          => $coachingIsBigger,
                        "totalCoachingDuration"     => $this->secondesToTimeDuration($totalCoachingDuration),
                        "totalActivitiesDuration"   => $this->secondesToTimeDuration($totalActivitiesDuration),
                        "totalActivitiesDone"       => $totalActivitiesDone,
                        "totalSessionsScheduled"    => $totalSessionsScheduled,
                        "coeff"                     => $coeff,
                        'pieBySportCoaching'        => $this->var_to_js('pieBySportCoaching', $pieBySportCoaching, 1),
                        'pieBySportDone'            => $this->var_to_js('pieBySportDone', $pieBySportDone, 1),
                        'pieByCategoryCoaching'     => $this->var_to_js('pieByCategoryCoaching', $pieByCategoryCoaching, 1),
                        'pieByCategoryDone'         => $this->var_to_js('pieByCategoryDone', $pieByCategoryDone, 1),
                        'pieByDifficultyCoaching'   => $this->var_to_js('pieByDifficultyCoaching', $pieByDifficultyCoaching, 1),
                        'pieByDifficultyDone'       => $this->var_to_js('pieByDifficultyDone', $pieByDifficultyDone, 1),
                        'pieByTimeZoneCoaching'     => $this->var_to_js('pieByTimeZoneCoaching', $pieByTimeZoneCoaching, 1),
                        'pieByTimeZoneDone'         => $this->var_to_js('pieByTimeZoneDone', $pieByTimeZoneDone, 1),
                        'displayPieByHRZone'        => $displayPieByHRZone,
                        'pieByHRZone'               => $this->var_to_js('pieByHRZone', $pieByHRZone, 1),
                        'HRRest'                    => is_null($HRRest) ? "-" : $HRRest,
                        'HRMax'                     => is_null($HRMax)  ? "-" : $HRMax,
                        'visitSeenPreference'       => $visitSeenPreference,
                        'user'                      => $user
                    )
                )->getContent();
                
                //Récupération des commentaires de chaque activité 
                $comments = array();
                foreach( $events as $event ) {
                    if ($event['activitySession_id'] != null) {
                        //var_dump($event['activitySession_id']);
                        //exit;
                        if (is_null($activity)) $comments[$event['activitySession_id']] = null;
                        else $comments[$event['activitySession_id']] = $activityRep->getCommentsOnActivity($activityRep->find($event['activitySession_id']));
                    }
                }
                
                if ($planId != -1) $isUser = $user->getId() == $plan->getUser()->getId();
                else $isUser = $user->getId() == $userId;
                $responseDatas['flatView'] = $this->render(
                    'KsCoachingBundle:Coaching:_coachingFlatView.html.twig',
                    array(
                        'planType'                  => $code,
                        'isUser'                    => $isUser,
                        'isManager'                 => $isManager ? 1 : 0,
                        'sessions'                  => $events,
                        'waypointsActivities'       => $this->var_to_js('waypointsActivities', $waypointsActivities, 1),
                        'comments'                  => $comments
                    )
                )->getContent();
            }
        }
        else if ($context == 'weekView') {
            $responseDatas['weekView'] = $this->render('KsCoachingBundle:Coaching:_coachingWeekView.html.twig', array(
                'planId'                    => $planId,
                'firstDay'                  => $firstDay->format("w"),
                'lastDay'                   => $lastDay->format("w"),
                '$weeksNumber'              => $weeksNumber,
                'weeks'                     => $weeks,
                'sports'                    => $sportsCoaching,
                "totalSessionsScheduled"    => $totalSessionsScheduled,
                "totalCoachingDuration"     => $this->secondesToTimeDuration($totalCoachingDuration),
                'pieBySportCoaching'        => $this->var_to_js('pieBySportCoaching', $pieBySportCoaching, 1),
                'pieByCategoryCoaching'     => $this->var_to_js('pieByCategoryCoaching', $pieByCategoryCoaching, 1),
                'pieByDifficultyCoaching'   => $this->var_to_js('pieByDifficultyCoaching', $pieByDifficultyCoaching, 1),
                'pieByTimeZoneCoaching'     => $this->var_to_js('pieByTimeZoneCoaching', $pieByTimeZoneCoaching, 1),
                )
            )->getContent();
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/{id}/{startOn}/{endOn}/{planId}", name = "ksCoaching_getStatisticsGraph", options={"expose"=true}  )
     * @ParamConverter("user", class="KsUserBundle:User")
     * @Template()
     */
    public function getStatisticsGraphAction(\Ks\UserBundle\Entity\User $user, $startOn, $endOn, $planId=null) {
        $em         = $this->getDoctrine()->getEntityManager();
        $userRep    = $em->getRepository('KsUserBundle:User');
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'dashboard');

        //Récupération du sport le plus partiqué par l'utilisateur
        $favoriteSport = $userRep->getFavoriteSportAndStartDate($user->getId());
        
        $sport_id = null;
        $defaultStartDate = date('01-m-Y', strtotime('-12 months'));
        $now = new \DateTime('now');
        
        if (isset($favoriteSport[0]) && $favoriteSport[0]['sport_id'] != null ) {
            $sport_id = $favoriteSport[0]['sport_id'];
            //$startOn = new \DateTime($favoriteSport[0]['startOn']);
            //$ecart = $startOn->diff($now)->format('%a');
            //if ($ecart <= 365) $defaultStartDate = date('d-m-Y', strtotime($favoriteSport[0]['startOn']));
        }
        
        $activitySportChoiceForm = $this->createForm(new \Ks\ActivityBundle\Form\SportType('Stats'), null);
        
        $myEquipmentsForm  = $this->createForm(new \Ks\EquipmentBundle\Form\MyEquipmentsType($user));
        
        $responseDatas = array();
        
        $responseDatas["code"] = 1;
        
        $responseDatas['graph'] = $this->render('KsCoachingBundle:Coaching:_coachingStatisticsView.html.twig', array(
                'user'                      => $user,
                'planId'                    => $planId,
                'favoriteSport'             => $sport_id,
                'startOn'                   => $startOn,
                'endOn'                     => $endOn,
                'activitySportChoiceForm'   => $activitySportChoiceForm->createView(),
                'myEquipmentsForm'          => $myEquipmentsForm->createView(),
                )
            )->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        
        return $response;
    }
    
    /**
     * @Route("/getComparisonTool/{id}/{activityId}", name = "ksCoaching_getComparisonTool", options={"expose"=true}  )
     * @ParamConverter("user", class="KsUserBundle:User")
     * @Template()
     */
    public function getComparisonToolAction(\Ks\UserBundle\Entity\User $user, $activityId) {
        $em                 = $this->getDoctrine()->getEntityManager();
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        $session->set('page', 'dashboard');

        $responseDatas = array();
        
        $responseDatas["code"] = 1;
        
        $coachingCategorySessionForm = $this->createForm(new \Ks\CoachingBundle\Form\CoachingCategorySessionType($user));
        
        $event                 = new \Ks\EventBundle\Entity\Event();
        $eventForm             = $this->createForm(new \Ks\EventBundle\Form\EventType(null, $user, null, 'withNoImportedPlans'), $event);
        
        $responseDatas['graph'] = $this->render('KsCoachingBundle:Coaching:_coachingComparisonToolView.html.twig', array(
                'user'          => $user,
                'activityId'    => $activityId,
                'form'          => $coachingCategorySessionForm->createView(),
                'eventForm'     => $eventForm->createView()
                )
            )->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/updateSessionOnActivity/{activityId}", name = "ksCoaching_updateSessionOnActivity", requirements={"activityId" = "\d+"}, options={"expose"=true} )
     */
    public function updateSessionOnActivityAction($activityId)
    {
        $request                = $this->get('request');
        $em                     = $this->getDoctrine()->getEntityManager();
        $activityRep            = $em->getRepository('KsActivityBundle:Activity');
        $coachingSessionRep       = $em->getRepository('KsCoachingBundle:CoachingSession');
        
        $parameters = $request->request->all();
        
        $sessionId    = isset( $parameters['sessionId'] ) && $parameters['sessionId'] != "" ? $parameters['sessionId'] : null;
        
        if (is_null($sessionId)) {
            $responseDatas['update'] = -1;
        } 
        else {
            $activity = $activityRep->find($activityId);
            $event = $activity->getEvent();
            $coachingSession = $coachingSessionRep->find($sessionId);
            
            $event->setCoachingSession($coachingSession);
            $event->setCoachingCategory($coachingSession->getCategory());
            $em->persist( $event );
            $em->flush();

            $responseDatas['update'] = 1;
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    public function html_to_js_var($t){
        return str_replace('</script>','<\/script>',addslashes(str_replace("\r",'',str_replace("\n","",$t))));
    }
    
    public function var_to_js($jsname,$a, $array){
        $ret='';
        if (is_array($a) ) {
            if ($array) 
                $ret.=$jsname.'= new Array();
                ';
            else
                $ret.=$jsname.'= new Object();
                ';

            foreach ($a as $k => $a) {
                if (is_int($k) || is_integer($k))
                    $ret.= $this->var_to_js($jsname.'['.$k.']',$a, 0);
                else
                    $ret.= $this->var_to_js($jsname."['".$k."']",$a, 0);
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
    
    function dateTimeToSeconds(\DateTime $dateTime) {
         $hours     = $dateTime->format('H');
         $minutes   = $dateTime->format('i');
         $seconds   = $dateTime->format('s');
         
         return $seconds + 60 * $minutes + $hours * 60 * 60;
    }
    
    function secondesToTimeDuration($duration) {
        $heure = intval(abs($duration / 3600));
        $duration = $duration - ($heure * 3600);
        $minute = intval(abs($duration / 60));
        $duration = $duration - ($minute * 60);
        $seconde = round($duration);
        //$time = new \DateTime("$heure:$minute:$seconde");
        //$time = $heure."H ".$minute."' ".$seconde."''";
        if ($seconde ==0) $time = "$heure h $minute min";
        else $time = "$heure h $minute min $seconde";
        return $time;
    }
    
    function secondesToDecimalHours($duration) {
        return round($duration / 3600, 1);
    }
    
    /**
     * @Route("/getComputeSession", name = "ksCoaching_getComputeSession", options={"expose"=true}  )
     */
    public function getComputeSessionAction()
    {
        $json1 = '{"distance":[0,0.012,0.042,0.063,0.088,0.118,0.13,0.151,0.174,0.199,0.22227,0.241,0.274,0.304,0.333,0.358,0.37,0.392,0.414,0.436,0.458,0.48056,0.50286,0.529,0.553,0.56639,0.593,0.61442,0.641,0.667,0.688,0.7,0.723,0.75,0.77544,0.80936,0.847,0.85936,0.88336,0.914,0.934,0.963,0.985,1.014,1.035,1.066,1.088,1.119,1.14044,1.172,1.194,1.215,1.246,1.276,1.295,1.316,1.347,1.3691,1.4006,1.431,1.45355,1.483,1.515,1.535,1.5543,1.586,1.6181,1.63827,1.673,1.705,1.721,1.748,1.76827,1.798,1.817,1.849,1.878,1.897,1.919,1.94027,1.969,2.006,2.02527,2.057,2.0832,2.10633,2.127,2.149,2.16,2.184,2.209,2.231,2.264,2.289,2.301,2.313,2.336,2.33644,2.364,2.384,2.395,2.407,2.43,2.451,2.47,2.48027,2.508,2.539,2.57448,2.618,2.6424,2.675,2.719,2.748,2.788,2.814,2.839,2.876,2.9,2.92232,2.956,2.98,3.024,3.061,3.095,3.13,3.149,3.159,3.186,3.18609,3.206,3.214,3.23,3.238,3.247,3.26421,3.288,3.296,3.313,3.329,3.358,3.39124,3.42,3.45292,3.48532,3.52545,3.563,3.5896,3.636,3.6601,3.68036,3.718,3.744,3.778,3.80308,3.835,3.878,3.89832,3.92821,3.947,3.963,3.982,3.998,4.005,4.02,4.029,4.037,4.053,4.061,4.083,4.0907,4.105,4.12611,4.174,4.19532,4.233,4.269,4.294,4.32956,4.375,4.39589,4.427,4.456,4.479,4.516,4.563,4.597,4.63,4.662,4.696,4.714,4.731,4.747,4.75845,4.774,4.791,4.799,4.799,4.80828,4.832,4.838,4.85424,4.86932,4.886,4.905,4.933,4.965,4.998,5.02212,5.071,5.10652,5.14232,5.176,5.199,5.237,5.25154,5.28842,5.328,5.366,5.4012,5.441,5.48288,5.511,5.528,5.537,5.554,5.563,5.579,5.586,5.603,5.603,5.622,5.644,5.658,5.674,5.69,5.7,5.733,5.767,5.807,5.842,5.876,5.9137,5.951,5.983,6.005,6.034,6.0581,6.091,6.127,6.145,6.161,6.177,6.177,6.185,6.196,6.206,6.229,6.25,6.284,6.318,6.34033,6.375,6.398,6.40911,6.432,6.4575,6.489,6.50824,6.546,6.572,6.59,6.608,6.629,6.64,6.663,6.696,6.726,6.757,6.778,6.796,6.81509,6.841,6.86,6.877,6.903,6.923,6.943,6.96255,6.993,7.013,7.049,7.068,7.096,7.127,7.158,7.176,7.1944,7.227,7.246,7.265,7.284,7.312,7.333,7.363,7.383,7.403,7.431,7.452,7.472,7.503,7.524,7.554,7.574,7.59445,7.616,7.634,7.65499,7.683,7.7041,7.734,7.765,7.785,7.8043,7.83527,7.86371,7.89336,7.917,7.93143,7.95413,7.979,8.013,8.0252,8.048,8.0722,8.094,8.129,8.158,8.1785,8.208,8.238,8.2583,8.286,8.316,8.348,8.37,8.391,8.41,8.4323,8.461,8.482,8.501],"altitude":[[0,38],[0.012,39],[0.042,39],[0.063,39],[0.088,38],[0.118,38],[0.13,38],[0.151,38.0300032],[0.174,39],[0.199,39],[0.22227,39],[0.241,39],[0.274,39],[0.304,39],[0.333,39],[0.358,38],[0.37,38],[0.392,38],[0.414,39],[0.436,39],[0.458,39],[0.48056,38],[0.50286,37],[0.529,37],[0.553,37],[0.56639,37],[0.593,39],[0.61442,39],[0.641,39],[0.667,38],[0.688,38],[0.7,38],[0.723,38],[0.75,38],[0.77544,38.04],[0.80936,39],[0.847,39],[0.85936,39],[0.88336,39],[0.914,39],[0.934,39],[0.963,38],[0.985,39],[1.014,39],[1.035,38.9699968],[1.066,38],[1.088,39],[1.119,39],[1.14044,39],[1.172,39],[1.194,39],[1.215,38],[1.246,38],[1.276,38],[1.295,38],[1.316,39],[1.347,39],[1.3691,38],[1.4006,38],[1.431,38],[1.45355,38],[1.483,38],[1.515,38],[1.535,37],[1.5543,37],[1.586,38],[1.6181,38],[1.63827,38],[1.673,38],[1.705,38],[1.721,38],[1.748,38],[1.76827,38],[1.798,38],[1.817,37],[1.849,37],[1.878,38],[1.897,38],[1.919,38],[1.94027,37],[1.969,38],[2.006,37],[2.02527,34],[2.057,31],[2.0832,31],[2.10633,29],[2.127,28],[2.149,29],[2.16,29],[2.184,29],[2.209,28],[2.231,28],[2.264,28],[2.289,28],[2.301,27],[2.313,28],[2.336,29],[2.33644,32],[2.364,31],[2.384,30.9099904],[2.395,27],[2.407,27],[2.43,27],[2.451,27],[2.47,27],[2.48027,28],[2.508,28],[2.539,28],[2.57448,28],[2.618,28],[2.6424,28],[2.675,28],[2.719,28],[2.748,28],[2.788,28],[2.814,28],[2.839,28],[2.876,28],[2.9,28],[2.92232,28],[2.956,27],[2.98,27],[3.024,27],[3.061,27],[3.095,27],[3.13,28],[3.149,28],[3.159,28],[3.186,28],[3.18609,29],[3.206,29],[3.214,29],[3.23,28],[3.238,29],[3.247,29],[3.26421,28],[3.288,29],[3.296,29],[3.313,29],[3.329,29],[3.358,29],[3.39124,28],[3.42,29],[3.45292,29],[3.48532,28],[3.52545,28],[3.563,28],[3.5896,28],[3.636,28],[3.6601,28],[3.68036,28],[3.718,28],[3.744,28],[3.778,27],[3.80308,27],[3.835,28],[3.878,27],[3.89832,27],[3.92821,27],[3.947,28],[3.963,28],[3.982,28],[3.998,28],[4.005,28],[4.02,28],[4.029,28],[4.037,29],[4.053,29],[4.061,29],[4.083,29],[4.0907,29],[4.105,29],[4.12611,29],[4.174,29],[4.19532,29],[4.233,28],[4.269,28],[4.294,28],[4.32956,27],[4.375,27],[4.39589,27],[4.427,28],[4.456,28],[4.479,28],[4.516,27],[4.563,27],[4.597,27],[4.63,27],[4.662,28],[4.696,28],[4.714,28],[4.731,28],[4.747,28],[4.75845,28.0499968],[4.774,29],[4.791,29],[4.799,29],[4.799,29],[4.80828,28],[4.832,28],[4.838,28],[4.85424,28],[4.86932,29],[4.886,29],[4.905,29],[4.933,29],[4.965,29],[4.998,29],[5.02212,29],[5.071,28],[5.10652,28],[5.14232,28],[5.176,28],[5.199,28],[5.237,28],[5.25154,28],[5.28842,28],[5.328,27],[5.366,27],[5.4012,27],[5.441,27],[5.48288,28],[5.511,28],[5.528,28],[5.537,28],[5.554,28],[5.563,28],[5.579,29],[5.586,30],[5.603,30],[5.603,29],[5.622,29],[5.644,29],[5.658,30],[5.674,29],[5.69,28],[5.7,29],[5.733,29],[5.767,29],[5.807,28.970293767267],[5.842,28],[5.876,29],[5.9137,28],[5.951,28],[5.983,28],[6.005,28],[6.034,28],[6.0581,28],[6.091,28],[6.127,29],[6.145,32],[6.161,32],[6.177,32],[6.177,31],[6.185,31],[6.196,29],[6.206,28],[6.229,28],[6.25,29],[6.284,29],[6.318,30],[6.34033,30],[6.375,29],[6.398,28.9699968],[6.40911,28],[6.432,30],[6.4575,32],[6.489,32],[6.50824,37],[6.546,40],[6.572,40],[6.59,39],[6.608,39],[6.629,39],[6.64,39],[6.663,39],[6.696,40],[6.726,39],[6.757,39],[6.778,39],[6.796,39],[6.81509,39],[6.841,38],[6.86,39],[6.877,39],[6.903,39],[6.923,39],[6.943,39],[6.96255,38],[6.993,38],[7.013,38],[7.049,38],[7.068,38],[7.096,39],[7.127,38],[7.158,39],[7.176,39],[7.1944,39],[7.227,38],[7.246,39],[7.265,39],[7.284,39],[7.312,39],[7.333,40],[7.363,40],[7.383,40],[7.403,39],[7.431,38],[7.452,38],[7.472,38],[7.503,39],[7.524,40],[7.554,40],[7.574,40],[7.59445,39],[7.616,39],[7.634,39],[7.65499,39],[7.683,39],[7.7041,39],[7.734,39],[7.765,39],[7.785,39],[7.8043,39],[7.83527,40],[7.86371,40],[7.89336,40],[7.917,40],[7.93143,40],[7.95413,40],[7.979,41],[8.013,40],[8.0252,40],[8.048,41],[8.0722,40],[8.094,40],[8.129,40],[8.158,40],[8.1785,40],[8.208,39],[8.238,41],[8.2583,40],[8.286,39.9699968],[8.316,39],[8.348,41],[8.37,40],[8.391,40],[8.41,40],[8.4323,40],[8.461,40],[8.482,40],[8.501,41]],"speed":[[0,3.6],[0.012,7.92],[0.042,10.476],[0.063,10.8],[0.088,11.16],[0.118,11.16],[0.13,10.8],[0.151,10.08],[0.174,10.44],[0.199,10.44],[0.22227,10.44],[0.241,10.44],[0.274,10.8],[0.304,10.8],[0.333,11.16],[0.358,11.16],[0.37,10.8],[0.392,10.8],[0.414,10.8],[0.436,10.8],[0.458,11.16],[0.48056,11.16],[0.50286,11.16],[0.529,11.16],[0.553,10.8],[0.56639,11.16],[0.593,11.16],[0.61442,11.52],[0.641,11.52],[0.667,11.52],[0.688,11.52],[0.7,11.52],[0.723,11.16],[0.75,11.52],[0.77544,11.52],[0.80936,11.52],[0.847,11.52],[0.85936,11.52],[0.88336,11.52],[0.914,11.16],[0.934,11.16],[0.963,11.16],[0.985,11.52],[1.014,11.52],[1.035,11.88],[1.066,12.24],[1.088,12.276],[1.119,11.88],[1.14044,12.24],[1.172,12.24],[1.194,11.88],[1.215,11.88],[1.246,11.88],[1.276,11.88],[1.295,11.88],[1.316,11.88],[1.347,11.88],[1.3691,11.88],[1.4006,12.6],[1.431,12.24],[1.45355,12.24],[1.483,11.88],[1.515,11.88],[1.535,11.52],[1.5543,11.52],[1.586,11.88],[1.6181,11.88],[1.63827,11.88],[1.673,12.24],[1.705,12.564],[1.721,12.24],[1.748,11.88],[1.76827,11.52],[1.798,11.16],[1.817,11.16],[1.849,11.52],[1.878,11.52],[1.897,11.52],[1.919,11.52],[1.94027,11.52],[1.969,11.52],[2.006,11.88],[2.02527,11.88],[2.057,12.24],[2.0832,12.996],[2.10633,12.96],[2.127,11.52],[2.149,11.16],[2.16,10.44],[2.184,10.08],[2.209,10.44],[2.231,10.8],[2.264,11.16],[2.289,11.16],[2.301,10.8],[2.313,10.08],[2.336,8.28],[2.33644,7.92],[2.364,7.92],[2.384,8.28],[2.395,7.92],[2.407,7.92],[2.43,8.64],[2.451,8.64],[2.47,9],[2.48027,9],[2.508,9.36],[2.539,10.872],[2.57448,16.2],[2.618,16.164],[2.6424,14.4],[2.675,14.4],[2.719,15.12],[2.748,15.12],[2.788,15.48],[2.814,15.48],[2.839,14.76],[2.876,14.76],[2.9,14.004],[2.92232,13.68],[2.956,13.68],[2.98,13.32],[3.024,15.516],[3.061,15.84],[3.095,15.84],[3.13,16.2],[3.149,9],[3.159,5.04],[3.186,7.56],[3.18609,5.724],[3.206,6.804],[3.214,5.76],[3.23,6.12],[3.238,5.76],[3.247,5.76],[3.26421,5.76],[3.288,6.084],[3.296,6.084],[3.313,6.12],[3.329,6.48],[3.358,14.076],[3.39124,15.12],[3.42,14.4],[3.45292,14.796],[3.48532,15.84],[3.52545,16.2],[3.563,16.2],[3.5896,16.2],[3.636,15.12],[3.6601,14.004],[3.68036,13.32],[3.718,13.68],[3.744,14.04],[3.778,14.4],[3.80308,15.12],[3.835,14.76],[3.878,15.12],[3.89832,15.84],[3.92821,12.384],[3.947,8.28],[3.963,8.64],[3.982,6.84],[3.998,6.12],[4.005,5.76],[4.02,5.4],[4.029,5.76],[4.037,5.796],[4.053,5.76],[4.061,6.12],[4.083,6.12],[4.0907,5.76],[4.105,5.76],[4.12611,16.02],[4.174,16.56],[4.19532,13.32],[4.233,15.84],[4.269,15.48],[4.294,15.48],[4.32956,15.84],[4.375,15.12],[4.39589,13.284],[4.427,12.96],[4.456,13.32],[4.479,13.68],[4.516,14.04],[4.563,14.76],[4.597,15.12],[4.63,15.12],[4.662,15.84],[4.696,11.484],[4.714,7.776],[4.731,6.336],[4.747,6.12],[4.75845,6.12],[4.774,5.76],[4.791,5.76],[4.799,5.4],[4.799,5.76],[4.80828,5.4],[4.832,5.76],[4.838,5.76],[4.85424,5.76],[4.86932,5.76],[4.886,6.12],[4.905,10.44],[4.933,15.876],[4.965,15.84],[4.998,14.328],[5.02212,14.4],[5.071,15.84],[5.10652,16.2],[5.14232,16.2],[5.176,15.48],[5.199,13.716],[5.237,13.32],[5.25154,13.356],[5.28842,14.4],[5.328,14.4],[5.366,14.76],[5.4012,15.12],[5.441,15.12],[5.48288,15.48],[5.511,9.972],[5.528,7.92],[5.537,5.76],[5.554,5.4],[5.563,5.796],[5.579,5.76],[5.586,5.76],[5.603,5.76],[5.603,5.4],[5.622,5.4],[5.644,5.76],[5.658,5.76],[5.674,5.76],[5.69,5.76],[5.7,7.596],[5.733,12.96],[5.767,13.32],[5.807,14.508],[5.842,14.04],[5.876,14.04],[5.9137,16.2],[5.951,14.4],[5.983,11.16],[6.005,11.16],[6.034,11.88],[6.0581,13.32],[6.091,13.32],[6.127,13.32],[6.145,8.28],[6.161,6.12],[6.177,5.04],[6.177,4.284],[6.185,3.96],[6.196,3.96],[6.206,5.4],[6.229,6.876],[6.25,9.36],[6.284,10.836],[6.318,11.88],[6.34033,12.24],[6.375,12.204],[6.398,10.8],[6.40911,11.16],[6.432,10.44],[6.4575,10.8],[6.489,11.16],[6.50824,11.52],[6.546,11.88],[6.572,10.116],[6.59,10.44],[6.608,9.36],[6.629,9.36],[6.64,9.36],[6.663,9.72],[6.696,10.08],[6.726,10.44],[6.757,11.16],[6.778,11.16],[6.796,11.16],[6.81509,11.16],[6.841,11.16],[6.86,11.16],[6.877,11.16],[6.903,11.16],[6.923,11.16],[6.943,11.16],[6.96255,11.16],[6.993,11.52],[7.013,11.52],[7.049,11.52],[7.068,11.52],[7.096,11.16],[7.127,11.52],[7.158,11.88],[7.176,11.52],[7.1944,11.52],[7.227,11.52],[7.246,11.52],[7.265,11.52],[7.284,11.52],[7.312,11.52],[7.333,11.52],[7.363,11.16],[7.383,11.52],[7.403,11.52],[7.431,11.52],[7.452,11.52],[7.472,11.16],[7.503,11.52],[7.524,11.52],[7.554,11.88],[7.574,11.88],[7.59445,11.52],[7.616,11.52],[7.634,11.52],[7.65499,11.52],[7.683,11.52],[7.7041,11.52],[7.734,11.88],[7.765,11.88],[7.785,11.52],[7.8043,11.52],[7.83527,11.52],[7.86371,11.52],[7.89336,11.52],[7.917,11.52],[7.93143,11.52],[7.95413,11.52],[7.979,11.16],[8.013,11.88],[8.0252,11.52],[8.048,11.52],[8.0722,11.16],[8.094,10.8],[8.129,11.52],[8.158,11.52],[8.1785,11.52],[8.208,11.52],[8.238,11.52],[8.2583,11.52],[8.286,11.52],[8.316,11.52],[8.348,11.52],[8.37,11.52],[8.391,11.52],[8.41,11.52],[8.4323,11.844],[8.461,11.52],[8.482,11.88],[8.501,11.88]],"time":[[0,0],[0.012,1],[0.042,2],[0.063,1],[0.088,2],[0.118,1],[0.13,1],[0.151,2],[0.174,2],[0.199,1],[0.22227,1],[0.241,1],[0.274,2],[0.304,2],[0.333,1],[0.358,1],[0.37,1],[0.392,2],[0.414,1],[0.436,2],[0.458,1],[0.48056,2],[0.50286,2],[0.529,1],[0.553,1],[0.56639,1],[0.593,0],[0.61442,1],[0.641,2],[0.667,1],[0.688,2],[0.7,1],[0.723,1],[0.75,1],[0.77544,2],[0.80936,1],[0.847,1],[0.85936,1],[0.88336,2],[0.914,2],[0.934,1],[0.963,2],[0.985,1],[1.014,1],[1.035,1],[1.066,1],[1.088,1],[1.119,3],[1.14044,2],[1.172,1],[1.194,1],[1.215,2],[1.246,1],[1.276,1],[1.295,2],[1.316,1],[1.347,1],[1.3691,2],[1.4006,2],[1.431,1],[1.45355,2],[1.483,1],[1.515,2],[1.535,1],[1.5543,1],[1.586,2],[1.6181,3],[1.63827,1],[1.673,2],[1.705,1],[1.721,1],[1.748,2],[1.76827,2],[1.798,2],[1.817,1],[1.849,2],[1.878,3],[1.897,1],[1.919,1],[1.94027,2],[1.969,2],[2.006,2],[2.02527,1],[2.057,1],[2.0832,2],[2.10633,1],[2.127,1],[2.149,1],[2.16,1],[2.184,1],[2.209,2],[2.231,2],[2.264,1],[2.289,1],[2.301,1],[2.313,2],[2.336,5],[2.33644,2],[2.364,2],[2.384,1],[2.395,2],[2.407,2],[2.43,1],[2.451,1],[2.47,1],[2.48027,1],[2.508,2],[2.539,2],[2.57448,2],[2.618,3],[2.6424,2],[2.675,1],[2.719,2],[2.748,1],[2.788,1],[2.814,2],[2.839,1],[2.876,2],[2.9,2],[2.92232,2],[2.956,1],[2.98,1],[3.024,2],[3.061,2],[3.095,1],[3.13,1],[3.149,1],[3.159,3],[3.186,1],[3.18609,1],[3.206,2],[3.214,1],[3.23,2],[3.238,1],[3.247,1],[3.26421,2],[3.288,3],[3.296,1],[3.313,1],[3.329,2],[3.358,2],[3.39124,1],[3.42,1],[3.45292,3],[3.48532,2],[3.52545,1],[3.563,1],[3.5896,2],[3.636,3],[3.6601,2],[3.68036,1],[3.718,1],[3.744,1],[3.778,1],[3.80308,2],[3.835,1],[3.878,2],[3.89832,2],[3.92821,2],[3.947,3],[3.963,1],[3.982,2],[3.998,1],[4.005,1],[4.02,1],[4.029,1],[4.037,2],[4.053,2],[4.061,1],[4.083,1],[4.0907,2],[4.105,4],[4.12611,2],[4.174,1],[4.19532,2],[4.233,2],[4.269,2],[4.294,1],[4.32956,2],[4.375,2],[4.39589,2],[4.427,1],[4.456,1],[4.479,1],[4.516,2],[4.563,1],[4.597,1],[4.63,1],[4.662,1],[4.696,1],[4.714,2],[4.731,1],[4.747,2],[4.75845,2],[4.774,1],[4.791,2],[4.799,4],[4.799,1],[4.80828,2],[4.832,1],[4.838,2],[4.85424,1],[4.86932,2],[4.886,3],[4.905,1],[4.933,2],[4.965,1],[4.998,2],[5.02212,3],[5.071,1],[5.10652,2],[5.14232,3],[5.176,2],[5.199,1],[5.237,2],[5.25154,2],[5.28842,1],[5.328,1],[5.366,2],[5.4012,2],[5.441,1],[5.48288,2],[5.511,2],[5.528,1],[5.537,3],[5.554,2],[5.563,2],[5.579,1],[5.586,1],[5.603,1],[5.603,1],[5.622,2],[5.644,2],[5.658,1],[5.674,1],[5.69,2],[5.7,1],[5.733,2],[5.767,2],[5.807,1],[5.842,2],[5.876,3],[5.9137,2],[5.951,1],[5.983,2],[6.005,1],[6.034,0],[6.0581,2],[6.091,2],[6.127,1],[6.145,2],[6.161,3],[6.177,2],[6.177,2],[6.185,1],[6.196,2],[6.206,1],[6.229,1],[6.25,1],[6.284,2],[6.318,1],[6.34033,2],[6.375,2],[6.398,2],[6.40911,1],[6.432,2],[6.4575,2],[6.489,1],[6.50824,2],[6.546,2],[6.572,3],[6.59,2],[6.608,1],[6.629,1],[6.64,1],[6.663,1],[6.696,3],[6.726,1],[6.757,1],[6.778,1],[6.796,1],[6.81509,1],[6.841,1],[6.86,1],[6.877,1],[6.903,1],[6.923,2],[6.943,2],[6.96255,2],[6.993,1],[7.013,3],[7.049,2],[7.068,2],[7.096,1],[7.127,2],[7.158,2],[7.176,1],[7.1944,2],[7.227,1],[7.246,1],[7.265,1],[7.284,2],[7.312,1],[7.333,2],[7.363,1],[7.383,3],[7.403,1],[7.431,1],[7.452,1],[7.472,2],[7.503,2],[7.524,1],[7.554,1],[7.574,1],[7.59445,2],[7.616,1],[7.634,1],[7.65499,2],[7.683,1],[7.7041,2],[7.734,1],[7.765,1],[7.785,1],[7.8043,1],[7.83527,1],[7.86371,2],[7.89336,2],[7.917,1],[7.93143,2],[7.95413,1],[7.979,2],[8.013,1],[8.0252,2],[8.048,1],[8.0722,2],[8.094,1],[8.129,2],[8.158,1],[8.1785,2],[8.208,2],[8.238,1],[8.2583,1],[8.286,1],[8.316,2],[8.348,2],[8.37,2],[8.391,1],[8.41,2],[8.4323,1],[8.461,1],[8.482,2],[8.501,2]]}';
        $json2 = '{"distance":[0,0.012,0.042,0.063,0.088,0.118,0.13,0.151,0.174,0.199,0.22227,0.241,0.274,0.304,0.333,0.358,0.37,0.392,0.414,0.436,0.458,0.48056,0.50286,0.529,0.553,0.56639,0.593,0.61442,0.641,0.667,0.688,0.7,0.723,0.75,0.77544,0.80936,0.847,0.85936,0.88336,0.914,0.934,0.963,0.985,1.014,1.035,1.066,1.088,1.119,1.14044,1.172,1.194,1.215,1.246,1.276,1.295,1.316,1.347,1.3691,1.4006,1.431,1.45355,1.483,1.515,1.535,1.5543,1.586,1.6181,1.63827,1.673,1.705,1.721,1.748,1.76827,1.798,1.817,1.849,1.878,1.897,1.919,1.94027,1.969,2.006,2.02527,2.057,2.0832,2.10633,2.127,2.149,2.16,2.184,2.209,2.231,2.264,2.289,2.301,2.313,2.336,2.33644,2.364,2.384,2.395,2.407,2.43,2.451,2.47,2.48027,2.508,2.539,2.57448,2.618,2.6424,2.675,2.719,2.748,2.788,2.814,2.839,2.876,2.9,2.92232,2.956,2.98,3.024,3.061,3.095,3.13,3.149,3.159,3.186,3.18609,3.206,3.214,3.23,3.238,3.247,3.26421,3.288,3.296,3.313,3.329,3.358,3.39124,3.42,3.45292,3.48532,3.52545,3.563,3.5896,3.636,3.6601,3.68036,3.718,3.744,3.778,3.80308,3.835,3.878,3.89832,3.92821,3.947,3.963,3.982,3.998,4.005,4.02,4.029,4.037,4.053,4.061,4.083,4.0907,4.105,4.12611,4.174,4.19532,4.233,4.269,4.294,4.32956,4.375,4.39589,4.427,4.456,4.479,4.516,4.563,4.597,4.63,4.662,4.696,4.714,4.731,4.747,4.75845,4.774,4.791,4.799,4.799,4.80828,4.832,4.838,4.85424,4.86932,4.886,4.905,4.933,4.965,4.998,5.02212,5.071,5.10652,5.14232,5.176,5.199,5.237,5.25154,5.28842,5.328,5.366,5.4012,5.441,5.48288,5.511,5.528,5.537,5.554,5.563,5.579,5.586,5.603,5.603,5.622,5.644,5.658,5.674,5.69,5.7,5.733,5.767,5.807,5.842,5.876,5.9137,5.951,5.983,6.005,6.034,6.0581,6.091,6.127,6.145,6.161,6.177,6.177,6.185,6.196,6.206,6.229,6.25,6.284,6.318,6.34033,6.375,6.398,6.40911,6.432,6.4575,6.489,6.50824,6.546,6.572,6.59,6.608,6.629,6.64,6.663,6.696,6.726,6.757,6.778,6.796,6.81509,6.841,6.86,6.877,6.903,6.923,6.943,6.96255,6.993,7.013,7.049,7.068,7.096,7.127,7.158,7.176,7.1944,7.227,7.246,7.265,7.284,7.312,7.333,7.363,7.383,7.403,7.431,7.452,7.472,7.503,7.524,7.554,7.574,7.59445,7.616,7.634,7.65499,7.683,7.7041,7.734,7.765,7.785,7.8043,7.83527,7.86371,7.89336,7.917,7.93143,7.95413,7.979,8.013,8.0252,8.048,8.0722,8.094,8.129,8.158,8.1785,8.208,8.238,8.2583,8.286,8.316,8.348,8.37,8.391,8.41,8.4323,8.461,8.482,8.501],"altitude":[[0,38],[0.012,39],[0.042,39],[0.063,39],[0.088,38],[0.118,38],[0.13,38],[0.151,38.0300032],[0.174,39],[0.199,39],[0.22227,39],[0.241,39],[0.274,39],[0.304,39],[0.333,39],[0.358,38],[0.37,38],[0.392,38],[0.414,39],[0.436,39],[0.458,39],[0.48056,38],[0.50286,37],[0.529,37],[0.553,37],[0.56639,37],[0.593,39],[0.61442,39],[0.641,39],[0.667,38],[0.688,38],[0.7,38],[0.723,38],[0.75,38],[0.77544,38.04],[0.80936,39],[0.847,39],[0.85936,39],[0.88336,39],[0.914,39],[0.934,39],[0.963,38],[0.985,39],[1.014,39],[1.035,38.9699968],[1.066,38],[1.088,39],[1.119,39],[1.14044,39],[1.172,39],[1.194,39],[1.215,38],[1.246,38],[1.276,38],[1.295,38],[1.316,39],[1.347,39],[1.3691,38],[1.4006,38],[1.431,38],[1.45355,38],[1.483,38],[1.515,38],[1.535,37],[1.5543,37],[1.586,38],[1.6181,38],[1.63827,38],[1.673,38],[1.705,38],[1.721,38],[1.748,38],[1.76827,38],[1.798,38],[1.817,37],[1.849,37],[1.878,38],[1.897,38],[1.919,38],[1.94027,37],[1.969,38],[2.006,37],[2.02527,34],[2.057,31],[2.0832,31],[2.10633,29],[2.127,28],[2.149,29],[2.16,29],[2.184,29],[2.209,28],[2.231,28],[2.264,28],[2.289,28],[2.301,27],[2.313,28],[2.336,29],[2.33644,32],[2.364,31],[2.384,30.9099904],[2.395,27],[2.407,27],[2.43,27],[2.451,27],[2.47,27],[2.48027,28],[2.508,28],[2.539,28],[2.57448,28],[2.618,28],[2.6424,28],[2.675,28],[2.719,28],[2.748,28],[2.788,28],[2.814,28],[2.839,28],[2.876,28],[2.9,28],[2.92232,28],[2.956,27],[2.98,27],[3.024,27],[3.061,27],[3.095,27],[3.13,28],[3.149,28],[3.159,28],[3.186,28],[3.18609,29],[3.206,29],[3.214,29],[3.23,28],[3.238,29],[3.247,29],[3.26421,28],[3.288,29],[3.296,29],[3.313,29],[3.329,29],[3.358,29],[3.39124,28],[3.42,29],[3.45292,29],[3.48532,28],[3.52545,28],[3.563,28],[3.5896,28],[3.636,28],[3.6601,28],[3.68036,28],[3.718,28],[3.744,28],[3.778,27],[3.80308,27],[3.835,28],[3.878,27],[3.89832,27],[3.92821,27],[3.947,28],[3.963,28],[3.982,28],[3.998,28],[4.005,28],[4.02,28],[4.029,28],[4.037,29],[4.053,29],[4.061,29],[4.083,29],[4.0907,29],[4.105,29],[4.12611,29],[4.174,29],[4.19532,29],[4.233,28],[4.269,28],[4.294,28],[4.32956,27],[4.375,27],[4.39589,27],[4.427,28],[4.456,28],[4.479,28],[4.516,27],[4.563,27],[4.597,27],[4.63,27],[4.662,28],[4.696,28],[4.714,28],[4.731,28],[4.747,28],[4.75845,28.0499968],[4.774,29],[4.791,29],[4.799,29],[4.799,29],[4.80828,28],[4.832,28],[4.838,28],[4.85424,28],[4.86932,29],[4.886,29],[4.905,29],[4.933,29],[4.965,29],[4.998,29],[5.02212,29],[5.071,28],[5.10652,28],[5.14232,28],[5.176,28],[5.199,28],[5.237,28],[5.25154,28],[5.28842,28],[5.328,27],[5.366,27],[5.4012,27],[5.441,27],[5.48288,28],[5.511,28],[5.528,28],[5.537,28],[5.554,28],[5.563,28],[5.579,29],[5.586,30],[5.603,30],[5.603,29],[5.622,29],[5.644,29],[5.658,30],[5.674,29],[5.69,28],[5.7,29],[5.733,29],[5.767,29],[5.807,28.970293767267],[5.842,28],[5.876,29],[5.9137,28],[5.951,28],[5.983,28],[6.005,28],[6.034,28],[6.0581,28],[6.091,28],[6.127,29],[6.145,32],[6.161,32],[6.177,32],[6.177,31],[6.185,31],[6.196,29],[6.206,28],[6.229,28],[6.25,29],[6.284,29],[6.318,30],[6.34033,30],[6.375,29],[6.398,28.9699968],[6.40911,28],[6.432,30],[6.4575,32],[6.489,32],[6.50824,37],[6.546,40],[6.572,40],[6.59,39],[6.608,39],[6.629,39],[6.64,39],[6.663,39],[6.696,40],[6.726,39],[6.757,39],[6.778,39],[6.796,39],[6.81509,39],[6.841,38],[6.86,39],[6.877,39],[6.903,39],[6.923,39],[6.943,39],[6.96255,38],[6.993,38],[7.013,38],[7.049,38],[7.068,38],[7.096,39],[7.127,38],[7.158,39],[7.176,39],[7.1944,39],[7.227,38],[7.246,39],[7.265,39],[7.284,39],[7.312,39],[7.333,40],[7.363,40],[7.383,40],[7.403,39],[7.431,38],[7.452,38],[7.472,38],[7.503,39],[7.524,40],[7.554,40],[7.574,40],[7.59445,39],[7.616,39],[7.634,39],[7.65499,39],[7.683,39],[7.7041,39],[7.734,39],[7.765,39],[7.785,39],[7.8043,39],[7.83527,40],[7.86371,40],[7.89336,40],[7.917,40],[7.93143,40],[7.95413,40],[7.979,41],[8.013,40],[8.0252,40],[8.048,41],[8.0722,40],[8.094,40],[8.129,40],[8.158,40],[8.1785,40],[8.208,39],[8.238,41],[8.2583,40],[8.286,39.9699968],[8.316,39],[8.348,41],[8.37,40],[8.391,40],[8.41,40],[8.4323,40],[8.461,40],[8.482,40],[8.501,41]],"speed":[[0,3.6],[0.012,7.92],[0.042,10.476],[0.063,10.8],[0.088,11.16],[0.118,11.16],[0.13,10.8],[0.151,10.08],[0.174,10.44],[0.199,10.44],[0.22227,10.44],[0.241,10.44],[0.274,10.8],[0.304,10.8],[0.333,11.16],[0.358,11.16],[0.37,10.8],[0.392,10.8],[0.414,10.8],[0.436,10.8],[0.458,11.16],[0.48056,11.16],[0.50286,11.16],[0.529,11.16],[0.553,10.8],[0.56639,11.16],[0.593,11.16],[0.61442,11.52],[0.641,11.52],[0.667,11.52],[0.688,11.52],[0.7,11.52],[0.723,11.16],[0.75,11.52],[0.77544,11.52],[0.80936,11.52],[0.847,11.52],[0.85936,11.52],[0.88336,11.52],[0.914,11.16],[0.934,11.16],[0.963,11.16],[0.985,11.52],[1.014,11.52],[1.035,11.88],[1.066,12.24],[1.088,12.276],[1.119,11.88],[1.14044,12.24],[1.172,12.24],[1.194,11.88],[1.215,11.88],[1.246,11.88],[1.276,11.88],[1.295,11.88],[1.316,11.88],[1.347,11.88],[1.3691,11.88],[1.4006,12.6],[1.431,12.24],[1.45355,12.24],[1.483,11.88],[1.515,11.88],[1.535,11.52],[1.5543,11.52],[1.586,11.88],[1.6181,11.88],[1.63827,11.88],[1.673,12.24],[1.705,12.564],[1.721,12.24],[1.748,11.88],[1.76827,11.52],[1.798,11.16],[1.817,11.16],[1.849,11.52],[1.878,11.52],[1.897,11.52],[1.919,11.52],[1.94027,11.52],[1.969,11.52],[2.006,11.88],[2.02527,11.88],[2.057,12.24],[2.0832,12.996],[2.10633,12.96],[2.127,11.52],[2.149,11.16],[2.16,10.44],[2.184,10.08],[2.209,10.44],[2.231,10.8],[2.264,11.16],[2.289,11.16],[2.301,10.8],[2.313,10.08],[2.336,8.28],[2.33644,7.92],[2.364,7.92],[2.384,8.28],[2.395,7.92],[2.407,7.92],[2.43,8.64],[2.451,8.64],[2.47,9],[2.48027,9],[2.508,9.36],[2.539,10.872],[2.57448,16.2],[2.618,16.164],[2.6424,14.4],[2.675,14.4],[2.719,15.12],[2.748,15.12],[2.788,15.48],[2.814,15.48],[2.839,14.76],[2.876,14.76],[2.9,14.004],[2.92232,13.68],[2.956,13.68],[2.98,13.32],[3.024,15.516],[3.061,15.84],[3.095,15.84],[3.13,16.2],[3.149,9],[3.159,5.04],[3.186,7.56],[3.18609,5.724],[3.206,6.804],[3.214,5.76],[3.23,6.12],[3.238,5.76],[3.247,5.76],[3.26421,5.76],[3.288,6.084],[3.296,6.084],[3.313,6.12],[3.329,6.48],[3.358,14.076],[3.39124,15.12],[3.42,14.4],[3.45292,14.796],[3.48532,15.84],[3.52545,16.2],[3.563,16.2],[3.5896,16.2],[3.636,15.12],[3.6601,14.004],[3.68036,13.32],[3.718,13.68],[3.744,14.04],[3.778,14.4],[3.80308,15.12],[3.835,14.76],[3.878,15.12],[3.89832,15.84],[3.92821,12.384],[3.947,8.28],[3.963,8.64],[3.982,6.84],[3.998,6.12],[4.005,5.76],[4.02,5.4],[4.029,5.76],[4.037,5.796],[4.053,5.76],[4.061,6.12],[4.083,6.12],[4.0907,5.76],[4.105,5.76],[4.12611,16.02],[4.174,16.56],[4.19532,13.32],[4.233,15.84],[4.269,15.48],[4.294,15.48],[4.32956,15.84],[4.375,15.12],[4.39589,13.284],[4.427,12.96],[4.456,13.32],[4.479,13.68],[4.516,14.04],[4.563,14.76],[4.597,15.12],[4.63,15.12],[4.662,15.84],[4.696,11.484],[4.714,7.776],[4.731,6.336],[4.747,6.12],[4.75845,6.12],[4.774,5.76],[4.791,5.76],[4.799,5.4],[4.799,5.76],[4.80828,5.4],[4.832,5.76],[4.838,5.76],[4.85424,5.76],[4.86932,5.76],[4.886,6.12],[4.905,10.44],[4.933,15.876],[4.965,15.84],[4.998,14.328],[5.02212,14.4],[5.071,15.84],[5.10652,16.2],[5.14232,16.2],[5.176,15.48],[5.199,13.716],[5.237,13.32],[5.25154,13.356],[5.28842,14.4],[5.328,14.4],[5.366,14.76],[5.4012,15.12],[5.441,15.12],[5.48288,15.48],[5.511,9.972],[5.528,7.92],[5.537,5.76],[5.554,5.4],[5.563,5.796],[5.579,5.76],[5.586,5.76],[5.603,5.76],[5.603,5.4],[5.622,5.4],[5.644,5.76],[5.658,5.76],[5.674,5.76],[5.69,5.76],[5.7,7.596],[5.733,12.96],[5.767,13.32],[5.807,14.508],[5.842,14.04],[5.876,14.04],[5.9137,16.2],[5.951,14.4],[5.983,11.16],[6.005,11.16],[6.034,11.88],[6.0581,13.32],[6.091,13.32],[6.127,13.32],[6.145,8.28],[6.161,6.12],[6.177,5.04],[6.177,4.284],[6.185,3.96],[6.196,3.96],[6.206,5.4],[6.229,6.876],[6.25,9.36],[6.284,10.836],[6.318,11.88],[6.34033,12.24],[6.375,12.204],[6.398,10.8],[6.40911,11.16],[6.432,10.44],[6.4575,10.8],[6.489,11.16],[6.50824,11.52],[6.546,11.88],[6.572,10.116],[6.59,10.44],[6.608,9.36],[6.629,9.36],[6.64,9.36],[6.663,9.72],[6.696,10.08],[6.726,10.44],[6.757,11.16],[6.778,11.16],[6.796,11.16],[6.81509,11.16],[6.841,11.16],[6.86,11.16],[6.877,11.16],[6.903,11.16],[6.923,11.16],[6.943,11.16],[6.96255,11.16],[6.993,11.52],[7.013,11.52],[7.049,11.52],[7.068,11.52],[7.096,11.16],[7.127,11.52],[7.158,11.88],[7.176,11.52],[7.1944,11.52],[7.227,11.52],[7.246,11.52],[7.265,11.52],[7.284,11.52],[7.312,11.52],[7.333,11.52],[7.363,11.16],[7.383,11.52],[7.403,11.52],[7.431,11.52],[7.452,11.52],[7.472,11.16],[7.503,11.52],[7.524,11.52],[7.554,11.88],[7.574,11.88],[7.59445,11.52],[7.616,11.52],[7.634,11.52],[7.65499,11.52],[7.683,11.52],[7.7041,11.52],[7.734,11.88],[7.765,11.88],[7.785,11.52],[7.8043,11.52],[7.83527,11.52],[7.86371,11.52],[7.89336,11.52],[7.917,11.52],[7.93143,11.52],[7.95413,11.52],[7.979,11.16],[8.013,11.88],[8.0252,11.52],[8.048,11.52],[8.0722,11.16],[8.094,10.8],[8.129,11.52],[8.158,11.52],[8.1785,11.52],[8.208,11.52],[8.238,11.52],[8.2583,11.52],[8.286,11.52],[8.316,11.52],[8.348,11.52],[8.37,11.52],[8.391,11.52],[8.41,11.52],[8.4323,11.844],[8.461,11.52],[8.482,11.88],[8.501,11.88]],"time":[[0,0],[0.012,1],[0.042,2],[0.063,1],[0.088,2],[0.118,1],[0.13,1],[0.151,2],[0.174,2],[0.199,1],[0.22227,1],[0.241,1],[0.274,2],[0.304,2],[0.333,1],[0.358,1],[0.37,1],[0.392,2],[0.414,1],[0.436,2],[0.458,1],[0.48056,2],[0.50286,2],[0.529,1],[0.553,1],[0.56639,1],[0.593,0],[0.61442,1],[0.641,2],[0.667,1],[0.688,2],[0.7,1],[0.723,1],[0.75,1],[0.77544,2],[0.80936,1],[0.847,1],[0.85936,1],[0.88336,2],[0.914,2],[0.934,1],[0.963,2],[0.985,1],[1.014,1],[1.035,1],[1.066,1],[1.088,1],[1.119,3],[1.14044,2],[1.172,1],[1.194,1],[1.215,2],[1.246,1],[1.276,1],[1.295,2],[1.316,1],[1.347,1],[1.3691,2],[1.4006,2],[1.431,1],[1.45355,2],[1.483,1],[1.515,2],[1.535,1],[1.5543,1],[1.586,2],[1.6181,3],[1.63827,1],[1.673,2],[1.705,1],[1.721,1],[1.748,2],[1.76827,2],[1.798,2],[1.817,1],[1.849,2],[1.878,3],[1.897,1],[1.919,1],[1.94027,2],[1.969,2],[2.006,2],[2.02527,1],[2.057,1],[2.0832,2],[2.10633,1],[2.127,1],[2.149,1],[2.16,1],[2.184,1],[2.209,2],[2.231,2],[2.264,1],[2.289,1],[2.301,1],[2.313,2],[2.336,5],[2.33644,2],[2.364,2],[2.384,1],[2.395,2],[2.407,2],[2.43,1],[2.451,1],[2.47,1],[2.48027,1],[2.508,2],[2.539,2],[2.57448,2],[2.618,3],[2.6424,2],[2.675,1],[2.719,2],[2.748,1],[2.788,1],[2.814,2],[2.839,1],[2.876,2],[2.9,2],[2.92232,2],[2.956,1],[2.98,1],[3.024,2],[3.061,2],[3.095,1],[3.13,1],[3.149,1],[3.159,3],[3.186,1],[3.18609,1],[3.206,2],[3.214,1],[3.23,2],[3.238,1],[3.247,1],[3.26421,2],[3.288,3],[3.296,1],[3.313,1],[3.329,2],[3.358,2],[3.39124,1],[3.42,1],[3.45292,3],[3.48532,2],[3.52545,1],[3.563,1],[3.5896,2],[3.636,3],[3.6601,2],[3.68036,1],[3.718,1],[3.744,1],[3.778,1],[3.80308,2],[3.835,1],[3.878,2],[3.89832,2],[3.92821,2],[3.947,3],[3.963,1],[3.982,2],[3.998,1],[4.005,1],[4.02,1],[4.029,1],[4.037,2],[4.053,2],[4.061,1],[4.083,1],[4.0907,2],[4.105,4],[4.12611,2],[4.174,1],[4.19532,2],[4.233,2],[4.269,2],[4.294,1],[4.32956,2],[4.375,2],[4.39589,2],[4.427,1],[4.456,1],[4.479,1],[4.516,2],[4.563,1],[4.597,1],[4.63,1],[4.662,1],[4.696,1],[4.714,2],[4.731,1],[4.747,2],[4.75845,2],[4.774,1],[4.791,2],[4.799,4],[4.799,1],[4.80828,2],[4.832,1],[4.838,2],[4.85424,1],[4.86932,2],[4.886,3],[4.905,1],[4.933,2],[4.965,1],[4.998,2],[5.02212,3],[5.071,1],[5.10652,2],[5.14232,3],[5.176,2],[5.199,1],[5.237,2],[5.25154,2],[5.28842,1],[5.328,1],[5.366,2],[5.4012,2],[5.441,1],[5.48288,2],[5.511,2],[5.528,1],[5.537,3],[5.554,2],[5.563,2],[5.579,1],[5.586,1],[5.603,1],[5.603,1],[5.622,2],[5.644,2],[5.658,1],[5.674,1],[5.69,2],[5.7,1],[5.733,2],[5.767,2],[5.807,1],[5.842,2],[5.876,3],[5.9137,2],[5.951,1],[5.983,2],[6.005,1],[6.034,0],[6.0581,2],[6.091,2],[6.127,1],[6.145,2],[6.161,3],[6.177,2],[6.177,2],[6.185,1],[6.196,2],[6.206,1],[6.229,1],[6.25,1],[6.284,2],[6.318,1],[6.34033,2],[6.375,2],[6.398,2],[6.40911,1],[6.432,2],[6.4575,2],[6.489,1],[6.50824,2],[6.546,2],[6.572,3],[6.59,2],[6.608,1],[6.629,1],[6.64,1],[6.663,1],[6.696,3],[6.726,1],[6.757,1],[6.778,1],[6.796,1],[6.81509,1],[6.841,1],[6.86,1],[6.877,1],[6.903,1],[6.923,2],[6.943,2],[6.96255,2],[6.993,1],[7.013,3],[7.049,2],[7.068,2],[7.096,1],[7.127,2],[7.158,2],[7.176,1],[7.1944,2],[7.227,1],[7.246,1],[7.265,1],[7.284,2],[7.312,1],[7.333,2],[7.363,1],[7.383,3],[7.403,1],[7.431,1],[7.452,1],[7.472,2],[7.503,2],[7.524,1],[7.554,1],[7.574,1],[7.59445,2],[7.616,1],[7.634,1],[7.65499,2],[7.683,1],[7.7041,2],[7.734,1],[7.765,1],[7.785,1],[7.8043,1],[7.83527,1],[7.86371,2],[7.89336,2],[7.917,1],[7.93143,2],[7.95413,1],[7.979,2],[8.013,1],[8.0252,2],[8.048,1],[8.0722,2],[8.094,1],[8.129,2],[8.158,1],[8.1785,2],[8.208,2],[8.238,1],[8.2583,1],[8.286,1],[8.316,2],[8.348,2],[8.37,2],[8.391,1],[8.41,2],[8.4323,1],[8.461,1],[8.482,2],[8.501,2]]}';
        
        return $this->render('KsCoachingBundle:Coaching:computeSessions.html.twig', array(
            "session1"                     => $json1,
            "session2"                     => $json2
        ));
    }
 
    
    /**
     * @Route("/printWeek/{planId}/{startOn}_{endOn}", requirements={"planId" = "\d+"}, name = "ksCoaching_printWeek", options={"expose"=true} )
     */
    public function printWeekAction($planId, $startOn, $endOn) {
        $em                             = $this->getDoctrine()->getEntityManager();
        $agendaRep                      = $em->getRepository('KsAgendaBundle:Agenda');
        $coachingPlanRep                = $em->getRepository('KsCoachingBundle:CoachingPlan');
        
        $plan = $coachingPlanRep->find($planId);
        if (!is_null($plan->getUser())) $userIdForFlatView = $plan->getUser()->getId();
        else $userIdForFlatView = null;
            
        if ($startOn == null || $startOn == 'null') {
            $startOn = "";
        }
            //$now = new \DateTime();
            //$startOn = $now->format("Y-m-01");
        
        if ($endOn == null || $endOn == 'null') {
            $endOn = "";
        }
        
        $events = $agendaRep->findAgendaEvents(array(
            "planId"                    => $planId,
            //"getUserSessionForFlatView" => $getUserSessionForFlatView,
            "userIdForFlatView"         => $userIdForFlatView,
            "order"                     => array("DATE(e.startDate)" => "ASC"),
            "startOn"                   => $startOn,
            "endOn"                     => $endOn
        ), $this->get('translator'));
        
        $startOnDate = new \DateTime($startOn);
        $endOnDate = new \DateTime($endOn);
        
        return $this->render('KsCoachingBundle:Coaching:printWeek.html.twig', array(
            "planId"        => $planId,
            "planLabel"     => $plan->getName(),
            "weekNumber"    => $startOnDate->format("W"),
            "startOn"       => $startOn,
            "endOn"         => $endOn,
            "startOnDate"   => $startOnDate->format("d/m/y"),
            "endOnDate"     => $endOnDate->format("d/m/y"),
            "sessions"      => $events
        ));
    }
}