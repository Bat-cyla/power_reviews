{$new_post_title = __("new_post")}
{if $smarty.request.product_id}
    {$new_post_title = __("write_review")}
    {$obj_id = $smarty.request.product_id}
{elseif $smarty.request.category_id}
    {$obj_id = ''}
{elseif $smarty.request.page_id}
    {$obj_id = ''} {* Bug in CS-Cart with pages *}
{/if}

{$obj_prefix="`$block.block_id`000"}
{if $block.properties.outside_navigation == "Y"}
    <div class="owl-theme ty-owl-controls">
        <div class="owl-controls clickable owl-controls-outside" id="owl_outside_nav_{$block.block_id}">
            <div class="owl-buttons">
                <div id="owl_prev_{$obj_prefix}" class="owl-prev"><i class="ty-icon-left-open-thin"></i></div>
                <div id="owl_next_{$obj_prefix}" class="owl-next"><i class="ty-icon-right-open-thin"></i></div>
            </div>
        </div>
    </div>
{/if}
{$modern_active = 1}
{if $items}
    <div class="clearfix">
        <div class="">
            <div id="scroll_list_{$block.block_id}" class="owl-carousel ty-scroller-list">
                {foreach from=$items item="item" name="blog_item"}
                
                    {$new_post_object_id = $item.object_id}
                    {$new_post_object_type = $item.object_type}
                    {$object_thread_id = $item.thread_id}
                    
                    {if $item.object_type == "P"}
                        {$item_link="products.view?product_id=`$item.object_id`&selected_section=discussion"|fn_url}
                    {elseif $item.object_type == "C"}
                        {$item_link="categories.view?category_id=`$item.object_id`&selected_section=discussion"|fn_url}
                    {elseif $item.object_type == "E"}
                        {$item_link="cp_pow_rev.all_reviews?object_type=E"|fn_url}
                    {elseif $item.object_type == "M"}
                        {$item_link="companies.view?company_id=`$item.object_id`&selected_section=discussion"|fn_url}
                    {elseif $item.object_type == "A"}
                        {$item_link="pages.view?page_id=`$item.object_id`&selected_section=discussion"|fn_url}
                    {elseif $item.object_type == "B"}
                        {$item_link="cp_blog.view?&post_id=`$item.object_id`&selected_section=discussion"|fn_url}
                    {else}
                        {$item_link="`$item.object_data.url`?selected_section=discussion"|fn_url}
                    {/if}
                    {if $item|is_array}
                        <div class="pr-item">
                            <div class="vertcenterwrap">
                                <div class="vertcentercentered clearfix">
                                    {if $block.properties.show_object_icon == "Y" && $item.object_type != "E"} 
                                        <a class="cp-reviews-block__img" href="{$item_link}#content_discussion">
                                            {if $item.object_type == "M"}
                                                <img src="{$item.object_data.main_pair.theme.image.image_path}" width="{$block.properties.object_image_width}" height="{$block.properties.object_image_height}" alt="{$item.object_data.main_pair.theme.image.alt}">
                                            {else}
                                                {include file="common/image.tpl" image_width=$block.properties.object_image_width image_height=$block.properties.object_image_height obj_id=$object_id images=$item.object_data.main_pair}
                                            {/if}
                                        </a>
                                    {/if}
                                    {if $item.object_type != "E"}
                                        <div class="lr-object">
                                            <a class="cp-reviews-block__object" href="{$item_link}#content_discussion">{$item.object_data.description|truncate:70:' ...':false}&nbsp;{$item.name_last_words}</a>
                                        </div>
                                    {/if}
                                    <div class="cp-reviews-block_msg-name">
                                        <div class="cp-reviews-block_info">
                                            <div class="cp-reviews-block_author">{$item.name}</div>
                                            <span class="cp-reviews-block__date">{$item.timestamp|date_format:"`$settings.Appearance.date_format`"}</span>
                                            {if $addons.cp_power_reviews.show_purchase_label == "Y" && $item.object_type == "P"}
                                                <div class="cp-pr__post_verified">
                                                    {if $item.cp_pr_verified_purchase == "Y"}
                                                        <span class="cp-pr__post_verif-i"><i class="cp_pr-ico-ok-circled"></i><span class="cp-pr__post_verif-txt">{__("cp_pr_verified_purchase")}</span></span>
                                                    {else}
                                                        <span class="cp-pr__post_not-verif-i"><i class="cp_pr-ico-cancel-circled"></i><span class="cp-pr__post_verif-txt">{__("cp_pr_not_verified_purchase")}</span></span>
                                                    {/if}
                                                </div>
                                            {/if}
                                            {if $item.cp_pr_exp && $item.cp_pr_exp != "C" && ($discussion.object_type == "P" || ($item.object_type && $item.object_type == "P"))}
                                                <div class="cp-pr__post_exp">
                                                    <strong>{__("cp_pr_experience")}:</strong> {if $item.cp_pr_exp == "N"}{__("no")}{elseif $item.cp_pr_exp == "W"}{__("cp_pr_experience_w")}{elseif $item.cp_pr_exp == "M"}{__("cp_pr_experience_m")}{elseif $item.cp_pr_exp == "Y"}{__("cp_pr_experience_y")}{/if}
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                    <div class="cp-reviews-block-rating" >
                                        {if $item.type == "R" || $item.type == "B"}
                                            <div class="cp-reviews-block__main-rating clearfix">
                                                <div class="cp-review-attr-string">
                                                    {if $item.cp_av_rate_stars}
                                                        {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$item.cp_av_rate_stars}
                                                    {else}
                                                        {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$item.rating_value|fn_cp_power_reviews_discussion_rating}
                                                    {/if}
                                                </div>
                                            </div>
                                            {if $block.properties.show_review_attrs && $block.properties.show_review_attrs == "Y"}
                                                {if $item.cp_attr_ratings}
                                                    <div class="cp-pr__post_ratings">
                                                        {foreach from=$item.cp_attr_ratings item="p_attr"}
                                                            <div class="cp-pr__post_attr clearfix">
                                                                <div class="cp-pr__post_attr-name">
                                                                    {$p_attr.cp_attr_name}{if $p_attr.view_type == "E" && $p_attr.attr_extr_name}:{/if}
                                                                </div>
                                                                {if $p_attr.view_type == "E" && $p_attr.attr_extr_name}
                                                                    <sapn class="cp-pr__post_attr-extrem">{$p_attr.attr_extr_name}</span>
                                                                {else}
                                                                    <div class="cp-pr__post_attr-stars">
                                                                        {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$p_attr.rating|fn_cp_power_reviews_discussion_rating}
                                                                    </div>
                                                                {/if}
                                                            </div>
                                                        {/foreach}
                                                    </div>
                                                {/if}
                                            {/if}
                                        {/if}
                                    </div>
                                    {if $item.message && ($item.type == "C" || $item.type == "B")}
                                        {if $block.properties.cp_pr_show_msg_title == "Y" && $item.cp_pr_title}
                                            <div class="cp-pr__msg_title">
                                                <div class="cp-pr__msg_title_txt">{$item.cp_pr_title|escape|nl2br nofilter}</div>
                                            </div>
                                        {/if}
                                        <div class="cp-reviews-block__msg-block {if $block.properties.cp_pr_show_msg_title != "Y" || !$item.cp_pr_title} cp-pr__top-border{/if}{if $block.properties.cp_pr_message_height} cp-pr__need-scrollbar{/if}" {if $block.properties.cp_pr_message_height}style="max-height: {$block.properties.cp_pr_message_height}px;overflow: hidden;"{/if}>
                                            {$show_more_btn=false}
                                            {if $item.cp_pr_advantages && $block.properties.cp_pr_show_adv == "Y"}
                                                {$show_more_btn=true}
                                                <div class="cp-pr__msg_advan">
                                                    <div class="cp-pr__post_msg_label">{__("cp_pr_advantages")}</div>
                                                    {if $item.short_adv}
                                                        <div class="cp-pr__msg_advan_txt">{$item.short_adv|escape|nl2br nofilter}...</div>
                                                    {else}
                                                        <div class="cp-pr__msg_advan_txt">{$item.cp_pr_advantages|escape|nl2br nofilter}</div>
                                                    {/if}
                                                </div>
                                            {/if}
                                            {if $item.cp_pr_disadvantages && $block.properties.cp_pr_show_adv == "Y"}
                                                {$show_more_btn=true}
                                                <div class="cp-pr__msg_advan">
                                                    <div class="cp-pr__post_msg_label">{__("cp_pr_disadvantages")}</div>
                                                    {if $item.short_disadv}
                                                        <div class="cp-pr__msg_disadvan_txt">{$item.short_disadv|escape|nl2br nofilter}...</div>
                                                    {else}
                                                        <div class="cp-pr__msg_disadvan_txt">{$item.cp_pr_disadvantages|escape|nl2br nofilter}</div>
                                                    {/if}
                                                </div>
                                            {/if}
                                            <div class="cp-pr__msg_advan">
                                            <div class="cp-pr__post_msg_label">{__("cp_pr_comment_txt")}</div>
                                            {if $item.short_msg}
                                                {$show_more_btn=true}
                                                <div class="cp-pr__msg__txt">{$item.short_msg|escape|nl2br nofilter}...</div>
                                            {else}
                                            <div class="cp-pr__msg__txt">{$item.message|escape|nl2br nofilter}</div>
                                            {/if}
                                            </div>
                                        </div>
                                        <div class="cp-pr__shadow-block"></div>
                                    {/if}   
                                </div>
                            </div>
                        </div>
                    {/if}
                {/foreach}
            </div>
        </div>
        {if $block.content && $block.content.items && $block.content.items.filling && in_array($block.content.items.filling, array("cp_pr_testimonials","cp_pr_vendors")) && $block.content.items.cp_pr_show_review_btn && $block.content.items.cp_pr_show_review_btn == "Y"}
            {include
                file="addons/discussion/views/discussion/components/new_post_button.tpl"
                name=__("write_review")
                obj_id=$new_post_object_id
                object_type=$new_post_object_type
                locate_to_review_tab=true}
        {/if}

        {if $block.properties.cp_show_view_all_btn && $block.properties.cp_show_view_all_btn == "Y"}
            {if $block.content && $block.content.items && $block.content.items.filling && $block.content.items.filling}
                {if $block.content.items.filling == "manually"}
                    
                    {$all_reviews_link="cp_pow_rev.all_reviews"|fn_url}
                    
                {elseif $block.content.items.filling == "prod_reviews_from_current_object"}
                    
                    {$all_reviews_link="cp_pow_rev.all_reviews?object_type=P"|fn_url}
                    
                {elseif $block.content.items.filling == "cp_pr_reviews"}
                    
                    {$all_reviews_link="cp_pow_rev.all_reviews?object_type=P"|fn_url}
                    
                {elseif $block.content.items.filling == "cp_pr_testimonials"}
                    
                    {$all_reviews_link="cp_pow_rev.store_reviews"|fn_url}
                {elseif $block.content.items.filling == "cp_pr_pages"}
                
                    {if $block.content.items.cp_take_from_cur_page == "Y" && $smarty.request.page_id}
                        {$all_reviews_link="cp_pow_rev.all_reviews?thread_id=`$object_thread_id`"|fn_url}
                    {else}
                        {$all_reviews_link="cp_pow_rev.all_reviews?object_type=A"|fn_url}
                    {/if}
                    
                {elseif $block.content.items.filling == "cp_pr_categories"}
                
                    {if $block.content.items.cp_take_from_cur_cat == "Y" && $smarty.request.category_id && $block.content.items.cp_take_from_sub_cat != "Y"}
                        {$all_reviews_link="cp_pow_rev.all_reviews?thread_id=`$object_thread_id`"|fn_url}
                    {else}
                        {$all_reviews_link="cp_pow_rev.all_reviews?object_type=C"|fn_url}
                    {/if}
                    
                {elseif $block.content.items.filling == "cp_pr_vendors"}
                
                    {if $block.content.items.cp_take_from_cur_vendor == "Y" && ($smarty.request.company_id || $smarty.request.product_id)}
                        {$all_reviews_link="cp_pow_rev.all_reviews?thread_id=`$object_thread_id`"|fn_url}
                    {else}
                        {$all_reviews_link="cp_pow_rev.all_reviews?object_type=M"|fn_url}
                    {/if}
                    
                {elseif $block.content.items.filling == "cp_pr_power_blog"}
                
                    {if $block.content.items.cp_take_from_cur_page == "Y" && $smarty.request.post_id}
                        {$all_reviews_link="cp_pow_rev.all_reviews?thread_id=`$object_thread_id`"|fn_url}
                    {else}
                        {$all_reviews_link="cp_pow_rev.all_reviews?object_type=B"|fn_url}
                    {/if}
                    
                {/if}
            {/if}
            {if $all_reviews_link}
                <a class="cp-pr__all-reviews-link" href="{$all_reviews_link}">{__("cp_pr_all_reviews")}</a>
            {/if}
        {/if}
    </div>
{/if}
{include file="common/scroller_init_with_quantity.tpl" prev_selector="#owl_prev_`$obj_prefix`" next_selector="#owl_next_`$obj_prefix`"}

