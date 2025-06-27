{capture name="mainbox"}
    {if $runtime.company_id || !"MULTIVENDOR"|fn_allowed_for || ("MULTIVENDOR"|fn_allowed_for && !$runtime.company_id)}
        {if $attributes}
            <form action="{""|fn_url}" method="post" name="apply_attributes" class="form-horizontal form-edit">
                {include file="common/subheader.tpl" title=__("cp_attrs") target="#cp_all_attrs"}
                <div id="cp_all_attrs" class="collapse in">
                    {foreach from=$attributes item="atr_data"}
                        <input type="hidden" class="cm-no-hide-input" name="apply_attrs[]" value="{$atr_data.cp_attr_id}" />
                        {$atr_data.cp_attr_name}<br />
                    {/foreach}
                </div>
                <div class="control-group">
                    <label for="elm_apply_action" class="control-label">{__("cp_pr_apply_action")}:</label>
                    <div class="controls">
                        <select name="apply_action" id="elm_apply_action">
                            <option value="add_sel">{__("cp_pr_usual")}</option>
                            <option value="del_sel">{__("cp_pr_remove_from_selected")}</option>
                            <option value="add">{__("cp_pr_add_to_all")}</option>
                            <option value="del">{__("cp_pr_remove_from_all")}</option>
                        </select>
                    </div>
                </div>
                <div id="apply_attribute_to_products">
                    <fieldset>
                        {include file="pickers/products/picker.tpl" positions="" input_name="applyed_to_products" data_id="added_products" type="links" placement="right"}
                    </fieldset>
                </div>

                {capture name="buttons"}
                    {include file="buttons/save.tpl" but_name="dispatch[cp_pow_rev.apply_attr_to_pr]" but_role="submit-link" but_target_form="apply_attributes"}
                {/capture}
            </form>
        {else}
            <p class="no-items">{__("no_attributes_selected")}</p>
        {/if}
    {else}
        {if !$runtime.forced_company_id} 
            {include file="common/select_company.tpl" hide_title=true select_id="company_select" assign="mb"}
            {$smarty.capture.mainbox nofilter}
        {/if}
    {/if}
{/capture}


{assign var="title" value="{__("cp_apply_glob_attr")}"}
    
{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}