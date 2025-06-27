{if $back_disc}
    {$discussion=$back_disc}
{else}
    {$discussion=$object_id|fn_get_discussion:$object_type:true:$smarty.request}
{/if}
{if $object_type == "P"}
    {$new_post_title = __("write_review")}
{else}
    {$new_post_title = __("new_post")}
{/if}
{if $object_type == "A" || $object_type == "B"}
    {if $addons.cp_power_reviews.allow_most_bl == "Y"}
        {$cp_type_for_most=$addons.cp_power_reviews.type_of_most_posts_page}
    {else}
        {$cp_type_for_most="not_display"}
    {/if}
    {$m_show_help_bl=$addons.cp_power_reviews.show_most_help_block_page}
    {$m_pos_limit=$addons.cp_power_reviews.barrier_for_positive_page}
    {if $addons.cp_power_reviews.allow_ld_page == "Y"}
        {$m_show_up_down="both"}
    {else}
        {$m_show_up_down="not_display"}
    {/if}
    {$m_msg_show_date="Y"}
    {$slider_qty=$addons.cp_power_reviews.image_slider_qty_page}
    {$slider_size=$addons.cp_power_reviews.slider_image_size_page}
{elseif $object_type == "M"}
    
    {if $addons.cp_power_reviews.allow_recom_vend == "Y"}
        {$cp_show_recom_block=1}
        {$cp_show_recom_btn=$addons.cp_power_reviews.type_of_recomend_btn_vend}
    {/if}
    {if $addons.cp_power_reviews.allow_most_bl_vend == "Y"}
        {$cp_type_for_most=$addons.cp_power_reviews.type_of_most_posts_vend}
    {else}
        {$cp_type_for_most="not_display"}
    {/if}
    {$m_show_help_bl=$addons.cp_power_reviews.show_most_help_block_vend}
    {$m_pos_limit=$addons.cp_power_reviews.barrier_for_positive_vend}
    {if $addons.cp_power_reviews.allow_ld_vend == "Y"}
        {$m_show_up_down="both"}
    {else}
        {$m_show_up_down="not_display"}
    {/if}
    {$m_msg_show_date="Y"}
    {$slider_qty=$addons.cp_power_reviews.image_slider_qty_vend}
    {$slider_size=$addons.cp_power_reviews.slider_image_size_vend}
    
{elseif $object_type == "E"}
    
    {$cp_type_for_most="not_display"}
    {$m_show_help_bl="Y"}
    {$m_pos_limit=4}
    {if $addons.cp_power_reviews.allow_ld_test == "Y"}
        {$m_show_up_down="both"}
    {else}
        {$m_show_up_down="not_display"}
    {/if}
    {$m_msg_show_date="Y"}
    {$slider_qty=$addons.cp_power_reviews.image_slider_qty_test}
    {$slider_size=$addons.cp_power_reviews.slider_image_size_test}
{/if}
{if $go_back_for_most}
    {$cp_type_for_most=$go_back_for_most.cp_type_for_most}
    {$m_show_up_down=$go_back_for_most.m_show_up_down}
    {$m_msg_show_date=$go_back_for_most.m_msg_show_date}
    {$m_show_help_bl=$go_back_for_most.m_show_help_bl}
    {$m_pos_limit=$go_back_for_most.m_pos_limit}
{/if}
{$cp_skip_prev_wrap="4101"|fn_cp_power_reviews_check_version}

