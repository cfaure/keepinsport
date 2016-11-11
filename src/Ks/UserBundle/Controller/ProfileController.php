<?php

namespace Ks\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;
use Ivory\GoogleMap\Overlays\Animation;
use Ivory\GoogleMap\Overlays\Marker;

/**
 * Profile controller.
 *
 */
class ProfileController extends Controller
{
    /**
     * @Route("", name = "ksProfile_V2", options={"expose"=true} )
     * @Template()
    */
    public function profileAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $request    = $this->getRequest();
        $sportRep   = $em->getRepository('KsActivityBundle:Sport');
        
        $user   = $this->container->get('security.context')->getToken()->getUser();

        $errors = array();
        $post = false;
        $informationsForm   = $this->createForm(new \Ks\UserBundle\Form\ProfileInformationsType($user), $user);
        $addressForm        = $this->createForm(new \Ks\UserBundle\Form\ProfileAdressesType(), $user);
        $sportsForm         = $this->createForm(new \Ks\UserBundle\Form\ProfileSportsType(), $user);
        $questionnaireForm  = $this->createForm(new \Ks\UserBundle\Form\ProfileQuestionnaireType(), $user);
        
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

        $map->setStylesheetOptions(array(
            'width'  => '825px',
            'height' => '600px',
        ));
        $map->setMapOption('zoom', 10);
        $map->setMapOption('disableDoubleClickZoom', false);
        $map->setLanguage('fr');
        //on récupère un marker
        $marker = $this->get('ivory_google_map.marker');
         //on enlève le prefix d'appel de la variable Javascript
        $marker->setPrefixJavascriptVariable('');
         //on nomme la vaiable map ; traitment en js avec ce nom la 
        $marker->setJavascriptVariable('marker');
        $marker->setOption('clickable', false);
        $marker->setOption('flat', true);
        $marker->setAnimation('bounce');
        $marker->setAnimation('drop');
        $marker->setOption('clickable', true);
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
        if( !is_null($userDetail)) {
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
        
        $sports = $sportRep->findBy(
           array(),
           array("label" => "asc")
        );
        
        $imageName = null;
        if( !is_null($userDetail)) {
            if (!is_null($userDetail->getImageName())) $imageName = $userDetail->getImageName();
        }
        
        return array(
            'edit'              => $user->getCompletedHisProfileRegistration() ? 1 : 0,
            'choosenPack'       => $user->getChoosenPack(),
            'choosenWatch'      => $user->getChoosenWatch(),
            'choosenCoach'      => $user->getChoosenCoach(),
            'choosenPackOffer'  => $user->getChoosenPackOffer(),
            'choosenWatchOffer' => $user->getChoosenWatchOffer(),
            'informationsForm'  => $informationsForm->createView(),
            'addressForm'       => $addressForm->createView(),
            'map'               => $map,
            'sportsForm'        => $sportsForm->createView(),
            'sports'            => $sports,
            'questionnaireForm' => $questionnaireForm->createView(),
            'user_imageName'    => $imageName,
            'post'              => $post,
            'errors'            => $errors
        );
    }
    
