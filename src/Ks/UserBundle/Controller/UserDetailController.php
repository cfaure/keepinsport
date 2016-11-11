<?php

namespace Ks\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Ks\UserBundle\Entity\UserDetail;
use Ks\UserBundle\Form\UserDetailType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Ks\UserBundle\Form\UserDetailHandler;
use Ks\UserBundle\Form\ProfileUserHandler;



/**
 * UserDetail controller.
 *
 */
class UserDetailController extends Controller
{

    /**
     * @Route("/new", name = "userdetail_new" )
    */
    public function newAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $sportRep   = $em->getRepository('KsActivityBundle:Sport');
        
        // Si déja profil
        // redirection vers le formulaire d'edition
        $user   = $this->container->get('security.context')->getToken()->getUser();

        if($user->getUserDetail()){
            return $this->redirect($this->generateUrl('userdetail_edit'));
        }
        
        $entity = new UserDetail();
        $entity->setReceivesDailyEmail(true);
        //$test = new UserDetailType();
        $userDetailform   = $this->createForm(new UserDetailType(), $entity);
        $userform   = $this->createForm(new \Ks\UserBundle\Form\ProfileUserType(), $user);
        //$userForm         = $this->createForm(new UserType(), $user);
        //var_dump($form->getChildren());
        
        /*foreach($form->getChildren() as $field){
            if($field->getName()=="upload"){
                
                
            }    
        }*/
        
        /*---------Gmap--------------------*/
        // on recupére le service geocode
        $geocoder = $this->get('ivory_google_map.geocoder');
        // Geocode a location ici Paris
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
        //on ajoute le marker 
        $map->addMarker($marker);
        /*---------------------*/
        
