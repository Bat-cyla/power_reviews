{include file="common/subheader.tpl" title=__("cp_pr_seo_for_reviews") target="#reviews_seo_section"}
<div id="reviews_seo_section" class="collapsed in">
    {include file="addons/cp_power_reviews/addons/seo/common/review_seo_name_field.tpl" 
        hide_title=true 
        object_data=$discussion.cp_seo 
        object_name="cp_object_seo" 
        object_id=$discussion.thread_id 
        object_type=$smarty.const.CP_PR_OBJECT_SEO_KEY 
        share_dont_hide=true
        preview_link="cp_pow_rev.view?thread_id=`$discussion.thread_id`"|fn_url:"C"
    }
    <div id="cp_pr_seo_meta" class="collapse in">
        <input type="hidden" name="cp_object_seo[object_type]" value="{$discussion.object_type}" />
        <input type="hidden" name="cp_object_seo[object_id]" value="{$discussion.object_id}" />
        <input type="hidden" name="cp_object_seo[thread_id]" value="{$discussion.thread_id}" />
        
        {if $cp_pf_avail_placeholders}
            <div class="control-group">
                <label class="control-label" for="elm_disc_h1">{__("cp_avail_placeholders")}:</label>
                <div class="controls">
                    {foreach from=$cp_pf_avail_placeholders key="pr_pl" item="pr_holder"}
                        <strong><code>{$pr_holder.pl_body}</code></strong>&nbsp;-&nbsp;{__($pr_holder.description)}<br />
                    {/foreach}
                </div>
            </div>
        {/if}
        <div class="control-group">
            <label class="control-label" for="elm_disc_h1">{__("cp_pr_h1_title")}:</label>
            <div class="controls">
                <input type="text" name="cp_object_seo[h1]" id="elm_disc_h1" size="55" value="{$discussion.cp_seo.h1}" class="input-large" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="elm_disc_page_title">{__("page_title")}:</label>
            <div class="controls">
                <input type="text" name="cp_object_seo[page_title]" id="elm_disc_page_title" size="55" value="{$discussion.cp_seo.page_title}" class="input-large" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="elm_disc_meta_descr">{__("meta_description")}:</label>
            <div class="controls">
                <textarea name="cp_object_seo[meta_description]" id="elm_disc_meta_descr" cols="55" rows="2" class="input-large">{$discussion.cp_seo.meta_description}</textarea>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="elm_disc_meta_keywords">{__("meta_keywords")}:</label>
            <div class="controls">
                <textarea name="cp_object_seo[meta_keywords]" id="elm_disc_meta_keywords" cols="55" rows="2" class="input-large">{$discussion.cp_seo.meta_keywords}</textarea>
            </div>
        </div>
        {if $addons.cp_seo_templates.status == "A"}
            {include file="common/subheader.tpl" title=__("cp_seo_subheader") target="#acc_custom_pr_bc_`$discussion.object_id`"}
            <div id="acc_custom_pr_bc_{$discussion.object_id}" class="collapse in">
                <div class="control-group">
                    <label class="control-label" for="elm_pr_disc_cust_bc">{__("cp_seo_custom_breadcrumb")}:</label>
                    <div class="controls">
                        <input type="text" name="cp_object_seo[cp_st_custom_bc]" id="elm_pr_disc_cust_bc" size="55" value="{$discussion.cp_seo.cp_st_custom_bc}" class="input-large" />
                    </div>
                </div>
            </div>
        {/if}
    </div>
