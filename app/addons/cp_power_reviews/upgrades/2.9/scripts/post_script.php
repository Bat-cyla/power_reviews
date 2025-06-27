<?php

use Tygh\Registry;
use Tygh\Languages\Languages;

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


if (Registry::get('addons.seo.status') == 'A') {
    $seo_active = true;
} else {
    $seo_active = false;
}
if (fn_allowed_for('ULTIMATE')) {
    $company_ids = db_get_fields("SELECT company_id FROM ?:companies");
} else {
    $company_ids = array(0);
}
foreach($company_ids as $company_id) {
    $data_reviews = array(
        'company_id' => $company_id,
        'name' => 'Reviews'
    );
    $reviews_id = $data_reviews['id'] = db_query("INSERT INTO ?:cp_pr_reaviews_seo ?e", $data_reviews);
    if (!empty($reviews_id)) {
        foreach (Languages::getAll() as $lang_code => $v) {
            $data_reviews['lang_code'] = $lang_code;
            $data_reviews['seo_name'] = 'reviews';
            db_replace_into('cp_pr_reaviews_seo_descr', $data_reviews);
            if (!empty($seo_active)) {
                fn_seo_update_object($data_reviews, $reviews_id, 'r', $lang_code);
            }
        }
    }
}