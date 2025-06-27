<form action="{""|fn_url}" class="ty-orders-search-options" name="orders_search_form" method="get">

<div class="ty-control-group">
    <label class="ty-control-group__title">{__("cp_pr_reviews_type")}</label>
    <div class="ty-status-info">
        {foreach from=$all_types_full key="obj_type" item="obj_name"}
            {if $all_types.$obj_type && $all_types.$obj_type == "Y"}
                <label>
                    <input type="checkbox" id="" name="cp_object_types[]" value="{$obj_type|strtoupper}" columns="4" class="ty-orders-search__options-status" 
                        {if $discussion.search.cp_object_types && in_array($obj_type|strtoupper, $discussion.search.cp_object_types) || ($discussion.search.object_type && in_array($obj_type|strtoupper, $discussion.search.object_type))}checked="checked"{/if}
                    >
                    {$obj_name}
                </label>
            {/if}
        {/foreach}
    </div>
</div>

<div class="ty-control-group">
    <label class="ty-control-group__title">{__("rating")}</label>
    <select name="minimal_rating">
        <option value=1 {if $discussion.search.minimal_rating == 1}selected="selected"{/if}>1</option>
        <option value=2 {if $discussion.search.minimal_rating == 2}selected="selected"{/if}>2</option>
        <option value=3 {if $discussion.search.minimal_rating == 3}selected="selected"{/if}>3</option>
        <option value=4 {if $discussion.search.minimal_rating == 4}selected="selected"{/if}>4</option>
        <option value=5 {if $discussion.search.minimal_rating == 5}selected="selected"{/if}>5</option>
    </select>
    &nbsp;-&nbsp;
    <select name="maximum_rating">
        <option value=1 {if $discussion.search.maximum_rating == 1}selected="selected"{/if}>1</option>
        <option value=2 {if $discussion.search.maximum_rating == 2}selected="selected"{/if}>2</option>
        <option value=3 {if $discussion.search.maximum_rating == 3}selected="selected"{/if}>3</option>
        <option value=4 {if $discussion.search.maximum_rating == 4}selected="selected"{/if}>4</option>
        <option value=5 {if !$discussion.search.maximum_rating || $discussion.search.maximum_rating == 5}selected="selected"{/if}>5</option>
    </select>
</div>
<div class="buttons-container ty-search-form__buttons-container">
    {include file="buttons/button.tpl" but_meta="ty-btn__secondary" but_text=__("search") but_name="dispatch[cp_pow_rev.all_reviews?id=`$smarty.request.id`]"}
</div>
</form>
