<?php 
namespace Ks\UserBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

class ProfileOthersHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $container;

    public function __construct(Form $form, Request $request, EntityManager $em, $container)
    {
        $this->form         = $form;
        $this->request      = $request;
        $this->em           = $em;
        $this->container    = $container;
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
    
    public function onSuccess(\Ks\UserBundle\Entity\User $user, $parameters)
    {
        

        $userManager = $this->container->get('fos_user.user_manager');
        $userManager->updateUser($user, true);
        
        $responseDatas = array(
            'response' => 1,
            'user' => $user
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
    
    
    /*public function onSuccessEdit(UserDetail $userDetail)
    {
        $this->em->persist($userDetail);
        $this->em->flush();
    }*/
}