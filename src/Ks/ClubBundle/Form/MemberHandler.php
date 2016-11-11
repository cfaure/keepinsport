<?php 
namespace Ks\ClubBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Ks\ClubBundle\Entity\Club;

use Ks\ClubBundle\Entity\ClubHasMembers;

class MemberHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $idClub;

    public function __construct(Form $form, Request $request = null, EntityManager $em, $idClub)
    {
        $this->form         = $form;
        $this->request      = $request;
        $this->em           = $em;
        $this->idClub       = $idClub;
    }

    public function process()
    {
        
        if( $this->request->getMethod() == 'POST' )
        {
            $this->form->bindRequest($this->request);

            if( $this->form->isValid() )
            {
                
                $member = $this->form->getData();
                $idMember = $member->getId();
                $club = $this->em->getRepository('KsClubBundle:Club')->find($this->idClub);
                $ClubHasMembers = $this->em->getRepository('KsClubBundle:ClubHasMembers')->findOneBy(array("member"=>$idMember,"club"=>$this->idClub ));
                if($ClubHasMembers==null){
                   $ClubHasMembers = new ClubHasMembers(); 
                }
               
                $ClubHasMembers->setMember($member);
                $ClubHasMembers->setClub($club);
                $this->onSuccess($member , $ClubHasMembers);
                
                return true;
            }
        }

        return false;
    }
    
    public function onSuccess(/*Ks\ClubBundle\Entity\Member*/ $member , $ClubHasMembers)
    {
   
        $this->em->persist($member);
        $this->em->flush();
        $this->em->persist($ClubHasMembers); 
        $this->em->flush();
    }

    
}