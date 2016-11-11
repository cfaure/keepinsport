<?php

namespace Ks\ActivityBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ks\ActivityBundle\Entity\Sport;

class LoadSport extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $sportTypeRep  = $manager->getRepository('KsActivityBundle:SportType');
        
        $xml = simplexml_load_file('src/Ks/ActivityBundle/DataFixtures/ORM/sports.xml');
        foreach ($xml->ks_sport as $curEntry) {
            $sport = new Sport();
            $sport->setLabel($curEntry->name);
            $sport->setSite($curEntry->site);
            $sport->setSportsGroundsEnabled(false);
            
            if ( isset($curEntry->typeCode) ) {
                $sportType = $sportTypeRep->findOneByCode($curEntry->typeCode);

                if (!is_object($sportType) ) {
                    throw new AccessDeniedException("Impossible de trouver le type de sport " . $curEntry->typeCode .".");
                } else {
                    $sport->setSportType($sportType);
                }
            }
            
            $sport->setCodeSport(str_replace (" " , "-" , strtolower($this->wd_remove_accents($curEntry->name)) ));
        
            $manager->persist($sport);
        }
        
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 2;
    }
    
   public function wd_remove_accents($str, $charset='utf-8')
   {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        return $str;
   }
    
    
    
}