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

function fn_cp_pr_seo_get_prod_reviews($template = [])
{
    $products = array();
    $item_ids = fn_cp_st_get_product_ids($template);
    if (!empty($item_ids) || $item_ids === null) {
        $object_params = array(
            'pid'           => $item_ids,
            'custom_extend' => ['product_name', 'categories', 'description', 'full_description'],
            'sort_by'       => 'timestamp'
        );
        $lang_code = !empty($template['lang_code']) ? $template['lang_code'] : CART_LANGUAGE;
        $step = 100;
        $page = 1;
        while (true) {
            $object_params['page'] = $page;
            list($finded_products, $search) = fn_get_products($object_params, $step, $lang_code);
            if (empty($finded_products)) {
                break;
            }
            foreach($finded_products as &$prod_data) {
                $prod_data['thread_id'] = db_get_field("SELECT thread_id FROM ?:discussion WHERE object_id = ?i AND object_type = ?s", $prod_data['product_id'], 'P');
                if (!empty($prod_data['thread_id'])) {
                    $cp_seo = db_get_row("SELECT ?:cp_pr_for_seo.* FROM ?:cp_pr_for_seo
                        LEFT JOIN ?:discussion ON ?:discussion.thread_id = ?:cp_pr_for_seo.thread_id
                        WHERE ?:discussion.object_id = ?i AND ?:discussion.object_type = ?s AND ?:cp_pr_for_seo.lang_code = ?s", $prod_data['product_id'], 'P', $lang_code
                    );
                    $prod_data = array_merge($cp_seo, $prod_data);
                }
            }
            $products = array_merge($products, $finded_products);
            $page++;
        }
    }
    return $products;
}

function fn_cp_pr_seo_step_products_reviews($template = array(), $page = 1, $step = 100)
{
    $params = array(
        'page' => $page,
        'items_per_page' => $step
    );
    $item_ids = fn_cp_st_get_product_ids($template, $params);

    $products = array();
    if (!empty($item_ids) || $item_ids === null) {
        $object_params = array(
            'pid'           => $item_ids,
            'custom_extend' => ['product_name', 'categories', 'description', 'full_description'],
            'sort_by'       => 'timestamp'
        );
        if ($item_ids === null) {
            $object_params = array_merge($object_params, $params);
        }
        $lang_code = !empty($template['lang_code']) ? $template['lang_code'] : CART_LANGUAGE;
        
        list($products) = fn_get_products($object_params, $step, $lang_code);
        foreach($products as &$prod_data) {
            $prod_data['thread_id'] = db_get_field("SELECT thread_id FROM ?:discussion WHERE object_id = ?i AND object_type = ?s", $prod_data['product_id'], 'P');
            if (!empty($prod_data['thread_id'])) {
                $cp_seo = db_get_row("SELECT ?:cp_pr_for_seo.* FROM ?:cp_pr_for_seo
                    LEFT JOIN ?:discussion ON ?:discussion.thread_id = ?:cp_pr_for_seo.thread_id
                    WHERE ?:discussion.object_id = ?i AND ?:discussion.object_type = ?s AND ?:cp_pr_for_seo.lang_code = ?s", $prod_data['product_id'], 'P', $lang_code
                );
                $prod_data = array_merge($cp_seo, $prod_data);
            }
        }
    }
    return $products;
}

function fn_cp_pr_seo_update_prod_reviews_data($object_data, $prod_data = array(), $template = array())
{
    if (empty($object_data['product_id']) || empty($object_data['thread_id'])) {
        return;
    }
    $product_id = $object_data['product_id'];
    $prod_data['thread_id'] = $object_data['thread_id'];
    $lang_code = !empty($template['lang_code']) ? $template['lang_code'] : CART_LANGUAGE;
    $prod_data['lang_code'] = $lang_code;
    $object_data['name'] = $prod_data['name'] = $object_data['product'];
    $prod_data['h1'] = !empty($prod_data['cp_st_h1']) ? $prod_data['cp_st_h1'] : '';
    
    db_replace_into('cp_pr_for_seo', $prod_data);

    if (!empty($prod_data['seo_name']) && (Registry::get('addons.seo.single_url') != 'Y' || $lang_code == CART_LANGUAGE)
    ) {
        $object_data['seo_name'] = fn_generate_name($prod_data['seo_name']);
        fn_seo_update_object($object_data, $prod_data['thread_id'], CP_PR_OBJECT_SEO_KEY, $lang_code);
    }
}
