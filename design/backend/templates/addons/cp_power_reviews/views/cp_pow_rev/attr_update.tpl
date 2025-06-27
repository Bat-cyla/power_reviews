{if $attr_data}
    {assign var="id" value=$attr_data.cp_attr_id}
{else}
    {assign var="id" value=0}
{/if}

{assign var="allow_save" value=true}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="add_attr_form" class="form-horizontal form-edit  {if !$allow_save}cm-hide-inputs{/if}">
<input type="hidden" class="cm-no-hide-input" name="cp_attr_id" value="{$id}" />

<div id="add_attribute">
    <fieldset>
        <div class="control-group">
            <label for="elm_cp_attr_name" class="control-label cm-required">{__("name")}:</label>
            <div class="controls">
                <input type="text" name="attr_data[cp_attr_name]" id="elm_cp_attr_name" size="25" value="{$attr_data.cp_attr_name}" class="input-short" />
            </div>
        </div>
        
        {if "ULTIMATE"|fn_allowed_for}
            {include file="views/companies/components/company_field.tpl"
                name="attr_data[company_id]"
                id="elm_$attr_data_`$id`"
                selected=$attr_data.company_id
            }
        {/if}
        {include file="common/select_status.tpl" input_name="attr_data[status]" id="elm_attr_data_status" obj=$attr_data hidden=false}
    </fieldset>
</div>

{capture name="buttons"}
{include file="buttons/save_cancel.tpl" but_name="dispatch[cp_pow_rev.attr_update]" hide_first_button=false hide_second_button=false but_target_form="add_attr_form" save=$id}
{/capture}

</form>
{/capture}

{if !$id}
    {assign var="title" value=__("cp_new_attr")}
{else}
    {assign var="title" value="{__("editing_attr")}:&nbsp;`$attr_data.cp_attr_name`"}
{/if}
{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons}