</div>
{if !$only_meta}
{include file="common/subheader.tpl" title=__("cp_reviews_attrs") target="#reviews_attr_section"}
<div class="table-responsive-wrapper" id="reviews_attr_section" class="collapsed in">
    <table class="table table-middle table--relative table-responsive">
    <thead class="cm-first-sibling">
        <tr>
            <th width="5%">{__("position")}</th>
            <th width="10%">{__("type")}</th>
            <th width="30%">{__("name")}</th>
            <th width="20%">{__("cp_pr_view_type")}</th>
            {if "ULTIMATE"|fn_allowed_for}
                <th width="15%">{__("company")}</th>
            {/if}
            <th width="15%">{__("status")}</th>
            <th width="15%">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        {if $product_data.cat_prod_attr}
            {foreach from=$product_data.cat_prod_attr item="attr" key="_key" name="prod_attrs"}
                <tr class="cm-row-item {if $attr.status == "D"}cp-disabled-opacity{/if}">
                    <td width="5%" data-th="{__("position")}" class="{$no_hide_input_if_shared_product}">
                        <input type="text" value="{$attr.attr_pos}" class="input-micro" readonly=true />
                    </td>
                    <td width="10%" data-th="{__("type")}" class="{$no_hide_input_if_shared_product}">
                        {if $attr.object_type == "G"}
                            {__("cp_global_attr")}
                        {elseif $attr.object_type == "P"}
                            {__("product")}
                        {/if}
                    <td width="30%" class="{$no_hide_input_if_shared_product}" data-th="{__("name")}">
                    
                        {if $attr.object_type == "G"}
                            {$attr.cp_attr_name}
                        {else}
                            <input type="text" value="{$attr.cp_attr_name}" class="input-large" readonly=true/>
                        {/if}
                    </td>
                    <td width="20%" data-th="{__("cp_pr_view_type")}">
                        {if $attr.view_type == "D"}
                            {__("cp_pr_default_txt")}
                        {else}
                            {foreach from=$attr.view_type_txt item="vt_data"}
                                {$val_type=$vt_data.type}
                                <span class="cp-pr__attr-vt_name">{$cp_pr_exterm_types.$val_type}:</span>
                                <div class="cp-pr__attr-vt">{$vt_data.name}</div>
                            {/foreach}
                        {/if}
                    </td>
                    {if "ULTIMATE"|fn_allowed_for}
                        <td width="15%" data-th="{__("company")}">
                            <input type="hidden" class="{$no_hide_input_if_shared_product}" value="{$attr.company_id}" />
                            {include file="views/companies/components/company_name.tpl" object=$attr}
                        </td>
                    {/if}
                    <td width="15%" class="nowrap {$no_hide_input_if_shared_product} left" data-th="{__("status")}">
                        {if $attr.status == "A"}
                            {__("active")}
                        {elseif $attr.status == "D"}
                            {__("disabled")}
                        {/if}
                    </td>
                    <td width="15%" class="nowrap {$no_hide_input_if_shared_product} right">
                        {__("cp_pr_from_cat")}
                    </td>
                </tr>
            {/foreach}
        {/if}
        {if $product_data.cp_prod_rew_attrs}
            {foreach from=$product_data.cp_prod_rew_attrs item="attr" key="_key" name="prod_attrs"}
                <tr class="cm-row-item {if $attr.status == "D"}cp-disabled-opacity{/if}">
                    <input type="hidden" class="{$no_hide_input_if_shared_product}" name="product_data[cp_rew_attr][{$_key}][cp_attr_id]" value="{$attr.cp_attr_id}" />
                    <input type="hidden" class="{$no_hide_input_if_shared_product}" name="product_data[cp_rew_attr][{$_key}][object_type]" value="{$attr.object_type}" />
                    
                    <td width="5%" class="{$no_hide_input_if_shared_product}" data-th="{__("position")}">
                        <input type="text" name="product_data[cp_rew_attr][{$_key}][attr_pos]" value="{$attr.attr_pos}" class="input-micro" />
                    </td>
                    <td width="10%" class="{$no_hide_input_if_shared_product}" data-th="{__("type")}">
                        {if $attr.object_type == "G"}
                            {__("cp_global_attr")}
                        {elseif $attr.object_type == "P"}
                            {__("product")}
                        {/if}
                    <td width="30%" class="{$no_hide_input_if_shared_product}" data-th="{__("name")}">
                    
                        {if $attr.object_type == "G"}
                            {$attr.cp_attr_name}
                        {else}
                            <input type="text" name="product_data[cp_rew_attr][{$_key}][cp_attr_name]" value="{$attr.cp_attr_name}" class="input-large" />
                            <div class="cp-pr__mandat-main">
                                {__("cp_pr_is_mandatory")}: 
                                <input type="hidden" name="product_data[cp_rew_attr][{$_key}][required]" value="N" />
                                <input type="checkbox" name="product_data[cp_rew_attr][{$_key}][required]" value="Y" {if $attr.required == "Y"}checked="checked"{/if} />
                            </div>
                        {/if}
                    </td>
                    <td width="20%" data-th="{__("cp_pr_view_type")}">
                        {if $attr.view_type == "D"}{__("cp_pr_default_txt")}{else}
                            {if $attr.object_type == "G"}
                                {foreach from=$attr.view_type_txt item="vt_data"}
                                    {$val_type=$vt_data.type}
                                    <span class="cp-pr__attr-vt_name"><strong>{$cp_pr_exterm_types.$val_type}:</strong></span>
                                    <div class="cp-pr__attr-vt">{$vt_data.name}</div>
                                {/foreach}
                            {else}
                                <div class="cp-pr__view-type__selector">
                                    <select class="span3 cp-select-view-type-attr" name="product_data[cp_rew_attr][{$_key}][view_type]" id="cp_selec_view_type_{$_key}">
                                        <option value="D" {if $attr.view_type == "D"}selected="selected"{/if}>{__("cp_pr_default_txt")}</option>
                                        <option value="E" {if $attr.view_type == "E"}selected="selected"{/if}>{__("cp_pr_extremum_txt")}</option>
                                    </select>
                                </div>
                                <div class="{if $attr.view_type == "D"}hidden{/if}" id="cp_selec_view_type_extrem_{$_key}">
                                    {foreach from=$attr.view_type_txt item="vt_data"}
                                        {$val_type=$vt_data.type}
                                        <span class="cp-pr__attr-vt_name">{$cp_pr_exterm_types.$val_type}:</span>
                                        <div class="cp-pr__attr-vt">
                                            <input name="product_data[cp_rew_attr][{$_key}][view_type_txt][{$vt_data.type}][name_id]" type="hidden" value="{$vt_data.name_id}" class="input-short" />
                                            <input name="product_data[cp_rew_attr][{$_key}][view_type_txt][{$vt_data.type}][name]" type="text" value="{$vt_data.name}" class="input-short" />
                                        </div>
                                    {/foreach}
                                </div>
                            {/if}
                        {/if}
                    </td>
                    {if "ULTIMATE"|fn_allowed_for}
                        <td width="15%" data-th="{__("company")}">
                            <input type="hidden" name="product_data[cp_rew_attr][{$_key}][company_id]" class="{$no_hide_input_if_shared_product}" value="{$attr.company_id}" />
                            {include file="views/companies/components/company_name.tpl" object=$attr}
                        </td>
                    {/if}
                    <td width="15%" class="nowrap {$no_hide_input_if_shared_product} left" data-th="{__("status")}">
                        {if $attr.object_type == "G"}
                            {if $attr.status == "A"}
                                {__("active")}
                                <input type="hidden" name="product_data[cp_rew_attr][{$_key}][status]" value="A" />
                            {elseif $attr.status == "D"}
                                {__("disabled")}
                                <input type="hidden" name="product_data[cp_rew_attr][{$_key}][status]" value="D" />
                            {/if}
                        {else}
                            <select class="cp-pr__status-select" name="product_data[cp_rew_attr][{$_key}][status]">
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
        {math equation="x+1" x=$_key|default:0 assign="new_key"}
        <tr class="{cycle values="table-row , " reset=1}{$no_hide_input_if_shared_product}" id="box_cp_add_rew_attr">
            <input type="hidden" name="product_data[cp_rew_attr][{$new_key}][new]" value="Y" />
            <td width="5%" data-th="{__("position")}">
                <input type="text" name="product_data[cp_rew_attr][{$new_key}][attr_pos]" value="" class="input-micro" />
            </td>
            <td width="10%" id="p_teg_for_id_{$new_key}" data-th="{__("type")}">
                <select class="span3 cp-select-type-attr" name="product_data[cp_rew_attr][{$new_key}][object_type]" id="cp_selec_type_{$new_key}">
                    <option value="P">{__("cp_product_attr")}</option>
                    <option value="G">{__("cp_global_attr")}</option>
                </select>
            </td>
            <td width="30%" data-th="{__("name")}">
                <span id="attr_name_field_{$new_key}">
                    <input type="text" name="product_data[cp_rew_attr][{$new_key}][cp_attr_name]" value="" class="input-large" />
                </span>
                <span id="attr_select_glob_{$new_key}" class="hidden">
                    {if $product_data.cp_other_glob_attr}
                        <select class="span3" name="product_data[cp_rew_attr][{$new_key}][cp_attr_id]" id="selec_attr_glob_{$new_key}" disabled="disabled">
                            {foreach from=$product_data.cp_other_glob_attr item="glob"}
                                <option value="{$glob.cp_attr_id}">{$glob.cp_attr_name}</option>
                            {/foreach}
                        </select>
                    {else}
                        {__("cp_no_avalibale_gl_attr")}
                    {/if}
                </span>
            </td>
            <td width="20%" data-th="{__("cp_pr_view_type")}">
                <div id="cp_selec_view_type_main_{$new_key}">
                    <div class="cp-pr__view-type__selector">
                        <select class="span3 cp-select-view-type-attr" name="product_data[cp_rew_attr][{$new_key}][view_type]" id="cp_selec_view_type_{$new_key}">
                            <option value="D">{__("cp_pr_default_txt")}</option>
                            <option value="E">{__("cp_pr_extremum_txt")}</option>
                        </select>
                    </div>
                    <div class="hidden" id="cp_selec_view_type_extrem_{$new_key}">
                        {foreach from=$cp_pr_exterm_types key="vt_type" item="type_name"}
                            <span class="cp-pr__attr-vt_name">{$type_name}:</span>
                            <div class="cp-pr__attr-vt">
                                <input name="product_data[cp_rew_attr][{$new_key}][view_type_txt][{$vt_type}]" type="text" value="" class="input-short" />
                            </div>
                        {/foreach}
                    </div>
                </div>
            </td>
            {if "ULTIMATE"|fn_allowed_for}
                <td width="15%" class="cp_pr_store_td" data-th="{__("company")}">
                    {include file="views/companies/components/company_field.tpl" name="product_data[cp_rew_attr][{$new_key}][company_id]" id="elm_attr_data_`$new_key`" }
                </td>
            {/if}
            <td width="15%" class="nowrap {$no_hide_input_if_shared_product} left" data-th="{__("status")}">
                <select class="cp-pr__status-select" name="product_data[cp_rew_attr][{$new_key}][status]">
                    <option value="A">{__("active")}</option>
                    <option value="D">{__("disabled")}</option>
                </select>
            </td>
            <td width="15%" class="right" id="plus_for_id_{$new_key}">
                {include file="buttons/multiple_buttons.tpl" item_id="cp_add_rew_attr"}
            </td>
        </tr>
    </tbody>
</table>
</div>
{/if}