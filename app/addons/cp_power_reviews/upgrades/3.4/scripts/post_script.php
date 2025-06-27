<?php

use Tygh\Registry;
use Tygh\Settings;

$addon_id = 'cp_power_reviews';
$addon_scheme = Tygh\Addons\SchemesManager::clearInternalCache($addon_id);
$addon_scheme = Tygh\Addons\SchemesManager::getScheme($addon_id);

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
