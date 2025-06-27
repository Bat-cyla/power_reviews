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
    
        fn_trusted_vars('remaind_data');
        $suffix = '';
        if ($mode == 'update_reviews_page_seo') {
            if (!empty($_REQUEST['id']) && !empty($_REQUEST['review_page_data'])) {
                fn_cp_pr_update_reviews_page_seo($_REQUEST['id'], $_REQUEST['review_page_data'], DESCR_SL);
                $suffix = '.reviews_page_seo';
            }
        }
        if ($mode == 'm_update_attrs') {
            if (!empty($_REQUEST['attr_data'])) {
                fn_cp_power_reviews_m_update_attributes($_REQUEST['attr_data'], DESCR_SL);
            }
            $suffix = '.attr_manage';
        }
        if ($mode == 'm_delete_attrs') {
            if (!empty($_REQUEST['cp_attr_ids'])) {
                fn_cp_power_reviews_del_attrs($_REQUEST['cp_attr_ids']);
            }
            $suffix = '.attr_manage';
        }
        if ($mode == 'attr_update') {
            if (!empty($_REQUEST['redirect_url'])) {
                $_REQUEST['redirect_url'] = 'cp_pow_rev.attr_manage';
            }
            $company_id = Registry::get('runtime.company_id');
            if (!empty($_REQUEST['attr_data'])) {
                $cp_attr_id = fn_cp_power_reviews_update_attribute($_REQUEST['attr_data'], $_REQUEST['cp_attr_id'], $company_id,  DESCR_SL);
                $suffix = ".attr_manage";
            } else {
                $suffix = ".attr_manage";
            }
        }
        if ($mode == 'apply_attr_to_prods') {
            if (!empty($_REQUEST['cp_attr_ids'])) {
                $attrs = implode(',', $_REQUEST['cp_attr_ids']);
                $suffix = '.apply_attr_to_pr?attrs='.$attrs;
            } else {
                $suffix = ".attr_manage";
            }
            if (!empty($_REQUEST['redirect_url'])) {
                unset($_REQUEST['redirect_url']);
            }
        }
        if ($mode == 'apply_attr_to_pr') {
            if (!empty($_REQUEST['apply_attrs']) && !empty($_REQUEST['apply_action'])) {
                if (in_array($_REQUEST['apply_action'], array('add','del'))) {
                
                    fn_cp_power_reviews_apply_attrs_to_all_objects($_REQUEST['apply_attrs'], $_REQUEST['apply_action'], 'P');
                    
                } elseif (!empty($_REQUEST['applyed_to_products']) && in_array($_REQUEST['apply_action'], array('add_sel','del_sel'))) {
                
                    fn_cp_power_reviews_apply_attrs_to_prods($_REQUEST['apply_attrs'], $_REQUEST['applyed_to_products'], $_REQUEST['apply_action']);
                }
            }
            $suffix = ".attr_manage";
        }
//for categories
        if ($mode == 'apply_attr_to_categors') {
            if (!empty($_REQUEST['cp_attr_ids'])) {
                $attrs = implode(',', $_REQUEST['cp_attr_ids']);
                $suffix = '.apply_attr_to_cat?attrs='.$attrs;
            } else {
                $suffix = ".attr_manage";
            }
            if (!empty($_REQUEST['redirect_url'])) {
                unset($_REQUEST['redirect_url']);
            }
        }
        if ($mode == 'apply_attr_to_cat') {
            if (!empty($_REQUEST['apply_attrs']) && !empty($_REQUEST['apply_action'])) {
                if (in_array($_REQUEST['apply_action'], array('add','del'))) {
                
                    fn_cp_power_reviews_apply_attrs_to_all_objects($_REQUEST['apply_attrs'], $_REQUEST['apply_action'], 'C');
                    
                } elseif (!empty($_REQUEST['applyed_to_categories']) && in_array($_REQUEST['apply_action'], array('add_sel','del_sel'))) {
                
                    fn_cp_power_reviews_apply_attrs_to_cats($_REQUEST['apply_attrs'], $_REQUEST['applyed_to_categories'], $_REQUEST['apply_action']);
                }
            }
            $suffix = ".attr_manage";
        }        
