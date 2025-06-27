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

    if ($mode == 'cp_reply_vendor') {
        if (Registry::get('addons.cp_power_reviews.allow_reply_rev') != 'Y') {
            return [CONTROLLER_STATUS_DENIED];
        }

        if (!empty($_REQUEST['cp_post_data'])) {
            fn_cp_power_reviews_update_reply($_REQUEST['cp_post_data']);
        }

        if (!empty($_REQUEST['redirect_url'])) {
            return array(CONTROLLER_STATUS_OK, fn_url($_REQUEST['redirect_url']));
        }
    }
    if ($mode == 'rp_approve') {
        fn_cp_power_reviews_approve_reply($_REQUEST['post_id']);

        if (!empty($_REQUEST['redirect_url'])) {
            return array(CONTROLLER_STATUS_OK, fn_url($_REQUEST['redirect_url']));
        }
    } elseif ($mode == 'rp_decline') {
        $post_id = 0;
        if ($action) {
            $post_id = $action;
        }

        fn_cp_power_reviews_disapprove_reply($post_id, $_REQUEST['cp_rp_approval']);

        if (!empty($_REQUEST['redirect_url'])) {
            return array(CONTROLLER_STATUS_OK, fn_url($_REQUEST['redirect_url']));
        }
    }
    if ($mode == 'm_delete') {
        if (!empty($_REQUEST['delete_posts']) && is_array($_REQUEST['delete_posts'])) {
            foreach ($_REQUEST['delete_posts'] as $p_id => $v) {
                db_query("DELETE FROM ?:cp_pow_attr_ratings WHERE post_id = ?i", $p_id);
                db_query("DELETE FROM ?:cp_review_images WHERE post_id = ?i", $p_id);
                fn_delete_image_pairs($p_id, 'cp_rev_post');
            }
        }
    }
    if ($mode == 'delete') {
        if (!empty($_REQUEST['post_id'])) {
            db_query("DELETE FROM ?:cp_pow_attr_ratings WHERE post_id = ?i", $_REQUEST['post_id']);
            db_query("DELETE FROM ?:cp_review_images WHERE post_id = ?i", $_REQUEST['post_id']);
            fn_delete_image_pairs($_REQUEST['post_id'], 'cp_rev_post');
        }
    }
    if ($mode == 'cp_delete_recom') {
        if (!empty($_REQUEST['thread_id']) && !empty($_REQUEST['ip'])) {
            fn_cp_pr_delete_thread_recommendation($_REQUEST['thread_id'], $_REQUEST['ip']);
        }
    }
}