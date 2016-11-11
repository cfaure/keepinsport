<?php

namespace Ks\ClubBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Ks\ClubBundle\Entity\Club;
use Ks\ClubBundle\Form\ClubType;

use Ks\ClubBundle\Form\ClubHandler;
use Ks\ClubBundle\Entity\ClubHasUsers;

use Ks\ClubBundle\Entity\Team;

/**
 * Club controller.
 *
 * 
 */
class ClubController extends Controller
{
    /**
     * @Route("/clubsBlocList", name = "ksClub_clubsBlockList" )
     */
    public function clubsBlocListAction() {
        $em                 = $this->getDoctrine()->getEntityManager();
        $clubRep    = $em->getRepository('KsClubBundle:Club');
        $user       = $this->get('security.context')->getToken()->getUser();
        
        //Récupération des clubs que je gère 
        $myClubs   = $clubRep->findMyClubs( $user );
    
        return $this->render('KsClubBundle:Club:_clubsBlockList.html.twig', array(
            'clubs'            => $myClubs,
        ));
    }
    
    /**
     * Lists all Club entities.
     *
     * @Route("/myClubsManaged", name="ksClub_myClubsManaged")
     * @Template()
     */
    public function myClubsManagedAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $clubRep    = $em->getRepository('KsClubBundle:Club');
        $user       = $this->container->get('security.context')->getToken()->getUser();

        $myClubsManaged = $clubRep->findMyClubsManaged( $user );
  
