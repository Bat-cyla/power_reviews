{if $back_disc}
    {$discussion=$back_disc}
{elseif !$discussion}
    {$discussion=$object_id|fn_get_discussion:$object_type:true:$smarty.request}
{/if}
{if $object_type == "P"}
    {$new_post_title = __("write_review")}
{else}
    {$new_post_title = __("new_post")}
{/if}
{if $object_type == "P"}
    {if $addons.cp_power_reviews.allow_most_bl == "Y"}
        {$cp_type_for_most=$addons.cp_power_reviews.type_of_most_posts}
    {else}
        {$cp_type_for_most="not_display"}
    {/if}
    {if $addons.cp_power_reviews.allow_recom == "Y"}
        {$cp_show_recom_block=1}
        {$cp_show_recom_btn=$addons.cp_power_reviews.type_of_recomend_btn}
    {/if}
    {if $addons.cp_power_reviews.allow_ld == "Y"}
        {$m_show_up_down="both"}
    {else}
        {$m_show_up_down="not_display"}
    {/if}
    {$m_show_help_bl=$addons.cp_power_reviews.show_most_help_block}
    {$m_pos_limit=$addons.cp_power_reviews.barrier_for_positive}
    {$slider_qty=$addons.cp_power_reviews.image_slider_qty}
    {$slider_size=$addons.cp_power_reviews.slider_image_size}
{elseif $object_type == "M"}
    {if $addons.cp_power_reviews.allow_most_bl_vend == "Y"}
        {$cp_type_for_most=$addons.cp_power_reviews.type_of_most_posts_vend}
    {else}
        {$cp_type_for_most="not_display"}
    {/if}
    {if $addons.cp_power_reviews.allow_recom_vend == "Y"}
        {$cp_show_recom_block=1}
        {$cp_show_recom_btn=$addons.cp_power_reviews.type_of_recomend_btn_vend}
    {/if}
    {if $addons.cp_power_reviews.allow_ld_vend == "Y"}
        {$m_show_up_down="both"}
    {else}
        {$m_show_up_down="not_display"}
    {/if}
    {$m_show_help_bl=$addons.cp_power_reviews.show_most_help_block_vend}
    {$m_pos_limit=$addons.cp_power_reviews.barrier_for_positive_vend}
    {$slider_qty=$addons.cp_power_reviews.image_slider_qty_vend}
    {$slider_size=$addons.cp_power_reviews.slider_image_size_vend}
{elseif $object_type == "A" || $object_type == "B"}
    {if $addons.cp_power_reviews.allow_most_bl_page == "Y"}
        {$cp_type_for_most=$addons.cp_power_reviews.type_of_most_posts_page}
    {else}
        {$cp_type_for_most="not_display"}
    {/if}
    {if $addons.cp_power_reviews.allow_recom_page == "Y"}
        {$cp_show_recom_block=1}
        {$cp_show_recom_btn=$addons.cp_power_reviews.type_of_recomend_btn_page}
    {/if}
    {if $addons.cp_power_reviews.allow_ld_page == "Y"}
        {$m_show_up_down="both"}
    {else}
        {$m_show_up_down="not_display"}
    {/if}
    {$m_show_help_bl=$addons.cp_power_reviews.show_most_help_block_page}
    {$m_pos_limit=$addons.cp_power_reviews.barrier_for_positive_page}
    {$slider_qty=$addons.cp_power_reviews.image_slider_qty_page}
    {$slider_size=$addons.cp_power_reviews.slider_image_size_page}
{elseif $object_type == "C"}
    {if $addons.cp_power_reviews.allow_most_bl_cat == "Y"}
        {$cp_type_for_most=$addons.cp_power_reviews.type_of_most_posts_cat}
    {else}
        {$cp_type_for_most="not_display"}
    {/if}
    {if $addons.cp_power_reviews.allow_ld_cat == "Y"}
        {$m_show_up_down="both"}
    {else}
        {$m_show_up_down="not_display"}
    {/if}
    {$m_show_help_bl=$addons.cp_power_reviews.show_most_help_block_cat}
    {$m_pos_limit=$addons.cp_power_reviews.barrier_for_positive_cat}
    {$slider_qty=$addons.cp_power_reviews.image_slider_qty_cat}
    {$slider_size=$addons.cp_power_reviews.slider_image_size_cat}
{else}
    {$cp_show_recom_block=0}
    {$cp_show_recom_btn="all"}
    {$cp_type_for_most="both"}
    {$m_rate_type="rate_stars"}
    {$m_show_up_down="both"}
    {$m_msg_show_date="Y"}
    {$m_show_help_bl="N"}
    {$m_pos_limit=3}
    {$slider_qty=5}
    {$slider_size=150}
{/if}
{$cp_skip_prev_wrap="4101"|fn_cp_power_reviews_check_version}

