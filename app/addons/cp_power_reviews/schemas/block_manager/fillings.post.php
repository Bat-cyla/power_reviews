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
$schema['cp_pr_testimonials'] = array(
    'cp_pr_show_review_btn' => array (
        'type' => 'checkbox',
        'default_value' => 'N'
    )
);

$schema['cp_pr_reviews'] = array (
    'productid' => array (
        'type' => 'picker',
        'option_name' => 'filter_by_products',
        'picker' => 'addons/cp_power_reviews/pickers/products/picker.tpl',
        'picker_params' => array(
            'multiple' => true,
            'use_keys' => 'N',
            'view_mode' => 'table',
            'no_item_text' => __('default_filter_by_location'),
        ),
        'unset_empty' => true, // remove this parameter from params list if the value is empty
    ),
    'b_cid' => array (
        'type' => 'picker',
        'option_name' => 'filter_by_categories',
        'picker' => 'pickers/categories/picker.tpl',
        'picker_params' => array(
            'multiple' => true,
            'use_keys' => 'N',
            'view_mode' => 'table',
            'no_item_text' => __('default_filter_by_location'),
        ),
        'unset_empty' => true, // remove this parameter from params list if the value is empty
    ),
    'cp_take_from_sub_cat' => array (
        'type' => 'checkbox',
        'default_value' => 'N'
    )
);
$schema['prod_reviews_from_current_object'] = array (
    'cp_from_cur_oject_type' => array (
        'type' => 'selectbox',
        'values' => array (
            'C' => 'category',
        ),
        'default_value' => 'category'
    ),
    'cp_take_from_sub_cat' => array (
        'type' => 'checkbox',
        'default_value' => 'N'
    )
);

$schema['cp_pr_pages'] = array (
    'cp_take_from_cur_page' => array (
        'type' => 'checkbox',
        'default_value' => 'N'
    ),
    'pageid' => array (
        'type' => 'picker',
        'option_name' => 'filter_by_pages',
        'picker' => 'addons/cp_power_reviews/pickers/pages/picker.tpl',
        'picker_params' => array(
            'multiple' => true,
            'use_keys' => 'N',
            'view_mode' => 'table',
            'no_item_text' => __('default_filter_by_location'),
        ),
        'unset_empty' => true, // remove this parameter from params list if the value is empty
    ),
);
$schema['cp_pr_categories'] = array (
    'cp_take_from_cur_cat' => array (
        'type' => 'checkbox',
        'default_value' => 'N'
    ),
    'cp_take_from_sub_cat' => array (
        'type' => 'checkbox',
        'default_value' => 'N'
    ),
    'b_cid' => array (
        'type' => 'picker',
        'option_name' => 'filter_by_categories',
        'picker' => 'pickers/categories/picker.tpl',
        'picker_params' => array(
            'multiple' => true,
            'use_keys' => 'N',
            'view_mode' => 'table',
            'no_item_text' => __('default_filter_by_location'),
        ),
        'unset_empty' => true, // remove this parameter from params list if the value is empty
    )
);
if (fn_allowed_for('MULTIVENDOR')) {
    $schema['cp_pr_vendors'] = array (
        'cp_take_from_cur_vendor' => array (
            'type' => 'checkbox',
            'default_value' => 'N'
        ),
        'cp_pr_show_review_btn' => array (
            'type' => 'checkbox',
            'default_value' => 'N'
        ),
        'vend_id' => array (
            'type' => 'picker',
            'option_name' => 'cp_filter_by_vendors',
            'picker' => 'pickers/companies/picker.tpl',
            'picker_params' => array(
                'multiple' => true,
                'use_keys' => 'N',
                'view_mode' => 'table',
                'no_item_text' => __('default_filter_by_location'),
            ),
            'unset_empty' => true, // remove this parameter from params list if the value is empty
        ),
    );
    $schema['cp_pr_reviews']['pr_vend_id'] = array(
        'type' => 'picker',
        'option_name' => 'cp_filter_by_vendors',
        'picker' => 'pickers/companies/picker.tpl',
        'picker_params' => array(
            'multiple' => true,
            'use_keys' => 'N',
            'view_mode' => 'table',
            'no_item_text' => __('default_filter_by_location'),
        ),
        'unset_empty' => true,
    );
    $schema['prod_reviews_from_current_object']['cp_from_cur_oject_type']['values']['V'] = 'vendor';
}
if (Registry::get('addons.cp_power_blog.status') == 'A') {
    $schema['cp_pr_power_blog']['cp_take_from_cur_page'] = array (
        'type' => 'checkbox',
        'default_value' => 'N'
    );
    $schema['cp_pr_power_blog']['blogid'] = array (
        'type' => 'picker',
        'option_name' => 'cp_pr_filter_by_articles',
        'picker' => 'addons/cp_power_blog/pickers/posts/picker.tpl',
        'picker_params' => array(
            'type' => 'links',
            'multiple' => true,
            'use_keys' => 'N',
            'view_mode' => 'table',
            'no_item_text' => __('default_filter_by_location'),
        ),
        'unset_empty' => true, 
    );
}
return $schema;
