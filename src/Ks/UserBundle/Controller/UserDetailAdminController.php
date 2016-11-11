<?php

namespace Ks\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Ks\UserBundle\Entity\UserDetail;
use Ks\UserBundle\Form\UserDetailType;

/**
 * UserDetail controller.
 *
 */
class UserDetailAdminController extends Controller
{
    /**
     * Lists all UserDetail entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('KsUserBundle:UserDetail')->findAll();

        return $this->render('KsUserBundle:UserDetailAdmin:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a UserDetail entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsUserBundle:UserDetail')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserDetail entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('KsUserBundle:UserDetailAdmin:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

        ));
    }

    /**
     * Displays a form to create a new UserDetail entity.
     *
     */
    public function newAction()
    {
        $entity = new UserDetail();
        $form   = $this->createForm(new UserDetailType(), $entity);

        return $this->render('KsUserBundle:UserDetailAdmin:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new UserDetail entity.
     *
     */
    public function createAction()
    {
        $entity  = new UserDetail();
        $request = $this->getRequest();
        $form    = $this->createForm(new UserDetailType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('userdetail_admin_show', array('id' => $entity->getId())));
            
        }

        return $this->render('KsUserBundle:UserDetailAdmin:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing UserDetail entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsUserBundle:UserDetail')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserDetail entity.');
        }

        $editForm = $this->createForm(new UserDetailType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('KsUserBundle:UserDetailAdmin:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing UserDetail entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsUserBundle:UserDetail')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserDetail entity.');
        }

        $editForm   = $this->createForm(new UserDetailType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('userdetail_admin_edit', array('id' => $id)));
        }

        return $this->render('KsUserBundle:UserDetailAdmin:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a UserDetail entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('KsUserBundle:UserDetail')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find UserDetail entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('userdetail_admin'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
