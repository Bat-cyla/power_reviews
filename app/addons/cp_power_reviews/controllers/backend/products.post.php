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

/* POST data processing */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'update') {
        fn_trusted_vars('cp_post_data');
        if (!empty($_REQUEST['cp_post_data'])) {
            fn_cp_power_reviews_update_discussion_posts($_REQUEST['cp_post_data']);
        }
        if (!empty($_REQUEST['cp_object_seo'])) {
            fn_cp_pr_update_discussion_seo($_REQUEST['cp_object_seo']);
        }
    }
    return;
}
if ($mode == 'update') {
    if (fn_allowed_for('MULTIVENDOR') || fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
        Registry::set('navigation.tabs.cp_reviews_attrs', [
            'title' => __('cp_pr_reviews_settings'),
            'js'    => true
        ]);
    }
    $discussion = Registry::get('view')->getTemplateVars('discussion');
    if (!empty($discussion) && $discussion['type'] != 'D') {
        if (fn_check_permissions('discussion', 'manage', 'admin')) {
            $recommendations = fn_cp_pr_get_thread_recommendations($discussion['thread_id']);
            if (!empty($recommendations)) {
                Registry::set('navigation.tabs.cp_pr_recomends', [
                    'title' => __('cp_pr_recommends_manage'),
                    'js'    => true
                ]);
                Tygh::$app['view']->assign('cp_pr_recommend', $recommendations);
            }
        }
    }
    $extrem_types = fn_cp_pr_get_view_type_langs();
    Tygh::$app['view']->assign('cp_pr_exterm_types', $extrem_types);
    
    $review_pl = fn_get_schema('cp_pr', 'placeholders');
    if (!empty($review_pl)) {
        Tygh::$app['view']->assign('cp_pf_avail_placeholders', $review_pl);
    }
    if (fn_allowed_for('MULTIVENDOR') || fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
        $product_data = Registry::get('view')->getTemplateVars('product_data');
        if (!empty($product_data['variation_parent_product_id'])) {
            Registry::set('navigation.tabs.cp_pr_discussion', [
                'title' => __('cp_pr_reviews_settings'),
                'js'    => true
            ]);
        }
    }

    if (Registry::get('addons.cp_power_reviews.allow_reply_rev') == "Y") {
        Tygh::$app['view']->assign('cp_reply_active', 'Y');
    }
}