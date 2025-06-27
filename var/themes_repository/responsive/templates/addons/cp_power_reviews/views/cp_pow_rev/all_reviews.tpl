{if $back_disc}
    {$discussion=$back_disc}
{/if}
{$new_post_title = __("cp_new_testimonial_post")}

{if $addons.cp_power_reviews.allow_most_bl_ap == "Y"}
    {$cp_type_for_most=$addons.cp_power_reviews.type_of_most_posts_ap}
{else}
    {$cp_type_for_most="not_display"}
{/if}
{$m_show_help_bl=$addons.cp_power_reviews.show_most_help_block_ap}
{$m_pos_limit=$addons.cp_power_reviews.barrier_for_positive_ap}
{if $addons.cp_power_reviews.allow_ld_ap == "Y"}
    {$m_show_up_down="both"}
{else}
    {$m_show_up_down="not_display"}
{/if}
{$m_rate_type="rate_stars"}
{$m_msg_show_date="Y"}
{$cp_show_recom_block=0}

{if $go_back_for_most}
    {$cp_type_for_most=$go_back_for_most.cp_type_for_most}
    {$m_rate_type=$go_back_for_most.m_rate_type}
    {$m_show_up_down=$go_back_for_most.m_show_up_down}
    {$m_msg_show_date=$go_back_for_most.m_msg_show_date}
    {$m_show_help_bl=$go_back_for_most.m_show_help_bl}
    {$m_pos_limit=$go_back_for_most.m_pos_limit}
{/if}
{$ratings_type="rate_stars"}
{if $cp_type_for_most && $cp_type_for_most != "not_display" && ($discussion.most_h_post || $discussion.most_u_post) || ($m_show_help_bl == "Y" && $discussion.cp_top_help)}
    {$top_most_pn_exist=true}
{/if}

{capture name="section"}
    {include file="addons/cp_power_reviews/components/reviews_search_form.tpl"}
{/capture}
{if $discussion.search.object_type}
    {include file="common/section.tpl" section_title=__("search_options") section_content=$smarty.capture.section class="ty-search-form"}
{else}
    {include file="common/section.tpl" section_title=__("search_options") section_content=$smarty.capture.section class="ty-search-form" collapse=true}
{/if}

<div class="all-reviews cp_pr__discussion-block nd-discussion-box" id="{if $container_id}{$container_id}{else}content_discussion{/if}">
    {if $wrap == true}
        {capture name="content"}
        {include file="common/subheader.tpl" title=$title}
    {/if}
    {if $subheader}
        <h4>{$subheader}</h4>
    {/if}
    {if $cp_type_for_most && $cp_type_for_most != "not_display" && ($discussion.most_h_post || $discussion.most_u_post) || ($m_show_help_bl == "Y" && $discussion.cp_top_help)}
        <div class="cp-disc__all-most-posts clearfix">
            {if $cp_type_for_most && $cp_type_for_most != "not_display" && ($discussion.most_h_post || $discussion.most_u_post)}
                <div class="cp-disc__most-posts nd-mp-container-nopadding nd-all-rev-paddingfix clearfix" id="cp_prod_most_posts_{$object_id}">
                    {if $cp_type_for_most == "both" || $cp_type_for_most == "most_h"}
                        {include file="addons/cp_power_reviews/components/mosts_posts.tpl" m_post=$discussion.most_h_post m_type="mh" m_text=__("cp_pr_most_positive")}
                    {/if}
                    {if $cp_type_for_most == "both" || $cp_type_for_most == "most_u"}
                        {include file="addons/cp_power_reviews/components/mosts_posts.tpl" m_post=$discussion.most_u_post m_type="mc" m_text=__("cp_pr_most_unhelpfull")}
                    {/if}
                <!--cp_prod_most_posts_{$object_id}--></div>
            {/if}
            {if $m_show_help_bl == "Y" && $discussion.cp_top_help}
                <div class="cp-disc__most-posts nd-mp-container-nopadding nd-all-rev-paddingfix cp-help-posts clearfix" id="cp_prod_most_help_posts_{$object_id}">
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
    <div class="cp-disc__right-block nd-discussion-box-right nd-disc-full-width">
        {if $discussion.posts}
            <div class="cp-prod-tab-rev-sorting">
                <span class="cp-pr__total-reviews">
                    <a class="cp_ty-discussion__review-a cm-external-click" data-ca-scroll="content_discussion" data-ca-external-click-id="discussion">
                        {$discussion.search.total_items} {__("reviews", [$discussion.search.total_items])}
                    </a>
                </span>
                {include file="addons/cp_power_reviews/components/review_sorting.tpl" res_id="pagination_contents_comments_`$object_id`"}
            </div>
            {include file="common/pagination.tpl" id="pagination_contents_comments_`$object_id`" extra_url="&selected_section=discussion" search=$discussion.search}
        {/if}
        <div id="cp_posts_list_{$object_id}">
            {if $discussion.posts}
                {foreach from=$discussion.posts item=post}
                    <div class="">
                        {include file="addons/cp_power_reviews/components/cp_extend_review_post.tpl" post=$post discussion=$discussion}
                    </div>
                {/foreach}
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
        {if $discussion.posts}
            {include file="common/pagination.tpl" id="pagination_contents_comments_`$object_id`" extra_url="&selected_section=discussion" search=$discussion.search}
        {/if}
    </div>
    {if $wrap == true}
        {/capture}
        {$smarty.capture.content nofilter}
    {else}
        {capture name="mainbox_title"}{__("cp_pr_all_reviews")}{/capture}
    {/if}
</div>
{if !$details_page}
    {include file="common/previewer.tpl"}
{/if}
