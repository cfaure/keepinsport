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
 * Polar controller.
 *
 * 
 */
class PolarController extends Controller
{
    /**
     * 
     * @Route("/configurePolarAccount", name = "ksActivity_configurePolarAccount", options={"expose"=true} )
     */
    public function configurePolarAccountAction()
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $user           = $this->get('security.context')->getToken()->getUser();
        $serviceRep     = $em->getRepository('KsUserBundle:Service');
        $request        = $this->get('request');
        $parameters     = $request->request->all();
        $responseDatas  = array();
        $polarApi       = $this->get('ks_user.polar');
        
        //On récupère les infos du compte polar
        $mailPolar  = isset($parameters["mailPolar"])   ? $parameters["mailPolar"]  : '' ;
        $mdpPolar   = isset($parameters["mdpPolar"])    ? $parameters["mdpPolar"]   : '' ;
        
        if (!$user) {
            throw new Exception('Utilisateur non connecté');
        }
        
        if ($polarApi->authorize($mailPolar, $mdpPolar)) {
            $responseDatas["configureResponse"] = 1;
            
            $service = $serviceRep->findOneByName("Polar");
            if (!is_object($service) ) {
                throw $this->createNotFoundException("Impossible de trouver le service 'Polar' ");
            }

            //On regarde si le service existe déjà pour cet utilisateur
            $userHasService = $em
                ->getRepository('KsUserBundle:UserHasServices')
                ->findOneBy(array('service' => $service->getId(), 'user' => $user->getId()));
            
            if (!is_object($userHasService)) {
                $userHasService = new \Ks\UserBundle\Entity\UserHasServices();
            }
            
            $userHasService->setIsActive(true);
            $userHasService->setSyncServiceToUser(true);
            $userHasService->setUserSyncToService(false);
            $userHasService->setFirstSync(true);
            $userHasService->setUser($user);
            $userHasService->setService($service);
            $userHasService->setConnectionId($mailPolar);
            $userHasService->setConnectionPassword(base64_encode($mdpPolar));
            $userHasService->setCollectedActivities(json_encode(array()));
            $userHasService->setStatus('pending');
            $em->persist($userHasService);
            $em->flush();
                    
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')
                ->checkParamService($user->getId());
            
            $this->get('session')->setFlash(
                'alert alert-success',
                'Le service Polar a été configuré avec succès !'
            );

        } else {
            $responseDatas["configureResponse"] = -1;
            $responseDatas["errorMessage"]      = "L'adresse mail et le mot de passe ne correspondent pas à un compte Polar valide...";
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
}
