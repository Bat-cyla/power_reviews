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

    if ($mode == 'cp_add_rew') {
        if (empty($_REQUEST['no_suffix'])) {
            $suffix = '&selected_section=cp_prod_reviews_tab';
        } else {
            $suffix = '';
        }
        if (AREA == 'C' && empty($_REQUEST['cp_skip_verf'])) {
            if (fn_image_verification('discussion', $_REQUEST) == false) {
                fn_save_post_data('post_data');

                return array(CONTROLLER_STATUS_REDIRECT, $_REQUEST['redirect_url'] . $suffix);
            }
        }
        if (!empty($_REQUEST['post_data'])) {
            if (!empty($_REQUEST['post_data']['thread_id'])) {
                $type = db_get_field("SELECT type FROM ?:discussion WHERE thread_id = ?i", $_REQUEST['post_data']['thread_id']);
                if ($type == 'C' || $type == 'B') {
                    $check_empty_msg = trim($_REQUEST['post_data']['message']);
                    if (empty($check_empty_msg)) {
                        fn_set_notification('E', __('error'), __('you_entered_an_empty_message'));
                        return array(CONTROLLER_STATUS_REDIRECT, $_REQUEST['redirect_url'] . $suffix);
                    }
                }
                $post_id = fn_cp_power_reviews_add_prod_ratings($_REQUEST['post_data']);
                if (!empty($post_id)) {
                    $pairs_data = fn_attach_image_pairs('cp_review_post', 'cp_rev_post', $post_id, DESCR_SL);
                    if (!empty($pairs_data)) {
                        if (!is_array($pairs_data)) {
                            $pairs_data = array($pairs_data);
                        }
                        foreach($pairs_data as $kry => $img_pair_id) {
                            $data_rev_image = array(
                                'post_image_id' => $img_pair_id,
                                'post_id' => $post_id,
                                'status' => 'A'
                            );
                            db_query("INSERT INTO ?:cp_review_images ?e", $data_rev_image);
                        }
                    }
                }
            }
        }
    }
    if ($mode == 'update') {
        fn_trusted_vars('cp_post_data');
        if (!empty($_REQUEST['cp_post_data'])) {
            fn_cp_power_reviews_update_discussion_posts($_REQUEST['cp_post_data']);
        }
    }
}
if ($mode == 'cp_del_post_img') {
    if (AREA == 'C') {
        exit;
    }
    if (!empty($_REQUEST['post_id']) && !empty($_REQUEST['pair_id'])) {
        db_query("DELETE FROM ?:cp_review_images WHERE post_image_id = ?i AND post_id = ?i", $_REQUEST['pair_id'], $_REQUEST['post_id']);
        fn_delete_image_pair($_REQUEST['pair_id'], 'cp_rev_post');
        $cp_review_pairs = fn_get_image_pairs($_REQUEST['post_id'], 'cp_rev_post', 'A', true, true, CART_LANGUAGE);
        if (!empty($cp_review_pairs)) {
            $post_images_data = db_get_hash_array("SELECT post_image_id, status FROM ?:cp_review_images WHERE post_id = ?i", 'post_image_id', $_REQUEST['post_id']);
            
            if (!empty($post_images_data)) {
                foreach($cp_review_pairs as $pair_id => $img_data) {
                    $post['cp_review_pairs'][$pair_id] = $cp_review_pairs[$pair_id];
                    $post['cp_review_pairs'][$pair_id]['status'] = $post_images_data[$img_data['pair_id']]['status'];
                    $post['post_id'] = $_REQUEST['post_id'];
                }
            }
        } else {
            $post['post_id'] = $_REQUEST['post_id'];
        }
    }
    if (!empty($_REQUEST['post_id']) && !empty($_REQUEST['video_id'])) {
        $res = fn_cp_pp_remove_post_video($_REQUEST['video_id']);
        if (!empty($res)) {
            $post['video_data'] = [];
        }
        $post['post_id'] = $_REQUEST['post_id'];
    }
    if (!empty($_REQUEST['is_preview']) && !empty($_REQUEST['post_id']) && !empty($_REQUEST['vid_id'])) {
        fn_cp_pr_replace_preview_img($_REQUEST['vid_id']);
        
        $post['post_id'] = $_REQUEST['post_id'];
        Tygh::$app['view']->assign('cp_removing_preview', true);
    }
    Tygh::$app['view']->assign('post', $post);
    Registry::get('view')->display('addons/cp_power_reviews/components/post.tpl');
    exit;
} elseif ($mode == 'view') {
    if (!empty($_REQUEST['amp;thread_id'])) {
        $_REQUEST['thread_id'] = $_REQUEST['amp;thread_id'];
    }
} elseif ($mode == 'cp_recommend_object') {
    if (!defined('AJAX_REQUEST')) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }
    if (!empty($_REQUEST['object_id']) && !empty($_REQUEST['object_type']) && !empty($_REQUEST['thread_id']) && !empty($_REQUEST['reccomend'])) {
        $ip = fn_get_ip();
        $ip_address = fn_ip_to_db($ip['host']);
        if (!empty($ip_address)) {
            $result = fn_cp_power_reviews_add_recommendation($_REQUEST['thread_id'], $ip_address, $_REQUEST['reccomend'], $_REQUEST['object_type']);
            if (!empty($result)) {
                $cp_recom_total = db_get_field("SELECT COUNT(thread_id) FROM ?:cp_pow_recomends WHERE thread_id = ?i", $_REQUEST['thread_id']);
                if (empty($cp_recom_total)) {
                    $cp_recom_total = 0;
                }
                $positiv = db_get_field("SELECT COUNT(thread_id) FROM ?:cp_pow_recomends WHERE thread_id = ?i AND type = ?s", $_REQUEST['thread_id'], 'U');
                if (empty($positiv)) {
                    $positiv = 0;
                }
                if (!empty($positiv) && !empty($cp_recom_total)) {
                    $cp_recom_proc = round(100*($positiv/$cp_recom_total));
                }
                $discussion = [
                    'type'              => 'B',
                    'object_id'         => $_REQUEST['object_id'],
                    'object_type'       => $_REQUEST['object_type'],
                    'thread_id'         => $_REQUEST['thread_id'],
                    'cp_recom_total'    => !empty($cp_recom_total) ? $cp_recom_total : 0,
                    'cp_recom_proc'     => !empty($cp_recom_proc) ? $cp_recom_proc : 0,
                    'cp_recom_positiv'  => $positiv,
                    'posts'             => [
                        1 => []
                    ]
                ];
                Tygh::$app['view']->assign('back_disc', $discussion);
                Tygh::$app['view']->assign('object_id', $_REQUEST['object_id']);
                Tygh::$app['view']->assign('object_type', $_REQUEST['object_type']);
                if ($_REQUEST['object_type'] == 'P') {
                    Registry::get('view')->display('addons/cp_power_reviews/views/cp_power_reviews/rew_views.tpl');
                } else {
                    Registry::get('view')->display('addons/discussion/views/discussion/view.tpl');
                }
            }
        }
    }
    exit;
}