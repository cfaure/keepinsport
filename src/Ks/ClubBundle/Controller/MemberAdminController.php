<?php

namespace Ks\ClubBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ks\ClubBundle\Entity\Member;
use Ks\ClubBundle\Form\MemberType;

/**
 * Member controller.
 *
 * @Route("/ks/member")
 */
class MemberController extends Controller
{
    /**
     * Lists all Member entities.
     *
     * @Route("/", name="ks_member")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('KsClubBundle:Member')->findAll();

        return array('entities' => $entities);
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
     *
     * @Route("/new", name="ks_member_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Member();
        $form   = $this->createForm(new MemberType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
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
        $entity  = new Member();
        $request = $this->getRequest();
        $form    = $this->createForm(new MemberType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('ks_member_show', array('id' => $entity->getId())));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Member entity.
     *
     * @Route("/{id}/edit", name="ks_member_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

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
        $em = $this->getDoctrine()->getEntityManager();

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

            return $this->redirect($this->generateUrl('ks_member_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Member entity.
     *
     * @Route("/{id}/delete", name="ks_member_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
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

        return $this->redirect($this->generateUrl('ks_member'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
