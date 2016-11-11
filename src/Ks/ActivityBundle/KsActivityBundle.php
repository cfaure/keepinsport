<?php

namespace Ks\ActivityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\DBAL\Types\Type;

class KsActivityBundle extends Bundle
{
    public function boot()
    {
        /*$typesMap = Type::getTypesMap();
        if (!isset($typesMap['longblob'])) {
            Type::addType('longblob', 'Ks\ActivityBundle\ORM\LongBlobType');
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('blob', 'longblob');
        }*/
    }
}
