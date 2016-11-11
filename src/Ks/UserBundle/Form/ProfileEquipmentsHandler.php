<?php 
namespace Ks\UserBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

class ProfileEquipmentsHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $previousEquipments;

    public function __construct(Form $form, Request $request, EntityManager $em, $previousEquipments)
    {
        $this->form         = $form;
        $this->request      = $request;
        $this->em           = $em;
        $this->previousEquipments= $previousEquipments;
    }

    public function process()
    {
        if( $this->request->getMethod() == 'POST' )
        {
            $user = $this->form->getData();
            $this->form->bindRequest( $this->request );
            $parameters       = $this->request->request->all();
            
            $newEquipmentsIds = $this->getObjIds( $user->getEquipments() );

            $this->deleteEquipments( $this->previousEquipments, $newEquipmentsIds );
            

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
    
    private function deleteEquipments( $oldEquipments, $newEquipmentsIds )
    {
        if (empty( $oldEquipments )) {
            return;
        }

        foreach ( $this->previousEquipments as $equ ) {
            if( !in_array( $equ->getId(), $newEquipmentsIds )) {
                $this->em->remove( $equ );
                $this->em->flush();
            }
        }
    }
    
    private function getObjIds( $array ) {
        $arrayReturn = array();
        foreach ( $array as $obj ) {
            $arrayReturn[] = $obj->getId();
        }
        
        return $arrayReturn;
    }

    
}