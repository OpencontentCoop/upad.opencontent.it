<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version 2014.07.0
 */

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to perform a Content query
 *
 * @property $criterion Deprecated alias for $query
 */
class Query extends ValueObject
{
    const SORT_ASC = 'ascending';

    const SORT_DESC = 'descending';

    /**
     * The Query filter
     *
     * For the storage backend that supports it (Solr) filters the result set
     * without influencing score. It also offers better performance as filter
     * part of the Query can be cached.
     *
     * In case when the backend does not distinguish between query and filter
     * (Legacy Storage implementation), it will simply be combined with Query query
     * using LogicalAnd criterion.
     *
     * Can contain multiple criterion, as items of a logical one (by default
     * AND)
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public $filter;

    /**
     * The Query query
     *
     * For the storage backend that supports it (Solr Storage) query will influence
     * score of the search results.
     *
     * Can contain multiple criterion, as items of a logical one (by default
     * AND). Defaults to MatchAll.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public $query;

    /**
     * Query sorting clauses
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    public $sortClauses = array();

    /**
     * An array of facet builders
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[]
     */
    public $facetBuilders = array();

    /**
     * Query offset
     *
     * @var int
     */
    public $offset = 0;

    /**
     * Query limit
     *
     * @var int
     */
    public $limit;

    /**
     * If true spellcheck suggestions are returned
     *
     * @var boolean
     */
    public $spellcheck;

    /**
     * Wrapper for deprecated $criterion property
     *
     * @param string $property
     * @return mixed
     */
    public function __get( $property)
    {
        if ( $property === 'criterion' )
        {
            return $this->query;
        }

        return parent::__get( $property );
    }

    /**
     * Wrapper for deprecated $criterion property
     *
     * @param string $property
     * @param mixed $value
     * @return void
     */
    public function __set( $property, $value )
    {
        if ( $property === 'criterion' )
        {
            $this->query = $value;
            return;
        }

        return parent::__set( $property, $value );
    }

    /**
     * Wrapper for deprecated $criterion property
     *
     * @param string $property
     * @return bool
     */
    public function __isset( $property )
    {
        if ( $property === 'criterion' )
        {
            return true;
        }

        return parent::__isset( $property );
    }
}
