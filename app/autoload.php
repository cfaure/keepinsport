<?php
require_once __DIR__.'/../vendor/symfony/src/Symfony/Component/ClassLoader/ApcUniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\ClassLoader\ApcUniversalClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = new UniversalClassLoader();
//$loader = new ApcUniversalClassLoader('apc.ks.');
$loader->registerNamespaces(array(
    'Symfony'          => array(__DIR__.'/../vendor/symfony/src', __DIR__.'/../vendor/bundles'),
    'Sensio'           => __DIR__.'/../vendor/bundles',
    'JMS'              => __DIR__.'/../vendor/bundles',
    'Doctrine\\Common\\DataFixtures' => __DIR__.'/../vendor/doctrine-fixtures/lib',
    'Doctrine\\Common' => __DIR__.'/../vendor/doctrine-common/lib',
    'Doctrine\\DBAL'   => __DIR__.'/../vendor/doctrine-dbal/lib',
    'Doctrine'         => __DIR__.'/../vendor/doctrine/lib',
    'Monolog'          => __DIR__.'/../vendor/monolog/src',
    'Assetic'          => __DIR__.'/../vendor/assetic/src',
    'Metadata'         => __DIR__.'/../vendor/metadata/src',
    'FOS'              => __DIR__.'/../vendor/bundles',
    'Imagine'          => __DIR__.'/../vendor/imagine/lib',
    'Avalanche'        => __DIR__.'/../vendor/bundles',
    'Buzz'             => __DIR__.'/../vendor/buzz/lib',
    'Geocoder'         => __DIR__.'/../vendor/geocoder/src',
    'Ivory'            => __DIR__.'/../vendor/bundles',
    'Vich'             => __DIR__.'/../vendor/bundles',
    'WhiteOctober'     => __DIR__.'/../vendor/bundles',
    'Bundle'           => __DIR__.'/../vendor/bundles'
    //'Lexik'            => __DIR__.'/../vendor/bundles',
));

$loader->registerPrefixes(array(
    'Twig_Extensions_' => __DIR__.'/../vendor/twig-extensions/lib',
    'Twig_'            => __DIR__.'/../vendor/twig/lib',
    //'Zend_'             => __DIR__ . '/vendor',
    'Zend_'            => __DIR__.'/../vendorZend/Zend/library',
));

// intl
if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/../vendor/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';
    
    $loader->register();
    
    $loader->registerPrefixFallbacks(array(__DIR__.'/../vendor/symfony/src/Symfony/Component/Locale/Resources/stubs'));
    
    set_include_path(__DIR__.'/../vendorZend/Zend/library'.PATH_SEPARATOR.get_include_path());
}

$loader->registerNamespaceFallbacks(array(
    __DIR__.'/../src',
));
$loader->register();


AnnotationRegistry::registerLoader(function($class) use ($loader) {
    $loader->loadClass($class);
    return class_exists($class, false);
});
AnnotationRegistry::registerFile(__DIR__.'/../vendor/doctrine/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');

// Swiftmailer needs a special autoloader to allow
// the lazy loading of the init file (which is expensive)
require_once __DIR__.'/../vendor/swiftmailer/lib/classes/Swift.php';
Swift::registerAutoload(__DIR__.'/../vendor/swiftmailer/lib/swift_init.php');

