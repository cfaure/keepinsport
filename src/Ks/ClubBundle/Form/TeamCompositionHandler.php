<?php 
namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

class TeamCompositionHandler
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
        
        if( $this->request->getMethod() == 'POST' ) {
            
            $teamComposition    = $this->form->getData();
            $request            = $this->request;
            $parameters         = $request->request->all();
            
            $this->form->bindRequest($this->request);

            if( $this->form->isValid() ) {
                $responseDatas = $this->onSuccess($teamComposition, $parameters); 
                
            } else {
                $errors = \Form\FormValidator::getErrorMessages($this->form);
                $responseDatas = $this->onError($errors);                  
            }
            
        } else {
            $responseDatas = $this->onIsNotPostRequest(); 
        }

        return $responseDatas;
    }
    
    public function onSuccess(\Ks\ClubBundle\Entity\TeamComposition $teamComposition)
    {
        $teamcompositionHasUsersRep = $this->em->getRepository('KsClubBundle:TeamCompositionHasUsers');
        
        $this->em->persist($teamComposition);
        $this->em->flush();
        
        $teamCompositionHasUsers = $teamcompositionHasUsersRep->findByTeamComposition( $teamComposition->getId() );

        foreach($teamCompositionHasUsers as $user){
            $this->em->remove( $user );
        }
        $this->em->flush();

        foreach($teamComposition->getUsers() as $user){
            $teamCompositionHasUsers = new \Ks\ClubBundle\Entity\TeamCompositionHasUsers( $teamComposition, $user );
            $this->em->persist( $teamCompositionHasUsers );  
        }
         $this->em->flush();
        
         $responseDatas = array(
            'response' => 1,
            'teamComposition' => $teamComposition
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