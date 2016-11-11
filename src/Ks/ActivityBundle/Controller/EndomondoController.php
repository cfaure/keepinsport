<?php

namespace Ks\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Endomondo controller.
 *
 * 
 */
class EndomondoController extends Controller
{
    /**
     * 
     * @Route("/configureEndomondoAccount", name = "ksActivity_configureEndomondoAccount", options={"expose"=true} )
     */
    public function configureEndomondoAccountAction()
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $user           = $this->get('security.context')->getToken()->getUser();
        $serviceRep     = $em->getRepository('KsUserBundle:Service');
        $request        = $this->get('request');
        $parameters     = $request->request->all();
        $responseDatas  = array();
        $endomondoApi   = $this->get('ks_user.endomondo');
        
        //On récupère les infos du compte endomondo
        $mailEndomondo  = isset($parameters["mailEndomondo"])   ? $parameters["mailEndomondo"]  : '' ;
        $mdpEndomondo   = isset($parameters["mdpEndomondo"])    ? $parameters["mdpEndomondo"]   : '' ;
        
        if (!$user) {
            throw new Exception('Utilisateur non connecté');
        }
        if ($endomondoApi->authenticate($mailEndomondo, $mdpEndomondo)) {
            $responseDatas["configureResponse"] = 1;
            
            $service = $serviceRep->findOneByName("Endomondo");
            if (!is_object($service) ) {
                throw $this->createNotFoundException("Impossible de trouver le service 'Endomondo' ");
            }

            //On regarde si le service existe déjà pour cet utilisateur
            $userHasService = $em
                ->getRepository('KsUserBundle:UserHasServices')
                ->findOneBy(array('service' => $service->getId(), 'user' => $user->getId()));
            
            if (!is_object($userHasService)) {
                $userHasService = new \Ks\UserBundle\Entity\UserHasServices();
            }
            
            $accessToken = $endomondoApi->getAuthToken();

            $userHasService->setIsActive(true);
            $userHasService->setSyncServiceToUser(true);
            $userHasService->setUserSyncToService(false);
            $userHasService->setFirstSync(true);
            $userHasService->setUser($user);
            $userHasService->setService($service);
            $userHasService->setConnectionId($mailEndomondo);
            $userHasService->setToken($accessToken);
            $userHasService->setConnectionPassword('');
            $userHasService->setCollectedActivities(json_encode(array()));
            $userHasService->setStatus('pending');
            $em->persist($userHasService);
            $em->flush();
                    
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')
                ->checkParamService($user->getId());
            
            //FIXME: code en double !! (voir ServiceController)
            $jobPath = $this->container->get('kernel')->getRootdir().'/jobs/';
            
            if (file_exists($jobPath.'pending/endomondo/'.$accessToken.'.job')){
                    $responseDatas["errorMessage"] = "Vous avez déjà effectué une demande de synchronisation de vos activités, le traitement est en cours veuillez patienter";
            } else {
                $file = fopen($jobPath.'pending/endomondo/'.$accessToken.'.job', "a+");
                fwrite($file, "php ".$this->container->get('kernel')->getRootdir()."/console ks:user:endomondosync $accessToken");
                fclose($file);
            }
            
            // FIN code en double
            
            $this->get('session')->setFlash(
                'alert alert-success',
                'Le service Endomondo a été configuré avec succès. La synchronisation de tes activités est en cours de traitement !'
            );

        } else {
            $responseDatas["configureResponse"] = -1;
            $responseDatas["errorMessage"]      = "L'adresse mail et le mot de passe ne correspondent pas à un compte Endomondo valide...";
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
}
