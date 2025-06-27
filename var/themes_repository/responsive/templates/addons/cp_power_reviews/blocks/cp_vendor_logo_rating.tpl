{** block-description:block_cp_vendor_logo_rating **}
{if $vendor_info}
    {if $vendor_info.cp_for_product_page}
        {$cp_comp_Link="companies.products?company_id=`$vendor_info.company_id`"}
    {else}
        {$cp_comp_Link="companies.view?company_id=`$vendor_info.company_id`"}
    {/if}
    <div class="logo-container">
        <a href="{"`$cp_comp_Link`"|fn_url}">
            <img src="{$vendor_info.logos.theme.image.image_path}" width="{$vendor_info.logos.theme.image.image_x}" height="{$vendor_info.logos.theme.image.image_y}" alt="{$vendor_info.logos.theme.image.alt}" class="logo">
        </a>
        {if $block.properties.cp_show_vend_name && $block.properties.cp_show_vend_name == "Y"}
            <div class="cp-rev-vend__logo-name">
                <a href="{"`$cp_comp_Link`"|fn_url}">
                    {$vendor_info.company}
                </a>
            </div>
        {/if}
        {if $block.properties.cp_show_vend_name && $block.properties.cp_show_vend_rate == "Y"}
            <div class="cp-rev-vend__logo-rating-m">
                {if in_array($addons.discussion.company_discussion_type, ['B', 'R'])}
                    {if $vendor_info.average_rating}
                        {$average_rating = $vendor_info.average_rating}
                    {elseif $vendor_info.discussion.average_rating}
                        {$average_rating = $vendor_info.discussion.average_rating}
                    {/if}
                    {if $average_rating > 0}
                        <span class="ty-nowrap ty-stars">
                            {$stars=$average_rating|fn_get_discussion_rating}
                            <a href="{"companies.view?company_id={$vendor_info.company_id}&selected_section=discussion#discussion"|fn_url}">
                                {section name="full_star" loop=$stars.full}
                                    <i class="ty-stars__icon ty-icon-star"></i>
                                {/section}
                                {if $stars.part}
                                    <i class="ty-stars__icon ty-icon-star-half"></i>
                                {/if}

                                {section name="full_star" loop=$stars.empty}
                                    <i class="ty-stars__icon ty-icon-star-empty"></i>
                                {/section}
                            </a>
                        </span>
                    {/if}
                    {if $vendor_info.discussion.posts}
                        <a href="{"companies.view?company_id=`$vendor_info.company_id`&selected_section=discussion#discussion"|fn_url}">
                            {$vendor_info.discussion.search.total_items} {__("reviews", [$vendor_info.discussion.search.total_items])}
                        </a>
                    {/if}
                {/if}
            </div>
        {/if}
        {if $block.properties.cp_show_vend_btn && $block.properties.cp_show_vend_btn == "Y"}
            <div class="cp-rev-vend__logo-btn">
                <a class="ty-btn" href="{"`$cp_comp_Link`"|fn_url}">{__("cp_visit_store")}</a>
            </div>
        {/if}
    </div>
{/if}