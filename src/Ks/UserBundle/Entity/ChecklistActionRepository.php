<?php

namespace Ks\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * 
 */
class ChecklistActionRepository extends EntityRepository
{
    /**
     * check "inviteFriends"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkInviteFriends($userId)
    {
        return $this->checkAction($userId, "inviteFriends");
    }
    
    /**
     * check "createClub"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkCreateClub($userId)
    {
        return $this->checkAction($userId, "createClub");
    }
    
    /**
     * check "inviteFriendInClub"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkInviteFriendInClub($userId)
    {
        return $this->checkAction($userId, "inviteFriendInClub");
    }
    
    /**
     * check "publishSportActivity"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkPublishSportActivity($userId)
    {
        return $this->checkAction($userId, "publishSportActivity");
    }
    
    /**
     * check "publishStatusPhotoVideo"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkPublishStatusPhotoVideo($userId)
    {
        return $this->checkAction($userId, "publishStatusPhotoVideo");
    }
    
    /**
     * check "commentLikeShareActivity"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkCommentLikeShareActivity($userId)
    {
        return $this->checkAction($userId, "commentLikeShareActivity");
    }
    
    /**
     * check "subscribeEvent"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkSubscribeEvent($userId)
    {
        return $this->checkAction($userId, "subscribeEvent");
    }
    
    /**
     * check "participateArticle"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkParticipateArticle($userId)
    {
        return $this->checkAction($userId, "participateArticle");
    }
    
    /**
     * check "consultBlog"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkConsultBlog($userId)
    {
        return $this->checkAction($userId, "consultBlog");
    }
    
    /**
     * check "paramService"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkParamService($userId)
    {
        return $this->checkAction($userId, "paramService");
    }
    
    /**
     * check "expertMode"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkExpertMode($userId)
    {
        return $this->checkAction($userId, "expertMode");
    }
    
    /**
     * check "visitSeen"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkVisitSeen($userId)
    {
        return $this->checkAction($userId, "visitSeen");
    }
    
    /**
     * check "competitionsSeen"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkCompetitionsSeen($userId)
    {
        return $this->checkAction($userId, "competitionsSeen");
    }
    
    /**
     * check "activityDetailSeen"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkActivityDetailSeen($userId)
    {
        return $this->checkAction($userId, "activityDetailSeen");
    }
    
    /**
     * check "dashboardSeen"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkDashboardSeen($userId)
    {
        return $this->checkAction($userId, "dashboardSeen");
    }
    
    /**
     * check "createShop"
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkCreateShop($userId)
    {
        return $this->checkAction($userId, "createShop");
    }
    
    public function uncheckVisitSeen($userId)
    {
        return $this->uncheckAction($userId, "visitSeen");
    }
    
    public function uncheckAction($userId, $checklistActionCode)
    {
        $checklistActionRep     = $this->_em->getRepository('KsUserBundle:ChecklistAction');
        $userChecklistActionRep = $this->_em->getRepository('KsUserBundle:UserHasToDoChecklistAction');
        $userRep                = $this->_em->getRepository('KsUserBundle:User');
        
        $checklistAction = $checklistActionRep->findOneByCode($checklistActionCode);
        
        if( is_object($checklistAction) ) {
            $userHasToDoChecklistAction = $userChecklistActionRep->findOneBy( 
                array(
                    "user"              => $userId,
                    "checklistAction"   => $checklistAction->getId(),
                )
            );
            $this->_em->remove($userHasToDoChecklistAction);
            $this->_em->flush();
        }
    }
    
    /**
     * check une action dans la checklist
     *
     * @param type $userId
     * @return boolean 
     */
    public function checkAction($userId, $checklistActionCode)
    {
        $checklistActionRep     = $this->_em->getRepository('KsUserBundle:ChecklistAction');
        $userChecklistActionRep = $this->_em->getRepository('KsUserBundle:UserHasToDoChecklistAction');
        $userRep                = $this->_em->getRepository('KsUserBundle:User');
        
        $checklistAction = $checklistActionRep->findOneByCode($checklistActionCode);
        
        if( is_object($checklistAction) ) {
            $userHasToDoChecklistAction = $userChecklistActionRep->findOneBy( 
                array(
                    "user"              => $userId,
                    "checklistAction"   => $checklistAction->getId(),
                )
            );

            //L'action n'existe pas pour cet utilisateur. on la crée et on la coche
            if( !is_object( $userHasToDoChecklistAction ) ) {
                $user = $userRep->find( $userId );
                if( is_object( $user )) {
                    $userHasToDoChecklistAction = new \Ks\UserBundle\Entity\UserHasToDoChecklistAction();
                    $userHasToDoChecklistAction->setUser( $user );
                    $userHasToDoChecklistAction->setChecklistAction( $checklistAction ); 
                    $userHasToDoChecklistAction->setDate( new \DateTime() );
                    $this->_em->persist( $userHasToDoChecklistAction );
                    $this->_em->flush();
                }
            } else {
                //Si l'action n'est pas encore effectuée
                if( $userHasToDoChecklistAction->getDate() == null ) {
                    $userHasToDoChecklistAction->setDate( new \DateTime() );
                    $this->_em->persist( $userHasToDoChecklistAction );
                    $this->_em->flush();
                    
                    return true;
                } 
                //L'action est déjà effectuée, on ne fait rien
                else {
                    return false;
                }
            }  
        } else {
            return false;
        }
    }
}