<div class="cp-pr__my-rev">
    {if $posts}
        <div class="cp-pr__my-rev_list">
            {foreach from=$posts item="r_post"}
            
                {include file="addons/cp_power_reviews/components/my_review_post.tpl"}
                
            {/foreach}
        </div>
    {else}
        <p class="ty-no-items">{__("no_posts_found")}</p>
    {/if}
</div>