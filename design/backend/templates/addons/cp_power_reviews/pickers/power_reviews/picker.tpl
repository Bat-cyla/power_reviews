{$positions="true"}
{$rnd = rand()}
{$data_id="`$data_id`_`$rnd`"}
{$view_mode=$view_mode|default:"mixed"}
{$start_pos=$start_pos|default:0}
{script src="js/tygh/picker.js"}

{if $item_ids && !$item_ids|is_array && $type != "table"}
    {$item_ids=","|explode:$item_ids}
{/if}

{if $view_mode != "list"}
    {if $extra_var}
        {$extra_var=$extra_var|escape:url}
    {/if}

    {if !$no_container}<div class="buttons-container">{/if}{if $picker_view}[{/if}
        {include file="buttons/button.tpl" but_id="opener_picker_`$data_id`" but_href="cp_pow_rev.picker?display=`$display`&picker_for=`$picker_for`&extra=`$extra_var`&checkbox_name=`$checkbox_name`&aoc=`$aoc`&data_id=`$data_id`"|fn_url but_text=$but_text|default:__("add_reviews") but_role="add" but_target_id="content_`$data_id`" but_meta="cm-dialog-opener btn pull-right" but_icon="icon-plus"}
    {if $picker_view}]{/if}{if !$no_container}</div>{/if}

    <div class="hidden" id="content_{$data_id}" title="{$but_text|default:__("add_reviews")}">
    </div>

{/if}

{if $view_mode != "button"}
    {if !$positions}
        <input id="b{$data_id}_ids" type="hidden" name="{$input_name}" value="{if $item_ids}{","|implode:$item_ids}{/if}" />
    {/if}
    
    <table width="100%" class="table table-middle">
    <thead>
        <tr>
        <th width="35%">{__("message")}</th>
        <th width="15%">{__("name")}</th>
        <th width="15%">{__("rating")}</th>
        <th width="20%">{__("date")}</th>
        <th width="10%">{__("object")}</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody id="{$data_id}"{if !$item_ids} class="hidden"{/if}>
    {include file="addons/cp_power_reviews/pickers/power_reviews/js.tpl" review_id="`$ldelim`review_id`$rdelim`" holder=$data_id input_name=$input_name clone=true hide_link=$hide_link hide_delete_button=$hide_delete_button}
    {if $item_ids}
        {foreach name="items" from=$item_ids key="p_id" item="item_id"}
            {include file="addons/cp_power_reviews/pickers/power_reviews/js.tpl" review_id=$item_id holder=$data_id input_name=$input_name hide_link=$hide_link hide_delete_button=$hide_delete_button first_item=$smarty.foreach.items.first}
        {/foreach}
    {/if}
    </tbody>
    <tbody id="{$data_id}_no_item"{if $item_ids} class="hidden"{/if}>
    <tr class="no-items">
        <td colspan="7"><p class="center">{$no_item_text|default:__("no_items")}</p></td>
    </tr>
    </tbody>
    </table>
{/if}