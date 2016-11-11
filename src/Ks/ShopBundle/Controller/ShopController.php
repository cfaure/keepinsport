<?php
namespace Ks\ShopBundle\Controller;

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

class ShopController extends Controller
{
    /**
     * @Route("/", name = "ksShop_list", options={"expose"=true} )
     * @Template()
     */
    public function shopsAction()
    {
        $em                   = $this->getDoctrine()->getEntityManager();
        $securityContext      = $this->container->get('security.context');
        $userRep              = $em->getRepository('KsUserBundle:User');
        $shopRep              = $em->getRepository('KsShopBundle:Shop');
        $leagueCatRep         = $em->getRepository('KsLeagueBundle:LeagueCategory'); 
        
        $session = $this->get('session');
        $session->set('pageType', 'shops');
        $session->set('page', 'shopList');
        
        $contestUsers = array();
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $this->get('security.context')->getToken()->getUser();
        }
        else {
            //Visitor
            $isExpertMode = false;
            $user = $userRep->find(1);
            $this->addUserAction('shops', 'visite', 'OK', null);
        }
        
        $userId = $user->getId();
        $shops = $shopRep->getShopWithUserHasVotedAndNumVotes($user);
        
        $leaguesUsers = array();
        
        //Récupération des leagues
        $leaguesCategories = $leagueCatRep->findLeaguesUpdatables();
        
        foreach( $leaguesCategories as $leagueCategory ) {
            //Récupération plus d'informations sur les utilisateurs (les points nous interessent)
            $users      = $userRep->findUsers(array(
                "withPoints"            => true,
                "leagueCategoryId"      => $leagueCategory["id"],
                "test"                  => false,
                "delay"                 => -1,
                "shopId"                => -1
                
            ), $this->get('translator'));

            //Tri par points décroissants
            usort( $users, array( "Ks\UserBundle\Entity\UserRepository", "orderUsersByPointsDesc" ) );
            
            //var_dump($users);
            
            $leaguesUsers[$leagueCategory["label"]] = $users;
        }
        
        //var_dump($leaguesUsers);
        
