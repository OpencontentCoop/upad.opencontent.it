{set $classification = cond( and(is_set( $align ), $align ), concat( $classification, ' text-', $align ), $classification )}


{$classification}

<p{if $classification|trim} class="{$classification|wash}"{/if}>
{if eq( $content|trim(), '' )}&nbsp;{else}{$content}{/if}
</p>