{$m_rate_type="rate_stars"}
{$m_msg_show_date="Y"}

{if $go_back_for_most}
    {$cp_type_for_most=$go_back_for_most.cp_type_for_most}
    {$m_rate_type=$go_back_for_most.m_rate_type}
    {$m_show_up_down=$go_back_for_most.m_show_up_down}
    {$m_msg_show_date=$go_back_for_most.m_msg_show_date}
    {$m_show_help_bl=$go_back_for_most.m_show_help_bl}
    {$m_pos_limit=$go_back_for_most.m_pos_limit}
{/if}
{if $cp_det_page}
    {$cp_m_det_page=$cp_det_page}
{else}
    {if $details_page}
        {$cp_m_det_page="Y"}
    {else}
        {$cp_m_det_page="N"}
    {/if}
{/if}
{if $cp_type_for_most && $cp_type_for_most != "not_display" && ($discussion.most_h_post || $discussion.most_u_post) || ($m_show_help_bl == "Y" && $discussion.cp_top_help)}
    {$top_most_pn_exist=true}
{/if}

{if $discussion.object_data}
    <div class="cp-pr__review-page clearfix">
        {if $discussion.object_data.main_pair && ($discussion.object_data.main_pair.icon || $discussion.object_data.main_pair.detailed || $discussion.object_data.main_pair.image)}
            {$has_icon=true}
            <div class="cp-pr__review-page_image">
                {if $discussion.object_data.main_pair.image}
                    {$img_data = $discussion.object_data.main_pair.image}
                {else}
                    {$img_data = $discussion.object_data.main_pair}
                {/if}
                {include file="common/image.tpl" images=$img_data obj_id="`$discussion.object_id`_main" image_width="125" image_height="" show_detailed_link=false}
            </div>
        {/if}
        {if $object_type == "P"}
            <div class="cp-pr__review-page_info {if $has_icon}cp-pr__with-img{/if}">
                {$obj_id=$discussion.object_data.product_id}
                {include file="common/product_data.tpl" product=$discussion.object_data show_price_values=true show_price=true but_role="big" show_add_to_cart=true but_text=__("add_to_cart")}
                {*
                {$form_open="form_open_`$obj_id`"}
                {$smarty.capture.$form_open nofilter}
                *}
                {if $addons.cp_seo_templates.status != "A" || ($addons.cp_seo_templates.status == "A" && $addons.cp_seo_templates.use_custom_h1 != "Y")}
                    <div class="cp-pr__review-page_info_name">
                        <h1>
                            {if $discussion.cp_seo.h1}
                                {$discussion.cp_seo.h1}
                            {else}
                                {$discussion.object_data.product}
                            {/if}
                            {*
                            <a href="{"products.view?product_id=`$discussion.object_data.product_id`"|fn_url}">
                            </a>
                            *}
                        </h1>
                    </div>
                {/if}
                <div class="cp-pr__review-page_price">
                    {$price="price_`$obj_id`"}
                    {$smarty.capture.$price nofilter}
                </div>
                {*
                <div class="cp-pr__review-page_atc">
                    {$add_to_cart="add_to_cart_`$obj_id`"}
                    {$smarty.capture.$add_to_cart nofilter}
                </div>
                {$form_close="form_close_`$obj_id`"}
                {$smarty.capture.$form_close nofilter}
                *}
                <div class="cp-pr__review-page_more">
                    <a href="{"products.view?product_id=`$discussion.object_data.product_id`"|fn_url}">{__("cp_pr_see_descr_and_photo")}</a>
                </div>
            </div>
        {/if}
        <div class="cp-pr__review-page_info {if $has_icon}cp-pr__with-img{/if}">
            <div class="cp-pr__review-page_info_name">{$discussion.object_data.name}</div>
            <div class="cp-pr__review-page_info_descr">{$discussion.object_data.descr|strip_tags|truncate:460 nofilter}
            </div>
        </div>
    </div>
{/if}

