<?php

namespace Ks\CanvasDrawingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class CanvasDrawingController extends Controller
{
    /**
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
    
    ## RENDER ##
    
    /**
     * 
     */
    public function activityAction($activityId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $user               = $this->get('security.context')->getToken()->getUser();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $characterRep       = $em->getRepository('KsCanvasDrawingBundle:Character');
        $equipmentRep       = $em->getRepository('KsUserBundle:Equipment');
        
        if (!is_object($user) ) {
            $userId = "-1";
        }
        else {
            $userId = $user->getId();
        }
        
        if ($activityId != $userId ) {
            $activityDatas = $activityRep->findActivities(array(
                'activityId' => $activityId,
                'isNotValidatePossible' => true
            ));

            if ( $activityDatas["activity"]["source"] != null) {
                $activity = $activityRep->find($activityId);
                $trackingDatas = $activity->getTrackingDatas();

                if (isset( $trackingDatas["info"]["distance"])) {
                    //$activityDatas["activity"]["distance"] =(float)$trackingDatas["info"]["distance"];
                }
                if (isset( $trackingDatas["info"]["D-"])) {
                    $activityDatas["activity"]["denNeg"] = $activityDatas["activity"]["elevationLost"];//(int)$trackingDatas["info"]["D-"]; Pour permettre la modif à posteriori
                }
                if (isset( $trackingDatas["info"]["D+"])) {
                    $activityDatas["activity"]["denPos"] = $activityDatas["activity"]["elevationGain"];//(int)$trackingDatas["info"]["D+"]; Pour permettre la modif à posteriori
                }
            }
            
            $equipments = $equipmentRep->findEquipments(array(
                "activitysessionId" => $activityId,
                "userId"     => $userId
            ));
            
            $character = $characterRep->findOneCharacter(array(
                "userId" => $activityDatas['activity']['user_id']
            ));
        }
        else {
            //Cas de la publication d'activité, on affiche le canvas mais l'activité n'existe pas en encore en base
            //On récupère les équipements par défaut pour le sport sélectionné pour les mettre sur le canvas
            //FIXME : il faudrait passer le sport_id en paramètre !!
            $equipments = $equipmentRep->findEquipments(array(
                "isByDefault" => true,
                //"sport_id"    => 14,
                "userId"      => $userId
            ));
            $character = $characterRep->findOneCharacter(array(
                "userId" => $userId
            ));
        }
        
        
        
        if ($activityId != $userId ) {
            return $this->render('KsCanvasDrawingBundle:CanvasDrawing:_activityForNewsFeed.html.twig', array(
                "activityId"    => $activityId,
                "activity"      => $activityDatas["activity"],
                'scores'        => $activityDatas["activityScores"],
                "character"     => $character,
                'equipments'    => $equipments
            ));
        }
        else {
            return $this->render('KsCanvasDrawingBundle:CanvasDrawing:_activity.html.twig', array(
                "activityId"     => $activityId,
                "character"      => $character,
                "equipments"     => $equipments,
            ));
        }
    }
}