    /**
     * Création de la commande dans la table ks_order,
     * et redirection vers la page de paiement.
     * 
     * TODO: en fait il faudrait un mini système de panier et présenter un récap à l'utilisateur
     * avant de rediriger sur le module de paiement.
     *
     * @Route("/process-offer", name = "ksProcessOffer", options={"expose"=true} )
     */
    public function processOfferAction()
    {
        // NOTE CF: ajout des grilles de paiement en dur,
        //  à défaut de les avoir en bdd c'est toujours mieux que des les avoir en js :)
        // TODO: à compléter avec les coachs et/ou à passer en bdd !
        // TODO: passer le calcul des mensualités en automatique...
        // TODO: le code et paybox sont prévus pour le paiement en 4x, à voir si on l'active pour toutes les montres ou uniquement les montres haut de gamme
        static $prices = array(
            // montres Suunto
            'ambit3sport' => array(
                1 => array(250),
                2 => array(200, 199),
                3 => array(133, 133, 133)
            ),
            'ambit3peak' => array(
                1 => array(490),
                2 => array(250, 249),
                3 => array(167, 167, 165)
            ),
            // montres Polar
            'm400' => array(
                1 => array(199),
                2 => array(100, 99),
                3 => array(50,50,49)
            ),
            'v800' => array(
                1 => array(449),
                2 => array(225, 224),
                3 => array(150,150,149)
            ),
            // montres Garmin
            '620' => array(
                1 => array(399),
                2 => array(200, 199),
                3 => array(133, 133, 133)
            ),
            '920xt' => array(
                1 => array(499),
                2 => array(250, 249),
                3 => array(167, 167, 165)
            ),
            // packs
            'premium' => array(
                12  => array(50),
                1   => array(0, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5)
            )
        );
        $payment    = $this->get('ks_payment.provider');
        $em         = $this->getDoctrine()->getEntityManager();
        $request    = $this->getRequest();
        $user       = $this->container->get('security.context')->getToken()->getUser();
        
        $pack             = strtolower($request->get('pack'));
        $packNumPayments  = (int)$request->get('packNumPayments');
        $watch            = strtolower($request->get('watch', ''));
        $watchNumPayments = (int)$request->get('watchNumPayments', '');
        // TODO: manque le choix du coach
                
        // Récupération des montants pour le pack + modalité de paiement.
        if (array_key_exists($pack, $prices) && array_key_exists($packNumPayments, $prices[$pack])) {
            $packPrices = $prices[$pack][$packNumPayments];
        } else {
            throw new \Exception('Le pack sélectionné ne correspond pas à une offre Keepinsport.');
        }
        // Récupération des montants pour la montre + modalité de paiement.
        // NOTE CF: on utilise le modèle comme clé
        if ($watch == '') { // cas: pas de montre sélectionnée
            $watchPrices = array(0); // NOTE CF: c'est plus simple pour le calcul du montant total
        } else if (array_key_exists($watch, $prices) && array_key_exists($watchNumPayments, $prices[$watch])) {
            $watchPrices = $prices[$watch][$watchNumPayments];
        } else {
            throw new \Exception('La montre sélectionnée ne correspond pas à une offre Keepinsport.');
        }
        
        // NOTE CF: d'après ce que je comprends, pour l'instant on ne fait payer que la montre
        // + la totalité de l'abo si l'utilisateur a sélectionné le paiement en 1x
        if (count($packPrices) == 1) {
            $rebate = 15; // NOTE CF: 15€ de promo si paiement en 1x pour l'abo, à voir comment on le gère...
            // on ajoute le paiement total de l'abo au premier mois de paiement pour la montre
            $amounts    = $watchPrices;
            $amounts[0] += $packPrices[0] - $rebate;
        } else {
            $amounts    = $watchPrices;
        }
        
        // Il faut absolument une référence de commande UNIQUE, sinon la transaction sera refusée.
        // On commence donc par créer une nouvelle entrée dans la table ks_order
        // on réutilisera cette entrée pour la confirmation avec paybox
        $order = new \Ks\PaymentBundle\Entity\Order();
        $order->setWatch($watch);
        $order->setPack($pack);
        $order->setAmounts(serialize($amounts));
        $order->setUser($user);
        $createdAt = new \DateTime();
        $order->setCreatedAt($createdAt);
        $order->setUpdatedAt($createdAt);
        $order->setPayboxAnswer('');
        $order->setStatus('created'); // TODO: utiliser une enum ?
        
        $em->persist($order);
        $em->flush();
                
        // C'est l'id $order qui va nous servir de clé unique
        $refCmd = $order->getId();

        $response = new Response(
            // FIXME: vérif sur l'email ? je suis pas sûr que ce soit toujours valide avec les inscriptions Facebook...
            $payment->buildForm($amounts, $refCmd, $user->getEmail())
        );
        
        return $response;
    }
    
