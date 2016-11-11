<?php

namespace Ks\UserBundle\Security\User\Provider;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use \BaseFacebook;
use \FacebookApiException;
use Ks\TrophyBundle\Entity\Showcase;

class FacebookProvider implements UserProviderInterface
{
    /**
     * @var \Facebook
     */
    public $facebook;
    protected $userManager;
    protected $validator;
    protected $doctrine;
    protected $container;
    
    public function __construct(BaseFacebook $facebook, $userManager, $validator, $doctrine, $container)
    {
        $this->facebook     = $facebook;
        $this->userManager  = $userManager;
        $this->validator    = $validator;
        $this->doctrine     = $doctrine;
        $this->container    = $container;
        
    }

    public function supportsClass($class)
    {
        return $this->userManager->supportsClass($class);
    }

    public function findUserByFbId($fbId)
    {
        return $this->userManager->findUserBy(array('facebookId' => $fbId));
    }
    
    public function findUserByEmail($email)
    {
        return $this->userManager->findUserBy(array('email' => $email));
    }
    
    public function loadUserByUsername($username)
    {
        $em                     = $this->doctrine->getEntityManager();
        $userRep                = $em->getRepository('KsUserBundle:User'); 
        $checklistActionRep     = $em->getRepository('KsUserBundle:ChecklistAction');
        $userChecklistActionRep = $em->getRepository('KsUserBundle:UserHasToDoChecklistAction');
        
        //Services
        $notificationService    = $this->container->get('ks_notification.notificationService');
        $user                   = $this->findUserByFbId($username);
        
        if ($user) { // utilisateur déjà en bdd
            return $user;
        } else {
            $fbdata = $this->facebook->api('/me');
            $user = $this->userManager->createUser();
            
            // id + role
            if (isset($fbdata['id'])) {
                $user->setFacebookId($fbdata['id']);
                $user->addRole('ROLE_FACEBOOK');
                $user->addRole('ROLE_USER');
            }
            
            // mot de passe
            // $user->setPassword("no-password-fb-auth");
            $user->setEnabled(true);
            $user->setPassword(' ');
            
            // username
            if (isset($fbdata['username'])) {
                $username = $fbdata['username'];
            } else {
                throw new Exception('Username non trouvé dans fbdata: création impossible');
            }
            $user->setUsername($username);
            $user->setUsernameCanonical($username);

            // email
            if (isset($fbdata['email'])) {
                $user->setEmail($fbdata['email']);
                $user->setEmailCanonical($fbdata['email']);
            } else {
                $user->setEmail($username.'@facebook.com');
                $user->setEmailCanonical($username.'@facebook.com');
            }
            
            // sexe
            // NOTE CF: SERIOUSLY ?!!! une table pour gérer ça ?
            $sexeRepo = $em->getRepository('KsUserBundle:Sexe');
            if (isset($fbdata['gender']) && $fbdata['gender'] == "female") {
                $sexe = $sexeRepo->findOneByNom("Feminin");
            } else {
                $sexe = $sexeRepo->findOneByNom("Masculin");
            }
            
            // localisation
            // Si l'addresse de l'utilisateur existe on la traite pour obtenir 
            // la latitude/longitude pour la table userDetail
            $aGeoloc = array();
            if (isset($fbdata['location']) && isset($fbdata['location']['name'])) {
                $geocoder   = $this->container->get('ivory_google_map.geocoder');
                $response   = $geocoder->geocode($fbdata['location']['name']);
                $results    = $response->getResults();
                if (count($results) > 0){
                    $adress     = $results[0]->getAddressComponents();  
                    $aGeoloc    = array(
                        "town"          => isset($adress[0]) ? $adress[0]->getLongName() : '',
                        "country_area"  => isset($adress[2]) ? $adress[2]->getLongName() : '',
                        "latitude"      => $results[0]->getGeometry()->getLocation()->getLatitude(),
                        "longitude"     => $results[0]->getGeometry()->getLocation()->getLongitude()
                    );
                }
            }
            
            $em->persist($user);
            $em->flush();
                
            // vitrine associée a l'utilisateur 
            $showcase = new Showcase();
            $showcase->setLabel("Vitrine");
            $em->persist($showcase);
            $em->flush();
            $user->setShowcase($showcase);

            // agenda associé à l'utilisateur
            $agenda = new \Ks\AgendaBundle\Entity\Agenda();
            $agenda->setName("agenda-".$fbdata['username']);
            $agenda->setCreatedAt(new \DateTime('now'));
            $em->persist($agenda);
            $em->flush();
            $user->setAgenda($agenda);
            
            //On cherche l'utilisateur keepinsport et on l'ajoute en ami s'il existe
            $keepinsportUser = $userRep->findOneByUsername("keepinsport");
            
            if( is_object( $keepinsportUser ) ) {
                //$this->setFlash('ksuser', $keepinsportUser->getId());
                $userHasFriends = new \Ks\UserBundle\Entity\UserHasFriends( $keepinsportUser, $user);
                $userHasFriends->setPendingFriendRequest(false);
                $user->addUserHasFriends($userHasFriends);
                $keepinsportUser->addUserHasFriends($userHasFriends);
                $em->persist($user);
                $em->persist($userHasFriends);
                $em->flush();
            } else {
                //$this->setFlash('ksuser', 'utilisateur keepinsport non trouvé');
            }
            
            //On met à jour la configuration pour l'envoi de notifications par mail
            $notificationService->updateUserMailNotifications( $user );
            
            //On met à jour sa checklist
            $checklistActions = $checklistActionRep->findAll();
            foreach( $checklistActions as $checklistAction ) {

                $userHasToDoChecklistAction = $userChecklistActionRep->findOneBy( 
                    array(
                        "user" => $user->getId(),
                        "checklistAction" => $checklistAction->getId()
                    )
                );

                if( !is_object( $userHasToDoChecklistAction ) ) {
                    $userHasToDoChecklistAction = new \Ks\UserBundle\Entity\UserHasToDoChecklistAction();
                    $userHasToDoChecklistAction->setUser( $user );
                    $userHasToDoChecklistAction->setChecklistAction( $checklistAction );

                    $em->persist( $userHasToDoChecklistAction );

                    $em->flush();
                }
            }

            //A la création on le positionne dans la league nothing par défaut 
            $leagueLevelRepo = $em->getRepository('KsLeagueBundle:LeagueLevel');
            $user->setLeagueLevel(
                $leagueLevelRepo->findOneByLabel("nothing")
            );
                
            //Création des services associés 
            $services = $em->getRepository('KsUserBundle:Service')->findAll();
            foreach($services as $service) {
                $userHasService = new \Ks\UserBundle\Entity\UserHasServices();
                $userHasService->setIsActive(false);
                $userHasService->setSyncServiceToUser(false);
                $userHasService->setUserSyncToService(false);
                $userHasService->setFirstSync(true);
                $userHasService->setUser($user);
                $userHasService->setService($service);
                $em->persist($userHasService);
                $em->flush();
            }
            
            $em->flush();
           
            // TODO: use http://developers.facebook.com/docs/api/realtime
            $user->setFBData(
                $fbdata,
                $sexe,
                null,
                $aGeoloc
            );
            
            if (count($this->validator->validate($user, 'Facebook'))) {
                // TODO: the user was found obviously, but doesnt match our expectations, do something smart
                var_dump($user); exit;
                throw new UsernameNotFoundException('The facebook user could not be stored');
            }
            $this->userManager->updateUser($user);

            if (empty($user)) {
                throw new UsernameNotFoundException('The user is not authenticated on facebook');
            }
            
            //return $this->redirect($this->generateUrl('ksProfile_informations', array('creationOrEdition' => 'creation')));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user)) || !$user->getFacebookId()) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getFacebookId());
    }
}
