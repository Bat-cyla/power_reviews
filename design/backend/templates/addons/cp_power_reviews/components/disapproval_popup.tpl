{$post_id = $post_id|default:0}
{$title = $title|default:__("cp_power_reviews.disapprove_reply")}
<div class="hidden" title="{$title}" id="cp_rp_disapproval_reason_{$post_id}">
    <div class="form-horizontal form-edit">
        <input type ="hidden" name="redirect_url" value="{$config.current_url}" />
        <div class="control-group">
            <label class="control-label">
                {__("cp_power_reviews.disapproval_reason")}:
            </label>
            <div class="controls">
                <textarea class="input-textarea-long premoderation-reason" name="cp_rp_approval[{$post_id}][reason]" cols="55" rows="8"></textarea>
            </div>
        </div>
    </div>
    <div class="buttons-container">
        <a class="cm-dialog-closer cm-cancel tool-link btn">
            {__("cancel")}
        </a>
        <input type="submit"
               class="btn btn-primary"
               name="dispatch[discussion.rp_decline.{$post_id}]"
               value="{__("cp_power_reviews.disapprove")}"
        />
    </div>
    <!--cp_rp_disapproval_reason_{$post_id}--></div>
