<?php 
namespace Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;


class FormHandler
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
            
        $this->form->bindRequest($this->request);

        if ( $this->form->isValid() ) {
            $responseDatas = $this->onSuccess($this->form->getData());  
        } else {
            $errors = \Form\FormValidator::getErrorMessages($this->form);
            $responseDatas = $this->onError($errors);   
        }
        
        return $responseDatas;
    }
    
    public function onSuccess( $entity )
    {        
        $this->em->persist( $entity ); 
        $this->em->flush();
        
        $responseDatas = array(
            'code' => 1,
            'entity' => $entity,
        ); 
        
        return $responseDatas;
    }
    
    public function onError($errors)
    {
        $responseDatas = array(
            'code'   => -1,
            'errorMessage'      => "Le formulaire n'est pas valide.",
            'errors'            => $errors
        ); 
        
        return $responseDatas;
    }
}