{assign var="current_redirect_url" value=$config.current_url|fn_link_attach:"selected_section=discussion"|escape:url}
<div class="cp-pr__top-tools tools clearfix">
    <div class="pull-left">
        {if "discussion.m_delete"|fn_check_view_permissions}
            <input type="checkbox" name="delete_posts[{$post.post_id}]" id="delete_checkbox_{$post.post_id}"  class="pull-left cm-ite cp-left-tools-rev-checkbox" value="Y">
        {/if}
        <div class="pull-left cm-statuses {if $cp_pr_is_412}post__statuses{else}cp-left-tools-rev{/if}">
            {if "discussion.update"|fn_check_view_permissions}
                {if $cp_pr_is_412}
                    <span class="cm-status-a {if $post.status == "D"}hidden{/if}">
                        <span class="label label-success">{__("approved")}</span>
                        {btn type="text"
                            id="premoderation_disapprove"
                            title=__("disapprove")
                            icon="icon-thumbs-down"
                            icon_first=true
                            class="btn post__btn-status-switch cm-status-switch"
                            data=[
                                "data-ca-status"=>"D",
                                "data-ca-post-id"=>$post.post_id
                            ]
                        }
                    </span>
                    <span class="cm-status-d {if $post.status == "A"}hidden{/if}">
                        <span class="label label-important">{__("not_approved")}</span>
                        {btn type="text"
                            id="premoderation_disapprove"
                            title=__("approve")
                            icon="icon-thumbs-up"
                            icon_first=true
                            class="btn post__btn-status-switch cm-status-switch"
                            data=[
                                "data-ca-status"=>"A",
                                "data-ca-post-id"=>$post.post_id
                            ]
                        }
                    </span>
                {else}
                    <span class="cm-status-a {if $post.status == "D"}hidden{/if}">
                        <span class="label label-success">{__("approved")}</span>
                        <a class="cm-status-switch icon-thumbs-down cm-tooltip" title="{__("disapprove")}" data-ca-status="D" data-ca-post-id="{$post.post_id}"></a>
                    </span>
                    <span class="cm-status-d {if $post.status == "A"}hidden{/if}">
                        <span class="label label-important">{__("not_approved")}</span>
                        <a class="cm-status-switch icon-thumbs-up cm-tooltip" title="{__("approve")}" data-ca-status="A" data-ca-post-id="{$post.post_id}"></a>
                    </span>
                {/if}
            {else}
                <span class="cm-status-{$post.status|lower}">
                    {if $post.status == "A"}
                        <span class="label label-success">{__("approved")}</span>
                    {else}
                        <span class="label label-important">{__("not_approved")}</span>
                    {/if}
                </span>
            {/if}
            {if "discussion.delete"|fn_check_view_permissions}
                {if $cp_pr_is_412}
                    {btn type="text"
                        icon="icon-trash"
                        title="{__("delete")}"
                        class="btn post__btn-delete cm-confirm"
                        method="POST"
                        href="{"discussion.delete?post_id=`$post.post_id`&redirect_url=`$current_redirect_url`"|fn_url}"
                    }
                {else}
                    <a class="icon-trash cm-tooltip cm-confirm cm-post" href="{"discussion.delete?post_id=`$post.post_id`&redirect_url=`$current_redirect_url`"|fn_url}" title="{__("delete")}"></a>
                {/if}
            {/if}
        </div>
        <span class="muted">
            {include file="common/calendar.tpl" date_id="elm_date_holder_`$post.post_id`" date_name="cp_post_data[`$post.post_id`][date]" date_val=$post.timestamp|default:$smarty.const.TIME start_year=$settings.Company.company_start_year date_meta="post-date" show_time=true time_name="cp_post_data[`$post.post_id`][time]"}

            /
            {__("ip_address")}:&nbsp;{$post.ip_address}
        </span>
        {if $post.cp_pr_user_delete && $post.cp_pr_user_delete == "Y"}
            <div class="cp-pr__top-tools_deleted">{__("cp_pr_review_was_deleted_by_user")}</div>
        {/if}
    </div>
</div>
{if $show_object_link}
    <div class="cp-pr__object-link">
        <a href="{$post.object_data.url|fn_url}" class="" title="{$post.object_data.description}">{$post.object_data.description}</a>
    </div>
{/if}

