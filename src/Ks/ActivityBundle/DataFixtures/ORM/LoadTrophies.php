<?php

namespace Ks\ActivityBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ks\TrophyBundle\Entity\Trophy;

class LoadTrophies extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {       
        $trophyCategoryRep  = $manager->getRepository('KsTrophyBundle:TrophyCategory');
        
        $xml = simplexml_load_file('src/Ks/ActivityBundle/DataFixtures/ORM/trophies.xml');
        foreach ($xml->ks_trophy as $curEntry) {
            $trophy = new Trophy();
            $trophy->setLabel($curEntry->label);
            $trophy->setPointsNumber($curEntry->points);
            $trophy->setCode($curEntry->code);
            $trophy->setMonth("-1");
            $trophy->setYear("-1");
            
            if( isset( $curEntry->inSeveralTimes ) && $curEntry->inSeveralTimes == 1 ) {
                $trophy->setInSeveralTimes( true );
                
                if( isset( $curEntry->timesToComplete ) ) {
                    $trophy->setTimesToComplete( $curEntry->timesToComplete );
                }
            } else {
                $trophy->setInSeveralTimes( false );
                $trophy->setTimesToComplete( 1 );
            }
            
            $trophyCategory = $trophyCategoryRep->findOneByLabel($curEntry->categorie);

            if (!is_object($trophyCategory) ) {
                throw new AccessDeniedException("Impossible de trouver la catégorie de trophés " . $curEntry->categorie .".");
            } else {
                $trophy->setCategory($trophyCategory);
            }
        
            $manager->persist($trophy);
        }
        
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 4;
    }
}