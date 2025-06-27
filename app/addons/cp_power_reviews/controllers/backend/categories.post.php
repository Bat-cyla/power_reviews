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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'update') {
        fn_trusted_vars('cp_post_data');
        if (!empty($_REQUEST['cp_post_data']) && fn_discussion_check_update_posts_permission($_REQUEST['cp_post_data'], $auth)) {
            fn_cp_power_reviews_update_discussion_posts($_REQUEST['cp_post_data']);
            fn_cp_power_reviews_update_sred_rate_from_posts($_REQUEST['cp_post_data']);
        }
    }
}
    
if ($mode == 'update') {
    if (fn_allowed_for('MULTIVENDOR') || fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
        Registry::set('navigation.tabs.cp_reviews_attrs', array (
            'title' => __('cp_reviews_attrs'),
            'js' => true
        ));
        $extrem_types = fn_cp_pr_get_view_type_langs();
        Registry::get('view')->assign('cp_pr_exterm_types', $extrem_types);
    }
}