<div class="cp-pr__settings-main clearfix">
    {if "discussion.update"|fn_check_view_permissions && ($addons.cp_power_reviews.show_usage == "Y" || ($addons.cp_power_reviews.show_purchase_label == "Y" 
        && (($discussion.object_type && $discussion.object_type == "P") || ($discussion_object_type && $discussion_object_type == "P"))))}
        <div class="cp-pr__settings_left">
            {if $addons.cp_power_reviews.show_usage == "Y"}
                <div class="control-group">
                    <label class="control-label">{__("cp_pr_experience")}:</label>
                    <div class="controls">
                        <select name="cp_post_data[{$post.post_id}][cp_pr_exp]">
                            <option value="C" {if !$post.cp_pr_exp || $post.cp_pr_exp == "C"}selected="selected"{/if}>{__("cp_pr_not_selected")}</option>
                            <option value="N" {if $post.cp_pr_exp == "N"}selected="selected"{/if}>{__("none")}</option>
                            <option value="W" {if $post.cp_pr_exp == "W"}selected="selected"{/if}>{__("cp_pr_experience_w")}</option>
                            <option value="M" {if $post.cp_pr_exp == "M"}selected="selected"{/if}>{__("cp_pr_experience_m")}</option>
                            <option value="Y" {if $post.cp_pr_exp == "Y"}selected="selected"{/if}>{__("cp_pr_experience_y")}</option>
                        </select>
                    </div>
                </div>
            {/if}
            {if $addons.cp_power_reviews.show_purchase_label == "Y" && (($discussion.object_type && $discussion.object_type == "P") || ($discussion_object_type && $discussion_object_type == "P"))}
                <div class="control-group">
                    <label class="control-label">{__("cp_pr_verified_purchase")}:</label>
                    <div class="controls">
                        <input type="hidden" name="cp_post_data[{$post.post_id}][cp_pr_verified_purchase]" value="N" />
                        <input type="checkbox" name="cp_post_data[{$post.post_id}][cp_pr_verified_purchase]" 
                            class="cm-ite cp-left-tools-rev-checkbox" value="Y" {if $post.cp_pr_verified_purchase == "Y"}checked="checked"{/if}>
                    </div>
                </div>
            {/if}
            {if $post.storefront_data}
                <div class="control-group">
                    <label class="control-label">{__("storefront")}:</label>
                    <div class="controls">
                        <span class="cp-pr__post-store-name">{$post.storefront_data.name}</span>
                    </div>
                </div>
            {/if}
        </div>
    {/if}
    <div class="cp-pr__settings_right {if !$show_object_link}cp-pr__top-padding_settings{/if}">
        <div class="">
            <span class="muted">
                <span class="cp-users-likes"><i class="icon-thumbs-up"></i><input type="text" value="{$post.cp_pos_post}" name="cp_post_data[{$post.post_id}][cp_pos_post]" class="input-mini" size="2"></span>
                <span class="cp-users-likes"><i class="icon-thumbs-down"></i><input type="text" value="{$post.cp_neg_post}" name="cp_post_data[{$post.post_id}][cp_neg_post]" class="input-mini" size="2"></span>
            </span>
            {if ($type == "R" || $type == "B") && $post.rating_value > 0}
                {if !$post.cp_attr_ratings}
                    {if $allow_save}
                        {include file="addons/discussion/views/discussion_manager/components/rate.tpl" rate_id="rating_`$post.post_id`" rate_value=$post.rating_value rate_name="cp_post_data[`$post.post_id`][rating_value]"}
                    {else}
                        {include file="addons/discussion/views/discussion_manager/components/stars.tpl" stars=$post.rating_value}
                    {/if}
                {/if}
            {/if}
        </div>
    </div>
