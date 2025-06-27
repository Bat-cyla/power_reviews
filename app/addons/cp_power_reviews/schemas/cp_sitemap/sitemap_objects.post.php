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

/* XML sitemaps */
$schema['common']['cp_pr_page'] = array(
    'get_function' => 'fn_cp_sitemap_get_cp_pr_threads',
    'link' => 'cp_pow_rev.view?thread_id=[id]',
    'link_params' => array('id' => 'thread_id'),
    'descr' => __('cp_pr_lang_for_sitemap'),
    'extra_attrs' => array('lastmod', 'changefreq', 'priority')
);
/* HTML sitemaps */
$schema['html']['cp_pr_page'] = $schema['common']['cp_pr_page'];
$schema['html']['cp_pr_page']['descr'] = __('cp_pr_reviews_txt');

return $schema;
