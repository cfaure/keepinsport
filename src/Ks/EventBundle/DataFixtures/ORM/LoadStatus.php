<?php

namespace Ks\EventBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Ks\EventBundle\Entity\Status;

class LoadStatus implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        //Notifications liées aux demandes d'ami
        $status = new Status();
        $status->setName('en-attente');
        $manager->persist($status);
        
        $status = new Status();
        $status->setName('accepte');
        $manager->persist($status);
        
        $status = new Status();
        $status->setName('refuse');
        $manager->persist($status);
        
        $manager->flush();
    }
}