    /**
     * @Route("/offers", name = "ksOffers", options={"expose"=true} )
     * @Template()
    */
    public function offersAction()
    {
        $em          = $this->getDoctrine()->getEntityManager();
        $request     = $this->getRequest();
        $user        = $this->container->get('security.context')->getToken()->getUser();
        $userRep     = $em->getRepository('KsUserBundle:User');
        

        $citations = array();
        $usersKs = array("clements", "adrien974", "stephane974", "quentin973", "moreljeanalain", "Sofi", "Domi76974");
        $texts = array();
        $texts[$usersKs[0]] = "Depuis que j'utilise keepinsport, je béneficie d'un suivi constant de mon entraînement et de ma progression. super outil qui permet à pascal blanc d'avoir un oeil attentif sur mon entraînement !";
        $texts[$usersKs[1]] = "Grâce à Keepinsport j'ai gagné en interactivité sur les plans d'entrainement que Pascal Blanc me fait. En cas de soucis le coach me modifie instantanément mon planning, et toutes mes séances sont détaillées ce qui me permet une progression continue. Vraiment un superbe outil au service de notre progression !";
        $texts[$usersKs[2]] = "Pour la préparation de La Diagonale des Fous 2014 j'ai décidé de prendre un coach . Ce n'est pas la motivation qui me manquait , ce n'est pas une perf que je recherchais , j'avais juste besoin d'un plan d'entraînement à suivre ... Pascal Blanc m'a apporté ce suivi , ses conseils , et grâce à Keepinsport j'ai de façon ludique 'enquillé' les séances , j'ai constaté ma progression , je me suis mesuré aux autres ... Et 4 mois après j'ai franchi cette putain de ligne au stade de la Redoute !";
        $texts[$usersKs[3]] = "Idéal pour observer sa progression et atteindre ses objectifs sportifs, merci Keepinsport pour sa plateforme interactive: géniale pour un coaching avec Pascal !";
        $texts[$usersKs[4]] = "Avec Keepinsport, je peux partager mes résussites et mes difficultés lors de mes entraînements !";
        $texts[$usersKs[5]] = "Pour ma part keepinsport m'as permis d'avoir un suivi plus direct avec Pascal, on peut échanger plus rapidement j'ai mon planning sur le site sur lequel je peux mettre des commentaires, on peut apporter une modification au plan si on a un empêchement et de son côté Pascal peut nous suivre de plus près et réadapter le plan selon notre emploi du temps c pratique :)";
        $texts[$usersKs[6]] = " Keepinsport est outil performant qui me permet de mesurer mon effort et ma performance et donne à mon coach Pascal Blanc toutes les données pour vérifier ma pratique correcte des activités planifiées. ";
        
        $citations = array();
        for ($i=0;$i<6;$i++) {
            $userToAffich = $userRep->findOneUser( array(
                "username" => $usersKs[$i]
            ));

            $citations[] = array(
                "user" => $userToAffich,
                "text" => $texts[$usersKs[$i]]
            );
        }
        
        return array(
            'citations' => $citations
        );
    }
    
    /**
     * @Route("/saveInformations", name = "ksProfile_saveInformations", options={"expose"=true} )
     * @Template()
    */
    public function saveInformationsAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $request    = $this->getRequest();
        
        $user   = $this->container->get('security.context')->getToken()->getUser();

        $form   = $this->createForm(new \Ks\UserBundle\Form\ProfileInformationsType($user), $user);
        $errors = array();
        $post = false;
        
