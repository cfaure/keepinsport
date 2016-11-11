<?php 
namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

class ProfileHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $container;
    protected $user;

    public function __construct(Form $form, Request $request, EntityManager $em, $container, $user)
    {
        $this->form         = $form;
        $this->request      = $request;
        $this->em           = $em;
        $this->container    = $container;
        $this->user         = $user;
    }

    public function process()
    {
        if( $this->request->getMethod() == 'POST' )
        {
            $user = $this->form->getData();
            $parameters       = $this->request->request->all();

            $this->form->bindRequest( $this->request );

            if( $this->form->isValid() ) {       
                $responseDatas = $this->onSuccess($user, $parameters);  
            } else {
                $errors = \Form\FormValidator::getErrorMessages($this->form);
                $responseDatas = $this->onError($errors);  
            }
        } else {
            $responseDatas = $this->onIsNotPostRequest(); 
        }

        return $responseDatas;
    }
    
    public function onSuccess(\Ks\ClubBundle\Entity\Club $club, $parameters)
    {   
        $clubHasUsersRep    = $this->em->getRepository('KsClubBundle:ClubHasUsers');
        $userManageClubRep  = $this->em->getRepository('KsClubBundle:UserManageClub');
        
        $this->em->persist($club);
        $this->em->flush();
        
        //Celui qui modifie le club doit être un manager
        $userManageClub = $userManageClubRep->findOneBy(
            array(
                "user"  => $this->user->getId(),
                "club"  => $club->getId()
            )
        );
        
        if( !is_object( $userManageClub )) {
            $userManageClub = new \Ks\ClubBundle\Entity\UserManageClub( $club, $this->user, true ); 
            $this->em->persist($userManageClub);
            $this->em->flush();
        }
        
        //Celui qui modifie le club doit être un membre
        $clubHasUser = $clubHasUsersRep->findOneBy(
            array(
                "user"  => $this->user->getId(),
                "club"  => $club->getId()
            )
        );
        
        if( !is_object( $clubHasUser) ) {
            $clubHasUser = new \Ks\ClubBundle\Entity\ClubHasUsers($club, $this->user); 
            $this->em->persist($clubHasUser);
            $this->em->flush();
        }
        
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
    
    public function onIsNotPostpRequest()
    {
        $responseDatas = array(
            'response'   => -1,
            'errorMessage'      => "La requête n'est pas une requête post."
        ); 
        
        return $responseDatas;
    }    
}