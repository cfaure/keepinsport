<?php

namespace Ks\ActivityBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Ks\UserBundle\Entity\Sexe;

class LoadWeather implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        //Notifications liées aux demandes d'ami
        $weather = new \Ks\ActivityBundle\Entity\Weather();
        $weather->setName('Pluvieux');
        $manager->persist($weather);
        
        $weather = new \Ks\ActivityBundle\Entity\Weather();
        $weather->setName('Tempéré');
        $manager->persist($weather);
        
        $weather = new \Ks\ActivityBundle\Entity\Weather();
        $weather->setName('Chaud');
        $manager->persist($weather);  
   
        $manager->flush();
    }
}


