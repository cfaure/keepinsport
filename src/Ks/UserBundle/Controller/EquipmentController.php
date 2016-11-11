<?php

namespace Ks\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;

/**
 * EquipmentController.
 *
 */
class EquipmentController extends Controller
{
    ## RENDER ##
    public function customSelectEquipmentTypeAction( ) {
        $securityContext    = $this->container->get('security.context');
        
        $em             = $this->getDoctrine()->getEntityManager();
        $equipmentTypeRep   = $em->getRepository('KsUserBundle:EquipmentType');
        
        
        $equipmentsTypes = $equipmentTypeRep->findEquipmentsTypes();
        
        return $this->render('KsUserBundle:Equipment:_customSelectEquipmentType.html.twig', array(
            'equipmentsTypes'     => $equipmentsTypes,
        )); 
    }
}
