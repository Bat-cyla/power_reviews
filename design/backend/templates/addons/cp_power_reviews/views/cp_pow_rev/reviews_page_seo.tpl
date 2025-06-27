
{$id = $review_page_data.id}

{$allow_save = $id|fn_allow_save_object:"cp_pow_rev"}
{$hide_inputs = ""|fn_check_form_permissions}

{capture name="mainbox"}

{if $runtime.company_id && "ULTIMATE"|fn_allowed_for || "MULTIVENDOR"|fn_allowed_for}
    <form action="{""|fn_url}" method="post" class="form-horizontal form-edit{if !$allow_save || $hide_inputs} cm-hide-inputs{/if}" name="reviews_page_form" enctype="multipart/form-data">
        <input type="hidden" class="cm-no-hide-input" name="fake" value="1" />
        <input type="hidden" class="cm-no-hide-input" name="id" value="{$id}" />
        
        <div class="control-group">
            <label for="elm_banner_name" class="control-label cm-required">{__("name")}</label>
            <div class="controls">
            <input type="text" name="review_page_data[name]" id="elm_banner_name" value="{$review_page_data.name}" size="25" class="input-large" /></div>
        </div>
        
        {include file="addons/seo/common/seo_name_field.tpl" object_data=$review_page_data object_name="review_page_data" object_id=$review_page_data.id object_type=$smarty.const.CP_PR_REVIEWS_SEO share_dont_hide=true}

        {capture name="buttons"}
            {if !$id}
                {include file="buttons/save_cancel.tpl" but_role="submit-link" but_target_form="reviews_page_form" but_name="dispatch[cp_pow_rev.update_reviews_page_seo]"}
            {else}
                {if "ULTIMATE"|fn_allowed_for && !$allow_save}
                    {assign var="hide_first_button" value=true}
                    {assign var="hide_second_button" value=true}
                {/if}
                {include file="buttons/save_cancel.tpl" but_name="dispatch[cp_pow_rev.update_reviews_page_seo]" but_role="submit-link" but_target_form="reviews_page_form" hide_first_button=$hide_first_button hide_second_button=$hide_second_button save=$id}
            {/if}
        {/capture}

    </form>
{/if}
{/capture}


{include file="common/mainbox.tpl"
    title=($id) ? $review_page_data.name : 'All reviews'
    content=$smarty.capture.mainbox
    buttons=$smarty.capture.buttons
    select_languages=true}

{** banner section **}
