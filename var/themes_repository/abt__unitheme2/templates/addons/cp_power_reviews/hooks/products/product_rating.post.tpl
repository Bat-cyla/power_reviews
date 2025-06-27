{if $settings.abt__ut2.product_list.show_rating == "YesNo::NO"|enum}
    {assign var="rating" value="rating_$obj_id"}
    {if $smarty.capture.$rating|strlen > 40 && $product.discussion_type && $product.discussion_type != "D"}
        {$smarty.capture.$rating nofilter}
    {/if}
{/if}