<?php 
namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Ks\ClubBundle\Entity\Club;

use Ks\ClubBundle\Entity\UserManageClub;

class ClubHasUsersHandler
{
    protected $form;
    protected $request;
    protected $em;

    public function __construct(Form $form, Request $request, EntityManager $em)
    {
        $this->form         = $form;
        $this->request      = $request;
        $this->em           = $em;
    }

    public function process()
    {
        
        if( $this->request->isXmlHttpRequest() ) {
            
            $clubHasUsers   = $this->form->getData();
            $request          = $this->request;
            $parameters       = $request->request->all();
            
            $this->form->bindRequest($this->request);

            if( $this->form->isValid() ) {
                
                $clubHasUsersSearch = $this->em->getRepository('KsClubBundle:ClubHasUsers')->findOneBy(
                        array(
                            "user"=>$clubHasUsers->getUser()->getId(),
                            "club"=>$clubHasUsers->getClub()->getId()
                        )
                );
                
                //L'utilisateur est déjà dans le groupe
                if( is_object( $clubHasUsersSearch )){
                   $responseDatas = array(
                        'response'   => -1,
                        'errorMessage'      => "Cet utilisateur fait déjà parti du groupe",
                        'errors'            => array()
                    ); 
                } else {
                    $responseDatas = $this->onSuccess($clubHasUsers, $parameters); 
                }
                
            } else {
                
                $errors = \Form\FormValidator::getErrorMessages($this->form);
                $responseDatas = $this->onError($errors);  
                
            }
            
        } else {
            $responseDatas = $this->onIsNotXmlHttpRequest(); 
        }

        return $responseDatas;
    }
    
    public function onSuccess(\Ks\ClubBundle\Entity\ClubHasUsers $clubHasUsers)
    {
        $this->em->persist($clubHasUsers);
        $this->em->flush();
        
         $responseDatas = array(
            'response' => 1,
            'clubHasUsers' => $clubHasUsers
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
    
    public function onIsNotXmlHttpRequest()
    {
        $responseDatas = array(
            'response'   => -1,
            'errorMessage'      => "La requête n'est pas une requête ajax."
        ); 
        
        return $responseDatas;
    }    
}