<?php

namespace Ks\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ks\UserBundle\Entity\UserHasServices;
use Ks\UserBundle\Form\UserHasServicesType;

/**
 * UserHasServices controller.
 *
 * @Route("/userhasservices")
 */
class UserHasServicesController extends Controller
{
    /**
     * Lists all UserHasServices entities.
     *
     * @Route("/", name="userhasservices")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('KsUserBundle:UserHasServices')->findAll();

        return array('entities' => $entities);
    }

    /**
     * Finds and displays a UserHasServices entity.
     *
     * @Route("/{id}/show", name="userhasservices_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsUserBundle:UserHasServices')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserHasServices entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        );
    }

    /**
     * Displays a form to create a new UserHasServices entity.
     *
     * @Route("/new", name="userhasservices_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new UserHasServices();
        $form   = $this->createForm(new UserHasServicesType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new UserHasServices entity.
     *
     * @Route("/create", name="userhasservices_create")
     * @Method("post")
     * @Template("KsUserBundle:UserHasServices:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new UserHasServices();
        $request = $this->getRequest();
        $form    = $this->createForm(new UserHasServicesType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('userhasservices_show', array('id' => $entity->getId())));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing UserHasServices entity.
     *
     * @Route("/{id}/edit", name="userhasservices_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsUserBundle:UserHasServices')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserHasServices entity.');
        }

        $editForm = $this->createForm(new UserHasServicesType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing UserHasServices entity.
     *
     * @Route("/{id}/update", name="userhasservices_update")
     * @Method("post")
     * @Template("KsUserBundle:UserHasServices:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsUserBundle:UserHasServices')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserHasServices entity.');
        }

        $editForm   = $this->createForm(new UserHasServicesType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('userhasservices_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a UserHasServices entity.
     *
     * @Route("/{id}/delete", name="userhasservices_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('KsUserBundle:UserHasServices')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find UserHasServices entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('userhasservices'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
