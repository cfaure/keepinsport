<?php

namespace Ks\MessageBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

class MessageHandler
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
        if ( $this->request->getMethod() == 'POST' ) {
            
            $parameters       = $this->request->request->all();
            $this->form->bindRequest($this->request);
            
            if ( $this->form->isValid() ) {
                $responseDatas = $this->onSuccess($this->form->getData(), $parameters);
            } else {
                $errors = \Form\FormValidator::getErrorMessages($this->form);
                $responseDatas = $this->onError($errors);
            }
        } else {
            $responseDatas = $this->onIsNotXmlHttpRequest(); 
        }

        return $responseDatas;
    }

    public function onSuccess(\Ks\MessageBundle\Entity\Message $message, $parameters)
    {
        //Si ce message est le premier de la discution, on ajoute l'expéditeur aux participants de la conversation
        if( $message->getPreviousMessage() == null ) {
            $message->addUser( $message->getFromUser() );
        }
        $this->em->persist($message);
        $this->em->flush();
        

        $responseDatas = array(
            'response' => 1,
            'message' => $message,
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
