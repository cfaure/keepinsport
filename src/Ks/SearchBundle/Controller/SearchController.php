<?php

namespace Ks\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
{
    /**
      * @Route("/", name = "ksSearch", options={"expose"=true} )
      */
    public function searchAction() {
        $securityContext = $this->container->get('security.context');
        $em              = $this->getDoctrine()->getEntityManager();
        $userRep         = $em->getRepository('KsUserBundle:User');
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $this->get('security.context')->getToken()->getUser();
        }
        else {
            $user = $userRep->find(1);
        }
        
        //Services
        $searchService   = $this->get('ks_searchService');
                
        $request = $this->getRequest();
        
       
        
        //Paramètres GET
        $parameters = $request->query->all();
        
        //Paramètres POST
        //$parameters = $request->request->all();
        
        $term    = isset ( $parameters['term'] ) ? $parameters['term'] : "";
        
        $results = $searchService->findResults(array(
            "term"      => $term,
            "userId"    => $user->getId()
        ));
        
        $responseDatas = array();
        $responseDatas['results'] = $results;

        $response = new Response(json_encode($responseDatas));

        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }
}
