{if $review_id == "0"}
    {assign var="review" value=$default_name}
{else}
    {assign var="review" value=$review_id|fn_cp_power_reviews_get_review_message|default:"`$ldelim`review`$rdelim`"}
    {assign var="review" value=$review|truncate:50}
    
    {assign var="review_data" value=$review_id|fn_cp_power_reviews_get_review}  
    {assign var="name" value=$review_data.name}    
{/if}

{assign var="discussion_object_types" value=""|fn_get_discussion_objects}

<tr {if !$clone}id="{$holder}_{$review_id}" {/if}class="cm-js-item{if $clone} cm-clone hidden{/if}">
    {if $position_field}
        <td>
            <input type="text" name="{$input_name}[{$review_id}]" value="{$position}" size="3" class="input-micro input-hidden" {if $clone}disabled="disabled"{/if} />
        </td>
    {/if}
    <input type="hidden" name="{$input_name}[{$review_id}]" value="{$review_id}" size="3" class="input-micro input-hidden" {if $clone}disabled="disabled"{/if} />
    <td>{$review}</td>
    <td>{if $name}{$name}{/if}</td>      
    <td>{if $review_data.rating_value}{include file="addons/discussion/views/discussion_manager/components/stars.tpl" stars=$review_data.rating_value}{/if}</td>       
    <td>{if $review_data.timestamp}{$review_data.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}{/if}</td>   
    <td>{if $review_data.object_type}{$discussion_object_types[$review_data.object_type]|ucfirst}{/if}</td>    
    <td>
        {capture name="tools_list"}
            {if !$hide_delete_button && !$view_only}
                <li><a onclick="Tygh.$.cePicker('delete_js_item', '{$holder}', '{$review_id}', 'b'); return false;">{__("delete")}</a></li>
            {/if}
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
</tr>