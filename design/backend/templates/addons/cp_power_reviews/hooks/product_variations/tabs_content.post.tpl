{if $product_data.variation_parent_product_id && ($runtime.company_id && "ULTIMATE"|fn_allowed_for || "MULTIVENDOR"|fn_allowed_for)}
    <div class="hidden" id="content_cp_pr_discussion">
        <fieldset>
            {$no_hide_input = false}
            {if "ULTIMATE"|fn_allowed_for}
                {$no_hide_input = true}
            {/if}

            {include file="addons/discussion/views/discussion_manager/components/allow_discussion.tpl"
                prefix="product_data"
                object_id=$product_data.product_id
                object_type="Addons\\Discussion\\DiscussionObjectTypes::PRODUCT"|enum
                title=__("discussion_title_product")
                no_hide_input=$no_hide_input
                discussion_default_type=$addons.discussion.product_discussion_type
            }
        </fieldset>
        {include file="addons/cp_power_reviews/components/prod_tab.tpl" only_meta=true}
    </div>
    {include file="addons/discussion/views/discussion_manager/components/discussion.tpl"
        object_company_id=$product_data.company_id
    }
{/if}