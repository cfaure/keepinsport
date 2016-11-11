<?php

namespace Ks\ActivityBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Ks\ActivityBundle\Entity\SportType;

class LoadSportTypes extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        //Pour les sports collectifs
        $sportType_teamSport = new SportType();
        $sportType_teamSport->setLabel('team_sport');
        $sportType_teamSport->setHexadecimalColor('#6bdaff');
        $sportType_teamSport->setCode('TS');
        $manager->persist($sportType_teamSport);
        
        //Pour les sports d'endurence classiques
        $sportType_endurance = new SportType();
        $sportType_endurance->setLabel('endurance');
        $sportType_endurance->setHexadecimalColor('#acff7f');
        $sportType_endurance->setCode('EOE');
        $manager->persist($sportType_endurance);
        
        //Pour les sports d'endurence sous l'eau
        $sportType_endurance_under_water = new SportType();
        $sportType_endurance_under_water->setLabel('endurance_under_water');
        $sportType_endurance_under_water->setHexadecimalColor('#ffdb72');
        $sportType_endurance_under_water->setCode('EUW');
        $manager->persist($sportType_endurance_under_water);
        
        //Pour les autres
        $sportType_other = new SportType();
        $sportType_other->setLabel('other');
        $sportType_other->setHexadecimalColor('#EBEBEB');
        $sportType_other->setCode('OT');
        $manager->persist($sportType_other);
        
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 1;
    }
}