</div>
<div class="cp-rev-attr-rate-block">
    {if ($type == "R" || $type == "B") && $post.rating_value > 0}
        {if $post.cp_attr_ratings}
            {if in_array($discussion.object_type, array("P","E","M")) || in_array($discussion_object_type, array("P","E","M"))}
                {if $allow_save}
                    <input type="hidden" name="cp_post_data[{$post.post_id}][cp_pr_common_rate_exist]"value=true>
                    <div class="control-group">
                        <label class="control-label">{__("cp_pr_common_rating")}:</label>
                        <div class="controls clearfix">
                            {include file="addons/discussion/views/discussion_manager/components/rate.tpl" rate_id="rating_`$post.post_id`" rate_value=$post.rating_value rate_name="cp_post_data[`$post.post_id`][rating_value]"}
                        </div>
                    </div>
                {else}
                    {include file="addons/discussion/views/discussion_manager/components/stars.tpl" stars=$post.rating_value}
                {/if}
            {/if}
            {foreach from=$post.cp_attr_ratings item="cp_attr"}
                {$rate_id = "rating_`$obj_prefix``$obj_id``$cp_attr.cp_attr_id``$post.post_id`"}
                <div class="control-group">
                    <label class="control-label">{$cp_attr.cp_attr_name}:</label>
                    {if $allow_save}
                        {if $cp_attr.view_type == "E" && $cp_attr.view_type_txt}
                            <div class="controls clearfix">
                                {include file="addons/cp_power_reviews/components/min_max_attr.tpl" rate_id=$rate_id rate_value=$cp_attr.rating rate_name="cp_post_data[{$post.post_id}][ratings][{$cp_attr.cp_attr_id}]"}
                            </div>
                        {else}
                            <div class="controls">
                                {include file="addons/discussion/views/discussion_manager/components/rate.tpl" rate_id=$rate_id rate_value=$cp_attr.rating rate_name="cp_post_data[{$post.post_id}][ratings][{$cp_attr.cp_attr_id}]"}
                            </div>
                        {/if}
                    {else}
                        <div class="controls">
                            <span class="cp-rev-rate-unedit-block">
                                {include file="addons/discussion/views/discussion_manager/components/stars.tpl" stars=$cp_attr.rating}
                            </span>
                        </div>
                    {/if}
                </div>
            {/foreach}
        {/if}
    {/if}
</div>
<div class="cp-rev-msg-block clearfix">
    <div class="cp-pr__fields-left">
        <div class="cp-pr__fields-left_main">
            <label class="cp_pr-post__adv_label">{__("author")}:</label>
            <input type="text" name="cp_post_data[{$post.post_id}][name]" value="{$post.name}" size="40" class="input-hidden">
        </div>
        {if $type == "C" || $type == "B"}
            <div class="cp-pr__fields-left_main">
                <label class="cp_pr-post__adv_label">{__("message")}:</label>
                <textarea name="cp_post_data[{$post.post_id}][message]" cols="80" rows="9" class="input-hidden">{$post.message}</textarea>
            </div>
        {/if}
    </div>
    {if in_array($type, array("C","B")) && ($show_title || $show_adv)}
        <div class="cp-pr__fields-right">
            {if $show_title}
                <div class="cp-pr__fields-left_main">
                    <label class="cp_pr-post__adv_label">{__("cp_pr_title_label")}:</label>
                    <input type="text" name="cp_post_data[{$post.post_id}][cp_pr_title]" value="{$post.cp_pr_title}" size="40" class="input-hidden" />
                </div>
            {/if}
            {if $show_adv}
                <div class="cp-pr__fields-left_main">
                    <label class="cp_pr-post__adv_label">{__("cp_pr_advantages")}:</label>
                    <textarea name="cp_post_data[{$post.post_id}][cp_pr_advantages]" cols="40" rows="3" class="input-hidden">{$post.cp_pr_advantages}</textarea>
                </div>
                <div class="cp-pr__fields-left_main">
                    <label class="cp_pr-post__adv_label">{__("cp_pr_disadvantages")}:</label>
                    <textarea name="cp_post_data[{$post.post_id}][cp_pr_disadvantages]" cols="40" rows="3" class="input-hidden">{$post.cp_pr_disadvantages}</textarea>
                </div>
            {/if}
        </div>
    {/if}
