{if $object_type == "P" && $addons.cp_power_reviews.common_for_variations == "Y" && $addons.product_variations.status == "A"}
    {$fo_vars_params="from_prod_tab=1"}
{else}
    {$fo_vars_params=""}
{/if}
<div class="cp-pr__sorting-box" id="cp_pr_reviews_sorting_block">
    <span>{__("sort_by")}:</span>
    <select id="sort_review_top" name="sort_review" onchange="fn_pr_change_sorting(this.value, '{$res_id}');">
        <option value="{$config.current_url}&amp;cp_sort_by=NW&amp;selected_section=discussion{if $fo_vars_params}&amp;{$fo_vars_params}{/if}#content_discussion_block" {if $discussion.cp_sort_by == "NW"}selected="selected"{/if}>{__("cp_sort_new")}</option>
        <option value="{$config.current_url}&amp;cp_sort_by=OD&amp;selected_section=discussion{if $fo_vars_params}&amp;{$fo_vars_params}{/if}#content_discussion_block" {if $discussion.cp_sort_by == "OD"}selected="selected"{/if}>{__("cp_sort_old")}</option>
        <option value="{$config.current_url}&amp;cp_sort_by=MH&amp;selected_section=discussion{if $fo_vars_params}&amp;{$fo_vars_params}{/if}#content_discussion_block" {if $discussion.cp_sort_by == "MH"}selected="selected"{/if}>{__("cp_sort_most_help")}</option>
        {if $discussion.type == "R" || $discussion.type == "B"}
            <option value="{$config.current_url}&amp;cp_sort_by=HR&amp;selected_section=discussion{if $fo_vars_params}&amp;{$fo_vars_params}{/if}#content_discussion_block" {if $discussion.cp_sort_by == "HR"}selected="selected"{/if}>{__("cp_sort_hight_rate")}</option>
            <option value="{$config.current_url}&amp;cp_sort_by=LR&amp;selected_section=discussion{if $fo_vars_params}&amp;{$fo_vars_params}{/if}#content_discussion_block" {if $discussion.cp_sort_by == "LR"}selected="selected"{/if}>{__("cp_sort_low_rate")}</option>
        {/if}
    </select>
<!--cp_pr_reviews_sorting_block--></div>

{$show_img_filter=false}

{if $object_type == "P" && $addons.cp_power_reviews.show_image_in_post}
    {$show_img_filter=true}
{elseif $object_type == "C" && $addons.cp_power_reviews.show_image_in_post_cat}
    {$show_img_filter=true}
{elseif ($object_type == "A" || $object_type == "B") && $addons.cp_power_reviews.show_image_in_post_page}
    {$show_img_filter=true}
{elseif $object_type == "M" && $addons.cp_power_reviews.show_image_in_post_vend}
    {$show_img_filter=true}
{elseif $object_type == "E" && $addons.cp_power_reviews.show_image_in_post_test}
    {$show_img_filter=true}
{elseif $discussion.object_type && $discussion.object_type == "ALL" && $addons.cp_power_reviews.show_image_in_post_ap}
    {$show_img_filter=true}
{/if}


{if $show_img_filter}
    {$filter_cur_url = $config.current_url|fn_query_remove:"result_ids":"cp_pr_with_images"}
    <div class="cp-pr__sorting_with-img">
        <input type="checkbox" class="cp-pr__filter-by-img" onchange="fn_pr_sort_by_images(this, '{$res_id}');" data-cp-cur-url="{$filter_cur_url}" 
            value="{$config.current_url}&amp;cp_pr_with_images=Y" 
            {if $smarty.request.cp_pr_with_images && $smarty.request.cp_pr_with_images == "Y"}checked="checked"{/if}
        />
        <span class="cp-pr__sorting_with-img_label">{__("cp_pr_with_photos")}</span>
    </div>
{/if}