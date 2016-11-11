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

/**
 * ClubAdmin controller.
 *
 * 
 */
class ClubAdminController extends Controller
{   
    /**
     * Lists of actions possibles by club administrateurs.
     *
     * @Route("/{clubId}/actions", name="ksClubAdmin_actions")
     * @Template()
     */
    public function actionsAction( $clubId )
    { 
        $em                     = $this->getDoctrine()->getEntityManager();
        $clubRep                = $em->getRepository('KsClubBundle:Club');
        $userManageClubRep      = $em->getRepository('KsClubBundle:UserManageClub');
        $clubHasUsersRep        = $em->getRepository('KsClubBundle:ClubHasUsers');
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
        
        $nbMembershipAskInProgress = count($clubHasUsersRep->findBy(
            array(
                "club"                      => $club->getId(),
                "membershipAskInProgress"   => true
            )
        ));
     
        return array(
            "club"                      => $club,
            "nbMembershipAskInProgress" => $nbMembershipAskInProgress
        );
    }
    
     /**
     * 
     * @Route("/addClubUser/{clubId}", requirements={"clubId" = "\d+"}, name = "ksClubAdmin_addClubUser", options={"expose"=true} )
     * @param int $clubId 
     */
    public function addClubUserAction( $clubId )
    {
        $request        = $this->get('request');
        $em             = $this->getDoctrine()->getEntityManager();
        $clubRep        = $em->getRepository('KsClubBundle:Club');
        $user           = $this->get('security.context')->getToken()->getUser();
        
        $club = $clubRep->find( $clubId );
        
        if (!is_object($club) ) {
            $impossibleClubMsg = $this->get('translator')->trans('impossible-to-find-club-%clubId%', array('%clubId%' => $clubId));
            $this->createNotFoundException($impossibleClubMsg);
        }
        
        $clubHasUsers       = new \Ks\ClubBundle\Entity\ClubHasUsers( $club );
        
        $form = $this->createForm( new \Ks\ClubBundle\Form\ClubHasUsersType( $club, $user ), $clubHasUsers);
        
         // On crée le gestionnaire pour ce formulaire, avec les outils dont il a besoin
        $formHandler = new \Ks\ClubBundle\Form\ClubHasUsersHandler($form, $request, $em);

        $responseDatas = $formHandler->process();
        
        //Si l'utilisateur a bien été ajouté
        if($responseDatas['response'] == 1) {
        
            //On récupère le nouveau formulaire
            $clubHasUsersForm = $this->createForm(new \Ks\ClubBundle\Form\ClubHasUsersType( $club, $user ), $clubHasUsers)->createView();
            $responseDatas["clubHasUsersForm"] = $this->render('KsClubBundle:ClubAdmin:_clubHasUsersForm.html.twig', array(
                'clubHasUsersForm'      => $clubHasUsersForm,
                'club'                  => $club
            ))->getContent();
        }
        
        $response = new Response(json_encode($responseDatas));
        $response->headers->set('Content-Type', 'application/json');

        return $response;    
    }
    
    /**
     * 
     * @Route("/acceptAnAskForMembershipInProgress/{clubId}_{userId}", requirements={"clubId" = "\d+"}, name = "ksClubAdmin_acceptAnAskForMembershipInProgress", options={"expose"=true} )
     * @param int $clubId 
     */
    public function acceptAnAskForMembershipInProgressAction( $clubId, $userId )
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $clubHasUsersRep    = $em->getRepository('KsClubBundle:ClubHasUsers');
        $notifTypeRep       = $em->getRepository('KsNotificationBundle:NotificationType');
        $packRep            = $em->getRepository('KsUserBundle:Pack');

        //services
        $notificationService    = $this->get('ks_notification.notificationService');
        
        $club = $clubRep->find( $clubId );
        
        if (!is_object($club) ) {
            $impossibleClubMsg = $this->get('translator')->trans('impossible-to-find-club-%clubId%', array('%clubId%' => $clubId));
            $this->createNotFoundException($impossibleClubMsg);
        }
        
