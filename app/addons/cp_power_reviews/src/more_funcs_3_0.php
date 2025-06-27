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
use Tygh\Settings;
use Tygh\Languages\Languages;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_cp_pr_create_seo_settings($is_upgrade = false)
{
    if (Registry::get('addons.seo.status') == 'A') {
        $seo_active = true;
    } else {
        $seo_active = false;
    }
    if (!empty($seo_active)) {
        if (fn_allowed_for('ULTIMATE')) {
            $all_comp = db_get_fields("SELECT company_id FROM ?:companies");
        } else {
            $all_comp = array();
            $all_comp[0] = 0;
        }
        foreach ($all_comp as $company) {
            foreach (fn_get_translation_languages() as $lc => $_v) {
                if (empty($is_upgrade)) {
                    fn_create_seo_name(0, 's', 'all-reviews', 0, 'cp_pow_rev.all_reviews', $company, $lc);
                }
                fn_create_seo_name(0, 's', 'store-reviews', 0, 'cp_pow_rev.store_reviews', $company, $lc);
            }
        }
    }
    
    $seo_section_id = db_get_field("SELECT section_id FROM ?:settings_sections WHERE name = ?s AND type= ?s", 'seo', 'ADDON');
    $seo_tab_id = db_get_field("SELECT section_id FROM ?:settings_sections WHERE parent_id = ?i AND name = ?s AND type= ?s", $seo_section_id, 'general', 'TAB');
    if (empty($seo_section_id) || empty($seo_tab_id)) {
        return false;
    }
    
    $seo_header_name = 'seo_cp_pr_posts_header';
    $seo_header_setting_id = Settings::instance()->getId($seo_header_name);
    
    if (empty($seo_header_setting_id)) {
        $seo_header_setting_id = Settings::instance()->update(array(
            'name' =>           $seo_header_name,
            'section_id' =>     $seo_section_id,
            'section_tab_id' => $seo_tab_id,
            'type' =>           'H',
            'position' =>       1150,
            'is_global' =>      'N',
            'handler' =>        '',
            'edition_type' =>   'ROOT,ULT:VENDOR,STOREFRONT'
        ));
        $reviews_head_setting_translations = array();
        foreach (Languages::getAll() as $lang_code => $v) {
            $reviews_head_setting_translations[] = array(
                'lang_code' => $lang_code,
                'name' => $seo_header_name,
                'value' => __('cp_pr_seo_tab', array(), $lang_code)
            );
        }
        fn_update_addon_settings_descriptions($seo_header_setting_id, Settings::SETTING_DESCRIPTION, $reviews_head_setting_translations);
        fn_update_addon_settings_originals('seo', $seo_header_setting_id, 'option', 'Cart-Power: Power Reviews');
    }
    
    /*
        Create SEO setting for discussion view 
    */
    
    $review_setting_name = 'seo_cp_pr_review_type';
    $tag_setting_id = Settings::instance()->getId($review_setting_name);
    if (empty($tag_setting_id)) {
        $tag_setting_id = Settings::instance()->update(array(
            'name' =>           $review_setting_name,
            'section_id' =>     $seo_section_id,
            'section_tab_id' => $seo_tab_id,
            'type' =>           'S',
            'position' =>       1151,
            'is_global' =>      'N',
            'handler' =>        '',
            'edition_type' =>   'ROOT,ULT:VENDOR,STOREFRONT'
        ));
        Settings::instance()->updateValueById($tag_setting_id, 'cp_pr_review_nohtml', null, false);
        $tag_setting_translations = array();
        foreach (Languages::getAll() as $lang_code => $v) {
            $tag_setting_translations[] = array(
                'lang_code' => $lang_code,
                'name' => $review_setting_name,
                'value' => __('cp_pr_seo_review_type', array(), $lang_code)
            );
        }
        fn_update_addon_settings_descriptions($tag_setting_id, Settings::SETTING_DESCRIPTION, $tag_setting_translations);
        fn_update_addon_settings_originals('seo', $tag_setting_id, 'option', 'Reviews page SEO URL format');
        $tag_variants = array(
            'cp_pr_review' => '/object/reviews.html',
            'cp_pr_review_nohtml' => '/object/reviews/',
        );
        fn_cp_pr_update_third_party_addon_settings_variants($tag_setting_id, $review_setting_name, $tag_variants, 'seo');
    }
    return true;
}
function fn_cp_pr_update_third_party_addon_settings_variants($setting_id, $setting_name, $variants, $addon)
{
    foreach ($variants as $variant_name => $variant_value) {
        $translations = array();
        foreach (Languages::getAll() as $lang_code => $v) {
            $translations[] = array(
                'lang_code' => $lang_code,
                'name' => $variant_name,
                'value' => $variant_value
            );
        }
        $settings_variants[] = array(
            'id' => $variant_name,
            'name' => $variant_value,
            'original' => $variant_value,
            'translations' => $translations
        );
    }
    foreach ($settings_variants as $variant_k => $variant) {
        $variant_id = Settings::instance()->updateVariant(array(
            'object_id'  => $setting_id,
            'name'       => $variant['id'],
            'position'   => $variant_k * 10,
        ));
        if (!empty($variant_id)) {
            fn_update_addon_settings_descriptions($variant_id, Settings::VARIANT_DESCRIPTION, $variant['translations']);
            fn_update_addon_settings_originals($addon, $setting_name . '::' . $variant['id'], 'variant', $variant['original']);
        }
    }
    return true;
}