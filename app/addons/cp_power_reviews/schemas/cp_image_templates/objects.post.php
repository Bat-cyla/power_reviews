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


$is_vendor = fn_allowed_for('MULTIVENDOR') && !empty(Tygh\Registry::get('runtime.company_id')) ? true : false;

if (!$is_vendor) {
    $schema['R'] = [
        'name'              => 'reviews',
        'title'             => AREA != 'C' ? __('cp_pr_reviews_txt') : '',
        'conditions'        => ['R'],
        'get_function'      => 'fn_cp_pr_get_alt_reviews',
        'update_function'   => 'fn_cp_ait_update_image_pairs',
        'fields'            => ['title' => true, 'alt' => true, 'img_name' => true],
        'img_type'          => 'detailed',
        'object_type'       => ['cp_rev_post'],
    ];
}

return $schema;