<div id="first_block_{$post_id}" class="{$fb_class}">{if $nofilter}{$fb_msg nofilter}{else}{$fb_msg|escape|nl2br nofilter}{/if}
    {if $addons.cp_power_reviews.show_more == "btn"}
        <a class="cp-pr__msg_more_click cp-show-more-link" data-post-id="{$post_id}">...</a>
    {else}
        <a class="cp-show-more-link" data-post-id="{$post_id}">{__("cp_pr_read_full")}</a>
    {/if}
</div>
<div id="second_block_{$post_id}" class="{$sb_class} hidden">{if $nofilter}{$sb_msg nofilter}{else}{$sb_msg|escape|nl2br nofilter}{/if}
   {*
   {if $addons.cp_power_reviews.show_more == "btn"}
        <a class="cp-pr__msg_more_click cp-show-less-link" data-post-id="{$post_id}">...</a>
    {else}
        <a class="cp-show-less-link" data-post-id="{$post_id}"><<< {__("cp_show_less")}</a>
    {/if}
    *}
</div>