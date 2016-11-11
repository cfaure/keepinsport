<?php
/**
 * Description of GarminController
 *
 * @author Clem
 */
namespace Ks\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GarminController extends Controller
{
    /**
     * @Route("/index", name="ksActivity_garminIndex")
     */
    public function indexAction()
    {
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'sportif');
        
        return $this->render('KsActivityBundle:Activity:garmin.html.twig');
    }
    
    /**
     * @Route("/import", name="ksActivity_garminImport", options={"expose"=true})
     */
    public function importAction()
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $importService  = $this->get('ks_activity.importActivityService');
        $user           = $this->get('security.context')->getToken()->getUser();   
        $activityGarmin = $this->getRequest()->get('activity');
        list($activityDatas, $error) = $importService->buildJsonToSave($user, array('xml' => $activityGarmin), 'garmin');
        $session        = $importService->saveUserSessionFromActivityDatas($activityDatas, $user);
        
        // Calcul des points de l'activité
        $leagueLevelService = $this->get('ks_league.leagueLevelService');
        $leagueLevelService->activitySessionEarnPoints($session, $user);
        
        // Flush de la session doctrine
        $em->flush();
        
        // Message flash après le redirect js
        $responseText = 'Ta séance de '.$session->getSport()->getLabel()
            .' du '.$session->getIssuedAt()->format("d/m/Y")
            .' a été importée avec succès.';
        $this->get('session')->setFlash('alert alert-info', $responseText);
        
        $response = new Response(json_encode(array(
            'text' => $responseText
        )));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * 
     * @Route("/configureGarminAccount", name = "ksActivity_configureGarminAccount", options={"expose"=true} )
     */
    public function configureGarminAccountAction()
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $user           = $this->get('security.context')->getToken()->getUser();
        $serviceRep     = $em->getRepository('KsUserBundle:Service');
        $request        = $this->get('request');
        $parameters     = $request->request->all();
        $responseDatas  = array();
        $service        = $serviceRep->findOneByName("Garmin");
        
        if (!is_object($service) ) {
            throw $this->createNotFoundException("Impossible de trouver le service 'Garmin' ");
        }
                
        //On récupère les infos du compte endomondo
        $mail   = isset($parameters["mailGarmin"]) ? $parameters["mailGarmin"] : '' ;
        $passwd = isset($parameters["mdpGarmin"])  ? $parameters["mdpGarmin"]  : '' ;
        
        $credentials = array(
            'username'   => $mail,
            'password'   => $passwd,
            'identifier' => $user->getId()
        );

        try {
            $garminApi = new \dawguk\GarminConnect($credentials);
            
            $responseDatas["configureResponse"] = 1;
            
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
            $userHasService->setConnectionId($mail);
            $userHasService->setToken('');
            $userHasService->setConnectionPassword(base64_encode($passwd));
            $userHasService->setCollectedActivities(json_encode(array()));
            //$userHasService->setStatus('pending');
            $em->persist($userHasService);
            $em->flush();
                    
            // On coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')
                ->checkParamService($user->getId());
            
            $this->get('session')->setFlash(
                'alert alert-success',
                'Le service Garmin a été configuré avec succès !'
            );
            
        } catch (\dawguk\AuthenticationException $e) {
            var_dump("L'adresse mail et le mot de passe ne correspondent pas à un compte Garmin valide...");exit;
            $responseDatas["configureResponse"] = -1;
            $responseDatas["errorMessage"]      = "L'adresse mail ou pseudo et le mot de passe ne correspondent pas à un compte Garmin valide...";
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
}
