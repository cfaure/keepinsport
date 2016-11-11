<?php

namespace Ks\ClubBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ks\ClubBundle\Entity\Team;
use Ks\ClubBundle\Form\TeamType;


use Ks\ClubBundle\Entity\ClubHasUsers;
use Ks\ClubBundle\Entity\Club;
use Ks\ClubBundle\Entity\ClubHasMembers;
use Ks\ClubBundle\Form\TeamHandler;


/**
 * Team controller.
 *
 * 
 */
class TeamController extends Controller
{
    

    /**
     * Finds and displays a Team team.
     *
     * @Route("/{teamId}/show", name="ksTeam_show")
     * @Template()
     */
    public function showAction($teamId)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $team = $em->getRepository('KsClubBundle:Team')->find($teamId);

        if (!$team) {
            throw $this->createNotFoundException('Unable to find Team team.');
        }

        return array(
            'team'                      => $team,
        );
    }



    /**
     * Creates a new Team team.
     *
     * @Route("/create", name="ksTeam_create")
     * @Method("post")
     * @Template("KsClubBundle:Team:new.html.twig")
     */
    public function createAction()
    {
        $request                = $this->getRequest();
        $em                     = $this->getDoctrine()->getEntityManager();
        $clubRep                = $em->getRepository('KsClubBundle:Club');
        $userManageClubRep      = $em->getRepository('KsClubBundle:UserManageClub');
        $user                   = $this->container->get('security.context')->getToken()->getUser();
        
        $clubId                 = $this->container->get('request')->get( "clubId" );
        $club                   = $clubRep->find( $clubId );
        
        if( ! $userManageClubRep->userIsClubManager( $club, $user ) ) {
             return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }
        
        $team  = new Team();
        $team->setClub( $club );
        
        $teamType = new \Ks\ClubBundle\Form\TeamType( $club );
        $form   = $this->createForm($teamType, $team);
        
        $formHandler = new TeamHandler($form, $request, $this->getDoctrine()->getEntityManager(), $club);
      
        if( $formHandler->process()){
            $this->get('session')->setFlash('alert alert-success', 'users.club_add_team_success');
            return $this->redirect($this->generateUrl('KsClub_teams', array('clubId' => $club->getId())));
        }
        
        return array(
            'team' => $team,
            'form'   => $form->createView(),
            'cbub' => $club,    
        );
    }

    /**
     * Displays a form to edit an existing Team team.
     *
     * @Route("/{id}/edit", name="ksTeam_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $userManageClubRep  = $em->getRepository('KsClubBundle:UserManageClub');
       
        $user               = $this->container->get('security.context')->getToken()->getUser();
        $idUser             = $user->getId(); 

        $team = $em->getRepository('KsClubBundle:Team')->find($id);

        if (!$team) {
            throw $this->createNotFoundException('Unable to find Team team.');
        }
        
        if( ! $userManageClubRep->userIsClubManager( $team->getClub(), $user ) ) {
             return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }

        $teamType = new \Ks\ClubBundle\Form\TeamType( $team->getClub() );
        $editForm   = $this->createForm($teamType, $team);

        return array(
            'team'      => $team,
            'editTeamForm'   => $editForm->createView(),  
        );
    }

    /**
     * Edits an existing Team team.
     *
     * @Route("/{id}/update", name="ksTeam_update")
     * @Method("post")
     * @Template("KsClubBundle:Team:edit.html.twig")
     */
    public function updateAction($id)
    {
        $request                = $this->getRequest();
        $em                     = $this->getDoctrine()->getEntityManager();
        $clubRep                = $em->getRepository('KsClubBundle:Club');
        $userManageClubRep      = $em->getRepository('KsClubBundle:UserManageClub');
        $user                   = $this->container->get('security.context')->getToken()->getUser();
        
        $clubId                 = $this->container->get('request')->get( "clubId" );
        $club                   = $clubRep->find( $clubId );
        
        $team = $em->getRepository('KsClubBundle:Team')->find($id);

        if (!$team) {
            throw $this->createNotFoundException('Unable to find Team team.');
        }
        
        if( ! $userManageClubRep->userIsClubManager( $team->getClub(), $user ) ) {
             return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }

        //$editForm   = $this->createForm(new TeamType(), $team);
        $teamType = new \Ks\ClubBundle\Form\TeamType($club);
        $editForm   = $this->createForm($teamType, $team);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();
        
        $formHandler = new TeamHandler($editForm, $request, $this->getDoctrine()->getEntityManager(),$club);
      
        if( $formHandler->process()){
            $this->get('session')->setFlash('alert alert-success', 'users.club_add_team_success');
            return $this->redirect($this->generateUrl('ksTeam_show', array("teamId" => $team->getId() )));
        }

        /*$editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($team);
            $em->flush();

            return $this->redirect($this->generateUrl('ksTeam_edit', array('id' => $id)));
        }*/

        return array(
            'team'      => $team,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'idClub'      => $club->getId(),  
        );
    }

    /**
     * Deletes a Team team.
     *
     * @Route("/{id}/delete", name="ksTeam_delete")
     */
    public function deleteAction($id)
    {
        //$idClub = $this->container->get('request')->get("idClub");
        $em = $this->getDoctrine()->getEntityManager();
        $teamRep = $em->getRepository('KsClubBundle:Team');
        
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $team = $teamRep->find($id);
        $club = $team->getClub();
        //$club = $em->getRepository('KsClubBundle:Club')->find($idClub);
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$user->getId(),"club"=>$club->getId()));
        if($userManageClub==null){
             return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }
        
        $team = $em->getRepository('KsClubBundle:Team')->find($id);
        if (is_object($team)) {
            $em->remove($team);
            $em->flush();
            
            $this->get('session')->setFlash('alert alert-success', "L'équipe a été suprimée");
        }

        return $this->redirect($this->generateUrl('KsClub_teams', array('clubId' => $club->getId())));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
