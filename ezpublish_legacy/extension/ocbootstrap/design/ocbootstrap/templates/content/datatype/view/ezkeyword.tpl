{$attribute.content.keyword_string|wash( xhtml )}
{if is_set( $#persistent_variable.keywords )}
    {set scope='global' persistent_variable=$#persistent_variable|merge( hash( 'keywords', concat( $#persistent_variable.keywords, ', ', $attribute.content.keyword_string ) ) )}
{else}
    {if is_array( $#persistent_variable )|not()}
        {set scope='global' persistent_variable=hash( 'keywords', $attribute.content.keyword_string )}
    {else}
        {set scope='global' persistent_variable=$#persistent_variable|merge( hash( 'keywords', $attribute.content.keyword_string ) )}
    {/if}
{/if}