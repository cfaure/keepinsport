<?php

namespace Ks\ActivityBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ks\TrophyBundle\Entity\TrophyCategory;

class LoadTrophiesCategories extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $xml = simplexml_load_file('src/Ks/ActivityBundle/DataFixtures/ORM/trophiesCategories.xml');
        foreach ($xml->ks_trophyCategory as $curEntry) {
            $trophyCategory = new TrophyCategory();
            $trophyCategory->setLabel($curEntry);
            $trophyCategory->setCode($curEntry);
        
            $manager->persist($trophyCategory);
        }
        
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 3;
    }
}