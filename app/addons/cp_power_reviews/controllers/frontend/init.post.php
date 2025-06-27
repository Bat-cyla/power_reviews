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

if (!empty($_REQUEST['cp_like']) && !empty($_REQUEST['post_id'])) {
    list($show_reg_form, $like_added, $new_values) = fn_cp_power_reviews_add_like_to_post($_REQUEST['cp_like'], $_REQUEST['post_id'], Tygh::$app['session']['auth'], $_REQUEST['object_type']);
    
    Tygh::$app['ajax']->assign('cp_pr_show_reg_link', $show_reg_form);
    Tygh::$app['ajax']->assign('cp_is_like_added', $like_added);
    Tygh::$app['ajax']->assign('cp_is_like_values', $new_values);
    
    exit;
}
$rev_settings = Registry::get('addons.cp_power_reviews');
if ($controller == 'products' && $mode == 'view' && ($rev_settings['show_videos_in_post'] == 'Y' || $rev_settings['include_videos'] == 'Y')) {
    Tygh::$app['view']->assign('cp_pr_need_yt_script', true);
} elseif ($controller == 'categories' && $mode == 'view' && ($rev_settings['show_videos_in_post_cat'] == 'Y' || $rev_settings['include_videos_cat'] == 'Y')) {
    Tygh::$app['view']->assign('cp_pr_need_yt_script', true);
} elseif ($controller == 'pages' && $mode == 'view' && ($rev_settings['show_videos_in_post_page'] == 'Y' || $rev_settings['include_videos_page'] == 'Y')) {
    Tygh::$app['view']->assign('cp_pr_need_yt_script', true);
} elseif ($controller == 'discussion' && $mode == 'view' && ($rev_settings['show_videos_in_post_test'] == 'Y' || $rev_settings['include_videos_test'] == 'Y')) {
    Tygh::$app['view']->assign('cp_pr_need_yt_script', true);
} elseif ($controller == 'companies' && $mode == 'view' && ($rev_settings['show_videos_in_post_vend'] == 'Y' || $rev_settings['include_videos_vend'] == 'Y')) {
    Tygh::$app['view']->assign('cp_pr_need_yt_script', true);
} elseif ($controller == 'cp_pow_rev' && in_array($mode, ['all_reviews']) && $rev_settings['show_videos_in_post_ap'] == 'Y') {
    Tygh::$app['view']->assign('cp_pr_need_yt_script', true);
} elseif ($controller == 'cp_pow_rev' && in_array($mode, ['view','store_reviews','my_reviews'])) {
    Tygh::$app['view']->assign('cp_pr_need_yt_script', true);
}