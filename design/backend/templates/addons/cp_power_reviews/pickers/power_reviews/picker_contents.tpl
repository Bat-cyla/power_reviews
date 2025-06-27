{if !$smarty.request.extra}
<script type="text/javascript">
(function(_, $) {
    _.tr('text_items_added', '{__("text_items_added")|escape:"javascript"}');

    $.ceEvent('on', 'ce.formpost_reviews_form', function(frm, elm) {

        var reviews = {};

        if ($('input.cm-item:checked', frm).length > 0) {
            $('input.cm-item:checked', frm).each( function() {
                var id = $(this).val();
                reviews[id] = $('#review_' + id).text();
            });

            {literal}
                        
            $.cePicker('add_js_item', frm.data('caResultId'), reviews, 'b', {
                '{review_id}': '%id',
                '{review}': '%item'
            });
            {/literal}

            $.ceNotification('show', {
                type: 'N', 
                title: _.tr('notice'), 
                message: _.tr('text_items_added'), 
                message_state: 'I'
            });
        }

        return false;
    });

}(Tygh, Tygh.$));
</script>
{/if}
</head>

{include file="addons/cp_power_reviews/components/discussion_search_form.tpl" dispatch="cp_pow_rev.picker" picker_selected_companies=$picker_selected_companies extra="<input type=\"hidden\" name=\"result_ids\" value=\"pagination_`$smarty.request.data_id`\">" put_request_vars=true form_meta="cm-ajax" in_popup=true}

<form action="{$smarty.request.extra|fn_url}" data-ca-result-id="{$smarty.request.data_id}" method="post" name="reviews_form">
    {include file="common/pagination.tpl" div_id="pagination_`$smarty.request.data_id`"}
    {if $reviews}
    <table width="100%" class="table table-middle">
    <thead>
    <tr>
        <th width="5%">
            {include file="common/check_items.tpl"}</th>
        <th width="35%">{__("message")}</th>
        <th width="15%">{__("rating")}</th>    
        <th width="15%">{__("name")}</th>  
        <th width="20%">{__("date")}</th>   
        <th width="10%">{__("object")}</th>    
    </tr>
    </thead>

    {foreach from=$reviews item="review"}
        <tr>
            <td><input type="checkbox" name="{$smarty.request.checkbox_name|default:"reviews_ids"}[]" value="{$review.post_id}" class="cm-item" /></td>
            <td id="review_{$review.post_id}">{$review.message|truncate:50:"...":true nofilter}</td>
            <td>{include file="addons/discussion/views/discussion_manager/components/stars.tpl" stars=$review.rating_value}</td>   
            <td>{$review.name}</td>
            <td>{$review.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>   
            <td>{$discussion_object_types[$review.object_type]|ucfirst}</td>
        </tr>
    {/foreach}
    </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}

    {include file="common/pagination.tpl" div_id="pagination_`$smarty.request.data_id`"}

    {if $reviews}
        <div class="buttons-container">
            {include file="buttons/add_close.tpl" but_text=__("add_reviews") but_close_text=__("add_reviews_and_close") is_js=$smarty.request.extra|fn_is_empty}
        </div>
    {/if}

</form>
