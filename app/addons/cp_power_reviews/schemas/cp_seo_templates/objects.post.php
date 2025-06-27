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

include_once(Registry::get('config.dir.addons') . 'cp_power_reviews/schemas/cp_seo_templates/objects.functions.php');

$schema['R'] = [
    'name'              => 'product_reviews',
    'title'             => __('cp_discussion_prod_reviews'),
    'conditions'        => ['P', 'C'],
    'get_function'      => 'fn_cp_pr_seo_get_prod_reviews',
    'step_function'     => 'fn_cp_pr_seo_step_products_reviews',
    'update_function'   => 'fn_cp_pr_seo_update_prod_reviews_data',
    'fields'            => [
        'page_title'        => true,
        'meta_description'  => true,
        'meta_keywords'     => true,
        'seo_name'          => true,
        'cp_st_h1'          => true,
        'cp_st_custom_bc'   => true
    ]
];

return $schema;
