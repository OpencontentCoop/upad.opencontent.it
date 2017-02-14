{*ezpagedata_set( 'left_menu', 'facet_left' )*}
{ezpagedata_set( 'left_menu', false() )}



{if is_set( $forceSort )|not()}
    {def $forceSort = 0}
{/if}

{* invio al pagedata le informazioni così le ho anche nel pagelayout *}
{*ezpagedata_set( 'facets', $facets )}
{ezpagedata_set( 'sortString', $sortString )}
{ezpagedata_set( 'classes', $classes )}
{ezpagedata_set( 'subtree', $subtree )}
{ezpagedata_set( 'forceSort', $forceSort )}
{ezpagedata_set( 'view_parameters', $view_parameters )*}

{*
    @TODO
    {ezpagedata_set( 'filters', $filters )}
    {ezpagedata_set( 'limit', $limit )}
    {ezpagedata_set( 'query', $query )}
*}

{if not( is_set( $default_filters ) )}
{def $default_filters = array()}
{/if}

{if not( is_set( $useDateFilter ) )}
{def $useDateFilter = false()}
{/if}

{def $params = hash( 'node', $node,
                     'facets', $facets,
                     'default_filters', $default_filters,
                     'sortString', $sortString,
                     'classes', $classes,
                     'subtree', $subtree,
                     'forceSort', $forceSort,
                     'useDateFilter', $useDateFilter,
                     'view_parameters', $view_parameters )}

<div id="sidemenu" style="float:left;width:300px;font-size: 0.9em;">
{include uri='design:menu/facet_left.tpl' name=facet_left params=$params}
</div>

{def $page_limit = 20
     $sort_by = hash()
     $query = ''
     $filters = array()}


{* controllo nei view_parameters se ci sono filtri attivi selezionati dalle faccette *}
{foreach $facets as $key => $value}
    {if $value|ne('')}
    {def $name = $value.name|urlencode }
    {if and( is_set( $view_parameters.$name ), $view_parameters.$name|ne( '' ) )}
        {set $filters = $filters|append( concat( $value.field, ':', $view_parameters.$name|urldecode ) )}
    {/if}
    {undef $name}
    {/if}
{/foreach}

{if and( is_set( $default_filters ), $default_filters|ne('') ) }
	{set $filters = $filters|merge( $default_filters )}
{/if}

{* controllo i view_parameters per il sort: se non c'è lo applico *}
{if is_set( $view_parameters.sort )|not()}
    {set $view_parameters = $view_parameters|merge( hash( 'sort', $sortString ) )}
{/if}

{if is_set( $view_parameters.forceSort )|not()}
    {set $view_parameters = $view_parameters|merge( hash( 'forceSort', $forceSort ) )}
{/if}


{* parso il view_parameters.sort da stringa a hash *}
{if and( is_set( $view_parameters.sort ), $view_parameters.sort|ne( '' ) )}
    {def $sortArray = $view_parameters.sort|explode( '|' )}
    {foreach $sortArray as $sortArrayPart}
        {def $sortArrayPartArray = $sortArrayPart|explode( '-' )}
        {set $sort_by = $sort_by|merge( hash( $sortArrayPartArray[0], $sortArrayPartArray[1] ) )}
        {undef $sortArrayPartArray}
    {/foreach}
    {undef $sortArray}
{/if}

{* controllo i view_parameters per la query text *}
{if and( is_set( $view_parameters.query ), $view_parameters.query|ne( '' ) )}
    {set $query = $view_parameters.query}
    {if $view_parameters.forceSort|ne(1)}
        {set $sort_by = hash( 'score', 'desc' )}
    {/if}
{/if}

{def $dateFilter=0}
{if and( is_set( $view_parameters.dateFilter ), $view_parameters.dateFilter|ne( '' ), $view_parameters.dateFilter|lt( 6 ) )}
    {set $dateFilter = $view_parameters.dateFilter}
{/if}

{* fetch a solr *}
{def $search_hash = hash( 'subtree_array', $subtree,
                          'query', $query,
                          'class_id', $classes,
                          'filter', $filters,
                          'offset', $view_parameters.offset,
                          'publish_date', $dateFilter,
                          'sort_by', cond( $sort_by|count()|gt(0), $sort_by, false() ),
                          'limit', $page_limit)
     $search = fetch( ezfind, search, $search_hash )
     $search_result = $search['SearchResult']
     $search_count = $search['SearchCount']
     $search_extras = $search['SearchExtras']
     $search_data = $search}



<div class="border-box" style="float:left;width:669px">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc float-break">

<div class="content-view-full">
    <div class="class-folder">

        <div class="attribute-header">
            <h1>{attribute_view_gui attribute=$node.data_map.name}</h1>
        </div>

        {*if $node.object.data_map.image.has_content}
            <div class="attribute-image object-left">
                {attribute_view_gui attribute=$node.data_map.image image_class=large}
            </div>
        {/if}

        {if $node.object.data_map.short_description.has_content}
            <div class="attribute-short">
                {attribute_view_gui attribute=$node.data_map.short_description}
            </div>
        {/if}

        {if $node.object.data_map.description.has_content}
            <div class="attribute-long block">
                {attribute_view_gui attribute=$node.data_map.description}
            </div>
        {/if*}

        <div id="children">
        {if $search_count|gt(0)}

            {include name=navigator
                 uri='design:navigator/google.tpl'
                 page_uri=$node.url_alias
                 item_count=$search_count
                 view_parameters=$view_parameters
                 item_limit=$page_limit}

            <div class="content-view-children">
                {foreach $search_result as $child }
                    {node_view_gui view='line' content_node=$child}
                {/foreach}
            </div>

            {include name=navigator
                 uri='design:navigator/google.tpl'
                 page_uri=$node.url_alias
                 item_count=$search_count
                 view_parameters=$view_parameters
                 item_limit=$page_limit}

        {/if}
        </div>

    </div>
</div>


</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>
