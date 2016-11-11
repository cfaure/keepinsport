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
     * @Route("/weeklyUpdate", name = "ksActivity_weeklyUpdate", options={"expose"=true} ) 
     */
    public function weeklyUpdateLeagueLeagueAction() { 
        $request        = $this->getRequest();
        if ($request->query->has('error')) {
            // TODO: gérer le message flash ou faire une page de rendu
            return $this->redirect($this->generateUrl('_ks_index'));
        }
        $rkApi              = $this->get('ks_league.leagueLevelService');
        $authCode           = $request->query->get('code');
        $redirectUri        = $this->generateUrl('service_authWithRunkeeper', array(), true);
        try {
            $accessToken    = $rkApi->getAccessToken($authCode, $redirectUri);
        } catch (Exception $e) {
            // TODO: faire quelque chose de plus intelligent
            throw $e;
        }
        $user               = $this->get('security.context')->getToken()->getUser();
        // Save runkeeper's access token to user's profile
//        $user->getDetails()->setRunkeeperAccessToken($accessToken);
//        $user->persist();
//        $this->em->flush();
        return array(
            'accessToken'   => $accessToken
        );
    }
    
    /**
     * @Route("/leaguelevelservice", name="service_league_level")
     * @Template
     */
    public function leaguelevelserviceAction()
    {   
        // get runkeeper's access token from authenticated user
        //$this->get('ks_job.background_process')->queueJob('ks:user:runkeepersync 103f7bdd2c7c405aa352be1e5b4aff4d');
        $this->get('ks_job.background_process')->queueJob(
            'ks_league.leagueLevelService',
            'weeklyUpdate'
            );
                
        return array();
    }
}