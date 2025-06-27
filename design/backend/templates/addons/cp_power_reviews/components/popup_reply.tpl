{if empty($capture_off)}
{capture name="content_for_popup_reply_`$post.post_id`"}
{/if}

{if !empty($discussion.thread_id)}
    {$thread_id = $discussion.thread_id}
{else}
    {$thread_id = $post.thread_id}
{/if}

    <form name="reply_post_{$post.post_id}" action="{""|fn_url}" method="post" class="form-horizontal form-edit">
        <input type ="hidden" name="cp_post_data[thread_id]" value="{$thread_id}" />
        <input type ="hidden" name="cp_post_data[post_id]" value="{$post.post_id}" />
        <input type ="hidden" name="redirect_url" value="{$redirect_url}" />
        
        <div class="control-group">
            <label class="control-label">{__("cp_dm_answered_by")}:</label>
            <div class="controls">
                <input type="text" name="cp_post_data[cp_admin_id]" value="{$post.cp_admin_id|default:""}" size="" class="input-hidden">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{__("answer")}:</label>
            <div class="controls">
                <textarea name="cp_post_data[cp_admin_answ]" id="cp_wysw_answ_{$post.post_id}" cols="80" rows="5" class="input-hidden input-textarea-long cm-wysiwyg" cols="70" rows="8">{$post.cp_admin_answ|default:""}</textarea>
            </div>
        </div>
        {if $auth.user_type == "UserTypes::VENDOR"|enum && (!empty($post.reply_status) && $post.reply_status == 'D')}
            <div class="control-group">
                <label class="control-label">{__("cp_power_reviews.reason_disapprove_reply")}:</label>
                <div class="controls">
                    <p>{$post.reason}</p>
                </div>
            </div>
        {/if}

        <div class="buttons-container">
            {include file="buttons/save_cancel.tpl" but_text=__("cp_pr_reply_to_review") but_name="dispatch[discussion.cp_reply_vendor]" cancel_action="close" hide_first_button=false}
        </div>
    </form>

{if empty($capture_off)}
{/capture}
{/if}