{if $addons.discussion.company_discussion_type != "D"}
    {$new_post_title = __("write_review")}
    {$object_type="M"}
    {$obj_id=$company_id}
    {$return_current_url = $config.current_url|escape:url}
    {$is_company_and_post_after_purchase_enabled = $object_type == "Addons\\Discussion\\DiscussionObjectTypes::COMPANY"|enum && $settings.discussion.companies.company_only_buyers == "Y"}
    {if !$auth.user_id && ($is_product_and_post_after_purchase_enabled || $is_company_and_post_after_purchase_enabled)}
        {$but_id = "opener_discussion_login_form_new_post_`$obj_prefix``$obj_id`"}
        {$target_id = "new_discussion_post_login_form_popup"}
        {$but_href = fn_url("discussion.get_user_login_form?return_url=`$return_current_url`")}
        
        <a id="{$but_id}" class="cm-dialog-opener cm-dialog-auto-size cp-pr__review-write" data-ca-target-id="{$target_id}" rel="nofollow" title="{__("sign_in")}" href="{$but_href}"><i class="ty-icon-star"></i>{$new_post_title}</a>
    {else}
        {$but_id = "opener_new_post_`$obj_prefix``$obj_id`"}
        {$but_href = fn_url("discussion.get_new_post_form?object_type=`$object_type`&object_id=`$obj_id`&obj_prefix=`$obj_prefix`&post_redirect_url=`$return_current_url`")}
        {$target_id = "new_post_dialog_`$obj_prefix``$obj_id`"}
        
        <a id="{$but_id}" class="cp-pr__review-write cm-dialog-opener cm-dialog-auto-size" data-ca-target-id="{$target_id}" rel="nofollow" href="{$but_href}" title="{__("write_review")}"><i class="ty-icon-star"></i>{$new_post_title}</a>
    {/if}
{/if}