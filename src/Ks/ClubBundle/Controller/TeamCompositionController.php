<?php

namespace Ks\ClubBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


/**
 * Team controller.
 *
 * 
 */
class TeamCompositionController extends Controller
{
    

    /**
     * Finds and displays a Team team.
     *
     * @Route("/{teamCompositionId}/show", name="ksTeamComposition_show")
     * @Template()
     */
    public function showAction( $teamCompositionId )
    {
        $em = $this->getDoctrine()->getEntityManager();
        $teamCompositionRep = $em->getRepository('KsClubBundle:TeamComposition');

        $teamComposition = $teamCompositionRep->find( $teamCompositionId );

        if (!$teamComposition) {
            throw $this->createNotFoundException('Unable to find Team composition entity.');
        }
        
        $deleteTeamCompositionform = $this->createDeleteForm( $teamCompositionId );

        return array(
            'teamComposition'               => $teamComposition,
            'deleteTeamCompositionform'     => $deleteTeamCompositionform->createView(),
        );
    }



        /**
     * Création d'une nouvelle composition d'équipe
     *
     * @Route("/new/{teamId}", name="ksTeamComposition_new")
     * @Template("KsClubBundle:TeamComposition:teamCompositionForm.html.twig")
     */
    public function newAction($teamId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $teamRep                = $em->getRepository('KsClubBundle:Team');
        $userManageClubRep      = $em->getRepository('KsClubBundle:UserManageClub');
        $user                   = $this->container->get('security.context')->getToken()->getUser();
        
        $team                   = $teamRep->find( $teamId );
        
        if ( !$team ) {
            throw $this->createNotFoundException('Unable to find Team team.');
        }
        
        if( ! $userManageClubRep->userIsClubManager( $team->getClub(), $user ) ) {
             return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }

        $teamComposition        = new \Ks\ClubBundle\Entity\TeamComposition( $team ); 
        $teamCompositionType    = new \Ks\ClubBundle\Form\TeamCompositionType( $team );
        $teamCompositionForm    = $this->createForm( $teamCompositionType, $teamComposition);
        
        return array(
            'teamCompositionForm'   => $teamCompositionForm->createView(),
            'team'                  => $team,
            //'teamComposition'       => $teamComposition
        );
    }
    
    /**
     * Creates a new Team team.
     *
     * @Route("/createTeamComposition/{teamId}", name="ksTeamComposition_create")
     * @Method("post")
     * @Template("KsClubBundle:TeamComposition:teamCompositionForm.html.twig")
     */
    public function createAction( $teamId )
    {
        $request                = $this->getRequest();
        $em                     = $this->getDoctrine()->getEntityManager();
        $teamRep                = $em->getRepository('KsClubBundle:Team');
        $userManageClubRep      = $em->getRepository('KsClubBundle:UserManageClub');
        $user                   = $this->container->get('security.context')->getToken()->getUser();

        $team                   = $teamRep->find( $teamId );
        
        if ( !$team ) {
            throw $this->createNotFoundException('Unable to find Team team.');
        }
        
        if( ! $userManageClubRep->userIsClubManager( $team->getClub(), $user ) ) {
             return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }
        
        $teamComposition        = new \Ks\ClubBundle\Entity\TeamComposition( $team ); 
        $teamCompositionType    = new \Ks\ClubBundle\Form\TeamCompositionType( $team );
        $teamCompositionForm    = $this->createForm( $teamCompositionType, $teamComposition);
        
        $formHandler = new \Ks\ClubBundle\Form\TeamCompositionHandler($teamCompositionForm, $request, $em);
        
        $responseDatas = $formHandler->process();
      
        if( $responseDatas["response"] == 1 ){
            $this->get('session')->setFlash('alert alert-success', "La composition d'équipe a été créée avec succès");
            return $this->redirect(
                $this->generateUrl('ksTeamComposition_show', array(
                    'teamCompositionId' => $teamComposition->getId()
                ))
            );
        }
        
        return array(
            'team' => $team,
            'teamCompositionForm'   => $teamCompositionForm->createView(),   
        );
    }

    /**
     * Displays a form to edit an existing Team team.
     *
     * @Route("/{teamCompositionId}/edit", name="ksTeamComposition_edit")
     * @Template("KsClubBundle:TeamComposition:teamCompositionForm.html.twig")
     */
    public function editAction( $teamCompositionId )
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $teamCompositionRep     = $em->getRepository('KsClubBundle:TeamComposition');
        $userManageClubRep      = $em->getRepository('KsClubBundle:UserManageClub');
       
        $user               = $this->container->get('security.context')->getToken()->getUser();

        $teamComposition = $teamCompositionRep->find( $teamCompositionId );

        if (!$teamComposition) {
            throw $this->createNotFoundException('Unable to find Team composition entity.');
        }
        
        if( ! $userManageClubRep->userIsClubManager( $teamComposition->getTeam()->getClub(), $user ) ) {
             return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }

        $teamCompositionType        = new \Ks\ClubBundle\Form\TeamCompositionType( $teamComposition->getTeam() );
        $teamCompositionEditForm    = $this->createForm($teamCompositionType, $teamComposition);
        $teamCompositionDeleteForm  = $this->createDeleteForm( $teamCompositionId );

