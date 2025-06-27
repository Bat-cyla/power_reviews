{if $runtime.company_id && "ULTIMATE"|fn_allowed_for || "MULTIVENDOR"|fn_allowed_for}
    <div class="hidden" id="content_cp_reviews_attrs">
        {if $smarty.const.CP_PR_VARIATIONS_TYPE && in_array($smarty.const.CP_PR_VARIATIONS_TYPE, array("Y","S","NS"))}
            <div class="control-group">
                <label class="control-label">{__("cp_pr_replace_meta_attr_for_vars")}</label>
                <div class="controls">
                    <input type="hidden" name="cp_object_seo[cp_pr_replace_for_vars]" value="N">
                    <input type="checkbox" name="cp_object_seo[cp_pr_replace_for_vars]" value="Y">
                </div>
            </div>
        {/if}
        {include file="addons/cp_power_reviews/components/prod_tab.tpl"}
    </div>
{/if}