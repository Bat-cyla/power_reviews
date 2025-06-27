{if !$more_index}
    {$more_index=""}
{/if}
{$allow_img=false}
{$allow_title=false}
{$required_title=true}
{$allow_videos=false}
{$allow_adv_disadv=false}
{$required_adv_disadv=true}
{$allow_common_rate=false}
{if $discussion.object_type == "P"}
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
    {$allow_common_rate=true}
    {if $addons.cp_power_reviews.allow_recom == "Y"}
        {$cp_show_recom_btn=$addons.cp_power_reviews.type_of_recomend_btn}
    {/if}
{elseif $discussion.object_type == "E"}
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
    {$allow_common_rate=true}
{elseif $discussion.object_type == "M"}
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
    {$allow_common_rate=true}
    {if $addons.cp_power_reviews.allow_recom_vend == "Y"}
        {$cp_show_recom_btn=$addons.cp_power_reviews.type_of_recomend_btn_vend}
    {/if}
{elseif $discussion.object_type == "C"}
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
{elseif $discussion.object_type == "A" || $discussion.object_type == "B"}
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
    {if $addons.cp_power_reviews.allow_recom_page == "Y"}
        {$cp_show_recom_btn=$addons.cp_power_reviews.type_of_recomend_btn_page}
    {/if}
{else}
    {$cp_show_recom_btn="all"}
{/if}

