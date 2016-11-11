<?php

namespace Ks\ClubBundle\Twig\Extensions;


class KsClubExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'is_manager'                    => new \Twig_Filter_Method($this, 'userIsClubManager'),
            'isInClub'                      => new \Twig_Filter_Method($this, 'userIsInClub'),
            'askForMembershipIsInProgress'  => new \Twig_Filter_Method($this, 'userAskedForMembershipIsInProgress'),
        );
    }

    public function userIsClubManager($club, $userId)
    {
        
        foreach($club->getManagers() as $userManageClub) {
            if( $userManageClub->getUser()->getId() == $userId ) {
                return true;
            }
        }
        return false;
    }
    
    public function userIsInClub($clubId, \Ks\UserBundle\Entity\User $user)
    {   
        foreach( $user->getClubs() as $clubHasUsers ) {
            if( $clubHasUsers->getClub()->getId() == $clubId && $clubHasUsers->getMembershipAskInProgress() == false ) {
                return true;
            }
        }   

        /*foreach( $club->getUsers() as $clubHasUsers ) {
            if( $clubHasUsers->getUser()->getId() == $user->getId() && $clubHasUsers->getMembershipAskInProgress() == false ) {
                return true;
            }
        }*/
        return false;
    }
    
    public function userAskedForMembershipIsInProgress($clubId, \Ks\UserBundle\Entity\User $user)
    {
        foreach( $user->getClubs() as $clubHasUsers ) {
            if( $clubHasUsers->getClub()->getId() == $clubId && $clubHasUsers->getMembershipAskInProgress() == true ) {
                return true;
            }
        }
        /*foreach( $club->getUsers() as $clubHasUsers ) {
            if( $clubHasUsers->getUser()->getId() == $user->getId() && $clubHasUsers->getMembershipAskInProgress() == true ) {
                return true;
            }
        }*/
        return false;
    }
  

    public function getName()
    {
        return 'ks_club_extension';
    }
}
?>
