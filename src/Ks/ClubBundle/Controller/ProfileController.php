<?php

namespace Ks\ClubBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;


/**
 * Profile controller.
 *
 */
class ProfileController extends Controller
{
    /**
     * @Route("/{clubId}/{creationOrEdition}/informations", name = "ksProfileClub_informations", options={"expose"=true} )
     * @Template()
    */
    public function informationsAction($clubId, $creationOrEdition)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $clubRep    = $em->getRepository('KsClubBundle:Club');
        $request = $this->getRequest();
        
        if ( $creationOrEdition != "creation" && $creationOrEdition != "edition" ) {
            throw $this->createNotFoundException("Impossible d'accéder à cette section");
        }
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'clubs');
        if( $creationOrEdition == "edition" ) {
            $session->set('clubIdSelected', $clubId);
        } else {
            $session->set('clubIdSelected', null);
        }
        $session->set('page', 'clubInfos');

        $user   = $this->container->get('security.context')->getToken()->getUser();
        $club = $clubRep->find($clubId);
        
        if( !is_object($club) ) {
            $club = new \Ks\ClubBundle\Entity\Club();
            $clubInfos = array(
                "id" => $clubId,
                "name" => null,
                "avatar" => null
            );
        } else {
            $clubInfos = array(
                "id" => $club->getId(),
                "name" => $club->getName(),
                "avatar" => $club->getAvatar()
            );
        }

        $form   = $this->createForm(new \Ks\ClubBundle\Form\ProfileInformationsType($user), $club);
        $errors = array();
        $post = false;
        
        if( $request->getMethod() == 'POST' ) {
            
            $formHandler = new \Ks\ClubBundle\Form\ProfileHandler($form, $request, $em, $this->container, $user);
            $responsedatas = $formHandler->process();
            if( $responsedatas["response"] == 1 ){
                $this->get('session')->setFlash('alert alert-success', 'Les informations du club ont été mises à jour.');
                
                if ( $creationOrEdition == "creation" ) {
                    //on coche l'action correspondante dans la checklist
                    $em->getRepository('KsUserBundle:ChecklistAction')->checkCreateClub($user->getId());
                    return $this->redirect($this->generateUrl('ksProfileClub_adresses', array(
                        "clubId"            => $responsedatas["club"]->getId(),
                        "creationOrEdition" => $creationOrEdition
                    )));
                } 
           } else {
               $post = true;
               $errors = $responsedatas["errors"];
           }
        }

        return array(
            'club'              => $club,
            'form'              => $form->createView(),
            'creationOrEdition' => $creationOrEdition,
            'clubInfos'         => $clubInfos,
            "post"              => $post,
            'errors'            => $errors
            
        );
    }
    
     /**
     * @Route("/{clubId}/{creationOrEdition}/adresses", name = "ksProfileClub_adresses" )
     * @Template()
    */
    public function adressesAction($clubId, $creationOrEdition)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $clubRep    = $em->getRepository('KsClubBundle:Club');
        $request = $this->getRequest();
        
        if ( $creationOrEdition != "creation" && $creationOrEdition != "edition" ) {
            throw $this->createNotFoundException("Impossible d'accéder à cette section");
        }

        $user   = $this->container->get('security.context')->getToken()->getUser();
        $club = $clubRep->find($clubId);
        
        if( !is_object($club) ) {
            $club = new \Ks\ClubBundle\Entity\Club();
            $clubInfos = array(
                "id" => $clubId,
                "name" => null,
                "avatar" => null
            );
        } else {
            $clubInfos = array(
                "id" => $club->getId(),
                "name" => $club->getName(),
                "avatar" => $club->getAvatar()
            );
        }

        $form   = $this->createForm(new \Ks\ClubBundle\Form\ProfileAdressesType($user), $club);
        $errors = array();
        $post = false;
        
        /*---------Gmap--------------------*/
        // on recupére le service geocode
        $geocoder = $this->get('ivory_google_map.geocoder');
        // Geocode a location ici Paris (valeur par défaut du marker)
        $response = $geocoder->geocode('Paris, France');
        // on recupére le service google map
        $map = $this->get('ivory_google_map.map');
        //on enlève le prefix d'appel de la variable Javascript
        $map->setPrefixJavascriptVariable('');
        //on nomme la vaiable map ; traitment en js avec ce nom la 
        $map->setJavascriptVariable('map');
        //Zoom par défaut de la carte 
        $map->setMapOption('zoom', 7);
        $map->setLanguage('fr');
        //on récupère un marker
        $marker = $this->get('ivory_google_map.marker');
         //on enlève le prefix d'appel de la variable Javascript
        $marker->setPrefixJavascriptVariable('');
         //on nomme la vaiable map ; traitment en js avec ce nom la 
        $marker->setJavascriptVariable('marker');
         //Normalement un seul résultat pour Paris
        if(count($response->getResults())==1){
            foreach($response->getResults() as $result){
                $marker->setPosition($result->getGeometry()->getLocation());
                $latitude = $result->getGeometry()->getLocation()->getLatitude();
                $longitude = $result->getGeometry()->getLocation()->getLongitude();
                $map->setCenter($latitude, $longitude, true);
            }
        }
        //Dans le formulaire d'edition on essaye de récupérer la géolocalisation
        //de l'utilisateur si elle existe
        $userDetail = $user->getUserDetail();
        if( $userDetail != null ){
            $latitude = $userDetail->getLatitude();
            $longitude = $userDetail->getLongitude();

            if($latitude && $longitude){
                $resp = $geocoder->reverse($latitude, $longitude);
                $result = $resp->getResults();
                $nbResult =  count($result);
                if($nbResult > 0){
                    $marker->setPosition($result[0]->getGeometry()->getLocation());
                    $map->setCenter($latitude, $longitude, true);
                }
            }
        }

        //on ajoute le marker 
        $map->addMarker($marker);
        
        if( $request->getMethod() == 'POST' ) {
            
            $formHandler = new \Ks\ClubBundle\Form\ProfileHandler($form, $request, $em, $this->container, $user);
            $responsedatas = $formHandler->process();
            if( $responsedatas["response"] == 1 ){
                $this->get('session')->setFlash('alert alert-success', "L'adresse du club a été mise à jour.");
               
                if ( $creationOrEdition == "creation" ) {
                    return $this->redirect($this->generateUrl('ksProfileClub_avatars', array(
                        "clubId"            => $responsedatas["club"]->getId(),
                        "creationOrEdition" => $creationOrEdition
                    )));
                }
           } else {
               $post = true;
               $errors = $responsedatas["errors"];
           }
        } 

        return array(
            'form'              => $form->createView(),
            'creationOrEdition' => $creationOrEdition,
            'clubInfos'         => $clubInfos,
            "map"               => $map,
            "post"              => $post,
            'errors'            => $errors
            
        );
    }
    
    /**
     * @Route("/{clubId}/{creationOrEdition}/avatars", name = "ksProfileClub_avatars" )
     * @Template()
    */
    public function avatarsAction($clubId, $creationOrEdition)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $clubRep    = $em->getRepository('KsClubBundle:Club');
        $helper     = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $request = $this->getRequest();
        
        if ( $creationOrEdition != "creation" && $creationOrEdition != "edition" ) {
            throw $this->createNotFoundException("Impossible d'accéder à cette section");
        }

        $user   = $this->container->get('security.context')->getToken()->getUser();
        $club = $clubRep->find($clubId);
        
        
        
        if( !is_object($club) ) {
            $club = new \Ks\ClubBundle\Entity\Club();
            $clubInfos = array(
                "id" => $clubId,
                "name" => null,
                "avatar" => null
            );
        } else {
            $clubInfos = array(
                "id" => $club->getId(),
                "name" => $club->getName(),
                "avatar" => $club->getAvatar()
            );
        }
        
        /*$pathOldImage = null;
        $userDetail = $user->getUserDetail();
        if( $userDetail != null ){
            if( $userDetail->getImageName() != null ) {
                $pathOldImage = $helper->asset($userDetail, 'image');
                $pathOldImage = substr ($pathOldImage , 1 );
                $pathOldImageResize = "uploads/images/users/resize_48x48/".$userDetail->getImageName();
            }
        }*/
        
        //$form   = $this->createForm(new \Ks\ClubBundle\Form\ProfileAvatarsType(), $club);
        //$errors = array();
        //$post = false;
        
        if( $request->getMethod() == 'POST' ) {
            
            //$formHandler = new \Ks\ClubBundle\Form\ProfileHandler($form, $request, $em, $this->container, $user);
            //$responsedatas = $formHandler->process();
            //if( $responsedatas["response"] == 1 ){
                $this->get('session')->setFlash('alert alert-success', "L'avatar du club a été mis à jour.");
                
                if ( $creationOrEdition == "creation" ) {
                    return $this->redirect($this->generateUrl('ksProfileClub_sports', array(
                        "clubId"            => $clubId,
                        "creationOrEdition" => $creationOrEdition
                    )));
                }
           /*} else {
                $post = true;
                $errors = $responsedatas["errors"];
           }*/
        } 

        return array(
            //'form'              => $form->createView(),
            'creationOrEdition' => $creationOrEdition,
            'clubInfos'         => $clubInfos,
            //"post"              => $post,
            //'errors'            => $errors
            
        );
    }
    
        /**
     * @Route("/{clubId}/{creationOrEdition}/sports", name = "ksProfileClub_sports" )
     * @Template()
    */
    public function sportsAction($clubId, $creationOrEdition)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $clubRep    = $em->getRepository('KsClubBundle:Club');
        $sportRep   = $em->getRepository('KsActivityBundle:Sport');
        $request    = $this->getRequest();
        
        if ( $creationOrEdition != "creation" && $creationOrEdition != "edition" ) {
            throw $this->createNotFoundException("Impossible d'accéder à cette section");
        }

        $user   = $this->container->get('security.context')->getToken()->getUser();
        $club = $clubRep->find($clubId);
        
        if( !is_object($club) ) {
            $club = new \Ks\ClubBundle\Entity\Club();
            $clubInfos = array(
                "id" => $clubId,
                "name" => null,
                "avatar" => null
            );
        } else {
            $clubInfos = array(
                "id" => $club->getId(),
                "name" => $club->getName(),
                "avatar" => $club->getAvatar()
            );
        }

        $form   = $this->createForm(new \Ks\ClubBundle\Form\ProfileSportsType($user), $club);
        $errors = array();
        $post = false;
        
        if( $request->getMethod() == 'POST' ) {
            
            $formHandler = new \Ks\ClubBundle\Form\ProfileHandler($form, $request, $em, $this->container, $user);
            $responsedatas = $formHandler->process();
            if( $responsedatas["response"] == 1 ) {
                $this->get('session')->setFlash('alert alert-success', 'Les sports de ton profil ont été mis à jour.');
               
                if ( $creationOrEdition == "creation" ) {
                    return $this->redirect($this->generateUrl('ksProfileClub_inviteFriends', array(
                        "clubId"            => $responsedatas["club"]->getId(),
                        "creationOrEdition" => $creationOrEdition
                    )));
                }
           } else {
                $post = true;
                $errors = $responsedatas["errors"];
           }
        } 
        
        $sports = $sportRep->findBy(
           array(),
           array("label" => "asc")
        );
        

        return array(
            'form'              => $form->createView(),
            'creationOrEdition' => $creationOrEdition,
            'clubInfos'         => $clubInfos,
            "sports"            => $sports,
            "club"              => $club,
            "post"              => $post,
            'errors'            => $errors
            
        );
    }
    
    /**
     * @Route("/{clubId}/{creationOrEdition}/inviteFriends", name = "ksProfileClub_inviteFriends" )
     * @Template()
    */
    public function inviteFriendsAction($clubId, $creationOrEdition)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $clubRep    = $em->getRepository('KsClubBundle:Club');
        $userRep    = $em->getRepository('KsUserBundle:User');
        $request = $this->getRequest();
        
        if ( $creationOrEdition != "creation" && $creationOrEdition != "edition" ) {
            throw $this->createNotFoundException("Impossible d'accéder à cette section");
        }

        $user   = $this->container->get('security.context')->getToken()->getUser();
        $club = $clubRep->find($clubId);
        
        if( !is_object($club) ) {
            $club = new \Ks\ClubBundle\Entity\Club();
            $clubInfos = array(
                "id" => $clubId,
                "name" => null,
                "avatar" => null
            );
        } else {
            $clubInfos = array(
                "id" => $club->getId(),
                "name" => $club->getName(),
                "avatar" => $club->getAvatar()
            );
        }
        
        
        $friendsIds  = $userRep->getFriendIds($user->getId());
        if( count($friendsIds) < 1 ) $friendsIds[] = 0;
        
        //Récupération de la liste des amis trés par nom d'utilisateurs
        $friends = $userRep->findUsers(array("usersIds" => $friendsIds), $this->get('translator'));

        //$form   = $this->createForm(new \Ks\ClubBundle\Form\ProfileInviteFriendsType($user), $club);
        
        if( $request->getMethod() == 'POST' ) { 
            if ( $creationOrEdition == "creation" ) {
                return $this->redirect($this->generateUrl('ksClub_public_profile', array(
                    "clubId"            => $clubId
                )));
            }
        } 
        
        

        return array(
            //'form'              => $form->createView(), 
            'friends'           => $friends,
            'creationOrEdition' => $creationOrEdition,
            'clubInfos'         => $clubInfos,
        );
    }
    
    /**
     * @Route("/changeAvatar/{clubId}", name = "ksProfileClub_changeAvatar", options={"expose"=true} )
     */
    public function changeAvatarAction($clubId)
    {
        $request            = $this->get('request');
        $em                 = $this->getDoctrine()->getEntityManager();
        $clubRep    = $em->getRepository('KsClubBundle:Club');
        $user               = $this->get('security.context')->getToken()->getUser();

        $club = $clubRep->find($clubId);
        
        if( !is_object($club) ) {
            $club = new \Ks\ClubBundle\Entity\Club();
        }
        
        $responseDatas = array();
        $responseDatas["response"] = 1;
        
        $parameters = $request->request->all();
        
        $uploadedPhotos = isset( $parameters["uploadedPhotos"] ) ? $parameters["uploadedPhotos"] : array() ;
                
        $uploadDirRelative = $this->container->get('templating.helper.assets')->getUrl('uploads');
        $uploadDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $uploadDirRelative . "/clubs/";
        $imgDirRelative = $this->container->get('templating.helper.assets')->getUrl('img');
        $clubsDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/clubs/";
        
        if (! is_dir( $clubsDirAbsolute ) ) mkdir( $clubsDirAbsolute );
        
        foreach( $uploadedPhotos as $key => $uploadedPhoto ) {
  
            $clubDirAbsolute = $clubsDirAbsolute.$clubId."/";

            //On crée le dossier qui contient les images de l'article s'il n'existe pas
            if (! is_dir( $clubDirAbsolute ) ) mkdir( $clubDirAbsolute );

            $clubOriginalPhotosDirAbsolute = $clubDirAbsolute . 'original/';
            if (! is_dir( $clubOriginalPhotosDirAbsolute ) ) mkdir($clubOriginalPhotosDirAbsolute);

            $clubThumbnailPhotosDirAbsolute = $clubDirAbsolute . 'resize_48x48/';
            if (! is_dir( $clubThumbnailPhotosDirAbsolute ) ) mkdir($clubThumbnailPhotosDirAbsolute);

            //On la déplace les photos originales et redimentionnés
            $renameOriginale = rename( $uploadDirAbsolute."original/"  . $uploadedPhoto, $clubOriginalPhotosDirAbsolute.$uploadedPhoto );
            $renameThumbnail = rename( $uploadDirAbsolute."resize_48x48/" . $uploadedPhoto, $clubThumbnailPhotosDirAbsolute.$uploadedPhoto );

            if( $renameOriginale && $renameThumbnail){
                $movePhotoResponse = 1;
                $club->setAvatar($uploadedPhoto);
                $em->persist($club);
                $em->flush();
                
            } else {
                
                $movePhotoResponse = -1;
                $responseDatas["response"] = -1;
            }

            $responseDatas["movePhotosResponse"][$key] = $movePhotoResponse;
        }
        
        $responseDatas["html"] = $this->render('KsClubBundle:Club:_clubImage.html.twig', array(
            'club_avatar'   => $club->getavatar(),
            'club_id'       => $club->getId(),
            'club_name'     => $club->getName()
        ))->getContent();
                
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
}