<div class="ty-discussion-post-popup {if !$cp_is_new_post_ajax}hidden{/if}" id="new_post_dialog_{$obj_prefix}{$obj_id}{$more_index}" title="{$new_post_title}">
    <form action="{""|fn_url}" method="post" class="{if !$post_redirect_url}cm-ajax cm-form-dialog-closer{/if} posts-form" name="add_post_form" id="add_post_form_{$obj_prefix}{$obj_id}{$more_index}" enctype="multipart/form-data">
        <input type="hidden" name="result_ids" value="cp_posts_list*,cp_new_post*,average_rating*">
        <input type ="hidden" name="post_data[thread_id]" value="{$discussion.thread_id}" />
        <input type ="hidden" name="redirect_url" value="{$post_redirect_url|default:$config.current_url}" />
        <input type="hidden" name="selected_section" value="" />

        <div id="cp_new_post_{$obj_prefix}{$obj_id}{$more_index}">
            {if $discussion.extra_var_name}
                <div class="ty-control-group">
                    {__("cp_pr_product_variant")}:&nbsp;{$discussion.extra_var_name}
                </div>
            {/if}
            <div class="ty-control-group">
                <label for="cp_dsc_name_{$obj_prefix}{$obj_id}{$more_index}" class="ty-control-group__title {if $addons.cp_power_reviews.allo_anonymous != "Y"}cm-required{/if}">{__("your_name")}</label>
                <input type="text" id="cp_dsc_name_{$obj_prefix}{$obj_id}{$more_index}" name="post_data[name]" value="{if $auth.user_id}{$user_info.firstname} {$user_info.lastname}{elseif $discussion.post_data.name}{$discussion.post_data.name}{/if}" size="50" class="ty-input-text-large" />
            </div>
            {if $discussion.type == "R" || $discussion.type == "B"}
                {if $discussion.cp_all_prod_attrs}
                    {*
                    <div class="ty-control-group">
                        <label class="ty-control-group__title">{__("cp_your_ratings")}:</label>
                    </div>
                    *}
                    {if $allow_common_rate}
                        <input type="hidden" name="post_data[cp_pr_common_rate_exist]"value=true>
                        <div class="cp-pr__new-attr-block_avg">
                            {$rate_id = "rating_`$obj_prefix``$obj_id``$more_index`"}
                            <label for="{$rate_id}" class="ty-control-group__label cm-required cm-multiple-radios">{__("cp_pr_common_rating")}</label>
                            <div class="cp-new-rew-stars-block">
                                {include file="addons/discussion/views/discussion/components/rate.tpl" rate_id=$rate_id rate_name="post_data[rating_value]"}
                            </div>
                        </div>
                    {/if}
                    <div class="cp-pr__new_attr-block">
                    {foreach from=$discussion.cp_all_prod_attrs item="cp_attr"}
                        <div class="cp-new-rew-attr-block clearfix">
                        {$rate_id = "rating_`$obj_prefix``$obj_id``$cp_attr.cp_attr_id``$more_index`"}
                            <label for="{$rate_id}" class="ty-control-group__label {if $cp_attr.required == "Y"}cm-required{/if} cm-multiple-radios {if $cp_attr.view_type == "E" && $cp_attr.view_type_txt} cp-pr__new-attr-label{/if}">{$cp_attr.cp_attr_name}</label>
                            {if $cp_attr.view_type == "E" && $cp_attr.view_type_txt}
                                <div class="cp-new-rew-stars-block cp_pr__new-attr">
                                    {include file="addons/cp_power_reviews/components/min_max_attr.tpl" rate_id=$rate_id rate_name="post_data[ratings][{$cp_attr.cp_attr_id}]"}
                                </div>
                            {else}
                                <div class="cp-new-rew-stars-block">
                                    {include file="addons/discussion/views/discussion/components/rate.tpl" rate_id=$rate_id rate_name="post_data[ratings][{$cp_attr.cp_attr_id}]"}
                                </div>
                            {/if}
                        </div>
                    {/foreach}
                    </div>
                {else}
                    <div class="ty-control-group">
                        {$rate_id = "rating_`$obj_prefix``$obj_id``$more_index`"}
                        <label for="{$rate_id}" class="ty-control-group__label cm-required cm-multiple-radios">{__("your_rating")}</label>
                        <div class="cp-new-rew-stars-block">
                            {include file="addons/discussion/views/discussion/components/rate.tpl" rate_id=$rate_id rate_name="post_data[rating_value]"}
                        </div>
                    </div>
                {/if}
            {/if}
            {if $addons.cp_power_reviews.show_usage == "Y" && $discussion.object_type == "P"}
                <div class="ty-control-group">
                    <label class="ty-control-group__title">{__("cp_pr_experience")}:</label>
                    <select name="post_data[cp_pr_exp]">
                        <option value="N">{__("none")}</option>
                        <option value="W">{__("cp_pr_experience_w")}</option>
                        <option value="M">{__("cp_pr_experience_m")}</option>
                        <option value="Y">{__("cp_pr_experience_y")}</option>
                    </select>
                </div>
            {/if}
            {hook name="discussion:add_post"}
            {if $discussion.type == "C" || $discussion.type == "B"}
                {if $allow_title}
                    <div class="ty-control-group">
                        <label for="cp_dsc_title_{$obj_prefix}{$obj_id}{$more_index}" class="ty-control-group__title {if $required_title}cm-required{/if}">{__("cp_pr_title_label")}:</label>
                        <input type="text" name="post_data[cp_pr_title]" id="cp_dsc_title_{$obj_prefix}{$obj_id}{$more_index}" value="" size="50" class="ty-input-text-large">
                    </div>
                {/if}
                <div class="ty-control-group">
                    <label for="cp_dsc_message_{$obj_prefix}{$obj_id}{$more_index}" class="ty-control-group__title cm-required">{__("your_message")}</label>
                    <textarea id="cp_dsc_message_{$obj_prefix}{$obj_id}{$more_index}" name="post_data[message]" class="ty-input-textarea ty-input-text-large" rows="5" cols="72">{$discussion.post_data.message}</textarea>
                </div>
                {if $allow_adv_disadv}
                    <div class="ty-control-group">
                        <label for="cp_dsc_cp_pr_advantages_{$obj_prefix}{$obj_id}{$more_index}" class="{if $required_adv_disadv}cm-required{/if} ty-control-group__title">{__("cp_pr_advantages")}:</label>
                        <textarea name="post_data[cp_pr_advantages]" id="cp_dsc_cp_pr_advantages_{$obj_prefix}{$obj_id}{$more_index}" class="ty-input-textarea ty-input-text-large" rows="3" cols="72" ></textarea>
                    </div>
                    <div class="ty-control-group">
                        <label for="cp_dsc_cp_pr_disadvantages_{$obj_prefix}{$obj_id}{$more_index}" class="{if $required_adv_disadv}cm-required{/if} ty-control-group__title">{__("cp_pr_disadvantages")}:</label>
                        <textarea name="post_data[cp_pr_disadvantages]" id="cp_dsc_cp_pr_disadvantages_{$obj_prefix}{$obj_id}{$more_index}" class="ty-input-textarea ty-input-text-large" rows="3" cols="72" ></textarea>
                    </div>
                {/if}
            {/if}
            {/hook}
            {if $discussion.object_type && $allow_img}
                <label class="ty-control-group__title">{__("cp_add_images")}:</label>
                <div class="cp-add-rev-post-img">
                    <div id="box_cp_new_image_{$obj_id}{$more_index}" class="cp-add-img-rev-block">
                        <div class="cm-row-item">
                            <div class="image-upload-wrap pull-left">
                                {include file="addons/cp_power_reviews/components/attach_images.tpl" image_name="cp_review_post" image_object_type="cp_rev_post" image_object_id="0" image_type="A" no_thumbnail=true hide_images=true hide_alt=true more_index=$more_index}
                                <div class="cp-pull-right cp-mult-but-img">
                                    {include file="addons/cp_power_reviews/components/multiple_buttons.tpl" item_id="cp_new_image_{$obj_id}{$more_index}"}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
            {if $discussion.object_type && $allow_videos}
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
                        {include file="addons/cp_power_reviews/components/attach_images.tpl" image_name="cp_pr_video_preview" image_object_type="cp_pr_video_preview" image_object_id="0" image_type="M" no_thumbnail=true hide_images=true hide_alt=true more_index=$more_index}
                        
                    </div>
                </div>
            {/if}
            {if $cp_show_recom_btn && ($cp_show_recom_btn == "all" || ($cp_show_recom_btn == "for_login" && $auth.user_id))}
                <div class="ty-control-group cp-pr__new-recomend">
                    <label class="ty-control-group__title">{__("cp_pr_do_you_recommend_this")} {if $discussion.object_type == "P"}{__("cp_pr_product_text")}{elseif $discussion.object_type == "M"}{__("cp_pr_vendor_text")}{elseif $discussion.object_type == "A"}{__("cp_pr_page_text")}{/if}?</label>
                    <input type="radio" class="hidden cp_pr_check_yes" id="cp_new_post_rec_yes" name="post_data[is_recommended]" value="U" />
                    <label class="cp-pr__new-recomend_label ty-btn cp_pr_check_yes" cp_pr_check_yes for="cp_new_post_rec_yes">{__("cp_pr_yes_text")}</label>
                    
                    <input type="radio" class="hidden cp_pr_check_no" id="cp_new_post_rec_no" name="post_data[is_recommended]" value="D" />
                    <label class="cp-pr__new-recomend_label-no ty-btn cp_pr_check_no" for="cp_new_post_rec_no">{__("cp_pr_no_text")}</label>
                </div>
            {/if}
            {include file="common/image_verification.tpl" option="discussion"}

            <div class="cp-buttons-container">
                {include file="buttons/button.tpl" but_text=__("submit") but_meta="ty-btn__secondary" but_role="submit" but_name="dispatch[discussion.cp_add_rew]"}
            </div>
        <!--cp_new_post_{$obj_prefix}{$obj_id}{$more_index}--></div>

    </form>
</div>
