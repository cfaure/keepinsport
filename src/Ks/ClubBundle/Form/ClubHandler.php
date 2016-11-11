<?php 
namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Ks\ClubBundle\Entity\Club;

use Ks\ClubBundle\Entity\UserManageClub;

class ClubHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $user;

    public function __construct(Form $form, Request $request, EntityManager $em, $user)
    {
        $this->form         = $form;
        $this->request      = $request;
        $this->em           = $em;
        $this->user         = $user;
    }

    public function process()
    {
        $club    = $this->form->getData();
        $parameters       = $this->request->request->all();
        
        if( $this->request->getMethod() == 'POST' )
        {
            $this->form->bindRequest($this->request);

            if( $this->form->isValid() )
            {
                $responseDatas = $this->onSuccess($club, $parameters); 
            } else {
                $errors = \Form\FormValidator::getErrorMessages($this->form);
                $responseDatas = $this->onError($errors);                  
            }
        } else {
            $responseDatas = $this->onIsNotPostRequest(); 
        }
        
        return $responseDatas;
    }
    
    public function onSuccess(Club $club, $parameters)
    {
        $userRep            = $this->em->getRepository('KsUserBundle:User');
        $clubHasUsersRep    = $this->em->getRepository('KsClubBundle:ClubHasUsers');
        $userManageClubRep  = $this->em->getRepository('KsClubBundle:UserManageClub');
        //var_dump($club->getAvatar());
        //exit(0);
        //$club->uploadAvatar();
        $this->em->persist($club);
        $this->em->flush();
        
        /*if( $club->getAvatar() != null ) {
            $club->resizeAvatar();
        }*/
        
        $clubHasUsersOld = $clubHasUsersRep->findByClub( $club->getId() );
        
        $userManageClub = $userManageClubRep->findOneBy(
            array(
                "user"  => $this->user->getId(),
                "club"  => $club->getId()
            )
        );
        
        if( !is_object( $userManageClub )) {
            $userManageClub = new UserManageClub( $club, $this->user, true ); 
            $this->em->persist($userManageClub);
        }

        $clubHasUser = $clubHasUsersRep->findOneBy(
            array(
                "user"  => $this->user->getId(),
                "club"  => $club->getId()
            )
        );
        
        if( !is_object( $clubHasUser) ) {
            $clubHasUser = new \Ks\ClubBundle\Entity\ClubHasUsers($club, $this->user); 
            $this->em->persist($clubHasUser);
        }
        
        //print_r($parameters);
        //if( isset( $parameters['users'] ) ) {
        
            $oldUsersId = array();
            foreach( $clubHasUsersOld as $clubHasUser ) {
                if( $clubHasUser->getUser()->getId() != $this->user->getId()) {
                    $oldUsersId[] = $clubHasUser->getUser()->getId();
                }
            }
            $newUsersId = array();
            foreach( $club->getUsers() as $user ) {
                $newUsersId[] = $user->getId();
            }
            
            foreach( $oldUsersId as $oldUserId ) {
                if (!in_array($oldUserId, $newUsersId)) {
                    $oldUser = $userRep->find( $oldUserId );
                    if(is_object( $oldUser )) {
                        //s'ils ne sont pas amis, l'utilisateur n'était pas dans la liste de choix donc il ne doit pas être supprimé
                        if($userRep->areFriends( $this->user, $oldUser ) ) {
                            $clubHasUser = $clubHasUsersRep->findOneBy(
                                array(
                                    "user"  => $oldUserId,
                                    "club"  => $club->getId()
                                )
                            );

                            if( is_object( $clubHasUser )) {
                                $user = $userRep->find( $oldUserId );
                                $this->em->remove($clubHasUser);
                                $this->em->flush();
                            }
                        }
                    }
                    
                }
            }
            
            foreach( $club->getUsers() as $user ) {
                //$this->em->persist($user);
                $clubHasUsersSearch = $clubHasUsersRep->findOneBy(
                    array(
                        "user"=> $user->getId(),
                        "club"=> $club->getId()
                    )
                );
                
                
                if( !is_object( $clubHasUsersSearch )){
                   //$user = $userRep->find( $userId );

                   if( is_object( $user )) {
                        $clubHasUsers = new \Ks\ClubBundle\Entity\ClubHasUsers($club, $user); 
                        $this->em->persist($clubHasUsers);
                    }
                }
            }
        //}
                
        $this->em->flush();
        
         $responseDatas = array(
            'response' => 1,
            'club' => $club
        ); 
        
        return $responseDatas;
    }
    
    public function onError($errors)
    {
        $responseDatas = array(
            'response'   => -1,
            'errorMessage'      => "Le formulaire n'est pas valide.",
            'errors'            => $errors
        ); 
        
        return $responseDatas;
    }
    
    public function onIsNotPostRequest()
    {
        $responseDatas = array(
            'response'   => -1,
            'errorMessage'      => "La requête n'est pas une requête post."
        ); 
        
        return $responseDatas;
    }    
    
}