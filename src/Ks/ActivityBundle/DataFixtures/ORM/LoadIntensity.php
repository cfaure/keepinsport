<?php

namespace Ks\ActivityBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

class LoadIntensity implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $intensity = new \Ks\ActivityBundle\Entity\Intensity();
        $intensity->setCode('low');
        $manager->persist($intensity);
        
        $intensity = new \Ks\ActivityBundle\Entity\Intensity();
        $intensity->setCode('medium');
        $manager->persist($intensity);
        
        $intensity = new \Ks\ActivityBundle\Entity\Intensity();
        $intensity->setCode('high');
        $manager->persist($intensity);
   
        $manager->flush();
    }
}