        return array(
            "userId"                => $userId,
            "shops"                 => $shops,
            'user'                  => $user,
            'leaguesUsers'          => $leaguesUsers
        );
    }
    
    /**
     * @Route("/searchShops", name = "ksShop_search", options={"expose"=true} )
     */
    public function searchShopsAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();     
        $request    = $this->getRequest();
        $shopRep    = $em->getRepository('KsShopBundle:Shop');
        $userRep    = $em->getRepository('KsUserBundle:User');
        $sportRep   = $em->getRepository('KsActivityBundle:Sport');
        $user       = $this->container->get('security.context')->getToken()->getUser();
        
        $parameters = $request->request->all();
        
        $responseDatas = array(
           "code" => 1
        );
        
        $searchTerms = isset($parameters["terms"]) && !empty( $parameters["offset"]) ? explode(' ', $parameters["terms"]) : array();
        $offset = isset( $parameters["offset"] ) && !empty( $parameters["offset"] ) ? intval($parameters["offset"]) : 0;
        $limit = isset( $parameters["limit"] ) && !empty( $parameters["limit"] ) ? intval($parameters["limit"]) : 10; 
        $myShops  = isset( $parameters["myShops"] ) && $parameters["myShops"] == "true" ? true : false; 
        $mySports = isset( $parameters["mySports"] ) && $parameters["mySports"] == "true" ? true : false;
        $webShops = isset( $parameters["webShops"] ) && $parameters["webShops"] == "true" ? true : false;
        $shopsWithConditions = isset( $parameters["shopsWithConditions"] ) && $parameters["shopsWithConditions"] == "true" ? true : false;
        
        $params = array(
            'userId'                    => $user->getId(),
            'searchTerms'               => $searchTerms,
            'myShops'                   => $myShops,
            'mySports'                  => $mySports,
            'webShops'                  => $webShops,
            'shopsWithConditions'       => $shopsWithConditions,
            'searchOffset'              => $offset,
            'searchLimit'               => $limit
        );
        
        if( is_object( $user ) && $myShops) {
            $shopsIds = $shopRep->getMyShopsIds( $user->getId() );
            if( count($shopsIds) >= 1 ) {
                $params["shopsIds"] = $shopsIds;
            }
        }
        
        if( $mySports) {
            $params["mySports"] = $mySports;
        }
        
        if( $webShops) {
            $params["webShops"] = $webShops;
        }
        
        if( $shopsWithConditions) {
            $params["shopsWithConditions"] = $shopsWithConditions;
        }
        
        $result = $shopRep->findShops($params, $this->get('translator'));
        
        $shops = $result["shops"];
        //var_dump($parameters["search"]);
        
        $responseDatas["shops_number_not_loaded"] = $result["shopsNumberNotLoaded"];
        $responseDatas["shops_number"] = count( $shops );
       
        $responseDatas["html"] = $this->render('KsShopBundle:Shop:_shops_grid.html.twig', array("userId" => 'userId', "shops" => $shops))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * @Route("/bubbleInfos/{shopId}", name = "ksShop_bubbleInfos" )
     */
    public function bubbleInfosAction($shopId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();     
        $securityContext    = $this->container->get('security.context');
        $shopRep            = $em->getRepository('KsShopBundle:Shop');
        $userRep            = $em->getRepository('KsUserBundle:User');
        
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user       = $this->container->get('security.context')->getToken()->getUser();
        }
        else {
            //Visitor
            $user = $userRep->find(1);
        }
        
        $shop = $shopRep->getShopWithUserHasVotedAndNumVotes($user, $shopId);
        
        return $this->render('KsShopBundle:Shop:_bubbleInfos.html.twig', array("userId" => $user->getId(), "shop" => $shop[0]));
    }
    
    /**
     * @Route("/", name = "ksCreate_shop", options={"expose"=true} )
     * @Template()
     */
    public function createShopAction()
    {
        $em                   = $this->getDoctrine()->getEntityManager();
        $user                 = $this->get('security.context')->getToken()->getUser();
        $shopRep              = $em->getRepository('KsShopBundle:Shop');
        
        $session = $this->get('session');
        $session->set('pageType', 'shops');
        
        return array();
    }
    
    /**
     * 
     * @Route("/voteOnShop/{shopId}", requirements={"shopId" = "\d+"}, name = "ksShop_voteOnShop", options={"expose"=true} )
     * @param int $shopId
     */
    public function voteOnShopAction($shopId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $shopRep        = $em->getRepository('KsShopBundle:Shop');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        //Services
        //$notificationService   = $this->get('ks_notification.notificationService');
        
        $shop = $shopRep->find($shopId);
        
        if (!is_object($shop) ) {
            $impossibleShopMsg = $this->get('translator')->trans('impossible-to-find-shop-%shopId%', array('%shopId%' => $shopId));
            throw new AccessDeniedException($impossibleShopMsg);
        }
        
        $numVotesByUser = (int)$shopRep->getNumVotesByUser($user);
        
        $responseDatas = array();
        
        //Si l'utilisateur n'a pas déjà voté sur l'activité et pas atteint son maximum de votes
        if ( ! $shopRep->haveAlreadyVoted($shop, $user) && $numVotesByUser <3) {
            $shopRep->voteOnShop($shop, $user);

            //Création d'une notification
//            $notificationType_name = "vote";
//            $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName($notificationType_name);
//
//            if (!$notificationType) {
//                $impossibleNotificationTypeMsg = $this->get('translator')->trans('impossible-to-find-notification-%$notificationTypeName%', array('%$notificationTypeName%' => $notificationType_name));
//                throw $this->createNotFoundException($impossibleNotificationTypeMsg);
//            }
//            
//            if ($shop->getUser() != $user) {
//                $notificationService->sendNotification($shop, $user, $shop->getUser(), $notificationType_name);  
//            }
//            
//            //Une notification de commentaire pour chaque abonné
//            foreach($shop->getSubscribers() as $shopHasSubscribers) {
//                $subscriber = $shopHasSubscribers->getSubscriber();
//
//                //Si l'abonné n'est pas lui même et qu'il n'a pas posté l'activité
//                if ($subscriber != $user && $shop->getUser() != $subscriber) {
//                    $notificationService->sendNotification($shop, $user, $subscriber, $notificationType_name);  
//                }
//            } 
            
//            //on coche l'action correspondante dans la checklist
//            $em->getRepository('KsUserBundle:ChecklistAction')->checkCommentLikeShareShop($user->getId());
            
            $responseDatas["responseVote"]  = 1;
        } else {
            $responseDatas["responseVote"] = -1;
            $voteShopMsg = $this->get('translator')->trans('Tu ne peux pas voter plus !');
            $responseDatas["errorMessage"] = $voteShopMsg;
        }
        
        $shop->numVotes     = (int)$shopRep->getNumVotesOnShop($shop);
        $shop->userHasVoted = $shopRep->haveAlreadyVoted($shop, $user);
        
        $responseDatas["voteLink"] = $this->render('KsShopBundle:Shop:_voteLink.html.twig', array(
            'shop'          => $shop
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    /**
     * 
     * @Route("/removeVoteOnShop/{shopId}", requirements={"shopId" = "\d+"}, name = "ksShop_removeVoteOnShop", options={"expose"=true} )
     * @param int $shopId
     */
    public function removeVoteOnShopAction($shopId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $shopRep        = $em->getRepository('KsShopBundle:Shop');
        $votesRep       = $em->getRepository('KsShopBundle:ShopHasVotes');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $responseDatas = array();
        
        $shop = $shopRep->find($shopId);
        
        if (!is_object($shop) ) {
            $impossibleShopMsg = $this->get('translator')->trans('impossible-to-find-shop-%shopId%', array('%shopId%' => $shopId));
            throw new AccessDeniedException($impossibleShopMsg);
        }
        
        if ( $shopRep->haveAlreadyVoted($shop, $user) ) {
            $shopHasVotes = $votesRep->find(array("shop" => $shopId, "voter" => $user->getId()));
        
            if (!is_object($shopHasVotes) ) {
                $impossibleVoteMsg = $this->get('translator')->trans('impossible-to-find-vote-%shopId%', array('%shopId%' => $shopId));
                throw new AccessDeniedException($impossibleVoteMsg);
            }

            $shopRep->removeVoteOnShop($shopHasVotes);
            $shop->numVotes            = (int)$shopRep->getNumVotesOnShop($shop);
            $shop->userHasVoted        = $shopRep->haveAlreadyVoted($shop, $user);
            $responseDatas["responseVote"]  = 1;
        } else {
            $responseDatas["responseVote"]  = -1;
            $youAlreadyRetireMsg = $this->get('translator')->trans('you-already-retire-your-shop');
            $responseDatas["errorMessage"]  = $youAlreadyRetireMsg;
        }
        
        $responseDatas["voteLink"] = $this->render('KsShopBundle:Shop:_voteLink.html.twig', array(
            'shop' => $shop
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    public function new_modalAction() {
        $form  = $this->createForm(new \Ks\ShopBundle\Form\NewShopType());
        
        $sportChoiceForm = $this->createForm(new \Ks\ActivityBundle\Form\SportType("MultiNotAll"), null);
        
        return $this->render('KsShopBundle:Shop:_new_modal.html.twig', array(
            "form" => $form->createView(),
            "sportChoiceForm" => $sportChoiceForm->createView()
        ));
    }
    
    /**
     * @Route("/createNewShop/", name = "ksShop_createNewShop", options={"expose"=true} )
     */
    public function createNewShopAction()
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $user           = $this->get('security.context')->getToken()->getUser();
        $shopRep        = $em->getRepository('KsShopBundle:Shop');
        $sportRep       = $em->getRepository('KsActivityBundle:Sport');
        
        $shop           = new \Ks\ShopBundle\Entity\Shop();
        $shopType       = new \Ks\ShopBundle\Form\NewShopType();
        
        $form = $this->createForm($shopType, $shop);
        
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                //var_dump($_POST);
                //var_dump($_POST['selectedSports']);
                $sportIds = explode(',', $_POST['selectedSports']);
                
                //var_dump($sportIds);
                
                foreach ($sportIds as $key => $sportId) {
                    $sport = $sportRep->find(intval($sportId));
                    $shop->setSport($sport);
                }
                
                $shop->setUser($user);
                //$shop->setName($name);
                //$shop->setAddress($address);
                $shop->setCountryArea("FR");
                $shop->setCountryCode("FR");
                $shop->setLongitude('');
                $shop->setLatitude('');
                //$shop->setTown('');
                //$shop->setTelNumber('');
                $shop->setMobileNumber('');
                //$shop->setEmail('');
                $shop->setUrlSiteWeb('');
                $shop->setAvatar('');
                $shop->setStatus(0);

                $em->persist($shop);
                $em->flush();
                
                $em->getRepository('KsUserBundle:ChecklistAction')->checkCreateShop($user->getId());
                
                $responseDatas = array(
                    'publishResponse' => 1,
                    'shopId' => $shop->getId()
                );
                
            } else {
                $this->get('session')->setFlash('alert alert-error', 'Erreur lors de la sauvegarde du magasin, merci de nous contacter par mail avec le feedback ci-dessous !');
                //var_dump(\Form\FormValidator::getErrorMessages($form));
                
                $responseDatas = array(
                    'publishResponse' => -1,
                    'shop' => $shop
                );
            }
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * @Route("/updateShop/{shopId}", requirements={"shopId" = "\d+"}, name = "ksShop_updateShop", options={"expose"=true} )
     */
    public function updateShopAction($shopId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $user           = $this->get('security.context')->getToken()->getUser();
        $shopRep        = $em->getRepository('KsShopBundle:Shop');
        $sportRep       = $em->getRepository('KsActivityBundle:Sport');
        
        $shop           = $shopRep->find($shopId);
        
        $shopType       = new \Ks\ShopBundle\Form\NewShopType();
        
        $form = $this->createForm($shopType, $shop);
        
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                
                $shopRep->deleteShopHasSports($shopId);
                
                $sportIds = explode(',', $_POST['selectedSports']);
                
                foreach ($sportIds as $key => $sportId) {
                    $sport = $sportRep->find(intval($sportId));
                    $shop->setSport($sport);
                }
                
                $em->persist($shop);
                $em->flush();
                
                $responseDatas = array(
                    'publishResponse' => 1,
                    'shop' => $shop
                );
                
            } else {
                $this->get('session')->setFlash('alert alert-error', 'Erreur lors de la sauvegarde du magasin, merci de nous contacter par mail avec le feedback ci-dessous !');
                //var_dump(\Form\FormValidator::getErrorMessages($form));
                
                $responseDatas = array(
                    'publishResponse' => -1,
                    'shop' => $shop
                );
            }
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**     
     * @Route("/getDetails/{shopId}", name="ksShop_getDetails", options={"expose"=true})
     * @Template()
     */
    public function getDetailsAction($shopId)
    {      
        $em                  = $this->getDoctrine()->getEntityManager();
        $shopRep             = $em->getRepository('KsShopBundle:Shop');
        $user                = $this->container->get('security.context')->getToken()->getUser();
        
        $shop = $shopRep->find($shopId);
        
        if ( ! is_object($shop) ) {
            $responseDatas["responseUpdate"] = -1;
            $responseDatas["errorMessage"] = "Impossible d'éditer ce magasin !";
        } else {
            $responseDatas["name"]       = $shop->getName();
            $responseDatas["address"]    = $shop->getAddress();
            $responseDatas["town"]       = $shop->getTown();
            $responseDatas["email"]      = $shop->getEmail();
            $responseDatas["telNumber"]  = $shop->getTelNumber();
            $responseDatas["conditions"] = $shop->getConditions();
            $responseDatas["userId"]     = $user->getId();
            if ($shop->getWebShop() == true) $responseDatas["webShop"] = 1; else $responseDatas["webShop"] = 0;
            foreach ($shop->getSports() as $key => $sport) {
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
     * @Route("/deleteShop/{shopId}", requirements={"shopId" = "\d+"}, name = "ksShop_delete", options={"expose"=true} )
     * @param int $shopId 
     */
    public function deleteShopAction($shopId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $shopRep        = $em->getRepository('KsShopBundle:Shop');
        
        $responseDatas = array();
        
        $shop = $shopRep->find($shopId);
        
        if ( ! is_object($shop) ) {
            $responseDatas["responseDelete"] = -1;
            $responseDatas["errorMessage"] = "Impossible de supprimer ce magasin !";
        } else {
            $shopRep->deleteShop($shop);
            $responseDatas["responseDelete"] = 1;
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * @Route("/changeAvatar", name = "ksShop_changeAvatar", options={"expose"=true} )
     */
    public function changeAvatarAction()
    {
        $request        = $this->get('request');
        $em             = $this->getDoctrine()->getEntityManager();
        $shopRep        = $em->getRepository('KsShopBundle:Shop');
        $parameters     = $request->request->all();
        
        $shopId = $parameters['shopId'];
        
        $shop = $shopRep->find($shopId);
        
        $responseDatas = array();
        $responseDatas["response"] = 1;
        
        $parameters = $request->request->all();
        
        $uploadedPhotos = isset( $parameters["uploadedPhotos"] ) ? $parameters["uploadedPhotos"] : array() ;
                
        $uploadDirRelative = $this->container->get('templating.helper.assets')->getUrl('uploads');
        $uploadDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $uploadDirRelative . "/shops/";
        $imgDirRelative = $this->container->get('templating.helper.assets')->getUrl('img');
        $shopsDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $imgDirRelative . "/shops/";
        
        if (! is_dir( $shopsDirAbsolute ) ) mkdir( $shopsDirAbsolute );
        
        foreach( $uploadedPhotos as $key => $uploadedPhoto ) {
  
            $shopDirAbsolute = $shopsDirAbsolute.$shop->getId()."/";

            //On crée le dossier qui contient les images des magasins s'il n'existe pas
            if (! is_dir( $shopDirAbsolute ) ) mkdir( $shopDirAbsolute );

            $shopDirAbsolute_original = $shopDirAbsolute . 'original/';
            if (! is_dir( $shopDirAbsolute_original ) ) mkdir($shopDirAbsolute_original);

            $shopDirAbsolute_1024x1024 = $shopDirAbsolute . 'resize_1024x1024/';
            if (! is_dir( $shopDirAbsolute_1024x1024 ) ) mkdir($shopDirAbsolute_1024x1024);
            
            $shopDirAbsolute_512x512 = $shopDirAbsolute . 'resize_512x512/';
            if (! is_dir( $shopDirAbsolute_512x512 ) ) mkdir($shopDirAbsolute_512x512);
            
            $shopDirAbsolute_128x128 = $shopDirAbsolute . 'resize_128x128/';
            if (! is_dir( $shopDirAbsolute_128x128 ) ) mkdir($shopDirAbsolute_128x128);
            
            $shopDirAbsolute_48x48 = $shopDirAbsolute . 'resize_48x48/';
            if (! is_dir( $shopDirAbsolute_48x48 ) ) mkdir($shopDirAbsolute_48x48);

            //On la déplace les photos originales et redimentionnés
            $rename_original = rename( $uploadDirAbsolute."original/"  . $uploadedPhoto, $shopDirAbsolute_original.$uploadedPhoto );
            $rename_1024x1024 = rename( $uploadDirAbsolute."resize_1024x1024/" . $uploadedPhoto, $shopDirAbsolute_1024x1024.$uploadedPhoto );
            $rename_512x512 = rename( $uploadDirAbsolute."resize_512x512/" . $uploadedPhoto, $shopDirAbsolute_512x512.$uploadedPhoto );
            $rename_128x128 = rename( $uploadDirAbsolute."resize_128x128/" . $uploadedPhoto, $shopDirAbsolute_128x128.$uploadedPhoto );
            $rename_48x48 = rename( $uploadDirAbsolute."resize_48x48/" . $uploadedPhoto, $shopDirAbsolute_48x48.$uploadedPhoto );
            
            if( $rename_original && $rename_1024x1024 && $rename_512x512 && $rename_128x128 && $rename_48x48){
                $movePhotoResponse = 1;
                $shop->setAvatar($uploadedPhoto);
                $em->persist($shop);
                $em->flush();
            } else {
                $movePhotoResponse = -1;
                $responseDatas["response"] = -1;
            }
            $responseDatas["movePhotosResponse"][$key] = $movePhotoResponse;
        }
        
        if( $shop->getAvatar() != null ) {
            $imageName = $shop->getAvatar();
        } else {
            $imageName = null;
        }
        
        $responseDatas["html"] = $this->render('KsShopBundle:Shop:_ShopImage.html.twig', array(
            'shop_imageName'   => $imageName,
            'shop_id' => $shopId
        ))->getContent();
                
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response;    
    }
}