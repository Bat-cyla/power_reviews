<div class="hidden" id="content_cp_reviews_attrs">
    {include file="common/subheader.tpl" title=__("cp_reviews_attrs") }
    
    <div class="table-responsive-wrapper">
        <table class="table table-middle table--relative table-responsive">
        <thead class="cm-first-sibling">
            <tr>
                <th width="5%">{__("position")}</th>
                <th width="15%">{__("type")} / {__("name")}</th>
                <th width="20%">{__("cp_pr_view_type")}</th>
                <th width="10%">{__("company")}</th>
                <th width="5%">{__("status")}</th>
                <th width="15%">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            {if $category_data.cp_cat_rew_attrs}
                {foreach from=$category_data.cp_cat_rew_attrs item="attr" key="_key" name="prod_attrs"}
                    <tr class="cm-row-item {if $attr.status == "D"}cp-disabled-opacity{/if}">
                        <input type="hidden" class="{$no_hide_input_if_shared_product}" name="category_data[cp_rew_attr][{$_key}][cp_attr_id]" value="{$attr.cp_attr_id}" />
                        <input type="hidden" class="{$no_hide_input_if_shared_product}" name="category_data[cp_rew_attr][{$_key}][object_type]" value="{$attr.object_type}" />
                        {if "MULTIVENDOR"|fn_allowed_for}
                            <input type="hidden" name="category_data[cp_rew_attr][{$_key}][company_id]" class="{$no_hide_input_if_shared_product}" value="{$attr.company_id}" />
                        {/if}
                        <td data-th="{__("position")}" width="5%" class="{$no_hide_input_if_shared_product}">
                            <input type="text" name="category_data[cp_rew_attr][{$_key}][attr_pos]" value="{$attr.attr_pos}" class="input-micro" />
                        </td>
                        <td data-th="{__("type")}" width="15%" class="{$no_hide_input_if_shared_product}">
                            <div><strong>{__("type")}:</strong></div>
                            {if $attr.object_type == "G"}
                                {__("cp_global_attr")}
                            {elseif $attr.object_type == "P"}
                                {__("product")}
                            {/if}
                            </div>
                                <div><strong>{__("name")}:</strong></div>
                                {if $attr.object_type == "G"}
                                    {$attr.cp_attr_name}
                                {else}
                                    <input type="text" name="category_data[cp_rew_attr][{$_key}][cp_attr_name]" value="{$attr.cp_attr_name}" class="input-medium" />
                                    <div class="cp-pr__mandat-main">
                                        {__("cp_pr_is_mandatory")}: 
                                        <input type="hidden" name="category_data[cp_rew_attr][{$_key}][required]" value="N" />
                                        <input type="checkbox" name="category_data[cp_rew_attr][{$_key}][required]" value="Y" {if $attr.required == "Y"}checked="checked"{/if} />
                                    </div>
                                {/if}
                            </div>
                        </td>
                        <td width="20%" data-th="{__("cp_pr_view_type")}">
                            {if $attr.view_type == "D"}{__("cp_pr_default_txt")}{else}
                                {if $attr.object_type == "G"}
                                    {foreach from=$attr.view_type_txt item="vt_data"}
                                        {$val_type=$vt_data.type}
                                        <span class="cp-pr__attr-vt_name">{$cp_pr_exterm_types.$val_type}:</span>
                                        <div class="cp-pr__attr-vt">{$vt_data.name}</div>
                                    {/foreach}
                                {else}
                                    <div class="cp-pr__view-type__selector">
                                        <select class="cp-select-view-type-attr" name="category_data[cp_rew_attr][{$_key}][view_type]" id="cp_selec_view_type_{$_key}">
                                            <option value="D" {if $attr.view_type == "D"}selected="selected"{/if}>{__("cp_pr_default_txt")}</option>
                                            <option value="E" {if $attr.view_type == "E"}selected="selected"{/if}>{__("cp_pr_extremum_txt")}</option>
                                        </select>
                                    </div>
                                    <div class="{if $attr.view_type == "D"}hidden{/if}" id="cp_selec_view_type_extrem_{$_key}">
                                        {foreach from=$attr.view_type_txt item="vt_data"}
                                            {$val_type=$vt_data.type}
                                            <span class="cp-pr__attr-vt_name">{$cp_pr_exterm_types.$val_type}:</span>
                                            <div class="cp-pr__attr-vt">
                                                <input name="category_data[cp_rew_attr][{$_key}][view_type_txt][{$vt_data.type}][name_id]" type="hidden" value="{$vt_data.name_id}" class="input-short" />
                                                <input name="category_data[cp_rew_attr][{$_key}][view_type_txt][{$vt_data.type}][name]" type="text" value="{$vt_data.name}" class="input-short" />
                                            </div>
                                        {/foreach}
                                    </div>
                                {/if}
                            {/if}
                        </td>
                        <td>
                            <input type="hidden" name="category_data[cp_rew_attr][{$_key}][company_id]" class="{$no_hide_input_if_shared_product}" value="{$attr.company_id}" />
                            {include file="views/companies/components/company_name.tpl" object=$attr}
                        </td> 
                        <td width="5%" class="nowrap {$no_hide_input_if_shared_product} left">
                            {if $attr.object_type == "G"}
                                {if $attr.status == "A"}
                                    {__("active")}
                                    <input type="hidden" name="category_data[cp_rew_attr][{$_key}][status]" value="A" />
                                {elseif $attr.status == "D"}
                                    {__("disabled")}
                                    <input type="hidden" name="category_data[cp_rew_attr][{$_key}][status]" value="D" />
                                {/if}
                            {else}
                                <select class="cp-pr__status-select" name="category_data[cp_rew_attr][{$_key}][status]">
                                    <option value="A" {if $attr.status == "A"}selected="selected"{/if}>{__("active")}</option>
                                    <option value="D" {if $attr.status == "D"}selected="selected"{/if}>{__("disabled")}</option>
                                </select>
                            {/if}
                        </td>
                        <td width="15%" class="nowrap {$no_hide_input_if_shared_product} right">
                            {include file="buttons/clone_delete.tpl" microformats="cm-delete-row" no_confirm=true}
                        </td>
                    </tr>
                {/foreach}
            {/if}
            {if "ULTIMATE"|fn_allowed_for || ("MULTIVENDOR"|fn_allowed_for && !$runtime.company_id)}
                {math equation="x+1" x=$_key|default:0 assign="new_key"}
                <tr class="{cycle values="table-row , " reset=1}{$no_hide_input_if_shared_product}" id="box_cp_add_rew_attr">
                    <input type="hidden" name="category_data[cp_rew_attr][{$new_key}][new]" value="Y" />
                    <td data-th="{__("position")}" width="5%">
                        <input type="text" name="category_data[cp_rew_attr][{$new_key}][attr_pos]" value="" class="input-micro" />
                    </td>
                    <td data-th="{__("type")}" width="15%" id="p_teg_for_id_{$new_key}">
                        <div><strong>{__("type")}:</strong></div>
                        <select class=" cp-select-type-attr" name="category_data[cp_rew_attr][{$new_key}][object_type]" id="cp_selec_type_{$new_key}">
                            <option value="P">{__("cp_product_attr")}</option>
                            <option value="G">{__("cp_global_attr")}</option>
                        </select>
                        
                        <div><strong>{__("name")}:</strong></div>
                        <div>
                            <span id="attr_name_field_{$new_key}">
                                <input type="text" name="category_data[cp_rew_attr][{$new_key}][cp_attr_name]" value="" class="input-medium" />
                            </span>
                            <span id="attr_select_glob_{$new_key}" class="hidden">
                                {if $category_data.cp_other_glob_attr}
                                    <select class="" name="category_data[cp_rew_attr][{$new_key}][cp_attr_id]" id="selec_attr_glob_{$new_key}" disabled="disabled">
                                        {foreach from=$category_data.cp_other_glob_attr item="glob"}
                                            <option value="{$glob.cp_attr_id}">{$glob.cp_attr_name}</option>
                                        {/foreach}
                                    </select>
                                {else}
                                    {__("cp_no_avalibale_gl_attr")}
                                {/if}
                            </span>
                        </div>
                    </td>
                    <td width="20%" data-th="{__("cp_pr_view_type")}">
                        <div id="cp_selec_view_type_main_{$new_key}">
                            <div class="cp-pr__view-type__selector">
                                <select class=" cp-select-view-type-attr" name="category_data[cp_rew_attr][{$new_key}][view_type]" id="cp_selec_view_type_{$new_key}">
                                    <option value="D">{__("cp_pr_default_txt")}</option>
                                    <option value="E">{__("cp_pr_extremum_txt")}</option>
                                </select>
                            </div>
                            <div class="hidden" id="cp_selec_view_type_extrem_{$new_key}">
                                {foreach from=$cp_pr_exterm_types key="vt_type" item="type_name"}
                                    <span class="cp-pr__attr-vt_name">{$type_name}:</span>
                                    <div class="cp-pr__attr-vt">
                                        <input name="category_data[cp_rew_attr][{$new_key}][view_type_txt][{$vt_type}]" type="text" value="" class="input-short" />
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                    </td>
                    <td class="cp_pr_store_td" data-th="{__("company")}">
                        {if "ULTIMATE"|fn_allowed_for}
                            {include file="views/companies/components/company_name.tpl" object=$category_data}
                        {else}
                            -
                        {/if}
                    </td>
                    <td width="5%" class="nowrap {$no_hide_input_if_shared_product} left">
                        <select class="cp-pr__status-select" name="category_data[cp_rew_attr][{$new_key}][status]">
                            <option value="A">{__("active")}</option>
                            <option value="D">{__("disabled")}</option>
                        </select>
                    </td>
                    <td width="15%" class="right" id="plus_for_id_{$new_key}">
                        {include file="buttons/multiple_buttons.tpl" item_id="cp_add_rew_attr"}
                    </td>
                </tr>
            {/if}
        </tbody>
    </table>
    </div>
</div>