{script src="js/tygh/node_cloning.js"}

{assign var="tag_level" value=$tag_level|default:"1"}
{strip}
    <span class="cp-zoom_image_icon">
        {include file="addons/cp_power_reviews/components/add_empty_item.tpl" but_onclick="Tygh.$('#box_' + this.id).cloneNode($tag_level); `$on_add`" item_id=$item_id}
    </span>
    <span class="cp-zoom_image_icon">
        {include file="buttons/remove_item.tpl" only_delete=$only_delete but_class="cm-delete-row"}
    </span>
{/strip}