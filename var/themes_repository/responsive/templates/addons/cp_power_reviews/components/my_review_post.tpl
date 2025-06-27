{$allow_img=false}
{$allow_title=false}
{$required_title=true}
{$allow_videos=false}
{$allow_adv_disadv=false}
{$required_adv_disadv=true}
{$allow_common_rate=false}
{$is_form_readonly=true}
{$msg_show_answ="N"}

{if $r_post.object_type == "P"}
    {if $addons.cp_power_reviews.show_image_uploader == "Y"}
        {$allow_img=true}
    {/if}
    {if $addons.cp_power_reviews.show_video_uploader == "Y"}
        {$allow_videos=true}
    {/if}
    {if $addons.cp_power_reviews.allow_message_title == "Y"}
        {$allow_title=true}
        {if $addons.cp_power_reviews.required_title == "N"}
            {$required_title=false}
        {/if}
    {/if}
    {if $addons.cp_power_reviews.allow_adv_disv == "Y"}
        {$allow_adv_disadv=true}
        {if $addons.cp_power_reviews.adv_disv_required == "N"}
            {$required_adv_disadv=false}
        {/if}
    {/if}
    {$msg_show_answ=$addons.cp_power_reviews.show_message_answer}
    {$allow_common_rate=true}
{elseif $r_post.object_type == "E"}
    {if $addons.cp_power_reviews.show_image_uploader_test == "Y"}
        {$allow_img=true}
    {/if}
    {if $addons.cp_power_reviews.show_video_uploader_test == "Y"}
        {$allow_videos=true}
    {/if}
    {if $addons.cp_power_reviews.allow_message_title_test == "Y"}
        {$allow_title=true}
        {if $addons.cp_power_reviews.required_title_test == "N"}
            {$required_title=false}
        {/if}
    {/if}
    {if $addons.cp_power_reviews.allow_adv_disv_test == "Y"}
        {$allow_adv_disadv=true}
        {if $addons.cp_power_reviews.adv_disv_required_test == "N"}
            {$required_adv_disadv=false}
        {/if}
    {/if}
    {$msg_show_answ=$addons.cp_power_reviews.show_test_message_answer}
    {$allow_common_rate=true}
{elseif $r_post.object_type == "M"}
    {if $addons.cp_power_reviews.show_image_uploader_vend == "Y"}
        {$allow_img=true}
    {/if}
    {if $addons.cp_power_reviews.show_video_uploader_vend == "Y"}
        {$allow_videos=true}
    {/if}
    {if $addons.cp_power_reviews.allow_message_title_vend == "Y"}
        {$allow_title=true}
        {if $addons.cp_power_reviews.required_title_vend == "N"}
            {$required_title=false}
        {/if}
    {/if}
    {if $addons.cp_power_reviews.allow_adv_disv_vend == "Y"}
        {$allow_adv_disadv=true}
        {if $addons.cp_power_reviews.adv_disv_required_vend == "N"}
            {$required_adv_disadv=false}
        {/if}
    {/if}
    {$msg_show_answ=$addons.cp_power_reviews.show_vend_message_answer}
    {$allow_common_rate=true}
{elseif $r_post.object_type == "C"}
    {if $addons.cp_power_reviews.show_image_uploader_cat == "Y"}
        {$allow_img=true}
    {/if}
    {if $addons.cp_power_reviews.show_video_uploader_cat == "Y"}
        {$allow_videos=true}
    {/if}
    {if $addons.cp_power_reviews.allow_message_title_cat == "Y"}
        {$allow_title=true}
        {if $addons.cp_power_reviews.required_title_cat == "N"}
            {$required_title=false}
        {/if}
    {/if}
    {if $addons.cp_power_reviews.allow_adv_disv_cat == "Y"}
        {$allow_adv_disadv=true}
        {if $addons.cp_power_reviews.adv_disv_required_cat == "N"}
            {$required_adv_disadv=false}
        {/if}
    {/if}
    {$msg_show_answ=$addons.cp_power_reviews.show_message_answer_cat}
{elseif $r_post.object_type == "A" || $r_post.object_type == "B"}
    {if $addons.cp_power_reviews.show_image_uploader_page == "Y"}
        {$allow_img=true}
    {/if}
    {if $addons.cp_power_reviews.show_video_uploader_page == "Y"}
        {$allow_videos=true}
    {/if}
    {if $addons.cp_power_reviews.allow_message_title_page == "Y"}
        {$allow_title=true}
        {if $addons.cp_power_reviews.required_title_page == "N"}
            {$required_title=false}
        {/if}
    {/if}
    {if $addons.cp_power_reviews.allow_adv_disv_page == "Y"}
        {$allow_adv_disadv=true}
        {if $addons.cp_power_reviews.adv_disv_required_page == "N"}
            {$required_adv_disadv=false}
        {/if}
    {/if}
    {$msg_show_answ=$addons.cp_power_reviews.show_message_answer_page}
{/if}
{$cur_time=time()}
{if $addons.cp_power_reviews.allow_review_e == "Y" && $r_post.cp_pr_can_edit == "Y"}
    {if $addons.cp_power_reviews.minutes_for_editing && $r_post.timestamp > ($cur_time - 60*$addons.cp_power_reviews.minutes_for_editing)}
        {$is_form_readonly = false}
    {elseif !$addons.cp_power_reviews.minutes_for_editing}
        {$is_form_readonly = false}
    {/if}
{/if}