        return array(
            'teamComposition'           => $teamComposition,
            'teamCompositionForm'   => $teamCompositionEditForm->createView(),
            'teamCompositionDeleteForm' => $teamCompositionDeleteForm->createView(),   
        );
    }

    /**
     * Edits an existing Team team.
     *
     * @Route("/{teamCompositionId}/update", name="ksTeamComposition_update")
     * @Method("post")
     * @Template("KsClubBundle:Team:edit.html.twig")
     */
    public function updateAction($teamCompositionId)
    {
        $request                = $this->getRequest();
        $em                     = $this->getDoctrine()->getEntityManager();
        $teamCompositionRepRep  = $em->getRepository('KsClubBundle:TeamComposition');
        $userManageClubRep      = $em->getRepository('KsClubBundle:UserManageClub');
        $user                   = $this->container->get('security.context')->getToken()->getUser();
        
        $teamComposition = $teamCompositionRepRep->find($teamCompositionId);

        if (!$teamComposition) {
            throw $this->createNotFoundException('Unable to find Team composition entity.');
        }
        
        if( ! $userManageClubRep->userIsClubManager( $teamComposition->getTeam()->getClub(), $user ) ) {
             return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }

        $teamCompositionType        = new \Ks\ClubBundle\Form\TeamCompositionType( $teamComposition->getTeam() );
        $teamCompositionEditForm    = $this->createForm($teamCompositionType, $teamComposition);
        $teamCompositionDeleteForm  = $this->createDeleteForm( $teamCompositionId );
        
        $formHandler = new \Ks\ClubBundle\Form\TeamCompositionHandler($teamCompositionEditForm, $request, $em);
        
        $responseDatas = $formHandler->process();
      
        if( $responseDatas["response"] == 1 ){
            $this->get('session')->setFlash('alert alert-success', "La composition d'équipe a été enregistrée avec succès");
            return $this->redirect(
                $this->generateUrl('ksTeamComposition_show', array(
                    'teamCompositionId' => $teamComposition->getId()
                ))
            );
        }

        return array(
            'teamComposition'           => $teamComposition,
            'teamCompositionForm'   => $teamCompositionEditForm->createView(),
            'teamCompositionDeleteForm' => $teamCompositionDeleteForm->createView(),   
        );
    }

    /**
     * Deletes a Team team.
     *
     * @Route("/{teamCompositionId}/delete", name="ksTeamComposition_delete")
     * @Method("post")
     */
    public function deleteAction($teamCompositionId)
    {
        $request                = $this->getRequest();
        $em                     = $this->getDoctrine()->getEntityManager();
        $teamCompositionRep     = $em->getRepository('KsClubBundle:TeamComposition');
        $userManageClubRep      = $em->getRepository('KsClubBundle:UserManageClub');
        $user                   = $this->container->get('security.context')->getToken()->getUser();
        
        $teamComposition = $teamCompositionRep->find($teamCompositionId);

        if (!$teamComposition) {
            throw $this->createNotFoundException('Unable to find Team composition entity.');
        }
        
        if( ! $userManageClubRep->userIsClubManager( $teamComposition->getTeam()->getClub(), $user ) ) {
             return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }
        
        $form = $this->createDeleteForm($teamCompositionId);

        $form->bindRequest($request);
        
        if ($form->isValid()) {
         
            $em->remove($teamComposition);
            $em->flush();
           
        }

        return $this->redirect($this->generateUrl('ksTeam_show', array('teamId' => $teamComposition->getTeam()->getId())));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
    /**
     * Inform selected users
     *
     * @Route("/{teamCompositionId}/informSelectedUsers", name="ksTeamComposition_informSelectedUsers")
     */
    public function informSelectedUsersAction($teamCompositionId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $teamCompositionRep     = $em->getRepository('KsClubBundle:TeamComposition');
        $userManageClubRep      = $em->getRepository('KsClubBundle:UserManageClub');
        $user                   = $this->container->get('security.context')->getToken()->getUser();
        
        //Appel aux services
        $notificationService    = $this->get('ks_notification.notificationService');
        
        $teamComposition = $teamCompositionRep->find($teamCompositionId);

        if (!$teamComposition) {
            throw $this->createNotFoundException('Unable to find Team composition entity.');
        }
        
        if( ! $userManageClubRep->userIsClubManager( $teamComposition->getTeam()->getClub(), $user ) ) {
             return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }
        
        $selectedUsers = $teamComposition->getUsers();
        
        foreach( $selectedUsers as $teamCompositionHasUser ) {
            $notificationService->sendClubNotification(
                $teamComposition->getTeam()->getClub(), 
                $teamCompositionHasUser->getUser(),
                "teamComposition",
                "Tu es sélectionné pour ". $teamComposition->getName() . " " .$teamComposition->getDate()->format("d/m/Y Hi"),
                $teamComposition
            );
        }
        
        $teamCompositionRep->publishTeamComposition( $teamComposition );
        
        $this->get('session')->setFlash('alert alert-success', "La composition d'équipe a été notifiée aux sportifs concernés");

        return $this->redirect($this->generateUrl('ksTeamComposition_show', array('teamCompositionId' => $teamComposition->getId())));
    }
    
  
}
