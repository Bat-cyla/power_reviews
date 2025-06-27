{if in_array($discussion_object_type,array("P","E","M","C","A","B"))}
    {$allow_title=false}
    {$allow_adv_disadv=false}
    {if $discussion_object_type == "P"}
        {if $addons.cp_power_reviews.allow_message_title == "Y"}
            {$allow_title=true}
        {/if}
        {if $addons.cp_power_reviews.allow_adv_disv == "Y"}
            {$allow_adv_disadv=true}
        {/if}
    {elseif $discussion_object_type == "E"}
        {if $addons.cp_power_reviews.allow_message_title_test == "Y"}
            {$allow_title=true}
        {/if}
        {if $addons.cp_power_reviews.allow_adv_disv_test == "Y"}
            {$allow_adv_disadv=true}
        {/if}
    {elseif $discussion_object_type == "M"}
        {if $addons.cp_power_reviews.allow_message_title_vend == "Y"}
            {$allow_title=true}
        {/if}
        {if $addons.cp_power_reviews.allow_adv_disv_vend == "Y"}
            {$allow_adv_disadv=true}
        {/if}
    {elseif $discussion_object_type == "C"}
        {if $addons.cp_power_reviews.allow_message_title_cat == "Y"}
            {$allow_title=true}
        {/if}
        {if $addons.cp_power_reviews.allow_adv_disv_cat == "Y"}
            {$allow_adv_disadv=true}
        {/if}
    {elseif $discussion_object_type == "A" || $discussion_object_type == "B"}
        {if $addons.cp_power_reviews.allow_message_title_page == "Y"}
            {$allow_title=true}
        {/if}
        {if $addons.cp_power_reviews.allow_adv_disv_page == "Y"}
            {$allow_adv_disadv=true}
        {/if}
    {/if}
    <div class="form-horizontal">
        {include file="addons/cp_power_reviews/components/post.tpl" post=$post type=$post.type show_object_link=true allow_save=true show_title=$allow_title show_adv=$allow_adv_disadv}
    </div>
{else}
    {include file="addons/discussion/views/discussion_manager/components/post.tpl" post=$post type=$post.type show_object_link=true allow_save=true}
{/if}
{if "discussion.update"|fn_check_view_permissions && $discussion_object_type != "O"}
    {if $post.cp_admin_answ}
        {include file="common/subheader.tpl" title=__("cp_pr_review_answer") target="#post_answer_`$post.post_id`"}
    {else}
        {include file="common/subheader.tpl" title=__("cp_pr_reply_to_review") meta="collapsed" target="#post_answer_`$post.post_id`"}
    {/if}
    <div id="post_answer_{$post.post_id}" class="cp-db__obj-answ collapse {if $post.cp_admin_answ}in{/if}">
        <div class="control-group cp-dm__post-answer">
            <label class="control-label">{__("cp_dm_answered_by")}:</label>
            <div class="controls">
                <input type="text" name="cp_post_data[{$post.post_id}][cp_admin_id]" value="{$post.cp_admin_id}" size="" class="input-hidden">
            </div>
        </div>
        <div class="control-group cp-dm__post-answer">
            <label class="control-label">{__("answer")}:</label>
            <div class="controls">
                <textarea name="cp_post_data[{$post.post_id}][cp_admin_answ]" cols="80" rows="5" class="input-hidden cm-wysiwyg">{$post.cp_admin_answ}</textarea>
            </div>
        </div>
    </div>
{/if}