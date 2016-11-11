<?php 
namespace Ks\ActivityBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Ks\ActivityBundle\Entity\Gpx;

class GpxHandler
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
                $entity = $this->form->getData();

                $responseDatas = $this->onSuccess($entity);
            } else {
                $errors = \Form\FormValidator::getErrorMessages($this->form);
                $responseDatas = $this->onError($errors);  
            }
        } else {
            $responseDatas = $this->onIsNotPostRequest(); 
        }

        return $responseDatas;
    }
    
    public function onSuccess(Gpx $gpx)
    {
        $this->em->persist($gpx);
        $this->em->flush();
        
        $responseDatas = array(
            'downloadResponse' => 1,
            'gpx' => $gpx
        ); 
        
        return $responseDatas;
        
    }
    
    public function onError($errors)
    {
        $responseDatas = array(
            'downloadResponse'   => -1,
            'errorMessage'      => "Le formulaire n'est pas valide.",
            'errors'            => $errors
        ); 
        
        return $responseDatas;
    }
    
    public function onIsNotPostRequest()
    {
        $responseDatas = array(
            'downloadResponse'   => -1,
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