<form action="{""|fn_url}" method="post" class="" name="review_list_item_form" id="review_list_item_form" enctype="multipart/form-data">
    <input type="hidden" name="post_data[post_id]" value="{$r_post.post_id}" />
    <input type="hidden" name="redirect_url" value="{$config.current_url}" />
    <input type="hidden" name="post_data[name]" value="{$r_post.name}" />
    <input type="hidden" name="post_data[thread_id]" value="{$r_post.thread_id}" />
    
    <div class="cp-pr__my-rev_list-item" id="cp_my_reviews_item_{$r_post.post_id}">
        {if $r_post.object_data}
            <div class="cp-pr__my-rev_object">
                <span class="cp-pr__my-rev_object-label">{__("cp_pr_review_about")}</span><span class="cp-pr__my-rev_object-name">
                    <a href="{"`$r_post.object_data.url`"|fn_url}">{$r_post.object_data.description}</a>
                </span>
            </div>
        {/if}
        <div class="cp-pr__my-rev_time">
            <strong>{__("date")}:</strong>&nbsp;{$r_post.timestamp|date_format:"`$settings.Appearance.date_format`"}
        </div>
        <div class="cp-pr__my-rev_status">
            <strong>{__("status")}:</strong>&nbsp;{if $r_post.status == "A"}{__("approved")}{else}{__("not_approved")}{/if}
        </div>
        {if $r_post.type == "R" || $r_post.type == "B"}
            {if $allow_common_rate}
                <input type="hidden" name="post_data[cp_pr_common_rate_exist]"value=true>
                <div class="cp-pr__new-attr-block_avg">
                    {$rate_id = "rating_`$r_post.post_id`"}
                    <label for="{$rate_id}" class="ty-control-group__label cm-required cm-multiple-radios">{__("cp_pr_common_rating")}</label>
                    <div class="cp-new-rew-stars-block">
                        {if $is_form_readonly}
                            {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$r_post.rating_value|fn_cp_power_reviews_discussion_rating}
                        {else}
                            {include file="addons/cp_power_reviews/components/usual_rate.tpl" rate_id=$rate_id rate_value=$r_post.rating_value rate_name="post_data[rating_value]"}
                        {/if}
                    </div>
                </div>
            {/if}
            <div class="cp-pr__new_attr-block">
                {foreach from=$r_post.cp_attr_ratings item="cp_attr"}
                    <div class="cp-new-rew-attr-block clearfix">
                    {$rate_id = "rating_`$r_post.post_id``$cp_attr.cp_attr_id`"}
                        <label for="{$rate_id}" class="ty-control-group__label {if $cp_attr.required == "Y"}cm-required{/if} cm-multiple-radios {if $cp_attr.view_type == "E" && $cp_attr.view_type_txt} cp-pr__new-attr-label{/if}">{$cp_attr.cp_attr_name}:</label>
                        {if $cp_attr.view_type == "E" && $cp_attr.view_type_txt}
                            {if $is_form_readonly}
                                <sapn class="cp-pr__post_attr-extrem">{$cp_attr.attr_extr_name}</span>
                            {else}
                                <div class="cp-new-rew-stars-block cp_pr__new-attr">
                                    {include file="addons/cp_power_reviews/components/min_max_attr.tpl" rate_id=$rate_id rate_value=$cp_attr.rating rate_name="post_data[ratings][{$cp_attr.cp_attr_id}]"}
                                </div>
                            {/if}
                        {else}
                            <div class="cp-new-rew-stars-block">
                                {if $is_form_readonly}
                                    {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$cp_attr.rating|fn_cp_power_reviews_discussion_rating}
                                {else}
                                    {include file="addons/cp_power_reviews/components/usual_rate.tpl" rate_id=$rate_id rate_value=$cp_attr.rating rate_name="post_data[ratings][{$cp_attr.cp_attr_id}]"}
                                {/if}
                            </div>
                        {/if}
                    </div>
                {/foreach}
            </div>
        {/if}
        {if $addons.cp_power_reviews.show_usage == "Y" && $r_post.object_type == "P"}
            <div class="ty-control-group">
                <label class="ty-control-group__title">{__("cp_pr_experience")}:</label>
                <select name="post_data[cp_pr_exp]" {if $is_form_readonly}disabled{/if} >
                    <option value="N" {if $r_post.cp_pr_exp == "N"}selected="selected"{/if}>{__("none")}</option>
                    <option value="W" {if $r_post.cp_pr_exp == "W"}selected="selected"{/if}>{__("cp_pr_experience_w")}</option>
                    <option value="M" {if $r_post.cp_pr_exp == "M"}selected="selected"{/if}>{__("cp_pr_experience_m")}</option>
                    <option value="Y" {if $r_post.cp_pr_exp == "Y"}selected="selected"{/if}>{__("cp_pr_experience_y")}</option>
                </select>
            </div>
        {/if}
        {if $r_post.type == "C" || $r_post.type == "B"}
            {if $allow_title}
                <div class="ty-control-group">
                    <label for="cp_dsc_title_{$r_post.post_id}" class="ty-control-group__title {if $required_title}cm-required{/if}">{__("cp_pr_title_label")}:</label>
                    {if $is_form_readonly}
                        {$r_post.cp_pr_title}
                    {else}
                        <input type="text" name="post_data[cp_pr_title]" id="cp_dsc_title_{$r_post.post_id}" value="{$r_post.cp_pr_title}" size="50" class="ty-input-text-large">
                    {/if}
                </div>
            {/if}
            <div class="ty-control-group">
                <label for="cp_dsc_message_{$r_post.post_id}" class="ty-control-group__title cm-required">{__("your_message")}</label>
                {if $is_form_readonly}
                    {$r_post.message}
                {else}
                    <textarea id="cp_dsc_message_{$r_post.post_id}" name="post_data[message]" class="ty-input-textarea ty-input-text-large" rows="5" cols="72">{$r_post.message}</textarea>
                {/if}
            </div>
            {if $allow_adv_disadv}
                <div class="ty-control-group">
                    <label for="cp_dsc_cp_pr_advantages_{$r_post.post_id}" class="{if $required_adv_disadv}cm-required{/if} ty-control-group__title">{__("cp_pr_advantages")}:</label>
                    {if $is_form_readonly}
                        {$r_post.cp_pr_advantages}
                    {else}
                        <textarea name="post_data[cp_pr_advantages]" id="cp_dsc_cp_pr_advantages_{$r_post.post_id}" class="ty-input-textarea ty-input-text-large" rows="3" cols="72" >{$r_post.cp_pr_advantages}</textarea>
                    {/if}
                </div>
                <div class="ty-control-group">
                    <label for="cp_dsc_cp_pr_disadvantages_{$r_post.post_id}" class="{if $required_adv_disadv}cm-required{/if} ty-control-group__title">{__("cp_pr_disadvantages")}:</label>
                    {if $is_form_readonly}
                        {$r_post.cp_pr_disadvantages}
                    {else}
                        <textarea name="post_data[cp_pr_disadvantages]" id="cp_dsc_cp_pr_disadvantages_{$r_post.post_id}" class="ty-input-textarea ty-input-text-large" rows="3" cols="72" >{$r_post.cp_pr_disadvantages}</textarea>
                    {/if}
                </div>
            {/if}
        {/if}
        {if !$is_form_readonly || ($is_form_readonly && $r_post.cp_review_pairs)}
            <div class="ty-control-group">
                <label class="ty-control-group__title">{__("cp_pr_photo_mats")}:</label>
                {include
                    file="addons/cp_power_reviews/components/form_file_uploader.tpl"
                    existing_pairs=$r_post.cp_review_pairs|default:[]
                    file_name="file"
                    image_pair_types=['N' => 'cp_review_post_add_image', 'A' => 'cp_review_post_image']
                    allow_update_files=!$is_form_readonly
                }
            </div>
        {/if}
        {if $allow_videos}
            <div class="ty-control-group" id="cp_review_video_block_{$r_post.post_id}">
                <label class="ty-control-group__title">{__("cp_pr_review_video")}:</label>
                {if $r_post.video_data}
                    <div class="cp-pr__my-rev_video">
                        {if $r_post.video_data.preview}
                            {include file="addons/cp_power_reviews/components/post_image.tpl" images=$r_post.video_data.preview link_class="" 
                            obj_id="`$r_post.post_id`" image_width="150" image_height="150" show_detailed_link=false}
                        {elseif $r_post.video_data.preview_def}
                            {include file="addons/cp_power_reviews/components/post_image.tpl" images=$r_post.video_data.preview_def link_class="" 
                            obj_id="`$r_post.post_id`" image_width="150" image_height="150" show_detailed_link=false}
                        {/if}
                        <div class="cp-pr__my-rev_delete-video hidden">
                            <div class="cp-pr__video-btns">
                                <a class="cp-pr__view-video-btn cm-tooltip" data-ca-target-id="cp_my_reviews_item_{$r_post.post_id}" 
                                    href="//youtube.com/watch?v={$r_post.video_data.youtube_id}" target="_blank" title="{__("preview")}"><i class="ty-icon-eye-open"></i>
                                </a>
                                {if !$is_form_readonly}
                                    <a class="cp-pr__del-video-btn cm-tooltip cm-post cm-confirm cm-ajax" data-ca-target-id="cp_review_video_block_{$r_post.post_id}" 
                                        href="{"cp_pow_rev.cp_del_post_img?post_id=`$r_post.post_id`&video_id=`$r_post.video_data.video_id`&object_type=`$r_post.object_type`"|fn_url}" title="{__("delete")}"><i class="ty-icon-trashcan"></i>
                                    </a>
                                {/if}
                            </div>
                        </div>
                    </div>
                {else}
                    <div class="ty-control-group">
                        <label class="ty-control-group__title" for="elm_video_code" title="">{__("cp_pr_youtube_id")}{include file="common/tooltip.tpl" tooltip=__("cp_pr_youtube_id_tooltip")}:</label>
                        <div class="input-prepend input-prepend--mobile-fullwidth">
                            <span class="cm-field-prefix add-on">http://youtube.com?v=</span>
                            <input type="text" class="input-medium" value="" name="post_data[youtube_id]" id="elm_video_code"/>
                        </div>
                    </div>
                    <div class="ty-control-group">
                        <label class="ty-control-group__title">{__("cp_pr_preview_txt")}</label>
                        <label class="checkbox inline">
                        <input type="hidden" name="post_data[upload_from_youtube]" value="N" />
                        <input type="checkbox" name="post_data[upload_from_youtube]" value="Y" checked="checked" onclick="Tygh.$('#attach_image_video_box').toggle();">{__("cp_pr_get_youtube_preview")}{include file="common/tooltip.tpl" tooltip=__("cp_pr_upload_preview_text")}</label>
                            
                        <div id="attach_image_video_box" class="cp-add-rev-post-img hidden">
                            {include file="addons/cp_power_reviews/components/attach_images.tpl" image_name="cp_pr_video_preview" image_object_type="cp_pr_video_preview" image_object_id="0" image_type="M" no_thumbnail=true hide_images=true hide_alt=true}
                        </div>
                    </div>
                {/if}
            <!--cp_review_video_block_{$r_post.post_id}--></div>
        {/if}
        {if $r_post.type != "R" && $r_post.type != "D" && $msg_show_answ && $msg_show_answ == "Y" && $r_post.cp_admin_answ && $r_post.cp_admin_id}
            <div class="cp-pr__post_answer">
                <div class="cp-pr__post_answer_inside">
                    <div class="cp-pr__post_answer_top">
                        <div class="cp-pr__post_answer_top-left">
                            <div class="cp-pr__post_answer_top-icon"><span>{$r_post.cp_admin_id}</span></div>
                            <div class="cp-pr__post_answer_top-author">{__("cp_dm_answered_by")}: {$r_post.cp_admin_id}</div>
                        </div>
                        <div class="cp-pr__post_answer_top-right">
                            <div class="cp-pr__post_answer_top-date">
                                {$r_post.cp_admin_answ_time|date_format:"`$settings.Appearance.date_format`"}
                            </div>
                        </div>
                    </div>
                    <div class="cp-pr__post_answer_bot ty-wysiwyg-content">
                        <div>{$r_post.cp_admin_answ nofilter}</div>
                    </div>
                </div>
            </div>
        {/if}
        <div class="cp-pr__my-rev_actions clearfix">
            {if !$is_form_readonly}
                {include file="buttons/button.tpl" but_text=__("save") but_meta="ty-btn__secondary" but_role="submit" but_name="dispatch[cp_pow_rev.update_review]"}
            {/if}
            {if $addons.cp_power_reviews.allow_review_d == "Y" && !$r_post.cp_admin_id}
                <a class="cp-pr__my-rev_delete cm-tooltip cm-post cm-confirm ty-btn" title="{__("delete")}" href="{"cp_pow_rev.delete_review?post_id=`$r_post.post_id`"|fn_url}"><i class="ty-icon-trashcan"></i></a>
            {/if}
        </div>
    <!--cp_my_reviews_item_{$r_post.post_id}--></div>
</form>