<?php
namespace Ks\ActivityBundle\Form;
//namespace FormEntity;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Ks\ActivityBundle\Entity\ActivityStatus;

class UserModifiesArticleHandler
{
    protected $form;
    protected $request;
    protected $em;

    public function __construct(Form $form, Request $request, EntityManager $em)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->em      = $em;
    }

    public function process()
    {    
        if ( $this->request->isXmlHttpRequest()) {
            $this->form->bindRequest($this->request);
            
            if ( $this->form->isValid() ) {
                $responseDatas = $this->onSuccess($this->form->getData());  
            } else {
                $errors = \Form\FormValidator::getErrorMessages($this->form);
                $responseDatas = $this->onError($errors);  
            }
        } else {
            $responseDatas = $this->onIsNotXmlHttpRequest(); 
        }

        return $responseDatas;
    }

    public function onSuccess(\Ks\ActivityBundle\Entity\UserModifiesArticle $userModifiesArticle)
    { 
        $this->em->persist($userModifiesArticle);
        $this->em->flush();

        $responseDatas = array(
            'publishResponse' => 1,
            'userModifiesArticle' => $userModifiesArticle
        ); 
        
        return $responseDatas;
    }
    
    public function onError($errors)
    {
        $responseDatas = array(
            'publishResponse'   => -1,
            'errorMessage'      => "Le formulaire n'est pas valide.",
            'errors'            => $errors
        ); 
        
        return $responseDatas;
    }
    
    public function onIsNotXmlHttpRequest()
    {
        $responseDatas = array(
            'publishResponse'   => -1,
            'errorMessage'      => "La requête n'est pas une requête ajax."
        ); 
        
        return $responseDatas;
    }
}
