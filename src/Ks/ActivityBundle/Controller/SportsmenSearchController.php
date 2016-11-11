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
/**
 * SportsmenSerach controller.
 *
 */
class SportsmenSearchController extends Controller
{
    /**
     * @Route("/", name = "ksSportsmenSearch_list", options={"expose"=true} )
     * @Template()
     */
    public function listAction()
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        
        $activities = $activityRep->findActivities(array(
            'activityTypes'     => array("sportsmen_search"),
        ));
        
        return array(
            'activities'        => $activities,
        );
    }
    /**
     * @Route("/publishSportsmenSearch", name = "ksSportsmenSearch_publishSportsmenSearch", options={"expose"=true} )
     */
    public function publishSportsmenSearchAction()
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $user               = $this->get('security.context')->getToken()->getUser();
        $sportsmenSearch    = new \Ks\ActivityBundle\Entity\SportsmenSearch($user);

        $form = $this->createForm(new \Ks\ActivityBundle\Form\SportsmenSearchType($user), $sportsmenSearch);
        $formHandler = new \Ks\ActivityBundle\Form\SportsmenSearchHandler($form, $request, $em, $this->container);

        $responseDatas = $formHandler->process();
        
        //Si l'activité a été publié
        if ($responseDatas['response'] == 1) {
            //On abonne l'utilisateur à l'activité
            $activityRep->subscribeOnActivity($responseDatas['sportsmenSearch'], $user);
            $activityDatas = $activityRep->findActivities(array(
                'activityId' => $responseDatas['sportsmenSearch']->getId()
            ));
            
            //on coche l'action correspondante dans la checklist
            //$em->getRepository('KsUserBundle:ChecklistAction')->checkPublishStatusPhotoVideo($user->getId());
            
            $responseDatas['html'] = $this->render(
                'KsActivityBundle:Activity:_activity.html.twig', 
                $activityDatas
            )->getContent();
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    public function menuItemAction()
    {    
        $securityContext    = $this->container->get('security.context');
        $em                 = $this->getDoctrine()->getEntityManager();
        
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user               = $securityContext->getToken()->getUser();
            $sportsmenSearch        = new \Ks\ActivityBundle\Entity\SportsmenSearch($user);
            $sportsmenSearchForm    = $this->createForm(new \Ks\ActivityBundle\Form\SportsmenSearchType($user), $sportsmenSearch);
        }
        
        return $this->render('KsActivityBundle:SportsmenSearch:_menuItem.html.twig', array(
            'sportsmenSearchForm'   => isset( $sportsmenSearchForm) ? $sportsmenSearchForm->createView() : null,
        ));
    }
    
    public function formAction()
    {    
        $securityContext    = $this->container->get('security.context');
        $em                 = $this->getDoctrine()->getEntityManager();
        
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user               = $securityContext->getToken()->getUser();
            $sportsmenSearch        = new \Ks\ActivityBundle\Entity\SportsmenSearch($user);
            $sportsmenSearchForm    = $this->createForm(new \Ks\ActivityBundle\Form\SportsmenSearchType($user), $sportsmenSearch);
        }
        
        return $this->render('KsActivityBundle:SportsmenSearch:_sportsmenSearchForm.html.twig', array(
            'form'   => isset( $sportsmenSearchForm) ? $sportsmenSearchForm->createView() : null,
        ));
    }
}
