<?php

namespace Ks\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ks\UserBundle\Entity\Invitation;
use Ks\UserBundle\Form\InvitationType;

/**
 * Invitation controller.
 *
 * @Route("/invitation")
 */
class InvitationController extends Controller
{
    /**
     * Lists all Invitation entities.
     *
     * @Route("/", name="invitation")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('KsUserBundle:Invitation')->findAll();

        return array('entities' => $entities);
    }

    /**
     * Finds and displays a Invitation entity.
     *
     * @Route("/{id}/show", name="invitation_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsUserBundle:Invitation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invitation entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        );
    }

    /**
     * Displays a form to create a new Invitation entity.
     *
     * @Route("/new", name="invitation_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Invitation();
        $form   = $this->createForm(new InvitationType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new Invitation entity.
     *
     * @Route("/create", name="invitation_create")
     * @Method("post")
     * @Template("KsUserBundle:Invitation:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new Invitation();
        $request = $this->getRequest();
        $form    = $this->createForm(new InvitationType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('invitation_show', array('id' => $entity->getId())));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Invitation entity.
     *
     * @Route("/{id}/edit", name="invitation_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsUserBundle:Invitation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invitation entity.');
        }

        $editForm = $this->createForm(new InvitationType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Invitation entity.
     *
     * @Route("/{id}/update", name="invitation_update")
     * @Method("post")
     * @Template("KsUserBundle:Invitation:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsUserBundle:Invitation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invitation entity.');
        }

        $editForm   = $this->createForm(new InvitationType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('invitation_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Invitation entity.
     *
     * @Route("/{id}/delete", name="invitation_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('KsUserBundle:Invitation')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Invitation entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('invitation'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
