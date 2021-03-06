<?php
/**
 * File containing the AggregateFacetBuilderVisitorPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version 2014.07.0
 */

namespace eZ\Publish\Core\Base\Container\Compiler\Storage\Solr;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Solr Storage facet builder visitors.
 */
class AggregateFacetBuilderVisitorPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.persistence.solr.search.content.facet_builder_visitor.aggregate' ) )
        {
            return;
        }

        $aggregateFacetBuilderVisitorDefinition = $container->getDefinition(
            'ezpublish.persistence.solr.search.content.facet_builder_visitor.aggregate'
        );

        foreach ( $container->findTaggedServiceIds( 'ezpublish.persistence.solr.search.content.facet_builder_visitor' ) as $id => $attributes )
        {
            $aggregateFacetBuilderVisitorDefinition->addMethodCall(
                'addVisitor',
                array(
                    new Reference( $id ),
                )
            );
        }
    }
}
