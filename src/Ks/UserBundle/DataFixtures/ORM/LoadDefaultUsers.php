<?php

namespace Ks\UserBundle\DataFixtures\ORM;


use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Encoder;


use Ks\UserBundle\Entity\User;
use Ks\UserBundle\Entity\UserDetail;
use Ks\LeagueBundle\Entity\LeagueLevel;
use Ks\UserBundle\Entity\Sexe;
use Ks\TrophyBundle\Entity\Showcase;
use Ks\AgendaBundle\Entity\Agenda;



class LoadDefaultUsers extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        //Création de l'utilisateur keepinsport
        
        $leagueLevelRep = $manager->getRepository("KsLeagueBundle:LeagueLevel");
        $leagueNothing  = $leagueLevelRep->findOneBy(array("label"=>"nothing"));
        
        $sexeRep        = $manager->getRepository("KsUserBundle:Sexe");
        $sexeMale = $sexeRep->findOneBy(array("nom"=>"Masculin"));
        
        $userDetail = new UserDetail();
        $userDetail->setSexe($sexeMale);
        $userDetail->setCountryCode("FR");
        $userDetail->setFirstname("Keepinsport");
        $userDetail->setLastname("Keepinsport");
        $userDetail->setTown("Toulouse");
        
        $manager->persist($userDetail);
        $manager->flush();
        $user = new User();
        $user->setUserDetail($userDetail);
        $user->setUsername("keepinsport");
        $user->setUsernameCanonical("keepinsport");
        $user->setEmail("contact@keepinsport.com");
        $user->setEmailCanonical("contact@keepinsport.com");
        $user->setEnabled(1);
        $user->setSalt("bd1d0ffmz5s048swsskokwoo888ow00");
        //$encoder = new Encoder\MessageDigestPasswordEncoder('sha512', true, 10);
        //$password = $encoder->encodePassword('test', $user->getSalt());
        $password = "kcsJAWAaA/W/CW34acEZ9IyOA7PGA+mfX1Dhtga+08dvCZdneoyBwdfGVRww0OyI1BumBniA6nc8xT+/Hm9SSA==";
        $user->setPassword($password);
        //$roles = json_decode('a:1:{i:0;s:10:"ROLE_ADMIN";}');
        $user->addRole('ROLE_ADMIN');
        
        $user->setShowcase(new Showcase());
        
        $agenda = new Agenda();
        $agenda->setName("Agenda de Keepinsport");
        $agenda->setCreatedAt(new \DateTime('now'));
        $user->setAgenda($agenda);
        
        $user->setLeagueLevel($leagueNothing);
        $user->setCredentialsExpired(0);
        $manager->persist($user);
        $manager->flush(); 
    }
    
    
    public function getOrder()
    {
        return 100;
    }
}