{if $discussion && $discussion.type != "D"}
    {if $discussion.cp_pr_slider}
        {include file="addons/cp_power_reviews/components/image_scroller.tpl"}
        {include file="common/subheader.tpl" title=__("cp_pr_reviews_txt")}
    {/if}
    {if $cp_type_for_most && $cp_type_for_most != "not_display" && ($discussion.most_h_post || $discussion.most_u_post) || ($m_show_help_bl == "Y" && $discussion.cp_top_help)}
        {$top_most_pn_exist=true}
    {/if}
    <div class="clearfix cp_pr__discussion-block" id="{if $container_id}{$container_id}{else}content_discussion{/if}">
        {if $wrap == true}
            {capture name="content"}
            {include file="common/subheader.tpl" title=$title}
        {/if}

        {if $subheader}
            <h4>{$subheader}</h4>
        {/if}
        {$ratings_type="rate_stars"}
        
        <div class="nd-discussion-box-right {if !$cp_show_recom_block && (!$discussion.posts || $object_type == "O" || (!$datails_page && !in_array($object_type, array("C","A","E","M","B"))))}nd-disc-full-width{/if} cp-disc__right-block">
            <div class="nd-discus">
                <p class="nd-discus__header">{__("cp_reviews_or")}</p>
                <div class="cp_ty-discussion__rating-wrapper">
                    {if $object_type == "E"}
                        {if in_array($addons.discussion.home_page_testimonials, ['B', 'R'])}
                            {if $discussion.average_rating}
                                {$average_rating = $discussion.average_rating}
                            {/if}
                        {/if}
                    {else}
                        {if $discussion.type == "R" || $discussion.type == "B"}
                            {if $discussion.average_rating}
                                {$average_rating = $discussion.average_rating}
                            {/if}
                        {/if}
                    {/if}
                    {if $average_rating > 0}
                        <div class="cp-pr__avr-rate-main">
                            {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$average_rating|fn_get_discussion_rating}
                            <span class="cp-pr_total__av-rate">{$average_rating} {__("cp_pr_out_of")} 5</span>
                        </div>
                    {/if}
                    {if $discussion.posts}
                        {if $discussion.cp_pr_by_each_star}
                            {include file="addons/cp_power_reviews/components/by_each_star.tpl"}
                        {/if}
                    {/if}
                </div>
                {if $cp_show_recom_block}
                    {include file="addons/cp_power_reviews/components/recommendation_block.tpl" object_id=$object_id}
                {/if}
            </div>
            {include file="addons/cp_power_reviews/components/before_wrire_review.tpl"}
            {if $discussion.cp_all_prod_attrs && $discussion.cp_show_sred_block}
                {include file="addons/cp_power_reviews/components/average_attr_block.tpl" object_id=$object_id}
            {/if}
        </div>
        
        {if in_array($object_type, array("E","M","C","A","B"))}
            <div class="nd-discussion-box-left cp-disc__left-block">
                {if $discussion.posts}
                    {if $cp_type_for_most && $cp_type_for_most != "not_display" && ($discussion.most_h_post || $discussion.most_u_post) || ($m_show_help_bl == "Y" && $discussion.cp_top_help)}
                        <div class="cp-disc__all-most-posts clearfix">
                            {if $cp_type_for_most && $cp_type_for_most != "not_display" && ($discussion.most_h_post || $discussion.most_u_post)}
                                <div class="nd-mp-container cp-disc__most-posts clearfix" id="cp_prod_most_posts_{$object_id}">

                                    {if $cp_type_for_most == "both" || $cp_type_for_most == "most_h"}
                                        {include file="addons/cp_power_reviews/components/mosts_posts.tpl" m_post=$discussion.most_h_post m_type="mh" m_text=__("cp_pr_most_positive")}
                                    {/if}
                                    {if $cp_type_for_most == "both" || $cp_type_for_most == "most_u"}
                                        {include file="addons/cp_power_reviews/components/mosts_posts.tpl" m_post=$discussion.most_u_post m_type="mc" m_text=__("cp_pr_most_unhelpfull")}
                                    {/if}
                                <!--cp_prod_most_posts_{$object_id}--></div>
                            {/if}
                            {if $m_show_help_bl == "Y" && $discussion.cp_top_help}
                                <div class="nd-mp-container cp-disc__most-posts cp-help-posts clearfix" id="cp_prod_most_help_posts_{$object_id}">
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
                    <div class="cp-prod-tab-rev-sorting">
                        {include file="addons/cp_power_reviews/components/review_sorting.tpl" res_id="pagination_contents_comments_`$object_id`"}
                    </div>
                    <div id="cp_posts_list_{$object_id}">
                        {if $discussion.posts}
                            {include file="common/pagination.tpl" id="pagination_contents_comments_`$object_id`" extra_url="&selected_section=discussion" search=$discussion.search}
                            {foreach from=$discussion.posts item=post}
                                <div class="">
                                    {include file="addons/cp_power_reviews/components/cp_extend_review_post.tpl" post=$post discussion=$discussion}
                                </div>
                            {/foreach}
                            {include file="common/pagination.tpl" id="pagination_contents_comments_`$object_id`" extra_url="&selected_section=discussion" search=$discussion.search}
                        {else}
                            <p class="ty-no-items">{__("no_posts_found")}</p>
                        {/if}
                        {if $settings.Security.secure_storefront != "partial"}
                            <div  id="cp_login_block_{$object_id}" class="hidden" title="{__("sign_in")}">
                                <div class="ty-login-popup">
                                    {include file="views/auth/login_form.tpl" style="popup" id="cp_pr_popup`$object_id`"}
                                </div>
                            </div>
                        {/if}
                    <!--cp_posts_list_{$object_id}--></div>
                {else}
                    <p class="ty-no-items">{__("no_posts_found")}</p>
                {/if}
                {if $discussion.type !== "Addons\\Discussion\\DiscussionTypes::TYPE_DISABLED"|enum && ($cp_skip_prev_wrap || !$discussion.disable_adding)}
                    {include
                        file="addons/discussion/views/discussion/components/new_post_button.tpl"
                        name=__("write_review")
                        obj_id=$object_id
                        object_type=$discussion.object_type
                        locate_to_review_tab=true
                        show_container=true
                    }
                {elseif "CRB"|strpos:$discussion.type !== false && !$discussion.disable_adding}
                    <div class="cp_ty-discussion-post__buttons buttons-container">
                        {include file="buttons/button.tpl" but_id="opener_cp_new_post" but_text=$new_post_title but_role="submit" but_target_id="new_post_dialog_`$obj_id`" but_meta="cm-dialog-opener cm-dialog-auto-size ty-btn__primary" but_rel="nofollow"}
                    </div>
                    {include file="addons/cp_power_reviews/components/cp_new_post.tpl" new_post_title=$new_post_title}
                {/if}
            </div>
        {/if}

        {if $wrap == true}
            {/capture}
            {$smarty.capture.content nofilter}
        {else}
            {capture name="mainbox_title"}{$title}{/capture}
        {/if}
    </div>
    {if !$details_page}
        {include file="common/previewer.tpl"}
    {/if}
{/if}
