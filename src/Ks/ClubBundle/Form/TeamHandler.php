<?php 
namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Ks\ClubBundle\Entity\Team;

use Ks\ClubBundle\Entity\TeamHasUsers;

class TeamHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $idClub;

    public function __construct(Form $form, Request $request = null, EntityManager $em, $club)
    {
        $this->form         = $form;
        $this->request      = $request;
        $this->em           = $em;
        $this->club         = $club;
    }

    public function process()
    {
        
        if( $this->request->getMethod() == 'POST' )
        {
            $this->form->bindRequest($this->request);

            if( $this->form->isValid() )
            {
                $team = $this->form->getData();
                $this->onSuccess( $team );

                return true;
            }
        }

        return false;
    }
    
    public function onSuccess( $team)
    {
        
        $this->em->persist($team); 
        $this->em->flush();
        
        $teamHasUsers = $this->em->getRepository('KsClubBundle:TeamHasUsers')->findByTeam( $team->getId() );

        foreach($teamHasUsers as $user){
            $this->em->remove( $user );
        }
        $this->em->flush();

        foreach($team->getUsers() as $user){
            $teamHasUsers = new TeamHasUsers( $team, $user );
            $this->em->persist($teamHasUsers);  
        }
        
        $this->em->flush();
        
    }    
}