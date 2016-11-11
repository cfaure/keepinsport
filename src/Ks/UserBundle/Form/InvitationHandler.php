<?php 
namespace Ks\UserBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Ks\UserBundle\Entity\Invitation;

class InvitationHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $currentUser;

    public function __construct(Form $form, Request $request = null, EntityManager $em, $currentUser)
    {
        $this->form         = $form;
        $this->request      = $request;
        $this->em           = $em;
        $this->currentUser  = $currentUser;
        
    }

    public function process()
    {
        

        if( $this->request->getMethod() == 'POST' )
        {
            
            
            $this->form->bindRequest($this->request);
 
            if( $this->form->isValid() )
            {
                $invitation = $this->form->getData();
               
                $emailGuest = $invitation->getEmailGuest();

                if(!$this->checkEmail($emailGuest)){
                    $responseData["validation"] = true;
                    $responseData["errorMessageMail"] = true;
                    return $responseData;
                }

                //$invitation->setPendingFriendRequest(1);
                //$invitation->setUserInviting($this->currentUser);
                //$invitation->setSalt(uniqid());
                
       
                $this->onSuccess($invitation);
                $responseData["validation"] = true;
                return $responseData;
                //return true;
            }
        }
        $responseData["validation"] = false;
        return $responseData;
    }
    
    
    public function onSuccess(Invitation $invitation)
    {
        //$this->em->persist($invitation);
        //$this->em->flush();
    }
    
    public function checkEmail($emailGuest)
    {
         
        /*$invitationExist = $this->em->getRepository('KsUserBundle:Invitation')->findOneBy(
                array(
                    'email_guest'         => $emailGuest,
                )
            );
        
           
        $userExist = $this->em->getRepository('KsUserBundle:User')->findOneBy(
                array(
                    'email'         => $emailGuest,
                )
            );
        
        if($invitationExist!=null || $userExist !=null){
             return false;
        }*/
        
        return true;
 
    }

   
}