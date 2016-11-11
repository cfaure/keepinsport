<?php
namespace Ks\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Ks\UserBundle\Entity\Invitation;
use Ks\UserBundle\Form\InvitationType;
use Ks\UserBundle\Form\InvitationHandler;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;


use Symfony\Component\HttpFoundation\Request;

use Ks\UserBundle\Entity\UserHasFriends;




class FriendController extends Controller
{
    /**
     * @Route("/loadFbAnbGoogleContacts", name = "ksFriends_loadFbAnbGoogleContacts", options={"expose"=true} )
     */
    public function loadFbAnbGoogleContactsAction() {
        $em         = $this->getDoctrine()->getEntityManager();     
        $userRep    = $em->getRepository('KsUserBundle:User');
        $user       = $this->container->get('security.context')->getToken()->getUser();
        $request    = $this->getRequest();
        $parameters = $request->request->all();
        
        if( ! is_object($user) )
        {
            $this->get('session')->setFlash('alert alert-error', 'La session a expirée. Merci de vous identifier.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        //var_dump($parameters);
        //services
        $facebookService    = $this->get('ks_facebook');

        $contactsNotOnKs = $contactsOnKs = array();
        
        if( isset( $parameters['isConnectedToFB'] ) && $parameters['isConnectedToFB'] == "true" ) {
             //echo "searchFb";
            //Récupération des amis facebook
            if( isset( $parameters['accessTokenFacebook'] ) && !empty( $parameters['accessTokenFacebook'] ) ) {
                $facebookService->facebook->setAccessToken($parameters['accessTokenFacebook']);
                $fbFriends = $facebookService->facebook->api('/me/friends?fields=id,name,picture'); //,username,email
                //var_dump($fbFriends);
                if( isset( $fbFriends["data"] )) {
                    foreach( $fbFriends["data"] as $fbFriend) {
                        //Si l'utilisateur n'a pas de "username" on ne pourra pas le contacter avec username@facebook.com
                        //On ne l'affiche pas
                        //if( isset( $fbFriend["username"] ) ) {
                            $fbFriendDetails = array(
                                "fbId"                  => $fbFriend["id"],
                                "name"                  => $fbFriend["name"],
                                //"username"            => $fbFriend["username"],
                                //"email"               => isset( $fbFriend["email"] ) ? $fbFriend["email"] : null,                   
                                "picture"               => $fbFriend["picture"]["data"]["url"],
                                "isFacebookFriend"      => true,
                                "isGoogleContact"       => false,
                                "ksSubscribed"          => false,
                                "ksFriendWithMe"        => false,
                                "ksAwaitingRFResponse"  => false,
                                "ksId"                  => -1,
                            );

                            //On recherche si ce facebook friend est inscrit sur ks avec le même compte fb
                            $ksUser = $userRep->findOneBy(array("facebookId" => $fbFriendDetails["fbId"]));

                            if( is_object($ksUser) ) {
                                $fbFriendDetails["ksSubscribed"] = true;
                                $fbFriendDetails["ksId"] = $ksUser->getId();
                                $fbFriendDetails["ksFriendWithMe"] = $userRep->areFriends($user->getId(), $ksUser->getId());
                                $fbFriendDetails["ksAwaitingRFResponse"] = $userRep->isAwaitingRequestFriendResponse($user->getId(), $ksUser->getId());

                                if( ! $fbFriendDetails["ksFriendWithMe"] && ! $fbFriendDetails["ksAwaitingRFResponse"] ) {
                                    $contactsOnKs[] = $fbFriendDetails;
                                }
                            } else {
                                //$contactsNotOnKs[] = $fbFriendDetails;
                            }
                        //}
                    }
                }
            }
        }
        
        //Récupération des contacts Google
        if( isset( $parameters['isConnectedToGoogle'] ) && $parameters['isConnectedToGoogle'] == "true" ) {
            //echo "searchGoogle";
            if( isset( $parameters['accessTokenGoogle'] ) && !empty( $parameters['accessTokenGoogle'] ) ) {
                $accesstoken = $parameters['accessTokenGoogle'];

                $max_results = 500;
                $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
                $url = 'https://www.google.com/m8/feeds/contacts/default/full?max-results='.$max_results.'&oauth_token='.$accesstoken;
                 
                $curl = curl_init();
                curl_setopt($curl,CURLOPT_URL,$url);                //The URL to fetch. This can also be set when initializing a session with curl_init().
                curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);     //TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
                curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,5);        //The number of seconds to wait while trying to connect.	

                //curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);//The contents of the "User-Agent: " header to be used in a HTTP request.
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);	//To follow any "Location: " header that the server sends as part of the HTTP header.
                curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);      //To automatically set the Referer: field in requests where it follows a Location: redirect.
                curl_setopt($curl, CURLOPT_TIMEOUT, 10);            //The maximum number of seconds to allow cURL functions to execute.
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);	//To stop cURL from verifying the peer's certificate.
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);

                $xmlresponse = curl_exec($curl);
                curl_close($curl);

                //curl_exec($curl) renvoi un string car CURLOPT_RETURNTRANSFER == TRUE;
                if( $xmlresponse != "" ) {

                    $xmlresponse = str_replace('gd:','', $xmlresponse);

                    $xml = simplexml_load_string($xmlresponse);
                    foreach($xml->entry as $node){
                        if($node->title != "") {
                            $googleContactDetails = array(
                                "name"                  => (string) $node->title,
                                "email"                 => (string) $node->email['address'],                   
                                "picture"               => null,
                                "isFacebookFriend"      => false,
                                "isGoogleContact"       => true,
                                "ksSubscribed"          => false,
                                "ksFriendWithMe"        => false,
                                "ksAwaitingRFResponse"  => false,
                                "ksId"                  => -1,
                            );

                            //On recherche si ce google contact est inscrit sur ks avec la même adresse mail
                            $ksUser = $userRep->findOneBy(array("email" => $googleContactDetails["email"]));

                            if( is_object($ksUser) ) {
                                $googleContactDetails["ksSubscribed"] = true;
                                $googleContactDetails["ksId"] = $ksUser->getId();
                                $googleContactDetails["ksFriendWithMe"] = $userRep->areFriends($user->getId(), $ksUser->getId());
                                $googleContactDetails["ksAwaitingRFResponse"] = $userRep->isAwaitingRequestFriendResponse($user->getId(), $ksUser->getId());

                                if( ! $googleContactDetails["ksFriendWithMe"] && ! $googleContactDetails["ksAwaitingRFResponse"] ) {
                                    $contactsOnKs[] = $googleContactDetails;
                                }
                            } else {
                                //$contactsNotOnKs[] = $googleContactDetails;
                            }

                        }
                    }
                    
                    //Pour les tests
                    /*$contactsNotOnKs[] = array(
                        "name"                  => "Cédric Delpoux",
                        "email"                 => "cyberced@gmail.com",                   
                        "picture"               => null,
                        "isFacebookFriend"      => false,
                        "isGoogleContact"       => true,
                        "ksSubscribed"          => false,
                        "ksFriendWithMe"        => false,
                        "ksAwaitingRFResponse"  => false,
                        "ksId"                  => -1,
                    );*/
                }
            }
        }
        
        //var_dump($contactsOnKs);
        //var_dump($contactsNotOnKs);
        $responseDatas = array(
            "contactsOnKsHtml" => $this->render('KsUserBundle:Friend:_facebookFriendsAndGoogleContactsList.html.twig', array(
                'fbFriendsAndGoogleContacts'          => $contactsOnKs,
            ))->getContent(),
            /*"contactsNotOnKsHtml" => $this->render('KsUserBundle:Friend:_facebookFriendsAndGoogleContactsList.html.twig', array(
                'fbFriendsAndGoogleContacts'          => $contactsNotOnKs,
            ))->getContent()*/
        );
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');
        return $response; 
    }
            
    /**
     * @Route("/loadFbFriends", name = "ksFriends_loadFbFriends", options={"expose"=true} )
     */
    public function loadFbFriendsAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();     
        $userRep    = $em->getRepository('KsUserBundle:User');
        $user       = $this->container->get('security.context')->getToken()->getUser();
        
        if( ! is_object($user) )
        {
            $this->get('session')->setFlash('alert alert-error', 'La session a expirée. Merci de vous identifier.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        //services
        $facebookService    = $this->get('ks_facebook');
        
        
        $fbFriends = $facebookService->facebook->api('/me/friends?fields=id,name,picture'); //,username,email
        
        $fbFriendsNotOnKs = $fbFriendsOnKs = array();
        
        foreach( $fbFriends["data"] as $fbFriend) {
            //Si l'utilisateur n'a pas de "username" on ne pourra pas le contacter avec username@facebook.com
            //On ne l'affiche pas
            if( isset( $fbFriend["username"] ) ) {
                $fbFriendDetails = array(
                    "id"                    => $fbFriend["id"],
                    "name"                  => $fbFriend["name"],
                    //"username"              => $fbFriend["username"],
                    //"email"                 => isset( $fbFriend["email"] ) ? $fbFriend["email"] : null,                   
                    "picture"               => $fbFriend["picture"]["data"]["url"],
                    "ksSubscribed"          => false,
                    "ksFriendWithMe"        => false,
                    "ksAwaitingRFResponse"  => false,
                    "ksId"                  => -1
                );

                $ksUser = $userRep->findOneBy(array("facebookId" => $fbFriend["id"]));

                if( is_object($ksUser) ) {
                    $fbFriendDetails["ksSubscribed"] = true;
                    $fbFriendDetails["ksId"] = $ksUser->getId();
                    $fbFriendDetails["ksFriendWithMe"] = $userRep->areFriends($user->getId(), $ksUser->getId());
                    $fbFriendDetails["ksAwaitingRFResponse"] = $userRep->isAwaitingRequestFriendResponse($user->getId(), $ksUser->getId());

                    //if( ! $fbFriendDetails["ksLinked"] && ! $fbFriendDetails["ksAwaitingRFResponse"] ) {
                        $fbFriendsOnKs[] = $fbFriendDetails;
                    //}
                } else {
                    //$fbFriendsNotOnKs[] = $fbFriendDetails;
                }
            }
        }

        $responseDatas = array(
            "fbFriendsOnKsHtml" => $this->render('KsUserBundle:Friend:_facebookFriendsList.html.twig', array(
                'fbFriends'          => $fbFriendsOnKs,
            ))->getContent(),
            /*"fbFriendsNotOnKsHtml" => $this->render('KsUserBundle:Friend:_facebookFriendsList.html.twig', array(
                'fbFriends'          => $fbFriendsNotOnKs,
            ))->getContent()*/
        );
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');
        return $response; 
    }
    
    /**
     * @Route("/loadGoogleContacts", name = "ksFriends_loadGoogleContacts", options={"expose"=true} )
     */
    public function loadGoogleContactsAction()
    {
        $em         = $this->getDoctrine()->getEntityManager();     
        $userRep    = $em->getRepository('KsUserBundle:User');
        $user       = $this->container->get('security.context')->getToken()->getUser();
        $request    = $this->getRequest();
        $parameters = $request->request->all();
        
        if( ! is_object($user) )
        {
            $this->get('session')->setFlash('alert alert-error', 'La session a expirée. Merci de vous identifier.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $responseDatas = array(
            "response" => 1,
            "messsage" => ""
        );
        
        //Récupération des contacts Google
        if( isset( $parameters['isConnectedToGoogle'] ) && $parameters['isConnectedToGoogle'] == "true" ) {
            //echo "searchGoogle";
            
            $contactsNotOnKs = array();
            
            if( isset( $parameters['accessTokenGoogle'] ) && !empty( $parameters['accessTokenGoogle'] ) ) {
                $accesstoken = $parameters['accessTokenGoogle'];

            
                $max_results = 500;
                $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
                $url = 'https://www.google.com/m8/feeds/contacts/default/full?max-results='.$max_results.'&oauth_token='.$accesstoken;
                $curl = curl_init();
                curl_setopt($curl,CURLOPT_URL,$url);	//The URL to fetch. This can also be set when initializing a session with curl_init().
                curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);	//TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
                //var_dump($test);
                curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,5);	//The number of seconds to wait while trying to connect.	

                //curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);	//The contents of the "User-Agent: " header to be used in a HTTP request.
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);	//To follow any "Location: " header that the server sends as part of the HTTP header.
                curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);	//To automatically set the Referer: field in requests where it follows a Location: redirect.
                curl_setopt($curl, CURLOPT_TIMEOUT, 10);	//The maximum number of seconds to allow cURL functions to execute.
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);	//To stop cURL from verifying the peer's certificate.
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
                $xmlresponse = curl_exec($curl);
                curl_close($curl);

                //curl_exec($curl) renvoi un string car CURLOPT_RETURNTRANSFER == TRUE;
                if( $xmlresponse != "" ) {

                    $xmlresponse = str_replace('gd:','', $xmlresponse);

                    $xml = simplexml_load_string($xmlresponse);
                    foreach($xml->entry as $node){
                        if($node->title != "") {
                            
                            $googleContactDetails = array(
                                "name"                  => (string) $node->title,
                                "email"                 => (string) $node->email['address'],                   
                                "picture"               => null,
                                "isFacebookFriend"      => false,
                                "isGoogleContact"       => true,
                                "ksSubscribed"          => false,
                                "ksFriendWithMe"        => false,
                                "ksAwaitingRFResponse"  => false,
                                "ksId"                  => -1,
                            );
                            
                            /*foreach($node->link as $link){
                                if( $link["type"] == "image/*" ) {
                                    $curl = curl_init();
                                    curl_setopt($curl,CURLOPT_URL,$link["href"].'?oauth_token='.$accesstoken);	//The URL to fetch. This can also be set when initializing a session with curl_init().
                                    //curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);	//TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
                                    //var_dump($test);
                                    curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,5);	//The number of seconds to wait while trying to connect.	

                                    //curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);	//The contents of the "User-Agent: " header to be used in a HTTP request.
                                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);	//To follow any "Location: " header that the server sends as part of the HTTP header.
                                    curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);	//To automatically set the Referer: field in requests where it follows a Location: redirect.
                                    curl_setopt($curl, CURLOPT_TIMEOUT, 10);	//The maximum number of seconds to allow cURL functions to execute.
                                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);	//To stop cURL from verifying the peer's certificate.
                                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
                                    $response = curl_exec($curl);
                                    curl_close($curl);
                                    
                                    if ( $response != "Photo not found") {
                                        $googleContactDetails["picture"] = $response;
                                        var_dump($response);
                                    }
                                    
                                }
                            }*/

                            //On recherche si ce google contact est inscrit sur ks avec la même adresse mail
                            $ksUser = $userRep->findOneBy(array("email" => $googleContactDetails["email"]));

                            if( is_object($ksUser) ) {
                                $googleContactDetails["ksSubscribed"] = true;
                                $googleContactDetails["ksId"] = $ksUser->getId();
                                $googleContactDetails["ksFriendWithMe"] = $userRep->areFriends($user->getId(), $ksUser->getId());
                                $googleContactDetails["ksAwaitingRFResponse"] = $userRep->isAwaitingRequestFriendResponse($user->getId(), $ksUser->getId());

                                //if( ! $fbFriendDetails["ksLinked"] && ! $fbFriendDetails["ksAwaitingRFResponse"] ) {
                                    //$contactsOnKs[] = $googleContactDetails;
                                //}
                            } else {
                                $contactsNotOnKs[] = $googleContactDetails;
                            }

                        }
                    }
                }
            }
        
            $responseDatas["contactsNotOnKsHtml"] = $this->render('KsUserBundle:Friend:_facebookFriendsAndGoogleContactsList.html.twig', array(
                    'fbFriendsAndGoogleContacts'          => $contactsNotOnKs
                )
            )->getContent();
        } else {
            //$responseDatas["response"] = -1;
            //$responseDatas["message"] = "Impossible de récupérer le jeton d'authentification";
        }

        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');
        return $response; 
    }
    
    /**
     * @Route("/sendFriendRequests", name = "ksFriends_sendFriendRequests", options={"expose"=true} )
     */
    public function sendFriendRequestsAction()
    {
        $em         = $this->getDoctrine()->getEntityManager(); 
        $request    = $this->getRequest();
        $userRep    = $em->getRepository('KsUserBundle:User');
        $notifRep   = $em->getRepository('KsNotificationBundle:NotificationType');
        $user       = $this->container->get('security.context')->getToken()->getUser();
        $parameters = $request->request->all();
        
        //Services
        $notificationService   = $this->get('ks_notification.notificationService');
        
        if( ! is_object($user) )
        {
            $this->get('session')->setFlash('alert alert-error', 'La session a expirée. Merci de vous identifier.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
                
        $responseDatas = array();
        $responseDatas["nbSendedRequests"] = 0;
        
         if( isset( $parameters['userIds'] ) && is_array( $parameters['userIds'] ) ) {
             
             foreach( $parameters['userIds'] as $userId ) {
                 
                 $friend = $em->getRepository('KsUserBundle:User')->find($userId);
                 
                 if( is_object( $friend ) ) {
                    //Si les utilisateurs ne sont pas déjà amis
                    if(!$userRep->areFriends($user->getId(), $friend->getId()) and !$userRep->mustGiveRequestFriendResponse($user->getId(), $friend->getId())) {
      
                        //On cré la liaison
                        $user_has_friends = new UserHasFriends($user, $friend);
                        $em->persist($user);
                        $em->persist($user_has_friends);
                        $em->flush();

                        //Une notification de demande d'ami
                        $notificationType_name = "ask_friend_request";
                        $notificationType = $notifRep->findOneByName($notificationType_name);

                        if (!$notificationType) {
                            throw $this->createNotFoundException('Impossible de trouver le type de notification ' . $notificationType_name . '.');
                        }
                        $notificationService->sendNotification(null, $user, $friend, $notificationType_name, "Souhaitez vous ajouter " . $user->getUsername() . " à votre liste d'amis ?");
                        
                        $responseDatas["nbSendedRequests"] += 1;
                    }
                 }
             }
         }
         
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');
        return $response; 
    }
    
    /**
     * @Route("/sendMailInvitations", name = "ksFriends_sendMailInvitations", options={"expose"=true} )
     */
    public function sendMailInvitationsAction()
    {
        $em         = $this->getDoctrine()->getEntityManager(); 
        $request    = $this->getRequest();
        $userRep    = $em->getRepository('KsUserBundle:User');
        $user       = $this->container->get('security.context')->getToken()->getUser();
        $parameters = $request->request->all();
        
        
        if( ! is_object($user) )
        {
            $this->get('session')->setFlash('alert alert-error', 'La session a expirée. Merci de vous identifier.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
                
        $responseDatas = array();
        $responseDatas["nbSendedMails"] = 0;
        
         if( isset( $parameters['emailAdresses'] ) && is_array( $parameters['emailAdresses'] ) ) {
             
             foreach( $parameters['emailAdresses'] as $emailAdress ) {
                 /*$emailGuest = $form->getData()->getEmailGuest();
                 $user
                 $invitationEmailBeta =  new \Ks\UserBundle\Entity\InvitationEmailBeta($emailGuest);
                 $invitationEmailBeta->setUserInviting($currentUser);
                 $invitationEmailBeta->setCouldInvit(false);
                 $invitationEmailBeta->setNomberInvitation(0);

                 $em->persist($invitationEmailBeta);
                 $em->flush();
                  //Envois du mail 
                 $emailInviting = $currentUser->getEmail();
                 */
                  
                $host       = $this->container->getParameter('host');
                $pathWeb    = $this->container->getParameter('path_web');
                $mailer     = $this->container->get('mailer');

                $contentMail = $this->container
                    ->get('templating')
                    ->render(
                        'KsUserBundle:Friend:_invite_mail.html.twig',
                        array(
                            'user'          => $user,
                            'host'          => $host,
                            'pathWeb'       => $pathWeb,
                            //'salt' => $form->getData()->getSalt(),
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
                    ->setSubject($user . " t'invite à le rejoindre sur Keepinsport")
                    ->setFrom("contact@keepinsport.com")
                    ->setTo($emailAdress)
                    ->setBody($body);
                
                $mailer->getTransport()->start();
                $mailer->send($message);
                $mailer->getTransport()->stop();
                  
                //on coche l'action correspondante dans la checklist
                $em->getRepository('KsUserBundle:ChecklistAction')->checkInviteFriends($user->getId());
                
                $responseDatas["nbSendedMails"] += 1;
            }
        }
         
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');
        return $response; 
    }
    
    /**
     * @Route("/ask_a_friend/{user2Id}", name = "ks_user_Ask_a_friend", options={"expose"=true})
     */
    public function ask_a_friendAction($user2Id = null) {
        
        //Services
        $notificationService   = $this->get('ks_notification.notificationService');
        
        if($user2Id == null) {
            throw $this->createNotFoundException("L'identifiant de l'utilisateur a mal été transmis.");
        }
        
        //On récupère l'utilisateur connecté
        $user1 = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($user1) )
        {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }
        
        //On récupère l'user2 (passé en paramètres)
        $em = $this->getDoctrine()->getEntityManager();
        $user2 = $em->getRepository('KsUserBundle:User')->find($user2Id);
        
        if (!$user2) {
            throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $user2Id . '.');
        }
        
        // On récupère le repository
        $repository = $this->getDoctrine()
                           ->getEntityManager()
                           ->getRepository('KsUserBundle:User');
        
        //Si les utilisateurs ne sont pas déjà amis
        if(!$repository->areFriends($user1->getId(), $user2->getId()) and !$repository->mustGiveRequestFriendResponse($user1->getId(), $user2->getId())) {
            //On a bien récupéré les 2 utilisateurs
            //$user1->addUser($user2);
            //$user2->addUser($user1);
            
            // On crée un objet association entre 2 utilisateurs (lien d'amitié).
            $user_has_friends = new UserHasFriends($user1, $user2);
            $user1->addUserHasFriends($user_has_friends);
            // On l'enregistre notre objet $user_user dans la base de données.
            //$em->persist($user_has_friends);
            $em->persist($user1);
            $em->persist($user_has_friends);
            $em->flush();

            //On sette une flash (Message qui apparaitra au chargement de la prochaine page)
            $this->get('session')->setFlash('alert alert-success', 'user.flash.ask_a_friend.success');
            
            //Une notification classique
            /*$notificationType_name = "friend_request";
            $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName($notificationType_name);
            
            if (!$notificationType) {
                throw $this->createNotFoundException('Impossible de trouver le type de notification ' . $notificationType_name . '.');
            }   
            $em->getRepository('KsNotificationBundle:Notification')->createNotification($user2, $user1, $notificationType, $user1 . " souhaite vous ajouter en ami.");*/
            
            //Une notification de demande d'ami
            $notificationType_name = "ask_friend_request";
            $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName($notificationType_name);
            
            if (!$notificationType) {
                throw $this->createNotFoundException('Impossible de trouver le type de notification ' . $notificationType_name . '.');
            }
            $notificationService->sendNotification(null, $user1, $user2, $notificationType_name, "Souhaitez vous ajouter " .$user1 . " à votre liste d'amis ?");
            //$em->getRepository('KsNotificationBundle:Notification')->createNotification($user2, $user1, $notificationType, "Souhaitez vous ajouter " .$user1 . " à votre liste d'amis ?", null, true);
        } else {
            
            //On sette une flash (Message qui apparaitra au chargement de la prochaine page)
            $this->get('session')->setFlash('alert alert-error', 'user.flash.ask_a_friend.error');
        }
        
        // On redirige vers la page du profil de cet utilisateur
        return $this->redirect($this->generateUrl('ks_user_public_profile', array(
            'username' => $user2->getUsername()
        )));
    }
    
    /**
     * @Route("/revoke_a_friend/{user2Id}", name = "ks_user_Revoke_a_friend", options={"expose"=true} )
     */
    public function revoke_a_friendAction($user2Id = null)
    {
        //Services
        $notificationService   = $this->get('ks_notification.notificationService');
        
        if($user2Id == null) {
            throw $this->createNotFoundException("L'identifiant de l'utilisateur a mal été transmis.");
        }
        
        //On récupère l'utilisateur connecté
        $user1 = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if (! is_object($user1))
        {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }
        
        //On récupère l'user2 (passé en paramètres)
        $em = $this->getDoctrine()->getEntityManager();
        $user2 = $em->getRepository('KsUserBundle:User')->find($user2Id);
        
        if (!$user2) {
            throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $user2Id . '.');
        }
        
        // On récupère le repository
        $repository = $this->getDoctrine()
                           ->getEntityManager()
                           ->getRepository('KsUserBundle:User');
        
        //Si les utilisateurs sont bien déjà amis
        if ($repository->areFriends($user1->getId(), $user2->getId())) {
            $removeSuccess = false;
            
            $uhf1 = $em->getRepository('KsUserBundle:UserHasFriends')->find(array("user" => $user1->getId(), "friend" => $user2->getId()));
            if ($uhf1) {
                $removeSuccess = $user1->removeUserHasFriends($uhf1);
                $em->persist($uhf1);
                $em->remove($uhf1);
            }
            
            $uhf2 = $em->getRepository('KsUserBundle:UserHasFriends')->find(array("user" => $user2->getId(), "friend" => $user1->getId()));
            if ($uhf2) {
                $removeSuccess = $user2->removeUserHasFriends($uhf2);
                $em->persist($uhf2);
                $em->remove($uhf2);
            }            

            $em->persist($user1);
            $em->persist($user2);

            $em->flush();

            //On sette une flash (Message qui apparaitra au chargement de la prochaine page)
            if ($removeSuccess) {
                $this->get('session')->setFlash('alert alert-success', 'user.flash.revoke_a_friend.success');
                
                $notificationType_name = "friend_request";
                $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName($notificationType_name);

                if (!$notificationType) {
                    throw $this->createNotFoundException('Impossible de trouver le type de notification ' . $notificationType_name . '.');
                }   
                $notificationService->sendNotification(null, $user1, $user2, $notificationType_name, $user1 . " vous a retiré de sa liste d'ami");
                //$em->getRepository('KsNotificationBundle:Notification')->createNotification($user2, $user1, $notificationType, $user1 . " vous a retiré de sa liste d'ami");
            } else {
                $this->get('session')->setFlash('alert alert-error', 'user.flash.revoke_a_friend.error.remove_failed');
            }
        } else {
            
            //On sette une flash (Message qui apparaitra au chargement de la prochaine page)
            $this->get('session')->setFlash('alert alert-error', 'user.flash.revoke_a_friend.error.are_not_friends');
        }
        
        // On redirige vers la page du profil de cet utilisateur
        return $this->redirect($this->generateUrl('ks_user_public_profile', array(
            'username' => $user2->getUsername()
        )));
    }
    
    /**
     * @Route("/cancel_a_friend_request/{user2Id}", name = "ks_user_Cancel_the_friend_request", options={"expose"=true} )
     */
    public function cancel_a_friend_requestAction($user2Id = null)
    {
        //Services
        $notificationService   = $this->get('ks_notification.notificationService');
        
        if($user2Id == null) {
            throw $this->createNotFoundException("L'identifiant de l'utilisateur a mal été transmis.");
        }
        
        //On récupère l'utilisateur connecté
        $user1 = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($user1) )
        {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }
        
        //On récupère l'user2 (passé en paramètres)
        $em = $this->getDoctrine()->getEntityManager();
        $user2 = $em->getRepository('KsUserBundle:User')->find($user2Id);
        
        if (!$user2) {
            throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $user2Id . '.');
        }
        //var_dump($user1);
        //$user_has_friends = $em->getRepository('KsUserBundle:UserHasFriends')->find(array("user" => $user1, "friend" => $user2));
        
        // On récupère le repository
        $repository = $this->getDoctrine()
                           ->getEntityManager()
                           ->getRepository('KsUserBundle:User');
        
        //Si les utilisateurs sont bien déjà amis
        if($repository->isAwaitingRequestFriendResponse($user1, $user2)) {
            //On a bien récupéré les 2 utilisateurs
            $removeSuccess = false;
            
            $uhf1 = $em->getRepository('KsUserBundle:UserHasFriends')->find(array("user" => $user1->getId(), "friend" => $user2->getId()));

            if ($uhf1) {
                $removeSuccess = $user1->removeUserHasFriends($uhf1);
                $em->persist($uhf1);
                $em->remove($uhf1);
            } else {
                throw $this->createNotFoundException('Impossible de trouver la liaison d\'amitié de ' . $user1->getId() . " et ". $user2->getId() . '.');
            }
            
            //$removeSuccess = $user2->removeUserHasFriends($uhf1);
            $uhf2 = $em->getRepository('KsUserBundle:UserHasFriends')->find(array("user" => $user2->getId(), "friend" => $user1->getId()));

            if ($uhf2) {
                $removeSuccess = $user2->removeUserHasFriends($uhf2);              
                $em->persist($uhf2);
                $em->remove($uhf2);
            } else {
                //throw $this->createNotFoundException('Impossible de trouver la liaison d\'amitié de ' . $user1->getId() . " et ". $user2->getId() . '.');
            }
            

            // On l'enregistre notre objet $user_user dans la base de données.
            $em->persist($user1);
            $em->persist($user2);

            $em->flush();

            //On sette une flash (Message qui apparaitra au chargement de la prochaine page)
            if ($removeSuccess) {
                $this->get('session')->setFlash('alert alert-success', 'user.flash.cancel_a_friend_request.success');
                
                $notificationType_name = "ask_friend_request";
                $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName($notificationType_name);

                if (!$notificationType) {
                    throw $this->createNotFoundException('Impossible de trouver le type de notification ' . $notificationType_name . '.');
                }   
            
                //On récupère l'ancienne notification pour signaler qu'une réponse a été fourni
                $oldNotification = $em->getRepository('KsNotificationBundle:Notification')->findOneBy(
                    array(
                        'owner'         => $user2->getId(),
                        'fromUser'      => $user1->getId(),
                        'type'          => $notificationType->getId(),
                        'gotAnAnswer'   => 0
                    )
                );

                if (!$oldNotification) {
                    throw $this->createNotFoundException('Impossible de trouver l\'ancienne notification.');
                }

                $oldNotification->setGotAnAnswer(true);
                $em->persist($oldNotification);
                $em->flush();
                
                //On envoi une notification pour signaler que la demande d'ami n'est plus d'actualité
                //Une notification classique
                $notificationType_name = "friend_request";
                $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName($notificationType_name);

                if (!$notificationType) {
                    throw $this->createNotFoundException('Impossible de trouver le type de notification ' . $notificationType_name . '.');
                }   
                $notificationService->sendNotification(null, $user1, $user2, $notificationType_name, $user1 . " a annulé la demande en ami.");
                //$em->getRepository('KsNotificationBundle:Notification')->createNotification($user2, $user1, $notificationType, $user1 . " a annulé la demande en ami.");
            } else {
                $this->get('session')->setFlash('alert alert-error', 'user.flash.cancel_a_friend_request.error.remove_failed');
            }
        } else {
            
            //On sette une flash (Message qui apparaitra au chargement de la prochaine page)
            $this->get('session')->setFlash('alert alert-error', 'user.flash.cancel_a_friend_request.error.is_not_awaiting_friend_request_response');
        }
        
        // On redirige vers la page du profil de cet utilisateur
        return $this->redirect($this->generateUrl('ks_user_public_profile', array(
            'username' => $user2->getUsername()
        )));
    }
    
     /**
     * @Route("/search_a_friend/{numPage}", name = "ks_user_Search_a_friend" )
     */
     public function search_a_friendAction(Request $request, $numPage)
    {  
        $string = $request->request->get('searchFriend');
        $string = addslashes($string);

        // On récupère le repository
        $em         = $this->getDoctrine()->getEntityManager();
        $userRep    = $em->getRepository('KsUserBundle:User');
        
        //On récupère l'utilisateur connecté 
        $user1              = $this->container->get('security.context')->getToken()->getUser();
        $idCurrentUser      = $user1->getId();
        $aUsers             = array();
        $usersArray         = $userRep->searchUserFriends($idCurrentUser, $string);
        $userDetailRepo     = $em->getRepository('KsUserBundle:UserDetail');
        
        //les différentes action qu'il peut effectuer
        foreach ($usersArray as $key => $user) {
            $user       = $userRep->find($user['id']);
            $userDetail = $user->getUserDetail(); //$userDetailRepo->find($user->getUserDetailId());

            $aUsers[$key]["friendWithMe"]                       = $userRep->areFriends($user1->getId(), $user->getId()); 
            $aUsers[$key]["mustGiveRequestFriendResponse"]      = $userRep->mustGiveRequestFriendResponse($user1->getId(), $user->getId());
            $aUsers[$key]["isAwaitingRequestFriendResponse"]    = $userRep->isAwaitingRequestFriendResponse($user1->getId(), $user->getId());
            $aUsers[$key]['firstname']  = $userDetail ? $userDetail->getFirstname() : '';
            $aUsers[$key]['lastname']   = $userDetail ? $userDetail->getLastname() : '';
        }
        
        $responseDatas          = array();
        $responseDatas['html']  = $this->render(
            'KsUserBundle:User:_usersSearchList.html.twig',
            array(
                'connectedUserId'   => $user1->getId(),
                'users'             => $usersArray, 
                'string'            => $string,
                'aUsers'            => $aUsers
            )
        )->getContent();
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
   
    }
    
    
    /**
     * @Route("/accept_a_friend_request/{user2Id}", name = "ks_user_Accept_a_friend_request", options={"expose"=true} )
     */
    public function accept_a_friend_requestAction($user2Id = null)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $trophyService          = $this->get('ks_trophy.trophyService');
        $notificationService    = $this->get('ks_notification.notificationService');
        
        if ($user2Id == null) {
            throw $this->createNotFoundException("L'identifiant de l'utilisateur a mal été transmis.");
        }
        
        //On récupère l'utilisateur connecté
        $user1 = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if (!is_object($user1)) {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }
        
        //On récupère l'user2 (passé en paramètres)
        $user2 = $em->getRepository('KsUserBundle:User')->find($user2Id);
        
        if (!$user2) {
            throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $user2Id . '.');
        }
        //var_dump($user1);
        //$user_has_friends = $em->getRepository('KsUserBundle:UserHasFriends')->find(array("user" => $user1, "friend" => $user2));
        
        // On récupère le repository
        $repository = $this->getDoctrine()
                           ->getEntityManager()
                           ->getRepository('KsUserBundle:User');
        
        //Si les utilisateurs sont bien déjà amis'utilisateur 2 doit confirmer la demande d'ami
        if ($repository->mustGiveRequestFriendResponse($user1, $user2)) {
            
            $friendRequest = $em->getRepository('KsUserBundle:UserHasFriends')->find(array(
                'user'                      => $user2->getId(),
                'friend'                    => $user1->getId(),
                'pending_friend_request'    => 1
            ));

            if ($friendRequest) {
                $friendRequest->setPendingFriendRequest(false);
                $em->persist($friendRequest);
                
                // On crée la relation d'amitié inverse
                $inverseFriendRequest = new UserHasFriends($user1, $user2);
                $inverseFriendRequest->setPendingFriendRequest(false);
                $em->persist($inverseFriendRequest);
                
                //Gain possibles de badges en ajoutant un ami
                $friendsTrohiesCode = array(
                    "friends_10",
                    "friends_20",
                    "friends_50",
                    "friends_100",
                );

                foreach ($friendsTrohiesCode as $friendTrohyCode) {
                    //$trophyService->beginOrContinueToWinTrophy($friendTrohyCode, $user1);   
                    //$trophyService->beginOrContinueToWinTrophy($friendTrohyCode, $user2);
                }
            }            

            // On l'enregistre notre objet $user_user dans la base de données.
            $em->persist($user1);
            $em->persist($user2);

            $em->flush();

            //On sette une flash (Message qui apparaitra au chargement de la prochaine page)
            
            $this->get('session')->setFlash('alert alert-success', 'user.flash.accept_a_friend.success');
            
            $notificationType_name = "ask_friend_request";
            $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName($notificationType_name);
            
            if (!$notificationType) {
                throw $this->createNotFoundException('Impossible de trouver le type de notification ' . $notificationType_name . '.');
            }   
            
            //On récupère l'ancienne notification pour signaler qu'une réponse a été fourni
            $oldNotification = $em->getRepository('KsNotificationBundle:Notification')->findOneBy(
                array(
                    'owner'         => $user1->getId(),
                    'fromUser'      => $user2->getId(),
                    'type'          => $notificationType->getId(),
                    'gotAnAnswer'   => 0
                )
            );
            
            if (!$oldNotification) {
                throw $this->createNotFoundException('Impossible de trouver l\'ancienne notification.');
            }
            
            $oldNotification->setGotAnAnswer(true);
            $em->persist($oldNotification);
            $em->flush();
            
            $notificationType_name = "friend_request";
            $notificationService->sendNotification(null, $user1, $user2, $notificationType_name, $user1 . " a accepté ta demande d'ami");
            //$em->getRepository('KsNotificationBundle:Notification')->createNotification($user2, $user1, $notificationType, $user1 . " a accepté ta demande d'ami");
        } else {
            
            //On sette une flash (Message qui apparaitra au chargement de la prochaine page)
            $this->get('session')->setFlash('alert alert-error', 'user.flash.accept_a_friend.error.are_not_awaitingFriends');
        }
        
        // On redirige vers la page du profil de cet utilisateur
        return $this->redirect($this->generateUrl('ks_user_public_profile', array(
            'username' => $user2->getUsername()
        )));
    }
    
        /**
     * @Route("/refuse_a_friend_request/{user2Id}", name = "ks_user_Refuse_a_friend_request", options={"expose"=true} )
     */
    public function refuse_a_friend_requestAction($user2Id = null)
    {
        //Services
        $notificationService   = $this->get('ks_notification.notificationService');
        
        if($user2Id == null) {
            throw $this->createNotFoundException("L'identifiant de l'utilisateur a mal été transmis.");
        }
        
        //On récupère l'utilisateur connecté
        $user1 = $this->container->get('security.context')->getToken()->getUser();

        // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
        if( ! is_object($user1) )
        {
            throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
        }
        
        //On récupère l'user2 (passé en paramètres)
        $em = $this->getDoctrine()->getEntityManager();
        $user2 = $em->getRepository('KsUserBundle:User')->find($user2Id);
        
        if (!$user2) {
            throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $user2Id . '.');
        }
        //var_dump($user1);
        //$user_has_friends = $em->getRepository('KsUserBundle:UserHasFriends')->find(array("user" => $user1, "friend" => $user2));
        
        // On récupère le repository
        $repository = $this->getDoctrine()
                           ->getEntityManager()
                           ->getRepository('KsUserBundle:User');
        
        //Si l'es utilisateurs sont bien déjà amis'utilisateur 2 doit confirmer la demande d'ami
        if($repository->mustGiveRequestFriendResponse($user1, $user2)) {
            //On a bien récupéré les 2 utilisateurs
            $removeSuccess = false;
            
            $uhf1 = $em->getRepository('KsUserBundle:UserHasFriends')->find(array("user" => $user1->getId(), "friend" => $user2->getId()));

            if ($uhf1) {
                $removeSuccess = $user1->removeUserHasFriends($uhf1);
                $em->persist($uhf1);
                $em->remove($uhf1);
            } else {
                //throw $this->createNotFoundException('Impossible de trouver la liaison d\'amitié de ' . $user1->getId() . " et ". $user2->getId() . '.');
            }
            
            //$removeSuccess = $user2->removeUserHasFriends($uhf1);
            $uhf2 = $em->getRepository('KsUserBundle:UserHasFriends')->find(array("user" => $user2->getId(), "friend" => $user1->getId()));

            if ($uhf2) {
                $removeSuccess = $user1->removeUserHasFriends($uhf2);
                $em->persist($uhf2);
                $em->remove($uhf2);
            } else {
                //throw $this->createNotFoundException('Impossible de trouver la liaison d\'amitié de ' . $user1->getId() . " et ". $user2->getId() . '.');
            }
            

            // On l'enregistre notre objet $user_user dans la base de données.
            $em->persist($user1);
            $em->persist($user2);

            $em->flush();

            //On sette une flash (Message qui apparaitra au chargement de la prochaine page)
            if ($removeSuccess) {
                $this->get('session')->setFlash('alert alert-success', 'user.flash.refuse_a_friend_request.success');
                
                $notificationType_name = "ask_friend_request";
                $notificationType = $em->getRepository('KsNotificationBundle:NotificationType')->findOneByName($notificationType_name);

                if (!$notificationType) {
                    throw $this->createNotFoundException('Impossible de trouver le type de notification ' . $notificationType_name . '.');
                }   

                //On récupère l'ancienne notification pour signaler qu'une réponse a été fourni
                $oldNotification = $em->getRepository('KsNotificationBundle:Notification')->findOneBy(
                    array(
                        'owner'         => $user1->getId(),
                        'fromUser'      => $user2->getId(),
                        'type'          => $notificationType->getId(),
                        'gotAnAnswer'   => 0
                    )
                );

                if (!$oldNotification) {
                    throw $this->createNotFoundException('Impossible de trouver l\'ancienne notification.');
                }

                $oldNotification->setGotAnAnswer(true);
                $em->persist($oldNotification);
                $em->flush();
            
                $notificationType_name = "friend_request";
                $notificationService->sendNotification(null, $user1, $user2, $notificationType_name, $user1 . " a refusé votre demande d'ami");
                //$em->getRepository('KsNotificationBundle:Notification')->createNotification($user2, $user1, $notificationType, $user1 . " a refusé votre d'ami");
            } else {
                $this->get('session')->setFlash('alert alert-error', 'user.flash.refuse_a_friend_request.error.remove_failed');
            } 
        } else {
            
            //On sette une flash (Message qui apparaitra au chargement de la prochaine page)
            $this->get('session')->setFlash('alert alert-error', 'user.flash.refuse_a_friend_request.error.are_not_awaitingFriends');
        }
        
        // On redirige vers la page du profil de cet utilisateur
        return $this->redirect($this->generateUrl('ks_user_public_profile', array(
            'username' => $user2->getUsername()
        )));
    }

    /**
     * @Route("/inviteFriends", name = "ksFriends_invite", options={"expose"=true} )
     */
    public function inviteFriends()
    {
        
        $em                     = $this->getDoctrine()->getEntityManager();
        $userRep                = $em->getRepository('KsUserBundle:User');
        $currentUser            = $this->container->get('security.context')->getToken()->getUser();
        $betainvitation         = $this->container->getParameter('betainvitation');
        
        //Récupération de la session
        $session = $this->get('session');
        $session->set('pageType', 'friends');
        $session->set('page', 'inviteFriends');
        
        //services
        $facebookService    = $this->get('ks_facebook');
        
        $admin                  = $this->get('security.context')->isGranted('ROLE_ADMIN');
        /*if($betainvitation=="true" && !$admin){
            $userRep->couldInvitOther($currentUser);
        }  */  

        $entity         = new Invitation();
        $request        = $this->getRequest();
        $form           = $this->createForm(new InvitationType(), $entity);
        
        $formHandler    = new InvitationHandler($form, $request, $this->getDoctrine()->getEntityManager(), $currentUser);
        $responseData   = $formHandler->process();

        if($responseData["validation"]){
            if (isset($responseData["errorMessageMail"]) && $responseData["errorMessageMail"]) {
                $this->get('session')->setFlash('alert alert-error', 'users.email_already_invited_or_user'); 
            } else {
                 $emailGuest = $form->getData()->getEmailGuest();
                 $invitationEmailBeta =  new \Ks\UserBundle\Entity\InvitationEmailBeta($emailGuest);
                 $invitationEmailBeta->setUserInviting($currentUser);
                 $invitationEmailBeta->setCouldInvit(false);
                 $invitationEmailBeta->setNomberInvitation(0);

                 $em->persist($invitationEmailBeta);
                 $em->flush();
                  //Envois du mail 
                 $emailInviting = $currentUser->getEmail();
                 
                  
                $host       = $this->container->getParameter('host');
                $pathWeb    = $this->container->getParameter('path_web');
                $mailer     = $this->container->get('mailer');

                $contentMail = $this->container
                    ->get('templating')
                    ->render(
                        'KsUserBundle:Friend:_invite_mail.html.twig',
                        array(
                            'user'          => $currentUser,
                            'host'          => $host,
                            'pathWeb'       => $pathWeb,
                            'salt' => $form->getData()->getSalt(),
                        ),
                        'text/html'
                    );

                $body = $this->container->get('templating')->render('KsUserBundle:User:template_mail.html.twig', 
                    array(
                        'host'      => $host,
                        'pathWeb'   => $pathWeb,
                        'content'   => $contentMail,
                        'user'      => is_object( $currentUser ) ? $currentUser : null
                    ), 
                'text/html');

                $message = \Swift_Message::newInstance()
                    ->setContentType('text/html')
                    ->setSubject($currentUser . " t'invite à le rejoindre sur Keepinsport")
                    ->setFrom("contact@keepinsport.com")
                    ->setTo($emailGuest)
                    ->setBody($body);
                $mailer->getTransport()->start();
                $mailer->send($message);
                $mailer->getTransport()->stop();
                  
              //on coche l'action correspondante dans la checklist
              $em->getRepository('KsUserBundle:ChecklistAction')->checkInviteFriends($currentUser->getId());

              $this->get('session')->setFlash('alert alert-success', 'users.send_invitation_ok');
              //return $this->redirect($this->generateUrl('_ks_index')); 
            } 
        } else {
            //$facebookService->facebook->getUser();
            //$fbFriends = $facebookService->facebook->api('/me');
            //var_dump($fbFriends);
        }
        return $this->render('KsUserBundle:Friend:invitations.html.twig', array(
            'entity' => $entity,
            'send_form'   => $form->createView()
        )); 
    }
    
    
    /**
     * @Route("/send_an_invit_to_friend_by_address_book", name = "send_an_invit_to_friend_by_address_book" )
     * @Template()
     */
    public function send_an_invit_to_address_bookAction()
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $userRep                = $em->getRepository('KsUserBundle:User');
        $currentUser            = $this->container->get('security.context')->getToken()->getUser();
        $betainvitation         = $this->container->getParameter('betainvitation');
        $admin                  = $this->get('security.context')->isGranted('ROLE_ADMIN');
        if($betainvitation=="true" && !$admin){
            $userRep->couldInvitOther($currentUser);
        }
        
        
        $request        = $this->getRequest();
        if ($request->query->has('error')) {
            // TODO: gérer le message flash ou faire une page de rendu
            return $this->redirect($this->generateUrl('_ks_index'));
        }
        $gmailApi           = $this->get('ks_user.googlecontact');
        $authCode           = $request->query->get('code');
        $redirectUri        = $this->generateUrl('send_an_invit_to_friend_by_address_book', array(), true);
        try {
            $accessToken    = $gmailApi->getAccessToken($authCode, $redirectUri);
        } catch (Exception $e) {
            // TODO: faire quelque chose de plus intelligent
            throw $e;
        }
        
        $xmlresponse = file_get_contents($gmailApi->_accessContactUrl.$accessToken);
        $xml = new \Symfony\Component\DependencyInjection\SimpleXMLElement($xmlresponse);
        $xml->registerXPathNamespace('gd', $gmailApi->_urlSchema);
        $adressBooks = $xml->xpath('//gd:email');

        //Trie du tableau en PHP car l'API ne le permet pas 
        $aAdress = array();
        foreach($adressBooks as $adressBook){
               $aAdress[] = $adressBook->attributes()->address;
        }
        

        sort($aAdress,SORT_STRING);
        $array_lowercase = array_map('strtolower', $aAdress);
        array_multisort($array_lowercase, SORT_ASC, SORT_STRING, $aAdress);
        
        $em = $this->getDoctrine()->getEntityManager();
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        $myEmail = $currentUser->getEmail();
        // On récupère le repository
        $userRepository = $this->getDoctrine()
                           ->getEntityManager()
                           ->getRepository('KsUserBundle:User');
        
        $aEmailRegistredUser = array();
        //Todo mettre nom de l'utilisateur et identifiant dans le tableau
        foreach($aAdress as $key => $adress){
            $user = $em->getRepository('KsUserBundle:User')->findOneByEmail($adress);
            
            if($user!=null && $user->getEmail()!=$myEmail){
                $aEmailRegistredUser[$key]["invitation"] = true;
                $aEmailRegistredUser[$key]["friendWithMe"] = $userRepository->areFriends($currentUser->getId(),$user->getId() ); 
                $aEmailRegistredUser[$key]["mustGiveRequestFriendResponse"] = $userRepository->mustGiveRequestFriendResponse($currentUser->getId(),$user->getId()  );
                $aEmailRegistredUser[$key]["isAwaitingRequestFriendResponse"] = $userRepository->isAwaitingRequestFriendResponse($currentUser->getId(), $user->getId()  );
                $aEmailRegistredUser[$key]["username"] = $user->getUsername();
                $aEmailRegistredUser[$key]["userid"] = $user->getid();
                $aEmailRegistredUser[$key]["name"] = $adress;
            }else{
                $aEmailToInvit[$key]["name"] = $adress;
                $aEmailToInvit[$key]["invitation"] = false;
                $aEmailToInvit[$key]["username"] = "";
                $aEmailToInvit[$key]["userid"] = 0;
            }
        }

        return $this->render('KsUserBundle:User:sendAnInvitToAddressBook.html.twig', array(
            'aEmailToInvit'       => $aEmailToInvit,
            'aEmailRegistredUser' => $aEmailRegistredUser,
        )); 
   
    }
    
    /**
     * @Route("/send_email_to_google_adress_book_chosen", name = "send_email_to_google_adress_book_chosen" )
     * @Template()
     */
     public function send_email_to_google_adress_book_chosenAction()
    {
        $em                     = $this->getDoctrine()->getEntityManager();
        $userRep                = $em->getRepository('KsUserBundle:User');
        $currentUser            = $this->container->get('security.context')->getToken()->getUser();
        $betainvitation         = $this->container->getParameter('betainvitation');
        $admin                  = $this->get('security.context')->isGranted('ROLE_ADMIN');
        $request                = $this->getRequest();
        $params                 = $request->request->all();
       
         //On récupère les emails qui ont été choisits
         $aEmails = array();
         foreach($params as $param){
            $aEmails[] = $param;
         }
         
        if($betainvitation=="true" && !$admin){
            $nbInvits = $userRep->couldInvitOther($currentUser);
            if(isset($nbInvits) && count($params) > 1  && count($aEmails) > $nbInvits){
                $this->get('session')->setFlash('alert alert-warning', 'users.dont_ve_got_enough_invit_%nomber%_left', array('%nomber%' => $nbInvits));
                return $this->redirect($this->generateUrl('_ks_index'));
            }
        }
         

         $currentUser = $this->container->get('security.context')->getToken()->getUser();
         //envois du mail 
         $emailInviting = $currentUser->getEmail();
         
         $body = "Vous avez reçu une invitation à rejoindre le site KeepInSport de : ";
         $subject = "Invitation KeepInSport";
         $host = $_SERVER["HTTP_HOST"];
         
         foreach($aEmails as $email){
            //on vérifie que l'email n'existe pas déjà en base coté invit ou compte user
            $invitationExist = $em->getRepository('KsUserBundle:Invitation')->findOneBy(
                array(
                    'email_guest'         => $email,
                )
            );
            $userExist = $em->getRepository('KsUserBundle:User')->findOneBy(
                    array(
                        'email'         => $email,
                    )
             );
            
            if($invitationExist==null && $userExist==null){
                
                $invitationEmailBeta =  new \Ks\UserBundle\Entity\InvitationEmailBeta($email);
                $em->persist($invitationEmailBeta);
                $invitationEmailBeta->setUserInviting($currentUser);
                $invitationEmailBeta->setCouldInvit(false);
                $invitationEmailBeta->setNomberInvitation(0);
                $em->flush();
                
                 //on met a jour la table des invitation
                $invitation = new Invitation();
                $invitation->setPendingFriendRequest(1);
                $invitation->setUserInviting($currentUser);
                $salt = uniqid();
                $invitation->setSalt($salt);
                $invitation->setEmailGuest($email);
                $em->persist($invitation);
                $em->flush();
                $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom('contact@keepinsport.com') // $emailInviting
                    ->setTo($email)
                    ->setBody(
                        $this->renderView(
                            'KsUserBundle:User:emailInvit.txt.twig',
                            array(
                                'emailGuest' => $email, 
                                'user' => $currentUser,
                                'salt' => $salt,
                                'body' => $body,
                                'host' => $host
                            )
                        ), 'text/html'
                    );
                $this->get('mailer')->getTransport()->start();
                $this->get('mailer')->send($message);
                $this->get('mailer')->getTransport()->stop();
            }
            
             
             
           
         }
         
        //$this->get('mailer')->send($message);

        $this->get('session')->setFlash('alert alert-success', 'users.send_invitations_ok');
        return $this->redirect($this->generateUrl('_ks_index'));
    }
    
    /**
     * @Route("/{userId}", name = "ks_user_friendsList" )
     */
    public function friendsListAction($userId)
    {
        $em                 = $this->getDoctrine()->getEntityManager();  
        $userRep            = $em->getRepository('KsUserBundle:User');
        
        //On récupère l'utilisateur connecté
        $user = $userRep->find($userId);

        if( ! is_object($user) )
        {
            throw $this->createNotFoundException('Impossible de trouver l\'utilisateur ' . $userId );
        }
       
        return $this->render('KsUserBundle:Friend:friendsList.html.twig', array(
            'friends' => $userRep->getFriendList($user->getId())
        ));
    }
    
    
      /**
     * @Route("/invitWithGoogle", name = "invitWithGoogle" )
     * @Template()
     */
     public function invitWithGoogleAction()
    {
        //TODO Enregistrement du jeton Google Contact en BDD
        /*$em                 = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();   
        $service = $em->getRepository('KsUserBundle:Service')->findOneByName("Google-Agenda");
        $idService = $service->getId();
        $userHasService = $em->getRepository('KsUserBundle:UserHasServices')->findOneBy(array("service"=>$idService,"user"=>$user->getId()));
        $accessToken = $userHasService->getToken();
        if($accessToken!=null){
            $route = 'send_an_invit_to_friend_by_address_book';
            $url = $this->container->get('router')->generate($route);
            $urlGoogleContact = $url."?code=".$accessToken;
            
            var_dump('la');
        }else{*/
            $redirectUri = $this->container->get('router')->generate('send_an_invit_to_friend_by_address_book', array(), true);
            //Si pas trouvé requet vers l'api pour le recup 
            $urlGoogleContact = 'https://accounts.google.com/o/oauth2/auth?client_id=221396145208.apps.googleusercontent.com&redirect_uri='.$redirectUri.'&scope=https://www.google.com/m8/feeds/&response_type=code';
        //}
        
        return $this->render('KsUserBundle:Friend:invitWithGoogle.html.twig', array(
            'urlGoogleContact'   => $urlGoogleContact,
        ));
         
        
    }
    
    

}
?>
