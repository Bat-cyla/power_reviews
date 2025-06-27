{if "discussion.add"|fn_check_view_permissions && !("MULTIVENDOR"|fn_allowed_for && $runtime.company_id && ($runtime.company_id != $object_company_id || $discussion.object_type == "M"))}
    {capture name="add_new_picker"}
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
        {/if}
        <form id="form" action="{""|fn_url}" method="post" class="form-horizontal form-edit cm-disable-empty-files" enctype="multipart/form-data">
            <div class="cm-no-hide-input" id="content_tab_add_post">
                <input type ="hidden" name="post_data[thread_id]" value="{$discussion.thread_id}" />
                <input type ="hidden" name="redirect_url" value="{$config.current_url}&amp;selected_section=discussion" />
                
                {if fn_allowed_for("MULTIVENDOR")}
                    {$default_store_id=""|fn_cp_pr_get_default_storefront_id}
                    <div class="control-group">
                        <label class="control-label" for="elm_storefront_ids">{__("cp_pr_storefront_txt")}:</label>
                        <div class="controls">
                            {include file="pickers/storefronts/picker.tpl"
                                multiple=false
                                input_name="post_data[storefront_id]"
                                item_ids=$default_store_id
                                data_id="elm_storefront_ids"
                                but_meta="pull-right"
                                no_item_text=__("all_storefronts")
                            }
                        </div>
                    </div>
                {/if}
                
                <div class="control-group">
                    <label for="post_data_name" class="{if $addons.cp_power_reviews.allo_anonymous != "Y"}cm-required{/if} control-label">{__("name")}:</label>
                    <div class="controls">
                        <input type="text" name="post_data[name]" id="post_data_name" value="{if $auth.user_id}{$user_info.firstname} {$user_info.lastname}{/if}" disabled="disabled">
                    </div>
                </div>
                <div class="control-group">
                    <label for="post_data_timestamp" class="control-label">{__("creation_date")}:</label>
                    <div class="controls">
                        {include file="common/calendar.tpl" date_id="post_data_timestamp" date_name="post_data[date]" date_val=$post_data.timestamp|default:$smarty.const.TIME start_year=$settings.Company.company_start_year show_time=true time_name="post_data[time]"}
                    </div>
                </div>
                {if $addons.cp_power_reviews.show_usage == "Y" && $discussion.object_type == "P"}
                    <div class="control-group">
                        <label for="post_data_cp_pr_exp" class="control-label">{__("cp_pr_experience")}:</label>
                        <div class="controls">
                            <select name="post_data[cp_pr_exp]" id="post_data_cp_pr_exp">
                                <option value="N">{__("none")}</option>
                                <option value="W">{__("cp_pr_experience_w")}</option>
                                <option value="M">{__("cp_pr_experience_m")}</option>
                                <option value="Y">{__("cp_pr_experience_y")}</option>
                            </select>
                        </div>
                    </div>
                {/if}
                {if "discussion.update"|fn_check_view_permissions}
                    {if $discussion.type == "R" || $discussion.type == "B"}
                        {if $discussion.cp_all_prod_attrs}
                            {if $allow_common_rate}
                                <input type="hidden" name="post_data[cp_pr_common_rate_exist]"value=true>
                                <div class="control-group">
                                    <label for="rating_value" class="control-label cm-required cm-multiple-radios">{__("cp_pr_common_rating")}</label>
                                    <div class="controls clearfix">
                                        {include file="addons/discussion/views/discussion_manager/components/rate.tpl" rate_id="rating_value" rate_name="post_data[rating_value]" disabled=true}
                                    </div>
                                </div>
                            {/if}
                            <div class="cp-add-rev-prod-stars">
                                {foreach from=$discussion.cp_all_prod_attrs item="cp_attr"}
                                    <div class="control-group">
                                        {$rate_id = "rating_`$product_data.product_id`__`$cp_attr.cp_attr_id`"}
                                        <label for="{$rate_id}" class="control-label {if $cp_attr.required == "Y"}cm-required{/if} cm-multiple-radios">{$cp_attr.cp_attr_name}</label>
                                        <div class="controls clearfix">
                                            {if $cp_attr.view_type == "E" && $cp_attr.view_type_txt}
                                                {include file="addons/cp_power_reviews/components/min_max_attr.tpl" rate_id=$rate_id rate_name="post_data[ratings][{$cp_attr.cp_attr_id}]" disabled=true}
                                            {else}
                                                {include file="addons/discussion/views/discussion_manager/components/rate.tpl" rate_id=$rate_id rate_name="post_data[ratings][{$cp_attr.cp_attr_id}]" disabled=true}
                                            {/if}
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                        {else}
                            <div class="control-group">
                                <label for="rating_value" class="control-label cm-required cm-multiple-radios">{__("your_rating")}</label>
                                <div class="controls clearfix">
                                    {include file="addons/discussion/views/discussion_manager/components/rate.tpl" rate_id="rating_value" rate_name="post_data[rating_value]" disabled=true}
                                </div>
                            </div>
                        {/if}
                    {/if}
                {/if}
                {if $allow_title}
                    <div class="control-group">
                        <label for="post_data_cp_pr_title" class="{if $required_title}cm-required{/if} control-label">{__("cp_pr_title_label")}:</label>
                        <div class="controls">
                            <input type="text" name="post_data[cp_pr_title]" id="post_data_cp_pr_title" value="" disabled="disabled">
                        </div>
                    </div>
                {/if}
                {if $discussion.type == "C" || $discussion.type == "B"}
                    <div class="control-group">
                        <label for="message" class="control-label cm-required">{__("your_message")}:</label>
                        <div class="controls">
                            <textarea name="post_data[message]" id="message" class="input-textarea-long" cols="70" rows="8" disabled="disabled"></textarea>
                        </div>
                    </div>
                    {if $allow_adv_disadv}
                        <div class="control-group">
                            <label for="cp_pr_advantages" class="{if $required_adv_disadv}cm-required{/if} control-label">{__("cp_pr_advantages")}:</label>
                            <div class="controls">
                                <textarea name="post_data[cp_pr_advantages]" id="cp_pr_advantages" class="input-textarea-long" cols="70" rows="8" disabled="disabled"></textarea>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="cp_pr_disadvantages" class="{if $required_adv_disadv}cm-required{/if} control-label">{__("cp_pr_disadvantages")}:</label>
                            <div class="controls">
                                <textarea name="post_data[cp_pr_disadvantages]" id="cp_pr_disadvantages" class="input-textarea-long" cols="70" rows="8" disabled="disabled"></textarea>
                            </div>
                        </div>
                    {/if}
                {/if}
                {if $discussion.object_type && $allow_img}
                    {include file="common/subheader.tpl" title=__("cp_add_images")}
                    <div class="cl-rev-post-main-img-block control-group">
                        <div id="box_post_new_image" class="cp-add-img-rev-block ty-control-group clearfix">
                            <div class="cm-row-item controls">
                                <div class="image-upload-wrap pull-left">
                                    {include file="addons/cp_power_reviews/components/attach_images.tpl" image_name="cp_review_post" image_object_type="cp_rev_post" image_object_id="0" image_type="A" no_thumbnail=true hide_images=true hide_alt=true}
                                </div>
                                <div class="pull-right cp-mult-but-img">
                                    {include file="buttons/multiple_buttons.tpl" item_id="post_new_image"}
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
                {if $discussion.object_type && $allow_videos}
                    {include file="common/subheader.tpl" title=__("cp_pr_add_video")}
                    <div class="control-group">
                        <label class="control-label" for="elm_video_code" title="">{__("cp_pr_youtube_id")}{include file="common/tooltip.tpl" tooltip=__("cp_pr_youtube_id_tooltip")}:</label>
                        <div class="controls"> 
                            <div class="input-prepend input-prepend--mobile-fullwidth">
                                <span class="cm-field-prefix add-on">http://youtube.com?v=</span>
                                <input type="text" class="input-medium" value="" name="post_data[youtube_id]" id="elm_video_code"/>
                            </div>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">{__("cp_pr_preview_txt")}:</label>
                        <div class="controls">
                            <input type="hidden" name="post_data[upload_from_youtube]" value="N" />
                            <input type="checkbox" name="post_data[upload_from_youtube]" value="Y" checked="checked" onclick="Tygh.$('#attach_image_video_box').toggle();">
                            <span class="cp-pp__add-post-prev">{__("cp_pr_get_youtube_preview")}{include file="common/tooltip.tpl" tooltip=__("cp_pr_upload_preview_text")}</span>
                            <div id="attach_image_video_box" class="hidden">
                                {include file="addons/cp_power_reviews/components/attach_images.tpl" image_name="cp_pr_video_preview" image_object_type="cp_pr_video_preview" image_object_id="0" image_type="M" no_thumbnail=true hide_images=true hide_alt=true}
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
            <div class="buttons-container">
                {include file="buttons/save_cancel.tpl" but_text=__("add") but_name="dispatch[discussion.cp_add_rew]" cancel_action="close" hide_first_button=false}
            </div>

        </form>
    {/capture}

    {include file="common/popupbox.tpl" id="add_new_post" text=__("cp_pr_add_review") content=$smarty.capture.add_new_picker act="fake"}
{/if}