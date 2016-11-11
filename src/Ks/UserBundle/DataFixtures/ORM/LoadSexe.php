<?php

namespace Ks\UserBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Ks\UserBundle\Entity\Sexe;

class LoadSexe implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        //Notifications liées aux demandes d'ami
        $sexe = new Sexe();
        $sexe->setNom('Masculin');
        $sexe->setCode('male');
        $manager->persist($sexe);
        
        $sexe = new Sexe();
        $sexe->setNom('Feminin');
        $sexe->setCode('female');
        $manager->persist($sexe);
   
        $manager->flush();
    }
}

