
{* DESIGN: Header START *}<div class="box-header"><div class="box-ml">

{* get the sections list which consist of: sections allowed to assign to the object by the
   current loged in user, plus the section which is currently assigned to the object *}
{*{def $sections=fetch( 'content', 'section_list' )*}
{def $sections=$object.allowed_assign_section_list
     $currentSectionName='unknown'}

{* get the name of the object's current section *}
{foreach $sections as $sectionItem }
    {if eq( $sectionItem.id, $object.section_id )}
        {set $currentSectionName=$sectionItem.name}
    {/if}
{/foreach}

{undef $currentSectionName}

{* DESIGN: Header END *}</div></div>

{* DESIGN: Content START *}<div class="box-bc"><div class="box-ml"><div class="box-content">

{* show the section selector *}
    <label for="SelectedSectionId">{'Choose section'|i18n( 'design/admin/node/view/full' )}:</label>
    <select id="SelectedSectionId" name="SelectedSectionId">
    {foreach $sections as $section}
        {if eq( $section.id, $object.section_id )}
        <option value="{$section.id}" selected="selected">{$section.name|wash}</option>
        {else}
        <option value="{$section.id}">{$section.name|wash}</option>
        {/if}
    {/foreach}
    </select>

<input type="submit" value="{'Set'|i18n( 'design/admin/node/view/full' )}" name="SectionEditButton" class="button" />
{* DESIGN: Content END *}</div></div></div>


