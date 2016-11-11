<?php

namespace Ks\LeagueBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ks\LeagueBundle\Entity\LeagueLevel;

class LoadLeagueLevels extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {     
        $leagueCategoryRep  = $manager->getRepository('KsLeagueBundle:LeagueCategory');
        
        $xml = simplexml_load_file('src/Ks/LeagueBundle/DataFixtures/ORM/leagueLevels.xml');
        foreach ($xml->ks_league_level as $curEntry) {
            $leagueLevel = new LeagueLevel();
            
            $label = ( isset( $curEntry->label ) && !empty( $curEntry->label ) ) ? $curEntry->label : $curEntry->category . " " . $curEntry->starNumber;
            $leagueLevel->setLabel($label);
            $leagueLevel->setRank($curEntry->rank);
            $leagueLevel->setStarNumber($curEntry->starNumber);
            
            $leagueCategory = $leagueCategoryRep->findOneByLabel($curEntry->category);

            if (!is_object($leagueCategory) ) {
                throw new AccessDeniedException("Impossible de trouver la catégorie de lique " . $curEntry->category .".");
            } else {
                $leagueLevel->setCategory($leagueCategory);
            }
        
            $manager->persist($leagueLevel);
        }
        
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 6;
    }
}