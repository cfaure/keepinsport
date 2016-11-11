<?php

namespace Ks\PaymentBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ks_payment');
        $rootNode
            ->children()
                ->scalarNode('identifiant')->end()
                ->scalarNode('rang')->end()
                ->scalarNode('site')->end()
                ->scalarNode('hmac')->end()
                ->scalarNode('server')->end()
                ->scalarNode('repondre_a')->end()
            ->end();

        return $treeBuilder;
    }
}
