<?php

use Tygh\Registry;

$addon_id = 'cp_power_reviews';
$addon_scheme = Tygh\Addons\SchemesManager::clearInternalCache($addon_id);
$addon_scheme = Tygh\Addons\SchemesManager::getScheme($addon_id);

require_once \Tygh\Registry::get('config.dir.addons') . 'cp_power_reviews/src/more_funcs_3_0.php';

if (function_exists('fn_get_addon_settings_values')
    && function_exists('fn_get_addon_settings_vendor_values')
) {
    $setting_values = $settings_vendor_values = array();
    $settings_values = fn_get_addon_settings_values($addon_id);
    $settings_vendor_values = fn_get_addon_settings_vendor_values($addon_id);

    fn_update_addon_settings($addon_scheme, true, $settings_values, $settings_vendor_values);
} else {
    fn_update_addon_settings($addon_scheme, true);
}

if (Registry::get('addons.seo.status')) {
    fn_cp_pr_create_seo_settings(true);
    
    db_query("DELETE FROM ?:seo_names WHERE type = ?s AND name LIKE ?l", 'r', '%reviews%');
}

db_query("UPDATE ?:discussion_posts SET cp_pr_can_edit = ?s", 'N');