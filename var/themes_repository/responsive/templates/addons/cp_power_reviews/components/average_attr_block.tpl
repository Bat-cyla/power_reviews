<div class="nd-attributes">
    <div class="cp-pr__attr-block-rated">{__("cp_pr_n_customers", [$discussion.cp_total_posts_with_attr])}</div>
    <div class="nd-product-ave-rate cp-product-ave-rate-block">
        {foreach from=$discussion.cp_all_prod_attrs item="pr_rate"}
            {if $pr_rate.atr_aver_rate}
                {$for_click_stats = "cp_pr_each_rates_`$object_id`_`$pr_rate.cp_attr_id`"}
                <div class="cp-pr__average-each-rate">
                    <div class="nd-rating-stars-block" onclick="fn_pr_click_show_stats('{$for_click_stats}');">
                        <div class="cp-pr__average_attr-name cp-pr__attr-block-name">
                            {$pr_rate.cp_attr_name}:
                        </div>
                        <div class="nd-rating-stars-block__indicator cp-review-left-block-rating-stars">
                            <a rel="nofollow">
                                <span>
                                    {if $pr_rate.view_type == "E" && $pr_rate.attr_extr_name}
                                        {$pr_rate.attr_extr_name}
                                    {else}
                                        {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$pr_rate.atr_aver_rate|fn_cp_power_reviews_discussion_rating}
                                    {/if}
                                </span>
                            </a>
                        </div>
                        <div class="cp_pr__rates-info">
                            <i class="ty-icon-help-circle"></i>
                        </div>
                    </div>
                    {if $pr_rate.by_each_value}
                        <div class="hidden cp-pr__attr-data" id="cp_pr_each_rates_{$object_id}_{$pr_rate.cp_attr_id}">
                            <span class="cp-caret-top"><span class="cp-caret-outer"></span><span class="cp-caret-inner"></span></span>
                            {foreach from=$pr_rate.by_each_value key="rate" item="r_data"}
                                {if $pr_rate.view_type == "E" && $pr_rate.attr_extr_name}
                                    <div class="cp-pr__attr-data_main">
                                        <div class="cp-pr__attr-data_top">
                                            <span class="cp-pr__attr-data_top_name">{$r_data.name}</span>
                                            <span class="cp-pr__attr-data_top_percent">{if $r_data.total > 0}{($r_data.total/$pr_rate.total_rated*100)|round}% ({$r_data.total}){else}0%{/if}</span>
                                        </div>
                                        <div class="cp-pr__attr-data_bot">
                                            <div class="cp-pr__attr-data_bot_bar" style="width: {if $r_data.total > 0}{$r_data.total/$pr_rate.total_rated*100}{else}0{/if}%;"></div>
                                        </div>
                                    </div>
                                {else}
                                    <div class="cp-pr__attr-data_top">
                                        <span class="cp-pr__attr-data_top_name">{include file="addons/discussion/views/discussion/components/stars.tpl" stars=$rate|fn_cp_power_reviews_discussion_rating}</span>
                                        <span class="cp-pr__attr-data_top_percent">{if $r_data.total > 0}{($r_data.total/$pr_rate.total_rated*100)|round}% ({$r_data.total}){else}0%{/if}</span>
                                    </div>
                                {/if}
                            {/foreach}
                        </div>
                    {/if}
                </div>
            {/if}
        {/foreach}
    </div>
</div>