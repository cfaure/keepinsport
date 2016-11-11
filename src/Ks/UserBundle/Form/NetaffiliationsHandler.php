<?php 
namespace Ks\UserBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

class NetaffiliationsHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $previousNetaffiliations;

    public function __construct(Form $form, Request $request, EntityManager $em, $previousNetaffiliations)
    {
        $this->form         = $form;
        $this->request      = $request;
        $this->em           = $em;
        $this->previousNetaffiliations= $previousNetaffiliations;
    }

    public function process()
    {
        if( $this->request->getMethod() == 'POST' )
        {
            $user = $this->form->getData();
            $this->form->bindRequest( $this->request );
            $parameters       = $this->request->request->all();
            
            $newNetaffiliationsIds = $this->getObjIds( $user->getNetaffiliations() );
            $this->deleteNetaffiliations( $this->previousNetaffiliations, $newNetaffiliationsIds );
            

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
        $this->em->persist($user);
        $this->em->flush();
        
        $responseDatas = array(
            'response' => 1,
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
    
    private function deleteNetaffiliations( $oldNetaffiliations, $newNetaffiliationsIds )
    {
        if (empty( $oldNetaffiliations )) {
            return;
        }

        foreach ( $this->previousNetaffiliations as $net ) {
            if( !in_array( $net->getId(), $newNetaffiliationsIds )) {
                $this->em->remove( $net );
                $this->em->flush();
            }
        }
    }
    
    private function getObjIds( $objArray ) {
        $arrayReturn = array();
        foreach ( $objArray as $obj ) {
            if( is_object( $obj ))
                $arrayReturn[] = $obj->getId();
        }
        
        return $arrayReturn;
    }

    
}