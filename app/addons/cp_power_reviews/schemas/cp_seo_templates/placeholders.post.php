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

include_once(Registry::get('config.dir.addons') . 'cp_power_reviews/schemas/cp_seo_templates/placeholders.functions.php');

$schema['R'] = [
    'reviews_count' => [
        'title'         => __('cp_pl_reviews_total_count'),
        'get_function'  => 'fn_cp_pr_set_reviews_count'
    ],
    'object_name' => [
        'title'         => __('cp_pl_object_reviews_name'),
        'get_function'  => 'fn_cp_pr_set_object_name'
    ],
    'company' => [
        'title'         => __('company_name'),
        'get_function'  => 'fn_cp_st_get_company_name'
    ],
    'product' => [
        'title' => __('product_name'),
        'field' => 'product'
    ],
    'product_code' => [
        'title' => __('sku'),
        'field' => 'product_code'
    ],
    'amount' => [
        'title' => __('products_amount'),
        'field' => 'amount'
    ],
    'price' => [
        'title'         => __('price'),
        'get_function'  => 'fn_cp_st_get_product_price'
    ],
    'list_price' => [
        'title'             => __('list_price_short'),
        'field'             => 'list_price',
        'proccess_function' => 'fn_format_price'
    ],
    'full_description' => [
        'title'         => __('full_description'),
        'get_function'  => 'fn_cp_st_get_product_full_description'
    ],
    'short_description' => [
        'title'         => __('short_description'),
        'get_function'  => 'fn_cp_st_get_product_short_description'
    ],
    'currency' => [
        'title'         => __('currency'),
        'get_function'  => 'fn_cp_st_get_currency'
    ], 
    'currency_symbol' => [
        'title'         => __('currency_sign'),
        'get_function'  => 'fn_cp_st_get_currency_symbol'
    ],
    'category' => [
        'title'         => __('cp_main_product_category'),
        'get_function'  => 'fn_cp_st_get_product_main_category'
    ],
    'feature' => [
        'title'                 => __('features'),
        'is_group'              => true,
        'variables_function'    => 'fn_cp_st_get_feature_variables',
        'get_function'          => 'fn_cp_st_get_product_feature'
    ],
    'storefront'    => [
        'is_dynamic'    => true,
        'title'         => __('storefront'),
        'get_function'  => 'fn_cp_st_set_storefront_pl'
    ],
    'pagination'    => [
        'is_dynamic'    => true,
        'tooltip'       => __('cp_st_pagination_descr', ['[href]' => fn_url('languages.translations?q=cp_st_page_title_placeholder')]),
        'title'         => __('cp_st_pagination_txt'),
        'get_function'  => 'fn_cp_st_set_pagination_pl'
    ]
];

return $schema;
