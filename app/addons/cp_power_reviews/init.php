<?php
/*****************************************************************************
*                                                        © 2013 Cart-Power   *
*           __   ______           __        ____                             *
*          / /  / ____/___ ______/ /_      / __ \____ _      _____  _____    *
*      __ / /  / /   / __ `/ ___/ __/_____/ /_/ / __ \ | /| / / _ \/ ___/    *
*     / // /  / /___/ /_/ / /  / /_/_____/ ____/ /_/ / |/ |/ /  __/ /        *
*    /_//_/   \____/\__,_/_/   \__/     /_/    \____/|__/|__/\___/_/         *
*                                                                            *
*                                                                            *
* -------------------------------------------------------------------------- *
* This is commercial software, only users who have purchased a valid license *
* and  accept to the terms of the License Agreement can install and use this *
* program.                                                                   *
* -------------------------------------------------------------------------- *
* website: https://store.cart-power.com                                      *
* email:   sales@cart-power.com                                              *
******************************************************************************/

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_define('CP_PR_YOUTUBE_PREVIEW_URL', 'https://img.youtube.com/vi/[VIDEO_ID]/maxresdefault.jpg');
fn_define('CP_PR_YOUTUBE_PLAYER_URL', 'https://www.youtube.com/');
fn_define('CP_PR_REVIEWS_SEO', 'r');
fn_define('CP_PR_OBJECT_SEO_KEY', 'd');
fn_define('CP_PR_SEO_SUFFIX', 'reviews');

fn_register_hooks(
    'ab__advanced_sitemap_write_links_to_file',
    'ab__as_get_settings_object_from_object_type',
    'ab__as_other_objects',
    'change_order_status_post',
    'create_seo_name_pre',
    'create_seo_name_post',
    'delete_company',
    'delete_category_after',
    'discussion_delete_post_post',
    'delete_discussion_pre',
    'delete_languages_post',
    'delete_product_post',
    'gather_additional_product_data_post',
    'get_category_data_post',
    'get_discussion_pre',
    'get_discussion_post',
    'get_discussion_posts_post',
    'get_discussions',
    'get_discussions_post',
    'get_discussion_posts',
    'get_product_data_post',
    'render_block_register_cache',
    'sitemap_link_object',
    'storefront_repository_delete_post',
    'tools_change_status',
    'update_company',
    'update_product_post',
    'update_language_post',
    'update_category_post',
    'url_pre'
);

if (version_compare(PRODUCT_VERSION, '4.11', '>=')) {
    fn_register_hooks(
        'google_sitemap_generate_sitemap_for_storefront_after_items'
    );
} else {
    fn_register_hooks(
        'sitemap_item'
    );
}
$addons = Registry::get('addons');
if (!empty($addons['product_variations']['status']) && $addons['product_variations']['status'] == 'A') {
    fn_register_hooks(
        ['get_discussion_pre', 1400],
        'get_route'
    );
    if (!empty($addons['cp_power_reviews']['common_for_variations'])) {
        fn_define('CP_PR_VARIATIONS_TYPE', $addons['cp_power_reviews']['common_for_variations']);
    }
}
if (!empty($addons['cp_power_reviews']['show_total_posts']) && $addons['cp_power_reviews']['show_total_posts'] == 'Y') {
    fn_define('CP_PR_SHOW_TOTAL_POSTS', true);
}