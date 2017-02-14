<div class="form-group">
    {foreach $values as $item}
      {if gt($item.count,0)}
        {if is_set($label)}<label for="{$id}">{$label}</label>{/if}
        {break}
      {/if}
    {/foreach}
  <div class="cloud text-center">

  {foreach $values as $item}
    {if gt($item.count,0)}
      <span style="white-space: nowrap">
        <input type="checkbox" style="display: inline" name="{$input_name}[]" id="{$id}" {if $item.active}checked="checked"{/if} value="{$item.query}" />
        <span style="white-space: nowrap;line-height:.5;{if $item.active}color:#f00{else}color:#333{/if};font-size:1.{1|mul($item.count)}em;"> {$item.raw_name|wash()}</span>
      </span>
    {/if}
  {/foreach}
  </div>
</div>