</div>
<div id="cp_reniew_img_block_{$post.post_id}">
    {if $post.cp_review_pairs}
        <div>{__("cp_pr_photo_mats")}:</div>
        <div class="cp-main_img_block clearfix">
            {$rev_img_width=180}
            {$rev_img_height=$rev_img_width}
            {foreach from=$post.cp_review_pairs key="key" item="rev_img"}
                <div class="cp-img-tool-string" id="cp_reniew_status_img_{$rev_img.pair_id}_I">
                    <div class="cp-single-img">
                        <a href="{$rev_img.detailed.image_path}" target="_blunk" >
                            {*
                            {include 
                                    file="common/image.tpl" 
                                    image=$rev_img.detailed 
                                    image_id=$rev_img.main_pair.image_id 
                                    image_width=$rev_img_width 
                                    image_height=$rev_img_width 
                            }
                            *}
                            <img src="{$rev_img.detailed.image_path}" width="{$rev_img_width}" height="{$rev_img_height}" alt="" >
                        </a>
                    </div>
                    <div class="cp-tools-img-rev">
                        <div class="cm-statuses {if $rev_img.status == "D"}cp-pr__lab-main_d{else}cp-pr__lab-main_a{/if}">
                            {if "discussion.update"|fn_check_view_permissions}
                                <span class="cm-status-a {if $rev_img.status == "D"}hidden{/if}" id="main_img_rev_span_a_{$rev_img.pair_id}_I">
                                    <span class="cp-pr__label label-success">{__("approved")}</span>
                                </span>
                                <span class="cm-status-d {if $rev_img.status == "A"}hidden{/if}" id="main_img_rev_span_d_{$rev_img.pair_id}_I">
                                    <span class="cp-pr__label label-important">{__("not_approved")}</span>
                                </span>
                            {else}
                                <span class="cm-status-{$rev_img.status|lower}">
                                    {if $rev_img.status == "A"}
                                        <span class="cp-pr__label label-success">{__("approved")}</span>
                                    {else}
                                        <span class="cp-pr__label label-important">{__("not_approved")}</span>
                                    {/if}
                                </span>
                            {/if}
                            
                        </div>
                        <div class="cp-pr__img-btns">
                            {if "discussion.update"|fn_check_view_permissions}
                                <a class="{if $rev_img.status == "D"}hidden{/if} cp-change-img-stat icon-thumbs-down cm-tooltip cm-ajax cm-post"  data-cp-type="I" id="main_img_rev_span_a_{$rev_img.pair_id}_I_btn" data-pair_id="{$rev_img.pair_id}" data-ca-target-id="cp_reniew_status_img_{$rev_img.pair_id}_I" href="{"tools.update_status?table=cp_review_images&id_name=post_image_id&id=`$rev_img.pair_id`&status=D"|fn_url}" title="{__("disapprove")}" data-ca-status="D" data-ca-post-id="{$rev_img.post_id}"></a>
                                <a class="{if $rev_img.status == "A"}hidden{/if} cp-change-img-stat icon-thumbs-up cm-tooltip cm-ajax cm-post"  data-cp-type="I" id="main_img_rev_span_d_{$rev_img.pair_id}_I_btn" data-pair_id="{$rev_img.pair_id}" data-ca-target-id="cp_reniew_status_img_{$rev_img.pair_id}_I" href="{"tools.update_status?table=cp_review_images&id_name=post_image_id&id=`$rev_img.pair_id`&status=A"|fn_url}" title="{__("approve")}" data-ca-status="A" data-ca-post-id="{$rev_img.post_id}"></a>
                            {/if}
                            {if "discussion.delete"|fn_check_view_permissions}
                                <a class="icon-trash cm-tooltip cm-confirm cm-ajax" data-ca-target-id="cp_reniew_img_block_{$post.post_id}" href="{"discussion.cp_del_post_img?post_id=`$post.post_id`&pair_id=`$rev_img.pair_id`"|fn_url}" title="{__("delete")}"></a>
                            {/if}
                        </div>
                    </div>
                <!--cp_reniew_status_img_{$rev_img.pair_id}_I--></div>
            {/foreach}
        </div>
    {else}
        <span class="hidden">&nbsp;</span>
    {/if}
<!--cp_reniew_img_block_{$post.post_id}--></div>

