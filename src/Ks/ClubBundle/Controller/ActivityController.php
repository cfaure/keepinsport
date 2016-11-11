<?php
namespace Ks\ClubBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Leg\GoogleChartsBundle\Charts\Gallery\Bar\VerticalGroupedChart;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use vendor\symfony\src\Symfony\Bundle\TwigBundle\Extension\AssetsExtension;


class ActivityController extends Controller
{
       /**
     * 
     * @Route("/loadClubActivities/{clubId}_{offset}", requirements={"clubId" = "\d+", "offset" = "\d+"}, name = "ksClub_loadClubActivities", options={"expose"=true} )
     * @param int $offset 
     */
    public function loadClubActivitiesAction( $clubId, $offset )
    {
        $numActivitiesPerPage = 10;
        $em             = $this->getDoctrine()->getEntityManager();
        $request        = $this->getRequest();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $parameters     = $request->request->all();
        
        $activitiesTypes    = isset($parameters["activitiesTypes"]) ? $parameters["activitiesTypes"] : array() ;
        $byLastModified     = isset($parameters["byLastModified"]) && $parameters["byLastModified"] == "false" ? false : true ;

        $activities         = $activityRep->findActivities(array(
            'clubId'                    => (int)$clubId,
            'activityTypes'             => $activitiesTypes,
            'lastModified'              => $byLastModified,
            'withNoPrivateCoaching'     => true,//Pour les plans d'entrainements : si event privé uniquement le coaché et le coach peuvent voir l'activité
            'offset'                    => $offset,
            'perPage'                   => $numActivitiesPerPage
        ));

        $offset += count($activities);

        $responseDatas = array(
            'offset'    => $offset,
            'html'      => $this->render(
                'KsActivityBundle:Activity:_activities.html.twig',
                array('activities' => $activities)
            )->getContent()
        ); 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/publishStatus/{clubId}", requirements={"clubId" = "\d+"}, name = "ksClub_publishStatus", options={"expose"=true} )
     */
    public function publishStatusAction($clubId)
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $user               = $this->get('security.context')->getToken()->getUser();
        
        $club               = $clubRep->find( $clubId );
        
        if (!is_object( $club )) {
            $impossibleFindClubMsg = $this->get('translator')->trans('impossible-to-find-club-%clubId%', array( '%clubId%' => $clubId ));
            throw $this->createNotFoundException($impossibleFindClubMsg);
        }
        
        $activityStatus     = new \Ks\ActivityBundle\Entity\ActivityStatus();
        $activityStatus     ->setClub( $club );

        $form = $this->createForm(new \Ks\ActivityBundle\Form\ActivityStatusType(), $activityStatus);
        
         // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $formHandler = new \Ks\ActivityBundle\Form\ActivityStatusHandler($form, $request, $em, $this->container);

        $responseDatas = $formHandler->process();
        
        //Si l'activité a été publié
        if ($responseDatas['publishResponse'] == 1) {
            //On abonne l'utilisateur à l'activité
            $activityRep->subscribeOnActivity($responseDatas['activityStatus'], $user);
            
            $responseDatas['html'] = $this->render(
                'KsActivityBundle:Activity:_activity.html.twig',
                $activityRep->findActivities(array('activityId' => $responseDatas['activityStatus']->getId()))
            )->getContent();
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/publishLink/{clubId}", requirements={"clubId" = "\d+"}, name = "ksClub_publishLink", options={"expose"=true} )
     */
    public function publishLinkAction( $clubId )
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        
        $user               = $this->get('security.context')->getToken()->getUser();
        
        $club               = $clubRep->find( $clubId );
        
        if (!is_object( $club )) {
            $impossibleFindClubMsg = $this->get('translator')->trans('impossible-to-find-club-%clubId%', array( '%clubId%' => $clubId ));
            throw $this->createNotFoundException($impossibleFindClubMsg);
        }
        
        $activityStatus     = new \Ks\ActivityBundle\Entity\ActivityStatus();
        $activityStatus     ->setClub( $club );
        
        $form = $this->createForm(new \Ks\ActivityBundle\Form\ActivityLinkType(), $activityStatus);
        
        // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $statusHandler = new \Ks\ActivityBundle\Form\ActivityStatusHandler($form, $request, $em, $this->container);

        $responseDatas = $statusHandler->process();
        
        //Si l'activité a été publié
        if($responseDatas['publishResponse'] == 1) {
            //On abonne l'utilisateur à l'activité
            $activityRep->subscribeOnActivity($responseDatas['activityStatus'], $user);
                        
            $responseDatas['html'] = $this->render(
                'KsActivityBundle:Activity:_activity.html.twig',
                $activityRep->findActivities(array('activityId' => $responseDatas['activityStatus']->getId()))
            )->getContent();
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
    
    /**
     * @Route("/publishAlbumPhoto/{clubId}", requirements={"clubId" = "\d+"}, name = "ksClub_publishAlbumPhoto", options={"expose"=true} )
     */
    public function publishAlbumPhotoAction( $clubId )
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $activityRep        = $em->getRepository('KsActivityBundle:Activity');
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        
        $user               = $this->get('security.context')->getToken()->getUser();
        $club               = $clubRep->find( $clubId );
        
        if (!is_object( $club )) {
            $impossibleFindClubMsg = $this->get('translator')->trans('impossible-to-find-club-%clubId%', array( '%clubId%' => $clubId ));
            throw $this->createNotFoundException($impossibleFindClubMsg);
        }

        $responseDatas = array();
        $responseDatas["publishResponse"] = 1;
        
        $parameters = $request->request->all();
        $description = isset( $parameters["description"] ) ? $parameters["description"] : "" ;
        $localisation = isset( $parameters["localisation"] ) ? $parameters["localisation"] : array(
            "fullAdress"        => "",
            "countryArea"       => "",
            "countryCode"       => "",
            "town"              => "",
            "latitude"          => "",
            "longitude"         => ""
        ) ;
        $uploadedPhotos = isset( $parameters["uploadedPhotos"] ) ? $parameters["uploadedPhotos"] : array() ;
                
        $uploadDirRelative = $this->container->get('templating.helper.assets')->getUrl('uploads');
        $uploadDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $uploadDirRelative . "/photos/";
        $imgDirRelative = $this->container->get('templating.helper.assets')->getUrl('img');
        $activitiesDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/activities/";
        //var_dump($uploadedPhotos);
        
        $photoAlbum = new\Ks\ActivityBundle\Entity\PhotoAlbum();
        $photoAlbum->setClub( $club );
        $photoAlbum->setDescription($description);
        
        if( ! empty ( $localisation["fullAdress"] ) ) {
            $place = new \Ks\EventBundle\Entity\Place();
            $place->setFullAdress( $localisation["fullAdress"] );
            $place->setRegionLabel( $localisation["countryArea"] );
            $place->setCountryCode( $localisation["countryCode"] );
            $place->setLatitude( $localisation["latitude"] );
            $place->setLongitude( $localisation["longitude"] );
            $place->setTownLabel( $localisation["town"] );
            $em->persist($place);
            
            $photoAlbum->setPlace( $place );
        }
        $em->persist( $photoAlbum );
        $em->flush();
        
        foreach( $uploadedPhotos as $key => $uploadedPhoto ) {

            //On récupère l'extension de la photo
            $ext = explode('.', $uploadedPhoto);
            $ext = array_pop($ext);
            $ext = "." . $ext;

            $activityId = $photoAlbum->getId();

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

                $photo->setActivity($photoAlbum);
                $photoAlbum->addPhoto($photo);
                $em->persist($photo);
                $em->persist($photoAlbum);

            } else {
                $em->remove($photo);
                $movePhotoResponse = -1;
                $responseDatas["publishResponse"] = -1;
            }

            $responseDatas["movePhotosResponse"][$key] = $movePhotoResponse;
        }
        
        if ($responseDatas["publishResponse"] == 1) {
            //On abonne l'utilisateur à l'activité
            $activityRep->subscribeOnActivity( $photoAlbum, $user );
            
            $em->flush();
            $responseDatas['html'] = $this->render(
                'KsActivityBundle:Activity:_activity.html.twig',
                $activityRep->findActivities(array('activityId' => $photoAlbum->getId()))
            )->getContent();
        } else {
            //On supprime l'entité photo album
            $em->remove($photoAlbum);
            $em->flush();
        }
                
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
    
    /**
     * @Route("/activitySessionClubForm/{clubId}", defaults={"activityId" = "new"} )
     * @Route("/activitySessionClubForm/{clubId}/{activityId}", name = "ksClub_activitySessionForm")
     */
    public function activitySessionClubFormAction($clubId, $activityId) {
        $em             = $this->getDoctrine()->getEntityManager();
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $clubRep        = $em->getRepository('KsClubBundle:Club');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $activitySession = new \Ks\ActivityBundle\Entity\ActivitySession($user);
        $activitySportChoiceForm = $this->createForm(new \Ks\ActivityBundle\Form\SportType(null), $activitySession);
        
        $activity = $activityRep->find($activityId);
        
        $club = $clubRep->find($clubId);
        
        return $this->render('KsClubBundle:Activity:activitySessionClubForm.html.twig', array(
             'activitySportChoiceForm' => $activitySportChoiceForm->createView(),
             'activity'    => $activity,
             'club'     => $club
        )); 
    }
}