<?php

namespace Ks\TournamentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MatchController extends Controller
{
    
    
    /**
     * @Route("/{id}/getMatchForm", name = "ksTournamentMatch_getMatchForm", options={"expose"=true} )
     * @ParamConverter("match", class="KsTournamentBundle:Match")
     */
    public function getMatchFormAction( \Ks\TournamentBundle\Entity\Match $match )
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();

        $club = $match->getRound()->getTournament()->getClub();
        $matchForm = $this->createForm(new \Ks\TournamentBundle\Form\MatchType( $club ), $match );
 
        $responseDatas = array(
            "html" => $this->render('KsTournamentBundle:Match:_matchForm.html.twig', array(
                'match' => $match,
                'form'  => $matchForm->createView(),
            ))->getContent()
        );
        
        //var_dump($responseDatas);
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{id}/updateMatch", name = "ksTournamentMatch_updateMatch", options={"expose"=true} )
     * @ParamConverter("match", class="KsTournamentBundle:Match")
     */
    public function updateMatchAction( \Ks\TournamentBundle\Entity\Match $match )
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $tournamentMatchRep = $em->getRepository('KsTournamentBundle:Match');
        
        if( is_object( $match) ) {
            $club = $match->getRound()->getTournament()->getClub();
            $matchForm = $this->createForm(new \Ks\TournamentBundle\Form\MatchType( $club ), $match );
            $formHandler = new \Form\FormHandler( $matchForm, $request, $em);

            $responseDatas = $formHandler->process();

            //Si le match a été modifié
            if ($responseDatas['code'] == 1) {

            }
        } else {
            $responseDatas = array(
                "code"          => -1,
                "errorMessage" => "Impossible de trouver le match"
            );
        }
        
        $responseDatas['html'] = $this->render('KsTournamentBundle:Match:_match.html.twig', array(
                'match' => $tournamentMatchRep->findOneTournamentMatch( array( "tournamentMatchId" => $match->getId() ) )
            ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
}
