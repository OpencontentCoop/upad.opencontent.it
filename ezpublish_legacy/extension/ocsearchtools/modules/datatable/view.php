<?php
/** @var eZModule $module */
$Module = $Params['Module'];
$ParentNodes = $Params['ParentNodes'];
$Classes = $Params['Classes'];
$Fields = $Params['Fields'];
$DefaultFilters = $Params['DefaultFilters'] !== null ? $Params['DefaultFilters'] : array();
$http = eZHTTPTool::instance();

$fields = explode( ',', $Fields );
$classes = explode( ',', $Classes );
$subtreeArray = explode( ',', $ParentNodes );
$defaultFilters = $DefaultFilters !== null ? explode( ',', $DefaultFilters ) : array();
$fieldsToReturn = $fields;
$iDisplayStart = $http->hasGetVariable( 'iDisplayStart' ) ? $http->getVariable( 'iDisplayStart' ) : 10;
$iDisplayLength = $http->hasGetVariable( 'iDisplayLength' ) ? $http->getVariable( 'iDisplayLength' ) : 100;

/*
 * Ordering
 */
$sortBy = array( ezfSolrDocumentFieldBase::generateMetaFieldName( 'sort_name' ) => 'asc' );
if ( $http->hasGetVariable( 'iSortCol_0' ) )
{    
    for ( $i=0 ; $i<intval( $http->getVariable( 'iSortingCols' ) ); $i++ )
    {
        $sortBy = array();
        if ( $http->getVariable( 'bSortable_'.intval( $http->getVariable( 'iSortCol_'.$i) ) ) == "true" )
        {
            $sortKey = $fields[ intval( $http->getVariable( 'iSortCol_' . $i ) ) ];
            if ( $sortKey == 'name_t' || $sortKey == 'name' )
            {
                $sortKey = ezfSolrDocumentFieldBase::generateMetaFieldName( 'sort_name' );
            }
            $sortBy[] = array( $sortKey, $http->getVariable( 'sSortDir_' . $i ) );             
        }
    }
}

$query = '';
if ( $http->getVariable( 'sSearch' ) != "" )
{
    $query = str_replace( ' ', ' AND ', $http->getVariable( 'sSearch' ) ) . '*';
}

/* 
 * Individual column filtering
 */
$filters = array();
if ( !empty( $defaultFilters ) )
{        
    if ( $DefaultFilters  > 1 )
    {
        $filters[] = $defaultFilters;
    }
    else
    {
        $filters[] = $defaultFilters[0];
    }    
}
for ( $i=0 ; $i<count( $fields ) ; $i++ )
{
    $columnFilters = array();
    $columnSearch = $http->getVariable( 'sSearch_' . $i );
    if ( ( $fields[$i] == 'name' || $fields[$i] == 'meta_name_t' ) && !empty( $columnSearch ) )
    {
        $query = str_replace( ' ', ' AND ', $http->getVariable( 'sSearch_' . $i ) ) . '*';
        //$searchText = str_replace( ' ', ' AND ', trim( $http->getVariable( 'sSearch_' . $i ) ) );
        //$query = $searchText . '* OR ' . $searchText;    
    }
    elseif ( $http->hasGetVariable( 'bSearchable_' . $i ) && $http->getVariable( 'bSearchable_' . $i ) == "true" && $http->getVariable( 'sSearch_' . $i ) != '' )
    {
        if ( isSubAttributeField( $fields[$i] ) )
        {
            $columnFilters[] = $fields[$i] . ':"' . $http->getVariable( 'sSearch_' . $i ) . '"';
        }
        elseif ( isUserNameField( $fields[$i] ) )
        {
            $columnFilters[] = $fields[$i] . ':' . $http->getVariable( 'sSearch_' . $i );
        }
        elseif ( isClassNameField( $fields[$i] ) )
        {
            $columnFilters[] = $fields[$i] . ':"' . $http->getVariable( 'sSearch_' . $i ) . '"';
        }
        elseif ( isTextField( $fields[$i] ) )
        {
            $searchText = str_replace( ' ', ' AND ', trim( $http->getVariable( 'sSearch_' . $i ) ) );
            $columnFilters[] = array( 'or', $fields[$i] . ':' . $searchText . '*', $fields[$i] . ':' . $searchText  );    
        }        
    }    
    if ( !empty( $columnFilters ) )
    {        
        if ( count( $columnFilters ) > 1 )
            $filters[] = array_merge( array( 'or' ), $columnFilters );
        else
            $filters[] = $columnFilters[0];
    }
}
if ( empty( $filters ) )
{
    $filters = null;
}

$solrSearch = new eZSolr();

$params = array( 'SearchOffset' => $iDisplayStart,
                 'SearchLimit' => $iDisplayLength,                 
                 'SortBy' => $sortBy,
                 'Filter' => $filters,
                 'SearchContentClassID' => $classes,                 
                 'SearchSubTreeArray' => $subtreeArray,
                 'AsObjects' => false,                 
                 'FieldsToReturn' => $fieldsToReturn );

$solrSearch = new eZSolr();
$search = $solrSearch->search( $query, $params );
$search['SearchParameters'] = $params;
$iFilteredTotal = count( $search['SearchResult'] );
$iTotal = $search['SearchCount'];
$output = array(
    "sEcho" => intval( $http->getVariable( 'sEcho' ) ),
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iTotal,
    "aaData" => array()
);

if ( eZINI::instance()->variable( 'DebugSettings', 'DebugOutput' ) === 'enabled' )
{
    $output['searchParams'] = $search;
    $output['searchQuery'] = $query;
    $output['request'] = $_GET;
    $output['fields'] = $fields;    
    $output['results'] = $search['SearchResult'];
}

foreach( $search['SearchResult'] as $item )
{
    $row = array();    
    for ( $i=0 ; $i<count( $fields ); $i++ )
    {
        if ( $fields[$i] == 'published' || $fields[$i] == 'published_dt' )
        {
            $timestamp = strtotime( $item[$fields[$i]] );
            $row[] = date( 'd/m/Y', $timestamp );
        }        
        elseif ( isset( $item[$fields[$i]] ) )
        {
            $row[] = $item[$fields[$i]];
        }
        elseif ( isset( $item['fields'][$fields[$i]] ) )
        {
            if( substr( $fields[$i], -2 ) == 'dt' )
            {
                $timestamp = strtotime( $item['fields'][$fields[$i]] );
                $row[] = date( 'd/m/Y', $timestamp );
            }
            else
            {
                $row[] = $item['fields'][$fields[$i]];
            }
        }
        elseif ( strpos( $fields[$i], 'meta' ) === 0 ) //@todo
        {
            $fieldPart = explode( '_', $fields[$i] );
            $meta = array_shift( $fieldPart );
            $type = array_pop( $fieldPart );
            $row[] = $item[implode( '_', $fieldPart )];
        }
        else
            $row[] = '';
    }    
    $output['aaData'][] = $row;
}

header('Content-Type: application/json');
echo json_encode( $output );
//eZDisplayDebug();
eZExecution::cleanExit();

//@todo

function isTextField( $field )
{
    return substr( $field, -1 ) == 't' || $field == 'name';
}

function isUserNameField( $field )
{
    return $field == 'meta_owner_name_ms';
}

function isClassNameField( $field )
{
    return substr( $field, -2 ) == 'ms';
}

function isSubAttributeField( $field )
{
    return strpos( $field, 'subattr' ) !== false;
}
