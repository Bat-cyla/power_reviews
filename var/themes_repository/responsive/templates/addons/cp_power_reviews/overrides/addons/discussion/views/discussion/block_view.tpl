{if $back_disc}
    {$discussion=$back_disc}
{else}
    {$discussion = $object_id|fn_get_discussion:$object_type:true:$smarty.request}
{/if}
{if $object_type == "C"}
    {if $addons.cp_power_reviews.allow_most_bl_cat == "Y"}
        {$cp_type_for_most=$addons.cp_power_reviews.type_of_most_posts_cat}
    {else}
        {$cp_type_for_most="not_display"}
    {/if}
    {$m_show_help_bl=$addons.cp_power_reviews.show_most_help_block_cat}
    {$m_pos_limit=$addons.cp_power_reviews.barrier_for_positive_cat}
    {if $addons.cp_power_reviews.allow_ld_cat == "Y"}
        {$m_show_up_down="both"}
    {else}
        {$m_show_up_down="not_display"}
    {/if}
    {$slider_qty=$addons.cp_power_reviews.image_slider_qty_cat}
    {$slider_size=$addons.cp_power_reviews.slider_image_size_cat}
{else}
    {$cp_type_for_most="both"}
    {$m_show_up_down="both"}
    {$m_show_help_bl="N"}
    {$m_pos_limit=3}
    {$slider_qty=5}
    {$slider_size=150}
{/if}
{if $go_back_for_most}
    {$cp_type_for_most=$go_back_for_most.cp_type_for_most}
    {$m_show_up_down=$go_back_for_most.m_show_up_down}
    {$m_msg_show_date=$go_back_for_most.m_msg_show_date}
    {$m_show_help_bl=$go_back_for_most.m_show_help_bl}
    {$m_pos_limit=$go_back_for_most.m_pos_limit}
{/if}

{$m_rate_type="rate_stars"}
{$m_msg_show_date="Y"}
{$cp_skip_prev_wrap="4101"|fn_cp_power_reviews_check_version}
{if $discussion && $discussion.type != "D"}
    {if $discussion.cp_pr_slider}
        {include file="addons/cp_power_reviews/components/image_scroller.tpl"}
    {/if}
    {include file="common/subheader.tpl" title=$title}
    <div class="cp_pr__discussion-block" id="posts_list_{$object_id}">
        {if $discussion.posts}
            {if ($cp_type_for_most && $cp_type_for_most != "not_display" && ($discussion.most_h_post || $discussion.most_u_post) || ($m_show_help_bl == "Y" && $discussion.cp_top_help))}
                <div class="cp-disc__all-most-posts clearfix">
                    {if $cp_type_for_most && $cp_type_for_most != "not_display" && ($discussion.most_h_post || $discussion.most_u_post)}
                       <div class="nd-mp-container-nopadding  cp-disc__most-posts clearfix" id="cp_prod_most_posts_{$object_id}">
                           {if $cp_type_for_most == "both" || $cp_type_for_most == "most_h"}
                               {include file="addons/cp_power_reviews/components/mosts_posts.tpl" m_post=$discussion.most_h_post m_type="mh" m_text=__("cp_pr_most_positive")}
                            {/if}
                            {if $cp_type_for_most == "both" || $cp_type_for_most == "most_u"}
                                {include file="addons/cp_power_reviews/components/mosts_posts.tpl" m_post=$discussion.most_u_post m_type="mc" m_text=__("cp_pr_most_unhelpfull")}
                            {/if}
                        <!--cp_prod_most_posts_{$object_id}--></div>
                    {/if}
                    {if $m_show_help_bl == "Y" && $discussion.cp_top_help}
                        <div class="nd-mp-container-nopadding  cp-disc__most-posts cp-help-posts clearfix" id="cp_prod_most_help_posts_{$object_id}">
                            {$first_go=1}
                            <div class="cp-disc__help-posts-title">{__("cp_pr_most_helpfull")}</div>
                            {foreach from=$discussion.cp_top_help item="bl_most_h"}
                                {if $first_go == 1}
                                    {include file="addons/cp_power_reviews/components/mosts_posts.tpl" m_post=$bl_most_h m_type="mh1"}
                                {else}
                                    {include file="addons/cp_power_reviews/components/mosts_posts.tpl" m_post=$bl_most_h m_type="mh2"}
                                {/if}
                                {$first_go=2}
                            {/foreach}
                        <!--cp_prod_most_help_posts_{$object_id}--></div>
                    {/if}
                </div>
            {/if}
            <div class="cp-pr__posts-list" id="cp_posts_list_{$object_id}">
                {foreach from=$discussion.posts item=post}
                    <div class="">
                        {include file="addons/cp_power_reviews/components/cp_extend_review_post.tpl" post=$post discussion=$discussion}
                    </div>
                {/foreach}
                {if $settings.Security.secure_storefront != "partial"}
                    <div  id="cp_login_block_{$object_id}" class="hidden" title="{__("sign_in")}">
                        <div class="ty-login-popup">
                            {include file="views/auth/login_form.tpl" style="popup" id="cp_pr_popup`$object_id`"}
                        </div>
                    </div>
                {/if}
            <!--cp_posts_list_{$object_id}--></div>
        {else}
            <p class="ty-no-items">{__("no_data")}</p>
        {/if}
    <!--posts_list_{$object_id}--></div>
    {if $object_type == "P"}
        {$new_post_title = __("write_review")}
    {else}
        {$new_post_title = __("new_post")}
    {/if}
    {if $discussion.type !== "Addons\\Discussion\\DiscussionTypes::TYPE_DISABLED"|enum && ($cp_skip_prev_wrap || !$discussion.disable_adding)}
        {include
            file="addons/discussion/views/discussion/components/new_post_button.tpl"
            name=__("write_review")
            obj_id=$object_id
            object_type=$discussion.object_type
            show_container=true
        }
    {elseif "CRB"|strpos:$discussion.type !== false && !$discussion.disable_adding}
        <div class="ty-discussion-post__buttons buttons-container">
            {include file="buttons/button.tpl" but_id="opener_cp_new_post" but_text=$new_post_title but_role="submit" but_target_id="new_post_dialog_`$obj_id`" but_meta="cm-dialog-opener cm-dialog-auto-size ty-btn__primary" but_rel="nofollow"}
        </div>
        {if $object_type != "P"}
            {include file="addons/cp_power_reviews/components/cp_new_post.tpl" new_post_title=$new_post_title}
        {/if}
    {/if}
    {$block = ["block_id" => "discussion", "properties" => ["item_quantity" => 2, "scroll_per_page" => "Y", "not_scroll_automatically" => "Y", "outside_navigation" => true]]}
    {include file="common/scroller_init.tpl" block=$block}
{/if}

{include file="common/previewer.tpl"}