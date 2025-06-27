{capture name="mainbox"}
    <form action="{""|fn_url}" method="post" name="attr_manage_form" class="{if ""|fn_check_form_permissions} cm-hide-inputs{/if}" id="attr_manage_form" >

    {include file="common/pagination.tpl" save_current_page=true save_current_url=true}

    {assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
    {assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
    {assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

    {if $attributes}
        <div class="table-responsive-wrapper">
        <table class="table table-middle table--relative table-responsive">
            <thead>
                <tr>
                    <th width="1%">
                        {include file="common/check_items.tpl"}
                    </th>
                    <th><a class="cm-ajax" href="{"`$c_url`&sort_by=position&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("position")}</th>{if $search.sort_by == "position"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
                    <th width="25%" class="nowrap left">
                        <a class="cm-ajax" href="{"`$c_url`&sort_by=cp_attr_name&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">
                            {__("cp_attr_name")}{if $search.sort_by == "cp_attr_name"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}
                        </a>
                    </th>
                    <th width="15%">
                        <a class="cm-ajax" href="{"`$c_url`&sort_by=object_type&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">
                            {__("cp_attr_type")}{if $search.sort_by == "object_type"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}
                        </a>
                    </th>
                    <th><a class="cm-ajax" href="{"`$c_url`&sort_by=view_type&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("cp_pr_view_type")}</th>{if $search.sort_by == "view_type"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
                    <th width="15%">&nbsp;</th>
                    <th width="10%" class="right">
                        <a class="cm-ajax" href="{"`$c_url`&sort_by=status&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">
                            {__("status")}{if $search.sort_by == "status"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}
                        </a>
                    </th>
                </tr>
            </thead>

            {foreach from=$attributes item="attr_data"}
                {assign var="additional_class" value="cm-no-hide-input"}
                {assign var="status_display" value=""}
                <tr class="cm-row-status-{$attr_data.status|lower} {$additional_class}">
                    <td class="left mobile-hide">
                        {if $attr_data.object_type == "G"}
                            <input name="cp_attr_ids[]" type="checkbox" value="{$attr_data.cp_attr_id}" class="cm-item" />
                        {/if}
                    </td>
                    <td class="left" data-th="{__("position")}">
                        <input type="text" name="attr_data[{$attr_data.cp_attr_id}][position]" size="40" value="{$attr_data.position}" class="input-small input-hidden"/>
                    </td>
                    <td class="left" data-th="{__("cp_attr_name")}">
                        <input type="text" name="attr_data[{$attr_data.cp_attr_id}][cp_attr_name]" size="40" value="{$attr_data.cp_attr_name}" class="input-short input-hidden"/>
                        {include file="views/companies/components/company_name.tpl" object=$attr_data}
                        <div>
                            {__("cp_pr_is_mandatory")}: 
                            <input type="hidden" name="attr_data[{$attr_data.cp_attr_id}][required]" value="N" />
                            <input type="checkbox" name="attr_data[{$attr_data.cp_attr_id}][required]" value="Y" {if $attr_data.required == "Y"}checked="checked"{/if} />
                        </div>
                    </td>
                    <td data-th="{__("cp_attr_type")}">
                        {if $attr_data.object_type}
                            {if $attr_data.object_type == "E"}
                                {__("cp_testimonails_glob_attr")}
                            {elseif $attr_data.object_type == "G"}
                                {__("cp_global_prod_attr")}
                            {elseif $attr_data.object_type == "M"}
                                {__("cp_global_vend_attr")}
                            {/if}
                        {else}
                            -
                        {/if}
                    </td>
                    <td data-th="{__("cp_pr_view_type")}">
                        {if $attr_data.view_type == "D"}
                            {__("cp_pr_default_txt")}
                        {elseif $attr_data.view_type == "E"}
                            {foreach from=$attr_data.view_type_txt item="vt_data"}
                                {$val_type=$vt_data.type}
                                <span class="cp-pr__attr-vt_name">{$cp_pr_exterm_types.$val_type}:</span>
                                <div class="cp-pr__attr-vt">
                                    <input name="attr_data[{$attr_data.cp_attr_id}][view_type_txt][{$vt_data.type}][name_id]" type="hidden" value="{$vt_data.name_id}" class="input-short" />
                                    <input name="attr_data[{$attr_data.cp_attr_id}][view_type_txt][{$vt_data.type}][name]" type="text" value="{$vt_data.name}" class="input-short" />
                                </div>
                            {/foreach}
                        {/if}
                    </td>
                    <td class="right">
                        <div class="hidden-tools">
                        {capture name="tools_list"}
                            <li>{btn type="list" text=__("delete") class="cm-confirm" href="cp_pow_rev.delete_attr?cp_attr_id=`$attr_data.cp_attr_id`"}</li>
                        {/capture}
                        {dropdown content=$smarty.capture.tools_list}
                        </div>
                    </td>
                    <td class="nowrap right" data-th="{__("status")}">
                        {include file="common/select_popup.tpl" popup_additional_class="dropleft" display=$status_display id=$attr_data.cp_attr_id status=$attr_data.status hidden=false object_id_name="cp_attr_id" table="cp_power_ext_reviews"}
                    </td>
                </tr>
            {/foreach}
        </table>
        </div>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}

    {include file="common/pagination.tpl"}

    {capture name="buttons"}
        {capture name="tools_list"}
            {if $attributes}
                <li>{btn type="list" text=__("apply_to_products") dispatch="dispatch[cp_pow_rev.apply_attr_to_prods]" form="attr_manage_form"}</li>
                {if !$company_id || !"MULTIVENDOR"|fn_allowed_for}
                    <li>{btn type="list" text=__("cp_pr_apply_to_cats") dispatch="dispatch[cp_pow_rev.apply_attr_to_categors]" form="attr_manage_form"}</li>
                {/if}
                <li>{btn type="delete_selected" dispatch="dispatch[cp_pow_rev.m_delete_attrs]" form="attr_manage_form"}</li>
            {/if}
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
        {if $attributes}
            {include file="buttons/save.tpl" but_name="dispatch[cp_pow_rev.m_update_attrs]" but_role="submit-link" but_target_form="attr_manage_form"}
        {/if}
    {/capture}
    {capture name="adv_buttons"}
        {if "cp_pow_rev.add_attr"|fn_check_view_permissions}
            <div class="btn-group">
                <a class="btn cm-dialog-opener cm-dialog-auto-size"href="{"cp_pow_rev.add_attr"|fn_url}" title="{__("cp_add_attr")}" data-ca-target-id="cp_add_attr">
                    <i class="cs-icon dropdown-icon icon-plus"></i>
                </a>
            </div>
        {/if}
    {/capture}
    </form>
{/capture}


{include file="common/mainbox.tpl" title=__("cp_reviews_attrs") content=$smarty.capture.mainbox tools=$smarty.capture.tools select_languages=true buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}