        $user       = $userRep->find( $userId );
        
        if (!is_object($user) ) {
            $impossibleUserMsg = $this->get('translator')->trans('impossible-to-find-user-%userId%', array('%userId%' => $userId));
            $this->createNotFoundException($impossibleUserMsg);
        }
        if ( $clubHasUsersRep->askForMembershipIsInProgress( $club, $user )  ) {
            $clubHasUsers = $clubHasUsersRep->findOneBy( array(
                "club" => $club->getId(),
                "user" => $user->getId()
            ));
            
            $clubHasUsersRep->acceptAnAskForMembershipInProgress( $clubHasUsers );
            $userHasPack = new \Ks\UserBundle\Entity\UserHasPack($packRep->find(1), $user);
            $em->persist( $userHasPack );
            $em->flush();

            if( $clubHasUsersRep->isInClub( $club, $user ) ) {
                $this->get('session')->setFlash('alert alert-success', "L'utilisateur a été ajouté au club");
                
                //On récupère le type de notification pour l'accpetation d'un utilisateur comme membre d'un club
                $notificationType_name = "club";
                $notificationType = $notifTypeRep->findOneByName($notificationType_name);

                if ($notificationType) {
                    $notificationService->sendClubNotification(
                        $club, 
                        $user,
                        $notificationType_name,
                        "Tu as été accepté en tant que membre du club ". $club->getName(),
                        null
                    );
                } else{
                    //throw $this->createNotFoundException('Impossible de trouver le type de notification ' . $notificationType_name . '.');
                }
            } else {
                $this->get('session')->setFlash('alert alert-danger', "Erreur lors de l'acceptation");
            }   
        } else {
            $this->get('session')->setFlash('alert alert-warning', "La demande n'a pas été trouvée ou a déjà été traitée !");
        }  
        //return $this->redirect($this->generateUrl('ksClub_public_profile', array("clubId" => $club->getID())));
        return $this->redirect($this->generateUrl('ksActivity_activitiesList'));
    }
    
    /**
     * 
     * @Route("/deleteClubUser/{clubId}_{userId}", requirements={"clubId" = "\d+"}, name = "ksClubAdmin_deleteClubUser", options={"expose"=true} )
     * @param int $clubId 
     */
    public function deleteClubUserAction( $clubId, $userId )
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $clubHasUsersRep    = $em->getRepository('KsClubBundle:ClubHasUsers');

        $club = $clubRep->find( $clubId );
        
        if (!is_object($club) ) {
            $impossibleClubMsg = $this->get('translator')->trans('impossible-to-find-club-%clubId%', array('%clubId%' => $clubId));
            $this->createNotFoundException($impossibleClubMsg);
        }
        
        $user       = $userRep->find( $userId );
        
        if (!is_object($user) ) {
            $impossibleUserMsg = $this->get('translator')->trans('impossible-to-find-user-%userId%', array('%userId%' => $userId));
            $this->createNotFoundException($impossibleUserMsg);
        }
        
        $clubHasUsers = $clubHasUsersRep->findOneBy( 
            array( 
                "club" => $club->getId(),
                "user" => $user->getId()    
            )
        );
                
        if ( is_object($clubHasUsers) ) {
            $clubHasUsersRep->deleteUserFromClub( $clubHasUsers );
            if( !$clubHasUsersRep->isInClub( $club, $user ) ) {
                $this->get('session')->setFlash('alert alert-success', "L'utilisateur a été supprimé du club");
            } else {
                $this->get('session')->setFlash('alert alert-success', "Erreur lors de la suppression");
            }   
        } else {
            $this->get('session')->setFlash('alert alert-success', "Le membre n'a pas été trouvé");
        }  
        
        return $this->redirect($this->generateUrl('KsClub_members', array("clubId" => $club->getId())));
    }
    
    /**
     * refuse un membre qui a postulé pour être dans le club
     * 
     * @Route("/refuseAnAskForMembershipInProgress/{clubId}_{userId}", requirements={"clubId" = "\d+"}, name = "ksClubAdmin_refuseAnAskForMembershipInProgress", options={"expose"=true} )
     * @param int $clubId 
     */
    public function refuseAnAskForMembershipInProgressAction( $clubId, $userId )
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $userRep            = $em->getRepository('KsUserBundle:User');
        $clubHasUsersRep    = $em->getRepository('KsClubBundle:ClubHasUsers');

        $club = $clubRep->find( $clubId );
        
        if (!is_object($club) ) {
            $impossibleClubMsg = $this->get('translator')->trans('impossible-to-find-club-%clubId%', array('%clubId%' => $clubId));
            $this->createNotFoundException($impossibleClubMsg);
        }
        
        $user       = $userRep->find( $userId );
        
        if (!is_object($user) ) {
            $impossibleUserMsg = $this->get('translator')->trans('impossible-to-find-user-%userId%', array('%userId%' => $userId));
            $this->createNotFoundException($impossibleUserMsg);
        }
        
        if ( $clubHasUsersRep->askForMembershipIsInProgress( $club, $user )  ) {
            $clubHasUsers = $clubHasUsersRep->findOneBy( array(
                "club" => $club->getId(),
                "user" => $user->getId()
            ));
            
            $clubHasUsersRep->refuseAnAskForMembershipInProgress( $clubHasUsers );

            if( !$clubHasUsersRep->isInClub( $club, $user ) && !$clubHasUsersRep->askForMembershipIsInProgress( $club, $user ) ) {
                $this->get('session')->setFlash('alert alert-success', "L'utilisateur a été refusé au club");
            } else {
                $this->get('session')->setFlash('alert alert-success', "Erreur lors du refus");
            }   
        } else {
            $this->get('session')->setFlash('alert alert-success', "La demande n'a pas été trouvée ou a déjà été trouvée !");
        }  
        
        //return $this->redirect($this->generateUrl('ksClub_public_profile', array("clubId" => $club->getID())));
        return $this->redirect($this->generateUrl('ksActivity_activitiesList'));
    }
    
    /**
     * Liste des demande d'adhésion en attente
     *
     * @Route("/{clubId}/askForMembershipInProgress", name="ksClubAdmin_askForMembershipInProgress")
     * @Template()
     */
    public function askForMembershipInProgressAction( $clubId )
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $clubHasUsersRep    = $em->getRepository('KsClubBundle:ClubHasUsers');

        $club = $clubRep->find( $clubId );
        
        if (! $club ) {
            throw $this->createNotFoundException('Unable to find Club entity.');
        }
        
        $clubHasUsers = $clubHasUsersRep->findBy( array(
            "club"                      => $club->getId(),
            "membershipAskInProgress"   => true
        ));

        return array(
            'club'          => $club,
            'clubHasUsers'  => $clubHasUsers
        );
    }
    
    /**
     * Ajouter un manager au club
     *
     * @Route("/{clubId}/addUserAsManager/{userId}", name="ksClubAdmin_addUserAsManager")
     * @Template()
     */
    public function addUserAsManagerAction( $clubId, $userId )
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $userManageClubRep  = $em->getRepository('KsClubBundle:UserManageClub');

        $club = $clubRep->find( $clubId );       
        $user = $userRep->find( $userId );
        
        if( is_object( $club ) && is_object( $user )) {
            $userManageClubRep->addUserAsManager( $club, $user );
            $this->get('session')->setFlash('alert alert-success', "L'utilisateur " . $user->getUsername() ." est maintenant manager du club");
        }

        return $this->redirect($this->generateUrl('KsClub_members', array("clubId" => $clubId)));
    }
    
    /**
     * Ajouter un manager au club
     *
     * @Route("/{clubId}/createTournament/{nbParticipants}", name="ksClubAdmin_createTournament", options={"expose"=true})
     * @Template()
     */
    public function createTournamentAction( $clubId, $nbParticipants )
    {
        $em                 = $this->getDoctrine()->getEntityManager();
        $userRep            = $em->getRepository('KsUserBundle:User');
        $clubRep            = $em->getRepository('KsClubBundle:Club');
        $tournamentRep      = $em->getRepository('KsTournamentBundle:Tournament');

        $club = $clubRep->find( $clubId );       
        
        if( is_object( $club ) ) {
            if(is_numeric( $nbParticipants ) && (int)$nbParticipants <= 32 ) {
            
                $request = $this->getRequest();

                $tournament = new \Ks\TournamentBundle\Entity\Tournament();
                $tournament->setClub( $club );

                $tournamentForm = $this->createForm(new \Ks\TournamentBundle\Form\TournamentType( ), $tournament );
                $tournamentForm->bindRequest( $request );

                $formHandler = new \Form\FormHandler( $tournamentForm, $request, $em );

                $responseDatas = $formHandler->process();
                if( $responseDatas['code'] == 1) {
                    $tournament = $responseDatas['entity'];

                    $knownBrackets = array(2,4,8,16,32);

                    $closest = null;
                    foreach( $knownBrackets as $knownBracket ) {
                        if( $knownBracket >= $nbParticipants ) {
                            $closest = $knownBracket;
                            break;
                        }
                    }

                    if( $closest != null ) {
                        $base = $closest;
                        $baseT 		= $base/2;
                        $baseC 		= $base/2;
                        $roundNumber 		= 1;

                        $round = null;
                        $matchNum = 1;
                        for($i=1; $i <= ($base-1); $i++ ) {
                            $baseR = $i/$baseT;

                            if( $round == null ) {
                                $round = new \Ks\TournamentBundle\Entity\Round( $tournament, $roundNumber ); 
                                $em->persist( $round );
                            }

                            $match = new \Ks\TournamentBundle\Entity\Match( $round );
                            $em->persist( $match );

                            //var_dump("round " . $roundNumber ." match " . $matchNum);
                            $matchNum++;
                            if($baseR>=1) { 
                                $roundNumber++;
                                $matchNum = 1;
                                $round = null; 

                                $baseC/= 2;
                                $baseT = $baseT + $baseC;
                                $baseR = $i/$baseT;
                            }
                        }
                    }

                    $em->flush();
                    
                    $tournamentRep->publishTournamentCreation( $tournament );
                    return $this->redirect($this->generateUrl('ksTournament_show', array("id" => $tournament->getId()))); 
                }
            } else {
                //Le nombre de participants reçu est incorrect
                $this->get('session')->setFlash('alert alert-error', "Le nombre de participants est incorrect.");
                return $this->redirect($this->generateUrl('KsClub_tournaments', array("clubId" => $clubId))); 
            }
        }

        return $this->redirect($this->generateUrl('KsClub_tournaments', array("clubId" => $clubId))); 
    }
    
    /**
     * Deletes a Team team.
     *
     * @Route("/{id}/delete", name="ksClubAdmin_delete")
     */
    public function deleteAction($id)
    {
        //$idClub = $this->container->get('request')->get("idClub");
        $em = $this->getDoctrine()->getEntityManager();
        $clubRep = $em->getRepository('KsClubBundle:Club');
        $userManageClubRep      = $em->getRepository('KsClubBundle:UserManageClub');
        
        $user = $this->container->get('security.context')->getToken()->getUser();

        $club = $clubRep->find($id);
        
        if( ! $userManageClubRep->userIsClubManager( $club, $user ) ) {
            $this->get('session')->setFlash('alert alert-error', "Vous n'êtes pas autorisé à supprimer ce club");
             return $this->redirect($this->generateUrl('ksClub_myClubs')); 
        }
        
        
        if (is_object($club)) {
            $em->remove($club);
            $em->flush();
            
            $this->get('session')->setFlash('alert alert-success', "Le club a été supprimé.");
        }

        return $this->redirect($this->generateUrl('ksClub_myClubs'));
    }
}
