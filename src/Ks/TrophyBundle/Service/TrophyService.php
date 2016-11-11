<?php

namespace Ks\TrophyBundle\Service;

use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ks\ActivityBundle\Trophy;
use Ks\NotificationBundle\Service\NotificationService;

/**
 *
 * @author Ced
 */
class TrophyService
    extends \Twig_Extension
{

    protected $doctrine;
    protected $notificationService;
    
    /**
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine, NotificationService $notificationService)
    {
        $this->doctrine            = $doctrine;
        $this->notificationService = $notificationService;
    }
    
    public function getName() {
        return 'TrophyService';
    }
    
    public function beginOrContinueToWinTrophy($trophyCode, $user) {  
        $em                         = $this->doctrine->getEntityManager(); 
        $trophyRep                  = $em->getRepository('KsTrophyBundle:Trophy');
        $userWinTrophiesRep         = $em->getRepository('KsTrophyBundle:UserWinTrophies');

        //gain d'un bagde de première activité s'il ne l'a pas déjà
        $trophy = $trophyRep->findOneByCode($trophyCode); 

        if (is_object($trophy) ) {

            //Si le trophé n'est pas déjà débloqué
            if ( ! $trophyRep->trophyIsUnlocked( $trophy, $user ) ) {
                
                //Le déverouillage du trophée a commencé mais est encore en cours.
                if( $trophyRep->trophyIsBeingUnlocked( $trophy, $user ) ) {
                    
                    //Deuxième vérification (revient à faire le même test que précédemment
                    $userTrophy = $userWinTrophiesRep->findOneBy( array(
                        'trophy'        => $trophy->getId(),
                        'user'          => $user->getId(),
                        'unlockedAt'    => NULL
                    ));
                    
                    //Le trophé en cours de dévérouillage a été trouvé
                    if( is_object( $userTrophy ) ) {
                        $userWinTrophiesRep->addTimesToUserTrophy($userTrophy, 1 );
                    } else {
                        $userTrophy = $trophyRep->startUnlock( $trophy, $user );
                    }

                } else {
                    //On débute le déblocage du trophée
                    $userTrophy = $trophyRep->startUnlock( $trophy, $user );
                }
                
                $this->controlUnlockIsPossible( $userTrophy );
                //On fait gagner le bagde
                //$trophyRep->unlockNewTrophy($trophy, $user);    
            }
        } else {
            //throw $this->createNotFoundException('Impossible de trouver le badge de première connexion');
        }
    } 
    
    private function controlUnlockIsPossible( $userTrophy ) {
        
        $trophy = $userTrophy->getTrophy();
        
        //Le trophée est prêt à être débloqué
        if( $userTrophy->getTimesSinceBegin() >= $trophy->getTimesToComplete() ) {
            $this->completeUnlock( $userTrophy );
        }
    }
    
    private function completeUnlock( $userTrophy ) { 
        $em                         = $this->doctrine->getEntityManager(); 
        $notificationRep            = $em->getRepository('KsNotificationBundle:Notification');
        $notificationTypeRep        = $em->getRepository('KsNotificationBundle:NotificationType');
        $showcaseExposesTrophiesRep = $em->getRepository('KsTrophyBundle:ShowcaseExposesTrophies');
        $userWinTrophiesRep         = $em->getRepository('KsTrophyBundle:UserWinTrophies');
        $user                       = $userTrophy->getUser();
        $trophy                     = $userTrophy->getTrophy();
        
        //Deblocage du trophée
        $userWinTrophiesRep->completeUnlock( $userTrophy );
        
        //Création d'une notification
        $notificationType_name = "trophy";
        $this->notificationService->sendNotification(null, $user, $user, $notificationType_name, "Félicitations ! Tu viens de débloquer le badge : ".$trophy->getLabel());

        //Si la vitrine n'est pas pleine, on expose le trophé
        $showcase = $user->getShowcase();
        if (! $showcaseExposesTrophiesRep->isAlreadyInShowcase($showcase, $trophy) ) {
            if( ! $showcaseExposesTrophiesRep->isFull($showcase) ) {
                $showcaseExposesTrophiesRep->exposeTrophieInShowcase($showcase, $trophy);
            }
        }

    }


    
}
