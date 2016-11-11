<?php

namespace Ks\ActivityBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Ks\UserBundle\Entity\Sexe;

class LoadStateOfHealth implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $stateOfHealth = new \Ks\ActivityBundle\Entity\StateOfHealth();
        $stateOfHealth->setName('Pas bien');
        $stateOfHealth->setCode('tired');
        $manager->persist($stateOfHealth);
        
        $stateOfHealth = new \Ks\ActivityBundle\Entity\StateOfHealth();
        $stateOfHealth->setName('Normal');
        $stateOfHealth->setCode('so_so');
        $manager->persist($stateOfHealth);
        
        $stateOfHealth = new \Ks\ActivityBundle\Entity\StateOfHealth();
        $stateOfHealth->setName('En forme');
        $stateOfHealth->setCode('great');
        $manager->persist($stateOfHealth);
   
        $manager->flush();
    }
}

