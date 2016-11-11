<?php

namespace Ks\TournamentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TournamentController extends Controller
{
    /**
     * @Route("/", name = "ksTournament_index", options={"expose"=true})
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
    
    /**
     * @Route("/{id}/show", name = "ksTournament_show", options={"expose"=true})
     * @Template()
     */
    public function showAction($id)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $tournamentRep  = $em->getRepository('KsTournamentBundle:Tournament');
        
        $tournament = $tournamentRep->findOneTournament(array(
            "tournamentId" => $id
        ));
        
        if ( $tournament == null ) {
            $this->get('session')->setFlash('alert alert-error', "Impossible de trouver le tournoi");
            return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }
        
        //var_dump($tournament);
        
        return array(
            "tournament" => $tournament
        );
    }
    
    /**
     * @Route("/{id}/getPodiumForm", name = "ksTournament_getPodiumForm", options={"expose"=true} )
     * @ParamConverter("tournament", class="KsTournamentBundle:Tournament")
     */
    public function getPodiumFormAction( \Ks\TournamentBundle\Entity\Tournament $tournament )
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();

        $podiumForm = $this->createForm(new \Ks\TournamentBundle\Form\PodiumType( $tournament->getClub() ), $tournament );
 
        $responseDatas = array(
            "html" => $this->render('KsTournamentBundle:Tournament:_podiumForm.html.twig', array(
                'tournament' => $tournament,
                'form'  => $podiumForm->createView(),
            ))->getContent()
        );
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/{id}/updatePodium", name = "ksTournament_updatePodium", options={"expose"=true} )
     * @ParamConverter("tournament", class="KsTournamentBundle:Tournament")
     */
    public function updatePodiumAction( \Ks\TournamentBundle\Entity\Tournament $tournament )
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $tournamentRep      = $em->getRepository('KsTournamentBundle:Tournament');
        
        if( is_object( $tournament ) ) {
            $podiumForm = $this->createForm(new \Ks\TournamentBundle\Form\PodiumType( $tournament->getClub() ), $tournament );
            $formHandler = new \Form\FormHandler( $podiumForm, $request, $em);

            $responseDatas = $formHandler->process();

            //Si le match a été modifié
            if ($responseDatas['code'] == 1) {

            }
        } else {
            $responseDatas = array(
                "code"          => -1,
                "errorMessage" => "Impossible de trouver le tournoi"
            );
        }
        
        $t = $tournamentRep->findOneTournament( array( "tournamentId" => $tournament->getId() ) );
                
        $responseDatas['html'] = $this->render('KsTournamentBundle:Tournament:_podium.html.twig', array(
                'tournament'      => $t,
            ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * Deletes a tournament.
     *
     * @Route("/{id}/delete", name="ksTournament_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $clubRep = $em->getRepository('KsClubBundle:Club');
        $tournamentRep = $em->getRepository('KsTournamentBundle:Tournament');
        
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $tournament = $tournamentRep->find($id);
        if (is_object($tournament)) {
            $club = $tournament->getClub();

            if( !$clubRep->isManager( $club->getId(), $user->getId() ) ) {
                return $this->redirect($this->generateUrl('ksTournament_show', array('id' => $id)));
            }
        
            $em->remove($tournament);
            $em->flush();
            
            $this->get('session')->setFlash('alert alert-success', "Le tournoi a été suprimée");
            return $this->redirect($this->generateUrl('KsClub_tournaments', array('clubId' => $club->getId())));
        }

        return $this->redirect($this->generateUrl('ksTournament_show', array('id' => $id)));
    }
}
