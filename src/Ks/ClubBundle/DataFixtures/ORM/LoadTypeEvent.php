<?php

namespace Ks\ClubBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use  Ks\EventBundle\Entity\TypeEvent;

class LoadTypeEvent implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        
        $typeEvent = new TypeEvent();
        $typeEvent->setNomType("event_training");
        $typeEvent->setColor("#B0CC99");
        
        $manager->persist($typeEvent);
        
        $typeEvent = new TypeEvent();
        $typeEvent->setNomType("event_competition");
        $typeEvent->setColor("#ABC8E2");
        $manager->persist($typeEvent);
        
        $typeEvent = new TypeEvent();
        $typeEvent->setNomType("event_meal");
        $typeEvent->setColor("#D0E09D");
        $manager->persist($typeEvent);
        
        $typeEvent = new TypeEvent();
        $typeEvent->setNomType("event_google");
        $typeEvent->setColor("#FFB6B8");
        $manager->persist($typeEvent);
        
        $manager->flush();
    }
}



