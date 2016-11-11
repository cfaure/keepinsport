<?php

namespace Ks\LeagueBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ks\LeagueBundle\Entity\LeagueCategory;

class LoadLeagueCategories extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $xml = simplexml_load_file('src/Ks/LeagueBundle/DataFixtures/ORM/leagueCategories.xml');
        foreach ($xml->ks_leagueCategory as $curEntry) {
            $leagueCategory = new LeagueCategory();
            $leagueCategory->setLabel($curEntry);
        
            $manager->persist($leagueCategory);
        }
        
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 5;
    }
}