{** block-description:block_cp_vendor_attr_rating **}
{if $cp_attr_data}
    {if $cp_attr_data.posts}
        <div class="ty-discussion__rating-wrapper">            
            {if in_array($addons.discussion.company_discussion_type, ['B', 'R'])}
                {if $cp_attr_data.average_rating}
                    {$average_rating = $cp_attr_data.average_rating}
                {/if}
                {if $average_rating > 0}
                    {$v_stars=$average_rating|fn_get_discussion_rating}
                    <span class="ty-nowrap ty-stars">
                        <a href="{"companies.view?company_id={$cp_attr_data.object_id}&selected_section=discussion#discussion"|fn_url}">
                            {section name="full_star" loop=$v_stars.full}
                                <i class="ty-stars__icon ty-icon-star"></i>
                            {/section}
                            {if $v_stars.part}
                                <i class="ty-stars__icon ty-icon-star-half"></i>
                            {/if}

                            {section name="full_star" loop=$v_stars.empty}
                                <i class="ty-stars__icon ty-icon-star-empty"></i>
                            {/section}
                        </a>
                    </span>
                {/if}

            {/if}
            {if $cp_attr_data.posts}
                <a href="{"companies.view?company_id={$cp_attr_data.object_id}&selected_section=discussion#discussion"|fn_url}">
                    {$cp_attr_data.search.total_items} {__("reviews", [$cp_attr_data.search.total_items])}
                </a>
            {/if}
        </div>
        {if $cp_attr_data.cp_all_prod_attrs && $cp_attr_data.cp_show_sred_block}
            <div class="cp-product-ave-rate-block clearfix">
                {foreach from=$cp_attr_data.cp_all_prod_attrs item="pr_rate"}
                    {if $pr_rate.atr_aver_rate}
                        <div class="cp-review-left-block-each-rat clearfix" >
                            <div class="cp-review-left-block-rating-name">
                                {$pr_rate.cp_attr_name}
                            </div>
                            <div class="cp-review-left-block-rating-stars">                                
                                {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$pr_rate.atr_aver_rate|fn_cp_power_reviews_discussion_rating}
                            </div>
                        </div>
                    {/if}
                {/foreach}
            </div>
        {/if}
    {/if}
{/if}