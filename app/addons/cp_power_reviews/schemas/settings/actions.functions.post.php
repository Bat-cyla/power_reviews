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


function fn_settings_actions_addons_cp_power_reviews(&$new_value, $old_value) {

    fn_cp_check_license_rev_20($new_value, $old_value, ($_REQUEST['id'])?$_REQUEST['id']:$_REQUEST['addon']);

    return true;
}

if (function_exists('fn_cp_check_license_rev_20') != true) {
    function fn_cp_check_license_rev_20($new_value, $old_value, $name) {
    
        if ($_REQUEST['dispatch'] == 'addons.update_status' && !empty($_REQUEST['status'])) {
            if ($_REQUEST['status'] == 'A') {
                db_query("UPDATE ?:product_tabs SET status = ?s WHERE template = ?s", 'D', 'addons/discussion/blocks/product_tabs/discussion.tpl');
            } else {
                db_query("UPDATE ?:product_tabs SET status = ?s WHERE template = ?s", 'A', 'addons/discussion/blocks/product_tabs/discussion.tpl');
            }
        }
        return true;
    }
}
function fn_settings_actions_addons_seo_seo_cp_pr_review_type($new_value, $old_value)
{
    if (!empty($old_value) && $new_value != $old_value) {
        $redirect_only = false;
        $options = array('cp_pr_review', 'cp_pr_review_nohtml');
        if (in_array($new_value, $options) && in_array($old_value, $options)) {
            $redirect_only = true;
        }

        fn_seo_settings_update(CP_PR_OBJECT_SEO_KEY, 'seo_cp_pr_review_type', $new_value, $redirect_only);
    }
}