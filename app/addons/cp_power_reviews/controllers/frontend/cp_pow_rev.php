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

    if ($mode == 'update_review') {
        if (!empty($_REQUEST['post_data']) && !empty($_REQUEST['post_data']['post_id']) && !empty($auth['user_id'])) {
            $check_allows = fn_cp_check_permissions_for_reviews($_REQUEST['post_data']['post_id'], 'E', $auth['user_id']);
            if (!empty($_REQUEST['post_data']['thread_id']) && !empty($check_allows)) {
                $type = db_get_field("SELECT type FROM ?:discussion WHERE thread_id = ?i", $_REQUEST['post_data']['thread_id']);
                if ($type == 'C' || $type == 'B') {
                    $check_empty_msg = trim($_REQUEST['post_data']['message']);
                    if (empty($check_empty_msg)) {
                        fn_set_notification('E', __('error'), __('you_entered_an_empty_message'));
                        return [CONTROLLER_STATUS_REDIRECT, $_REQUEST['redirect_url']];
                    }
                }
                fn_cp_pr_update_own_review($_REQUEST['post_data']['post_id'], $_REQUEST['post_data']);
            }
        }
        return [CONTROLLER_STATUS_REDIRECT, $_REQUEST['redirect_url']];
    }
    if ($mode == 'delete_review') {
        if (!empty($_REQUEST['post_id']) && !empty($auth['user_id'])) {
            $check_allows = fn_cp_check_permissions_for_reviews($_REQUEST['post_id'], 'D', $auth['user_id']);
            if (!empty($check_allows)) {
                fn_cp_pr_delete_by_user($_REQUEST['post_id']);
            }
        }
        return [CONTROLLER_STATUS_REDIRECT, 'cp_pow_rev.my_reviews'];
    }
    if ($mode == 'cp_del_post_img') {
        if (!empty($_REQUEST['post_id']) && !empty($_REQUEST['video_id']) && !empty($_REQUEST['object_type'])) {
            $res = fn_cp_pp_remove_post_video($_REQUEST['video_id']);
            if (!empty($res)) {
                fn_set_notification('N', __('notice'), __('done'));
                $post['video_data'] = [];
            }
            $post['post_id'] = $_REQUEST['post_id'];
            $post['object_type'] = $_REQUEST['object_type'];
            Tygh::$app['view']->assign('r_post', $post);
            Registry::get('view')->display('addons/cp_power_reviews/components/my_review_post.tpl');
        }
        
        exit;
    }
    if ($mode == 'upload') {
        $rebuilt = fn_rebuild_files('file');
        $file = reset($rebuilt);

        if (empty($file)) {
            exit;
        }

        $file_extension = fn_get_file_ext($file['name']);

        if (!fn_is_file_extension_allowed($file_extension)) {
            exit;
        }

        $file = fn_move_uploaded_file($file);

        Tygh::$app['ajax']->assign('local_data', $file);
        exit;
    }

    return [CONTROLLER_STATUS_OK];
}
if ($mode == 'all_reviews') {
    if (empty($_REQUEST['id'])) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }
    fn_add_breadcrumb(__('cp_pr_all_reviews'));
    $params = [];
    $params = $_REQUEST;
    $params['cp_is_all_page'] = true;
    $object_types = Registry::get('addons.cp_power_reviews.objects_for_all');
    if (!empty($object_types) && empty($params['cp_object_types'])) {
        $object_types = array_keys(array_change_key_case($object_types, CASE_UPPER), 'Y');
        if (!empty($object_types)) {
            $params['cp_object_types'] = $object_types;
        }
    }
    list($pre_discussion, $search) = fn_cp_power_reviews_get_power_reviews($params, Registry::get('settings.Appearance.elements_per_page'));
    $discussion = [
        'type'          => 'B',
        'object_type'   => 'ALL'
    ];
    $discussion['search'] = $search;
    $discussion['posts'] = $pre_discussion;
    $discussion['cp_login_url'] = fn_url('auth.login_form','C');
    $discussion['cp_top_help'] = (!empty($search['for_disc']) && !empty($search['for_disc']['cp_top_help'])) ? $search['for_disc']['cp_top_help'] : [];
    $discussion['most_u_post'] = (!empty($search['for_disc']) && !empty($search['for_disc']['most_u_post'])) ? $search['for_disc']['most_u_post'] : [];
    $discussion['most_h_post'] = (!empty($search['for_disc']) && !empty($search['for_disc']['most_h_post'])) ? $search['for_disc']['most_h_post'] : [];
    $discussion['all_positive_posts'] = (!empty($search['for_disc']) && !empty($search['for_disc']['all_positive_posts'])) ? $search['for_disc']['all_positive_posts'] : [];
    $discussion['all_critical_posts'] = (!empty($search['for_disc']) && !empty($search['for_disc']['all_critical_posts'])) ? $search['for_disc']['all_critical_posts'] : [];
    
    $data = fn_cp_pr_get_object_data($params);
    if (!empty($data)) {
        if (!empty($data['cp_name'])) {
            fn_add_breadcrumb($data['cp_name']);
        } else {
            $discussion_object_data = fn_get_discussion_object_data($data['object_id'], $data['object_type']);
            fn_add_breadcrumb($discussion_object_data['description'], $discussion_object_data['url']);
        }
    }

    $all_types_full = fn_settings_variants_addons_cp_power_reviews_objects_for_all();
    
    $all_types = Registry::get('addons.cp_power_reviews.objects_for_all');
    
    Tygh::$app['view']->assign('all_types_full', $all_types_full);
    Tygh::$app['view']->assign('all_types', $all_types);
    Tygh::$app['view']->assign('discussion', $discussion);
    Tygh::$app['view']->assign('object_type', 'ALL');
    Tygh::$app['view']->assign('object_id', 'ALL_R');
    
} elseif ($mode == 'view') {
    if (!empty($_REQUEST['thread_id'])) {
        $params = $_REQUEST;;
        $thread_data = fn_discussion_get_object($params);
        $addons = Registry::get('addons');
        if (!empty($thread_data['type']) && $thread_data['type'] == 'D' || !in_array($thread_data['object_type'], ['P'])) {
            return [CONTROLLER_STATUS_NO_PAGE];
        }
        if (!empty($thread_data) && !empty($thread_data['thread_id'])) {
        
            $avail_arguments = ['page','r_limit','cp_post_kind','cp_filter_stars','cp_pr_with_images','sl','cp_sort_by','from_prod_tab'];
            foreach($avail_arguments as $arg_name) {
                if (!empty($params[$arg_name])) {
                    $thread_data[$arg_name] = $params[$arg_name];
                }
            }
            $get_posts = true;
            if ($thread_data['object_type'] == 'P' && !empty($addons['product_variations']['status']) && $addons['product_variations']['status'] == 'A' && $addons['cp_power_reviews']['common_for_variations'] == 'Y') {
                $thread_data['from_prod_tab'] = 1;
                $thread_data['product_id'] = $thread_data['object_id'];
                $get_posts = false;
            }
            $discussion = fn_get_discussion($thread_data['object_id'], $thread_data['object_type'], $get_posts, $thread_data);
            
            list($object_link, $object_name, $object_type) = fn_cp_pr_get_object_link($thread_data);
            if (fn_allowed_for('MULTIVENDOR')) {
                $cur_comp_id = 0;
            } else {
                $cur_comp_id = Registry::get('runtime.company_id');
            }
            $all_reviews_id = db_get_field("SELECT id FROM ?:cp_pr_reviews_seo WHERE company_id = ?i", $cur_comp_id);
            if (!empty($object_name) && !empty($object_link)) {
                fn_add_breadcrumb(__('cp_pr_reviews_txt'), fn_url('cp_pow_rev.all_reviews?id=' . $all_reviews_id, 'C'));
                fn_add_breadcrumb($object_name, $object_link);
            } elseif ($thread_data['object_type'] == 'E') {
                fn_add_breadcrumb(__('cp_pr_reviews_txt'), fn_url('cp_pow_rev.all_reviews?id=' . $all_reviews_id, 'C'));
                fn_add_breadcrumb(__('cp_pr_testimonials'));
            }
            if (!empty($discussion)) {
                $discussion = fn_cp_pr_get_additional_data_for_object($discussion, CART_LANGUAGE);
            }
            $review_pl = fn_get_schema('cp_pr', 'placeholders');
            $meta_data = [
                'title'             => '',
                'meta_description'  => '',
                'meta_keywords'     => ''
            ];
            if (!empty($discussion['cp_seo'])) {
                if (!empty($discussion['cp_seo']['page_title'])) {
                    $meta_data['title'] = $discussion['cp_seo']['page_title'];
                }
                if (!empty($discussion['cp_seo']['meta_description'])) {
                    $meta_data['meta_description'] = $discussion['cp_seo']['meta_description'];
                }
                if (!empty($discussion['cp_seo']['meta_keywords'])) {
                    $meta_data['meta_keywords'] = $discussion['cp_seo']['meta_keywords'];
                }
                if (!empty($discussion['cp_seo']['h1'])) {
                    if (!empty($review_pl)) {
                        foreach($review_pl as $pl_key => $pl_data) {
                            if (!empty($pl_data['field'])) {
                                $expld_fields = explode('///', $pl_data['field']);
                                $field_path = $discussion;
                                foreach($expld_fields as $exp_fields) {
                                    if (!empty($field_path[$exp_fields])) {
                                        $field_path = $field_path[$exp_fields];
                                    } else {
                                        $field_path = '';
                                        break;
                                    }
                                }
                                if (!empty($pl_data['extra_lang']) && !empty($field_path)) {
                                    $field_path = __($pl_data['extra_lang'], [$field_path]);
                                }
                                $discussion['cp_seo']['h1'] = str_replace('[' . $pl_key . ']', !empty($field_path) ? $field_path : '', $discussion['cp_seo']['h1']);
                                $discussion['cp_seo']['h1'] = str_replace('{{ ' . $pl_key . ' }}', !empty($field_path) ? $field_path : '', $discussion['cp_seo']['h1']);
                            }
                        }
                    }
                }
                if (Registry::get('addons.cp_seo_templates.status') == 'A' && Registry::get('addons.cp_seo_templates.use_custom_h1') == 'Y') {
                    fn_cp_seo_assign_object_h1('r', $discussion['cp_seo']);
                    if (!empty($discussion['cp_seo']['h1'])) {
                        Tygh::$app['view']->assign('custom_h1', $discussion['cp_seo']['h1']);
                    }
                }
            }
            $location_data = Registry::get('view')->getTemplateVars('location_data');
            foreach(['title','meta_description','meta_keywords'] as $meta_field) {
                if (empty($meta_data[$meta_field]) && !empty($location_data[$meta_field])) {
                    $meta_data[$meta_field] = $location_data[$meta_field];
                }
                if (!empty($meta_data[$meta_field])) {
                    if (!empty($review_pl)) {
                        foreach($review_pl as $pl_key => $pl_data) {
                            if (!empty($pl_data['field'])) {
                                $expld_fields = explode('///', $pl_data['field']);
                                $field_path = $discussion;
                                foreach($expld_fields as $exp_fields) {
                                    if (!empty($field_path[$exp_fields])) {
                                        $field_path = $field_path[$exp_fields];
                                    } else {
                                        $field_path = '';
                                        break;
                                    }
                                }
                                if (!empty($pl_data['extra_lang']) && !empty($field_path)) {
                                    $field_path = __($pl_data['extra_lang'], [$field_path]);
                                }
                                $meta_data[$meta_field] = str_replace('[' . $pl_key . ']', !empty($field_path) ? $field_path : '', $meta_data[$meta_field]);
                                $meta_data[$meta_field] = str_replace('{{ ' . $pl_key . ' }}', !empty($field_path) ? $field_path : '', $meta_data[$meta_field]);
                            }
                        }
                    }
                    
                    if ($meta_field == 'title') {
                        Tygh::$app['view']->assign('page_title', $meta_data[$meta_field]);
                    } else {
                        Tygh::$app['view']->assign($meta_field, $meta_data[$meta_field]);
                    }
                }
            }
            
            Tygh::$app['view']->assign('search', $discussion['search']);
            Tygh::$app['view']->assign('discussion', $discussion);
            Tygh::$app['view']->assign('object_type', $thread_data['object_type']);
            Tygh::$app['view']->assign('object_id', $thread_data['object_id']);
        }
    } else {
        return [CONTROLLER_STATUS_NO_PAGE];
    }
} elseif ($mode == 'store_reviews') {
    $params = $_REQUEST;
    $params['object_type'] = 'E';
    
    $discussion = fn_get_discussion(0, 'E', true, $params);
    
    fn_add_breadcrumb(__('cp_pr_testimonials'));
    
    Tygh::$app['view']->assign('discussion', $discussion);
    Tygh::$app['view']->assign('object_type', $discussion['object_type']);
    Tygh::$app['view']->assign('object_id', $discussion['object_id']);
    
} elseif ($mode == 'my_reviews') {
    if (!empty($auth['user_id'])) {
    
        fn_add_breadcrumb(__('cp_pr_my_reviews'));
        
        $params = $_REQUEST;
        $params['for_user_id'] = $auth['user_id'];
        
        list($posts, $search) = fn_cp_power_reviews_get_power_reviews($params, Registry::get('settings.Appearance.elements_per_page'));
        
        Tygh::$app['view']->assign('posts', $posts);
        Tygh::$app['view']->assign('search', $search);
    } else {
        return [CONTROLLER_STATUS_NO_PAGE];
    }
}


