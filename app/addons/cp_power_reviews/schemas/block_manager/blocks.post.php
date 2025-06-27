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

if (fn_allowed_for('MULTIVENDOR')) {
    $schema['cp_vendor_attr_rating'] = array(
        'templates' => array(
            'addons/cp_power_reviews/blocks/cp_vendor_attr_rating.tpl' => array(),
        ),
        'wrappers' => 'blocks/wrappers',
        'content' => array(
            'cp_attr_data' => array(
                'type' => 'function',
                'function' => array('fn_cp_power_reviews_bl_get_vendor_attr_info'),
            )
        ),
        'cache' => array(
            'update_handlers' => array('companies', 'company_descriptions', 'logos', 'images_links', 'images', 'discussion', 'discussion_posts'),
            'request_handlers' => array('company_id')
        ),
    );
    $schema['cp_vendor_logo_rating'] = array(
        'templates' => array(
            'addons/cp_power_reviews/blocks/cp_vendor_logo_rating.tpl' => array(),
        ),
        'wrappers' => 'blocks/wrappers',
        'content' => array(
            'vendor_info' => array(
                'type' => 'function',
                'function' => array('fn_cp_power_reviews_bl_get_vendor_info'),
            )
        ),
        'cache' => array(
            'update_handlers' => array('companies', 'company_descriptions', 'logos', 'images_links', 'images', 'discussion', 'discussion_posts'),
            'request_handlers' => array('company_id')
        ),
        'settings' => array(
            'cp_show_vend_name' => array(
                'type' => 'checkbox',
                'default_value' => 'N'
            ),
            'cp_show_vend_btn' => array(
                'type' => 'checkbox',
                'default_value' => 'N'
            ),
            'cp_show_vend_rate' => array(
                'type' => 'checkbox',
                'default_value' => 'N'
            ),
        ),
    );
}
$schema['cp_prev_power_reviews'] = array (
    'content' => array (
        'items' => array (
            'remove_indent' => true,
            'hide_label' => true,
            'type' => 'enum',
            'object' => 'power_reviews',
            'items_function' => 'fn_cp_power_reviews_get_power_reviews',
            'fillings' => array (
                'manually' => array (
                    'picker' => 'addons/cp_power_reviews/pickers/power_reviews/picker.tpl',
                    'picker_params' => array (
                        'type' => 'links',
                    ),
                    'params' => array (
                        'sort_by' => 'timestamp',
                        'sort_order' => 'desc'
                    )
                ),
                'prod_reviews_from_current_object' => array (
                    'params' => array (
                        'sort_by' => 'timestamp',
                        'sort_order' => 'desc',
                        'request' => array (
                            'cid' => '%CATEGORY_ID%',
                        )
                    )
                ),
                'cp_pr_reviews' => array (
                    'params' => array (
                        'sort_by' => 'timestamp',
                        'sort_order' => 'desc',
                        'request' => array (
                            'ccid' => '%CATEGORY_ID%',
                        )
                    )
                ),
                'cp_pr_testimonials' => array (
                    'params' => array (
                        'sort_by' => 'timestamp',
                        'sort_order' => 'desc'
                    )
                ),
                'cp_pr_pages' => array (
                    'params' => array (
                        'sort_by' => 'timestamp',
                        'sort_order' => 'desc',
                        'request' => array (
                            'pgid' => '%PAGE_ID%'
                        )
                    )
                ),
                'cp_pr_categories' => array (
                    'params' => array (
                        'sort_by' => 'timestamp',
                        'sort_order' => 'desc',
                        'request' => array (
                            'cid' => '%CATEGORY_ID%',
                        )
                    )
                ),
            ),
        ),
    ),
    'templates' => array (
        'addons/cp_power_reviews/blocks/slider.tpl' => array(
        'settings' => array (
                'limit' => array (
                    'type' => 'input',
                    'default_value' => '20'
                ),
                'cp_last_days' => array (
                    'type' => 'input',
                    'default_value' => '10'
                ), 
                'cp_fill_type' => array (
                    'type' => 'selectbox',
                    'values' => array(
                        'RND' => 'random',
                        'TPR' => 'cp_sort_hight_rate',
                        'LWR' => 'cp_sort_low_rate',
                        'MSH' => 'cp_sort_most_help',
                        'NEW' => 'cp_sort_newest',
                    ),
                    'default_value' => 'random'
                ),
                'max_rating_limit' => array (
                    'type' => 'input',
                    'default_value' => '5'
                ),
                'min_rating_limit' => array (
                    'type' => 'input',
                    'default_value' => '1'
                ),
                'cp_sort_attributes_only' => array (
                    'type' => 'checkbox',
                    'default_value' => 'ATR'
                ),
                'show_object_icon' => array (
                    'type' => 'checkbox',
                    'default_value' => 'Y'
                ),  
                'object_image_width' => array (
                    'type' => 'input',
                    'default_value' => '80'
                ), 
                'object_image_height' => array (
                    'type' => 'input',
                    'default_value' => '80'
                ),
                'show_review_attrs' => array (
                    'type' => 'checkbox',
                    'default_value' => 'Y'
                ),
                'cp_pr_show_msg_title' => array (
                    'type' => 'checkbox',
                    'default_value' => 'N'
                ),
                'cp_pr_show_adv' => array (
                    'type' => 'checkbox',
                    'default_value' => 'N'
                ),
                'cp_pr_message_height' =>  array (
                    'type' => 'input',
                    'default_value' => 200
                ),
                'not_scroll_automatically' => array (
                    'type' => 'checkbox',
                    'default_value' => 'N'
                ),
                'speed' =>  array (
                    'type' => 'input',
                    'default_value' => 400
                ),
                'pause_delay' =>  array (
                    'type' => 'input',
                    'default_value' => 3
                ),
                'item_quantity' =>  array (
                    'type' => 'input',
                    'default_value' => 3
                ),
                'outside_navigation' => array (
                    'type' => 'checkbox',
                    'default_value' => 'Y'
                ),
                'cp_show_view_all_btn' => array (
                    'type' => 'checkbox',
                    'default_value' => 'N'
                ),
            ), 
        ),
    ),
    'wrappers' => 'blocks/wrappers'
);
if (fn_allowed_for('MULTIVENDOR')) {
    $schema['cp_prev_power_reviews']['content']['items']['fillings']['cp_pr_vendors'] = array(
        'params' => array (
            'sort_by' => 'timestamp',
            'sort_order' => 'desc',
            'request' => array (
                'cmpid' => '%COMPANY_ID%',
                'cpid' => '%PRODUCT_ID%',
            )
        )
    );
    $schema['content']['items']['fillings']['prod_reviews_from_current_object']['params']['request']['cmpid'] = '%COMPANY_ID%';
}
if (Registry::get('addons.cp_power_blog.status') == 'A') {
    $schema['cp_prev_power_reviews']['content']['items']['fillings']['cp_pr_power_blog'] = array (
        'params' => array (
            'sort_by' => 'timestamp',
            'sort_order' => 'desc',
            'request' => array (
                'postid' => '%POST_ID%'
            )
        )
    );
}

return $schema;
