<?php
namespace Ks\TournamentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;


class ActivityController extends Controller
{
    /**
     * @Route("/{id}/activitySessionForm", name = "ksTournamentActivity_activitySessionForm")
     * @ParamConverter("tournament", class="KsTournamentBundle:Tournament")
     */
    public function activitySessionFormAction( \Ks\TournamentBundle\Entity\Tournament $tournament ) {
        $em             = $this->getDoctrine()->getEntityManager();
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $club = $tournament->getClub();
        
        $activitySession = new \Ks\ActivityBundle\Entity\ActivitySession( $user );
        $activitySession->setUser( $user );
        $activitySession->setClub( $club );
        $activitySession->setTournament( $tournament );
        
        $sport = $tournament->getSport();
        //if( is_object( $sport ) ) {
            $activitySession->setSport( $sport );
        //}
        
        $activitySportChoiceForm = $this->createForm( new \Ks\ActivityBundle\Form\SportType(null), $activitySession );
        
        return $this->render('KsActivityBundle:Sport:activitySessionForm.html.twig', array(
             'activitySportChoiceForm' => $activitySportChoiceForm->createView(),
             'club'              => $club,
             'tournament'     => $tournament
        )); 
    }
}