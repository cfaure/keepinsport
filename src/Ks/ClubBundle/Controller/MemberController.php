<?php

namespace Ks\ClubBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ks\ClubBundle\Entity\Member;
use Ks\ClubBundle\Form\MemberType;

use Ks\ClubBundle\Entity\ClubHasUsers;
use Ks\ClubBundle\Entity\ClubHasMembers;
use Ks\ClubBundle\Form\MemberHandler;

/**
 * Member controller.
 *
 */
class MemberController extends Controller
{
    /**
     * Lists all Member entities.
     *
     * @Route("/{idClub}/index", name="ks_member")
     * @Template()
     */
    public function indexAction($idClub)
    {

        $em = $this->getDoctrine()->getEntityManager();
    
        $club = $em->getRepository('KsClubBundle:Club')->find($idClub);
        $ClubHasMembers = $em->getRepository('KsClubBundle:ClubHasMembers')->findBy(array("club"=>$idClub ));

        if (!$club) {
            throw $this->createNotFoundException('Unable to find Club entity.');
        }

        return array('entities' => $ClubHasMembers,
                      'club' => $club  );
    }

    /**
     * Finds and displays a Member entity.
     *
     * @Route("/{id}/show", name="ks_member_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsClubBundle:Member')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Member entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        );
    }

    /**
     * Displays a form to create a new Member entity.
     * @Route("/{idClub}/new", name="ks_member_new")
     * 
     * @Template()
     */
    public function newAction($idClub)
    {
        //On vérifie si l'utilisateur a bien le droit de gérer ce club avant toute chose 
        $em = $this->getDoctrine()->getEntityManager();
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        $idUser = $currentUser->getId();
        $club = $em->getRepository('KsClubBundle:Club')->find($idClub);
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$idUser,"club"=>$idClub));
        if($userManageClub==null){
             return $this->redirect($this->generateUrl('admin_club'));
        }
        
        $entity = new Member();
        $form   = $this->createForm(new MemberType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'idClub'     => $idClub,    
        );
    }

    /**
     * Creates a new Member entity.
     *
     * @Route("/create", name="ks_member_create")
     * @Method("post")
     * @Template("KsClubBundle:Member:new.html.twig")
     */
    public function createAction()
    {
        //On vérifie si l'utilisateur a bien le droit de gérer ce club avant toute chose 
        $idClub = $this->container->get('request')->get("idClub");
        $em = $this->getDoctrine()->getEntityManager();
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        $idUser = $currentUser->getId();
        $club = $em->getRepository('KsClubBundle:Club')->find($idClub);
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$idUser,"club"=>$idClub));
        if($userManageClub==null){
             return $this->redirect($this->generateUrl('admin_club'));
        }
        $entity  = new Member();
        $request = $this->getRequest();
        $form    = $this->createForm(new MemberType(), $entity);
        
        $formHandler = new MemberHandler($form, $request, $this->getDoctrine()->getEntityManager(), $idClub);
        if( $formHandler->process()){
            $this->get('session')->setFlash('alert alert-success', 'users.club_add_member_success');
            //return $this->redirect($this->generateUrl('admin_club'));
            return $this->redirect($this->generateUrl('ks_member', array('idClub' => $idClub)));
        }


        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'idClub' => $idClub,        
        );
    }

    /**
     * Displays a form to edit an existing Member entity.
     *
     * @Route("/{id}/{idClub}/edit", name="ks_member_edit")
     * @Template()
     */
    public function editAction($id,$idClub)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        $idUser = $currentUser->getId();
        $club = $em->getRepository('KsClubBundle:Club')->find($idClub);
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$idUser,"club"=>$idClub));
        if($userManageClub==null){
             return $this->redirect($this->generateUrl('admin_club'));
        }

        $entity = $em->getRepository('KsClubBundle:Member')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Member entity.');
        }

        $editForm = $this->createForm(new MemberType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'idClub'      => $idClub,
        );
    }

    /**
     * Edits an existing Member entity.
     *
     * @Route("/{id}/update", name="ks_member_update")
     * @Method("post")
     * @Template("KsClubBundle:Member:edit.html.twig")
     */
    public function updateAction($id)
    {
        $idClub = $this->container->get('request')->get("idClub");
        $em = $this->getDoctrine()->getEntityManager();
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        $idUser = $currentUser->getId();
        $club = $em->getRepository('KsClubBundle:Club')->find($idClub);
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$idUser,"club"=>$idClub));
        
        if($userManageClub==null){
             return $this->redirect($this->generateUrl('admin_club'));
        }
        
        

        $entity = $em->getRepository('KsClubBundle:Member')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Member entity.');
        }

        $editForm   = $this->createForm(new MemberType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();
        
        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('ks_member', array('idClub' => $idClub )));
           
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'idClub'      => $idClub,
        );
    }

    /**
     * Deletes a Member entity.
     *
     * @Route("/{id}/{idClub}/delete", name="ks_member_delete")
     * @Method("post")
     */
    public function deleteAction($id,$idClub)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        $idUser = $currentUser->getId();
        $club = $em->getRepository('KsClubBundle:Club')->find($idClub);
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$idUser,"club"=>$idClub));
        if($userManageClub==null){
             return $this->redirect($this->generateUrl('admin_club'));
        }
        
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('KsClubBundle:Member')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Member entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('ks_member',array('idClub' => $idClub )));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
