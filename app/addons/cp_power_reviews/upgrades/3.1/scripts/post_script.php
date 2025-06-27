<?php

use Tygh\Registry;
use Tygh\Settings;
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
    $reviews_id = $data_reviews['id'] = db_query("INSERT INTO ?:cp_pr_reviews_seo ?e", $data_reviews);
    if (!empty($reviews_id)) {
        foreach (Languages::getAll() as $lang_code => $v) {
            $data_reviews['lang_code'] = $lang_code;
            $data_reviews['seo_name'] = 'reviews';
            db_replace_into('cp_pr_reviews_seo_descr', $data_reviews);
            if (!empty($seo_active)) {
                fn_seo_update_object($data_reviews, $reviews_id, 'r', $lang_code);
            }
        }
        $s_data = db_get_row("SELECT * FROM ?:seo_names WHERE company_id = ?i AND dispatch = ?s", $company_id, 'cp_pow_rev.all_reviews');
        if (!empty($s_data)) {
            $cur_ext_2 = Registry::get('addons.seo.seo_other_type');
            $url_ = '/' . $s_data['name'];
            if ($cur_ext_2 == 'file') {
                $url_ .= '.html';
            }
            $_redirect_data = array(
                'src' => $url_,
                'type' => 'r',
                'object_id' => $reviews_id,
                'company_id' => $company_id,
                'lang_code' => $s_data['lang_code']
            );
            db_query("INSERT INTO ?:seo_redirects ?e ON DUPLICATE KEY UPDATE ?u", $_redirect_data, $_redirect_data);
            db_query("DELETE FROM ?:seo_names WHERE  company_id = ?i AND dispatch = ?s", $company_id, 'cp_pow_rev.all_reviews');
        }
    }
}

if (!empty($seo_active)) {
    $rev_seo_names = db_get_array("SELECT * FROM ?:seo_names WHERE type = ?s", CP_PR_OBJECT_SEO_KEY);
    db_query("UPDATE ?:cp_pr_for_seo SET name = ?s", '');
    if (!empty($rev_seo_names)) {
        $cur_ext = Registry::get('addons.seo.seo_cp_pr_review_type');
        foreach($rev_seo_names as $seo_data) {
            $url = '/' . $seo_data['name'] . '/reviews';
            if ($cur_ext == 'cp_pr_review') {
                $url .= '.html';
            }
            $redirect_data = array(
                'src' => $url,
                'type' => CP_PR_OBJECT_SEO_KEY,
                'object_id' => $seo_data['object_id'],
                'company_id' => $seo_data['company_id'],
                'lang_code' => $seo_data['lang_code']
            );
            db_query("INSERT INTO ?:seo_redirects ?e ON DUPLICATE KEY UPDATE ?u", $redirect_data, $redirect_data);
            db_query("DELETE FROM ?:seo_names WHERE object_id = ?i AND type = ?s", $seo_data['object_id'], CP_PR_OBJECT_SEO_KEY);
        }
    }
}
$seo_section_id = db_get_field("SELECT section_id FROM ?:settings_sections WHERE name = ?s AND type= ?s", 'seo', 'ADDON');
$seo_tab_id = db_get_field("SELECT section_id FROM ?:settings_sections WHERE parent_id = ?i AND name = ?s AND type= ?s", $seo_section_id, 'general', 'TAB');

if (!empty($seo_section_id) && !empty($seo_tab_id)) {
    $review_setting_name = 'seo_cp_pr_reviews_page';
    $tag_setting_id = Settings::instance()->getId($review_setting_name);

    if (empty($tag_setting_id)) {
        $tag_setting_id = Settings::instance()->update(array(
            'name' =>           $review_setting_name,
            'section_id' =>     $seo_section_id,
            'section_tab_id' => $seo_tab_id,
            'type' =>           'S',
            'position' =>       1152,
            'is_global' =>      'N',
            'handler' =>        '',
            'edition_type' =>   'ROOT,ULT:VENDOR,STOREFRONT'
        ));

        Settings::instance()->updateValueById($tag_setting_id, 'cp_pr_reviews_nohtml', null, false);

        $tag_setting_translations = array();

        foreach (Languages::getAll() as $lang_code => $v) {
            $tag_setting_translations[] = array(
                'lang_code' => $lang_code,
                'name' => $review_setting_name,
                'value' => __('cp_pr_seo_reviews_page', array(), $lang_code)
            );
        }

        fn_update_addon_settings_descriptions($tag_setting_id, Settings::SETTING_DESCRIPTION, $tag_setting_translations);
        fn_update_addon_settings_originals('seo', $tag_setting_id, 'option', 'All reviews page SEO URL format');

        $tag_variants = array(
            'cp_pr_reviews' => '/reviews.html',
            'cp_pr_reviews_nohtml' => '/reviews/',
        );

        fn_cp_pr_update_third_party_addon_settings_variants($tag_setting_id, $review_setting_name, $tag_variants, 'seo');
    }
}

$values = db_get_array("SELECT * FROM ?:original_values WHERE msgctxt LIKE ?l", '%SettingsVariants::seo::seo_cp_pr_review_type::cp_pr_review%');
if (!empty($values)) {
    foreach($values as $val) {
        if (strpos($val['msgctxt'], 'seo_cp_pr_review_type::cp_pr_review') !== false) {
            db_query("UPDATE ?:original_values SET msgid = ?s WHERE msgctxt = ?s", '/reviews/object.html', $val['msgctxt']);
        }
        if (strpos($val['msgctxt'], 'seo_cp_pr_review_type::cp_pr_review_nohtml') !== false) {
            db_query("UPDATE ?:original_values SET msgid = ?s WHERE msgctxt = ?s", '/reviews/object/', $val['msgctxt']);
        }
    }
}

$obj_id = db_get_field("SELECT object_id FROM ?:settings_objects WHERE name = ?s", 'seo_cp_pr_review_type');
if (!empty($obj_id)) {
    $var_id = db_get_field("SELECT variant_id FROM ?:settings_variants WHERE object_id = ?i AND name = ?s", $obj_id, 'cp_pr_review');
    if (!empty($var_id)) {
        db_query("UPDATE ?:settings_descriptions SET value = ?s WHERE object_id = ?i", '/reviews/object.html', $var_id);
    }
    $var_id2 = db_get_field("SELECT variant_id FROM ?:settings_variants WHERE object_id = ?i AND name = ?s", $obj_id, 'cp_pr_review_nohtml');
    if (!empty($var_id2)) {
        db_query("UPDATE ?:settings_descriptions SET value = ?s WHERE object_id = ?i", '/reviews/object/', $var_id2);
    }
}
