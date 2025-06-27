{$show_img=true}
{$show_video=true}
{if $discussion.object_type && $discussion.object_type == "P"}
    {$cp_ob_type="P"}
    {if $addons.cp_power_reviews.allow_ld == "Y"}
        {$show_up_down="both"}
    {else}
        {$show_up_down="not_display"}
    {/if}
    {$msg_show_answ=$addons.cp_power_reviews.show_message_answer}
    {$msg_show_title=$addons.cp_power_reviews.allow_message_title}
    {$msg_show_adv=$addons.cp_power_reviews.allow_adv_disv}
    {$post_anon=$addons.cp_power_reviews.allow_anon_post_vote}
    
{elseif $discussion.object_type && $discussion.object_type == "E"}
    {$cp_ob_type="E"}
    {if $addons.cp_power_reviews.allow_ld_test == "Y"}
        {$show_up_down="both"}
    {else}
        {$show_up_down="not_display"}
    {/if}
    {$msg_show_answ=$addons.cp_power_reviews.show_test_message_answer}
    {$msg_show_title=$addons.cp_power_reviews.allow_message_title_test}
    {$msg_show_adv=$addons.cp_power_reviews.allow_adv_disv_test}
    {$post_anon=$addons.cp_power_reviews.allow_anon_post_vote_test}
    
{elseif $discussion.object_type && $discussion.object_type == "M"}
    {$cp_ob_type="M"}
    {if $addons.cp_power_reviews.allow_ld_vend == "Y"}
        {$show_up_down="both"}
    {else}
        {$show_up_down="not_display"}
    {/if}
    {$msg_show_answ=$addons.cp_power_reviews.show_vend_message_answer}
    {$msg_show_title=$addons.cp_power_reviews.allow_message_title_vend}
    {$msg_show_adv=$addons.cp_power_reviews.allow_adv_disv_vend}
    {$post_anon=$addons.cp_power_reviews.allow_anon_post_vote_vend}
    
{elseif $discussion.object_type && $discussion.object_type == "C"}
    {$cp_ob_type="C"}
    {if $addons.cp_power_reviews.allow_ld_cat == "Y"}
        {$show_up_down="both"}
    {else}
        {$show_up_down="not_display"}
    {/if}
    {$msg_show_answ=$addons.cp_power_reviews.show_message_answer_cat}
    {$msg_show_title=$addons.cp_power_reviews.allow_message_title_cat}
    {$msg_show_adv=$addons.cp_power_reviews.allow_adv_disv_cat}
    {$post_anon=$addons.cp_power_reviews.allow_anon_post_vote_cat}
    
{elseif $discussion.object_type && $discussion.object_type == "A" || $discussion.object_type == "B"}
    {$cp_ob_type="A"}
    {if $addons.cp_power_reviews.allow_ld_page == "Y"}
        {$show_up_down="both"}
    {else}
        {$show_up_down="not_display"}
    {/if}
    {$msg_show_answ=$addons.cp_power_reviews.show_message_answer_page}
    {$msg_show_title=$addons.cp_power_reviews.allow_message_title_page}
    {$msg_show_adv=$addons.cp_power_reviews.allow_adv_disv_page}
    {$post_anon=$addons.cp_power_reviews.allow_anon_post_vote_page}
    
{elseif $discussion.object_type && $discussion.object_type == "ALL"}
    {$cp_ob_type="ALL"}
    {if $addons.cp_power_reviews.allow_ld_ap == "Y"}
        {$show_up_down="both"}
    {else}
        {$show_up_down="not_display"}
    {/if}
    {$msg_show_answ=$addons.cp_power_reviews.show_ap_message_answer}
    {$msg_show_title=$addons.cp_power_reviews.allow_message_title_all}
    {$msg_show_adv=$addons.cp_power_reviews.allow_adv_disv_all}
    {if $post.object_type == "P"}
        {$post_anon=$addons.cp_power_reviews.allow_anon_post_vote}
    {elseif $post.object_type == "E"}
        {$post_anon=$addons.cp_power_reviews.allow_anon_post_vote_test}
    {elseif $post.object_type == "M"}
        {$post_anon=$addons.cp_power_reviews.allow_anon_post_vote_vend}
    {elseif $post.object_type == "C"}
        {$post_anon=$addons.cp_power_reviews.allow_anon_post_vote_cat}
    {elseif $post.object_type == "A" || $post.object_type == "B"}
        {$post_anon=$addons.cp_power_reviews.allow_anon_post_vote_page}
    {else}
        {$post_anon="N"}
    {/if}
    {if $addons.cp_power_reviews.show_image_in_post_ap == "N"}
        {$show_img=false}
    {/if}
    {if $addons.cp_power_reviews.show_videos_in_post_ap == "N"}
        {$show_video=false}
    {/if}
{else}
    {$show_up_down="not_display"}
    {$msg_show_answ="N"}
    {$msg_show_title="N"}
    {$msg_show_adv="N"}
    {$post_anon="N"}
{/if}
{$show_more_type = $addons.cp_power_reviews.show_more}
{$ty_av_rate="stars"}
{$rate_type="rate_stars"}
{$msg_border="Y"}
{$msg_show_date="Y"}