        return array(
            "myClubsManaged"     => $myClubsManaged
        );
    }
    
    /**
     * Lists my Clubs.
     *
     * @Route("/myClubs", name="ksClub_myClubs")
     */
    public function myClubsAction()
    {
        
        $em         = $this->getDoctrine()->getEntityManager();
        $clubRep    = $em->getRepository('KsClubBundle:Club');
        $user       = $this->get('security.context')->getToken()->getUser();
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'clubs');
        $session->set('clubIdSelected', null);
        
        //Récupération des clubs que je gère 
        $myClubs   = $clubRep->findMyClubs( $user );
     
        return $this->render('KsClubBundle:Club:clubsList.html.twig', 
            array(
                "clubs"     => $myClubs
            )
        );
    }

    /**
     * Finds and displays a Club entity.
     *
     * @Route("/{id}/show", name="ksClub_show", options={"expose"=true})
     */
    public function showAction($id)
    {
        return $this->redirect($this->generateUrl('ksClub_public_profile', array("clubId" => $id)));
    }

    /**
     * Displays a form to create a new Club entity.
     *
     * @Route("/new", name="admin_club_new")
     * @Template()
     */
    public function newAction()
    {
        $user       = $this->get('security.context')->getToken()->getUser();
        
        $entity = new Club();
        $form   = $this->createForm(new ClubType($user), $entity);
        
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
        
        return array(
            'entity' => $entity,
            'map' => $map,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new Club entity.
     *
     * @Route("/create", name="admin_club_create")
     * @Method("post")
     * @Template("KsClubBundle:Club:new.html.twig")
     */
    public function createAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $user       = $this->get('security.context')->getToken()->getUser();
        
        $club  = new Club();
        $request = $this->getRequest();
        $form    = $this->createForm(new ClubType($user), $club);
        $form->bindRequest($request);
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        
        //TODO mise a jour de la table ks_user_manage_club
        $formHandler = new ClubHandler($form, $request, $this->getDoctrine()->getEntityManager(), $currentUser);
        
        
         if( $formHandler->process()){
            //on coche l'action correspondante dans la checklist
            $em->getRepository('KsUserBundle:ChecklistAction')->checkCreateClub($user->getId());
            
            $this->get('session')->setFlash('alert alert-success', 'users.club_creation_success');
            return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }
        
        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Club entity.
     *
     * @Route("/{id}/edit", name="ksClub_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $user       = $this->get('security.context')->getToken()->getUser();
        $sportRep   = $em->getRepository('KsActivityBundle:Sport');

        $club = $em->getRepository('KsClubBundle:Club')->find($id);
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$user->getId(),"club"=>$id));
        if($userManageClub==null){
             return $this->redirect($this->generateUrl('admin_club_new'));
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
        if (count($response->getResults())==1){
            foreach ($response->getResults() as $result){
                $marker->setPosition($result->getGeometry()->getLocation());
                $latitude = $result->getGeometry()->getLocation()->getLatitude();
                $longitude = $result->getGeometry()->getLocation()->getLongitude();
                $map->setCenter($latitude, $longitude, true);
            }
        }
        
        $latitude   = $club->getLatitude();
        $longitude  = $club->getLongitude();
        if ($latitude && $longitude) {
            $resp   = $geocoder->reverse($latitude, $longitude);
            $result = $resp->getResults();
            $nbResult = count($result);
            if($nbResult > 0){
                $marker->setPosition($result[0]->getGeometry()->getLocation());
                $map->setCenter($latitude, $longitude, true);
            }
        }
        
       
        //on ajoute le marker 
        $map->addMarker($marker);
        /*---------------------*/
        
        $em     = $this->getDoctrine()->getEntityManager();
        $club   = $em->getRepository('KsClubBundle:Club')->find($id);

        if (!$club) {
            throw $this->createNotFoundException('Unable to find Club entity.');
        }

        $clubEditForm   = $this->createForm(new ClubType($user), $club);
        $deleteForm     = $this->createDeleteForm($id);
        $sports         = $sportRep->findAll();
        
        return array(
            'map'           => $map,
            'club'          => $club,
            'clubEditForm'  => $clubEditForm->createView(),
            'delete_form'   => $deleteForm->createView(),
            "sports"        => $sports
        );
    }

    /**
     * Edits an existing Club entity.
     *
     * @Route("/{id}/update", name="ksClub_update")
     * @Method("post")
     * @Template("KsClubBundle:Club:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $sportRep   = $em->getRepository('KsActivityBundle:Sport');
        
        $user       = $this->get('security.context')->getToken()->getUser();
        
        $club = $em->getRepository('KsClubBundle:Club')->find($id);
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$user->getId(),"club"=>$id));
        if($userManageClub==null){
             return $this->redirect($this->generateUrl('admin_club_new'));
        }
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
        $latitude = $club->getLatitude();
        $longitude = $club->getLongitude();
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
        
        $uploadHelper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
            
        /*if( $club->getImageName() != null){
            
            $pathOldImage = $uploadHelper->asset($club, 'image');
            $pathOldImage = substr ($pathOldImage , 1 );
            
            $pathOldImageResize = "img/clubs/resize_".$width."x".$height."/".$club->getImageName();
        }*/
           
        
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('KsClubBundle:Club')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Club entity.');
        }

        $clubEditForm   = $this->createForm(new ClubType( $user ), $entity);
        $deleteForm = $this->createDeleteForm($id);
        
        $formHandler = new ClubHandler($clubEditForm, $request, $this->getDoctrine()->getEntityManager(), $user);
        
        $responseDatas = $formHandler->process();
         if( $responseDatas["response"] == 1 ){
             if( count($responseDatas["club"]->getUsers()) > 2 ) {
                 //on coche l'action correspondante dans la checklist
                $em->getRepository('KsUserBundle:ChecklistAction')->checkInviteFriendInClub($user->getId());
             }
             /*if($responseDatas["club"]->getImageName() != null){
                //throw $this->createNotFoundException('passe ici.');
                $pathImage = $uploadHelper->asset($responseDatas["club"], 'image');
                $pathImage = substr ($pathImage , 1 );
                //var_dump($pathImage);

                //if($pathImage!=null && (isset($pathOldImage)) && $pathOldImage!=$pathImage){
                    $width = 48;
                    $height = 48;
                    $pathImageResize = "img/clubs/resize_".$width."x".$height."/".$responseDatas["club"]->getImageName();
                    
                    $responseDatas["club"]->resizeImage($pathImage,$pathImageResize,$width, $height);
                    //on oublie pas de supprimer les anciennes images si elles existent
                    if(isset($pathOldImage)){
                        $responseDatas["club"]->removeUpload($pathOldImage);
                        if(isset($pathOldImageResize)){
                            $responseDatas["club"]->removeUpload($pathOldImageResize);
                        }
                    }
                //}
             } else {
                 $this->get('session')->setFlash('alert alert-error', $responseDatas["club"]->getImage());
                 
             }*/
                
            $this->get('session')->setFlash('alert alert-success', 'users.club_update_success');
            //return $this->redirect($this->generateUrl('ksClub_edit', array("id" => $club->getId())));
            
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

         return $this->redirect($this->generateUrl('ksClub_edit', array("id" => $club->getId())));
        /*$sports = $sportRep->findAll();
        
        return array(
            'map'           => $map,
            'club'          => $club,
            'clubEditForm'   => $clubEditForm->createView(),
            'delete_form' => $deleteForm->createView(),
            "sports"     => $sports
        );*/
    }

    /**
     * Deletes a Club entity.
     *
     * @Route("/{id}/delete", name="admin_club_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        $idUser = $currentUser->getid();
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('KsClubBundle:Club')->find($id);
            
            

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Club entity.');
            }else{
                $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$idUser,"club"=>$id));
            }
            $this->get('session')->setFlash('alert alert-success', 'users.club_delete_success');
            $em->remove($userManageClub);
            $em->flush();
            $em->remove($entity);
            $em->flush();
           
            
        }

        return $this->redirect($this->generateUrl('ksClub_myClubs'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
    /**
     *
     * @Route("/{clubId}/informations", name="KsClub_informations", options={"expose"=true} )
     * @Template()
     */
    public function informationsAction( $clubId )
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $clubRep    = $em->getRepository('KsClubBundle:Club');
        $teamRep    = $em->getRepository('KsClubBundle:Team');

        $session = $this->get('session');
        $session->set('pageType', 'club');
        $session->set('page', 'infos');
        
        $club = $clubRep->find( $clubId );
        
        if (! $club ) {
            throw $this->createNotFoundException('Unable to find Club entity.');
        }
        

        return array(
            'club'  => $club,
        );
    }
    
    /**
     * Lists all club teams.
     *
     * @Route("/{clubId}/teams", name="KsClub_teams", options={"expose"=true} )
     * @Template()
     */
    public function teamsAction( $clubId )
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $clubRep    = $em->getRepository('KsClubBundle:Club');
        $teamRep    = $em->getRepository('KsClubBundle:Team');
        
        $session = $this->get('session');
        $session->set('pageType', 'club');
        $session->set('page', 'teams');
        
        $club = $clubRep->find( $clubId );
        
        if (! $club ) {
            throw $this->createNotFoundException('Unable to find Club entity.');
        }
        
        $teams = $teamRep->findByClub( $clubId );

        return array(
            'club'  => $club,
            'teams' => $teams
        );
    }
    
    /**
     * Lists all club teams.
     *
     * @Route("/{clubId}/events", name="ksClub_events", options={"expose"=true} )
     * @Template()
     */
    public function eventsAction( $clubId )
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $clubRep    = $em->getRepository('KsClubBundle:Club');
        $eventRep    = $em->getRepository('KsEventBundle:Event');

        $session = $this->get('session');
        $session->set('pageType', 'club');
        $session->set('page', 'events');
        
        $club = $clubRep->find( $clubId );
        
        if (! $club ) {
            throw $this->createNotFoundException('Unable to find Club entity.');
        }
        
        $events = $eventRep->findBy( 
            array(
                "club" => $clubId
            ),
            array(
                "startDate" => "DESC"
            )
        );

        return array(
            'club'  => $club,
            'events' => $events
        );
    }
    
    /**
     * Lists all club members.
     *
     * @Route("/{clubId}/members", name="KsClub_members", options={"expose"=true} )
     * @Template()
     */
    public function membersAction( $clubId )
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $clubHasUsersRep    = $em->getRepository('KsClubBundle:ClubHasUsers');
        
        $session = $this->get('session');
        $session->set('pageType', 'club');
        $session->set('page', 'members');
        
        $user               = $this->container->get('security.context')->getToken()->getUser();
        
        if( ! is_object($user) )
        {
            $this->get('session')->setFlash('alert alert-error', 'La session a expirée. Merci de vous identifier.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $club = $clubRep->find( $clubId );
        
        if (! $club ) {
            throw $this->createNotFoundException('Unable to find Club entity.');
        }
        
        /*$clubHasUsers = $clubHasUsersRep->findByClub( $clubId );
        
        $members = array();
        
        foreach( $clubHasUsers as $clubHasUser ) {
            $members[] = $clubHasUser->getUser();
        }*/
        
        $usersIds = $clubRep->findMembersIds($clubId);
        
        $users = $userRep->findUsers(array("usersIds" => $usersIds), $this->get('translator'));
        
        //On cherche les liaison d'amitié et les forfaits en cours avec le club
        foreach( $users as $key => $u ) {
            $users[$key]['areFriends']                          = $userRep->areFriends($user->getId(), $u['id']);
            $users[$key]['mustGiveRequestFriendResponse']       = $userRep->mustGiveRequestFriendResponse($user->getId(), $u['id']);
            $users[$key]['isAwaitingRequestFriendResponse']     = $userRep->isAwaitingRequestFriendResponse($user->getId(), $u['id']);
            $users[$key]['remainingSessions']                   = $userRep->getRemainingSessions($clubId, $u['id']);
        }
       
        return array(
            'club'  => $club,
            'users' => $users
        );
    }
    
        /**
     * Création d'une nouvelle équipe dans le club
     *
     * @Route("/{clubId}/newTeam", name="KsClub_newTeam")
     */
    public function newTeamAction($clubId)
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $clubRep                = $em->getRepository('KsClubBundle:Club');
        $userManageClubRep      = $em->getRepository('KsClubBundle:UserManageClub');
        $user                   = $this->container->get('security.context')->getToken()->getUser();
        
        $club                   = $clubRep->find( $clubId );
        
        $userManageClub = $userManageClubRep->findOneBy(
                array(
                    "user"=> $user->getId(),
                    "club"=>$clubId
                )
        );
        
        if( !is_object( $userManageClub ) ) {
             return $this->redirect($this->generateUrl('ksClub_myClubs'));
        }

        $team = new Team();
        $team->setClub( $club );
        
        $teamType = new \Ks\ClubBundle\Form\TeamType( $club );
        
        $form   = $this->createForm($teamType, $team);
        
        return $this->render('KsClubBundle:Team:new.html.twig', array(
            'newTeamForm'   => $form->createView(),
            'club' => $club,
        ));
    }
    
    /**
     * Choose a friend to be admin of the club 
     * @Route("/{idClub}/{numPage}/friendclubadmin", name="club_friend_admin")
     * @Template()
     */
    public function friendclubadminAction($idClub,$numPage)
    {  
        $user1  = $this->container->get('security.context')->getToken()->getUser();
        $idUser = $user1->getid();
        
        $em     = $this->getDoctrine()->getEntityManager();
        $club   = $em->getRepository('KsClubBundle:Club')->find($idClub);
     
        //affichage de la liste d'amis
        // On récupère le repository
        $repository = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('KsUserBundle:User');
        
        $usersHasFriend = $repository->getFriendList($idUser);

        // On récupère le nombre total d'utilisateurs
        $nb_users = count($usersHasFriend);

        // On définit le nombre d'utilisateurs par page
        $nb_users_page = 5;

        // On calcule le nombre total de pages
        $nb_pages = ceil($nb_users/$nb_users_page);
        $nb_pages = $nb_pages > 0 ? $nb_pages : 1;
        
        // On va récupérer les utilisateurs à partir du N-ième événement :
        $offset = ($numPage-1) * $nb_users_page;

        // Ici on a changé la condition pour déclencher une erreur 404
        // lorsque la page est inférieur à 1 ou supérieur au nombre max.
        if( $numPage < 1 OR $numPage > $nb_pages )
        {
            throw $this->createNotFoundException('Page inexistante (page = '. $numPage .')');
        }

        // On récupère les utilisateurs qu'il faut grâce à findBy() :
        /*$users = $repository->findBy(
            array(),                 // Pas de critère
            array('username' => 'asc'), // On tri par date décroissante
            $nb_users_page,       // On sélectionne $nb_users_page articles
            $offset                  // A partir du $offset ième
        );
        
        //On récupère l'utilisateur connecté
        //$user1 = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($user1) )
        {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }*/
        
        //ici on vérifie que l'utilisateur n'administre déjà pas le club
        $aUsers = array();
        foreach($usersHasFriend as $key => $user) {
            $aUsers[$key]["SuperAdmin"] = false;
            
            if($idUser == $user->getUser()->getId()){
                 $myUserManagerId = $user->getFriend()->getId(); 
            }else{
                 $myUserManagerId = $user->getUser()->getId(); 
            }
            
            
            
            $userManageThisClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("club"=>$idClub,"user"=>$myUserManagerId));
            
            if($userManageThisClub!=null){
               $aUsers[$key]["ManageClub"] = true;
               if($userManageThisClub->getSuperAdmin()==true){
                   $aUsers[$key]["SuperAdmin"] = true;
               }
               
               
            }else{
               $aUsers[$key]["ManageClub"] = false; 
            }
            
            
        }
        
       


        return $this->render('KsClubBundle:Club:friendclubadmin.html.twig', array(
            'connectedUserId' => $user1->getId(),
            'users' => $usersHasFriend,
            'page'     => $numPage,    // On transmet à la vue la page courante,
            'nb_pages' => $nb_pages,   // Et le nombre total de pages.
            'club' => $club,
            'aUsers' => $aUsers,
        ));
        


    }
    
    /**
     * Name a friend to be admin of a club 
     * @Route("/{userId}/{idClub}/nameuserclubadmin", name="name_user_club_admin")
     * @Template()
     */
    public function nameuserclubadminAction($userId,$idClub)
    {
        //on vérifie que l'utilisateur qui fait ca a le droit 
        $em = $this->getDoctrine()->getEntityManager();
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        $idUser = $currentUser->getId();
        $club = $em->getRepository('KsClubBundle:Club')->find($idClub);
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$idUser,"club"=>$idClub));
        if($userManageClub==null){
             return $this->redirect($this->generateUrl('admin_club_new'));
        }
        

        $em = $this->getDoctrine()->getEntityManager();
        $club = $em->getRepository('KsClubBundle:Club')->find($idClub);
        if($club==null){
            throw $this->createNotFoundException('Club is not found');
        }
        $user = $em->getRepository('KsUserBundle:User')->find($userId);
        if($user==null){
            throw $this->createNotFoundException('User is not found');
        }
        
        
        
        $userManageClub = new \Ks\ClubBundle\Entity\UserManageClub($club, $user, 0);
        
        $em->persist($userManageClub);
        $em->flush();

        $this->get('session')->setFlash('alert alert-success', 'users.user_nammed_admin_success');
        // On redirige vers la page qui liste les utilisateurs
        return $this->redirect($this->generateUrl('club_friend_admin', array(
            'idClub' => $idClub,
            'numPage' => 1,
        )));

    }
    
    
     /**
     * Name a friend to be admin of a club 
     * @Route("/{userId}/{idClub}/revokeuserclubadmin", name="revoke_user_club_admin")
     * @Template()
     */
    public function revokeuserclubadminAction($userId,$idClub)
    {
        //on vérifie que l'utilisateur qui fait ca a le droit 
        $em = $this->getDoctrine()->getEntityManager();
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        $idUser = $currentUser->getId();
        $club = $em->getRepository('KsClubBundle:Club')->find($idClub);
        $userManageClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("user"=>$idUser,"club"=>$idClub));
        if($userManageClub==null){
             return $this->redirect($this->generateUrl('admin_club_new'));
        }
        

        $em = $this->getDoctrine()->getEntityManager();
        $club = $em->getRepository('KsClubBundle:Club')->find($idClub);
        if($club==null){
            throw $this->createNotFoundException('Club is not found');
        }
        $user = $em->getRepository('KsUserBundle:User')->find($userId);
        if($user==null){
            throw $this->createNotFoundException('User is not found');
        }
        
        
        $userManageThisClub = $em->getRepository('KsClubBundle:UserManageClub')->findOneBy(array("club"=>$idClub,"user"=>$userId));
        
        $em->remove($userManageThisClub);
        $em->flush();

        $this->get('session')->setFlash('alert alert-success', 'users.user_revoke_admin_success');
        // On redirige vers la page qui liste les utilisateurs
        return $this->redirect($this->generateUrl('club_friend_admin', array(
            'idClub' => $idClub,
            'numPage' => 1,
        )));

    }
    
    public function leftColumnAction($clubId)
    {    
        $securityContext    = $this->container->get('security.context');
        $em                 = $this->getDoctrine()->getEntityManager();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        
        $club = $clubRep->find($clubId);
                
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user               = $securityContext->getToken()->getUser();
            
            $isManager      = $clubRep->isManager($clubId, $user->getId());
            $status         = new \Ks\ActivityBundle\Entity\ActivityStatus();
            $status         ->setClub( $club ); 
            $statusForm     = $this->createForm(new \Ks\ActivityBundle\Form\ActivityStatusType(), $status);

            $link           = new \Ks\ActivityBundle\Entity\ActivityStatus();
            $link           ->setClub( $club );
            $linkForm       = $this->createForm(new \Ks\ActivityBundle\Form\ActivityLinkType(), $link);

            $photo          = new \Ks\ActivityBundle\Entity\ActivityStatus();
            $photo          ->setClub( $club );
            $photoForm      = $this->createForm(new \Ks\ActivityBundle\Form\ActivityPhotoType(), $photo);
        }
        
        $managers = $clubRep->findManagersByClub( $clubId );
        $aManagers = array();
        foreach( $managers as $key => $manager) {
            $userManager = $userRep->find($manager['user_id']);
            $aManagers[$key]['id'] = $userManager->getId();
            $aManagers[$key]['function'] = $manager['function'];
            $aManagers[$key]['username'] = $userManager->getUsername();
            $userdetail = $userManager->getUserDetail();
            $aManagers[$key]['firstname'] = !empty($userdetail) ? $userdetail->getFirstname() : null;
            $aManagers[$key]['lastname'] = !empty($userdetail) ? $userdetail->getLastname() : null;
            $aManagers[$key]['imageName'] = !empty($userdetail) ? $userdetail->getImageName() : null;
            $aManagers[$key]['friendWithMe']                       = $userRep->areFriends($user->getId(), $userManager->getId()); 
            $aManagers[$key]['mustGiveRequestFriendResponse']      = $userRep->mustGiveRequestFriendResponse($user->getId(), $userManager->getId());
            $aManagers[$key]['isAwaitingRequestFriendResponse']    = $userRep->isAwaitingRequestFriendResponse($user->getId(), $userManager->getId());
            $aManagers[$key]['membershipAskInProgress']    = $clubRep->isMembershipAskInProgress($clubId, $userManager->getId());
        }
        
        $managersNb = count( $aManagers );
        
        shuffle($aManagers);
        
        $members = $clubRep->findAthletesByClub( $clubId );
        $aMembers = array();
        foreach( $members as $key => $member) {
            $userMember = $userRep->find($member['user_id']);
            $aMembers[$key]['id'] = $userMember->getId();
            $aMembers[$key]['username'] = $userMember->getUsername();
            $userdetail = $userMember->getUserDetail();
            $aMembers[$key]['firstname'] = !empty($userdetail) ? $userdetail->getFirstname() : null;
            $aMembers[$key]['lastname'] = !empty($userdetail) ? $userdetail->getLastname() : null;
            $aMembers[$key]['imageName'] = !empty($userdetail) ? $userdetail->getImageName() : null;
            $aMembers[$key]['friendWithMe']                       = $userRep->areFriends($user->getId(), $userMember->getId()); 
            $aMembers[$key]['mustGiveRequestFriendResponse']      = $userRep->mustGiveRequestFriendResponse($user->getId(), $userMember->getId());
            $aMembers[$key]['isAwaitingRequestFriendResponse']    = $userRep->isAwaitingRequestFriendResponse($user->getId(), $userMember->getId());
            $aMembers[$key]['membershipAskInProgress']    = $clubRep->isMembershipAskInProgress($clubId, $userMember->getId());
        }
        
        $membersNb = count( $aMembers );
        
        shuffle($aMembers);

        //$aMembers = array_slice($aMembers, 0, 8);
        
        $membersAwaiting = $clubRep->findMembersAwaitingByClub( $clubId );
        $aMembersAwaiting = array();
        foreach( $membersAwaiting as $key => $member) {
            $aMembersAwaiting[$key]['id'] = $member->getId();
            $aMembersAwaiting[$key]['username'] = $member->getUsername();
            $userdetail = $member->getUserDetail();
            $aMembersAwaiting[$key]['firstname'] = !empty($userdetail) ? $userdetail->getFirstname() : null;
            $aMembersAwaiting[$key]['lastname'] = !empty($userdetail) ? $userdetail->getLastname() : null;
            $aMembersAwaiting[$key]['imageName'] = !empty($userdetail) ? $userdetail->getImageName() : null;
            $aMembersAwaiting[$key]['friendWithMe']                       = $userRep->areFriends($user->getId(), $member->getId()); 
            $aMembersAwaiting[$key]['mustGiveRequestFriendResponse']      = $userRep->mustGiveRequestFriendResponse($user->getId(), $member->getId());
            $aMembersAwaiting[$key]['isAwaitingRequestFriendResponse']    = $userRep->isAwaitingRequestFriendResponse($user->getId(), $member->getId());
            $aMembersAwaiting[$key]['membershipAskInProgress']    = $clubRep->isMembershipAskInProgress($clubId, $member->getId());
        }
        
        $membersAwaitingNb = count( $aMembersAwaiting );
        
        shuffle($aMembersAwaiting);

        //$aMembersAwaiting = array_slice($aMembersAwaiting, 0, 8);
        
        return $this->render('KsClubBundle:Club:_leftColumn.html.twig', array(
            'club'                      => $club,
            'isManager'                 => $isManager,
            'statusForm'                => $statusForm->createView(),
            'linkForm'                  => $linkForm->createView(),
            'photoForm'                 => $photoForm->createView(),
            'membersAwaiting'           => $aMembersAwaiting,
            'members'                   => $aMembers,
            'managers'                  => $aManagers,
            'membersNb'                 => $membersNb,
            'managersNb'                => $managersNb,
            'membersAwaitingNb'         => $membersAwaitingNb
        ));
    }
    
    /**
     * @Route("/public_profile/{clubId}", name="ksClub_public_profile", options={"expose"=true} )
     * @Template()
     */
    public function publicProfileAction($clubId)
    {   
        $em                 = $this->getDoctrine()->getEntityManager();  
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $teamRep            = $em->getRepository('KsClubBundle:Team');    
        $agendaRep          = $em->getRepository('KsAgendaBundle:Agenda');
        $clubHasUsersRep    = $em->getRepository('KsClubBundle:ClubHasUsers');
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'clubs');
        $session->set('clubIdSelected', $clubId);
        $session->set('page', 'clubProfile');
        
        $user = $this->container->get('security.context')->getToken()->getUser();
        
       // $team = $teamRep->findOneByLabel("t1");
        $club = $clubRep->find($clubId);
        
        if( ! is_object($club) )
        {
            throw $this->createNotFoundException('Impossible de récupérer le club ' . $clubId .".");
        }
           
        //Formulaires
        $activitySession = new \Ks\ActivityBundle\Entity\ActivitySession();
        $activitySession->setClub( $club );
        $activitySportChoiceForm = $this->createForm(new \Ks\ActivityBundle\Form\SportType(null), $activitySession);
        
        /*$lastFiveEvents = $eventRep->findBy(
            array("club" => $clubId),
            array("startDate" => "desc"),
            2
        );*/
        
        $lastFiveEvents = $agendaRep->findAgendaEvents(array(
            "clubId"  => $clubId,
            "limit"     => 3,  
            "extended"  => true
        ), $this->get('translator'));
        
        $members = $clubRep->findMembersByClub( $clubId );//var_dump(count($members));
        $aMembers = array();
        foreach( $members as $key => $member) {
            $aMembers[$key]['id'] = $member->getId();
            $aMembers[$key]['username'] = $member->getUsername();
            $userdetail = $member->getUserDetail();
            $aMembers[$key]['firstname'] = !empty($userdetail) ? $userdetail->getFirstname() : null;
            $aMembers[$key]['lastname'] = !empty($userdetail) ? $userdetail->getLastname() : null;
            $aMembers[$key]['imageName'] = !empty($userdetail) ? $userdetail->getImageName() : null;
            $aMembers[$key]['friendWithMe']                       = $userRep->areFriends($user->getId(), $member->getId()); 
            $aMembers[$key]['mustGiveRequestFriendResponse']      = $userRep->mustGiveRequestFriendResponse($user->getId(), $member->getId());
            $aMembers[$key]['isAwaitingRequestFriendResponse']    = $userRep->isAwaitingRequestFriendResponse($user->getId(), $member->getId());
        }
        
        $membersNb = count( $aMembers );
        
        shuffle($aMembers);

        $aMembers = array_slice($aMembers, 0, 8);
        
        $teams = $teamRep->findTeamsWithUsersAndLastTeamCompositions( $clubId );
        
        
        $nbMembershipAskInProgress = count($clubHasUsersRep->findBy(
            array(
                "club"                      => $club->getId(),
                "membershipAskInProgress"   => true
            )
        ));
        //$clubHasUsers       = new \Ks\ClubBundle\Entity\ClubHasUsers( $club );
        //$clubHasUsersForm   = $this->createForm(new \Ks\ClubBundle\Form\ClubHasUsersType( $club, $user ), $clubHasUsers);
        
        //V2 pour l'instant on redirige vers la page d'actu
        return $this->redirect($this->generateUrl('ksActivity_activitiesList'));
        
        /*return $this->render(
            'KsClubBundle:Club:publicProfile.html.twig', array(
                'club'                      => $club,
                'activitySportChoiceForm'   => $activitySportChoiceForm->createView(),
                'members'                   => $aMembers,
                'membersNb'                 => $membersNb,
                'teams'                     => $teams,
                'lastFiveEvents'            => $lastFiveEvents,
                "nbMembershipAskInProgress" => $nbMembershipAskInProgress
                //'clubHasUsersForm'          => $clubHasUsersForm->createView(),               
            )
        );*/ 
    } 
    
   /**
     * 
     * @Route("/getTeamDetailsBloc/{teamId}", name = "ksClub_getTeamDetailsBloc", options={"expose"=true} )
     * @param int $teamId 
     */
    public function getTeamDetailsBlocAction($teamId)
    {
        $em                 = $this->getDoctrine()->getEntityManager(); 
       
        $teamRep           = $em->getRepository('KsClubBundle:Team');
        
        $responseDatas = array();

        $team = $teamRep->find($teamId);
        
        if (!is_object($team) ) {
            //throw new AccessDeniedException("Impossible de trouver l'équipe " . $teamId .".");
            $responseDatas["response"] = -1; 
            $responseDatas["errorMessage"] = "Impossible de trouver l'équipe " . $teamId ."."; 
        } 
        else {
            $responseDatas["response"] = 1;        
            $responseDatas["html"] = $this->render('KsClubBundle:Club:_teamDetails.html.twig', array(
                'team'          => $team,
            ))->getContent();
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response; 
    }
    
    /**
     * 
     * @Route("/askForMembership/{clubId}", requirements={"activityId" = "\d+"}, name = "ksClub_askForMembership", options={"expose"=true} )
     * @param int $activityId 
     */
    public function askForMembershipAction($clubId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $clubRep        = $em->getRepository('KsClubBundle:Club');
        $clubHasUsersRep = $em->getRepository('KsClubBundle:ClubHasUsers');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        //Services
        $notificationService   = $this->get('ks_notification.notificationService');
        
        $club = $clubRep->find($clubId);
        
        if (!is_object($club) ) {
            $impossibleClubMsg = $this->get('translator')->trans('impossible-to-find-club-%clubId%', array('%clubId%' => $clubId));
            throw $this->createNotFoundException($impossibleClubMsg);
        }
        
        if (!is_object($user) ) {
            throw new AccessDeniedException("Vous devez être identifié");
        }
        
        $responseDatas = array();
        
        //Si l'utilisateur n'est pas déjà dans le club
        if ( !$clubHasUsersRep->isInClub( $club, $user )  ) {
            //Si l'utilisateur n'a pas déjà demandé à être membre
            if ( !$clubHasUsersRep->askForMembershipIsInProgress( $club, $user )  ) {
                $clubHasUsersRep->askForMembership( $club, $user );

                $responseDatas["response"] = 1;
                $responseDatas["message"] = $this->get('translator')->trans("Ta demande d'adhésion est en cours de validation par le club");
                
                $notificationType_name = "club";
                
                //On envoie une notification à chaque administrateur
                foreach( $club->getManagers() as $userManageClub ) {
                    $notificationService->sendClubNotification(
                        $club, 
                        $userManageClub->getUser(),
                        $notificationType_name,
                        $user->getUsername() . " souhaite devenir adhérent du club ". $club->getName(),
                        null,
                        $user
                    );
                }
            } else {
                $responseDatas["response"] = -1;
                $responseDatas["errorMessage"] = $this->get('translator')->trans('you-had-already-an-ask-for-membership-in-progress');
            }
        } else {
            $responseDatas["response"] = -1;
            $responseDatas["errorMessage"] = $this->get('translator')->trans('you-are-already-in-this-club');
        }
                
        $responseDatas["askForMembershipLink"] = $this->render('KsClubBundle:Club:_askForMembershipLink.html.twig', array(
            'club_id'                          => $club->getId()
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/askForMembershipV2/{clubId}", requirements={"activityId" = "\d+"}, name = "ksClub_askForMembership_V2", options={"expose"=true} )
     * @param int $clubId 
     */
    public function askForMembershipV2Action($clubId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $clubRep        = $em->getRepository('KsClubBundle:Club');
        $clubHasUsersRep = $em->getRepository('KsClubBundle:ClubHasUsers');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        //Services
        $notificationService   = $this->get('ks_notification.notificationService');
        
        $club = $clubRep->find($clubId);
        
        if (!is_object($club) ) {
            $impossibleClubMsg = $this->get('translator')->trans('impossible-to-find-club-%clubId%', array('%clubId%' => $clubId));
            throw $this->createNotFoundException($impossibleClubMsg);
        }
        
        if (!is_object($user) ) {
            throw new AccessDeniedException("Vous devez être identifié");
        }
        
        //Si l'utilisateur n'est pas déjà dans le club
        if ( !$clubHasUsersRep->isInClub( $club, $user )  ) {
            //Si l'utilisateur n'a pas déjà demandé à être membre
            if ( !$clubHasUsersRep->askForMembershipIsInProgress( $club, $user )  ) {
                $clubHasUsersRep->askForMembership( $club, $user );

                $this->get('session')->setFlash('alert alert-success', "Ta demande d'adhésion a été envoyée au club pour validation, merci de patienter :)");
                
                $notificationType_name = "club";
                
                //On envoie une notification à chaque administrateur
                foreach( $club->getManagers() as $userManageClub ) {
                    $notificationService->sendClubNotification(
                        $club, 
                        $userManageClub->getUser(),
                        $notificationType_name,
                        $user->getUsername() . " souhaite devenir adhérent du club ". $club->getName(),
                        null,
                        $user
                    );
                }
            } else {
                $this->get('session')->setFlash('alert alert-danger', "Tu as déjà envoyé une demande d'adhésion à ce club, merci de patienter :)");
            }
        } else {
            $this->get('session')->setFlash('alert alert-danger', 'Tu es déjà un adhérent de ce club !');
        }
        return new RedirectResponse($this->container->get('router')->generate('ksActivity_activitiesList'));
    }
    
    /**
     * 
     * @Route("/removeAskForMembership/{clubId}", requirements={"activityId" = "\d+"}, name = "ksClub_removeAskForMembership", options={"expose"=true} )
     * @param int $activityId 
     */
    public function removeAskForMembershipAction( $clubId )
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $clubRep        = $em->getRepository('KsClubBundle:Club');
        $clubHasUsersRep = $em->getRepository('KsClubBundle:ClubHasUsers');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $club = $clubRep->find($clubId);
        
        if (!is_object($club) ) {
            $impossibleClubMsg = $this->get('translator')->trans('impossible-to-find-club-%clubId%', array('%clubId%' => $clubId));
            throw $this->createNotFoundException($impossibleClubMsg);
        }
        
        if (!is_object($user) ) {
            throw new AccessDeniedException("Vous devez être identifié");
        }
        
        $responseDatas = array();

        
        //Si l'utilisateur n'est pas déjà dans le club
        if ( !$clubHasUsersRep->isInClub( $club, $user )  ) {
            //Si l'utilisateur n'a pas déjà demandé à être membre
            if ( $clubHasUsersRep->askForMembershipIsInProgress( $club, $user )  ) {
                $clubHasUsers = $clubHasUsersRep->findOneBy( array(
                    "club" => $club->getId(),
                    "user" => $user->getId()
                ));

                if(is_object($clubHasUsers)) {
                    $clubHasUsersRep->removeAskForMembership( $clubHasUsers );
                    $responseDatas["response"] = 1;
                } else {
                    $responseDatas["response"] = -1;
                    $responseDatas["errorMessage"] = $this->get('translator')->trans('impossible-to-find-clubHasUsers-%clubId%-%userId%', array('%clubId%' => $clubId, '%userId%' => $user->getId()));
                }   
            } else {
                $responseDatas["response"] = -1;
                $responseDatas["errorMessage"] = $this->get('translator')->trans('you-didnt-have-an-ask-for-membership-in-progress');
            }
        } else {
            $responseDatas["response"] = -1;
            $responseDatas["errorMessage"] = $this->get('translator')->trans('you-are-already-in-this-club');
        }
                
        $responseDatas["askForMembershipLink"] = $this->render('KsClubBundle:Club:_askForMembershipLink.html.twig', array(
            'club_id'                          => $club->getId(),
        ))->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * @Route("/list", name = "ksClub_all", options={"expose"=true} )
     */
    public function clubsDynamicListAction()
    {
        $em                 = $this->getDoctrine()->getEntityManager();  
        $clubRep            = $em->getRepository('KsClubBundle:Club');

        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'clubs');
        $session->set('clubIdSelected', null);
        $session->set('page', 'clubList');
        
        $clubs = $clubRep->findAll();

        
        return $this->render('KsClubBundle:Club:clubsList.html.twig', array(
            'clubs'    => $clubs,
        ));
    }
    
    /**
     * 
     * @Route("/sendInviteByMail/{clubId}", requirements={"clubId" = "\d+"}, name = "ksClub_sendInviteByMail", options={"expose"=true} )
     * @param int $clubId 
     */
    public function sendInviteByMailAction($clubId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $clubRep        = $em->getRepository('KsClubBundle:Club');
        $request        = $this->getRequest();
        $parameters     = $request->request->all();
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $club = $clubRep->find($clubId);
        
        if (!is_object($club) ) {
            $impossibleClubMsg = $this->get('translator')->trans('impossible-to-find-club-%clubId%', array('%clubId%' => $clubId));
            throw $this->createNotFoundException($impossibleClubMsg);
        }
        
        if (!is_object($user) ) {
            throw new AccessDeniedException("Vous devez être identifié");
        }
        
        $responseDatas = array();
        
        if( isset( $parameters['email'] ) && filter_var( $parameters['email'], FILTER_VALIDATE_EMAIL ) ) {
            $host       = $this->container->getParameter('host');
            $pathWeb    = $this->container->getParameter('path_web');
            $mailer     = $this->container->get('mailer');

            $contentMail = $this->container
                ->get('templating')
                ->render(
                    'KsClubBundle:Club:_inviteInClub_mail.html.twig',
                    array(
                        'user'          => $user,
                        'club'          => $club,
                        'host'          => $host,
                        'pathWeb'       => $pathWeb
                    ),
                    'text/html'
                );

            $body = $this->container->get('templating')->render('KsUserBundle:User:template_mail.html.twig', 
                array(
                    'host'      => $host,
                    'pathWeb'   => $pathWeb,
                    'content'   => $contentMail,
                    'user'      => is_object( $user ) ? $user : null
                ), 
            'text/html');

            $message = \Swift_Message::newInstance()
                ->setContentType('text/html')
                ->setSubject("Invitation à rejoindre le club " . $club->getName())
                ->setFrom("contact@keepinsport.com")
                ->setTo($parameters['email'])
                ->setBody($body);
            $mailer->getTransport()->start();
            $mailer->send($message);
            $mailer->getTransport()->stop();

            $responseDatas["response"] = 1;
            $responseDatas["message"] = "E-mail envoyé";
        } else {
            $responseDatas["response"] = -1;
            $responseDatas["message"] = "L'adresse mail n'est pas valide";
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/sendInviteByNotif/{clubId}", requirements={"clubId" = "\d+"}, name = "ksClub_sendInviteByNotif", options={"expose"=true} )
     * @param int $clubId 
     */
    public function sendInviteByNotifAction($clubId)
    {
        $em              = $this->getDoctrine()->getEntityManager();
        $clubRep         = $em->getRepository('KsClubBundle:Club');
        $userRep         = $em->getRepository('KsUserBundle:User');
        $notifTypeRep    = $em->getRepository('KsNotificationBundle:NotificationType');
        $request         = $this->getRequest();
        $parameters      = $request->request->all();
        $user            = $this->get('security.context')->getToken()->getUser();
        
        //services
        $notificationService    = $this->get('ks_notification.notificationService');
        
        $club = $clubRep->find($clubId);
        
        if (!is_object($club) ) {
            $impossibleClubMsg = $this->get('translator')->trans('impossible-to-find-club-%clubId%', array('%clubId%' => $clubId));
            throw $this->createNotFoundException($impossibleClubMsg);
        }
        
        if (!is_object($user) ) {
            throw new AccessDeniedException("Vous devez être identifié");
        }
        
        $responseDatas = array();

        $userIds = isset( $parameters['userIds'] ) && !empty( $parameters['userIds'] ) ? $parameters['userIds'] : array();
        
        $notificationType_name = "club";
        $notificationType = $notifTypeRep->findOneByName($notificationType_name);

        if ( $notificationType ) {
            if( count( $userIds ) > 0 ) {
                foreach( $userIds as $userId ) {
                    $userInvited = $userRep->find($userId);

                    if(is_object( $userInvited )) {
                        $notificationService->sendClubNotification(
                            $club, 
                            $userInvited,
                            $notificationType_name,
                            $user->getUsername() . " souhaite que tu t'inscrives sur Keepinsport pour rejoindre le club ". $club->getName()
                        );
                    } else {
                       $responseDatas["response"] = -1;
                        $responseDatas["message"] = "Impossible de trouver l'utilisateur " . $userId; 
                    }
                }
                $responseDatas["response"] = 1;
                $responseDatas["message"] = "Notification(s) envoyée(s)";
            } else {
                $responseDatas["response"] = -1;
                $responseDatas["message"] = "Sélectionne au moins un sportif";
            }
        } else {
            $responseDatas["response"] = -1;
            $responseDatas["message"] = "Impossible d'envoyer les notifications";
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * @Route("/{clubId}/inviteFriends", name = "ksClub_inviteFriends", options={"expose"=true} )
     * @Template()
    */
    public function inviteFriendsAction($clubId)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $clubRep    = $em->getRepository('KsClubBundle:Club');
        $userRep    = $em->getRepository('KsUserBundle:User');

        $session = $this->get('session');
        $session->set('pageType', 'clubs');
        $session->set('page', 'inviteFriends');
        
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
              
        return array(
            'club'              => $club,
            'friends'           => $friends,
            'clubInfos'         => $clubInfos,
        );
    }
    
    /**
     * Lists all club tournaments.
     *
     * @Route("/{clubId}/tournaments", name="KsClub_tournaments", options={"expose"=true} )
     * @Template()
     */
    public function tournamentsAction( $clubId )
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $userRep            = $em->getRepository('KsUserBundle:User');
        
        $session = $this->get('session');
        $session->set('pageType', 'clubs');
        $session->set('page', 'tournaments');
        $user               = $this->container->get('security.context')->getToken()->getUser();
        
        if( ! is_object($user) )
        {
            $this->get('session')->setFlash('alert alert-error', 'La session a expirée. Merci de vous identifier.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $club = $clubRep->find( $clubId );
        
        $tournamentRep  = $em->getRepository('KsTournamentBundle:Tournament');
        
        $tournaments = $tournamentRep->findTournaments(array(
            "clubId" => $club->getId()
        ));
        
        $tournament = new \Ks\TournamentBundle\Entity\Tournament();
        $tournament->setClub( $club );
        
        $tournamentForm = $this->createForm(new \Ks\TournamentBundle\Form\TournamentType( ), $tournament );
        

        return array(
            'club'  => $club,
            'tournaments' => $tournaments,
            'tournamentForm' => $tournamentForm->createView()
        );
    }
    

    /* RENDERS */
    public function lastActivitiesAction($clubId, $nbActivities)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $activitySessionRep = $em->getRepository('KsActivityBundle:ActivitySession');
        $activityRep    = $em->getRepository('KsActivityBundle:Activity');
        
        $now = new \DateTime();
        //"issuedAt" < $now->format("Y-m-d h:i:s")
        /*$lastModifiedActivities = $activitySessionRep->findBy(
            array(),
            array("issuedAt" => "desc"),
            $nbActivities
        );*/
        
        $activities = $activityRep->findActivities(array(
            'endOn'             => $now->format("Y-m-d"),
            'perPage'           => $nbActivities,
            'activitiesTypes'   => array('session'),
            'withNoPrivateCoaching'     => true,//Pour les plans d'entrainements : si event privé uniquement le coaché et le coach peuvent voir l'activité
            'clubId'            => $clubId,
            'isPublic'          => true
            //'withLocalisation'    => true
        ));
        
        return $this->render(
            'KsActivityBundle:Activity:_last_activities.html.twig',
            array(
                'activities' => $activities,
            )
        );
        
    }
    
    public function actionsAction($id)
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $userManageClubRep      = $em->getRepository('KsClubBundle:UserManageClub');
        
        $user               = $this->container->get('security.context')->getToken()->getUser();
        
        $club = $clubRep->find($id);
        //var_dump($club->getId());
        if( is_object($club) && is_object($user) && $userManageClubRep->userIsClubManager( $club, $user ) ) {
            return $this->render(
                'KsClubBundle:ClubAdmin:_actions.html.twig',
                array(
                    'clubId' => $id,
                )
            );
        } else {
            return $this->render(
                'KsClubBundle:Club:_actions.html.twig',
                array(
                    'clubId' => $id,
                )
            );
        }
    }
    
    public function package_modalAction() {
        $form       = $this->createForm(new \Ks\ClubBundle\Form\PackageType());
        
        return $this->render('KsClubBundle:Club:_package_modal.html.twig', array(
            "form"            => $form->createView()
        ));
    }
    
    /**     
     * @Route("/getDetails/{clubId}/{userId}", name="ksClub_getPackageDetails", options={"expose"=true})
     * @Template()
     */
    public function getDetailsAction($clubId, $userId)
    {      
        $em             = $this->getDoctrine()->getEntityManager();
        $clubRep        = $em->getRepository('KsClubBundle:Club');
        $userRep        = $em->getRepository('KsUserBundle:User');
        
        $club = $clubRep->find($clubId);
        $user = $userRep->find($userId);
        
        if ( ! is_object($club) || ! is_object($user)  ) {
            $responseDatas["responseUpdate"] = -1;
            $responseDatas["errorMessage"] = "Impossible de récupérer les infos !";
        } else {
            $responseDatas["username"]                = $user->getUserName();
            $responseDatas["remainingSessions"]       = $userRep->getRemainingSessions($clubId, $userId);
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;  
    }
    
    /**
     * @Route("/createNewPackage/", name = "ksClub_createNewPackage", options={"expose"=true} )
     */
    public function createNewPackageAction()
    {
        $em             = $this->getDoctrine()->getEntityManager();
        
        $package      = new \Ks\UserBundle\Entity\UserHasSportPackageFromClub();
        $packageType  = new \Ks\ClubBundle\Form\PackageType();
        
        $form = $this->createForm($packageType, $package);
        
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            
            if ($form->isValid()) {
                //Création d'un nouveau package pour l'utilisateur du club
                $package->setClub($em->getRepository('KsClubBundle:Club')->find($_POST['clubId']));
                $package->setUser($em->getRepository('KsUserBundle:User')->find($_POST['userId']));
                $package->setSport($em->getRepository('KsActivityBundle:Sport')->find($_POST['sportId']));
                $em->persist($package);
                $em->flush();
                
                $responseDatas = array(
                    'publishResponse'   => 1,
                    'clubId'            => $_POST['clubId']
                );
                
            } else {
                $this->get('session')->setFlash('alert alert-error', 'Erreur lors de la sauvegarde du forfait, merci de nous contacter par mail avec le feedback ci-dessous !');
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
     * @Route("/updatePackage/", name = "ksClub_updatePackage", options={"expose"=true} )
     */
    public function updatePackageAction()
    {
        $em               = $this->getDoctrine()->getEntityManager();
        $packageRep       = $em->getRepository('KsUserBundle:UserHasSportPackageFromClub');
                
        $package      = new \Ks\UserBundle\Entity\UserHasSportPackageFromClub();
        $packageType  = new \Ks\ClubBundle\Form\PackageType();
        
        $form = $this->createForm($packageType, $package);
        
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                //var_dump($_POST);
                $packageRep->deleteUserHasSportPackageFromClub($_POST['clubId'], $_POST['userId']);
                
                $package->setClub($em->getRepository('KsClubBundle:Club')->find($_POST['clubId']));
                $package->setUser($em->getRepository('KsUserBundle:User')->find($_POST['userId']));
                $package->setSport($em->getRepository('KsActivityBundle:Sport')->find($_POST['sportId']));
                $em->persist($package);
                $em->flush();
                
                $responseDatas = array(
                    'publishResponse' => 1,
                    'clubId' => $_POST['clubId']
                );
                
            } else {
                $this->get('session')->setFlash('alert alert-error', 'Erreur lors de la sauvegarde du forfait, merci de nous contacter par mail avec le feedback ci-dessous !');
                //var_dump(\Form\FormValidator::getErrorMessages($form));
                
                $responseDatas = array(
                    'publishResponse' => -1,
                    'clubId' => $_POST['clubId']
                );
            }
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json'); 

        return $response; 
    }
    
    /**
     * 
     * @Route("/sendUpdateByMail/{clubId}", requirements={"clubId" = "\d+"}, name = "ksClub_sendUpdateToParticipants", options={"expose"=true} )
     * @param int $clubId 
     */
    public function sendUpdateByMailAction($clubId)
    {
        $em             = $this->getDoctrine()->getEntityManager();
        $clubRep        = $em->getRepository('KsClubBundle:Club');
        $userRep        = $em->getRepository('KsUserBundle:User');
        $request        = $this->getRequest();
        $parameters     = $request->request->all();
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $club = $clubRep->find($clubId);
        
        if (!is_object($club) ) {
            $impossibleClubMsg = $this->get('translator')->trans('impossible-to-find-club-%clubId%', array('%clubId%' => $clubId));
            throw $this->createNotFoundException($impossibleClubMsg);
        }
        
        if (!is_object($user) ) {
            throw new AccessDeniedException("Vous devez être identifié sur le site !");
        }
        
        $responseDatas = array();
        
        if( isset( $parameters['userIds'] ) && $parameters['userIds'] != null) {
            $host       = $this->container->getParameter('host');
            $pathWeb    = $this->container->getParameter('path_web');
            $mailer     = $this->container->get('mailer');

            foreach($parameters['userIds'] as $participantId) {
                $participant = $userRep->find($participantId);
                
                $contentMail = $this->container
                    ->get('templating')
                    ->render(
                        'KsClubBundle:Club:_updateToParticipants_mail.html.twig',
                        array(
                            'user'          => $participant,
                            'club'          => $club,
                            'host'          => $host,
                            'pathWeb'       => $pathWeb
                        ),
                        'text/html'
                    );
                
                $body = $this->container->get('templating')->render('KsUserBundle:User:template_mail.html.twig', 
                    array(
                        'host'      => $host,
                        'pathWeb'   => $pathWeb,
                        'content'   => $contentMail,
                        'user'      => is_object( $participant ) ? $participant : null
                    ), 
                'text/html');
            
                $message = \Swift_Message::newInstance()
                    ->setContentType('text/html')
                    ->setSubject($club->getName() . " : Mise à jour de ton agenda")
                    ->setFrom("contact@keepinsport.com")
                    ->setTo($participant->getEmail())
                    ->setBody($body);
                $mailer->getTransport()->start();
                $mailer->send($message);
                $mailer->getTransport()->stop();
            }
            $responseDatas["response"] = 1;
        } else {
            $responseDatas["response"] = -1;
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
}

