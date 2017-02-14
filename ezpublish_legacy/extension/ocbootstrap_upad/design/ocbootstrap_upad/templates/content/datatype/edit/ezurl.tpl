{* DO NOT EDIT THIS FILE! Use an override template instead. *}
{default attribute_base=ContentObjectAttribute html_class='full' placeholder=false()}

{if ne( $attribute_base, 'ContentObjectAttribute' )}
    {def $id_base = concat( 'ezcoa-', $attribute_base, '-', $attribute.contentclassattribute_id, '_', $attribute.contentclass_attribute_identifier )}
{else}
    {def $id_base = concat( 'ezcoa-', $attribute.contentclassattribute_id, '_', $attribute.contentclass_attribute_identifier )}
{/if}
{if $placeholder}<label>{$placeholder}</label>{/if}
<p><input placeholder="http://www.google.com" id="{$id_base}_url" class="{$html_class}" type="text" name="{$attribute_base}_ezurl_url_{$attribute.id}" value="{$attribute.content|wash( xhtml )}" /></p>
<p style="display: none"><input placeholder="{'Text'|i18n( 'design/standard/content/datatype' )}"id="{$id_base}_text" class="{$html_class}" class="{$html_class}" type="text" name="{$attribute_base}_ezurl_text_{$attribute.id}" value="{$attribute.data_text|wash( xhtml )}" /></p>

{/default}