//end        
        return array(CONTROLLER_STATUS_OK, "cp_pow_rev$suffix");
}
if ($mode == 'attr_manage') {

    $cpv1 = ___cp('c2V0dGluZ3MuQPBwZWFyYW5jZS5hZG1pbl9lbGVtZW50c19wZPJfcGFnZQ');
    $cpv2 = ___cp('cnVudGltZS5jb21wYW55P2lk');
    
    $params = $_REQUEST;
    $params['attr_type'] = array('G', 'E', 'M');
    $params['cp_go_by_type'] = true;
    list($attributes, $search) = call_user_func(___cp('Zm5fY3BfcG93ZPJfcmV2aWV3c19nZPRfYPR0cnM'), $params, Registry::get($cpv1), DESCR_SL);
    $company_id = Registry::get($cpv2);
    
    $extrem_types = fn_cp_pr_get_view_type_langs();
    Registry::get('view')->assign('cp_pr_exterm_types', $extrem_types);
    
    Registry::get('view')->assign('company_id', $company_id);
    Registry::get('view')->assign('search', $search);
    Registry::get('view')->assign('attributes', $attributes);
    
} elseif ($mode == 'delete_attr') {
    if (!empty($_REQUEST['cp_attr_id'])) {
        fn_cp_power_reviews_del_attrs($_REQUEST['cp_attr_id']);
    }
    return array(CONTROLLER_STATUS_REDIRECT, "cp_pow_rev.attr_manage");
    
} elseif ($mode == 'apply_attr_to_pr') {
    $attributes = array();
    if (!empty($_REQUEST['attrs'])) {
        $params = $_REQUEST;	
        list($attributes, $search) = fn_cp_power_reviews_get_attrs($params, Registry::get('settings.Appearance.admin_elements_per_page'), DESCR_SL);
    }
    Registry::get('view')->assign('attributes', $attributes);
} elseif ($mode == 'apply_attr_to_cat') {
    $attributes = array();
    if (!empty($_REQUEST['attrs'])) {
        $params = $_REQUEST;    
        list($attributes, $search) = fn_cp_power_reviews_get_attrs($params, Registry::get('settings.Appearance.admin_elements_per_page'), DESCR_SL);
    }
    Registry::get('view')->assign('attributes', $attributes);
} elseif ($mode == 'picker') {
    $_params = $_REQUEST;
    list($posts, $search) = fn_cp_power_reviews_get_power_reviews($_params, Registry::get('settings.Appearance.admin_elements_per_page'));
    
    $discussion_object_types = fn_get_discussion_objects();
    
    Registry::get('view')->assign('discussion_object_types', $discussion_object_types);
    
    Tygh::$app['view']->assign('reviews', $posts);
    Tygh::$app['view']->assign('search', $search);
    Registry::get('view')->display('addons/cp_power_reviews/pickers/power_reviews/picker_contents.tpl');
    exit;
}
if ($mode == 'check_purchased') {
    fn_cp_pr_check_purchased_posts();
    exit;
}
if ($mode == 'play_review_video') {
    if (!empty($_REQUEST['video_id']) && !empty($_REQUEST['post_id']) && !empty($_REQUEST['youtube_id'])) {
        Tygh::$app['view']->assign('video_id', $_REQUEST['video_id']);
        Tygh::$app['view']->assign('post_id', $_REQUEST['post_id']);
        Tygh::$app['view']->assign('youtube_id', $_REQUEST['youtube_id']);
        
        Tygh::$app['view']->display('addons/cp_power_reviews/views/cp_pow_rev/play_review_video.tpl');
    }
    exit;
}
if ($mode == 'add_attr') {

    $cpv2 = ___cp('cnVudGltZS5jb21wYW55P2lk');
    $company_id = Registry::get($cpv2);
    
    Tygh::$app['view']->assign('company_id', $company_id);
    
    Tygh::$app['view']->display('addons/cp_power_reviews/views/cp_pow_rev/add_attr.tpl');
    exit;
}
if ($mode == 'reviews_page_seo') {

    $review_page_data = fn_cp_pr_get_reviews_page_data(DESCR_SL);
    
    Tygh::$app['view']->assign('review_page_data', $review_page_data);
}
if ($mode == 'inport_from_def') {
    
    $prod_reviews = Registry::get('addons.cp_power_reviews');
    if (!empty($prod_reviews)) {
        fn_cp_pr_import_reviews_from_default();
    }
    
    exit;
}
if ($mode == 'apply_to_vars') {
    fn_cp_pr_set_vars_from_parent($_REQUEST);
    exit;
}


