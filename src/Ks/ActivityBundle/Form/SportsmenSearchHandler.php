<?php
namespace Ks\ActivityBundle\Form;
//namespace FormEntity;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Ks\ActivityBundle\Entity\SportsmanSearch;

class SportsmenSearchHandler
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
            
            $sportsmenSearch = $this->form->getData();
            $request          = $this->request;
            $parameters       = $request->request->all();
            
            
            $this->form->bindRequest($request);
            
            if ( $this->form->isValid() ) {
                $responseDatas = $this->onSuccess($sportsmenSearch, $parameters);  
            } else {
                $errors = \Form\FormValidator::getErrorMessages($this->form);
                $responseDatas = $this->onError($errors);  
            }
        } else {
            $responseDatas = $this->onIsNotXmlHttpRequest(); 
        }
//$responseDatas = $this->onSuccess($this->form->getData());  
         
        return $responseDatas;
    }

    public function onSuccess(\Ks\ActivityBundle\Entity\SportsmenSearch $sportsmenSearch, $parameters)
    {   
        $place = $sportsmenSearch->getPlace();
        if ( is_object( $place ) && ( $place->getFullAdress() == null || $place->getFullAdress() == "" ) ) {
            $this->em->remove($place);
            $sportsmenSearch->setPlace(null);
        }
        
        $this->em->persist($sportsmenSearch);
        $this->em->flush();
        

        $responseDatas = array(
            'response' => 1,
            'sportsmenSearch' => $sportsmenSearch
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
