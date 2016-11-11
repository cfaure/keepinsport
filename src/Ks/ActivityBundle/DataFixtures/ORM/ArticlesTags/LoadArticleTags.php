<?php

namespace Ks\ActivityBundle\DataFixtures\ORM\ArticlesTags;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ks\ActivityBundle\Entity\ArticleTag;

class LoadArticleTags extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $xml = simplexml_load_file('src/Ks/ActivityBundle/DataFixtures/ORM/ArticlesTags/articleTags.xml');
        foreach ($xml->ks_tag as $curEntry) {
            
            
            $isCategory = ( isset( $curEntry->isCategory ) && !empty( $curEntry->isCategory ) && $curEntry->isCategory == 1 ) ? true : false;
            
            $articleTag = new ArticleTag($curEntry->label, $isCategory);

        
            $manager->persist($articleTag);
        }
        
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 7;
    }
}