<?php

namespace Ks\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Ks\NotificationBundle\Entity\NotificationType;
use Ks\NotificationBundle\Form\NotificationTypeType;

/**
 * NotificationType controller.
 *
 */
class NotificationTypeController extends Controller
{
    /**
     * Lists all NotificationType entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('KsNotificationBundle:NotificationType')->findAll();

        return $this->render('KsNotificationBundle:NotificationType:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a NotificationType entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsNotificationBundle:NotificationType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find NotificationType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('KsNotificationBundle:NotificationType:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

        ));
    }

    /**
     * Displays a form to create a new NotificationType entity.
     *
     */
    public function newAction()
    {
        $entity = new NotificationType();
        $form   = $this->createForm(new NotificationTypeType(), $entity);

        return $this->render('KsNotificationBundle:NotificationType:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new NotificationType entity.
     *
     */
    public function createAction()
    {
        $entity  = new NotificationType();
        $request = $this->getRequest();
        $form    = $this->createForm(new NotificationTypeType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('notificationtype_show', array('id' => $entity->getId())));
            
        }

        return $this->render('KsNotificationBundle:NotificationType:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing NotificationType entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsNotificationBundle:NotificationType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find NotificationType entity.');
        }

        $editForm = $this->createForm(new NotificationTypeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('KsNotificationBundle:NotificationType:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing NotificationType entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsNotificationBundle:NotificationType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find NotificationType entity.');
        }

        $editForm   = $this->createForm(new NotificationTypeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('notificationtype_edit', array('id' => $id)));
        }

        return $this->render('KsNotificationBundle:NotificationType:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a NotificationType entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('KsNotificationBundle:NotificationType')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find NotificationType entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('notificationtype'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
