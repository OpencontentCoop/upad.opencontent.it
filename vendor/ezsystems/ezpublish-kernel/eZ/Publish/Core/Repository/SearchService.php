<?php
/**
 * File containing the eZ\Publish\Core\Repository\SearchService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version 2014.07.0
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location as LocationCriterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location as LocationSortClause;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\SPI\Persistence\Content\Search\Handler;
use eZ\Publish\SPI\Persistence\Content\Location\Search\Handler as LocationSearchHandler;

/**
 * Search service
 *
 * @package eZ\Publish\Core\Repository
 */
class SearchService implements SearchServiceInterface
{
    /**
     * 2^30, since PHP_INT_MAX can cause overflows in DB systems, if PHP is run
     * on 64 bit systems
     */
    const MAX_LIMIT = 1073741824;

    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    protected $searchHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Search\Handler
     */
    protected $locationSearchHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var \eZ\Publish\Core\Repository\DomainMapper
     */
    protected $domainMapper;

    /**
     * @var \eZ\Publish\Core\Repository\PermissionsCriterionHandler
     */
    protected $permissionsCriterionHandler;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Search\Handler $locationSearchHandler
     * @param \eZ\Publish\Core\Repository\DomainMapper $domainMapper
     * @param \eZ\Publish\Core\Repository\PermissionsCriterionHandler $permissionsCriterionHandler
     * @param array $settings
     */
    public function __construct(
        RepositoryInterface $repository,
        Handler $searchHandler,
        LocationSearchHandler $locationSearchHandler,
        DomainMapper $domainMapper,
        PermissionsCriterionHandler $permissionsCriterionHandler,
        array $settings = array()
    )
    {
        $this->repository = $repository;
        $this->searchHandler = $searchHandler;
        $this->locationSearchHandler = $locationSearchHandler;
        $this->domainMapper = $domainMapper;
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + array(
            //'defaultSetting' => array(),
        );
        $this->permissionsCriterionHandler = $permissionsCriterionHandler;
    }

    /**
     * Finds content objects for the given query.
     *
     * @todo define structs for the field filters
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if query is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     * @param boolean $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findContent( Query $query, array $fieldFilters = array(), $filterOnUserPermissions = true )
    {
        $query = clone $query;
        $query->filter = $query->filter ?: new Criterion\MatchAll();

        $this->validateContentCriteria( array( $query->query ), "\$query" );
        $this->validateContentCriteria( array( $query->filter ), "\$query" );
        $this->validateContentSortClauses( $query );
        $this->validateSortClauses( $query );

        if ( $filterOnUserPermissions && !$this->permissionsCriterionHandler->addPermissionsCriterion( $query->filter ) )
        {
            return new SearchResult( array( 'time' => 0, 'totalCount' => 0 ) );
        }

        if ( $query->limit === null )
        {
            $query->limit = self::MAX_LIMIT;
        }

        $result = $this->searchHandler->findContent( $query, $fieldFilters );

        foreach ( $result->searchHits as $hit )
        {
            $hit->valueObject = $this->domainMapper->buildContentDomainObject(
                $hit->valueObject
            );
        }

        return $result;
    }

    /**
     * Checks that $criteria does not contain Location criterions.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion[] $criteria
     * @param string $argumentName
     */
    protected function validateContentCriteria( array $criteria, $argumentName )
    {
        foreach ( $criteria as $criterion )
        {
            if ( $criterion instanceof LocationCriterion )
            {
                throw new InvalidArgumentException(
                    $argumentName, "Location criterions cannot be used in Content search"
                );
            }
            if ( $criterion instanceof LogicalOperator )
            {
                $this->validateContentCriteria( $criterion->criteria, $argumentName );
            }
        }
    }

    /**
     * Checks that $query does not contain Location sort clauses.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     */
    protected function validateContentSortClauses( Query $query )
    {
        foreach ( $query->sortClauses as $sortClause )
        {
            if ( $sortClause instanceof LocationSortClause )
            {
                throw new InvalidArgumentException(
                    "\$query", "Location sort clauses cannot be used in Content search"
                );
            }
        }
    }

    /**
     * Validates sort clauses of a given $query.
     *
     * For the moment this validates only Field sort clauses.
     * Valid Field sort clause provides $languageCode if targeted field is translatable,
     * and the same in reverse - it does not provide $languageCode for non-translatable field.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If sort clauses are not valid
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     *
     * @return void
     */
    protected function validateSortClauses( Query $query )
    {
        foreach ( $query->sortClauses as $key => $sortClause )
        {
            if ( !$sortClause instanceof SortClause\Field && !$sortClause instanceof SortClause\MapLocationDistance )
            {
                continue;
            }

            /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget|\eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\MapLocationTarget $fieldTarget */
            $fieldTarget = $sortClause->targetData;
            $contentType = $this->repository->getContentTypeService()->loadContentTypeByIdentifier(
                $fieldTarget->typeIdentifier
            );

            if ( $contentType->getFieldDefinition( $fieldTarget->fieldIdentifier )->isTranslatable )
            {
                if ( $fieldTarget->languageCode === null )
                {
                    throw new InvalidArgumentException(
                        "\$query->sortClauses[{$key}]", "No language is specified for translatable field"
                    );
                }
            }
            else if ( $fieldTarget->languageCode !== null )
            {
                throw new InvalidArgumentException(
                    "\$query->sortClauses[{$key}]", "Language is specified for non-translatable field, null should be used instead"
                );
            }
        }
    }

    /**
     * Performs a query for a single content object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the object was not found by the query or due to permissions
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if criterion is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if there is more than one result matching the criterions
     *
     * @todo define structs for the field filters
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     * @param boolean $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function findSingle( Criterion $filter, array $fieldFilters = array(), $filterOnUserPermissions = true )
    {
        $this->validateContentCriteria( array( $filter ), "\$filter" );

        if ( $filterOnUserPermissions && !$this->permissionsCriterionHandler->addPermissionsCriterion( $filter ) )
        {
            throw new NotFoundException( 'Content', '*' );
        }

        return $this->domainMapper->buildContentDomainObject(
            $this->searchHandler->findSingle( $filter, $fieldFilters )
        );
    }

    /**
     * Suggests a list of values for the given prefix
     *
     * @param string $prefix
     * @param string[] $fieldPaths
     * @param int $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     */
    public function suggest( $prefix, $fieldPaths = array(), $limit = 10, Criterion $filter = null )
    {

    }

    /**
     * Finds Locations for the given query.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if query is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     * @param boolean $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findLocations( LocationQuery $query, $filterOnUserPermissions = true )
    {
        $query = clone $query;
        $query->filter = $query->filter ?: new Criterion\MatchAll();

        $this->validateSortClauses( $query );

        if ( $filterOnUserPermissions && !$this->permissionsCriterionHandler->addPermissionsCriterion( $query->filter ) )
        {
            return new SearchResult( array( 'time' => 0, 'totalCount' => 0 ) );
        }

        if ( $query->limit === null )
        {
            $query->limit = self::MAX_LIMIT;
        }

        $result = $this->locationSearchHandler->findLocations( $query );

        foreach ( $result->searchHits as $hit )
        {
            $hit->valueObject = $this->domainMapper->buildLocationDomainObject(
                $hit->valueObject
            );
        }

        return $result;
    }
}
