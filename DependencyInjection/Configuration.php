<?php
/*
 * @copyright  Copyright (C) 2017, 2018, 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com>
 * @see        https://github.com/PhilETaylor/mysites.guru
 * @license    MIT
 */

namespace Philetaylor\DoctrineEncryptBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration tree for security bundle. Full tree you can see in Resources/docs.
 *
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        //Create tree builder
        $treeBuilder = new TreeBuilder('phil_e_taylor_doctrine_encrypt');

        // Grammar of config tree
        $treeBuilder->getRootNode()
            ->children()
            ->arrayNode('keys')
            ->useAttributeAsKey('name')
            ->prototype('scalar')
            ->end()
            ->end()
            ->scalarNode('secret_key')
            ->end()
            ->scalarNode('encryptor')
            ->end()
            ->end();

        return $treeBuilder;
    }
}
