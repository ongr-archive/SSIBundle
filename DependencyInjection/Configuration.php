<?php
namespace Crunch\Bundle\SSIBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('crunch_ssi');

        $rootNode
            ->children()
            ->booleanNode('use_header')->defaultFalse()->end()
            ->booleanNode('inline')->defaultFalse()->end();
        return $treeBuilder;
    }
}
