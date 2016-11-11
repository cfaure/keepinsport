<?php
namespace Ks\EquipmentBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/*Pour executer une commande du controller*/
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class EquipmentController extends Controller
{
    /**
     * @Route("/searchEquipments", name = "ksEquipment_search", options={"expose"=true} )
     */
    public function searchEquipmentsAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();     
        $request    = $this->getRequest();
        $equipmentRep    = $em->getRepository('KsUserBundle:Equipment');
        $userRep    = $em->getRepository('KsUserBundle:User');
        $sportRep   = $em->getRepository('KsActivityBundle:Sport');
        $user       = $this->container->get('security.context')->getToken()->getUser();
        
        $parameters = $request->request->all();
        
        $responseDatas = array(
           "code" => 1
        );
        
        $searchTerms = isset($parameters["terms"]) && $parameters["terms"] != "" ? explode(' ', $parameters["terms"]) : array();
        $offset = isset( $parameters["offset"] ) && !empty( $parameters["offset"] ) ? intval($parameters["offset"]) : 0;
        $limit = isset( $parameters["limit"] ) && !empty( $parameters["limit"] ) ? intval($parameters["limit"]) : 10; 
        $allEquipments  = isset( $parameters["allEquipments"] ) && $parameters["allEquipments"] == "true" ? true : false; 
        
        $params = array(
            'userId'          => $user->getId(),
            'searchTerms'     => $searchTerms,
            'allEquipments'   => $allEquipments,
            'searchOffset'    => $offset,
            'searchLimit'     => $limit
        );
        
        if( is_object( $user ) && ! $allEquipments) {
            $equipmentsIds = $equipmentRep->getMyEquipmentsIds( $user->getId() );
            if( count($equipmentsIds) >= 1 ) {
                $params["equipmentsIds"] = $equipmentsIds;
            }
        }
        
        $result = $equipmentRep->findEquipments($params, $this->get('translator'));
        
        $equipments = $result["equipments"];
        
        $responseDatas["equipments_number_not_loaded"] = $result["equipmentsNumberNotLoaded"];
        $responseDatas["equipments_number"] = count( $equipments );
       
        $responseDatas["html"] = $this->render('KsEquipmentBundle:Equipment:_equipments_grid.html.twig', 
                array("userId"        => $user->getId(), 
                      "equipments"    => $equipments, 
                      "allEquipments" => $allEquipments, 
                      "creationOrEdition" => true))
                ->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/bubbleInfos/{equipmentId}/{creationOrEdition}", name = "ksEquipment_bubbleInfos" )
     */
    public function bubbleInfosAction($equipmentId, $creationOrEdition)
    {
        $em           = $this->getDoctrine()->getEntityManager();     
        $equipmentRep = $em->getRepository('KsUserBundle:Equipment');
        $user         = $this->container->get('security.context')->getToken()->getUser();
        
        $params = array("equipmentId"  => $equipmentId,
                        "userId"       => $user->getId());
        
        $equipment = $equipmentRep->findEquipments($params, $this->get('translator'));
        
        $intCreationOrEdition = ($creationOrEdition == 'creation' ? 1 : 0);
        
        return $this->render('KsEquipmentBundle:Equipment:_bubbleInfos.html.twig', 
                array("userId"               => $user->getId(), 
                      "equipment"            => $equipment[0],
                      "intCreationOrEdition" => $intCreationOrEdition));
    }
    
    public function new_modalAction() {
        $form  = $this->createForm(new \Ks\EquipmentBundle\Form\NewEquipmentType());
        
        $sportChoiceForm = $this->createForm(new \Ks\ActivityBundle\Form\SportType("Multi"), null);
        
        return $this->render('KsEquipmentBundle:Equipment:_new_modal.html.twig', array(
            "form"            => $form->createView(),
            "sportChoiceForm" => $sportChoiceForm->createView()
        ));
    }
    
    /**
     * @Route("/createNewEquipment/", name = "ksEquipment_createNewEquipment", options={"expose"=true} )
     */
    public function createNewEquipmentAction()
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $user           = $this->get('security.context')->getToken()->getUser();
        $equipmentRep   = $em->getRepository('KsUserBundle:Equipment');
        $sportRep       = $em->getRepository('KsActivityBundle:Sport');
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $articleRep     = $em->getRepository('KsActivityBundle:Article');
        $articleTagRep  = $em->getRepository('KsActivityBundle:ArticleTag');
        
        $equipment           = new \Ks\UserBundle\Entity\Equipment();
        $equipmentType       = new \Ks\EquipmentBundle\Form\NewEquipmentType();
        
        $form = $this->createForm($equipmentType, $equipment);
        
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                //Création d'un article de type "matériel" correspondant au nouvel équipement
                $article = new \Ks\ActivityBundle\Entity\Article($user);
                $categoryTag = $articleTagRep->find(5);
                $article->setCategoryTag($categoryTag);
                $article->setBrand($equipment->getBrand());
                $article->setLabel($equipment->getName());
                $article->setUser($user);
                $article->setType("article");
                $article->setEquipmentType($equipment->getType());
                
                $em->persist($article);
                $em->flush();
                
                //On abonne l'utilisateur à l'activité
                $activityRep->subscribeOnActivity($article, $user);
                
                //Tableau qui contient tous les éléments de la modification
                $modification = array(
                    "title"         => base64_encode($article->getLabel() ),
                    "description"   => "",
                    "elements"      => array(),
                    "photos"        => array(),
                    "tags"          => array(5),
                    "trainingPlan"  => array()
                );

                //Tableau qui dira si les choses ont changés
                $thingsWereChanged = array(
                    "title"         => false,
                    "description"   => false,
                    "elements"      => false,
                    "photos"        => false,
                    "tags"          => false,
                    "trainingPlan"  => false
                );

                //On enregistre les modifications sur le contenu
                $articleRep->modificationOnArticle($article, $user, json_encode($modification/*, JSON_UNESCAPED_UNICODE*/), $thingsWereChanged);

                //Création de l'équipement correspondant à l'activité de type article "matériel"
                //var_dump($_POST);
                //var_dump($_POST['selectedSports']);
                $sportIds = explode(',', $_POST['selectedSports']);
                
                //var_dump($sportIds);
                
                foreach ($sportIds as $key => $sportId) {
                    $sport = $sportRep->find(intval($sportId));
                    $equipment->addSport($sport);
                }
                
                $equipment->setUser($user);
                $equipment->setAvatar(''); //l'avatar est chargé après via ksEquipment_changeAvatar
                $equipment->setActivity($article);
                
                $em->persist($equipment);
                $em->flush();
                
                $responseDatas = array(
                    'publishResponse' => 1,
                    'equipmentId' => $equipment->getId()
                );
                
            } else {
                $this->get('session')->setFlash('alert alert-error', 'Erreur lors de la sauvegarde du matériel, merci de nous contacter par mail avec le feedback ci-dessous !');
                //var_dump(\Form\FormValidator::getErrorMessages($form));
                
                $responseDatas = array(
                    'publishResponse' => -1
                );
            }
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/updateEquipment/{equipmentId}", requirements={"equipmentId" = "\d+"}, name = "ksEquipment_updateEquipment", options={"expose"=true} )
     */
    public function updateEquipmentAction($equipmentId)
    {
        $em               = $this->getDoctrine()->getEntityManager();
        $user             = $this->get('security.context')->getToken()->getUser();
        $equipmentRep     = $em->getRepository('KsUserBundle:Equipment');
        $equipmentTypeRep = $em->getRepository('KsUserBundle:EquipmentType');
        $sportRep         = $em->getRepository('KsActivityBundle:Sport');
        
        $equipment      = $equipmentRep->find($equipmentId);
        
        $equipmentType  = new \Ks\EquipmentBundle\Form\NewEquipmentType();
        
        $form = $this->createForm($equipmentType, $equipment);
        
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                
                $equipmentRep->deleteEquipmentHasSports($equipmentId);
                
                //Sauvegarde du champ custom "Sport" :
                $sportIds = explode(',', $_POST['selectedSports']);
                
                foreach ($sportIds as $key => $sportId) {
                    $sport = $sportRep->find(intval($sportId));
                    $equipment->addSport($sport);
                }
                
                //Sauvegarde du champ custom "Type"
                $typeId = $_POST['selectedType'];
                $type = $equipmentTypeRep->find(intval($typeId));
                $equipment->setType($type);
                
                $em->persist($equipment);
                $em->flush();
                
                $responseDatas = array(
                    'publishResponse' => 1,
                    'equipment' => $equipment
                );
                
            } else {
                $this->get('session')->setFlash('alert alert-error', 'Erreur lors de la sauvegarde du matériel, merci de nous contacter par mail avec le feedback ci-dessous !');
                //var_dump(\Form\FormValidator::getErrorMessages($form));
                
                $responseDatas = array(
                    'publishResponse' => -1,
                    'equipment' => $equipment
                );
            }
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**     
     * @Route("/getDetails/{equipmentId}", name="ksEquipment_getDetails", options={"expose"=true})
     * @Template()
     */
    public function getDetailsAction($equipmentId)
    {      
        $em                  = $this->getDoctrine()->getEntityManager();
        $equipmentRep        = $em->getRepository('KsUserBundle:Equipment');
        $equipmentTypeRep    = $em->getRepository('KsUserBundle:EquipmentType');
        
        $equipment = $equipmentRep->find($equipmentId);
        
        if ( ! is_object($equipment) ) {
            $responseDatas["responseUpdate"] = -1;
            $responseDatas["errorMessage"] = "Impossible d'éditer ce matériel !";
        } else {
            $equipmentType = $equipmentTypeRep->find($equipment->getType()->getId());
            
            $responseDatas["brand"]                   = $equipment->getBrand();
            $responseDatas["name"]                    = $equipment->getName();
            $responseDatas["weight"]                  = $equipment->getWeight();
            $responseDatas["type"][]                  = $equipment->getType()->getId();
            $responseDatas["primaryColor"]            = $equipment->getPrimaryColor();
            $responseDatas["secondaryColor"]          = $equipment->getSecondaryColor();
            $responseDatas["isByDefault"]             = $equipment->getIsByDefault();
            
            $responseDatas["isWeightEnabled"]         = $equipmentType->getIsWeightEnabled();
            $responseDatas["isPrimaryColorEnabled"]   = $equipmentType->getIsPrimaryColorEnabled();
            $responseDatas["isSecondaryColorEnabled"] = $equipmentType->getIsSecondaryColorEnabled();
            
            foreach ($equipment->getSports() as $key => $sport) {
                $responseDatas["sports"][] = $sport->getId();
            }
            //var_dump($responseDatas["sports"]);
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;  
    }
    
    /**
     * 
     * @Route("/deleteEquipment/{equipmentId}", requirements={"equipmentId" = "\d+"}, name = "ksEquipment_delete", options={"expose"=true} )
     * @param int $equipmentId 
     */
    public function deleteEquipmentAction($equipmentId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $equipmentRep        = $em->getRepository('KsUserBundle:Equipment');
        
        $responseDatas = array();
        
        $equipment = $equipmentRep->find($equipmentId);
        
        if ( ! is_object($equipment) ) {
            $responseDatas["responseDelete"] = -1;
            $responseDatas["errorMessage"] = "Impossible de supprimer ce matériel !";
        } else {
            $equipmentRep->deleteEquipment($equipment);
            $responseDatas["responseDelete"] = 1;
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * @Route("/changeAvatar", name = "ksEquipment_changeAvatar", options={"expose"=true} )
     */
    public function changeAvatarAction()
    {
        $request        = $this->get('request');
        $em             = $this->getDoctrine()->getEntityManager();
        $equipmentRep   = $em->getRepository('KsUserBundle:Equipment');
        $parameters     = $request->request->all();
        
        $equipmentId = $parameters['equipmentId'];
        
        $equipment = $equipmentRep->find($equipmentId);
        
        $responseDatas = array();
        $responseDatas["response"] = 1;
        
        $parameters = $request->request->all();
        
        $avatars = isset( $parameters["uploadedPhotos"] ) ? $parameters["uploadedPhotos"] : array() ;
        
        $uploadDirRelative = $this->container->get('templating.helper.assets')->getUrl('uploads');
        $uploadDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $uploadDirRelative . "/equipments/";
        $imgDirRelative = $this->container->get('templating.helper.assets')->getUrl('img');
        $equipmentsDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/equipments/";
        
        if (! is_dir( $equipmentsDirAbsolute ) ) mkdir( $equipmentsDirAbsolute );
        
        foreach( $avatars as $key => $avatar ) {
  
            $equipmentDirAbsolute = $equipmentsDirAbsolute.$equipment->getId()."/";

            //On crée le dossier qui contient les images des matériels s'il n'existe pas
            if (! is_dir( $equipmentDirAbsolute ) ) mkdir( $equipmentDirAbsolute );

            $equipmentDirAbsolute_original = $equipmentDirAbsolute . 'original/';
            if (! is_dir( $equipmentDirAbsolute_original ) ) mkdir($equipmentDirAbsolute_original);

            $equipmentDirAbsolute_1024x1024 = $equipmentDirAbsolute . 'resize_1024x1024/';
            if (! is_dir( $equipmentDirAbsolute_1024x1024 ) ) mkdir($equipmentDirAbsolute_1024x1024);
            
            $equipmentDirAbsolute_512x512 = $equipmentDirAbsolute . 'resize_512x512/';
            if (! is_dir( $equipmentDirAbsolute_512x512 ) ) mkdir($equipmentDirAbsolute_512x512);
            
            $equipmentDirAbsolute_128x128 = $equipmentDirAbsolute . 'resize_128x128/';
            if (! is_dir( $equipmentDirAbsolute_128x128 ) ) mkdir($equipmentDirAbsolute_128x128);
            
            $equipmentDirAbsolute_48x48 = $equipmentDirAbsolute . 'resize_48x48/';
            if (! is_dir( $equipmentDirAbsolute_48x48 ) ) mkdir($equipmentDirAbsolute_48x48);

            //On la déplace les photos originales et redimentionnés
            $rename_original = rename( $uploadDirAbsolute."original/"  . $avatar, $equipmentDirAbsolute_original.$avatar );
            $rename_1024x1024 = rename( $uploadDirAbsolute."resize_1024x1024/" . $avatar, $equipmentDirAbsolute_1024x1024.$avatar );
            $rename_512x512 = rename( $uploadDirAbsolute."resize_512x512/" . $avatar, $equipmentDirAbsolute_512x512.$avatar );
            $rename_128x128 = rename( $uploadDirAbsolute."resize_128x128/" . $avatar, $equipmentDirAbsolute_128x128.$avatar );
            $rename_48x48 = rename( $uploadDirAbsolute."resize_48x48/" . $avatar, $equipmentDirAbsolute_48x48.$avatar );
            
            if( $rename_original && $rename_1024x1024 && $rename_512x512 && $rename_128x128 && $rename_48x48){
                $movePhotoResponse = 1;
                $equipment->setAvatar($avatar);
                $em->persist($equipment);
                $em->flush();
            } else {
                $movePhotoResponse = -1;
                $responseDatas["response"] = -1;
            }
            $responseDatas["movePhotosResponse"][$key] = $movePhotoResponse;
        }
        
        if( $equipment->getAvatar() != null ) {
            $imageName = $equipment->getAvatar();
        } else {
            $imageName = null;
        }
        
        $responseDatas["html"] = $this->render('KsEquipmentBundle:Equipment:_equipmentImage.html.twig', array(
            'equipment_imageName'   => $imageName,
            'equipment_id' => $equipmentId
        ))->getContent();
                
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
    
    /**
     * 
     * @Route("/addOnEquipment/{equipmentId}", requirements={"equipmentId" = "\d+"}, name = "ksEquipment_addOnEquipment", options={"expose"=true} )
     * @param int $equipmentId
     */
    public function addOnEquipmentAction($equipmentId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $equipmentRep   = $em->getRepository('KsUserBundle:Equipment');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $params = array(
            'equipmentId'     => $equipmentId,
            'allEquipments'   => true,
            'userId'          => $user->getId()
        );
        
        $equipment = $equipmentRep->find($equipmentId);
        
        if (!is_object($equipment) ) {
            $impossibleEquipmentMsg = $this->get('translator')->trans('impossible-to-find-equipment-%equipmentId%', array('%equipmentId%' => $equipmentId));
            throw new AccessDeniedException($impossibleEquipmentMsg);
        }
        
        $responseDatas = array();
        
        //Création du nouvel équipement :
        $newEquipment = new \Ks\UserBundle\Entity\Equipment();
        
        $newEquipment->setUser($user);
        $newEquipment->setBrand($equipment->getBrand());
        $newEquipment->setName($equipment->getName());
        $newEquipment->setWeight($equipment->getWeight());
        $newEquipment->setType($equipment->getType());
        $newEquipment->setPrimaryColor($equipment->getPrimaryColor());
        $newEquipment->setSecondaryColor($equipment->getSecondaryColor());
        $newEquipment->setSports($equipment->getSports());
        $newEquipment->setActivity($equipment->getActivity());
        $newEquipment->setIsByDefault(true);
        
        $em->persist($newEquipment);
        $em->flush();
        
        //Copie des photos de l'équipement copié vers le nouveau :
        $request        = $this->get('request');
        
        $avatar = $equipment->getAvatar();
        
        if (isset($avatar) && $avatar != "") {
            $imgDirRelative = $this->container->get('templating.helper.assets')->getUrl('img');
            $equipmentsDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/equipments/";

            $equipmentDirAbsolute = $equipmentsDirAbsolute.$equipment->getId()."/";
            $newEquipmentDirAbsolute = $equipmentsDirAbsolute.$newEquipment->getId()."/";

            //On crée le dossier qui contient les images des matériels s'il n'existe pas
            if (! is_dir( $newEquipmentDirAbsolute ) ) mkdir( $newEquipmentDirAbsolute );

            $newEquipmentDirAbsolute_original = $newEquipmentDirAbsolute . 'original/';
            if (! is_dir( $newEquipmentDirAbsolute_original ) ) mkdir($newEquipmentDirAbsolute_original);

            $newEquipmentDirAbsolute_1024x1024 = $newEquipmentDirAbsolute . 'resize_1024x1024/';
            if (! is_dir( $newEquipmentDirAbsolute_1024x1024 ) ) mkdir($newEquipmentDirAbsolute_1024x1024);

            $newEquipmentDirAbsolute_512x512 = $newEquipmentDirAbsolute . 'resize_512x512/';
            if (! is_dir( $newEquipmentDirAbsolute_512x512 ) ) mkdir($newEquipmentDirAbsolute_512x512);

            $newEquipmentDirAbsolute_128x128 = $newEquipmentDirAbsolute . 'resize_128x128/';
            if (! is_dir( $newEquipmentDirAbsolute_128x128 ) ) mkdir($newEquipmentDirAbsolute_128x128);

            $newEquipmentDirAbsolute_48x48 = $newEquipmentDirAbsolute . 'resize_48x48/';
            if (! is_dir( $newEquipmentDirAbsolute_48x48 ) ) mkdir($newEquipmentDirAbsolute_48x48);

            //On copie les photos originales et redimentionnés 
            $rename_original = copy( $equipmentDirAbsolute."original/"  . $avatar, $newEquipmentDirAbsolute_original.$avatar );
            $rename_1024x1024 = copy( $equipmentDirAbsolute."resize_1024x1024/" . $avatar, $newEquipmentDirAbsolute_1024x1024.$avatar );
            $rename_512x512 = copy( $equipmentDirAbsolute."resize_512x512/" . $avatar, $newEquipmentDirAbsolute_512x512.$avatar );
            $rename_128x128 = copy( $equipmentDirAbsolute."resize_128x128/" . $avatar, $newEquipmentDirAbsolute_128x128.$avatar );
            $rename_48x48 = copy( $equipmentDirAbsolute."resize_48x48/" . $avatar, $newEquipmentDirAbsolute_48x48.$avatar );

            if( $rename_original && $rename_1024x1024 && $rename_512x512 && $rename_128x128 && $rename_48x48){
                $movePhotoResponse = 1;
                $newEquipment->setAvatar($avatar);
                $em->persist($newEquipment);
                $em->flush();
            }
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    /**
     * 
     * @Route("/wishOnEquipment/{equipmentId}", requirements={"equipmentId" = "\d+"}, name = "ksEquipment_wishOnEquipment", options={"expose"=true} )
     * @param int $equipmentId
     */
    public function wishOnEquipmentAction($equipmentId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $equipmentRep   = $em->getRepository('KsUserBundle:Equipment');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $equipment = $equipmentRep->find($equipmentId);
        
        if (!is_object($equipment) ) {
            $impossibleEquipmentMsg = $this->get('translator')->trans('impossible-to-find-equipment-%equipmentId%', array('%equipmentId%' => $equipmentId));
            throw new AccessDeniedException($impossibleEquipmentMsg);
        }
        
        $numWishesByUser = (int)$equipmentRep->getNumWishesByUser($user);
        
        $responseDatas = array();
        
        //Si l'utilisateur n'a pas déjà voté sur l'équipement
        $entityEquipment = $equipmentRep->find($equipmentId);
        if ( ! $equipmentRep->haveAlreadyWished($equipment, $user) ) {
            $equipmentRep->wishOnEquipment($equipment, $user);
            $responseDatas["responseWish"]  = 1;
        } else {
            $responseDatas["responseWish"] = -1;
            $responseDatas["errorMessage"] = 'error';
        }
        
        $equipment->numWishes     = (int)$equipmentRep->getNumWishesOnEquipment($equipment);
        $equipment->userHasWished = $equipmentRep->haveAlreadyWished($equipment, $user);
        $equipment->user_id       = $equipment->getUser()->getId();
        
        $responseDatas["addLink"] = $this->render('KsEquipmentBundle:Equipment:_addLink.html.twig', array(
            'equipment'     => $equipment,
            'userId'        => $user->getId(),
            'allEquipments' => true
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    /**
     * 
     * @Route("/removeWishOnEquipment/{equipmentId}", requirements={"equipmentId" = "\d+"}, name = "ksEquipment_removeWishOnEquipment", options={"expose"=true} )
     * @param int $equipmentId
     */
    public function removeWishOnEquipmentAction($equipmentId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $equipmentRep    = $em->getRepository('KsUserBundle:Equipment');
        $wishesRep       = $em->getRepository('KsUserBundle:EquipmentHasWishes');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $responseDatas = array();
        
        $equipment = $equipmentRep->find($equipmentId);
        
        if (!is_object($equipment) ) {
            $impossibleEquipmentMsg = $this->get('translator')->trans('impossible-to-find-equipment-%equipmentId%', array('%equipmentId%' => $equipmentId));
            throw new AccessDeniedException($impossibleEquipmentMsg);
        }
        
        if ( $equipmentRep->haveAlreadyWished($equipment, $user) ) {
            $equipmentHasWishes = $wishesRep->find(array("equipment" => $equipmentId, "wisher" => $user->getId()));
        
            if (!is_object($equipmentHasWishes) ) {
                $impossibleWishMsg = $this->get('translator')->trans('impossible-to-find-wish-%equipmentId%', array('%equipmentId%' => $equipmentId));
                throw new AccessDeniedException($impossibleWishMsg);
            }

            $equipmentRep->removeWishOnEquipment($equipmentHasWishes);
            $equipment->numWishes     = (int)$equipmentRep->getNumWishesOnEquipment($equipment);
            $equipment->userHasWished = $equipmentRep->haveAlreadyWished($equipment, $user);
            $equipment->user_id       = $equipment->getUser()->getId();
            $responseDatas["responseWish"]  = 1;
        } else {
            $responseDatas["responseWish"]  = -1;
            $youAlreadyRetireMsg = $this->get('translator')->trans('you-already-retire-your-equipment');
            $responseDatas["errorMessage"]  = $youAlreadyRetireMsg;
        }
        
        $responseDatas["addLink"] = $this->render('KsEquipmentBundle:Equipment:_addLink.html.twig', array(
            'equipment'     => $equipment,
            'userId'        => $user->getId(),
            'allEquipments' => true
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * @Route("/{id}/equipmentUsers", name = "ksEquipment_equipmentUsers", options={"expose"=true} )
     */
    public function equipmentUsersAction($id)
    {
        $em           = $this->getDoctrine()->getEntityManager();
        $equipmentRep = $em->getRepository('KsUserBundle:Equipment');
        $user         = $this->get('security.context')->getToken()->getUser();
        
        $params = array();
        $params["activityId"] = $id;
        $params["extended"] = true;
        $params["userId"] = $user->getId();
        
        $result = $equipmentRep->findEquipments($params, $this->get('translator'));
        $users = array();
        
        if (count($result) >0 ) {
            $equipment = $result[0]["equipment"];
            
            $doublons = array(); // contiendra les ids à éviter
            foreach($result[0]["users"] as $user)
            {
              if(!in_array($user['user_id'], $doublons)) {
                 $users[] = $user;
                 $doublons[] = $user['user_id'];   
              }
            }
        }
        else {
            $equipment["activity_id"] = $id;
        }
        
        return $this->render('KsEquipmentBundle:Equipment:_equipmentUsers.html.twig', array(
                'equipment'  => $equipment,
                'users'      => $users,
                'affichDesc' => false
        ));
    }
    
    /**
     * 
     * @Route("/userUsesEquipment/{activityId}", requirements={"activityId" = "\d+"}, name = "ksEquipment_userUsesEquipment", options={"expose"=true} )
     * @param int $activityId 
     */
    public function userUsesEquipmentAction($activityId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $equipmentRep   = $em->getRepository('KsUserBundle:Equipment');
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        $articleRep     = $em->getRepository('KsActivityBundle:Article');
        $articleTagRep  = $em->getRepository('KsActivityBundle:ArticleTag');
        $modifiesArtRep = $em->getRepository('KsActivityBundle:UserModifiesArticle');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        //On doit créer un equipement de toute pièce
        $article = $articleRep->find($activityId);

        $equipment           = new \Ks\UserBundle\Entity\Equipment();
        $equipmentType       = new \Ks\EquipmentBundle\Form\NewEquipmentType();
        $equipment->setUser($user);
        $equipment->setAvatar('');
        $equipment->setActivity($article);
        $equipment->setBrand($article->getBrand());
        $equipment->setName($article->getLabel());
        if(!is_null($article->getEquipmentType())) $equipment->setType($article->getEquipmentType());
        $equipment->setIsByDefault(true);
        
        $em->persist($equipment);
        $em->flush();
        
        //Copie de la photo principale de l'activité si elle existe
        $modif = $modifiesArtRep->getLastModification($article);
        $articleContent = json_decode($modif->getContent(), true);
        $articlePhotos = isset( $articleContent["photos"] ) ? $articleContent["photos"] : array();
        if (isset($articlePhotos[0]["path"])) $avatar = $articlePhotos[0]["path"];
        else $avatar = null;
        
        $responseDatas = array();
        $responseDatas["photos"] = $articlePhotos;
        $responseDatas["avatar"] = $avatar;
        
        //Copie de la photo principale de l'activité si elle existe
        if (isset($avatar) && $avatar != "") {
            $imgDirRelative = $this->container->get('templating.helper.assets')->getUrl('img');
            
            $newEquipmentDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/equipments/" .$equipment->getId()."/";
            $activityDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/wiki/" .$article->getId()."/";

            //On crée le dossier qui contient les images des matériels s'il n'existe pas
            if (! is_dir( $newEquipmentDirAbsolute ) ) mkdir( $newEquipmentDirAbsolute );

            $newEquipmentDirAbsolute_original = $newEquipmentDirAbsolute . 'original/';
            if (! is_dir( $newEquipmentDirAbsolute_original ) ) mkdir($newEquipmentDirAbsolute_original);

            $newEquipmentDirAbsolute_1024x1024 = $newEquipmentDirAbsolute . 'resize_1024x1024/';
            if (! is_dir( $newEquipmentDirAbsolute_1024x1024 ) ) mkdir($newEquipmentDirAbsolute_1024x1024);

            $newEquipmentDirAbsolute_512x512 = $newEquipmentDirAbsolute . 'resize_512x512/';
            if (! is_dir( $newEquipmentDirAbsolute_512x512 ) ) mkdir($newEquipmentDirAbsolute_512x512);

            $newEquipmentDirAbsolute_128x128 = $newEquipmentDirAbsolute . 'resize_128x128/';
            if (! is_dir( $newEquipmentDirAbsolute_128x128 ) ) mkdir($newEquipmentDirAbsolute_128x128);

            $newEquipmentDirAbsolute_48x48 = $newEquipmentDirAbsolute . 'resize_48x48/';
            if (! is_dir( $newEquipmentDirAbsolute_48x48 ) ) mkdir($newEquipmentDirAbsolute_48x48);

            //On copie les photos originales et redimentionnés
            //FIXME : pour le wiki apparemment il n'y a pas tous les formats uniquement original et thumbnail !
            $rename_original = copy( $activityDirAbsolute."original/"  . $avatar, $newEquipmentDirAbsolute_original.$avatar );
            $rename_1024x1024 = copy( $activityDirAbsolute."original/" . $avatar, $newEquipmentDirAbsolute_1024x1024.$avatar );
            $rename_512x512 = copy( $activityDirAbsolute."original/" . $avatar, $newEquipmentDirAbsolute_512x512.$avatar );
            $rename_128x128 = copy( $activityDirAbsolute."thumbnail/" . $avatar, $newEquipmentDirAbsolute_128x128.$avatar );
            $rename_48x48 = copy( $activityDirAbsolute."thumbnail/" . $avatar, $newEquipmentDirAbsolute_48x48.$avatar );

            if( $rename_original && $rename_1024x1024 && $rename_512x512 && $rename_128x128 && $rename_48x48){
                $equipment->setAvatar($avatar);
                $em->persist($equipment);
                $em->flush();
            }
        }
        
        $em->persist($equipment);
        $em->flush();
        
        $result = $equipmentRep->findEquipments(array(
            "activityId"  => $activityId,
            "extended"    => true
        ), $this->get('translator'));
        
        $responseDatas["useResponse"] = 1;
        
        $users = array(); // le nouveau tableau dédoublonné
        $doublons = array(); // contiendra les ids à éviter
        foreach($result[0]["users"] as $user)
        {
          if(!in_array($user['user_id'], $doublons)) {
             $users[] = $user;
             $doublons[] = $user['user_id'];   
          }
        }
        
        $responseDatas["userUsesEquipmentLink"] = $this->render('KsEquipmentBundle:Equipment:_userUsesEquipmentLink.html.twig', array(
            'equipment' => $result[0]["equipment"],
            'users'     => $users
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
}