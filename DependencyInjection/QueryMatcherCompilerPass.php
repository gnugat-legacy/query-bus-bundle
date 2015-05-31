<?php

/*
 * This file is part of the gnugat/query-bus-bundle package.
 *
 * (c) Loïc Chardonnet <loic.chardonnet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gnugat\QueryBusBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class QueryMatcherCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('gnugat_query_bus.query_bus')) {
            return;
        }
        $definition = $container->getDefinition('gnugat_query_bus.query_bus');
        $taggedServices = $container->findTaggedServiceIds('gnugat_query_bus.query_matcher');
        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall('add', array(new Reference($id)));
        }
    }
}