<div class="clearfix" id="cp_review_video_block_{$post.post_id}">
    {if $post.video_data || $cp_removing_preview}
        {$rev_video_width = 150}
        {$rev_video_height = 150}
        <div>{__("cp_pr_video_mats")}:</div>
        <div id="cp_reniew_status_img_{$post.video_data.video_id}_V">
            <div class="cp-pr__video-part clearfix">
                <div class="clearfix cp-pr__video-thumb">
                    <a class="cp-pr__post-video cm-dialog-opener cm-dialog-auto-size"
                        href="{"cp_pow_rev.play_review_video?post_id=`$post.post_id`&video_id=`$post.video_data.video_id`&youtube_id=`$post.video_data.youtube_id`"|fn_url}"
                        data-ca-target-id="cp_review_player_{$post.post_id}_{$post.video_data.video_id}"
                        title="{__("cp_pr_review_video")}"
                        data-cp-uid="{$post.video_data.youtube_id}"
                    >
                        {if $post.video_data.preview}
                            <img src="{$post.video_data.preview.detailed.image_path}" width="250" height="250" alt="" >
                        {elseif $post.video_data.preview_def}
                            <img src="{$post.video_data.preview_def}" width="250" height="250" alt=""/>
                        {/if}
                    </a>
                </div>
                <div class="cp-tools-img-rev">
                    <div class="cm-statuses {if $post.video_data.status == "D"}cp-pr__lab-main_d{else}cp-pr__lab-main_a{/if}">
                        {if "discussion.update"|fn_check_view_permissions}
                            <span class="cm-status-a {if $post.video_data.status == "D"}hidden{/if}" id="main_img_rev_span_a_{$post.video_data.video_id}_V">
                                <span class="cp-pr__label label-success">{__("approved")}</span>
                            </span>
                            <span class="cm-status-d {if $post.video_data.status == "A"}hidden{/if}" id="main_img_rev_span_d_{$post.video_data.video_id}_V">
                                <span class="cp-pr__label label-important">{__("not_approved")}</span>
                            </span>
                        {else}
                            <span class="cm-status-{$post.video_data.status|lower}">
                                {if $post.video_data.status == "A"}
                                    <span class="cp-pr__label label-success">{__("approved")}</span>
                                {else}
                                    <span class="cp-pr__label label-important">{__("not_approved")}</span>
                                {/if}
                            </span>
                        {/if}
                    </div>
                    <div class="cp-pr__img-btns">
                        {if "discussion.update"|fn_check_view_permissions}
                            <a class="{if $post.video_data.status == "D"}hidden{/if} cp-change-img-stat icon-thumbs-down cm-tooltip cm-ajax cm-post" data-cp-type="V" id="main_img_rev_span_a_{$post.video_data.video_id}_V_btn" data-pair_id="{$post.video_data.video_id}" data-ca-target-id="cp_reniew_status_img_{$post.video_data.video_id}_V" href="{"tools.update_status?table=cp_pr_video_links&id_name=video_id&id=`$post.video_data.video_id`&status=D"|fn_url}" title="{__("disapprove")}" data-ca-status="D" data-ca-post-id="{$post.video_data.post_id}"></a>
                            <a class="{if $post.video_data.status == "A"}hidden{/if} cp-change-img-stat icon-thumbs-up cm-tooltip cm-ajax cm-post"  data-cp-type="V" id="main_img_rev_span_d_{$post.video_data.video_id}_V_btn" data-pair_id="{$post.video_data.video_id}" data-ca-target-id="cp_reniew_status_img_{$post.video_data.video_id}_V" href="{"tools.update_status?table=cp_pr_video_links&id_name=video_id&id=`$post.video_data.video_id`&status=A"|fn_url}" title="{__("approve")}" data-ca-status="A" data-ca-post-id="{$post.video_data.post_id}"></a>
                        {/if}
                        {if "discussion.delete"|fn_check_view_permissions}
                            <a class="icon-trash cm-tooltip cm-confirm cm-ajax" data-ca-target-id="cp_review_video_block_{$post.post_id}" href="{"discussion.cp_del_post_img?post_id=`$post.post_id`&video_id=`$post.video_data.video_id`"|fn_url}" title="{__("delete")}"></a>
                        {/if}
                    </div>
                </div>
            </div>
        <!--cp_reniew_status_img_{$post.video_data.video_id}_V"--></div>
    {/if}
<!--cp_review_video_block_{$post.post_id}--></div>