<?php

namespace Ks\UserBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Ks\UserBundle\Entity\CountryCode;

class LoadCountryCode implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $xml = simplexml_load_file('src/Ks/UserBundle/DataFixtures/ORM/iso_3166-1_list_fr.xml');
        foreach ($xml->ks_country_code as $curEntry) {
            $countryCode = new \Ks\UserBundle\Entity\CountryCode();
            $countryCode->setCode($curEntry->code);
            $countryCode->setName($curEntry->name);
            $manager->persist($countryCode);
        }
        
        $manager->flush();
    }
}