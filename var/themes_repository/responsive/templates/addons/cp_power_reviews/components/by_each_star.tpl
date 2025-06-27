<div class="cp-pr__by-stars">
    {if $smarty.request.cp_post_kind}
        {$filter_cur_url = $config.current_url|fn_query_remove:"cp_post_kind":"r_limit"}
    {/if}
    {if $discussion.cp_pr_by_each_star}
        {$e_stars=$discussion}
    {/if}
    {if $object_type == "P" && $addons.cp_power_reviews.common_for_variations == "Y" && $addons.product_variations.status == "A"}
        {$fo_vars_params="from_prod_tab=1"}
    {else}
        {$fo_vars_params=""}
    {/if}
    {$more_to_url=""}
    {if $smarty.request.cp_pr_with_images == "Y"}
        {$more_to_url="&cp_pr_with_images=Y"}
    {/if}
    {foreach from=$e_stars.cp_pr_by_each_star key="val" item="val_total"}
        <div class="cp-pr__by-stars_item">
            <div class="cp-pr__by-stars_item_label">
                {if $val_total > 0 && !$skip_star_links}
                    {if $smarty.request.cp_post_kind}
                        {if $fo_vars_params}
                            {$cp_star_url="`$filter_cur_url`&cp_filter_stars=`$val`&selected_section=discussion`$more_to_url`&`$fo_vars_params`#content_discussion_block"}
                        {else}
                            {$cp_star_url="`$filter_cur_url`&cp_filter_stars=`$val`&selected_section=discussion`$more_to_url`#content_discussion_block"}
                        {/if}
                        <a onclick="fn_pr_click_stars('{$cp_star_url}', '{$cur_result_id}', 1, 1);" rel="nofollow">{__("cp_pr_n_stars", [$val])}</a>
                    {else}
                        {if $fo_vars_params}
                            {$cp_star_url="`$config.current_url`&cp_filter_stars=`$val`&selected_section=discussion`$more_to_url`&`$fo_vars_params`#content_discussion_block"}
                        {else}
                            {$cp_star_url="`$config.current_url`&cp_filter_stars=`$val`&selected_section=discussion`$more_to_url`#content_discussion_block"}
                        {/if}
                        <a onclick="fn_pr_click_stars('{$cp_star_url}', '{$cur_result_id}', 1, 0);" rel="nofollow">{__("cp_pr_n_stars", [$val])}</a>
                    {/if}
                {else}
                    {__("cp_pr_n_stars", [$val])}
                {/if}
            </div>
            
            <div class="cp-pr__by-stars_item_bar_o">
                <div class="cp-pr__by-stars_item_bar_i">
                    <div class="cp-pr__by-stars_item_bar_i2" style="width: {100*($val_total/$e_stars.cp_pr_total_rated)}%;"></div>
                </div>
            </div>
            <div class="cp-pr__by-stars_item_total">{$val_total}</div>
        </div>
    {/foreach}
    {if $smarty.request.cp_post_kind}
        {$cp_all_star_url="`$filter_cur_url`&selected_section=discussion#content_discussion_block"}
        <div class="cp-pr__by-starts_all">
            <a class="ty-btn" onclick="fn_pr_click_stars('{$cp_all_star_url}', '{$cur_result_id}', 0, 1);" rel="nofollow">{__("cp_pr_show_all")}</a>
        </div>
    {else}
        <div class="hidden cp-pr__by-starts_all">
            {$cp_all_star_url="`$config.current_url`&cp_reset_star_filter=1&selected_section=discussion#content_discussion_block"}
            <a class="ty-btn" onclick="fn_pr_click_stars('{$cp_all_star_url}', '{$cur_result_id}', 0, 0);" rel="nofollow">{__("cp_pr_show_all")}</a>
        </div>
    {/if}
</div>