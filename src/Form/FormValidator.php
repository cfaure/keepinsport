<?php

namespace Form;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ks\Form\FormValidator
 *
 * @ORM\Entity
 */
class FormValidator
{
    public static function getErrorMessages(\Symfony\Component\Form\Form $form) {
        $errors = array();
        foreach ($form->getErrors() as $key => $error) {
            $template = $error->getMessageTemplate();
            $parameters = $error->getMessageParameters();

            foreach($parameters as $var => $value){
                $template = str_replace($var, $value, $template);
            }

            $errors[$key] = $template;
        }
        if ($form->hasChildren()) {
            foreach ($form->getChildren() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = FormValidator::getErrorMessages($child);
                }
            }
        }

        return $errors;
    }
    
    public static function processingForActivityAddingPhoto( $activity, $photos, $em ) {
        $uploadDirAbsolute = dirname($_SERVER['SCRIPT_FILENAME']).'/uploads/photos/';
        $activitiesDirAbsolute = dirname($_SERVER['SCRIPT_FILENAME']).'/img/activities/';
        
        if (! is_dir( $activitiesDirAbsolute ) ) mkdir( $activitiesDirAbsolute );

        foreach( $photos as $key => $uploadedPhoto ) {
            //On récupère l'extension de la photo
            $ext = explode('.', $uploadedPhoto);
            $ext = array_pop($ext);
            $ext = "." . $ext;

            $activityId = $activity->getId();

            $activityDirAbsolute = $activitiesDirAbsolute.$activityId."/";

            //On crée le dossier qui contient les images de l'article s'il n'existe pas
            if (! is_dir( $activityDirAbsolute ) ) mkdir( $activityDirAbsolute );

            $activityOriginalPhotosDirAbsolute = $activityDirAbsolute . 'original/';
            if (! is_dir( $activityOriginalPhotosDirAbsolute ) ) mkdir($activityOriginalPhotosDirAbsolute);

            $activityThumbnailPhotosDirAbsolute = $activityDirAbsolute . 'thumbnail/';
            if (! is_dir( $activityThumbnailPhotosDirAbsolute ) ) mkdir($activityThumbnailPhotosDirAbsolute);
            
            $photo = new \Ks\ActivityBundle\Entity\Photo($ext);
            $em->persist($photo);
            $em->flush();
            
            $photoPath = $photo->getId().$ext;

            //On la déplace les photos originales et redimentionnés
            $renameOriginale = rename( $uploadDirAbsolute."original/"  . $uploadedPhoto, $activityOriginalPhotosDirAbsolute.$photoPath );
            $renameThumbnail = rename( $uploadDirAbsolute."thumbnail/" . $uploadedPhoto, $activityThumbnailPhotosDirAbsolute.$photoPath );

            if( $renameOriginale && $renameThumbnail){
                $movePhotoResponse = 1;

                $photo->setActivity($activity);
                $em->persist($photo);
                $activity->addPhoto($photo);
            } else {
                $em->remove($photo);
            }
        }

        $em->persist($activity);
        $em->flush();
    }

    /**
     * @param $activity
     * @param $photos
     * @param $em
     */
    public static function processingForActivityDeletingPhoto( $activity, $photos, $em ) {
        $photoRep = $em->getRepository('KsActivityBundle:Photo');
        
        $activitiesDirAbsolute = dirname($_SERVER['SCRIPT_FILENAME']).'/img/activities/';

        foreach( $photos as $key => $photoToDelete ) {
            //On récupère l'id de la photo contenu avant l'extention
            $aTemp = explode('.', $photoToDelete);
            $photoId = array_shift($aTemp);

            $activityId = $activity->getId();

            $activityDirAbsolute = $activitiesDirAbsolute.$activityId."/";

            $activityOriginalPhotosDirAbsolute = $activityDirAbsolute . 'original/';
            $activityThumbnailPhotosDirAbsolute = $activityDirAbsolute . 'thumbnail/';
            
            $photo = $photoRep->find($photoId);
            
            $removeResponse = false;
            
            if ( is_object( $photo ) ) {
                $removeResponse = $activity->removePhoto( $photo );
                $em->remove( $photo );
                $em->persist($activity);
                $em->flush();
            }
            
            //Si la photo a été effacé correctement dans les objets, on efface les fichiers photos
            if( $removeResponse ) {
                //l'originale
                if( file_exists( $activityOriginalPhotosDirAbsolute.$photoToDelete ) ) {
                    unlink( $activityOriginalPhotosDirAbsolute.$photoToDelete );
                }
                
                //La miniature
                if( file_exists( $activityThumbnailPhotosDirAbsolute.$photoToDelete ) ) {
                    unlink( $activityThumbnailPhotosDirAbsolute.$photoToDelete );
                }
            }   
        }
    }
    
    public static function processingForActivityAddingGPX( $enduranceSession, $GPXToAdd, $checkboxSRTM, $em, $container ) {
        //Pour l'instant 1 seul GPX, plus tard on pourrait être amené à en traiter plusieurs
        foreach( $GPXToAdd as $key => $uploadedGPX ) {
            //Utile pour le cas de la modification d'une activité
            $em->getRepository('KsActivityBundle:Gpx')->deleteGPXFromActivity($enduranceSession->getId());
            
            $gpx = new \Ks\ActivityBundle\Entity\Gpx();
            $gpx->setUploadedBy($enduranceSession->getUser()->getId());
            $gpx->setUploadedAt(new \DateTime("now"));
            $gpx->setName($uploadedGPX);
            $gpx->setActivity($enduranceSession);
            $gpx->setSport($enduranceSession->getSport());

            $em->persist($gpx);
        }

        $importService = $container->get('ks_activity.importActivityService');
        $gpxPath    = $container->get('kernel')->getRootdir()
            .DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR.'web'
            .DIRECTORY_SEPARATOR.'uploads'
            .DIRECTORY_SEPARATOR.'gpx'
            .DIRECTORY_SEPARATOR.$gpx->getName();

        $activityDatas = $importService->buildJsonToSave($enduranceSession->getUser(), array('fileName' => $gpxPath), 'gpx', $checkboxSRTM);

        if (!$activityDatas || count($activityDatas) == 0) {
            //FIXME : Traitement erreur si fichier vide !
            //$this->get('session')->setFlash('alert alert-warning', 'Format du fichier GPX incorrect');
            //return $this->redirect($this->generateUrl('ks_upload_import_gpx_file'));
        }
                
        $enduranceSession->setSource($activityDatas[0]['info']['source'] . ($checkboxSRTM === 'true' ? '_SRTM' : ''));
        $enduranceSession->setDistance($activityDatas[0]['info']['distance']);
        $enduranceSession->setDuration($activityDatas[0]['info']['timeDuration']);
        $enduranceSession->setTimeMoving($activityDatas[0]['info']['duration']);
        $enduranceSession->setIssuedAt($activityDatas[0]['info']['startDate']);
        $enduranceSession->setModifiedAt(new \DateTime('Now'));
        $enduranceSession->setElevationGain($activityDatas[0]['info']['D+']);
        $enduranceSession->setElevationLost($activityDatas[0]['info']['D-']);
        $enduranceSession->setElevationMin($activityDatas[0]['info']['minEle']);
        $enduranceSession->setElevationMax($activityDatas[0]['info']['maxEle']);
        $enduranceSession->setTrackingDatas($activityDatas[0]);

        $firstWaypoint = $importService->getFirstWaypointNotEmpty($activityDatas[0]);
        if ($firstWaypoint != null) {
            $enduranceSession->setPlace($importService->findPlace($firstWaypoint["lat"], $firstWaypoint["lon"])
            );
        }
    }
}