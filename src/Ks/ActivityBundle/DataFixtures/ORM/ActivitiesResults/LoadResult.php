<?php

namespace Ks\ActivityBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

class LoadResult implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $result = new \Ks\ActivityBundle\Entity\Result();
        //$result->setLabel('Victory');
        $result->setLabel('Victoire');
        $result->setCode('v');
        $manager->persist($result);
        
        $result = new \Ks\ActivityBundle\Entity\Result();
        //$result->setLabel('Draw');
        $result->setLabel('Match nul');
        $result->setCode('n');
        $manager->persist($result);
        
        $result = new \Ks\ActivityBundle\Entity\Result();
        //$result->setLabel('Defeat');
        $result->setLabel('Défaite');
        $result->setCode('d');
        $manager->persist($result);
   
        $manager->flush();
    }
}

