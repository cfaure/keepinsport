<?php
namespace Ks\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SportingActivityController extends Controller
{
    /**
     * @Route("/localisations", name="ksSportingActivities_localisations", options={"expose"=true} )
     * @Template()
     */
    public function localisationsAction()
    {
        return array(
            
        );
    }
    
    /**
     * @Route("/search", name = "ksSportingActivities_search", options={"expose"=true} )
     */
    public function searchUsersAction()
    {
        $securityContext    = $this->container->get('security.context');
        $user               = $securityContext->getToken()->getUser();
        
        $em                 = $this->getDoctrine()->getEntityManager();   
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        
        $request            = $this->getRequest();
        $parameters         = $request->request->all();

        $responseDatas = array(
           "code" => 1
        );
        
        $searchTerms    = $parameters["terms"] != "" ? explode(' ', $parameters["terms"]) : array();
        $activitiesFrom = isset( $parameters["activitiesFrom"] ) && is_array( $parameters["activitiesFrom"] ) ? $parameters["activitiesFrom"] : array();
        $sportId          = $parameters["sportId"] == "" ? null : $parameters["sportId"];
        
        /*$params = array(
            'user'              => $user,
            //'extendedSearch'    => true,
            //'searchTerms'       => $searchTerms,
            'getExtraDatas'     => false,
            'activitiesTypes'   => array("session"),
            'activitiesFrom'    => $activitiesFrom,
            'withLocalisation'  => true
        );
        
        $activities = $activityRep->findActivities($params);  */
        
        $params = array(
            'user'              => $user,
            'activitiesFrom'    => $activitiesFrom,
            'sportId'           => $sportId
        );

        $activities = $activityRep->findLocalisedSportingActivities($params);  
       
        $responseDatas["activities"] = $activities;
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/loadGoogleMapBubbleContent/{activityId}", name = "ksSportingActivities_loadGoogleMapBubbleContent", options={"expose"=true} )
     */
    public function loadGoogleMapBubbleContentAction($activityId)
    {
        $securityContext    = $this->container->get('security.context');
        $user               = $securityContext->getToken()->getUser();
        
        $em                 = $this->getDoctrine()->getEntityManager();   
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        
        $request            = $this->getRequest();
        $parameters         = $request->request->all();

        $responseDatas = array(
           "code" => 1
        );
        
        $activityDatas = $activityRep->findActivities(array(
            'activityId' => $activityId
        ));  
        //var_dump($activity);
        $responseDatas["html"] = $this->render('KsActivityBundle:Activity:_activityBloc.html.twig', array(
            'isShared'          => true,
            'activity'          => $activityDatas["activity"],
            'photos'            => $activityDatas["photos"],
            'comments'          => $activityDatas["comments"],
            'activityTeamates'  => $activityDatas["activityTeamates"],
            'activityOpponents' => $activityDatas["activityOpponents"],
            'connectedActivity' => $activityDatas["connectedActivity"],
            'activityScores'    => $activityDatas["activityScores"],
            'clubManagers'      => $activityDatas["clubManagers"]
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
}