{if $discussion && $discussion.type != "D"}
    {capture name="cp_rating_`$obj_id`"}
        {if $discussion.type == "R" || $discussion.type == "B"}
            {if $discussion.average_rating}
                {$average_rating = $discussion.average_rating}
            {/if}

            {if $average_rating > 0}
                <div class="cp-pr__avr-rate-main">
                    {include file="addons/discussion/views/discussion/components/stars.tpl"
                        stars=$average_rating|fn_get_discussion_rating
                    } 
                    <span class="cp-pr_total__av-rate">{$average_rating} {__("cp_pr_out_of")} 5</span>
                </div>
            {/if}
        {/if}
    {/capture}
    {if $no_capture}
        {assign var="capture_name" value="cp_rating_`$obj_id`"}
        {$smarty.capture.$capture_name nofilter}
    {/if}
    {$cur_result_id="pagination_contents_comments_`$object_id`"}
    <div class="clearfix cp_pr__discussion-block nd-discussion-box" id="{if $container_id}{$container_id}{else}content_discussion{/if}">
        
        {if $wrap == true}
            {capture name="content"}
            {include file="common/subheader.tpl" title=$title}
        {/if}
        {if $subheader}
            <h4>{$subheader}</h4>
        {/if}
        <div class="nd-discussion-box-right cp-disc__right-block">
            {if $discussion.posts}
                <div class="nd-discus">
                    <p class="nd-discus__header">{__("cp_reviews_or")}</p>
                    <div class="cp_ty-discussion__rating-wrapper">
                        {$rating="cp_rating_`$obj_id`"}{$smarty.capture.$rating nofilter}
                        {if $discussion.cp_pr_by_each_star}
                            {include file="addons/cp_power_reviews/components/by_each_star.tpl"}
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
            {/if}
        </div>
         <div class="nd-discussion-box-left cp-disc__left-block">
            {if $discussion.cp_pr_slider}
                <div class="cp-pr__video-slider">
                    {include file="addons/cp_power_reviews/components/image_scroller.tpl"}
                </div>
            {/if}
            {if !$skip_mosts && $cp_type_for_most && $cp_type_for_most != "not_display" && ($discussion.most_h_post || $discussion.most_u_post) || ($m_show_help_bl == "Y" && $discussion.cp_top_help)}
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
                            {assign var="first_go" value=1}
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
                {if $discussion.posts}
                    <div class="cp-prod-tab-rev-sorting">
                        {include file="addons/cp_power_reviews/components/review_sorting.tpl" res_id=$cur_result_id}
                    </div>
                    {include file="common/pagination.tpl" id=$cur_result_id extra_url="&selected_section=discussion" search=$discussion.search}
                    {foreach from=$discussion.posts item=post}
                            {include file="addons/cp_power_reviews/components/cp_extend_review_post.tpl" post=$post discussion=$discussion}
                    {/foreach}
                    {include file="common/pagination.tpl" id=$cur_result_id extra_url="&selected_section=discussion" search=$discussion.search}
                {else}
                    <p class="ty-no-items {if $cp_show_recom_block}nd-noitems-height-fix{/if}">{__("no_posts_found")}</p>
                {/if}
                {if $settings.Security.secure_storefront != "partial"}
                    <div  id="cp_login_block_{$object_id}" class="hidden" title="{__("sign_in")}">
                        <div class="ty-login-popup">
                            {include file="views/auth/login_form.tpl" style="popup" id="cp_pr_popup`$object_id`"}
                        </div>
                    </div>
                {/if}
            <!--cp_posts_list_{$object_id}--></div>

            {if "CRB"|strpos:$discussion.type !== false && !$discussion.disable_adding}
                <div class="ty-discussion-post__buttons buttons-container">
                    {include
                        file="addons/discussion/views/discussion/components/new_post_button.tpl"
                        name=__("write_review")
                        obj_id=$object_id
                        object_type=$discussion.object_type
                        locate_to_review_tab=true
                    }
                </div>
            {/if}
        </div>
        {if $wrap == true}
            {/capture}
            {$smarty.capture.content nofilter}
        {else}
            {capture name="mainbox_title"}{$title}{/capture}
        {/if}
    </div>
    {include file="common/previewer.tpl"}
{/if}
