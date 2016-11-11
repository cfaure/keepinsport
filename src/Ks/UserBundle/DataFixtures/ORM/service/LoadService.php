<?php

namespace Ks\UserBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Ks\UserBundle\Entity\Service;

class LoadService implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        //Notifications liées aux demandes d'ami
        $service = new Service();
        $service->setName("Runkeeper");
        $manager->persist($service);
        
        //Notifications liées aux demandes d'ami
        $service = new Service();
        $service->setName("Google-Agenda");
        $manager->persist($service);
        
        //Nike+
        $service = new Service();
        $service->setName("NikePlus");
        $manager->persist($service);
     
        $manager->flush();
    }
}