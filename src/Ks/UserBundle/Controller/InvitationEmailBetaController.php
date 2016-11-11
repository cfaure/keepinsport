<?php

namespace Ks\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ks\UserBundle\Entity\InvitationEmailBeta;
use Ks\UserBundle\Form\InvitationEmailBetaType;

/**
 * InvitationEmailBeta controller.
 *
 * @Route("/admin/invitationemailbeta")
 */
class InvitationEmailBetaController extends Controller
{
    /**
     * Lists all InvitationEmailBeta entities.
     *
     * @Route("/", name="invitationemailbeta")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('KsUserBundle:InvitationEmailBeta')->findAll();

        return array('entities' => $entities);
    }

    /**
     * Finds and displays a InvitationEmailBeta entity.
     *
     * @Route("/{id}/show", name="invitationemailbeta_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsUserBundle:InvitationEmailBeta')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InvitationEmailBeta entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        );
    }

    /**
     * Displays a form to create a new InvitationEmailBeta entity.
     *
     * @Route("/new", name="invitationemailbeta_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new InvitationEmailBeta();
        $form   = $this->createForm(new InvitationEmailBetaType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new InvitationEmailBeta entity.
     *
     * @Route("/create", name="invitationemailbeta_create")
     * @Method("post")
     * @Template("KsUserBundle:InvitationEmailBeta:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new InvitationEmailBeta();
        $request = $this->getRequest();
        $form    = $this->createForm(new InvitationEmailBetaType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('invitationemailbeta_show', array('id' => $entity->getId())));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing InvitationEmailBeta entity.
     *
     * @Route("/{id}/edit", name="invitationemailbeta_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsUserBundle:InvitationEmailBeta')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InvitationEmailBeta entity.');
        }

        $editForm = $this->createForm(new InvitationEmailBetaType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing InvitationEmailBeta entity.
     *
     * @Route("/{id}/update", name="invitationemailbeta_update")
     * @Method("post")
     * @Template("KsUserBundle:InvitationEmailBeta:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsUserBundle:InvitationEmailBeta')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InvitationEmailBeta entity.');
        }

        $editForm   = $this->createForm(new InvitationEmailBetaType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('invitationemailbeta_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a InvitationEmailBeta entity.
     *
     * @Route("/{id}/delete", name="invitationemailbeta_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('KsUserBundle:InvitationEmailBeta')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InvitationEmailBeta entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('invitationemailbeta'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