        if( $request->getMethod() == 'POST' ) {
            
            $formHandler = new \Ks\UserBundle\Form\ProfileInformationsHandler($form, $request, $em, $this->container);
            $responseDatas = $formHandler->process();
            if( $responseDatas["response"] == 1 ){
                $responseDatas["message"] = 'Les informations de ton profil ont été mises à jour !';
                //On indique qu'il a complété son profil
                $user->setCompletedHisProfileRegistration(true);
                $em->persist($user);
                $em->flush();
           } else {
               $post = true;
               $errors = $responseDatas["errors"];
           }
        } 

        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;
    }
    
    /**
     * @Route("/saveAddress", name = "ksProfile_saveAddress", options={"expose"=true} )
     * @Template()
    */
    public function saveAddressAction()
    {
        $em      = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $user    = $this->container->get('security.context')->getToken()->getUser();
        $form    = $this->createForm(new \Ks\UserBundle\Form\ProfileAdressesType(), $user);
        $errors  = array();
        $post    = false;
        
        if( $request->getMethod() == 'POST' ) {
            $formHandler = new \Ks\UserBundle\Form\ProfileOthersHandler($form, $request, $em, $this->container);
            $responseDatas = $formHandler->process();
            if( $responseDatas["response"] == 1 ){
                $responseDatas["message"] = 'L\'adresse du profil a été mise à jour !';
            }
            else {
               $post = true;
               $errors = $responseDatas["errors"];
            }
        }

        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;
    }
    
    /**
     * @Route("/saveSports", name = "ksProfile_saveSports", options={"expose"=true} )
     * @Template()
    */
    public function saveSportsAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $sportRep   = $em->getRepository('KsActivityBundle:Sport');
        $request    = $this->getRequest();
        
        $user   = $this->container->get('security.context')->getToken()->getUser();

        $form   = $this->createForm(new \Ks\UserBundle\Form\ProfileSportsType(), $user);
        $errors = array();
        $post = false;
        
        if( $request->getMethod() == 'POST' ) {
            
            $formHandler = new \Ks\UserBundle\Form\ProfileOthersHandler($form, $request, $em, $this->container);
            $responseDatas = $formHandler->process();
            if( $responseDatas["response"] == 1 ) {
                $responseDatas["message"] = 'Les sports de ton profil ont été mis à jour !';
            } else {
                $post = true;
                $errors = $responseDatas["errors"];
            }
        } 
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;
    }
    
    /**
     * @Route("/saveQuestionnaire", name = "ksProfile_saveQuestionnaire", options={"expose"=true} )
     * @Template()
    */
    public function saveQuestionnaireAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $request    = $this->getRequest();
        
        $user   = $this->container->get('security.context')->getToken()->getUser();

        $form        = $this->createForm(new \Ks\UserBundle\Form\ProfileQuestionnaireType(), $user);
        $errors = array();
        $post = false;
        
        if( $request->getMethod() == 'POST' ) {
            $formHandler = new \Ks\UserBundle\Form\ProfileOthersHandler($form, $request, $em, $this->container);
            $responseDatas = $formHandler->process();
            if( $responseDatas["response"] == 1 ){
                $responseDatas["message"] = 'Les données de ton questionnaire ont été mises à jour !!';
            }
            else {
               $post = true;
               $errors = $responseDatas["errors"];
            }
        }

        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;
    }
    
    /**
     * @Route("/{creationOrEdition}/inviteFriends", name = "ksProfile_inviteFriends" )
     * @Template()
    */
    public function inviteFriendsAction($creationOrEdition)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $helper     = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $request = $this->getRequest();
        
        if ( $creationOrEdition != "creation" && $creationOrEdition != "edition" ) {
            throw $this->createNotFoundException("Impossible d'accéder à cette section");
        }
        
        if( $request->getMethod() == 'POST' ) {
            $this->get('session')->setFlash('alert alert-success', 'Invitation(s) envoyée(s)');
               
            if ( $creationOrEdition == "creation" ) {
                return $this->redirect($this->generateUrl('ksProfile_mails', array(
                    "creationOrEdition" => $creationOrEdition
                )));
            }
        }


        return array(
            'creationOrEdition' => $creationOrEdition,      
        );
    }
    
    /**
     * @Route("/{creationOrEdition}/mails", name = "ksProfile_mails" )
     * @Template()
    */
    public function mailsAction($creationOrEdition)
    {
        $em      = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        
        if ( $creationOrEdition != "creation" && $creationOrEdition != "edition" ) {
            throw $this->createNotFoundException("Impossible d'accéder à cette section");
        }

        $user   = $this->container->get('security.context')->getToken()->getUser();

        $form   = $this->createForm(new \Ks\UserBundle\Form\ProfileMailsType(), $user);
        $errors = array();
        $post   = false;
        
        if( $request->getMethod() == 'POST' ) {
            
            $formHandler = new \Ks\UserBundle\Form\ProfileOthersHandler($form, $request, $em, $this->container);
            $responsedatas = $formHandler->process();
            if( $responsedatas["response"] == 1 ){
               $this->get('session')->setFlash('alert alert-success', 'La configuration des mails du profil a été mise à jour.');
               
               
               if ( $creationOrEdition == "creation" ) {
                   //return $this->redirect($this->generateUrl('ks_user_public_profile', array("username" => $user->getUsername())));
                    //return $this->redirect($this->generateUrl('ksUser_showChecklist'));
                   return $this->redirect($this->generateUrl('ksProfile_character', array(
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
            'post'              => $post,
            'errors'            => $errors
        );
    }
    
    /**
     * @Route("/changeAvatar", name = "ksProfile_changeAvatar", options={"expose"=true} )
     */
    public function changeAvatarAction()
    {
        $request    = $this->get('request');
        $em         = $this->getDoctrine()->getEntityManager();
        $clubRep    = $em->getRepository('KsClubBundle:Club');
        $user       = $this->get('security.context')->getToken()->getUser();
        
        $responseDatas = array();
        $responseDatas["response"] = 1;
        
        $parameters = $request->request->all();
        
        $uploadedPhotos = isset( $parameters["uploadedPhotos"] ) ? $parameters["uploadedPhotos"] : array() ;
                
        $uploadDirRelative = $this->container->get('templating.helper.assets')->getUrl('uploads');
        $uploadDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $uploadDirRelative . "/users/";
        $imgDirRelative = $this->container->get('templating.helper.assets')->getUrl('img');
        $usersDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/users/";
        
        if (! is_dir( $usersDirAbsolute ) ) mkdir( $usersDirAbsolute );
        
        $count = count($uploadedPhotos);
        
        foreach( $uploadedPhotos as $key => $uploadedPhoto ) {
  
            if ($key == $count -1) {
                $userDirAbsolute = $usersDirAbsolute.$user->getId()."/";

                //On crée le dossier qui contient les images de l'article s'il n'existe pas
                if (! is_dir( $userDirAbsolute ) ) mkdir( $userDirAbsolute );

                $userDirAbsolute_original = $userDirAbsolute . 'original/';
                if (! is_dir( $userDirAbsolute_original ) ) mkdir($userDirAbsolute_original);

                $userDirAbsolute_1024x1024 = $userDirAbsolute . 'resize_1024x1024/';
                if (! is_dir( $userDirAbsolute_1024x1024 ) ) mkdir($userDirAbsolute_1024x1024);

                $userDirAbsolute_512x512 = $userDirAbsolute . 'resize_512x512/';
                if (! is_dir( $userDirAbsolute_512x512 ) ) mkdir($userDirAbsolute_512x512);

                $userDirAbsolute_128x128 = $userDirAbsolute . 'resize_128x128/';
                if (! is_dir( $userDirAbsolute_128x128 ) ) mkdir($userDirAbsolute_128x128);

                $userDirAbsolute_48x48 = $userDirAbsolute . 'resize_48x48/';
                if (! is_dir( $userDirAbsolute_48x48 ) ) mkdir($userDirAbsolute_48x48);

                //On la déplace les photos originales et redimentionnés
                $rename_original = rename( $uploadDirAbsolute."original/"  . $uploadedPhoto, $userDirAbsolute_original.$uploadedPhoto );
                $rename_1024x1024 = rename( $uploadDirAbsolute."resize_1024x1024/" . $uploadedPhoto, $userDirAbsolute_1024x1024.$uploadedPhoto );
                $rename_512x512 = rename( $uploadDirAbsolute."resize_512x512/" . $uploadedPhoto, $userDirAbsolute_512x512.$uploadedPhoto );
                $rename_128x128 = rename( $uploadDirAbsolute."resize_128x128/" . $uploadedPhoto, $userDirAbsolute_128x128.$uploadedPhoto );
                $rename_48x48 = rename( $uploadDirAbsolute."resize_48x48/" . $uploadedPhoto, $userDirAbsolute_48x48.$uploadedPhoto );

                if( $rename_original && $rename_1024x1024 && $rename_512x512 && $rename_128x128 && $rename_48x48){
                    $movePhotoResponse = 1;
                    $user->getUserDetail()->setImageName($uploadedPhoto);
                    $em->persist($user);
                    $em->flush();

                } else {

                    $movePhotoResponse = -1;
                    $responseDatas["response"] = -1;
                }

                $responseDatas["movePhotosResponse"][$key] = $movePhotoResponse;
            }
        }
        
        if( $user->getUserDetail()->getImageName() != null ) {
            $imageName = $user->getUserDetail()->getImageName();
        } else {
            $imageName = null;
        }
        
        $responseDatas["html"] = $this->render('KsUserBundle:User:_userImage_medium.html.twig', array(
            'user_imageName'   => $imageName,
            'user_id' => $user->getId()
        ))->getContent();
                
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
    
     /**
     * @Route("/{creationOrEdition}/godFather", name = "ksProfile_godFather" )
     * @Template()
    */
    public function godFatherAction($creationOrEdition)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        
        if ( $creationOrEdition != "creation" && $creationOrEdition != "edition" ) {
            throw $this->createNotFoundException("Impossible d'accéder à cette section");
        }

        $user   = $this->container->get('security.context')->getToken()->getUser();
        
        if( $user->getUserDetail() != null ) {
            $imageName = $user->getUserDetail()->getImageName();
        } else {
            $imageName = null;
        }
        
        if( $request->getMethod() == 'POST' ) {
            $this->get('session')->setFlash('alert alert-success', "Le parrain a été mis à jour.");

            if ( $creationOrEdition == "creation" ) {
                return $this->redirect($this->generateUrl('ksProfile_sports', array(
                    "creationOrEdition" => $creationOrEdition
                )));
            }
        } 

        return array(
            'creationOrEdition' => $creationOrEdition,
            'user_imageName'    => $imageName           
        );
    }
    
    /**
     * @Route("/{creationOrEdition}/character", name = "ksProfile_character" )
     * @Template()
    */
    public function characterAction($creationOrEdition)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        
        if ( $creationOrEdition != "creation" && $creationOrEdition != "edition" ) {
            throw $this->createNotFoundException("Impossible d'accéder à cette section");
        }

        $user   = $this->container->get('security.context')->getToken()->getUser();
        $character = $user->getCharacter();
        
        if( !is_object($character)) {
            $character = new \Ks\CanvasDrawingBundle\Entity\Character();
            $user->setCharacter($character);
            $em->persist($character);
            $em->persist($user);
            $em->flush();
        }

        $post = false;
        
        if( $request->getMethod() == 'POST' ) {
            $parameters     = $request->request->all();
            //var_dump($parameters);
            
            
            $character->setSexeCode($parameters["sexeCode"]);
            $character->setSkinColor($parameters["skinColor"]);
            $character->setHairColor($parameters["hairColor"]);
            $character->setEyesColor($parameters["eyesColor"]);
            $character->setShirtColor($parameters["shirtColor"]);
            $character->setShortColor($parameters["shortColor"]);
            $character->setShoesPrimaryColor($parameters["shoesPrimaryColor"]);
            $character->setShoesSecondaryColor($parameters["shoesSecondaryColor"]);
            $em->persist($character);
            $em->flush();
            
            $this->get('session')->setFlash('alert alert-success', 'Ton personnage a été mis à jour.');

            if ( $creationOrEdition == "creation" ) {
                return $this->redirect($this->generateUrl('ksProfile_equipment', array(
                    "creationOrEdition" => $creationOrEdition
                )));
             }

            $post = true;
        } 

  
        return array(
            'creationOrEdition' => $creationOrEdition,
            "post"              => $post,
            
        );
    }
    
    /**
     * @Route("/{creationOrEdition}/equipment", name = "ksProfile_equipment", options={"expose"=true} )
     * @Template("KsEquipmentBundle:Equipment:equipments.html.twig"))
    */
    public function equipmentAction($creationOrEdition)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $equipmentTypeRep   = $em->getRepository('KsUserBundle:EquipmentType');
        $request            = $this->getRequest();
        
        if ( $creationOrEdition != "creation" && $creationOrEdition != "edition" ) {
            throw $this->createNotFoundException("Impossible d'accéder à cette section");
        }
        
        $equipmentsTypes = $equipmentTypeRep->findEquipmentsTypes();

        $user   = $this->container->get('security.context')->getToken()->getUser();

        //On récupère la liste des équipements avant la soumission du formulaire (pour effacer ceux qui ne servent plus)
        $equipments = $user->getEquipments();
        $previousEquipments = array();
        foreach( $equipments as $eq) { $previousEquipments[] = $eq; }
        
        $form   = $this->createForm(new \Ks\UserBundle\Form\ProfileEquipmentsType($user), $user);
        $errors = array();
        $post = false;
        
        if( $request->getMethod() == 'POST' ) {
            
            $formHandler = new \Ks\UserBundle\Form\ProfileEquipmentsHandler($form, $request, $em, $previousEquipments);
            $responsedatas = $formHandler->process();
            if( $responsedatas["response"] == 1 ){
               $this->get('session')->setFlash('alert alert-success', 'Ton matériel a été mis à jour !');
               
               
               if ( $creationOrEdition == "creation" ) {
                   return $this->redirect($this->generateUrl('ksUser_showChecklist'));
                }
                
           } else {
               $post = true;
               $errors = $responsedatas["errors"];
           }

            $post = true;
        } 

  
        return array(
            'form'              => $form->createView(),
            'creationOrEdition' => $creationOrEdition,
            'post'              => $post,
            'errors'            => $errors,
            'equipmentsTypes'   => $equipmentsTypes
        );
    }
    
    /**
     * @Route("/{creationOrEdition}/thematique", name = "ksProfile_thematique", options={"expose"=true} )
     * @Template("KsUserBundle:Profile:thematique.html.twig"))
    */
    public function thematiqueAction($creationOrEdition)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $equipmentTypeRep       = $em->getRepository('KsUserBundle:EquipmentType');
        $userChecklistActionRep = $em->getRepository('KsUserBundle:UserHasToDoChecklistAction');
        $request                = $this->getRequest();
        
        if ( $creationOrEdition != "creation" && $creationOrEdition != "edition" ) {
            throw $this->createNotFoundException("Impossible d'accéder à cette section");
        }
        
        $user   = $this->container->get('security.context')->getToken()->getUser();

        $checklistActions = $userChecklistActionRep->findActionsToDo($user->getId());
        
        return array(
            'creationOrEdition' => $creationOrEdition,
            'checklistActions'  => $checklistActions
        );
    }
    
    public function new_modalAction() {
        $form  = $this->createForm(new \Ks\UserBundle\Form\NewSportType());
        return $this->render('KsUserBundle:Profile:_new_modal.html.twig', array(
            "form" => $form->createView()
        ));
    }
}
