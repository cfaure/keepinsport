<?php
// src/Sdz/BlogBundle/Form/ArticleHandler.php

namespace Ks\UserBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Ks\UserBundle\Entity\Feedback;

class FeedbackHandler
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

    public function onSuccess(\Ks\UserBundle\Entity\Feedback $feedback)
    {
        $this->em->persist($feedback);
        $this->em->flush();
        

        $responseDatas = array(
            'postResponse' => 1,
            'feedback' => $feedback,
        ); 
        
        return $responseDatas;
    }
    
    public function onError($errors)
    {
        $responseDatas = array(
            'postResponse'   => -1,
            'errorMessage'      => "Le formulaire n'est pas valide.",
            'errors'            => $errors
        ); 
        
        return $responseDatas;
    }
    
    public function onIsNotXmlHttpRequest()
    {
        $responseDatas = array(
            'postResponse'   => -1,
            'errorMessage'      => "La requête n'est pas une requête ajax."
        ); 
        
        return $responseDatas;
    }
}
