<?php
namespace Ks\ActivityBundle\ORM;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class LongBlobType extends Type
{
    const BLOB = 'blob';

    public function getName()
    {
        return self::BLOB;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getDoctrineTypeMapping('blob');
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return ($value === null) ? null : base64_encode($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return ($value === null) ? null : base64_decode($value);
    }
}