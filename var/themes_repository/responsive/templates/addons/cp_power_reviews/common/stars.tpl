<div id="average_rating_product" class="pr-rating">

{if $block.properties.rating_show_type != "not_display" && $item.rating_value}
    <span class="ty-nowrap ty-stars pr-stars">
    {if $is_link}<a href="{"`$item.object_data.url`"|fn_url}">{/if}
    {section name="full_star" loop=$stars.full}<i class="ty-stars__icon ty-icon-star"></i>{/section}
    {if $stars.part}<i class="ty-stars__icon ty-icon-star-half"></i>{/if}
    {section name="full_star" loop=$stars.empty}<i class="ty-stars__icon ty-icon-star-empty"></i>{/section}
    {if $is_link}</a>{/if}
    </span>
{/if}

{assign var="rating" value="rating_`$obj_id`"}{$smarty.capture.$rating nofilter}

{if $block.properties.show_reviews_count == "Y"}
    {if $total_items}
        <span class="pr-rating-count">    
            <a class="ty-discussion__review-a cm-external-click" href="{"`$item.object_data.url`"|fn_url}">{$total_items} {__("reviews", [$total_items])}</a>
        </span>
    {/if}
{/if}
<!--average_rating_product--></div>