        $sports = $sportRep->findAll();
        return $this->render('KsUserBundle:UserDetail:new.html.twig', array(
            //'entity' => $entity,
            'map'           => $map,
            'form'          => $userDetailform->createView(),
            'userForm'      => $userform->createView(),
            'sports'        => $sports,
            'user'          => $user
        ));

    }

    /**
     * @Route("/create", name = "userdetail_create" )
    */
    public function createAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $sportRep   = $em->getRepository('KsActivityBundle:Sport');
        
        $entity  = new UserDetail();
        $request = $this->getRequest();

        //$form    = $this->createForm(new UserDetailType(), $entity);
        
        $user   = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($user) )
        {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }
        
        $userform   = $this->createForm(new \Ks\UserBundle\Form\ProfileUserType(), $user);
        /*---------Gmap--------------------*/
        // on recupére le service geocode
        $geocoder = $this->get('ivory_google_map.geocoder');
        // Geocode a location ici Paris
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
        //on ajoute le marker 
        $map->addMarker($marker);
        /*--------------------------------------------*/
        
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        
        $formHandler = new ProfileUserHandler($userform, $request, $this->getDoctrine()->getEntityManager(), $this->container);
        
        $responseDatas = $formHandler->process();
        if( $responseDatas['modifResponse'] == 1 )
        {
            //Récupération du chemin de l'image originale et redimensionnement
            if($userform->getData()->getUserDetail()->getImageName()){
                $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
                $pathImage = $helper->asset($responseDatas['user']->getUserDetail(), 'image');
                if($pathImage!=null){
                    $pathImage = substr ($pathImage , 1 );
                    $width = 48;
                    $height = 48;
                    $pathImageResize = "uploads/images/users/resize_".$width."x".$height."/".$responseDatas['user']->getUserDetail()->getImageName();
                    $responseDatas['user']->getUserDetail()->resizeImage($pathImage,$pathImageResize,$width, $height);
                }
            }    
          
            $this->get('session')->setFlash('alert alert-success', 'users.profil_creation_success');
            return $this->redirect($this->generateUrl('userdetail_edit'));
        } else {
            $messageHtml = $responseDatas["errorMessage"];
            //var_dump( $responseDatas["errors"]);
            if( isset( $responseDatas["errors"] ) && !empty( $responseDatas["errors"] ) ) {
                $messageHtml .= "<ul>";
                foreach( $responseDatas["errors"] as $error ) {
                    if( is_array( $error )) {
                        foreach( $error as $e ) {
                            $messageHtml .= "<li>" . $e . "</li>";
                        }
                    } else {
                        $messageHtml .= "<li>" . $error . "</li>";
                    }
                }
                $messageHtml .= "</ul>";
            }
            $this->get('session')->setFlash('alert alert-error', $messageHtml);
        }
       
        $sports = $sportRep->findAll();
        
        return $this->render('KsUserBundle:UserDetail:new.html.twig', array(
            //'entity' => $entity,
            'map'   => $map,
            'userForm'   => $userform->createView(),
            'sports' => $sports
        ));
    }

    /**
     * @Route("/edit", name = "userdetail_edit" )
    */
    public function editAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $sportRep   = $em->getRepository('KsActivityBundle:Sport');
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        $userDetail = $currentUser->getUserDetail();
        if($userDetail==null){
             return $this->redirect($this->generateUrl('userdetail_new'));
        }
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

       
        $latitude = $userDetail->getLatitude();
        $longitude = $userDetail->getLongitude();
        $idUserDetail = $userDetail->getId();
        if($latitude && $longitude){
        $resp = $geocoder->reverse($latitude, $longitude);
        $result = $resp->getResults();
        $nbResult =  count($result);
            if($nbResult > 0){
                $marker->setPosition($result[0]->getGeometry()->getLocation());
                $map->setCenter($latitude, $longitude, true);
            }
        }
        
       
        //on ajoute le marker 
        $map->addMarker($marker);
        /*---------------------*/
        /*$entity = $em->getRepository('KsUserBundle:UserDetail')->find($idUserDetail);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserDetail entity.');
        }

        $editForm = $this->createForm(new UserDetailType(), $entity);
        $request = null;*/

   

        //$formHandler = new UserDetailHandler($editForm, $request, $this->getDoctrine()->getEntityManager(), $currentUser);

        $user   = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($user) )
        {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }
        
        $userform   = $this->createForm(new \Ks\UserBundle\Form\ProfileUserType(), $user);
        
        $sports = $sportRep->findAll();
        
        return $this->render('KsUserBundle:UserDetail:edit.html.twig', array(
            //'entity'      => $entity,
            'map'         => $map,
            //'edit_form'   => $editForm->createView(),
            'userForm'   => $userform->createView(),
            'sports'      => $sports,
        ));
    }

    /**
     * @Route("/update", name = "userdetail_update" )
    */
    public function updateAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $sportRep   = $em->getRepository('KsActivityBundle:Sport');
        
        $currentUser    = $this->container->get('security.context')->getToken()->getUser();
        $userDetail     = $currentUser->getUserDetail();
        $idUserDetail   = $userDetail->getId();
        $imageUserDetail = $userDetail->getImageName();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $width = 48;
        $height = 48;
        if($imageUserDetail!=null){
           $pathOldImage = $helper->asset($userDetail, 'image');
           $pathOldImage = substr ($pathOldImage , 1 );
           $pathOldImageResize = "uploads/images/users/resize_".$width."x".$height."/".$userDetail->getImageName();
        }
        
        $entity = $em->getRepository('KsUserBundle:UserDetail')->find($idUserDetail);
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserDetail entity.');
        }

        $editForm   = $this->createForm(new UserDetailType(), $entity);

        $request = $this->getRequest();
    
        /*---------Gmap--------------------*/
        // on recupére le service geocode
        $geocoder = $this->get('ivory_google_map.geocoder');
        // Geocode a location ici Paris (valeur par défaut du marker)
        $response = $geocoder->geocode('Paris, France');
        // on recupére le service google map
        $map = $this->get('ivory_google_map.map');
        //on enlève le prefix d'appel de la variable Javascript
        $map->setPrefixJavascriptVariable('');
        //on nomme la variable map ; traitment en js avec ce nom la 
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
        $arrayValues = $request->request->get('ks_userbundle_userdetailtype');
        $latitude    = $arrayValues['latitude'];
        $longitude   = $arrayValues['longitude'];
        if($latitude && $longitude){
           $resp = $geocoder->reverse($latitude, $longitude);
           $result = $resp->getResults();
           //var_dump($result);
           $nbResult =  count($result);
           if($nbResult > 0){
               $marker->setPosition($result[0]->getGeometry()->getLocation());
               $map->setCenter($latitude, $longitude, true);
           }
        }
        //on ajoute le marker 
        $map->addMarker($marker);

        /*----------------------------------------------*/
        
        $user   = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($user) )
        {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }
        
        $userform   = $this->createForm(new \Ks\UserBundle\Form\ProfileUserType(), $user);
        
        $formHandler = new ProfileUserHandler($userform, $request, $this->getDoctrine()->getEntityManager(), $this->container);
        //$formHandler = new UserDetailHandler($editForm, $request, $this->getDoctrine()->getEntityManager(), $currentUser);
        
        $responseDatas = $formHandler->process();
        if( $responseDatas["modifResponse"] == 1 )
        {
            if($editForm->getData()->getImageName()){
                
                $pathImage = $helper->asset($entity, 'image');
                $pathImage = substr ($pathImage , 1 );

                if($pathImage!=null && (isset($pathOldImage)) && $pathOldImage!=$pathImage){

                    $pathImageResize = "uploads/images/users/resize_".$width."x".$height."/".$entity->getImageName();
                    $entity->resizeImage($pathImage,$pathImageResize,$width, $height);
                    //on oublie pas de supprimer les anciennes images si elles existent
                    if(isset($pathOldImage)){
                        $entity->removeUpload($pathOldImage);
                        if(isset($pathOldImageResize)){
                            $entity->removeUpload($pathOldImageResize);
                        }
                    }
                }
            }    

            $this->get('session')->setFlash('alert alert-success', 'users.profil_update_success');
            //return $this->redirect($this->generateUrl('userdetail_edit'));
        } else {
            $messageHtml = $responseDatas["errorMessage"];
            if( isset( $responseDatas["errors"] ) && !empty( $responseDatas["errors"] ) ) {
                $messageHtml .= "<ul>";
                foreach( $responseDatas["errors"] as $error ) {
                    $messageHtml .= "<li>" . $error . "</li>";
                }
                $messageHtml .= "</ul>";
            }
            $this->get('session')->setFlash('alert alert-error', $messageHtml);
        }
        
        
        
        $sports = $sportRep->findAll();

        return $this->render('KsUserBundle:UserDetail:edit.html.twig', array(
            //'entity'      => $entity,
            'map'         => $map,
            //'edit_form'   => $editForm->createView(),
            'sports'      => $sports,
            //'user'        => $currentUser
            'userForm'   => $userform->createView(),
        ));
    }
}