{if $cp_det_page}
    {$cp_is_det_page=$cp_det_page}
{else}
    {if $details_page}
        {$cp_is_det_page="Y"}
    {else}
        {$cp_is_det_page="N"}
    {/if}
{/if}
{$cp_skip_prev_wrap="4101"|fn_cp_power_reviews_check_version}
{*design indicator*}
{$modern_active=1}


<div class="cp-pr__post_main">
    {if !$post.cp_pr_user_delete || $post.cp_pr_user_delete == "N"}
        <div class="cp-pr__post_topline">
            <div class="cp-pr__post_topline_left">
                <div class="cp-pr__post_topline_icon"><span>{$post.name}</span></div>
                <div class="cp-pr__post_topline_author">
                    <div class="cp-pr__post_topline_author_name">{$post.name}</div>
                    <div class="cp-pr__post_header">
                        {if $addons.cp_power_reviews.show_purchase_label == "Y" && (($discussion.object_type && $discussion.object_type == "P") || ($post.object_type && $post.object_type == "P"))}
                            <div class="cp-pr__post_verified">
                                {if $post.cp_pr_verified_purchase == "Y"}
                                    <span class="cp-pr__post_verif-i"><i class="cp_pr-ico-ok-circled"></i><span class="cp-pr__post_verif-txt">{__("cp_pr_verified_purchase")}</span></span>
                                {else}
                                    <span class="cp-pr__post_not-verif-i"><i class="cp_pr-ico-cancel-circled"></i><span class="cp-pr__post_verif-txt">{__("cp_pr_not_verified_purchase")}</span></span>
                                {/if}
                            </div>
                        {/if}
                        {if $post.cp_pr_exp && $post.cp_pr_exp != "C" && ($discussion.object_type == "P" || ($post.object_type && $post.object_type == "P"))}
                            <div class="cp-pr__post_exp">
                                <strong>{__("cp_pr_experience")}:</strong> {if $post.cp_pr_exp == "N"}{__("no")}{elseif $post.cp_pr_exp == "W"}{__("cp_pr_experience_w")}{elseif $post.cp_pr_exp == "M"}{__("cp_pr_experience_m")}{elseif $post.cp_pr_exp == "Y"}{__("cp_pr_experience_y")}{/if}
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
            {if (($discussion.type == "R" || $discussion.type == "B") && ($post.cp_av_rating_post || $post.rating_value)) || ($msg_show_date && $msg_show_date == "Y")}
                <div class="cp-pr__post_topline_right">
                    {if $msg_show_date && $msg_show_date == "Y"}
                        <div class="cp-pr__post_topline_date">{$post.timestamp|date_format:"`$settings.Appearance.date_format`"}</div>
                    {/if}
                    {if ($discussion.type == "R" || $discussion.type == "B") && ($post.cp_av_rating_post || $post.rating_value)}
                        <div class="cp-pr__post_topline_rate">
                            {if $post.cp_av_rate_stars}
                                {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$post.cp_av_rate_stars}
                            {else}
                                {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$post.rating_value|fn_cp_power_reviews_discussion_rating}
                            {/if}
                        </div>
                    {/if}
                </div>
            {/if}
        </div>
        <div class="cp-pr__post_body">
            {if ($cp_ob_type == "ALL" || ($smarty.const.CP_PR_VARIATIONS_TYPE && in_array($smarty.const.CP_PR_VARIATIONS_TYPE, array("Y","NS")) && $cp_ob_type == "P")) && $post.object_data && $post.object_data.description}
                {$post_link=""}
                {if $post.object_type == "P"}
                    {if $post.object_data.extra_var_name}
                        {$post_link="products.view&product_id=`$post.object_id`"|fn_url}
                    {else}
                        {$post_link="cp_pow_rev.view&thread_id=`$post.thread_id`"|fn_url}
                    {/if}
                    {$object_prefix_link=__("cp_pr_product_txt")}
                {elseif $post.object_type == "M"}
                    {$post_link="companies.view&company_id=`$post.object_id`"|fn_url}
                    {$object_prefix_link=__("cp_pr_vendor_txt")}
                {elseif $post.object_type == "A"}
                    {$post_link="pages.view&page_id=`$post.object_id`"|fn_url}
                    {$object_prefix_link=__("cp_pr_page_txt")}
                {elseif $post.object_type == "C"}
                    {$post_link="categories.view&category_id=`$post.object_id`"|fn_url}
                    {$object_prefix_link=__("cp_pr_category_txt")}
                {elseif $post.object_type == "B"}
                    {$post_link="cp_blog.view&post_id=`$post.object_id`"|fn_url}
                    {$object_prefix_link=__("cp_pr_article_txt")}
                {/if}
                {if $post.object_data.description}
                    <div class="cp-pr__post_object-name-post">
                        {if $post_link}
                            {if $post.object_data.extra_var_name && $cp_ob_type != "ALL"}
                            {__("cp_pr_product_variant")}:&nbsp;
                            {else}
                            {__("cp_pr_review_about")} {$object_prefix_link}:&nbsp;
                            {/if}
                            <a class="cp-all-post-about-link" href="{$post_link}">
                                {if $post.object_data.extra_var_name}
                                    {if $cp_ob_type == "ALL"}
                                        {$post.object_data.description|truncate:60|nl2br nofilter}&nbsp;-&nbsp;
                                    {/if}
                                    {$post.object_data.extra_var_name}
                                {else}
                                    {$post.object_data.description|truncate:60|nl2br nofilter}
                                {/if}
                            </a>
                        {else}
                            {if $post.object_data.extra_var_name}
                                {if $cp_ob_type == "ALL"}
                                    {$post.object_data.description|truncate:60|nl2br nofilter}&nbsp;-&nbsp;
                                {/if}
                                {$post.object_data.extra_var_name}
                            {else}
                                {$post.object_data.description}
                            {/if}
                        {/if}
                    </div>
                {/if}
            {/if}
            {if $msg_show_title && $msg_show_title == "Y" && $post.cp_pr_title && ($discussion.type == "C" || $discussion.type == "B")}
                <div class="cp-pr__post_title">
                    <div class="cp-pr__post_title-txt">{$post.cp_pr_title|escape|nl2br nofilter}</div>
                </div>
            {/if}
            {if ($discussion.type == "R" || $discussion.type == "B") && $post.cp_attr_ratings}
                <div class="cp-pr__post_ratings">
                    {foreach from=$post.cp_attr_ratings item="p_attr"}
                        <div class="cp-pr__post_attr clearfix">
                            <div class="cp-pr__post_attr-name">
                                {$p_attr.cp_attr_name}{if $p_attr.view_type == "E" && $p_attr.attr_extr_name}:{/if}
                            </div>
                            {if $p_attr.view_type == "E" && $p_attr.attr_extr_name}
                                <div class="cp-pr__post_attr-extrem">{$p_attr.attr_extr_name}</div>
                            {else}
                                <div class="cp-pr__post_attr-stars">
                                    {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$p_attr.rating|fn_cp_power_reviews_discussion_rating}
                                </div>
                            {/if}
                        </div>
                    {/foreach}
                </div>
            {/if}
            {if $discussion.type == "C" || $discussion.type == "B"}
                <div class="cp-pr__post_msg">
                    {if $post.cp_pr_advantages && $msg_show_adv && $msg_show_adv == "Y"}
                        <div class="cp-pr__post_msg-section">
                            <div class="cp-pr__post_msg_label">{__("cp_pr_advantages")}</div>
                            <div class="cp-pr__post_msg-txt">
                                {if $post.short_adv}
                                    {include file="addons/cp_power_reviews/components/more_less.tpl" fb_msg=$post.short_adv 
                                        post_id="`$post.post_id`_adv" sb_msg=$post.cp_pr_advantages
                                    }
                                {else}
                                    {$post.cp_pr_advantages|escape|nl2br nofilter}
                                {/if}
                            </div>
                        </div>
                    {/if}
                    {if $post.cp_pr_disadvantages && $msg_show_adv && $msg_show_adv == "Y"}
                        <div class="cp-pr__post_msg-section">
                            <div class="cp-pr__post_msg_label">{__("cp_pr_disadvantages")}</div>
                            <div class="cp-pr__post_msg-txt">
                                {if $post.short_disadv}
                                    {include file="addons/cp_power_reviews/components/more_less.tpl" fb_msg=$post.short_disadv 
                                        post_id="`$post.post_id`_disadv"  sb_msg=$post.cp_pr_disadvantages
                                    }
                                {else}
                                    {$post.cp_pr_disadvantages|escape|nl2br nofilter}
                                {/if}
                            </div>
                        </div>
                    {/if}
                    <div class="cp-pr__post_msg-section">
                        <div class="cp-pr__post_msg_label">{__("cp_pr_comment_txt")}</div>
                        <div class="cp-pr__post_msg-txt">
                            {if $post.short_msg}
                                {include file="addons/cp_power_reviews/components/more_less.tpl" fb_msg=$post.short_msg post_id="`$post.post_id`_msg" sb_msg=$post.message}
                            {else}
                                {$post.message|escape|nl2br nofilter}
                            {/if}
                        </div>
                    </div>
                </div>
            {/if}
            {if $post.cp_review_pairs || $post.video_data}
                {$rev_img_width=$addons.cp_power_reviews.rev_image_width}
                {if !$rev_img_width}
                    {$rev_img_width=80}
                {/if}
                {$thumb_size = $rev_img_width * 2.2}
                <div class="cp-pr__post_images clearfix {if !$cp_skip_prev_wrap}cm-preview-wrapper{/if}">
                    {$cp_pr_order=0}
                    {if $post.video_data}
                        <div class="cp-post-bl-img cp-pr__post-video" data-cp-vid="{$post.video_data.video_id}" data-cp-ulink="{$smarty.const.CP_PR_YOUTUBE_PLAYER_URL}" data-cp-uid="{$post.video_data.youtube_id}" {if $post.video_data.preview}data-cp-obji="{$post.post_id}_{$post.video_data.preview.detailed_id}"{else}data-cp-obji="{$post.post_id}_cp_pr_def_icon"{/if} style="width:{$rev_img_width}px; height:{$rev_img_width}px; line-height: {$rev_img_width}px;">
                            {if $post.video_data.preview}
                                {include file="addons/cp_power_reviews/components/post_image.tpl" video_data=$post.video_data images=$post.video_data.preview link_class="cm-image-previewer cp-pr-previewer" 
                                    obj_id="`$post.post_id`_`$post.video_data.preview.detailed_id`" image_width=$thumb_size image_height=$thumb_size image_id="preview[post_images_`$post.post_id`]" show_detailed_link=true
                                }
                            {elseif $post.video_data.preview_def}
                                <a id="det_img_link_{$post.post_id}_cp_pr_def_icon" {if $cp_pr_order}data-ca-image-order="{$cp_pr_order}"{/if} data-ca-image-id="preview[post_images_{$post.post_id}]" class="cm-image-previewer cp-pr-previewer" data-ca-image-width="600" data-ca-image-height="600" href="{$post.video_data.preview_def}" title="">
                                    <img class="ty-pict cm-image" id="det_img_{$post.post_id}_cp_pr_def_icon" src="{$post.video_data.preview_def}" width="{$rev_img_width}" height="{$rev_img_width}" />
                                </a>
                            {/if}
                        </div>
                        {$cp_pr_order=$cp_pr_order + 1}
                    {/if}
                    {if $post.cp_review_pairs}
                        {$total_img_thumbs = count($post.cp_review_pairs)}
                        {foreach from=$post.cp_review_pairs item="post_img"}
                            <div class="cp-post-bl-img 
                                {if ($cp_pr_order >= 5 && $post.video_data) || (!$post.video_data && $total_img_thumbs > 4 && $cp_pr_order > 4)} hidden{/if}
                                {if ($total_img_thumbs >= 3 && $post.video_data && $cp_pr_order == 4) || (!$post.video_data && $total_img_thumbs > 4 && $cp_pr_order == 4)} cp-pr__is-plusimg{/if}
                                " 
                                style="width:{$rev_img_width}px; height:{$rev_img_width}px; line-height: {$rev_img_width}px;" 
                                {if $post.video_data}data-cp-vid="{$post.video_data.video_id}" 
                                    data-cp-ulink="{$smarty.const.CP_PR_YOUTUBE_PLAYER_URL}" 
                                    data-cp-uid="{$post.video_data.youtube_id}" {if $post.video_data.preview}data-cp-obji="{$post.post_id}_{$post.video_data.preview.detailed_id}"{else}data-cp-obji="{$post.post_id}_cp_pr_def_icon"{/if}{/if}
                            >
                            
                                {if ($total_img_thumbs >= 3 && $post.video_data && $cp_pr_order == 4) || (!$post.video_data && $total_img_thumbs > 4 && $cp_pr_order == 4)}
                                    {if $post.video_data}
                                        {$more_imgs_txt = $total_img_thumbs - $cp_pr_order + 1}
                                    {else}
                                        {$more_imgs_txt = $total_img_thumbs - $cp_pr_order}
                                    {/if}
                                {else}
                                    {$more_imgs_txt=0}
                                {/if}
                                {include file="addons/cp_power_reviews/components/post_image.tpl" more_imgs_txt=$more_imgs_txt images=$post_img link_class="cm-image-previewer cp-pr-previewer" obj_id="`$post.post_id`_`$post_img.detailed_id`" image_width=$thumb_size image_height=$thumb_size image_id="preview[post_images_`$post.post_id`]" show_detailed_link=true}
                            </div>
                            {$cp_pr_order=$cp_pr_order + 1}
                        {/foreach}
                    {/if}
                </div>
            {/if}
        </div>
        {if $show_up_down && $show_up_down != "not_display"}
            <div class="cp-pr__post_likes">
                <div class="cp-pr__post_likes-label">{__("cp_pr_does_it_helps")}</div>
                {if !$auth.user_id}
                    <span class="cp-rev-sign-link {if !$discussion.cp_show_signin}hidden{/if}">
                        <a href="" {if $settings.Security.secure_storefront != "partial"} data-ca-target-id="cp_login_block_{$object_id}" class="cm-dialog-opener cm-dialog-auto-size"{else} class=""{/if} rel="nofollow">{__("sign_in")}</a>
                    </span>
                {/if}
                {if $show_up_down == "only_up" || $show_up_down == "both"}
                    <span><a rel="nofollow" class="cm-ajax" data-ca-target-id="cp_posts_list_{$object_id}" href="{"discussion.cp_like_post&object_id={$object_id}&det_page={$cp_is_det_page}&object_type={$discussion.object_type}&cp_like=Y&post_id=`$post.post_id`&cp_sort_by=`$discussion.cp_sort_by`&selected_section=discussion&thread_id=`$discussion.thread_id`"|fn_url}"><i class="cp-rev-icons-up-down cp_pr-ico-like"></i></a><span class="cp-users-likes">{$post.cp_pos_post}</span></span>
                {/if}
                {if $show_up_down == "only_down" || $show_up_down == "both"}
                    <span><a rel="nofollow" class="cm-ajax" data-ca-target-id="cp_posts_list_{$object_id}" href="{"discussion.cp_like_post&object_id={$object_id}&det_page={$cp_is_det_page}&object_type={$discussion.object_type}&cp_like=N&post_id=`$post.post_id`&cp_sort_by=`$discussion.cp_sort_by`&selected_section=discussion&thread_id=`$discussion.thread_id`"|fn_url}"><i class="cp-rev-icons-up-down cp_pr-ico-dislike"></i></a><span class="cp-users-likes">{$post.cp_neg_post}</span></span>
                {/if}
            </div>
        {/if}
    {else}
        <div class="cp-pr__post_user-deleted">
            <span>{__("cp_pr_review_was_deleted_by_user")}</span>
        </div>
    {/if}
    {if $discussion.type != "R" && $discussion.type != "D" && $msg_show_answ && $msg_show_answ == "Y" && $post.cp_admin_answ && $post.cp_admin_id}
        <div class="cp-pr__post_answer">
            <div class="cp-pr__post_answer_inside">
                <div class="cp-pr__post_answer_top">
                    <div class="cp-pr__post_answer_top-left">
                        <div class="cp-pr__post_answer_top-icon"><span>{$post.cp_admin_id}</span></div>
                        <div class="cp-pr__post_answer_top-author">{__("cp_dm_answered_by")}: {$post.cp_admin_id}</div>
                    </div>
                    <div class="cp-pr__post_answer_top-right">
                        <div class="cp-pr__post_answer_top-date">
                            {$post.cp_admin_answ_time|date_format:"`$settings.Appearance.date_format`"}
                        </div>
                    </div>
                </div>
                <div class="cp-pr__post_answer_bot ty-wysiwyg-content">
                    {if $post.answ_short_msg}
                        {include file="addons/cp_power_reviews/components/more_less.tpl" fb_class="" fb_msg=$post.answ_short_msg 
                            post_id="`$post.post_id`_answ" sb_class="" sb_msg=$post.cp_admin_answ nofilter=true
                        }
                    {else}
                        <div>{$post.cp_admin_answ nofilter}</div>
                    {/if}
                </div>
            </div>
        </div>
    {/if}
</div>
