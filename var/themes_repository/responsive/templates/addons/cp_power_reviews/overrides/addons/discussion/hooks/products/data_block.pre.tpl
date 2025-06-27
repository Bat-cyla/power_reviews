{if $show_rating}

    {if $product.discussion_type && $product.discussion_type == "R" || $product.discussion_type == "B"}
        {if $product.average_rating}
            {$average_rating = $product.average_rating}
        {elseif $product.discussion.average_rating}
            {$average_rating = $product.discussion.average_rating}
        {/if}

        {if $average_rating > 0}
            <span class="cp-pr__top-row-stars" onclick="fn_pr_click_show_top_stats('cp_pr_top_product_stats');">
            {$tar_link="products.view?product_id={$product.product_id}&selected_section=discussion#discussion"}
            {if !$details_page}
                {$star_link = $tar_link}
            {else}
                {$star_link=""}
            {/if}
            {include file="addons/discussion/views/discussion/components/stars.tpl"
                stars=$average_rating|fn_get_discussion_rating
                link=$star_link
            }
            </span>
            {if $product.cp_pr_by_each_star}
                <div class="cp_ty-discussion__rating-wrapper cp-pr__top-stars-stat hidden" id="cp_pr_top_product_stats">
                    <span class="cp-caret-top"><span class="cp-caret-outer"></span><span class="cp-caret-inner"></span></span>
                    <div class="cp-pr__top-stars_avg">
                        {$average_rating} {__("cp_pr_out_of")} 5
                    </div>
                    {include file="addons/cp_power_reviews/components/by_each_star.tpl" skip_star_links=1 e_stars=$product}
                    <div class="cp-pr__top-stars_see-all">
                        <a class="cm-external-click" onclick="fn_pr_click_show_top_stats('cp_pr_top_product_stats');" data-ca-scroll="content_discussion" data-ca-external-click-id="discussion">{__("cp_pr_view_reviews")}</a>
                    </div>
                </div>
            {/if}
        {/if}
    {/if}
{/if}
