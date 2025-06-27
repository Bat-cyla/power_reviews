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
use Tygh\Languages\Languages;
use Tygh\BlockManager\ProductTabs;
use Tygh\Mailer;
use Tygh\Storage;
use Tygh\BlockManager\Block;
use Tygh\Navigation\LastView;
use Tygh\Settings;
use Tygh\Storefront\Storefront;
use Tygh\Enum\YesNo;
use Tygh\Addons\ProductVariations\ServiceProvider as ProductVariationsServiceProvider;
use Tygh\Addons\ProductVariations\Product\Group\Group as VariationsGroup;

if (version_compare(PRODUCT_VERSION, '4.11.5', '>')) {
    include_once(Registry::get('config.dir.addons') . 'cp_power_reviews/src/more_classes.php');
}

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_cp_power_reviews_get_route($req, &$result, $area, $is_allowed_url)
{
    if ($area !== 'C' || empty($req['dispatch']) || $req['dispatch'] !== 'products.view' || empty($req['product_id']) || Registry::get('addons.seo.status') !== 'A' ) {
        return;
    }
    if (defined('AJAX_REQUEST') && (!empty($req['cp_filter_stars']) || !empty($req['cp_pr_with_images']) || !empty($req['cp_sort_by']))) {
        if (empty($req['variation_id'])) {
            $var_group_id = db_get_field("SELECT parent_product_id FROM ?:product_variation_group_products WHERE product_id = ?i", $req['product_id']);
            if (!empty($var_group_id)) {
                $lang_code = Registry::get('settings.Appearance.frontend_default_language');
                $pre_url = 'products.view?product_id=' . $req['product_id'];
                if (!empty($req['cp_filter_stars'])) {
                    $pre_url .= '&cp_filter_stars=' . $req['cp_filter_stars'];
                }
                if (!empty($req['cp_pr_with_images'])) {
                    $pre_url .= '&cp_pr_with_images=' . $req['cp_pr_with_images'];
                }
                if (!empty($req['cp_sort_by'])) {
                    $pre_url .= '&cp_sort_by=' . $req['cp_sort_by'];
                }
                $result = [INIT_STATUS_REDIRECT, fn_url($pre_url, 'C', 'rel', $lang_code)];
            }
        }
    }
}

function fn_cp_power_reviews_gather_additional_product_data_post(&$product, $auth, $params)
{
    if (AREA == 'C' && !empty($product['discussion_type']) && $product['discussion_type'] != 'D' && ((defined('CP_PR_VARIATIONS_TYPE') && CP_PR_VARIATIONS_TYPE == 'Y') || defined('CP_PR_SHOW_TOTAL_POSTS'))) {
        $key_pre = [
            'product_id'=> $product['product_id'],
            'store_id'  => Tygh::$app['storefront']->storefront_id,
            'var_common'=> (defined('CP_PR_VARIATIONS_TYPE') && CP_PR_VARIATIONS_TYPE == 'Y') ? true : false
        ];
        $key = 'cp_pr_product_rate_' . md5(implode('|', $key_pre));
        $cache_tables = ['discussion_rating', 'cp_pow_attr_ratings', 'discussion_posts'];
        Registry::registerCache(['cp_power_reviews', $key], $cache_tables, Registry::cacheLevel('static'));
        $all_thread_ids = [!empty($product['discussion_thread_id']) ? $product['discussion_thread_id'] : 0];
        if (Registry::isExist($key)) {
            $prod_totals = Registry::get($key);
            if (!empty($prod_totals['avg']) && $prod_totals['avg'] != 100) {
                $product['average_rating'] = $prod_totals['avg'];
            }
            if (!empty($prod_totals['total'])) {
                $product['cp_pr_total_posts'] = $prod_totals['total'];
            }
        } else {
            $prod_total = 100; // strange fix for not setting cache for products without average_rating
            $total_posts = 0;
            if (!empty($product['variation_group_id']) && defined('CP_PR_VARIATIONS_TYPE') && CP_PR_VARIATIONS_TYPE == 'Y') {
                $all_var_thread_ids = db_get_fields("SELECT ?:discussion.thread_id FROM ?:product_variation_group_products 
                    LEFT JOIN ?:discussion ON ?:discussion.object_id = ?:product_variation_group_products.product_id 
                    WHERE ?:product_variation_group_products.group_id = ?i AND ?:discussion.object_type = ?s", $product['variation_group_id'], 'P'
                );
                if (!empty($all_var_thread_ids)) {
                    list($prod_total, $total_posts) = fn_cp_power_reviews_get_total_post_avg_rate($all_var_thread_ids, true);
                    if (!empty($prod_total)) {
                        $product['average_rating'] = $prod_total;
                    }
                    if (!empty($total_posts)) {
                        $product['cp_pr_total_posts'] = $total_posts;
                    }
                }
            } elseif (!empty($product['variation_group_id']) && (!defined('CP_PR_VARIATIONS_TYPE') || (defined('CP_PR_VARIATIONS_TYPE') && CP_PR_VARIATIONS_TYPE != 'S'))) {
                if (!empty($product['variation_parent_product_id'])) {
                    $use_this_parent = $product['variation_parent_product_id'];
                } else {
                    $use_this_parent = $product['product_id'];
                }
                $all_var_thread_ids = db_get_fields("SELECT ?:discussion.thread_id FROM ?:product_variation_group_products as pvgp
                    LEFT JOIN ?:discussion ON ?:discussion.object_id = pvgp.product_id 
                    WHERE (pvgp.parent_product_id = ?i OR pvgp.product_id = ?i) AND ?:discussion.object_type = ?s", $use_this_parent, $use_this_parent, 'P'
                );
                if (!empty($all_var_thread_ids)) {
                    list($prod_total, $total_posts) = fn_cp_power_reviews_get_total_post_avg_rate($all_var_thread_ids, true);
                    if (!empty($prod_total)) {
                        $product['average_rating'] = $prod_total;
                    }
                    if (!empty($total_posts)) {
                        $product['cp_pr_total_posts'] = $total_posts;
                    }
                }
            } elseif (!empty($all_thread_ids) && defined('CP_PR_SHOW_TOTAL_POSTS')) {
                list($avg_rate, $total_posts) = fn_cp_power_reviews_get_total_post_avg_rate($all_thread_ids, true);
                if (!empty($avg_rate)) {
                    $product['average_rating'] = $avg_rate;
                }
                if (!empty($total_posts)) {
                    $product['cp_pr_total_posts'] = $total_posts;
                }
            }
            Registry::set($key, ['avg' => $prod_total, 'total' => $total_posts]);
        }
    }
}

function fn_cp_power_reviews_get_discussion_pre($object_id, $object_type, &$get_posts, &$params)
{
    if (AREA == 'C' && !empty($object_id) && $object_type == 'P' && (!defined('CP_PR_VARIATIONS_TYPE') || (defined('CP_PR_VARIATIONS_TYPE') && (in_array(CP_PR_VARIATIONS_TYPE, ['Y','S','NS']) && !empty($params['cp_pr_single_vars']) || !in_array(CP_PR_VARIATIONS_TYPE, ['S']))))) {
        if (!empty($params['from_prod_tab']) && !empty($params['req'])) {
            $params = array_merge($params, $params['req']);
            unset($params['req']);
        } elseif (empty($params['from_prod_tab'])) {
            $is_det = Registry::get('cp_pr_is_det_page');
            if (!empty($is_det)) {
                $parent_product_ids = fn_cp_pr_get_all_group_vars_for_pid($object_id);
                if (!empty($parent_product_ids)) {
                    $get_posts = false;
                    $params['from_prod_tab'] = 1;
                }
            }
        } elseif (!empty($params['from_prod_tab'])) {
            $get_posts = false;
        }
    }
    if (!empty($object_id) && $object_type == 'P' && ((defined('CP_PR_VARIATIONS_TYPE') && in_array(CP_PR_VARIATIONS_TYPE, ['Y','S','NS']) && empty($params['cp_pr_single_vars']) || AREA == 'A'))) {
        $params['cp_pr_single_vars'] = true;
        $params['skip_check_child_product'] = true;
    }
}

function fn_cp_power_reviews_storefront_repository_delete_post($storefront, $operation_result)
{
    if (!empty($storefront) && !empty($operation_result)) {
        $store_id = $storefront->storefront_id;
        if (!empty($store_id)) {
            $posts = db_get_fields("SELECT post_id FROM ?:cp_pr_reviews_storefronts WHERE storefront_id = ?i", $store_id);
            if (!empty($posts)) {
                foreach($posts as $post_id) {
                    fn_discussion_delete_post($post_id);
                }
            }
        }
    }
}

function fn_cp_power_reviews_create_seo_name_pre($object_id, $object_type, $object_name, $index, $dispatch, $company_id, $lang_code, &$params)
{
    if (!empty($object_id) && !empty($object_type) && $object_type == 'p') {
        $thread_ids = db_get_fields("SELECT thread_id FROM ?:discussion WHERE object_id = ?i AND object_type = ?s", $object_id, 'P');
        if (!empty($thread_ids)) {
            $params['cp_pr_prev_thread_seo'] = [];
            foreach($thread_ids as $thr_id) {
                $prev_thread_seo = db_get_array("SELECT * FROM ?:seo_names WHERE object_id = ?i AND type = ?s", $thr_id, CP_PR_OBJECT_SEO_KEY);
                if (!empty($prev_thread_seo)) {
                    $params['cp_pr_prev_thread_seo'][$thr_id] = $prev_thread_seo;
                    db_query("DELETE FROM ?:seo_names WHERE object_id = ?i AND type = ?s", $thr_id, CP_PR_OBJECT_SEO_KEY);
                }
            }
        }
    }
}

function fn_cp_power_reviews_create_seo_name_post($_object_name, $object_id, $object_type, $object_name, $index, $dispatch, $company_id, $lang_code, &$params)
{
    if (!empty($object_id) && !empty($object_type) && $object_type == 'p' && !empty($params['cp_pr_prev_thread_seo'])) {
        foreach($params['cp_pr_prev_thread_seo'] as $thread_id => $th_seo_data) {
            foreach($th_seo_data as $thread_seo) {
                db_replace_into('seo_names', $thread_seo);
            }
        }
        unset($params['cp_pr_prev_thread_seo']);
    }
}

function fn_cp_power_reviews_ab__advanced_sitemap_write_links_to_file($object_type, $value, $settings_var, $ab_settings, &$text)
{
    if (!empty($object_type) && in_array($object_type, array('cp_prod_review'))) {
        $reviews_settings = Registry::get('addons.cp_power_reviews');
        
        if ($object_type == 'cp_prod_review') {
            if (!empty($reviews_settings['product_reviews_change']) && $reviews_settings['product_reviews_change'] != 'do_not_use') {
                $text .= "<changefreq>{$reviews_settings['product_reviews_change']}</changefreq>\n";
            }
            if (!empty($reviews_settings['product_reviews_priority']) && $reviews_settings['product_reviews_priority'] != 'do_not_use') {
                $text .= "<priority>{$reviews_settings['product_reviews_priority']}</priority>\n";
            }
        }
        
        if (\Tygh\Enum\YesNo::toBool($ab_settings['add_lastmod'])) {
            $text .= '<lastmod>' . date('c', time()) . "</lastmod>\n";
        }
    }
}

function fn_cp_power_reviews_ab__as_get_settings_object_from_object_type($object_type, $xml_builder, &$settings_var)
{
    if ($object_type == 'cp_prod_review') {
        $settings_var = 'product_reviews';
    }
}

function fn_cp_power_reviews_ab__as_other_objects(&$objects, $storefront, $settings)
{
    $reviews_settings = Registry::get('addons.cp_power_reviews');
    if (!empty($reviews_settings['include_product_reviews']) && $reviews_settings['include_product_reviews'] == 'Y') {
        $params = $_REQUEST;
        $params['custom_extend'] = array('categories');
        $params['sort_by'] = 'null';
        $params['get_conditions'] = true;
        $params['area'] = 'C';
        if ($storefront->getCompanyIds()) {
            $params['only_for_storefront_id'] = array_merge([0], $storefront->getCompanyIds());
        }

        $original_auth = Tygh::$app['session']['auth'];
        Tygh::$app['session']['auth'] = fn_fill_auth([], [], false, 'C');

        list($fields, $join, $condition) = fn_get_products($params, 0);
        
        $join .= db_quote(" INNER JOIN ?:discussion_posts ON ?:discussion_posts.thread_id = cp_disc.thread_id");
        $condition .= db_quote(' AND ?:discussion_posts.status = ?s AND ?:discussion_posts.cp_pr_user_delete = ?s', 'A', 'N');
        
        $thread_ids = db_get_fields("SELECT DISTINCT(cp_disc.thread_id) FROM ?:products as products
            LEFT JOIN ?:discussion as cp_disc ON cp_disc.object_id = products.product_id ?p
            WHERE cp_disc.object_type = ?s AND cp_disc.type != ?s ?p", $join, 'P', 'D', $condition);
            
        if (!empty($thread_ids)) {
            $objects['cp_prod_review'] = $thread_ids;
        }
        Tygh::$app['session']['auth'] = $original_auth;
    }
    if (!empty($reviews_settings['include_test_reviews']) && $reviews_settings['include_test_reviews'] == 'Y') {
        $objects['cp_test_review'] = ['Y'];
    }
    if (!empty($reviews_settings['include_all_reviews']) && $reviews_settings['include_all_reviews'] == 'Y') {
        if (fn_allowed_for('MULTIVENDOR')) {
            $cur_comp_id = 0;
        } else {
            $cur_comp_id = Registry::get('runtime.company_id');
        }
        $all_reviews_id = db_get_field("SELECT id FROM ?:cp_pr_reviews_seo WHERE company_id = ?i", $cur_comp_id);
        $objects['cp_all_review'] = array($all_reviews_id);
    }
    
}

function fn_cp_power_reviews_update_company($company_data, $company_id, $lang_code, $action)
{
    if (!empty($action) && $action == 'add' && !empty($company_id) && fn_allowed_for('ULTIMATE')) {
        fn_cp_pr_create_reviews_page_seo_data($company_id);
    }
}

function fn_cp_power_reviews_sitemap_link_object(&$link, $type, $id)
{
    if ($type == 'cp_prod_review') {
        $link = 'cp_pow_rev.view?thread_id=' . $id;
    }
    if ($type == 'cp_test_review') {
        $link = 'cp_pow_rev.store_reviews';
    }
    if ($type == 'cp_all_review') {
        $link = 'cp_pow_rev.all_reviews?id=' . $id;
    }
}

//hook for cart version >= 4.11
function fn_cp_power_reviews_google_sitemap_generate_sitemap_for_storefront_after_items($storefront, $settings, &$file, $last_modified_time, &$link_counter, &$file_counter)
{
    $reviews_settings = Registry::get('addons.cp_power_reviews');
    $languages = fn_google_sitemap_get_sitemap_languages($storefront);

    
    $sitemap_header = <<<HEAD
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">


HEAD;

    $sitemap_footer = <<<FOOT

</urlset>
FOOT;

    if ($reviews_settings['include_product_reviews'] == 'Y') {
        list($file, $link_counter, $file_counter) = fn_google_sitemap_write_cp_product_reviews_to_sitemap(
            $storefront,
            $last_modified_time,
            $reviews_settings['product_reviews_change'],
            $reviews_settings['product_reviews_priority'],
            $file,
            $link_counter,
            $file_counter,
            $sitemap_header,
            $sitemap_footer,
            $languages
        );
    }

    if ($reviews_settings['include_test_reviews'] == 'Y') {
        list($file, $link_counter, $file_counter) = fn_google_sitemap_write_test_reviews_to_sitemap(
            $storefront,
            $last_modified_time,
            $reviews_settings['test_reviews_change'],
            $reviews_settings['test_reviews_priority'],
            $file,
            $link_counter,
            $file_counter,
            $sitemap_header,
            $sitemap_footer,
            $languages
        );
    }
    
    if ($reviews_settings['include_all_reviews'] == 'Y') {
        list($file, $link_counter, $file_counter) = fn_google_sitemap_write_all_reviews_to_sitemap(
            $storefront,
            $last_modified_time,
            $reviews_settings['all_reviews_change'],
            $reviews_settings['all_reviews_priority'],
            $file,
            $link_counter,
            $file_counter,
            $sitemap_header,
            $sitemap_footer,
            $languages
        );
    }
}

//hook for cart version < 4.11
function fn_fn_cp_power_reviews_sitemap_item($settings, &$file, $last_modified_time, &$link_counter, &$file_counter)
{
    $languages = db_get_hash_single_array("SELECT lang_code, name FROM ?:languages WHERE status = 'A'", array('lang_code', 'name'));
    $reviews_settings = Registry::get('addons.cp_power_reviews');

    if ($reviews_settings['include_product_reviews'] == 'Y') {
        $params = $_REQUEST;
        $params['custom_extend'] = array('categories');
        $params['sort_by'] = 'null';
        $params['get_conditions'] = true;
        $params['area'] = 'C';

        $original_auth = Tygh::$app['session']['auth'];
        Tygh::$app['session']['auth'] = fn_fill_auth([], [], false, 'C');

        list($fields, $join, $condition) = fn_get_products($params, 0);
        
        $thread_ids = db_get_fields("SELECT DISTINCT(cp_disc.thread_id) FROM ?:products as products $join 
            LEFT JOIN ?:discussion as cp_disc ON cp_disc.object_id = products.product_id
            WHERE cp_disc.object_type = ?s AND cp_disc.type != ?s ?p", 'P', 'D', $condition);

        Tygh::$app['session']['auth'] = $original_auth;
        
        if (!empty($thread_ids)) {
        
            fn_set_progress('step_scale', count($thread_ids));
            
            foreach ($thread_ids as $thread_id) {
                $links = fn_google_sitemap_generate_link('cp_prod_review', $thread_id, $languages);
                $item = fn_google_sitemap_print_item_info($links, $last_modified_time, $reviews_settings['product_reviews_change'], $reviews_settings['product_reviews_priority']);

                fn_google_sitemap_check_counter($file, $link_counter, $file_counter, $links, '', '', 'cp_prod_reviews');

                fwrite($file, $item);
            }
        }
    }

    if ($reviews_settings['include_test_reviews'] == "Y") {
        
        fn_set_progress('step_scale', 1);
        
        $links = fn_google_sitemap_generate_link('cp_test_review', 0, $languages);
        $item = fn_google_sitemap_print_item_info($links, $last_modified_time, $reviews_settings['test_reviews_change'], $reviews_settings['test_reviews_priority']);

        fn_google_sitemap_check_counter($file, $link_counter, $file_counter, $links, '', '', 'cp_test_reviews');

        fwrite($file, $item);
    }
    if ($reviews_settings['include_all_reviews'] == "Y") {

        fn_set_progress('step_scale', 1);
        
        if (fn_allowed_for('MULTIVENDOR')) {
            $cur_comp_id = 0;
        } else {
            $cur_comp_id = Registry::get('runtime.company_id');
        }
        $all_reviews_id = db_get_field("SELECT id FROM ?:cp_pr_reviews_seo WHERE company_id = ?i", $cur_comp_id);
        
        $links = fn_google_sitemap_generate_link('cp_all_review', $all_reviews_id, $languages);
        
        $item = fn_google_sitemap_print_item_info($links, $last_modified_time, $reviews_settings['all_reviews_change'], $reviews_settings['all_reviews_priority']);

        fn_google_sitemap_check_counter($file, $link_counter, $file_counter, $links, '', '', 'cp_all_reviews');

        fwrite($file, $item);
    }
}

function fn_cp_power_reviews_url_pre(&$url, $area, $protocol, $lang_code)
{

    if ($area === 'C' && strpos($url, 'cp_pow_rev.view') !== false && strpos($url, 'thread_id=') !== false) {
        //$url = str_replace('cp_pow_rev.view', 'cp_pow_rev_fake.view', $url);
        $cp_url = str_replace('&amp;', '&', $url);
        $parsed_url = parse_url($cp_url);
        $parsed_query = [];
        if (!empty($parsed_url['query'])) {
            parse_str($parsed_url['query'], $parsed_query);
        }
        if (!empty($parsed_query['thread_id']) && Registry::get('addons.seo.status') == 'A') {
            $thread_data = fn_discussion_get_object(array('thread_id' => $parsed_query['thread_id']));
            if (!empty($thread_data) && !empty($thread_data['object_type']) && $thread_data['object_type'] == 'P') {
                $check_exist = db_get_field("SELECT name FROM ?:cp_pr_for_seo WHERE thread_id = ?i AND lang_code = ?s", $parsed_query['thread_id'], $lang_code);
                if (empty($check_exist)) {
                    $thread_tables = fn_cp_pr_thread_object_tables();
                    if (!empty($thread_tables) && !empty($thread_tables[$thread_data['object_type']])) {
                        $product_id = db_get_field("SELECT object_id FROM ?:discussion WHERE thread_id = ?i AND object_type = ?s", $parsed_query['thread_id'], 'P');
                        if (!empty($product_id)) {
                            $object_name = db_get_field("SELECT name FROM ?:seo_names WHERE object_id = ?i AND type = ?s", $product_id, 'p');
                        } else {
                            $table_info = $thread_tables[$thread_data['object_type']];
                            $object_name = db_get_field("SELECT " . $table_info['column'] . " FROM ?:" . $table_info['table'] . " WHERE " . $table_info['id'] . " = ?i AND lang_code = ?s", $thread_data['object_id'], $lang_code);
                        }
                        if (!empty($object_name)) {
                            $put_data = array(
                                'thread_id' => $thread_data['thread_id'],
                                'name' => $object_name,
                                'lang_code' => $lang_code,
                            );
                            db_replace_into('cp_pr_for_seo', $put_data);
                        }
                    }
                }
            }
        }
    }
}

function fn_cp_power_reviews_url_post(&$url, $area, $original_url, $prefix, $company_id_in_url, $lang_code)
{
    if ($area == 'C' && strpos($url, 'dispatch=cp_pow_rev_fake.view') !== false && strpos($url, 'thread_id=') !== false && Registry::get('addons.seo.status') == 'A') {
        $url = str_replace('cp_pow_rev_fake.view', 'cp_pow_rev.view', $url);
        $d = SEO_DELIMITER;
        $settings_company_id = empty($company_id_in_url) ? 0 : $company_id_in_url;
        $seo_settings = fn_get_seo_settings($settings_company_id);
        
        $parsed_query = [];
        $parsed_url = parse_url($url);
        
        if (!empty($parsed_url['query'])) {
            parse_str($parsed_url['query'], $parsed_query);
        }

        if (!fn_allowed_for('ULTIMATE:FREE')) {
            if (!empty($parsed_query['lc'])) {
                //if localization parameter is exist we will get language code for this localization.
                $loc_languages = db_get_hash_single_array(
                    "SELECT a.lang_code, a.name FROM ?:languages as a 
                    LEFT JOIN ?:localization_elements as b ON b.element_type = 'L' AND b.element = a.lang_code 
                    WHERE b.localization_id = ?i ORDER BY position", array('lang_code', 'name'), $parsed_query['lc']
                );
                $new_lang_code = (!empty($loc_languages)) ? key($loc_languages) : '';
                $lang_code = (!empty($new_lang_code)) ? $new_lang_code : $lang_code;
            }
        }
        
        $index_script = Registry::get('config.customer_index');
        $path = str_replace($index_script, '', $parsed_url['path'], $count);
        if ($count > 0) {
            //$seo_vars = fn_get_seo_vars();
            $fragment = !empty($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
            $link_parts = array(
                'scheme' => !empty($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '',
                'host' => !empty($parsed_url['host']) ? $parsed_url['host'] : '',
                'path' => $path,
                'lang_code' => ($seo_settings['seo_language'] == 'Y') ? $lang_code . '/' : '',
                'parent_items_names' => '',
                'name' => '',
                'page' => '',
                'extension' => '',
            );
            if (!empty($parsed_query)) {
                if (!empty($parsed_query['sl'])) {
                    $lang_code = $parsed_query['sl'];

                    if ($seo_settings['single_url'] != 'Y') {
                        $unset_lang_code = $parsed_query['sl'];
                        unset($parsed_query['sl']);
                    }

                    if ($seo_settings['seo_language'] == 'Y') {
                        $link_parts['lang_code'] = $lang_code . '/';
                        $unset_lang_code = isset($parsed_query['sl']) ? $parsed_query['sl'] : $unset_lang_code;
                        unset($parsed_query['sl']);
                    }
                }

                $lang_code = fn_get_corrected_seo_lang_code($lang_code, $seo_settings);

                if (!empty($parsed_query['dispatch']) && is_string($parsed_query['dispatch'])) {

                    $seo_vars = fn_get_seo_vars();
                    $rewritten = false;

                    foreach ($seo_vars as $type => $seo_var) {
                        if (empty($seo_var['dispatch']) || ($seo_var['dispatch'] == $parsed_query['dispatch'] && !empty($parsed_query[$seo_var['item']]))) {

                            if (!empty($seo_var['dispatch'])) {
                                $link_parts['name'] = fn_seo_get_name($type, $parsed_query[$seo_var['item']], '', $company_id_in_url, $lang_code);
                            } else {
                                $link_parts['name'] = fn_seo_get_name($type, 0, $parsed_query['dispatch'], $company_id_in_url, $lang_code);
                            }
                            if (empty($link_parts['name'])) {
                                continue;
                            }

                            if (fn_check_seo_schema_option($seo_var, 'tree_options', $seo_settings)) {
                                $parent_item_names = fn_seo_get_parent_items_path($seo_var, $type, $parsed_query[$seo_var['item']], $company_id_in_url, $lang_code);
                                $link_parts['parent_items_names'] = !empty($parent_item_names) ? join('/', $parent_item_names) . '/' : '';
                            }

                            if (fn_check_seo_schema_option($seo_var, 'html_options', $seo_settings)) {
                                $link_parts['extension'] = SEO_FILENAME_EXTENSION;
                            } else {
                                $link_parts['name'] .= '/';
                            }

                            if (!empty($seo_var['pager'])) {

                                $page = isset($parsed_query['page']) ? intval($parsed_query['page']) : 0;

                                if (!empty($page) && $page != 1) {
                                    if (fn_check_seo_schema_option($seo_var, 'html_options', $seo_settings)) {
                                        $link_parts['name'] .= $d . 'page' . $d . $page;
                                    } else {
                                        $link_parts['name'] .= 'page' . $d . $page . '/';
                                    }
                                }
                                unset($parsed_query['page']);
                            }

                            fn_seo_parsed_query_unset($parsed_query, $seo_var['item']);

                            $rewritten = true;
                            break;
                        }
                    }
                    if (!$rewritten) {
                       
                        if (empty($link_parts['name'])) {
                            // for non-rewritten links
                            $link_parts['path'] .= $index_script;
                            $link_parts['lang_code'] = '';
                            if (!empty($unset_lang_code)) {
                                $parsed_query['sl'] = $unset_lang_code;
                            }
                        }
                    } else {
                        unset($parsed_query['company_id']); // we do not need this parameter if url is rewritten
                    }

                } elseif ($seo_settings['seo_language'] != 'Y' && !empty($unset_lang_code)) {
                    $parsed_query['sl'] = $unset_lang_code;
                }
            }
            if (substr($link_parts['name'], -1, 1) == '/') {
                $link_parts['name'] .= CP_PR_SEO_SUFFIX;
            } else {
                $link_parts['name'] .= '/' . CP_PR_SEO_SUFFIX;
            }
            $url = join('', $link_parts);
            if (!empty($parsed_query)) {
                $url .= '?' . http_build_query($parsed_query) . $fragment;
            }
            return $url;
        }
    }
}

function fn_cp_power_reviews_discussion_delete_post_post($post_id)
{
    if (!empty($post_id)) {
        fn_cp_pp_delete_post_additional_data(array($post_id));
        db_query("DELETE FROM ?:cp_pr_reviews_storefronts WHERE post_id = ?i", $post_id);
    }
}

function fn_cp_power_reviews_delete_discussion_pre($object_id, $object_type)
{
    $thread_id = db_get_field("SELECT thread_id FROM ?:discussion WHERE object_id IN (?n) AND object_type = ?s", $object_id, $object_type);
    if (!empty($thread_id)) {
        $post_ids = db_get_fields("SELECT post_id FROM ?:discussion_posts WHERE thread_id = ?i", $thread_id);
        if (!empty($post_ids)) {
            fn_cp_pp_delete_post_additional_data($post_ids);
        }
        db_query("DELETE FROM ?:cp_pow_recomends WHERE thread_id = ?i", $thread_id);
        db_query("DELETE FROM ?:cp_pr_for_seo WHERE thread_id = ?i", $thread_id);
        if (Registry::get('addons.seo.status') == 'A') {
            fn_delete_seo_name($thread_id, CP_PR_OBJECT_SEO_KEY);
        }
    }
}

function fn_cp_power_reviews_change_order_status_post($order_id, $status_to, $status_from, $force_notification, $place_order, $order_info, $edp_data)
{
    if (!empty($order_id) && !empty($status_to) && !empty($order_info['user_id'])) {
        $verif_statuses_set = Registry::get('addons.cp_power_reviews.orders_for_purchase');
        if (!empty($verif_statuses_set)) {
            $verif_statuses = [];
            foreach($verif_statuses_set as $st => $val) {
                if (!empty($val) && $val == 'Y') {
                    $verif_statuses[] = $st;
                }
            }
            if (!empty($verif_statuses)) {
                if (in_array($status_to, $verif_statuses)) {
                    $post_ids = db_get_fields(
                        "SELECT ?:discussion_posts.post_id FROM ?:discussion_posts 
                        LEFT JOIN ?:discussion ON ?:discussion.thread_id = ?:discussion_posts.thread_id
                        LEFT JOIN ?:order_details ON ?:order_details.product_id = ?:discussion.object_id
                        WHERE ?:order_details.order_id = ?i AND ?:discussion.object_type = ?s AND ?:discussion_posts.user_id = ?i", $order_id, 'P', $order_info['user_id']
                    );
                    if (!empty($post_ids)) {
                        db_query("UPDATE ?:discussion_posts SET cp_pr_verified_purchase = ?s WHERE post_id IN (?n)", 'Y', $post_ids);
                    }
                } else {
                    $exists_products = db_get_fields("SELECT DISTINCT(?:order_details.product_id) FROM ?:order_details
                        LEFT JOIN ?:orders ON ?:orders.order_id = ?:order_details.order_id
                        WHERE ?:orders.user_id = ?i AND ?:orders.status IN (?a)", $order_info['user_id'], $verif_statuses);
                    if (!empty($exists_products)) {
                        db_query("
                            UPDATE ?:discussion_posts 
                            LEFT JOIN ?:discussion ON ?:discussion.thread_id = ?:discussion_posts.thread_id 
                            SET ?:discussion_posts.cp_pr_verified_purchase = ?s 
                            WHERE ?:discussion_posts.user_id = ?i AND ?:discussion.object_id NOT IN (?n) AND ?:discussion.object_type = ?s", 'N', $order_info['user_id'], $exists_products, 'P'
                        );
                    } else {
                        db_query("UPDATE ?:discussion_posts SET cp_pr_verified_purchase = ?s WHERE user_id = ?i", 'N', $order_info['user_id']);
                    }
                }
            }
        }
    }
}

function fn_cp_power_reviews_delete_company ($company_id, $result) 
{
    if (!empty($result) && !empty($company_id)) {
        $del_attr = db_get_fields("SELECT cp_attr_id FROM ?:cp_power_ext_reviews WHERE company_id = ?i", $company_id);
        if (!empty($del_attr)) {
            fn_cp_power_reviews_del_attrs($del_attr);
        }
    }
}

function fn_cp_power_reviews_delete_category_after ($category_id) 
{
    if (!empty($category_id)) {
        db_query("DELETE FROM ?:cp_power_rev_cats WHERE category_id = ?i", $category_id);
    }
}

function fn_cp_power_reviews_add_recommendation ($thread_id, $ip_address, $reccomend, $object_type) {
    if (!empty($thread_id) && !empty($ip_address) && !empty($reccomend) && !empty($object_type)) {
        $check_exist = db_get_array("SELECT * FROM ?:cp_pow_recomends WHERE thread_id = ?i AND ip_address = ?s", $thread_id, $ip_address);
        if (empty($check_exist)) {
        
        $ffff = db_get_array("SELECT * FROM ?:cp_pow_recomends");
            $put_data = array(
                'thread_id' => $thread_id,
                'ip_address' => $ip_address,
                'type' => $reccomend,
                'timestamp' => time()
            );
            db_query("INSERT INTO ?:cp_pow_recomends ?e ON DUPLICATE KEY UPDATE ?u", $put_data, $put_data);
        } else {
            if ($object_type == 'P') {
                $recom_object = __('cp_pr_product_text');
            } elseif ($object_type == 'M') {
                $recom_object = __('cp_pr_vendor_text');
            } elseif ($object_type == 'A') {
                $recom_object = __('cp_pr_page_text');
            } elseif ($object_type == 'B') {
                $recom_object = __('cp_pr_blog_text');
            }
            fn_set_notification('W', __('warning'), __('cp_you_already_recommend_this') . ' ' . $recom_object);
            return false;
        }
    }
    return true;
}
function fn_cp_power_reviews_update_sred_rate_from_posts ($posts) {
    if (!empty($posts) && is_array($posts)) {
        $db_posts = db_get_hash_array("SELECT ?:discussion_rating.post_id, ?:discussion_rating.thread_id, ?:discussion_rating.rating_value, ?:discussion.object_type FROM ?:discussion_rating 
            LEFT JOIN ?:discussion ON ?:discussion.thread_id = ?:discussion_rating.thread_id
            WHERE ?:discussion_rating.post_id IN (?n)", 'post_id', array_keys($posts));
        if (!empty($db_posts)) {
            foreach ($posts as $p_id => $data) {
                if (!empty($db_posts[$p_id]) && !empty($db_posts[$p_id]['object_type']) && in_array($db_posts[$p_id]['object_type'], array('C','A','B')) && !empty($db_posts[$p_id]['rating_value'])) {
                    db_query("UPDATE ?:discussion_rating SET cp_sred_rate = ?d WHERE post_id = ?i", $db_posts[$p_id]['rating_value'], $p_id);
                }
            }
        }
    }
    return true;
}
//BLOCK companies.products
function fn_cp_power_reviews_bl_get_vendor_attr_info () {
    $discussion = [];
    if (fn_allowed_for('MULTIVENDOR')) {
        $company_id = isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : null;
        if (!empty($company_id)) {
            $discussion = fn_get_discussion($company_id, 'M', true, $_REQUEST);
            $discussion['object_id'] = $company_id;
            
        }
    }
    return $discussion;
}
function fn_cp_power_reviews_bl_get_vendor_info() {

    $company_data = [];
    if (fn_allowed_for('MULTIVENDOR')) {
        $product_id = isset($_REQUEST['product_id']) ? $_REQUEST['product_id'] : null;
        if (!empty($product_id)) {
            $company_id = db_get_field("SELECT company_id FROM ?:products WHERE product_id = ?i", $product_id);
        } else {
            $company_id = isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : null;
        }
        if (!empty($company_id)) {
            $params = $_REQUEST;
            $params['status'] = 'A';
            $params['get_description'] = 'Y';
            $params['company_id'] = array($company_id);
            $auth = Tygh::$app['session']['auth'];
            list($companies, $search) = fn_get_companies($params, $auth, 0);
            if (!empty($companies))  {
                $company_data = reset($companies);
            } else {
                $company_data = fn_get_company_data($company_id);
            }
            if (!empty($company_data)) {
                $company_data['logos'] = fn_get_logos($company_id);
                $company_data['discussion'] = fn_get_discussion($company_id, 'M', true);
            }
        }
        if (!empty($product_id)) {
            $company_data['cp_for_product_page'] = true;
        }
    }
    return $company_data;
}
function fn_cp_power_reviews_update_category_post($category_data, $category_id, $lang_code) {
    if (!empty($category_id)) {
        if (fn_allowed_for('ULTIMATE') && empty($category_data['company_id'])) {
            $comp_id = Registry::get('runtime.company_id');
            if (!empty($comp_id)) {
                $condition = db_quote(" AND ?:cp_power_ext_reviews.company_id = ?i", $comp_id);
                $check_exist = db_get_fields("SELECT ?:cp_power_rev_cats.cp_attr_id FROM ?:cp_power_rev_cats 
                    LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_power_rev_cats.cp_attr_id
                    WHERE ?:cp_power_rev_cats.category_id = ?i AND ?:cp_power_ext_reviews.company_id = ?i", $category_id, $comp_id);
            } else {
                $check_exist = db_get_fields("SELECT cp_attr_id FROM ?:cp_power_rev_cats WHERE category_id = ?i", $category_id);
                $condition = '';
            }
        } elseif (fn_allowed_for('ULTIMATE')) {
            $check_exist = db_get_fields("SELECT cp_attr_id FROM ?:cp_power_rev_cats WHERE category_id = ?i", $category_id);
            $condition = db_quote(" AND ?:cp_power_ext_reviews.company_id = ?i", $category_data['company_id']);
            $comp_id = $category_data['company_id'];
        } else {
            $check_exist = db_get_fields("SELECT cp_attr_id FROM ?:cp_power_rev_cats WHERE category_id = ?i", $category_id);
            $condition = '';
            $comp_id = 0;
        }
        if (!empty($check_exist)) {
            if (!empty($category_data['cp_rew_attr'])) {
                $glob_attr = $prod_attr = [];
                foreach($category_data['cp_rew_attr'] as $key => $attr_data) {
                    if (!empty($attr_data['cp_attr_name'])) {
                        $trim_attr_name = trim($attr_data['cp_attr_name']);
                    } else {
                        $trim_attr_name = '';
                    }
                    if (!empty($attr_data['cp_attr_id']) && !in_array($attr_data['cp_attr_id'], $check_exist) && $attr_data['object_type'] == 'G') {
                        $n_data = array(
                            'cp_attr_id' => $attr_data['cp_attr_id'],
                            'attr_pos' => $attr_data['attr_pos'],
                            'category_id' => $category_id
                        );
                        $glob_attr[] = $attr_data['cp_attr_id'];
                        db_query("REPLACE INTO ?:cp_power_rev_cats ?e", $n_data);
                    } elseif (!empty($attr_data['cp_attr_id']) && in_array($attr_data['cp_attr_id'], $check_exist) ) {
                        if ($attr_data['object_type'] == 'P') {
                            $data = array(
                                'cp_attr_id' => $attr_data['cp_attr_id'],
                                'object_id' => $category_id,
                                'attr_pos' => $attr_data['attr_pos'],
                                'object_type' => $attr_data['object_type'],
                                'status' => $attr_data['status'],
                                'cp_attr_name' => trim($attr_data['cp_attr_name']),
                                'view_type' => isset($attr_data['view_type']) ? $attr_data['view_type'] : 'D',
                                'required' => isset($attr_data['required']) ? $attr_data['required'] : 'Y',
                            );
                            if (!empty($data['view_type']) && $data['view_type'] == 'E' && !empty($attr_data['view_type_txt'])) {
                                $data['view_type_txt'] = $attr_data['view_type_txt'];
                            }
                            $prod_attr[] = $attr_data['cp_attr_id'];
                            fn_cp_power_reviews_update_attribute ($data, $attr_data['cp_attr_id'], $comp_id, $lang_code, $category_id, 'C');
                        } else {
                            $n_data = array(
                                'cp_attr_id' => $attr_data['cp_attr_id'],
                                'attr_pos' => $attr_data['attr_pos'],
                                'category_id' => $category_id
                            );
                            $glob_attr[] = $attr_data['cp_attr_id'];
                            db_query("UPDATE ?:cp_power_rev_cats SET ?u WHERE cp_attr_id = ?i AND category_id = ?i", $n_data, $attr_data['cp_attr_id'], $category_id);
                        }
                    } elseif (empty($attr_data['cp_attr_id']) && !empty($attr_data['new']) && !empty($trim_attr_name)) {
                        $data = array(
                            'cp_attr_id' => 0,
                            'object_id' => $category_id,
                            'attr_pos' => $attr_data['attr_pos'],
                            'object_type' => $attr_data['object_type'],
                            'status' => $attr_data['status'],
                            'cp_attr_name' => trim($attr_data['cp_attr_name']),
                            'view_type' => isset($attr_data['view_type']) ? $attr_data['view_type'] : 'D',
                            'required' => isset($attr_data['required']) ? $attr_data['required'] : 'Y',
                        );
                        if (!empty($data['view_type']) && $data['view_type'] == 'E' && !empty($attr_data['view_type_txt'])) {
                            $data['view_type_txt'] = $attr_data['view_type_txt'];
                        }
                        $data['company_id'] = $comp_id;
                        $new_attr_id = fn_cp_power_reviews_update_attribute ($data, 0, $comp_id, $lang_code, $category_id, 'C');
                        if (!empty($new_attr_id)) {
                            $prod_attr[] = $new_attr_id;
                        }
                    }
                }
                if (!empty($glob_attr)) {
                    $check_glob_prod_exist = db_get_fields("SELECT ?:cp_power_rev_cats.cp_attr_id FROM ?:cp_power_rev_cats 
                        LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_power_rev_cats.cp_attr_id 
                        WHERE ?:cp_power_rev_cats.category_id = ?i AND ?:cp_power_ext_reviews.object_type = ?s AND ?:cp_power_rev_cats.cp_attr_id NOT IN (?n) ?p", $category_id, 'G', $glob_attr, $condition);
                    if (!empty($check_glob_prod_exist)) {
                        db_query("DELETE FROM ?:cp_power_rev_cats WHERE category_id = ?i AND cp_attr_id IN (?n)", $category_id, $check_glob_prod_exist);
                    }
                } else {
                    $check_glob_prod_exist = db_get_fields("SELECT ?:cp_power_rev_cats.cp_attr_id FROM ?:cp_power_rev_cats 
                        LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_power_rev_cats.cp_attr_id 
                        WHERE ?:cp_power_rev_cats.category_id = ?i AND ?:cp_power_ext_reviews.object_type = ?s ?p", $category_id, 'G', $condition);
                    if (!empty($check_glob_prod_exist)) {
                        db_query("DELETE FROM ?:cp_power_rev_cats WHERE category_id = ?i AND cp_attr_id IN (?n)", $category_id, $check_glob_prod_exist);
                    }
                }
                if (!empty($prod_attr)) {
                    $check_products_exist = db_get_fields("SELECT ?:cp_power_rev_cats.cp_attr_id FROM ?:cp_power_rev_cats 
                        LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_power_rev_cats.cp_attr_id 
                        WHERE ?:cp_power_rev_cats.category_id = ?i AND ?:cp_power_ext_reviews.object_type = ?s AND ?:cp_power_rev_cats.cp_attr_id NOT IN (?n) ?p", $category_id, 'P', $prod_attr, $condition);
                    if (!empty($check_products_exist)) {
                        fn_cp_power_reviews_del_attrs($check_products_exist);
                    }
                } else {
                    $check_products_exist = db_get_fields("SELECT ?:cp_power_rev_cats.cp_attr_id FROM ?:cp_power_rev_cats 
                        LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_power_rev_cats.cp_attr_id 
                        WHERE ?:cp_power_rev_cats.category_id = ?i AND ?:cp_power_ext_reviews.object_type = ?s ?p", $category_id, 'P', $condition);
                    if (!empty($check_products_exist)) {
                        fn_cp_power_reviews_del_attrs($check_products_exist);
                    }
                }
            } elseif (isset($category_data['cp_rew_attr']) && empty($category_data['cp_rew_attr'])) {
                if (fn_allowed_for('ULTIMATE')) {
                    if (!empty($comp_id)) {
                        $product_only_atrs = db_get_fields("SELECT cp_attr_id FROM ?:cp_power_ext_reviews WHERE cp_attr_id IN (?n) AND object_type = ?s AND company_id = ?i", $check_exist, 'P', $comp_id);
                    } else {
                        $product_only_atrs = db_get_fields("SELECT cp_attr_id FROM ?:cp_power_ext_reviews WHERE cp_attr_id IN (?n) AND object_type = ?s", $check_exist, 'P');
                    }
                } else {
                    $product_only_atrs = db_get_fields("SELECT cp_attr_id FROM ?:cp_power_ext_reviews WHERE cp_attr_id IN (?n) AND object_type = ?s", $check_exist, 'P');
                }
                if (!empty($product_only_atrs)) {
                    fn_cp_power_reviews_del_attrs($product_only_atrs);
                }
            }
        } else {
            if (!empty($category_data['cp_rew_attr'])) {
                foreach($category_data['cp_rew_attr'] as $key => $attr_data) {
                    if (!empty($attr_data['cp_attr_name'])) {
                        $trim_atr_name = trim($attr_data['cp_attr_name']);
                    } else {
                        $trim_atr_name = '';
                    }
                    if (!empty($attr_data['cp_attr_id'])) {
                        $n_data = array(
                            'cp_attr_id' => $attr_data['cp_attr_id'],
                            'attr_pos' => $attr_data['attr_pos'],
                            'category_id' => $category_id
                        );
                        db_query("INSERT INTO ?:cp_power_rev_cats ?e", $n_data);
                    } elseif (!empty($trim_atr_name)) {
                        $data = array(
                            'cp_attr_id' => 0,
                            'object_id' => $category_id,
                            'attr_pos' => $attr_data['attr_pos'],
                            'object_type' => $attr_data['object_type'],
                            'status' => $attr_data['status'],
                            'cp_attr_name' => trim($attr_data['cp_attr_name']),
                            'view_type' => isset($attr_data['view_type']) ? $attr_data['view_type'] : 'D',
                            'required' => isset($attr_data['required']) ? $attr_data['required'] : 'Y',
                        );
                        if (!empty($data['view_type']) && $data['view_type'] == 'E' && !empty($attr_data['view_type_txt'])) {
                            $data['view_type_txt'] = $attr_data['view_type_txt'];
                        }
                        if (fn_allowed_for('ULTIMATE')) {
                            $data['company_id'] = !empty($comp_id) ? $comp_id : Registry::get('runtime.company_id');
                        }
                        fn_cp_power_reviews_update_attribute ($data, 0, $comp_id, $lang_code, $category_id, 'C');
                    }
                }
            }
        }
    }
}
function fn_cp_power_reviews_get_category_data_post ($category_id, $field_list, $get_main_pair, $skip_company_condition, $lang_code, &$category_data) {
    
    if (!empty($category_data['category_id']) && AREA == 'A') {
        if (fn_allowed_for('ULTIMATE') && empty($category_data['company_id'])) {
            $comp_id = Registry::get('runtime.company_id');
            if (!empty($comp_id)) {
                $condition = db_quote(" AND ?:cp_power_ext_reviews.company_id = ?i", $comp_id);
            } else {
                $condition = '';
            }
        } elseif (fn_allowed_for('ULTIMATE')) {
            $condition = db_quote(" AND ?:cp_power_ext_reviews.company_id = ?i", $category_data['company_id']);
        } else {
            $comp_id = Registry::get('runtime.company_id');
            if (!empty($comp_id)) {
                $comps = array($comp_id, 0);
                $condition = db_quote(" AND ?:cp_power_ext_reviews.company_id IN (?n)", $comps);
            } else {
                $condition = '';
            }
        }
        $category_data['cp_cat_rew_attrs'] = db_get_array("SELECT ?:cp_power_rev_cats.*, ?:cp_pow_attr_descr.cp_attr_name, ?:cp_power_ext_reviews.* FROM ?:cp_power_rev_cats 
            LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_power_rev_cats.cp_attr_id 
            LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_power_rev_cats.cp_attr_id 
            WHERE ?:cp_power_rev_cats.category_id =?i AND ?:cp_pow_attr_descr.lang_code = ?s ?p ORDER BY ?:cp_power_rev_cats.attr_pos", $category_data['category_id'], $lang_code, $condition);
        $all_prod_glob_attr = db_get_fields("SELECT ?:cp_power_ext_reviews.cp_attr_id FROM ?:cp_power_ext_reviews 
            LEFT JOIN ?:cp_power_rev_cats ON ?:cp_power_rev_cats.cp_attr_id = ?:cp_power_ext_reviews.cp_attr_id 
            WHERE ?:cp_power_rev_cats.category_id =?i AND ?:cp_power_ext_reviews.object_type = ?s ?p", $category_data['category_id'], 'G', $condition);
        if (!empty($all_prod_glob_attr)) {
            $category_data['cp_other_glob_attr'] = db_get_array("SELECT ?:cp_pow_attr_descr.cp_attr_name, ?:cp_power_ext_reviews.cp_attr_id, ?:cp_power_ext_reviews.company_id FROM ?:cp_power_ext_reviews 
            LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_power_ext_reviews.cp_attr_id 
            WHERE ?:cp_power_ext_reviews.object_type = ?s AND ?:cp_pow_attr_descr.lang_code = ?s AND ?:cp_power_ext_reviews.cp_attr_id NOT IN (?n) ?p",'G', $lang_code, $all_prod_glob_attr, $condition);
        } else {
            $category_data['cp_other_glob_attr'] = db_get_array("SELECT ?:cp_pow_attr_descr.cp_attr_name, ?:cp_power_ext_reviews.cp_attr_id, ?:cp_power_ext_reviews.company_id  FROM ?:cp_power_ext_reviews 
                LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_power_ext_reviews.cp_attr_id 
                WHERE ?:cp_power_ext_reviews.object_type = ?s AND ?:cp_pow_attr_descr.lang_code = ?s ?p", 'G', $lang_code, $condition);
        }
        
        if (!empty($category_data['cp_cat_rew_attrs'])) {
            $category_data['cp_cat_rew_attrs'] = fn_cp_pr_get_view_type_txts($category_data['cp_cat_rew_attrs'], $lang_code);
        }
        if (!empty($category_data['cp_other_glob_attr'])) {
            $category_data['cp_other_glob_attr'] = fn_cp_pr_get_view_type_txts($category_data['cp_other_glob_attr'], $lang_code);
        }
    }
}
function fn_cp_power_reviews_render_block_register_cache($block, &$cache_key, $block_schema, $cache_this_block, $display_this_block) {
    if (!empty($_REQUEST['cp_sort_by'])) {
        $cache_key .= TIME;
    }
}
function fn_cp_power_reviews_tools_change_status ($params, $result) {
    if (!empty($params) && !empty($result) && $params['table'] == 'discussion_posts' && !empty($params['id'])) {
        db_query("UPDATE ?:cp_pow_attr_ratings SET post_status = ?s WHERE post_id = ?i", $params['status'], $params['id']);
    }
}
//Block function
function fn_cp_power_reviews_get_review($post_id = 0) {
$review['message'] = db_get_field('SELECT message FROM ?:discussion_messages WHERE post_id = ?i', $post_id);
$review['rating_value'] = db_get_field('SELECT rating_value FROM ?:discussion_rating WHERE post_id = ?i', $post_id);
$review['name'] = db_get_field('SELECT name FROM ?:discussion_posts WHERE post_id = ?i', $post_id);   
$review['timestamp'] = db_get_field('SELECT timestamp FROM ?:discussion_posts WHERE post_id = ?i', $post_id);      
$review['object_type'] = db_get_field('SELECT object_type FROM ?:discussion LEFT JOIN ?:discussion_messages ON ?:discussion.thread_id = ?:discussion_messages.thread_id WHERE ?:discussion_messages.post_id = ?i', $post_id);    

return $review;
}

function fn_cp_power_reviews_get_review_message($post_id = 0) 
{
return db_get_field('SELECT message FROM ?:discussion_messages WHERE post_id = ?i', $post_id);

}

function fn_cp_pr_get_subcategories_from_parents($parent_ids)
{
    $categories = '';
    if (!empty($parent_ids)) {
        $parent_cond = '';
        foreach($parent_ids as $pcat_id) {
            if (empty($parent_cond)) {
                $parent_cond .= db_quote(" AND (id_path LIKE ?l OR id_path LIKE ?l OR id_path LIKE ?l OR id_path LIKE ?l", $pcat_id, $pcat_id . '/%', '%/' . $pcat_id. '/%', '%/'. $pcat_id);
            } else {
                $parent_cond .= db_quote(" OR id_path LIKE ?l OR id_path LIKE ?l OR id_path LIKE ?l OR id_path LIKE ?l", $pcat_id, $pcat_id . '/%', '%/' . $pcat_id. '/%', '%/'. $pcat_id);
            }
        }
        $parent_cond .= ')';
        $all_categories = db_get_fields("SELECT category_id FROM ?:categories WHERE 1 ?p", $parent_cond);
        $all_categories = array_unique($all_categories);
        $c_params = [
            'simple'            => false,
            'group_by_level'    => false,
            'item_ids'          => implode(',', $all_categories),
            'plain'             => false,
            'status'            => 'A'
        ];
        list($categories) = fn_get_categories($c_params, $lang_code);
        
        $cat_ids = [];
        if (!empty($categories)) {
            foreach($categories as $cat_data) {
                $cat_ids[] = $cat_data['category_id'];
            }
        }
        $categories = implode(',',$cat_ids);
    }
    return $categories;
}

function fn_cp_power_reviews_get_power_reviews($params, $items_per_page = 0) 
{
    $initial_params = $params;
    $pr_settings = Registry::get('addons.cp_power_reviews');
    if (AREA == 'C') {
        if (!isset($_params)) {
            $_params = [];
        }
        $_params['status'] = 'A';
        $_params['not_this_types'] = 'D';
        if (!empty($params['block_data']) && !empty($params['block_data']['content']) && $params['block_data']['content']['items']['filling'] == 'newest_reviews') {
            $_params['sort_by'] = 'timestamp';
            $_params['sort_order'] = 'desc';
            if (!empty($params['b_cid'])) {
                $_params['object_ids'] = $params['b_cid'];
                $_params['object_type'] = 'C';
            } elseif (!empty($params['productid'])) {
                $_params['object_ids'] = $params['productid'];
                $_params['object_type'] = 'P';
            } elseif (!empty($params['pageid'])) {
                $_params['object_ids'] = $params['pageid'];
                $_params['object_type'] = 'A';
            }
        } elseif (!empty($params['block_data']) && !empty($params['block_data']['content']) && $params['block_data']['content']['items']['filling'] == 'manually') {
            $_params['post_ids'] = explode(",", $params['item_ids']);
        } elseif (!empty($params['block_data']) && !empty($params['block_data']['content']) && $params['block_data']['content']['items']['filling'] == 'from_current_object') {
            if (!empty($params['pid'])) {
                $_params['object_ids'] = $params['pid'];
                $_params['object_type'] = 'P';
            }
            if (!empty($params['pgid'])) {
                $_params['object_ids'] = $params['pgid'];
                $_params['object_type'] = 'A';
            }
            if (!empty($params['cid'])) {
                $_params['object_ids'] = $params['cid'];
                $_params['object_type'] = 'C';
            }
            if (!empty($params['cmpid']) && fn_allowed_for('MULTIVENDOR')) {
                $_params['object_ids'] = $params['cmpid'];
                $_params['object_type'] = 'M';
            }
            if (!empty($params['postid'])) {
                $_params['object_ids'] = $params['postid'];
                $_params['object_type'] = 'B';
            }
        } elseif (!empty($params['block_data']) && !empty($params['block_data']['content']) && $params['block_data']['content']['items']['filling'] == 'cp_pr_testimonials') {
            $_params['object_type'] = 'E';
        } elseif (!empty($params['block_data']) && !empty($params['block_data']['content']) && in_array($params['block_data']['content']['items']['filling'], array('cp_pr_reviews','prod_reviews_from_current_object'))) {
            $_params['object_type'] = 'P';
            
            if (!empty($params['pr_vend_id'])) {
                $_params['pr_vend_ids'] = $params['pr_vend_id'];
            } elseif (!empty($params['cmpid']) && !empty($params['cp_from_cur_oject_type']) && $params['cp_from_cur_oject_type'] == 'V') {
                $_params['pr_vend_ids'] = $params['cmpid'];
            }
            if (!empty($params['cp_take_from_sub_cat']) && $params['cp_take_from_sub_cat'] == 'Y') {
                if (!empty($params['b_cid'])) {
                    $parent_ids = explode(',', $params['b_cid']);
                } elseif (!empty($params['cid']) && !empty($params['cp_from_cur_oject_type']) && $params['cp_from_cur_oject_type'] == 'C') {
                    $parent_ids = explode(',', $params['cid']);
                }
                if (!empty($parent_ids)) {
                    $all_cats = fn_cp_pr_get_subcategories_from_parents($parent_ids);
                    if (!empty($all_cats)) {
                        $_params['prod_from_cid'] = $all_cats;
                    }
                }
            } else {
                if (!empty($params['b_cid'])) {
                    $_params['prod_from_cid'] = $params['b_cid'];
                } elseif (!empty($params['cid']) && !empty($params['cp_from_cur_oject_type']) && $params['cp_from_cur_oject_type'] == 'C') {
                    $_params['prod_from_cid'] = $params['cid'];
                }
                if (!empty($params['productid']) && !empty($params['b_cid'])) {
                    $_params['this_productid'] = $params['productid'];
                } elseif (!empty($params['productid'])) {
                    $_params['object_ids'] = $params['productid'];
                }
            }
        } elseif (!empty($params['block_data']) && !empty($params['block_data']['content']) && $params['block_data']['content']['items']['filling'] == 'cp_pr_vendor_products' && !empty($params['cmpid'])) {
        
            $_params['pr_vend_ids'] = $params['cmpid'];
            $_params['object_type'] = 'P';
            
        } elseif (!empty($params['block_data']) && !empty($params['block_data']['content']) && $params['block_data']['content']['items']['filling'] == 'cp_pr_pages') {
        
            $_params['object_type'] = 'A';
            
            if (!empty($params['cp_take_from_cur_page']) && $params['cp_take_from_cur_page'] == 'Y' && !empty($params['pgid'])) {
                $_params['object_ids'] = $params['pgid'];
            } elseif (!empty($params['pageid'])) {
                $_params['object_ids'] = $params['pageid'];
            }
        } elseif (!empty($params['block_data']) && !empty($params['block_data']['content']) && $params['block_data']['content']['items']['filling'] == 'cp_pr_power_blog') {
        
            $_params['object_type'] = 'B';
            
            if (!empty($params['cp_take_from_cur_page']) && $params['cp_take_from_cur_page'] == 'Y' && !empty($params['postid'])) {
                $_params['object_ids'] = $params['postid'];
            } elseif (!empty($params['blogid'])) {
                $_params['object_ids'] = $params['blogid'];
            }
        } elseif (!empty($params['block_data']) && !empty($params['block_data']['content']) && $params['block_data']['content']['items']['filling'] == 'cp_pr_categories') {
        
            $_params['object_type'] = 'C';
            
            if (!empty($params['cid']) && !empty($params['cp_take_from_cur_cat']) && $params['cp_take_from_cur_cat'] == 'Y' && $params['cp_take_from_sub_cat'] != 'Y') {
                $_params['object_ids'] = $params['cid'];
            } elseif (!empty($params['cp_take_from_sub_cat']) && $params['cp_take_from_sub_cat'] == 'Y') {
                if (!empty($params['b_cid'])) {
                    $parent_ids = explode(',', $params['b_cid']);
                } elseif (!empty($params['cid']) && !empty($params['cp_take_from_cur_cat']) && $params['cp_take_from_cur_cat'] == 'Y') {
                    $parent_ids = explode(',', $params['cid']);
                }
                if (!empty($parent_ids)) {
                    $all_cats = fn_cp_pr_get_subcategories_from_parents($parent_ids);
                    if (!empty($all_cats)) {
                        $_params['object_ids'] = $all_cats;
                    }
                }
            } elseif (!empty($params['b_cid'])) {
                $_params['object_ids'] = $params['b_cid'];
            }
            
        } elseif (!empty($params['block_data']) && !empty($params['block_data']['content']) && $params['block_data']['content']['items']['filling'] == 'cp_pr_vendors') {
            $_params['object_type'] = 'M';
            if (!empty($params['cp_take_from_cur_vendor']) && $params['cp_take_from_cur_vendor'] == 'Y' && (!empty($params['cmpid']) || !empty($params['cpid']))) {
                if (!empty($params['cmpid'])) {
                    $_params['object_ids'] = $params['cmpid'];
                } elseif ($params['cpid']) {
                    $_params['object_ids'] = db_get_field("SELECT company_id FROM ?:products WHERE product_id = ?i", $params['cpid']);
                }
            } elseif (!empty($params['vend_id'])) {
                $_params['object_ids'] = $params['vend_id'];
            }
        }
        if (!empty($params['block_data']) && !empty($params['block_data']['content']) && !empty($params['block_data']['content']['items']['filling'])) {
            $_params['minimal_rating'] = $params['min_rating_limit'];
            $_params['maximum_rating'] = $params['max_rating_limit'];
            $_params['page'] = 1;
            $_params['items_per_page'] = $params['block_data']['properties']['limit'];
            $items_per_page = '';
            $_params['cp_last_days'] = !empty($params['cp_last_days']) ? $params['cp_last_days'] : 0;
            $params = $_params; 
        }
//for all reviews
        if (!empty($params['cp_is_all_page'])) {
            $params['status'] = 'A';
            $params['not_this_types'] = 'D';
        }
//
    }

    // Init filter
    $params = LastView::instance()->update('discussion', $params);

    // Set default values to input params
    $default_params = [
        'page'          => 1,
        'items_per_page'=> $items_per_page
    ];

    $params = array_merge($default_params, $params);
    
    // Define fields that should be retrieved
    $fields = [
        '?:discussion_posts.*',
        '?:discussion_messages.message',
        '?:discussion_messages.cp_pr_title',
        '?:discussion_messages.cp_pr_advantages',
        '?:discussion_messages.cp_pr_disadvantages',
        '?:discussion_rating.rating_value',
        '?:discussion.*'
    ];

    // Define sort fields
    $sortings = [
        'object'        => "?:discussion.object_type",
        'name'          => "?:discussion_posts.name",
        'ip_address'    => "?:discussion_posts.ip_address",
        'timestamp'     => "?:discussion_posts.timestamp",
        'status'        => "?:discussion_posts.status",
        'date'          => "?:orders.timestamp",
        'total'         => "?:orders.total",
        'cp_sred_rate'  => "?:discussion_rating.cp_sred_rate",
        'cp_pos_post'   => "?:discussion_posts.cp_pos_post",
    ];
    
    $condition = $join = $order_by = '';
    
    if (!empty($initial_params['cp_fill_type']) && $initial_params['block_data']['content']['items']['filling'] != 'newest_reviews') {
        if ($initial_params['cp_fill_type'] == 'TPR') {
            $params['sort_by'] = 'cp_sred_rate';
            $params['sort_order'] = 'desc';
        } elseif ($initial_params['cp_fill_type'] == 'LWR') {
            $params['sort_by'] = 'cp_sred_rate';
            $params['sort_order'] = 'asc';
        } elseif ($initial_params['cp_fill_type'] == 'MSH') {
            $params['sort_by'] = 'cp_pos_post';
            $params['sort_order'] = 'desc';
        } elseif ($initial_params['cp_fill_type'] == 'OLD') {
            $params['sort_by'] = 'timestamp';
            $params['sort_order'] = 'asc';
        } elseif ($initial_params['cp_fill_type'] == 'NEW') {
            $params['sort_by'] = 'timestamp';
            $params['sort_order'] = 'desc';
        }
        if (!empty($params['cp_sort_attributes_only']) && $params['cp_sort_attributes_only'] == 'Y') {
            $join .= " LEFT JOIN ?:cp_pow_attr_ratings ON ?:cp_pow_attr_ratings.post_id = ?:discussion_posts.post_id ";
            $condition .= db_quote(" AND (SELECT COUNT(?:cp_pow_attr_ratings.rating) FROM ?:cp_pow_attr_ratings WHERE ?:cp_pow_attr_ratings.post_id = ?:discussion_posts.post_id) > ?i", 1);
        }
    }
    $sorting = db_sort($params, $sortings, !empty($params['sort_by']) ? $params['sort_by'] : 'timestamp', !empty($params['sort_order']) ? $params['sort_order'] : 'desc');
    
    if (!empty($initial_params['cp_fill_type']) && $initial_params['cp_fill_type'] == 'RND') {
        $sorting = 'ORDER BY RAND()';
    }
//for all reviews
    if (!empty($params['cp_is_all_page']) || !empty($params['from_prod_tab'])) {
        if (!empty($params['cp_sort_by'])) {
            if ($params['cp_sort_by'] == 'MH') {
                $sorting = 'ORDER BY ?:discussion_posts.cp_pos_post desc,?:discussion_posts.timestamp desc';
            } elseif ($params['cp_sort_by'] == 'HR') {
                $sorting = 'ORDER BY ?:discussion_rating.cp_sred_rate desc,?:discussion_posts.timestamp desc';
            } elseif ($params['cp_sort_by'] == 'LR') {
                $sorting = 'ORDER BY ?:discussion_rating.cp_sred_rate asc,?:discussion_posts.timestamp desc';
            } elseif ($params['cp_sort_by'] == 'NW') {
                $sorting = 'ORDER BY ?:discussion_posts.timestamp desc';
            } elseif ($params['cp_sort_by'] == 'OD') {
                $sorting = 'ORDER BY ?:discussion_posts.timestamp asc';
            }
        }
    }
//
    if (!empty($params['from_prod_tab']) && !empty($params['cp_filter_stars']) && $params['cp_filter_stars'] > 0) {
        $condition .= db_quote(" AND ?:discussion_rating.rating_value = ?i", $params['cp_filter_stars']);
    }
    if (isset($params['name']) && fn_string_not_empty($params['name'])) {
        $condition .= db_quote(" AND ?:discussion_posts.name LIKE ?l", "%".trim($params['name'])."%");
    }

    if (isset($params['message']) && fn_string_not_empty($params['message'])) {
        $condition .= db_quote(" AND ?:discussion_messages.message LIKE ?l", "%".trim($params['message'])."%");
    }

    if (!empty($params['type'])) {
        $condition .= db_quote(" AND ?:discussion.type = ?s", $params['type']);
    }
    if (!empty($params['thread_id'])) {
        $condition .= db_quote(" AND ?:discussion.thread_id = ?i", $params['thread_id']);
    }
    if (!empty($params['cp_post_kind']) && !empty($params['r_limit'])) {
        if ($params['cp_post_kind'] == 'pos') {
            $condition .= db_quote(" AND ?:discussion_rating.cp_sred_rate >= ?i", $params['r_limit']);
        } else {
            $condition .= db_quote(" AND ?:discussion_rating.cp_sred_rate < ?i", $params['r_limit']);
        }
    }
    if (!empty($params['status'])) {
        $condition .= db_quote(" AND ?:discussion_posts.status = ?s", $params['status']);
    }
    if (!empty($params['object_ids'])) {
        $condition .= db_quote(" AND ?:discussion.object_id IN (?n)", explode(',',$params['object_ids']));
    }
    if (!empty($params['post_id'])) {
        $condition .= db_quote(" AND ?:discussion_posts.post_id = ?i", $params['post_id']);
    }
    
    if (!empty($params['post_ids'])) {
        $condition .= db_quote(" AND ?:discussion_posts.post_id IN (?n)", $params['post_ids']);
    } 
    
    if (isset($params['ip_address']) && fn_string_not_empty($params['ip_address'])) {
        $condition .= db_quote(" AND ?:discussion_posts.ip_address = ?s", fn_ip_to_db(trim($params['ip_address'])));
    }

    if (!empty($params['rating_value'])) {
        $condition .= db_quote(" AND ?:discussion_rating.rating_value = ?i", $params['rating_value']);
    }
    
    if (!empty($params['minimal_rating'])) {
        $condition .= db_quote(" AND ?:discussion_rating.cp_sred_rate >= ?i", $params['minimal_rating']);
    }    
    if (!empty($params['maximum_rating'])) {
        $condition .= db_quote(" AND ?:discussion_rating.cp_sred_rate <= ?i", $params['maximum_rating']);
    }  
    if (!empty($params['object_type'])) {
        $condition .= db_quote(" AND ?:discussion.object_type = ?s", $params['object_type']);
    }
    if (!empty($params['for_user_id'])) {
        $condition .= db_quote(" AND ?:discussion_posts.user_id = ?i AND ?:discussion_posts.cp_pr_user_delete = ?s", $params['for_user_id'], 'N');
    } elseif (AREA == 'C') {
        $condition .= db_quote(" AND ?:discussion_posts.cp_pr_user_delete = ?s", 'N');
    }
    if ((!empty($initial_params['cp_is_all_page']) || (defined('CP_PR_VARIATIONS_TYPE') && in_array(CP_PR_VARIATIONS_TYPE, ['Y','NS']))) && !empty($params['cp_pr_with_images']) && $params['cp_pr_with_images'] == 'Y') {
        $all_images_posts = db_get_fields("SELECT DISTINCT(?:cp_review_images.post_id) FROM ?:cp_review_images 
            LEFT JOIN ?:discussion_posts ON ?:discussion_posts.post_id = ?:cp_review_images.post_id");
        if (!empty($all_images_posts)) {
            $condition .= db_quote(" AND ?:discussion_posts.post_id IN (?n)", $all_images_posts);
        }
    }
//for all reviews
    if (!empty($params['cp_object_types'])) {
        $pb_addon = Registry::get('addons.cp_power_blog');
        if (in_array('B', $params['cp_object_types']) && (empty($pb_addon) || (!empty($pb_addon) && $pb_addon['status'] != 'A'))) {
            $params['cp_object_types'] = array_diff($params['cp_object_types'], array('B'));
        }
        $condition .= db_quote(" AND ?:discussion.object_type IN (?a)", $params['cp_object_types']);
    }
    if (!empty($params['not_this_types'])) {
        $condition .= db_quote(" AND ?:discussion.type != ?s", $params['not_this_types']);
    }
//
    if (fn_allowed_for('MULTIVENDOR')) {
        $store_id = Tygh::$app['storefront']->storefront_id;
        $all_comp_ids = db_get_fields("SELECT company_id FROM ?:storefronts_companies WHERE storefront_id = ?i", $store_id);
        if (!empty($all_comp_ids)) {
            $all_comp_ids[] = 0;
            $condition .= db_quote(" AND ?:discussion.company_id IN (?n)", $all_comp_ids);
        }
    } else {
        $condition .= fn_get_discussion_company_condition('?:discussion.company_id');
    }

    if (!empty($params['period']) && $params['period'] != 'A') {
        list($params['time_from'], $params['time_to']) = fn_create_periods($params);
        $condition .= db_quote(" AND (?:discussion_posts.timestamp >= ?i AND ?:discussion_posts.timestamp <= ?i)", $params['time_from'], $params['time_to']);
    }

    $join .= " INNER JOIN ?:discussion ON ?:discussion.thread_id = ?:discussion_posts.thread_id";
    $join .= " INNER JOIN ?:discussion_messages ON ?:discussion_messages.post_id = ?:discussion_posts.post_id";
    $join .= " INNER JOIN ?:discussion_rating ON ?:discussion_rating.post_id = ?:discussion_posts.post_id";
    
    if (!empty($params['object_type']) && $params['object_type'] == 'P') {
        $join .= db_quote(" LEFT JOIN ?:products ON ?:products.product_id = ?:discussion.object_id");
        $condition .= db_quote(" AND ?:products.status = ?s", 'A');
        $join .= db_quote(" LEFT JOIN ?:products_categories ON ?:products_categories.product_id = ?:discussion.object_id");
        $join .= db_quote(" LEFT JOIN ?:categories ON ?:categories.category_id = ?:products_categories.category_id");
        $condition .= db_quote(" AND ?:categories.status = ?s", 'A');
    } elseif (!empty($params['object_type']) && $params['object_type'] == 'A') {
        $join .= db_quote(" LEFT JOIN ?:pages ON ?:pages.page_id = ?:discussion.object_id");
        $condition .= db_quote(" AND ?:pages.status = ?s", 'A');
    } elseif (!empty($params['object_type']) && $params['object_type'] == 'C') {
        $join .= db_quote(" LEFT JOIN ?:categories ON ?:categories.category_id = ?:discussion.object_id");
        $condition .= db_quote(" AND ?:categories.status = ?s", 'A');
    } elseif (!empty($params['object_type']) && $params['object_type'] == 'M') {
        $join .= db_quote(" LEFT JOIN ?:companies ON ?:companies.company_id = ?:discussion.object_id");
        $condition .= db_quote(" AND ?:companies.status = ?s", 'A');
    }
    if (Registry::get('addons.cp_power_blog.status') == 'A' && !empty($params['object_type']) && $params['object_type'] == 'B') {
        $join .= db_quote(" LEFT JOIN ?:cp_blog_posts ON ?:cp_blog_posts.post_id = ?:discussion.object_id");
        $condition .= db_quote(" AND ?:cp_blog_posts.status = ?s", 'A');
    }
    if (!empty($params['prod_from_cid'])) {
        if (!empty($params['this_productid'])) {
            $condition .= db_quote(" AND ?:products_categories.category_id IN (?n) AND ?:discussion.object_id IN (?n)", explode(',',$params['prod_from_cid']), explode(',',$params['this_productid']));
        } else {
            $condition .= db_quote(" AND ?:products_categories.category_id IN (?n)", explode(',',$params['prod_from_cid']));
        }
    }
//for all reviews
    if (!empty($params['pr_vend_ids'])) {
        if (Registry::get('addons.master_products.status') == 'A') {
            $join .= db_quote(" LEFT JOIN ?:products as cp_mast_prod ON cp_mast_prod.master_product_id = ?:products.product_id");
            $condition .= db_quote(" AND (?:products.company_id IN (?n) OR cp_mast_prod.company_id IN (?n))", explode(',',$params['pr_vend_ids']), explode(',',$params['pr_vend_ids']));
        } else {
            $condition .= db_quote(" AND ?:products.company_id IN (?n)", explode(',',$params['pr_vend_ids']));
        }
    }
//
    if (!empty($params['cp_last_days']) && $params['cp_last_days'] > 0) {
        $from_this = time() - $params['cp_last_days']*24*60*60;
        $condition .= db_quote(" AND ?:discussion_posts.timestamp >= ?i", $from_this);
    }
    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:discussion_posts $join WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }
    $posts = db_get_hash_array("SELECT " . implode(',', $fields) . " FROM ?:discussion_posts $join WHERE 1 $condition $sorting $limit", 'post_id');
    
    if (AREA == 'C') {
        $prod_vars_active = false;
        if (Registry::get('addons.product_variations.status') == 'A') {
            $prod_vars_active = true;
        }
//for all reviews
        if (!empty($params['cp_is_all_page'])) {
            $all_pos_posts = $all_neg_posts = $most_h_post = $most_u_post = 0;
            if (!empty($posts)) {
                $params['for_disc'] = [];
                $post_limit = $pr_settings['barrier_for_positive_ap'];
                $max_pos_rate = $min_neg_rate = 0;
                if ($pr_settings['show_most_help_block_ap'] == 'Y') {
                    $most_h_posts = db_get_hash_array("SELECT " . implode(',', $fields) . " FROM ?:discussion_posts $join WHERE 1 $condition AND ?:discussion_posts.cp_pos_post > ?i ORDER BY ?:discussion_posts.cp_pos_post desc,?:discussion_posts.timestamp desc  LIMIT 2", 'post_id', 0);
                    if (!empty($most_h_posts)) {
                        if ($pr_settings['show_image_in_post_ap'] == 'Y') {
                            fn_cp_power_reviews_get_more_post_data($most_h_posts, 'ALL', false, true);
                        } else {
                            fn_cp_power_reviews_get_more_post_data($most_h_posts, 'ALL', false, false);
                        }
                        $params['for_disc']['cp_top_help'] = [];
                        $params['for_disc']['cp_top_help'] = $most_h_posts;
                    }
                }
                if ($pr_settings['allow_most_bl_ap'] == 'Y') {
                    $most_pos_post = db_get_hash_array("SELECT " . implode(',', $fields) . " FROM ?:discussion_posts $join WHERE 1 $condition AND ?:discussion_rating.rating_value > ?i ORDER BY ?:discussion_rating.rating_value desc LIMIT 1", 'post_id', $post_limit);
                    if (!empty($most_pos_post)) {;
                        if ($pr_settings['show_image_in_post_ap'] == 'Y') {
                            fn_cp_power_reviews_get_more_post_data($most_pos_post, 'ALL', false, true);
                        } else {
                            fn_cp_power_reviews_get_more_post_data($most_pos_post, 'ALL', false, false);
                        }
                        $params['for_disc']['most_h_post'] = [];
                        $params['for_disc']['most_h_post'] = reset($most_pos_post);
                    }
                    $most_neg_post = db_get_hash_array("SELECT " . implode(',', $fields) . " FROM ?:discussion_posts $join WHERE 1 $condition AND ?:discussion_rating.rating_value <= ?i AND ?:discussion_rating.rating_value > ?i ORDER BY ?:discussion_rating.rating_value asc LIMIT 1", 'post_id', $post_limit,0);
                    if (!empty($most_neg_post)) {
                        if ($pr_settings['show_image_in_post_ap'] == 'Y') {
                            fn_cp_power_reviews_get_more_post_data($most_neg_post, 'ALL', false, true);
                        } else {
                            fn_cp_power_reviews_get_more_post_data($most_neg_post, 'ALL', false, false);
                        }
                        $params['for_disc']['most_u_post'] = [];
                        $params['for_disc']['most_u_post'] = reset($most_neg_post);
                    }
                }
            }
        }
//
        $pb_status = Registry::get('addons.cp_power_blog');
        $params['cur_disc_type'] = '';
        $feat_var_data = [];
        foreach ($posts as $k => $v) {
            if (isset($post['ip_address'])) {
                $posts[$k]['ip_address'] = fn_ip_from_db($post['ip_address']);
            }
            if (!empty($initial_params['truncate_message']) && mb_strlen($v['message'], 'UTF-8') > $initial_params['truncate_message']) {
                $v['message'] = trim($v['message']);
                $truncated = explode(' ', $v['message']);
                $posts[$k]['message_last_words'] = [];
                $kkk = 1;
                while ($kkk <= $initial_params['show_last_trunc_words']) {        
                    $posts[$k]['message_last_words'][] = $truncated[count($truncated)-$kkk];
                    $kkk ++;
                }
                $posts[$k]['message_last_words'] = array_reverse($posts[$k]['message_last_words']);
                $posts[$k]['message_last_words'] = implode(' ', $posts[$k]['message_last_words']);
            }
            $posts[$k]['object_data'] = fn_get_discussion_object_data($v['object_id'], $v['object_type'], DESCR_SL);
            if ($v['object_type'] == 'P' && defined('CP_PR_VARIATIONS_TYPE') && in_array(CP_PR_VARIATIONS_TYPE, ['Y','S','NS'])) {
                if (empty($feat_var_data[$v['object_id']])) {
                    $get_fields = 'pvgp.group_id, pvgf.purpose, pfv.feature_id, pfv.variant_id, pfv.value, pfv.value_int, pfvd.variant, pfd.description';
                    $var_feats_data = db_get_array("SELECT $get_fields FROM ?:product_variation_group_products as pvgp 
                        LEFT JOIN ?:product_variation_group_features as pvgf ON pvgf.group_id = pvgp.group_id 
                        LEFT JOIN ?:product_features_descriptions pfd ON pfd.feature_id = pvgf.feature_id AND pfd.lang_code = ?s
                        LEFT JOIN ?:product_features_values as pfv ON pfv.feature_id = pvgf.feature_id AND pfv.product_id = ?i AND pfv.lang_code = ?s
                        LEFT JOIN ?:product_feature_variant_descriptions as pfvd ON pfvd.variant_id = pfv.variant_id AND pfvd.lang_code = ?s
                        WHERE pvgp.product_id = ?i", DESCR_SL, $v['object_id'], DESCR_SL, DESCR_SL, $v['object_id']);
                    $feat_var_data[$v['object_id']] = $var_feats_data;
                }
                $posts[$k]['object_data']['var_features'] = $feat_var_data[$v['object_id']];
                $posts[$k]['object_data']['extra_var_name'] = '';
                foreach($posts[$k]['object_data']['var_features'] as $post_fv) {
                    if (!empty($posts[$k]['object_data']['extra_var_name'])) {
                        $posts[$k]['object_data']['extra_var_name'] .= '; ';
                    }
                    $posts[$k]['object_data']['extra_var_name'] .= $post_fv['description'] . ': ' . $post_fv['variant'];
                }
            }
            if (mb_strlen($posts[$k]['object_data']['description'], 'UTF-8') > /*$initial_params['truncate_object_name']*/ 70) {
                $posts[$k]['object_data']['description'] = trim($posts[$k]['object_data']['description']);
            
                $truncated = explode(' ', $posts[$k]['object_data']['description']);
                
                if (count($truncated) > /*$initial_params['name_show_last_truncated_words']*/2) {
                    $posts[$k]['name_last_words'] = [];
                    $kkk = 1;    
                    while ($kkk <= /*$initial_params['name_show_last_truncated_words']*/2) {        
                        $posts[$k]['name_last_words'][] = $truncated[count($truncated)-$kkk];
                        $kkk ++;
                    }
                    $posts[$k]['name_last_words'] = array_reverse($posts[$k]['name_last_words']);
                    $posts[$k]['name_last_words'] = implode(' ', $posts[$k]['name_last_words']);
                }
            }    
            $o_type = '';
            if ($v['object_type'] == 'P') {
                $o_type = 'product';
            } elseif ($v['object_type'] == 'C') {
                $o_type = 'category';
            } elseif ($v['object_type'] == 'B' && !empty($pb_status) && $pb_status['status'] == 'A') {
                $o_type = 'cp_blog_post';
            } elseif ($v['object_type'] == 'B') {
                $o_type = 'blog';
            }
            if (!empty($o_type)) {
                $cs_cart_vers = fn_cp_power_reviews_check_version ('4101');
                if ($v['object_type'] == 'P' && !empty($prod_vars_active) && empty($cs_cart_vers)) {
                    $show_var_id = db_get_field("SELECT product_id FROM ?:products WHERE parent_product_id = ?i AND is_default_variation = ?s", $v['object_id'], 'Y');
                    if (!empty($show_var_id)) {
                        $posts[$k]['object_data']['main_pair'] = fn_get_image_pairs($show_var_id, $o_type, 'M', true, true, CART_LANGUAGE);
                    } else {
                        $posts[$k]['object_data']['main_pair'] = fn_get_image_pairs($v['object_id'], $o_type, 'M', true, true, CART_LANGUAGE);
                    }
                } else {
                    $posts[$k]['object_data']['main_pair'] = fn_get_image_pairs($v['object_id'], $o_type, 'M', true, true, CART_LANGUAGE);
                }
            }
            if ($v['object_type'] == 'M') {
                $posts[$k]['object_data']['main_pair'] = fn_get_logos($v['object_id']);
            }
            if (!empty($v['thread_id'])) {
                $posts[$k]['cp_total_thread_posts'] = db_get_field("SELECT COUNT(post_id) FROM ?:discussion_posts WHERE thread_id = ?i", $v['thread_id']);
            }
            if (!empty($initial_params['truncate_message'])) {
                $posts[$k]['cp_block_trunc_msg'] = $initial_params['truncate_message'];
            }
            if (!empty($initial_params['block_data'])) {
                $posts[$k]['cp_is_block_run'] = true;
            }
            if (!empty($params['is_cur_product']) && $v['object_id'] == $params['is_cur_product']) {
                $params['cur_disc_type'] = $v['type'];
            }
        }
        $cp_skip_img = $show_img = false;
        if (!empty($initial_params['cp_fill_type']) && !empty($initial_params['skip_reviews_wtihout_img']) && $initial_params['skip_reviews_wtihout_img'] == 'Y') {
            $cp_skip_img = true;
        }
        if (!empty($initial_params['show_review_image']) && $initial_params['show_review_image'] != 'not_display') {
            $show_img = true;
        } elseif (!empty($params['cp_is_all_page']) && $pr_settings['show_image_in_post_ap'] == 'Y') {
            $show_img = true;
        }
        if (!empty($params['cp_is_all_page'])) {
            $cp_type = 'ALL';
        } else {
            $cp_type = '';
        }
        fn_cp_power_reviews_get_more_post_data($posts, $cp_type, $cp_skip_img, $show_img);
    }
    return [$posts, $params];
}

function fn_cp_power_reviews_get_mosts_post_data (&$posts, $initial_params) 
{

    foreach ($posts as $k => $v) {
        if (isset($post['ip_address'])) {
            $posts[$k]['ip_address'] = fn_ip_from_db($post['ip_address']);
        }
        if (!empty($initial_params['truncate_message']) && mb_strlen($v['message'], 'UTF-8') > $initial_params['truncate_message']) {
            $v['message'] = trim($v['message']);
            $truncated = explode(' ', $v['message']);
            $posts[$k]['message_last_words'] = [];
            $kkk = 1;    
            while ($kkk <= $initial_params['show_last_trunc_words']) {        
                $posts[$k]['message_last_words'][] = $truncated[count($truncated)-$kkk];
                $kkk ++;
            }
            $posts[$k]['message_last_words'] = array_reverse($posts[$k]['message_last_words']);
            $posts[$k]['message_last_words'] = implode(' ', $posts[$k]['message_last_words']);
        }
        $posts[$k]['object_data'] = fn_get_discussion_object_data($v['object_id'], $v['object_type'], DESCR_SL);
        if (mb_strlen($posts[$k]['object_data']['description'], 'UTF-8') > /*$initial_params['truncate_object_name']*/ 70) {
            $posts[$k]['object_data']['description'] = trim($posts[$k]['object_data']['description']);
            $truncated = explode(' ', $posts[$k]['object_data']['description']);
            if (count($truncated) > /*$initial_params['name_show_last_truncated_words']*/2) {
                $posts[$k]['name_last_words'] = [];
                $kkk = 1;    
                while ($kkk <= /*$initial_params['name_show_last_truncated_words']*/2) {        
                    $posts[$k]['name_last_words'][] = $truncated[count($truncated)-$kkk];
                    $kkk ++;
                }
                $posts[$k]['name_last_words'] = array_reverse($posts[$k]['name_last_words']);
                $posts[$k]['name_last_words'] = implode(' ', $posts[$k]['name_last_words']);
            }
        }    
        $o_type = '';
        if ($v['object_type'] == 'P') {
            $o_type = 'product';
        } elseif ($v['object_type'] == 'C') {
            $o_type = 'category';
        }
        if (!empty($o_type)) {
            $posts[$k]['object_data']['main_pair'] = fn_get_image_pairs($v['object_id'], $o_type, 'M', true, true, CART_LANGUAGE);
        }
        if ($v['object_type'] == 'M') {
            $posts[$k]['object_data']['main_pair'] = fn_get_logos($v['object_id']);
        }
        if (!empty($v['thread_id'])) {
            $posts[$k]['cp_total_thread_posts'] = db_get_field("SELECT COUNT(post_id) FROM ?:discussion_posts WHERE thread_id = ?i", $v['thread_id']);
        }
    }
    return $posts;
}

function fn_cp_power_reviews_check_version ($vers) {
    if ($vers == '49') {
        $result = false;
    } else {
        $result = true;
    }
    if ($vers == '49' && version_compare(PRODUCT_VERSION, '4.9.0', '>')) {
        $result = true;
    }
    if ($vers == '4101' && version_compare(PRODUCT_VERSION, '4.9.3', '>')) {
        $result = true;
    }
    return $result;
}
//get new params for prod_review block
function fn_cp_power_reviews_get_new_params ($block_params, $request_params) {
    $new_params = array(
        'cp_fill_type' => $block_params['cp_fill_type'],
        'min_rating_limit' => $block_params['min_rating_limit'],
        'max_rating_limit' => $block_params['max_rating_limit'],
        'limit' => $block_params['limit'],
        'skip_reviews_wtihout_img' => $block_params['skip_reviews_wtihout_img'],
        'truncate_text_length' => $block_params['truncate_text_length'],
        'show_last_trunc_words' => $block_params['show_last_trunc_words'],
        'show_review_image' => $block_params['show_review_image']
    );
    if (!empty($request_params)) {
        $new_params = array_merge($new_params, $request_params);
    }
    return $new_params;
}

function fn_cp_power_reviews_get_discussions(&$params, $items_per_page, &$fields, &$join, &$condition, $sorting, &$limit)
{
    $fields .= ", ?:discussion_messages.cp_pr_title, ?:discussion_messages.cp_pr_advantages, ?:discussion_messages.cp_pr_disadvantages";
    if (AREA == 'C') {
        if (!empty($params['items_per_page'])) {
            $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:discussion_posts $join WHERE 1 $condition AND ?:discussion_posts.cp_pr_user_delete = ?s", 'N');
            $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
        }
    }
    if (!empty($params['cp_ait_only_new'])) {
        $join .= db_quote(" LEFT JOIN ?:images_links as cp_ait_lr ON cp_ait_lr.object_id = ?:discussion_posts.post_id AND cp_ait_lr.object_type = ?s", 'cp_rev_post');
        $join .= db_quote(" LEFT JOIN ?:images ON ?:images.image_id = cp_ait_lr.detailed_id");
        $condition .= db_quote(' AND ?:images.cp_ait_is_new = ?s', $params['cp_ait_only_new']);
    }
    if (!empty($params['cp_pr_with_images']) && $params['cp_pr_with_images'] == 'Y') {
        $all_images_posts = db_get_fields("SELECT DISTINCT(?:cp_review_images.post_id) FROM ?:cp_review_images 
            LEFT JOIN ?:discussion_posts ON ?:discussion_posts.post_id = ?:cp_review_images.post_id");
            
        if (!empty($all_images_posts)) {
            $condition .= db_quote(" AND ?:discussion_posts.post_id IN (?n)", $all_images_posts);
        }
    }
}

function fn_cp_power_reviews_get_discussions_post ($params, $items_per_page, &$posts) {
    if (!empty($posts)) {
        fn_cp_power_reviews_get_more_post_data($posts);
    }
}
//get more post data
function fn_cp_power_reviews_get_more_post_data(&$posts, $cp_type = '', $cp_skip_img = false, $show_img = false) {
    if (!empty($posts)) {
        $review_settings = Registry::get('addons.cp_power_reviews');
        $object_suffix = fn_cp_pr_get_object_suffixes();
        foreach($posts as $key => $post_data) {
            $get_images = false;
            $words_limit = 0;
            if ((!empty($post_data['object_type']) && in_array($post_data['object_type'], array('P','E','M','C','A','B'))) || in_array($cp_type, array('P','E','M','C','A','B','ALL'))) {
                if (fn_allowed_for('MULTIVENDOR')) {
                    $posts[$key]['storefront_data'] = db_get_row("SELECT ?:storefronts.name, ?:storefronts.storefront_id FROM ?:storefronts 
                        LEFT JOIN ?:cp_pr_reviews_storefronts ON ?:cp_pr_reviews_storefronts.storefront_id = ?:storefronts.storefront_id 
                        WHERE ?:cp_pr_reviews_storefronts.post_id = ?i", $post_data['post_id']);
                }
                if (!empty($post_data['object_type']) && $post_data['object_type'] == 'P' || $cp_type == 'P') {
                    $words_limit = $review_settings['msg_word_limit'];
                    if ($review_settings['show_image_in_post'] == 'Y') {
                        $get_images = true;
                    }
                    $posts[$key]['cp_attr_ratings'] = db_get_hash_array("SELECT ?:cp_power_ext_reviews.view_type, ?:cp_pow_attr_ratings.*, ?:cp_power_rev_products.attr_pos, ?:cp_pow_attr_descr.cp_attr_name FROM ?:cp_pow_attr_ratings
                        LEFT JOIN ?:cp_power_rev_products ON ?:cp_power_rev_products.cp_attr_id = ?:cp_pow_attr_ratings.cp_attr_id 
                        LEFT JOIN ?:discussion ON ?:discussion.object_id = ?:cp_power_rev_products.product_id
                        LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_pow_attr_ratings.cp_attr_id 
                        LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_pow_attr_ratings.cp_attr_id
                        WHERE ?:cp_pow_attr_ratings.post_id = ?i AND ?:cp_pow_attr_descr.lang_code = ?s AND ?:cp_power_ext_reviews.status = ?s AND ?:discussion.thread_id = ?s AND ?:discussion.object_type = ?s ORDER BY ?:cp_power_rev_products.attr_pos", 'cp_attr_id', $post_data['post_id'], DESCR_SL, 'A', $post_data['thread_id'], 'P');
                        
                    $prod_main_cat = db_get_field("SELECT ?:products_categories.category_id FROM ?:products_categories 
                        LEFT JOIN ?:discussion ON ?:discussion.object_id = ?:products_categories.product_id
                        WHERE ?:discussion.thread_id = ?i AND ?:products_categories.link_type = ?s", $post_data['thread_id'], 'M');
                    if (!empty($prod_main_cat)) {
                        if (!empty($posts[$key]['cp_attr_ratings'])) {
                            $already_get_ids = array_keys($posts[$key]['cp_attr_ratings']);
                        } else {
                            $already_get_ids = [];
                        }
                        
                        $cat_prod_attr = db_get_hash_array("SELECT ?:cp_power_ext_reviews.view_type, ?:cp_pow_attr_ratings.*, ?:cp_power_rev_cats.attr_pos, ?:cp_pow_attr_descr.cp_attr_name FROM ?:cp_pow_attr_ratings
                            LEFT JOIN ?:cp_power_rev_cats ON ?:cp_power_rev_cats.cp_attr_id = ?:cp_pow_attr_ratings.cp_attr_id
                            LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_pow_attr_ratings.cp_attr_id 
                            LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_pow_attr_ratings.cp_attr_id
                            WHERE ?:cp_pow_attr_ratings.post_id = ?i AND ?:cp_pow_attr_descr.lang_code = ?s AND ?:cp_power_ext_reviews.status = ?s AND ?:cp_power_ext_reviews.cp_attr_id NOT IN (?n) ORDER BY ?:cp_power_rev_cats.attr_pos", 'cp_attr_id', $post_data['post_id'], DESCR_SL, 'A', $already_get_ids);
                    }
                    if (!empty($cat_prod_attr)) {
                        if (!empty($posts[$key]['cp_attr_ratings'])) {
                            $posts[$key]['cp_attr_ratings'] = $cat_prod_attr + $posts[$key]['cp_attr_ratings'];
                            uasort($posts[$key]['cp_attr_ratings'], "fn_cp_power_reviews_sort_reviews_by_pos");
                        } else {
                            $posts[$key]['cp_attr_ratings'] = $cat_prod_attr;
                        }
                    }
                } elseif (!empty($post_data['object_type']) && in_array($post_data['object_type'], array('E', 'M')) || in_array($cp_type, array('E', 'M'))) {
                    
                    if ((!empty($post_data['object_type']) && $post_data['object_type'] == 'E') || $cp_type == 'E') {
                        $words_limit = $review_settings['msg_word_limit_test'];
                        if ($review_settings['show_image_in_post_test'] == 'Y') {
                            $get_images = true;
                        }
                        $sql_type = 'E';
                    } elseif ((!empty($post_data['object_type']) && $post_data['object_type'] == 'M') || $cp_type == 'M') {
                        $words_limit = $review_settings['msg_word_limit_vend'];
                        if ($review_settings['show_image_in_post_vend'] == 'Y') {
                            $get_images = true;
                        }
                        $sql_type = 'M';
                    }
                    $posts[$key]['cp_attr_ratings'] = db_get_array("SELECT ?:cp_power_ext_reviews.view_type, ?:cp_pow_attr_ratings.*, ?:cp_pow_attr_descr.cp_attr_name FROM ?:cp_pow_attr_ratings
                        LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_pow_attr_ratings.cp_attr_id 
                        LEFT JOIN ?:discussion ON ?:discussion.object_type = ?:cp_power_ext_reviews.object_type
                        LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_pow_attr_ratings.cp_attr_id
                        WHERE ?:cp_pow_attr_ratings.post_id = ?i AND ?:cp_pow_attr_descr.lang_code = ?s AND ?:cp_power_ext_reviews.status = ?s AND ?:discussion.thread_id = ?s AND ?:cp_power_ext_reviews.object_type = ?s", $post_data['post_id'], DESCR_SL, 'A', $post_data['thread_id'], $sql_type);
                
                } elseif ((!empty($post_data['object_type']) && $post_data['object_type'] == 'C') || $cp_type == 'C') {
                    $words_limit = $review_settings['msg_word_limit_cat'];
                    if ($review_settings['show_image_in_post_cat'] == 'Y') {
                        $get_images = true;
                    }
                } elseif ((!empty($post_data['object_type']) && in_array($post_data['object_type'], array('A','B'))) || in_array($cp_type, array('A','B'))) {
                    $words_limit = $review_settings['msg_word_limit_page'];
                    if ($review_settings['show_image_in_post_page'] == 'Y') {
                        $get_images = true;
                    }
                } 
                if ($cp_type == 'ALL') {
                    $words_limit = $review_settings['msg_word_limit_ap'];
                    if ($review_settings['show_image_in_post_ap'] == 'Y') {
                        $get_images = true;
                    } else {
                        $get_images = false;
                    }
                }
                if (!isset($get_images) && !empty($show_img)) {
                    $get_images = true;
                }
                if (!empty($posts[$key]['cp_attr_ratings'])) {
                    if (!empty($post_data['rating_value']) && (!empty($post_data['object_type']) && in_array($post_data['object_type'], array('P','E','M')) || in_array($cp_type, array('P','E','M')))) {
                        $posts[$key]['cp_av_rating_post'] = $post_data['rating_value'];
                    } else {
                        $posts[$key]['cp_av_rating_post'] = $sred_rate = round(db_get_field("SELECT AVG(?:cp_pow_attr_ratings.rating) FROM ?:cp_pow_attr_ratings  
                            LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_pow_attr_ratings.cp_attr_id 
                            WHERE ?:cp_pow_attr_ratings.post_id = ?i AND ?:cp_pow_attr_ratings.rating > ?i AND ?:cp_power_ext_reviews.status = ?s", $post_data['post_id'], 0, 'A'), 1);
                        
                        if (!empty($sred_rate)) {
                            $posts[$key]['cp_av_rate_stars'] = fn_cp_power_reviews_discussion_rating($sred_rate);
                        }
                    }
                    $posts[$key]['cp_attr_ratings'] = fn_cp_pr_get_view_type_txts($posts[$key]['cp_attr_ratings'], DESCR_SL);
                } else {
                    if (!empty($post_data['rating_value'])) {
                        $posts[$key]['cp_av_rating_post'] = $post_data['rating_value'];
                    }
                }
                if (!empty($get_images)) {
                    $cp_review_pairs = fn_get_image_pairs($post_data['post_id'], 'cp_rev_post', 'A', true, true, CART_LANGUAGE);
                    if (!empty($cp_review_pairs)) {
                        $post_images_data = db_get_hash_array("SELECT post_image_id, status FROM ?:cp_review_images WHERE post_id = ?i", 'post_image_id', $post_data['post_id']);
                        if (!empty($post_images_data)) {
                            foreach($cp_review_pairs as $pair_id => $img_data) {
                                if (!empty($post_images_data[$img_data['pair_id']])) {
                                    if ($post_images_data[$img_data['pair_id']]['status'] == 'A' && AREA == 'C') {
                                        $posts[$key]['cp_review_pairs'][$pair_id] = $cp_review_pairs[$pair_id];
                                        $posts[$key]['cp_review_pairs'][$pair_id]['status'] = $post_images_data[$img_data['pair_id']]['status'];
                                    } elseif (AREA == 'A') {
                                        $posts[$key]['cp_review_pairs'][$pair_id] = $cp_review_pairs[$pair_id];
                                        $posts[$key]['cp_review_pairs'][$pair_id]['status'] = $post_images_data[$img_data['pair_id']]['status'];
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($cp_type)) {
                    $obj_type = $cp_type;
                } elseif (!empty($post_data['object_type'])) {
                    $obj_type = $post_data['object_type'];
                }
                if (isset($object_suffix[$obj_type]) && !empty($review_settings['show_videos_in_post' . $object_suffix[$obj_type]]) 
                    && $review_settings['show_videos_in_post' . $object_suffix[$obj_type]] == 'Y' || AREA == 'A') {
                    $video_cond = '';
                    if (AREA == 'C') {
                        $video_cond .= db_quote(" AND status = ?s", 'A');
                    }
                    $video_data = db_get_row("
                        SELECT * FROM ?:cp_pr_video_links WHERE post_id = ?i ?p", $post_data['post_id'], $video_cond
                    );
                    if (!empty($video_data)) {
                        $video_data['preview'] = fn_get_image_pairs($video_data['video_id'], 'cp_pr_video_preview', 'M', true, true, CART_LANGUAGE);
                        if (empty($video_data['preview'])) { // add default preview img
                            $video_data['preview_def'] = Storage::instance('images')->getUrl('cp_pr_youtube.jpg');
                        }
                        $posts[$key]['video_data'] = $video_data;
                    }
                }
            }
            if (!empty($post_data['cp_block_trunc_msg'])) {
                $words_limit = $post_data['cp_block_trunc_msg'];
            }
            if (!empty($post_data['message'])) {
                if (!empty($words_limit)) {
                    if (!empty($post_data['cp_is_block_run'])) {
                        $fileds_to_slice = [];
                    } else {
                        $fileds_to_slice = array('msg' => 'message','adv' => 'cp_pr_advantages','disadv' => 'cp_pr_disadvantages');
                    }
                    foreach($fileds_to_slice as $sl_key => $p_filed) {
                        if (!empty($post_data[$p_filed])) {
                            $short = explode(' ', $post_data[$p_filed]);
                            $count = count($short);
                            //$posts[$key]['cp_words_amount'] = $count;
                            if(!empty($short)) {
                                if ($count > $words_limit) {
                                    $posts[$key]['short_' . $sl_key] = array_slice($short, 0, $words_limit);
        //                             $posts[$key]['short_msg_last'] = array_slice($short, $words_limit);
        //                             $posts[$key]['short_msg_last'] = implode(' ', $posts[$key]['short_msg_last']);
                                    $posts[$key]['short_' . $sl_key] = implode(' ', $posts[$key]['short_' . $sl_key]);
                                }
                            }
                        }
                    }
                    if (!empty($post_data['cp_admin_answ']) && !empty($post_data['cp_admin_id'])) {
                        $anw_short = explode(' ', $post_data['cp_admin_answ']);
                        $anw_count = count($anw_short);
                        if(!empty($anw_short)) {
                            if ($anw_count > $words_limit) {
                                $posts[$key]['answ_short_msg'] = array_slice($anw_short, 0, $words_limit);
//                                 $posts[$key]['answ_short_msg_last'] = array_slice($anw_short, $words_limit);
//                                 $posts[$key]['answ_short_msg_last'] = implode(' ', $posts[$key]['answ_short_msg_last']);
                                $posts[$key]['answ_short_msg'] = implode(' ', $posts[$key]['answ_short_msg']);
                            } 
                        }
                    }
                }
                /*else {
                    $short = explode(' ', $post_data['message']);
                    $posts[$key]['cp_words_amount'] = count($short);
                }
                */
                $posts[$key]['total_chars'] = iconv_strlen($post_data['message'], 'UTF-8');
            }
            if (!empty($cp_skip_img) && empty($posts[$key]['cp_review_pairs'])) {
                unset($posts[$key]);
            }
        }
    }
    return true;
}
function fn_cp_power_reviews_discussion_rating($rating_value) {

    $starss = [];
    if (!empty($rating_value)) {
        $starss['full'] = floor($rating_value);
        $starss['part'] = $rating_value - $starss['full'];
        $starss['empty'] = 5 - $starss['full'] - (($starss['part'] == 0) ? 0 : 1);
        if (!empty($starss['part'])) {
            if ($starss['part'] <= 0.25) {
                $starss['part'] = 1;
            } elseif ($starss['part'] <= 0.5) {
                $starss['part'] = 2;
            } elseif ($starss['part'] <= 0.75) {
                $starss['part'] = 3;
            } elseif ($starss['part'] <= 0.99) {
                $starss['part'] = 4;
            }
        }
    }
    return $starss;
}
function fn_cp_power_reviews_replace_placeholders ($text, $order_info, $company_info) {
    $text = str_replace('%firstname%', $order_info['firstname'], $text);
    $text = str_replace('%lastname%', $order_info['lastname'], $text);
    if (!empty($company_info)) {
        foreach($company_info as $key => $value) {
            $text = str_replace('%'.$key.'%', $company_info[$key], $text);
        }
    }
    return $text;
}

function fn_cp_power_reviews_update_discussion_posts ($posts) {
    if (!empty($posts) && is_array($posts)) {
        $threads = db_get_hash_single_array("SELECT post_id, thread_id FROM ?:discussion_posts WHERE post_id IN (?n)", array('post_id', 'thread_id'), array_keys($posts));
        $messages_exist = db_get_fields("SELECT post_id FROM ?:discussion_messages WHERE post_id IN (?n)", array_keys($posts));
        $rating_exist = db_get_fields("SELECT post_id FROM ?:discussion_rating WHERE post_id IN (?n)", array_keys($posts));
        fn_delete_notification('company_access_denied');
        $answ_name = '';
        if (!empty(Tygh::$app['session']['auth']) && !empty(Tygh::$app['session']['auth']['user_id']) && AREA == 'A' && Tygh::$app['session']['auth']['user_type'] == 'A') {
            $amd_name = db_get_row("SELECT firstname, lastname FROM ?:users WHERE user_id = ?i", Tygh::$app['session']['auth']['user_id']);
            if (!empty($amd_name)) {
                $answ_name = !empty($amd_name['firstname']) ? $amd_name['firstname'] : '';
                if (!empty($answ_name)) {
                    $answ_name = !empty($amd_name['lastname']) ? $answ_name . ' ' . $amd_name['lastname'] : $answ_name;
                } else {
                    $answ_name = !empty($amd_name['lastname']) ? $amd_name['lastname'] : '';
                }
            }
            if (empty($answ_name)) {
                $answ_name = __('administrator');
            }
        }
        foreach ($posts as $p_id => $data) {
            unset($data['thread_id'], $data['post_id']);

            if (!empty($data['date'])) {
                if (empty($data['time'])) {
                    $data['time'] = '00:00';
                }
                $data['timestamp'] = fn_cp_power_reviews_parse_datetime($data['date'] . ' ' . $data['time']);
            }
            
            if (!empty($data['cp_admin_answ'])) {
                $data['cp_admin_answ'] = trim($data['cp_admin_answ']);
                if (!empty($data['cp_admin_answ']) && empty($data['cp_admin_id'])) {
                    $data['cp_admin_id'] = $answ_name;
                }
                $check_time = db_get_field("SELECT cp_admin_answ_time FROM ?:discussion_posts WHERE post_id = ?i", $p_id);
                if (empty($check_time)) {
                    $data['cp_admin_answ_time'] = time();
                }
            } else {
                $data['cp_admin_id'] = '';
            }
            // Validate rating value
            if (!empty($data['ratings'])) {
                $sred_rat = $counter = 0;
                foreach($data['ratings'] as $cp_attr_id => $rating) {
                    if (!in_array($rating, array_keys(fn_get_discussion_ratings()))) {
                        unset($data['ratings'][$cp_attr_id]);
                    } else {
                        $sred_rat = $sred_rat + $rating;
                        $counter = $counter + 1;
                    }
                }
                if (!empty($sred_rat)) {
                    $data['cp_sred_rate'] = $sred_rat/$counter;
                    if (empty($data['cp_pr_common_rate_exist'])) {
                        $data['rating_value'] = floor($sred_rat/$counter);
                    } elseif(!empty($data['rating_value'])) {
                        $data['cp_sred_rate'] = $data['rating_value'];
                    }
                }
            }
            if (!empty($data['rating_value'])) {
                if (!in_array($data['rating_value'], array_keys(fn_get_discussion_ratings()))) { 
                    unset($data['rating_value']);
                } else {
                    $data['cp_sred_rate'] = $data['rating_value'];
                }
            }

            db_query("UPDATE ?:discussion_posts SET ?u WHERE post_id = ?i", $data, $p_id);

            if (in_array($p_id, $messages_exist)) {
                db_query("UPDATE ?:discussion_messages SET ?u WHERE post_id = ?i", $data, $p_id);
            } else {
                $data['thread_id'] = $threads[$p_id];
                $data['post_id'] = $p_id;
                db_query("INSERT INTO ?:discussion_messages ?e", $data);
            }

            if (in_array($p_id, $rating_exist)) {
                db_query("UPDATE ?:discussion_rating SET ?u WHERE post_id = ?i", $data, $p_id);
                
            } else {
                $data['thread_id'] = $threads[$p_id];
                $data['post_id'] = $p_id;
                db_query("INSERT INTO ?:discussion_rating ?e", $data);
            }
            if (!empty($data['ratings']) && !empty($p_id)) {
                foreach($data['ratings'] as $cp_attr_id => $rating) {
                    $pt_data = array(
                        'cp_attr_id' => $cp_attr_id,
                        'post_id' => $p_id,
                        'rating' => $rating,
                        'post_status' => db_get_field("SELECT status FROM ?:discussion_posts WHERE post_id = ?i", $p_id)
                    );
                    db_query("REPLACE INTO ?:cp_pow_attr_ratings ?e", $pt_data);
                }
            }
        }
    }
    return true;
}

function fn_cp_power_reviews_add_like_to_post ($cp_like, $post_id, $auth, $object_type = '') {
    $result = false;
    if (!empty($object_type) && $object_type == 'ALL' && !empty($post_id)) {
        $object_type = db_get_field("SELECT ?:discussion.object_type FROM ?:discussion
            LEFT JOIN ?:discussion_posts ON ?:discussion_posts.thread_id = ?:discussion.thread_id WHERE ?:discussion_posts.post_id = ?i", $post_id);
    }
    if (!empty($auth['user_id'])) {
        $check_post_like = db_get_row("SELECT post_id, rate_type FROM ?:cp_pow_attr_likes_users WHERE post_id = ?i AND user_id = ?i", $post_id, $auth['user_id']);
        if (!empty($check_post_like) && $check_post_like['rate_type'] == $cp_like) {
            fn_set_notification('W', __('warning'), __('you_already_rate_this_review'));
        } else {
            db_query("DELETE FROM ?:cp_pow_attr_likes_users WHERE post_id = ?i AND user_id = ?i", $check_post_like, $auth['user_id']);
            $like_data = array(
                'post_id' => $post_id,
                'user_id' => $auth['user_id'],
                'rate_type' => $cp_like
            );
            db_query("INSERT INTO ?:cp_pow_attr_likes_users ?e ON DUPLICATE KEY UPDATE ?u", $like_data, $like_data);
            if ($cp_like == 'Y') {
                if (!empty($check_post_like)) {
                    db_query("UPDATE ?:discussion_posts SET cp_neg_post = cp_neg_post-1 WHERE post_id = ?i", $post_id);
                }
                db_query("UPDATE ?:discussion_posts SET cp_pos_post = cp_pos_post + 1 WHERE post_id = ?i", $post_id);
            } else {
                if (!empty($check_post_like)) {
                    db_query("UPDATE ?:discussion_posts SET cp_pos_post = cp_pos_post-1 WHERE post_id = ?i", $post_id);
                }
                db_query("UPDATE ?:discussion_posts SET cp_neg_post = cp_neg_post + 1 WHERE post_id = ?i", $post_id);
            }
        }
    } else {
        if (!empty($object_type) && !empty($post_id)) {
            $allow_anon = false;
            if ($object_type == 'P' && Registry::get('addons.cp_power_reviews.allow_anon_post_vote') == 'Y') {
                $allow_anon = true;
            } elseif ($object_type == 'C' && Registry::get('addons.cp_power_reviews.allow_anon_post_vote_cat') == 'Y') {
                $allow_anon = true;
            } elseif (($object_type == 'A' || $object_type == 'B') && Registry::get('addons.cp_power_reviews.allow_anon_post_vote_page') == 'Y') {
                $allow_anon = true;
            } elseif ($object_type == 'E' && Registry::get('addons.cp_power_reviews.allow_anon_post_vote_test') == 'Y') {
                $allow_anon = true;
            } elseif ($object_type == 'M' && Registry::get('addons.cp_power_reviews.allow_anon_post_vote_vend') == 'Y') {
                $allow_anon = true;
            }
            if (!empty($allow_anon)) {
                $session = &Tygh::$app['session'];
                if (!isset($session['cp_pr_anon_posts'])) {
                    $session['cp_pr_anon_posts'] = [];
                }
                if (empty($session['cp_pr_anon_posts'][$post_id])) {
                    $session['cp_pr_anon_posts'][$post_id] = true;
                    if ($cp_like == 'Y') {
                        db_query("UPDATE ?:discussion_posts SET cp_pos_post = cp_pos_post + 1 WHERE post_id = ?i", $post_id);
                    } else {
                        db_query("UPDATE ?:discussion_posts SET cp_neg_post = cp_neg_post + 1 WHERE post_id = ?i", $post_id);
                    }
                } else {
                    fn_set_notification('W', __('warning'), __('you_already_rate_this_review'));
                }
            } else {
                fn_set_notification('E', __('error'), __('error_not_logged'));
                $result = true;
            }
        } else {
            fn_set_notification('E', __('error'), __('error_not_logged'));
            $result = true;
        }
    }
    return $result;
}

function fn_cp_pr_get_object_suffixes()
{
    $object_suffix = array(
        'P'     => '',
        'C'     => '_cat',
        'E'     => '_test',
        'M'     => '_vend',
        'A'     => '_page',
        'B'     => '_page',
        'ALL'   => '_ap'
    );
    return $object_suffix;
}

function fn_cp_power_reviews_add_prod_ratings ($post_data, $send_notifications = true, $from_email = []) {
    
    if (!empty($from_email)) {
        $auth = array(
            'user_id' => $from_email['user_id']
        );
    } else {
        $auth = &Tygh::$app['session']['auth'];
    }
    $discussion_settings = Registry::get('addons.discussion');
    $discussion_object_types = fn_get_discussion_objects();
    $addon_settings = Registry::get('addons.cp_power_reviews');
    
    if (empty($post_data['name']) && $addon_settings['allo_anonymous'] == 'Y') {
        $post_data['name'] = __('cp_pr_anonym_customer');
    }
    $object_suffix = fn_cp_pr_get_object_suffixes();
    
    $object = fn_cp_power_reviews_get_object($post_data);

    if (empty($object) || !fn_cp_power_reviews_check_thread_permissions($object, $auth)) {
        fn_set_notification('E', __('error'), __('cant_find_thread'));
        return false;
    }
    if ($object['object_type'] == 'P' && !empty($auth) && !empty($auth['user_id']) && !empty($object['object_id'])) {// check purchase
        $purch_statses = $addon_settings['orders_for_purchase'];
        if (!empty($purch_statses)) {
            $verif_statuses = [];
            foreach($purch_statses as $st => $val) {
                if (!empty($val) && $val == 'Y') {
                    $verif_statuses[] = $st;
                }
            }
            if (!empty($verif_statuses)) {
                $is_purch = db_get_field("SELECT ?:orders.order_id FROM ?:orders
                    LEFT JOIN ?:order_details ON ?:order_details.order_id = ?:orders.order_id
                    WHERE ?:orders.status IN (?a) AND ?:orders.user_id = ?i AND ?:order_details.product_id = ?i", $verif_statuses, $auth['user_id'], $object['object_id']);
                if (!empty($is_purch)) {
                    $post_data['cp_pr_verified_purchase'] = 'Y';
                }
            }
        }
    }
    $post_data['thread_id'] = $object['thread_id'];
    $object_data = fn_get_discussion_object_data($object['object_id'], $object['object_type']);
    $object_name = $discussion_object_types[$object['object_type']];
    $ip = fn_get_ip();
    $post_data['ip_address'] = fn_ip_to_db($ip['host']);
    if (!empty($post_data['message'])) {
        $post_data['message'] = trim($post_data['message']);
    }
    $post_data['status'] = 'A';
    if (AREA != 'A' && !empty($discussion_settings[$object_name . '_post_ip_check']) && $discussion_settings[$object_name . '_post_ip_check'] == 'Y') {
        $is_exists = db_get_field(
            "SELECT COUNT(*) FROM ?:discussion_posts WHERE thread_id = ?i AND ip_address = ?s",
            $post_data['thread_id'], $post_data['ip_address']
        );
        if (!empty($is_exists)) {
            if (!empty($from_email)) {
                $prod_name = db_get_field("SELECT ?:product_descriptions.product FROM ?:product_descriptions 
                    LEFT JOIN ?:discussion ON ?:discussion.object_id = ?:product_descriptions.product_id 
                    WHERE ?:discussion.thread_id = ?i AND ?:product_descriptions.lang_code = ?s", $post_data['thread_id'], CART_LANGUAGE);
                fn_set_notification('E', __('error'), __('cp_error_already_posted').' - '.$prod_name);
            } else {
                fn_set_notification('E', __('error'), __('error_already_posted'));
            }
            return false;
        }
    }
    // Check if post needs to be approved
    if (AREA != 'A' && !empty($discussion_settings[$object_name . '_post_approval'])) {
        if ($discussion_settings[$object_name . '_post_approval'] == 'any' || ($discussion_settings[$object_name . '_post_approval'] == 'anonymous' && empty($auth['user_id']))) {
            fn_set_notification('W', __('text_thank_you_for_post'), __('text_post_pended'));
            $post_data['status'] = 'D';
        }
    }
    if (!empty($post_data['date'])) {
        if (empty($post_data['time'])) {
            $post_data['time'] = '00:00';
        }
        $post_data['timestamp'] = fn_cp_power_reviews_parse_datetime($post_data['date'] . ' ' . $post_data['time']);
    } else {
        $post_data['timestamp'] = TIME;
    }
    // Validate rating value
    $sred_rat = $counter = 0;
    if (!empty($post_data['ratings'])) {
        foreach($post_data['ratings'] as $cp_attr_id => $rating) {
            if (!in_array($rating, array_keys(fn_get_discussion_ratings()))) {
                unset($post_data['ratings'][$cp_attr_id]);
            } else {
                $sred_rat = $sred_rat + $rating;
                $counter = $counter + 1;
            }
        }
    }
    if (!empty($sred_rat)) {
        $post_data['cp_sred_rate'] = $sred_rat/$counter;
        if (empty($post_data['cp_pr_common_rate_exist'])) {
            $post_data['rating_value'] = floor($sred_rat/$counter);
        } elseif (!empty($post_data['rating_value'])) {
            $post_data['cp_sred_rate'] = $post_data['rating_value'];
        }
//1st post fix
    } elseif (!empty($post_data['rating_value'])) {
        $post_data['cp_sred_rate'] = $post_data['rating_value'];
    }
//end
    if (!empty($post_data['rating_value']) && !in_array($post_data['rating_value'], array_keys(fn_get_discussion_ratings()))) {
        unset($post_data['rating_value']);
    }
    $post_data['user_id'] = $auth['user_id'];
    $post_data['post_id'] = db_query("INSERT INTO ?:discussion_posts ?e", $post_data);

    db_query("REPLACE INTO ?:discussion_messages ?e", $post_data);
    db_query("REPLACE INTO ?:discussion_rating ?e", $post_data);
    
    if (!empty($post_data['is_recommended'])) {
        fn_cp_power_reviews_add_recommendation($post_data['thread_id'], $post_data['ip_address'], $post_data['is_recommended'], $object['object_type']);
    }
    
    if (!empty($post_data['ratings']) && !empty($post_data['post_id'])) {
        foreach($post_data['ratings'] as $cp_attr_id => $rating) {
            $pt_data = [
                'cp_attr_id'    => $cp_attr_id,
                'post_id'       => $post_data['post_id'],
                'rating'        => $rating,
                'post_status'   => $post_data['status']
            ];
            db_query("REPLACE INTO ?:cp_pow_attr_ratings ?e", $pt_data);
        }
    }
    if (!empty($post_data['post_id'])) {
        if (AREA == 'C') {
            $store_id = Tygh::$app['storefront']->storefront_id;
        } elseif (fn_allowed_for('MULTIVENDOR') && !empty($post_data['storefront_id'])) {
            $store_id = $post_data['storefront_id'];
        }
        if (!empty($store_id)) {
            $post_store_data = [
                'post_id'       => $post_data['post_id'],
                'storefront_id' => $store_id
            ];
            db_replace_into('cp_pr_reviews_storefronts', $post_store_data);
        }
    }
    if (!empty($post_data['post_id']) && !empty($post_data['youtube_id']) && isset($object_suffix[$object['object_type']]) 
        && !empty($addon_settings['show_video_uploader' . $object_suffix[$object['object_type']]]) && $addon_settings['show_video_uploader' . $object_suffix[$object['object_type']]] == 'Y') {
        $video_data = [
            'youtube_id'=> $post_data['youtube_id'],
            'post_id'   => $post_data['post_id']
        ];
        $video_id = db_query("INSERT INTO ?:cp_pr_video_links ?e", $video_data);
        if (isset($post_data['upload_from_youtube']) && $post_data['upload_from_youtube'] == 'Y') {
            $url = str_replace('[VIDEO_ID]', $post_data['youtube_id'], CP_PR_YOUTUBE_PREVIEW_URL);
            fn_cp_pr_video_attach_image('url', $url, 'cp_pr_video_preview', $video_id, false, true, CART_LANGUAGE);
        } else {
            fn_attach_image_pairs('cp_pr_video_preview', 'cp_pr_video_preview', $video_id, CART_LANGUAGE);
        }
    }
    if ($send_notifications) {
        if (empty($lang_code)) {
            $lang_code = CART_LANGUAGE;
        }
        $fn_prepare_subject = function($type, $lang_code) {
            return __('discussion_title_' . $type, '', $lang_code) . ' - ' . __($type, '', $lang_code);
        };
        
        if (version_compare(PRODUCT_VERSION, '4.11.5', '>')) {
            fn_cp_pr_send_emails_event_disp($auth, $object, $object_data, $object_name, $post_data, $fn_prepare_subject, $discussion_object_types, $lang_code);
        } else {
            if ($object['object_type'] == 'O') {
                $order_info = db_get_row(
                    "SELECT email, company_id, lang_code FROM ?:orders WHERE order_id = ?i",
                    $object['object_id']
                );
                if (AREA == 'C') {
                    $lang_code = Registry::get('settings.Appearance.backend_default_language');
                    //Send to admin
                    Mailer::sendMail(array(
                        'to' => 'default_company_orders_department',
                        'from' => 'default_company_orders_department',
                        'data' => array(
                            'url' => fn_url("orders.details?order_id=$object[object_id]", 'A', 'http'),
                            'object_data' => $object_data,
                            'post_data' => $post_data,
                            'object_name' => $object_name,
                            'subject' => $fn_prepare_subject($discussion_object_types[$object['object_type']], $lang_code),
                        ),
                        'template_code' => 'discussion_notification',
                        'tpl' => 'addons/discussion/notification.tpl',
                        'company_id' => $order_info['company_id'],
                    ), 'A', $lang_code);
                    // Send to vendor
                    if (!empty($order_info['company_id']) && !empty($discussion_settings[$object_name . '_notify_vendor']) && $discussion_settings[$object_name . '_notify_vendor'] == 'Y') {
                        $lang_code = fn_get_company_language($order_info['company_id']);
                        Mailer::sendMail(array(
                            'to' => 'company_orders_department',
                            'from' => 'company_orders_department',
                            'data' => array(
                                'url' => fn_url("orders.details?order_id=$object[object_id]", 'V', 'http'),
                                'object_data' => $object_data,
                                'post_data' => $post_data,
                                'object_name' => $object_name,
                                'subject' => $fn_prepare_subject($discussion_object_types[$object['object_type']], $lang_code),
                            ),
                            'template_code' => 'discussion_notification',
                            'tpl' => 'addons/discussion/notification.tpl', // this parameter is obsolete and is used for back compatibility
                            'company_id' => $order_info['company_id'],
                        ), 'A', $lang_code);
                    }

                } elseif (AREA == 'A') {
                    $lang_code = $order_info['lang_code'];
                    
                    Mailer::sendMail(array(
                        'to' => $order_info['email'],
                        'from' => 'company_orders_department',
                        'data' => array(
                            'url' => fn_url("orders.details?order_id=$object[object_id]", 'C', 'http'),
                            'object_data' => $object_data,
                            'post_data' => $post_data,
                            'object_name' => $object_name,
                            'subject' => $fn_prepare_subject($discussion_object_types[$object['object_type']], $lang_code),
                        ),
                        'template_code' => 'discussion_notification',
                        'tpl' => 'addons/discussion/notification.tpl', // this parameter is obsolete and is used for back compatibility
                        'company_id' => $order_info['company_id'],
                    ), 'C', $lang_code);
                }
            } elseif (!empty($discussion_settings[$object_name . '_notification_email']) || (!empty($discussion_settings[$object_name . '_notify_vendor']) && $discussion_settings[$object_name . '_notify_vendor'] == 'Y')) {
                $company_id = 0;
                if (fn_allowed_for('MULTIVENDOR')) {
                    if ($object_name == 'product') {
                        $company_id = db_get_field(
                            "SELECT company_id FROM ?:products WHERE product_id = ?i", $object['object_id']
                        );
                    } elseif ($object_name == 'page') {
                        $company_id = db_get_field(
                            "SELECT company_id FROM ?:pages WHERE page_id = ?i", $object['object_id']
                        );
                    } elseif ($object_name == 'company') {
                        $company_id = $object['object_id'];
                    }
                }
                $url = "discussion_manager.manage?object_type=$object[object_type]&post_id=$post_data[post_id]";
                if (!empty($discussion_settings[$object_name . '_notification_email'])) {
                    $lang_code = Registry::get('settings.Appearance.backend_default_language');
                    Mailer::sendMail(array(
                        'to' => $discussion_settings[$object_name . '_notification_email'],
                        'from' => 'company_site_administrator',
                        'data' => array(
                            'url' => fn_url($url, 'A', 'http', null, true),
                            'object_data' => $object_data,
                            'post_data' => $post_data,
                            'object_name' => $object_name,
                            'subject' => $fn_prepare_subject($discussion_object_types[$object['object_type']], $lang_code),
                        ),
                        'tpl' => 'addons/discussion/notification.tpl',
                        'company_id' => $company_id,
                    ), 'A', $lang_code);
                }

                // Send to vendor
                if (!empty($company_id) && !empty($discussion_settings[$object_name . '_notify_vendor']) && $discussion_settings[$object_name . '_notify_vendor'] == 'Y') {

                    $lang_code = fn_get_company_language($company_id);
                    $url = ($object_name == 'company' ? 'companie' : $object_name) . "s.update?" . http_build_query(array(
                        $object_name . '_id' => $object['object_id'],
                        'selected_section' => 'discussion',
                    ));
                    Mailer::sendMail(array(
                        'to' => 'company_site_administrator',
                        'from' => 'default_company_site_administrator',
                        'data' => array(
                            'url' => fn_url($url, 'V', 'http', null, true),
                            'object_data' => $object_data,
                            'post_data' => $post_data,
                            'object_name' => $object_name,
                            'subject' => $fn_prepare_subject($discussion_object_types[$object['object_type']], $lang_code),
                        ),
                        'tpl' => 'addons/discussion/notification.tpl',
                        'company_id' => $company_id,
                    ), 'A', $lang_code);
                }
            }
        }
    }
    fn_set_hook('add_discussion_post_post', $post_data, $send_notifications);
     
    return $post_data['post_id'];
}

function fn_cp_pr_video_attach_image($type, $path, $object_type, $object_id, $icon, $detailed, $lang_code = CART_LANGUAGE) {
    if (empty($path)) {
        return false;
    }
    if ($icon) {
        $_REQUEST["type_preview_image_icon"] = array($type);
        $_REQUEST["file_preview_image_icon"] = array($path);
    }
    if ($detailed) {
        $_REQUEST["type_preview_image_detailed"] = array($type);
        $_REQUEST["file_preview_image_detailed"] = array($path);
    }
    $_REQUEST['preview_image_data'] = array(
        array(
            'type' => 'M',
            'image_alt' => '',
            'detailed_alt' => '',
            'position' => 0,
        )
    );
    fn_attach_image_pairs('preview', $object_type, $object_id, $lang_code);
    return true;
}

//CLONE DISCUSSION FUNCTIONS FOR MULTIVERSION
function fn_cp_power_reviews_check_thread_permissions($thread, $auth) {
    if (is_numeric($thread)) {
        $thread = db_get_row("SELECT * FROM ?:discussion WHERE thread_id = ?i", $thread);
    } elseif ((empty($thread['object_type']) || empty($thread['object_id'])) && !empty($thread['thread_id'])) {
        $thread = db_get_row("SELECT * FROM ?:discussion WHERE thread_id = ?i", $thread['thread_id']);
    }
    if (!$thread) {
        return false;
    }
    return true;
}
function fn_cp_power_reviews_get_object($params) {
    $condition = [];
    if (!empty($params['thread_id'])) {
        $condition[] = db_quote("thread_id = ?i", $params['thread_id']);
    }
    if (!empty($params['object_id']) && !empty($params['object_type'])) {
        $condition[] = db_quote("object_id = ?i", $params['object_id']);
        $condition[] = db_quote("object_type = ?s", $params['object_type']);
    }
    if (!$condition) {
        return [];
    }
    return db_get_row(
        "SELECT thread_id, object_type, object_id, type FROM ?:discussion WHERE " . implode(' AND ', $condition)
    );
}
//END
function fn_cp_power_reviews_get_total_post_avg_rate ($thread_ids, $get_total = false) {
    if (!empty($thread_ids)) {
        if (!is_array($thread_ids)) {
            $thread_ids = (array) $thread_ids;
        }
        $each_join = $each_condition = '';
        $total_posts = 0;
        if (fn_allowed_for('MULTIVENDOR') && AREA == 'C' && Registry::get('addons.cp_power_reviews.split_storefronts') == 'Y') {
            $store_id = Tygh::$app['storefront']->storefront_id;
            if (!empty($store_id)) {
                $each_join .= " LEFT JOIN ?:cp_pr_reviews_storefronts ON ?:cp_pr_reviews_storefronts.post_id = b.post_id";
                $each_condition .= db_quote(" AND (?:cp_pr_reviews_storefronts.storefront_id = ?i OR ?:cp_pr_reviews_storefronts.storefront_id IS NULL)", $store_id);
            }
        }
        
        $rating = db_get_field("SELECT AVG(a.cp_sred_rate) as val FROM ?:discussion_rating as a 
            LEFT JOIN ?:discussion_posts as b ON a.post_id = b.post_id ?p
            WHERE a.thread_id IN (?n) AND b.status = 'A' AND a.rating_value > ?i AND b.cp_pr_user_delete = ?s ?p", $each_join, $thread_ids, 0, 'N', $each_condition);
        $rating = number_format($rating, 1);
        if (!empty($get_total)) {
            $total_posts = db_get_field("SELECT COUNT(b.post_id) as val FROM ?:discussion_posts as b ?p
            WHERE b.thread_id IN (?n) AND b.status = ?s AND b.cp_pr_user_delete = ?s ?p", $each_join, $thread_ids, 'A', 'N', $each_condition);
        }
        $avg_rate = intval($rating) == $rating ? intval($rating) : $rating;
        return [$avg_rate, $total_posts];
    }
    return false;
}
function fn_cp_power_reviews_sort_reviews_by_pos($a, $b) {
    if ($a['attr_pos'] == $b['attr_pos']) {
        return 0;
    }
    return ($a['attr_pos'] < $b['attr_pos']) ? -1 : 1;
}
//get product attributes
function fn_cp_power_reviews_get_discussion_post ($object_id, $object_type, $get_posts, $params, &$discussion) {
    if ((!empty($object_id) && !empty($object_type) && $object_type == 'P' && !empty($discussion['thread_id'])) || (!empty($object_type) && in_array($object_type, ['E','M','C','A','B']) && !empty($discussion['thread_id']))) {
        $reviews_settings = Registry::get('addons.cp_power_reviews');
        if (fn_allowed_for('MULTIVENDOR') && AREA == 'C' && $reviews_settings['split_storefronts'] == 'Y') {
            $need_mve_sores = true;
        } else {
            $need_mve_sores = false;
        }
        $exist_post_with_attr = false;
        $store_join = $store_condition = '';
        if (fn_allowed_for('ULTIMATE')) {
            $comp_id = Registry::get('runtime.company_id');
            if (!empty($comp_id)) {
                $condition = db_quote(" AND ?:cp_power_ext_reviews.company_id = ?i", $comp_id);
            } else {
                $condition = '';
            }
        } else {
            $condition = '';
            if (!empty($need_mve_sores)) {
                $store_id = Tygh::$app['storefront']->storefront_id;
                if (!empty($store_id)) {
                    $store_join .= " LEFT JOIN ?:cp_pr_reviews_storefronts ON ?:cp_pr_reviews_storefronts.post_id = ?:discussion_posts.post_id";
                    $store_condition .= db_quote(" AND (?:cp_pr_reviews_storefronts.storefront_id = ?i OR ?:cp_pr_reviews_storefronts.storefront_id IS NULL)", $store_id);
                }
            }
        }
        $skip_next_get_posts = false;
        if (!empty($object_id) && !empty($object_type) && $object_type == 'P' && !empty($discussion['thread_id'])) {
            if (AREA == 'C' && defined('CP_PR_VARIATIONS_TYPE') && in_array(CP_PR_VARIATIONS_TYPE, ['Y','S','NS'])) {
                    $get_fields = 'pvgp.group_id, pvgf.purpose, pfv.feature_id, pfv.variant_id, pfv.value, pfv.value_int, pfvd.variant, pfd.description';
                    $var_feats_data = db_get_array("SELECT $get_fields FROM ?:product_variation_group_products as pvgp 
                        LEFT JOIN ?:product_variation_group_features as pvgf ON pvgf.group_id = pvgp.group_id 
                        LEFT JOIN ?:product_features_descriptions pfd ON pfd.feature_id = pvgf.feature_id AND pfd.lang_code = ?s
                        LEFT JOIN ?:product_features_values as pfv ON pfv.feature_id = pvgf.feature_id AND pfv.product_id = ?i AND pfv.lang_code = ?s
                        LEFT JOIN ?:product_feature_variant_descriptions as pfvd ON pfvd.variant_id = pfv.variant_id AND pfvd.lang_code = ?s
                        WHERE pvgp.product_id = ?i", CART_LANGUAGE, $object_id, CART_LANGUAGE, CART_LANGUAGE, $object_id);
                    if (!empty($var_feats_data)) {
                        $extra_var_name = '';
                        foreach($var_feats_data as $d_fv) {
                            if (!empty($extra_var_name)) {
                                $extra_var_name .= '; ';
                            }
                            $extra_var_name .= $d_fv['description'] . ': ' . $d_fv['variant'];
                        }
                        $discussion['extra_var_name'] = $extra_var_name;
                    }
            }
            $parent_ids_array = $all_thread_ids = [];
            if (AREA == 'C' && !empty($params['from_prod_tab']) && !empty($params['product_id'])) {
                
                $parent_product_ids = fn_cp_pr_get_all_group_vars_for_pid($params['product_id'], false, true);
                if (!empty($parent_product_ids)) {
                    $skip_next_get_posts = true;
                    $parent_ids_array = explode(',',$parent_product_ids);
                    $all_thread_ids = db_get_fields("SELECT thread_id FROM ?:discussion WHERE object_id IN (?n) AND object_type = ?s AND type != ?s", $parent_ids_array, 'P', 'D');
                    $post_params = $params;
                    list($all_posts, $all_post_search) = fn_cp_pr_get_posts_for_diff_variations($params, $parent_product_ids);
                    
                    $discussion['search'] = $all_post_search;
                    $discussion['posts'] = $all_posts;
                } else {
                    unset($params['from_prod_tab']);
                    $parent_ids_array[] = $object_id;
                    $all_thread_ids[] = $discussion['thread_id'];
                }
            } elseif (defined('CP_PR_VARIATIONS_TYPE') && in_array(CP_PR_VARIATIONS_TYPE, ['Y','S','NS'])) {
                $parent_ids_array[] = $object_id;
                $all_thread_ids[] = $discussion['thread_id'];
                $var_group_id = db_get_field("SELECT parent_product_id FROM ?:product_variation_group_products WHERE product_id = ?i", $object_id);
                if (!empty($var_group_id)) {
                    $parent_ids_array[] = $var_group_id;
                }
                if (CP_PR_VARIATIONS_TYPE == 'NS' && AREA == 'C') {
                    if (empty($var_group_id)) {
                        $more_ids = db_get_fields("SELECT product_id FROM ?:product_variation_group_products WHERE parent_product_id = ?i", $object_id);
                        if (!empty($more_ids)) {
                            $parent_ids_array = array_merge($parent_ids_array, $more_ids);
                            $parent_ids_array = array_unique($parent_ids_array);
                        }
                    }
                    $all_thread_ids = db_get_fields("SELECT thread_id FROM ?:discussion WHERE object_id IN (?n) AND object_type = ?s AND type != ?s", $parent_ids_array, 'P', 'D');
                    
                    list($all_posts, $all_post_search) = fn_cp_pr_get_posts_for_diff_variations($params, implode(',',$parent_ids_array));
                        
                    $discussion['search'] = $all_post_search;
                    $discussion['posts'] = $all_posts;
                }
            } else {
                if (Registry::get('addons.product_variations.status') == 'A') {
                    $vars_from_main = db_get_fields("SELECT ?:discussion.thread_id FROM ?:product_variation_group_products as pvgp 
                        LEFT JOIN ?:discussion ON ?:discussion.object_id = pvgp.product_id
                        WHERE ?:discussion.object_type = ?s AND (pvgp.product_id = ?i OR pvgp.parent_product_id = ?i)", 'P', $object_id, $object_id
                    );
                    if (!empty($vars_from_main)) {
                        $all_thread_ids = $vars_from_main;
                        $parent_product_ids = $parent_ids_array = db_get_fields("SELECT object_id FROM ?:discussion WHERE thread_id IN (?n)", $vars_from_main);
                        $parent_product_ids[] = $object_id;
                        list($all_posts, $all_post_search) = fn_cp_pr_get_posts_for_diff_variations($params, implode(',',$parent_product_ids));
                        $discussion['search'] = $all_post_search;
                        $discussion['posts'] = $all_posts;
                    }
                }
                $parent_ids_array[] = $object_id;
                $all_thread_ids[] = $discussion['thread_id'];
                $all_thread_ids = array_unique($all_thread_ids);
            }
            
            $discussion['cp_all_prod_attrs'] = db_get_hash_array("SELECT ?:cp_power_rev_products.*, ?:cp_pow_attr_descr.cp_attr_name, ?:cp_power_ext_reviews.* FROM ?:cp_power_rev_products 
                LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_power_rev_products.cp_attr_id 
                LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_power_rev_products.cp_attr_id 
                WHERE ?:cp_power_rev_products.product_id IN (?n) AND ?:cp_pow_attr_descr.lang_code = ?s AND ?:cp_power_ext_reviews.status = ?s ?p ORDER BY ?:cp_power_rev_products.attr_pos", 'cp_attr_id', $parent_ids_array, DESCR_SL, 'A', $condition);
            $prod_main_cat = db_get_fields("SELECT category_id FROM ?:products_categories WHERE product_id = ?i AND link_type = ?s", $parent_ids_array, 'M');
            if (!empty($prod_main_cat)) {
                if (!empty($discussion['cp_all_prod_attrs'])) {
                    $already_get_ids = array_keys($discussion['cp_all_prod_attrs']);
                } else {
                    $already_get_ids = [];
                }
                if (fn_allowed_for('MULTIVENDOR')) {
                    $comp_id = db_get_field("SELECT ?:products.company_id FROM ?:products
                        LEFT JOIN ?:discussion ON ?:discussion.object_id = ?:products.product_id WHERE ?:discussion.thread_id = ?i", $discussion['thread_id']);
                    if (!empty($comp_id)) {
                        $comps = array($comp_id, 0);
                        $condition = db_quote(" AND ?:cp_power_ext_reviews.company_id IN (?n)", $comps);
                    } else {
                        $condition = '';
                    }
                }
                
                $cat_prod_attr = db_get_hash_array("SELECT ?:cp_power_rev_cats.*, ?:cp_pow_attr_descr.cp_attr_name, ?:cp_power_ext_reviews.* FROM ?:cp_power_rev_cats 
                    LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_power_rev_cats.cp_attr_id 
                    LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_power_rev_cats.cp_attr_id 
                    WHERE ?:cp_power_rev_cats.category_id IN (?n) AND ?:cp_pow_attr_descr.lang_code = ?s AND ?:cp_power_ext_reviews.status = ?s AND ?:cp_power_ext_reviews.cp_attr_id NOT IN (?n) ?p 
                    ORDER BY ?:cp_power_rev_cats.attr_pos", 'cp_attr_id', $prod_main_cat, DESCR_SL, 'A', $already_get_ids, $condition);
            }
            if (!empty($cat_prod_attr)) {
                if (!empty($discussion['cp_all_prod_attrs'])) {
                    $discussion['cp_all_prod_attrs'] = $cat_prod_attr + $discussion['cp_all_prod_attrs'];
                    uasort($discussion['cp_all_prod_attrs'], "fn_cp_power_reviews_sort_reviews_by_pos");
                } else {
                    $discussion['cp_all_prod_attrs'] = $cat_prod_attr;
                }
            }
            if (!empty($discussion['cp_all_prod_attrs'])) {
                foreach($discussion['cp_all_prod_attrs'] as $attr_id => &$attr_data) {
                    $check_status_posts  = db_get_field("SELECT ?:cp_pow_attr_ratings.post_id FROM ?:cp_pow_attr_ratings 
                        LEFT JOIN ?:discussion_posts ON ?:discussion_posts.post_id = ?:cp_pow_attr_ratings.post_id ?p
                        WHERE ?:cp_pow_attr_ratings.cp_attr_id = ?i AND ?:cp_pow_attr_ratings.post_status = ?s AND ?:discussion_posts.thread_id IN (?n) ?p", $store_join, $attr_data['cp_attr_id'], 'A', $all_thread_ids, $store_condition);
                    if (!empty($check_status_posts)) {
                        $attr_data['cp_show_common'] = 'A';
                        $attr_data['atr_aver_rate'] = round(db_get_field("SELECT AVG(?:cp_pow_attr_ratings.rating) FROM ?:cp_pow_attr_ratings 
                            LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_pow_attr_ratings.cp_attr_id
                            LEFT JOIN ?:discussion_posts ON ?:discussion_posts.post_id = ?:cp_pow_attr_ratings.post_id ?p
                            WHERE ?:cp_pow_attr_ratings.cp_attr_id = ?i AND ?:cp_pow_attr_ratings.rating > ?i AND ?:cp_power_ext_reviews.status = ?s 
                                AND ?:discussion_posts.thread_id IN (?n) AND ?:discussion_posts.status = ?s AND ?:discussion_posts.cp_pr_user_delete = ?s ?p ?p", $store_join, $attr_data['cp_attr_id'], 0, 'A', $all_thread_ids, 'A', 'N', $condition, $store_condition), 1);
                        $exist_post_with_attr = true;
                    } else {
                        $attr_data['cp_show_common'] = 'D';
                    }
                }
            }
//for testimonials
        } elseif (!empty($object_type) && in_array($object_type, array('E','M')) && !empty($discussion['thread_id'])) {
            $discussion['cp_all_prod_attrs'] = db_get_hash_array("SELECT ?:cp_pow_attr_descr.cp_attr_name, ?:cp_power_ext_reviews.* FROM ?:cp_power_ext_reviews 
                LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_power_ext_reviews.cp_attr_id 
                WHERE ?:cp_pow_attr_descr.lang_code = ?s AND ?:cp_power_ext_reviews.status = ?s AND ?:cp_power_ext_reviews.object_type = ?s ?p", 'cp_attr_id', DESCR_SL, 'A', $object_type, $condition);
            if (!empty($discussion['cp_all_prod_attrs'])) {
                foreach($discussion['cp_all_prod_attrs'] as $attr_id => $attr_data) {
                    $check_status_posts  = db_get_field("SELECT ?:cp_pow_attr_ratings.post_id FROM ?:cp_pow_attr_ratings 
                        LEFT JOIN ?:discussion_posts ON ?:discussion_posts.post_id = ?:cp_pow_attr_ratings.post_id
                        WHERE ?:cp_pow_attr_ratings.cp_attr_id = ?i AND ?:cp_pow_attr_ratings.post_status = ?s 
                            AND ?:discussion_posts.thread_id = ?i AND ?:discussion_posts.cp_pr_user_delete = ?s", $attr_data['cp_attr_id'], 'A', $discussion['thread_id'], 'N');
                    if (!empty($check_status_posts)) {
                        $discussion['cp_all_prod_attrs'][$attr_id]['cp_show_common'] = 'A';
                        $discussion['cp_all_prod_attrs'][$attr_id]['atr_aver_rate'] = round(db_get_field("SELECT AVG(?:cp_pow_attr_ratings.rating) FROM ?:cp_pow_attr_ratings 
                            LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_pow_attr_ratings.cp_attr_id
                            LEFT JOIN ?:discussion_posts ON ?:discussion_posts.post_id = ?:cp_pow_attr_ratings.post_id
                            WHERE ?:discussion_posts.thread_id = ?i AND ?:cp_pow_attr_ratings.cp_attr_id = ?i AND ?:cp_pow_attr_ratings.rating > ?i AND ?:cp_power_ext_reviews.status = ?s 
                                AND ?:cp_power_ext_reviews.object_type = ?s AND ?:discussion_posts.status = ?s 
                                AND ?:discussion_posts.cp_pr_user_delete = ?s ?p", $discussion['thread_id'], $attr_data['cp_attr_id'], 0, 'A', $object_type, 'A', 'N', $condition), 1);
                        $exist_post_with_attr = true;
                    } else {
                        $discussion['cp_all_prod_attrs'][$attr_id]['cp_show_common'] = 'D';
                    }
                }
            }
        }
//get ratings by each start
        if (AREA == 'C') {
            if (empty($all_thread_ids)) {
                $all_thread_ids = [$discussion['thread_id']];
            }
            $stars = array_reverse(fn_cp_pr_get_discussion_ratings_revers(), true);
            $discussion['cp_pr_by_each_star'] = [];
            $discussion['cp_pr_total_rated'] = 0;
            $each_condition = $each_join = '';
            if (!empty($need_mve_sores)) {
                $store_id = Tygh::$app['storefront']->storefront_id;
                if (!empty($store_id)) {
                    $each_join .= " LEFT JOIN ?:cp_pr_reviews_storefronts ON ?:cp_pr_reviews_storefronts.post_id = ?:discussion_posts.post_id";
                    $each_condition .= db_quote(" AND (?:cp_pr_reviews_storefronts.storefront_id = ?i OR ?:cp_pr_reviews_storefronts.storefront_id IS NULL)", $store_id);
                }
            }
            foreach($stars as $val => $txt) {
                $discussion['cp_pr_total_rated'] += $discussion['cp_pr_by_each_star'][$val] = db_get_field("SELECT COUNT(?:discussion_rating.rating_value) FROM ?:discussion_rating 
                            LEFT JOIN ?:discussion_posts ON ?:discussion_posts.post_id = ?:discussion_rating.post_id ?p
                            WHERE ?:discussion_rating.thread_id IN (?n) AND ?:discussion_rating.rating_value = ?i AND ?:discussion_posts.status = ?s 
                                AND ?:discussion_posts.cp_pr_user_delete = ?s ?p", $each_join, $all_thread_ids, $val, 'A', 'N', $each_condition);
            }
        }
        
        if (!empty($discussion['cp_all_prod_attrs'])) {
            $discussion['cp_all_prod_attrs'] = fn_cp_pr_get_view_type_txts($discussion['cp_all_prod_attrs'], DESCR_SL, $all_thread_ids);
            if (AREA == 'C') {
                $discussion['cp_total_posts_with_attr'] = db_get_field("SELECT COUNT(DISTINCT(?:cp_pow_attr_ratings.post_id)) FROM ?:cp_pow_attr_ratings
                    LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_pow_attr_ratings.cp_attr_id
                    LEFT JOIN ?:discussion_posts ON ?:discussion_posts.post_id = ?:cp_pow_attr_ratings.post_id ?p
                    WHERE ?:discussion_posts.thread_id IN (?n) AND ?:cp_pow_attr_ratings.rating > ?i AND ?:cp_power_ext_reviews.status = ?s 
                        AND ?:discussion_posts.status = ?s AND ?:discussion_posts.cp_pr_user_delete = ?s ?p", $each_join, $all_thread_ids, 0, 'A', 'A', 'N', $each_condition);
            }
        }
        
        if (empty($discussion) || (!empty($discussion) && !empty($discussion['type']) && $discussion['type'] != 'R' && $discussion['type'] != 'B')) {
            $get_av_rating = false;
        } else {
            $get_av_rating = true;
        }
        if (!empty($get_av_rating) && !empty($all_thread_ids)) {
            list($discussion['average_rating'],) = fn_cp_power_reviews_get_total_post_avg_rate($all_thread_ids);
        }
        if (AREA == 'C') {
            if (!empty($exist_post_with_attr)) {
                $discussion['cp_show_sred_block'] = true;
            }
            if (!empty($params['cp_sort_by'])) {
                $discussion['cp_sort_by'] = $params['cp_sort_by'];
            }
            $discussion['cp_login_url'] = fn_url('auth.login_form','C');
            
            if (!empty($object_type) && in_array($object_type, ['P','A','B','M']) && !empty($all_thread_ids)) {
                $cp_recom_total = db_get_field("SELECT COUNT(thread_id) FROM ?:cp_pow_recomends WHERE thread_id IN (?n)", $all_thread_ids);
                if (empty($cp_recom_total)) {
                    $cp_recom_total = 0;
                }
                $positiv = db_get_field("SELECT COUNT(thread_id) FROM ?:cp_pow_recomends WHERE thread_id IN (?n) AND type = ?s", $all_thread_ids, 'U');
                if (empty($positiv)) {
                    $positiv = 0;
                }
                if (!empty($positiv) && !empty($cp_recom_total)) {
                    $cp_recom_proc = round(100*($positiv/$cp_recom_total));
                }
                $discussion['cp_recom_total'] = $cp_recom_total;
                $discussion['cp_recom_proc'] = !empty($cp_recom_proc) ? $cp_recom_proc : 0;
                $discussion['cp_recom_positiv'] = $positiv;
            }
            if (!empty($discussion['posts'])) {
                if (!empty($object_type)) {
                    if ($object_type == 'P') {
                    
                        $post_limit = $reviews_settings['barrier_for_positive'];
                        $show_most_help_block = $reviews_settings['show_most_help_block'];
                        $show_image_in_post = $reviews_settings['show_image_in_post'];
                        $allow_most_bl = $reviews_settings['allow_most_bl'];
                        $show_slider = $reviews_settings['show_image_slider'];
                        $slider_limit = $reviews_settings['image_slider_limit'];
                        $add_videos = $reviews_settings['include_videos'];
                        
                    } elseif ($object_type == 'C') {
                    
                        $post_limit = $reviews_settings['barrier_for_positive_cat'];
                        $show_most_help_block = $reviews_settings['show_most_help_block_cat'];
                        $show_image_in_post = $reviews_settings['show_image_in_post_cat'];
                        $allow_most_bl = $reviews_settings['allow_most_bl_cat'];
                        $show_slider = $reviews_settings['show_image_slider_cat'];
                        $slider_limit = $reviews_settings['image_slider_limit_cat'];
                        $add_videos = $reviews_settings['include_videos_cat'];
                        
                    } elseif ($object_type == 'A' || $object_type == 'B') {
                    
                        $post_limit = $reviews_settings['barrier_for_positive_page'];
                        $show_most_help_block = $reviews_settings['show_most_help_block_page'];
                        $show_image_in_post = $reviews_settings['show_image_in_post_page'];
                        $allow_most_bl = $reviews_settings['allow_most_bl_page'];
                        $show_slider = $reviews_settings['show_image_slider_page'];
                        $slider_limit = $reviews_settings['image_slider_limit_page'];
                        $add_videos = $reviews_settings['include_videos_page'];
                        
                    } elseif ($object_type == 'M') {
                    
                        $post_limit = $reviews_settings['barrier_for_positive_vend'];
                        $show_most_help_block = $reviews_settings['show_most_help_block_vend'];
                        $show_image_in_post = $reviews_settings['show_image_in_post_vend'];
                        $allow_most_bl = $reviews_settings['allow_most_bl_vend'];
                        $show_slider = $reviews_settings['show_image_slider_vend'];
                        $slider_limit = $reviews_settings['image_slider_limit_vend'];
                        $add_videos = $reviews_settings['include_videos_vend'];
                        
                    } elseif ($object_type == 'E') {
                    
                        $post_limit = 9999;
                        $show_most_help_block = 'N';
                        $show_image_in_post = $reviews_settings['show_image_in_post_test'];
                        $allow_most_bl = 'N';
                        $show_slider = $reviews_settings['show_image_slider_test'];
                        $slider_limit = $reviews_settings['image_slider_limit_test'];
                        $add_videos = $reviews_settings['include_videos_test'];
                    }
                }
                $all_pos_posts = $all_neg_posts = $most_h_post = $most_u_post = 0;
                $max_pos_rate = $min_neg_rate = 0;
                $m_fields = array (
                    '?:discussion_posts.*',
                    '?:discussion_messages.message',
                    '?:discussion_messages.cp_pr_title',
                    '?:discussion_messages.cp_pr_advantages',
                    '?:discussion_messages.cp_pr_disadvantages',
                    '?:discussion_rating.rating_value',
                    '?:discussion.*'
                );
                $m_join = " INNER JOIN ?:discussion ON ?:discussion.thread_id = ?:discussion_posts.thread_id";
                $m_join .= " INNER JOIN ?:discussion_messages ON ?:discussion_messages.post_id = ?:discussion_posts.post_id";
                $m_join .= " INNER JOIN ?:discussion_rating ON ?:discussion_rating.post_id = ?:discussion_posts.post_id";
                
                $m_condition = '';
                if (fn_allowed_for('MULTIVENDOR')) {
                    $store_id = Tygh::$app['storefront']->storefront_id;
                    $all_comp_ids = db_get_fields("SELECT company_id FROM ?:storefronts_companies WHERE storefront_id = ?i", $store_id);
                    if (!empty($all_comp_ids)) {
                        $m_condition .= db_quote(" AND ?:discussion.company_id IN (?n)", $all_comp_ids);
                    }
                    if (!empty($need_mve_sores)) {
                        if (!empty($store_id)) {
                            $m_join .= " LEFT JOIN ?:cp_pr_reviews_storefronts ON ?:cp_pr_reviews_storefronts.post_id = ?:discussion_posts.post_id";
                            $m_condition .= db_quote(" AND (?:cp_pr_reviews_storefronts.storefront_id = ?i OR ?:cp_pr_reviews_storefronts.storefront_id IS NULL)", $store_id);
                        }
                    }
                } else {
                    $m_condition = fn_get_discussion_company_condition('?:discussion.company_id');
                }
                $m_condition .= db_quote(" AND ?:discussion.object_type = ?s", $object_type);
                $m_condition .= db_quote(" AND ?:discussion_posts.thread_id IN (?n) AND ?:discussion_posts.status = ?s AND ?:discussion_posts.cp_pr_user_delete = ?s", $all_thread_ids, 'A', 'N');
                
                
                if (!empty($show_most_help_block) && $show_most_help_block == 'Y') {
                    $most_h_posts = db_get_hash_array("SELECT " . implode(',', $m_fields) . " FROM ?:discussion_posts $m_join WHERE 1 $m_condition AND ?:discussion_posts.cp_pos_post > ?i ORDER BY ?:discussion_posts.cp_pos_post desc,?:discussion_posts.timestamp desc  LIMIT 2", 'post_id', 0);
                    if (!empty($most_h_posts)) {
                        if ($show_image_in_post == 'Y') {
                            fn_cp_power_reviews_get_more_post_data($most_h_posts, $object_type, false, true);
                        } else {
                            fn_cp_power_reviews_get_more_post_data($most_h_posts, $object_type, false, false);
                        }
                        $discussion['cp_top_help'] = [];
                        $discussion['cp_top_help'] = $most_h_posts;
                    }
                }
                if ($allow_most_bl == 'Y') {
                    $most_pos_post = db_get_hash_array("SELECT " . implode(',', $m_fields) . " FROM ?:discussion_posts $m_join WHERE 1 $m_condition AND ?:discussion_rating.rating_value >= ?i ORDER BY ?:discussion_rating.rating_value desc LIMIT 1", 'post_id', $post_limit);
                    if (!empty($most_pos_post)) {
                        if ($show_image_in_post == 'Y') {
                            fn_cp_power_reviews_get_more_post_data($most_pos_post, $object_type, false, true);
                        } else {
                            fn_cp_power_reviews_get_more_post_data($most_pos_post, $object_type, false, false);
                        }
                        $discussion['most_h_post'] = [];
                        $discussion['most_h_post'] = reset($most_pos_post);
                        $discussion['all_positive_posts'] = db_get_field("SELECT COUNT(?:discussion_posts.post_id) FROM ?:discussion_posts $m_join WHERE 1 $m_condition AND ?:discussion_rating.rating_value >= ?i", $post_limit);
                    }
                    $most_neg_post = db_get_hash_array("SELECT " . implode(',', $m_fields) . " FROM ?:discussion_posts $m_join WHERE 1 $m_condition AND ?:discussion_rating.rating_value <= ?i AND ?:discussion_rating.rating_value > ?i ORDER BY ?:discussion_rating.rating_value asc LIMIT 1", 'post_id',  $post_limit, 0);
                    if (!empty($most_neg_post)) {
                        if ($show_image_in_post == 'Y') {
                            fn_cp_power_reviews_get_more_post_data($most_neg_post, $object_type, false, true);
                        } else {
                            fn_cp_power_reviews_get_more_post_data($most_neg_post, $object_type, false, false);
                        }
                        $discussion['most_u_post'] = [];
                        $discussion['most_u_post'] = reset($most_neg_post);
                        $discussion['all_critical_posts'] = db_get_field("SELECT COUNT(?:discussion_posts.post_id) FROM ?:discussion_posts $m_join WHERE 1 $m_condition AND ?:discussion_rating.rating_value < ?i AND ?:discussion_rating.rating_value > ?i", $post_limit, 0);
                    }
                }
                if (!empty($show_slider) && $show_slider == 'Y' && !empty($all_thread_ids)) {
                    $discussion['cp_pr_slider'] = [];
                    $limit = !empty($slider_limit) ? $slider_limit : 0;
                    if (!empty($limit)) {
                        $limit = ' LIMIT ' . $limit;
                    } else {
                        $limit = '';
                    }
                    $img_join = $img_condition = '';
                    if (!empty($need_mve_sores)) {
                        $store_id = Tygh::$app['storefront']->storefront_id;
                        if (!empty($store_id)) {
                            $img_join .= " LEFT JOIN ?:cp_pr_reviews_storefronts ON ?:cp_pr_reviews_storefronts.post_id = ?:discussion_posts.post_id";
                            $img_condition .= db_quote(" AND (?:cp_pr_reviews_storefronts.storefront_id = ?i OR ?:cp_pr_reviews_storefronts.storefront_id IS NULL)", $store_id);
                        }
                    }
                    
                    $img_descr_join = db_quote(" LEFT JOIN ?:common_descriptions ON ?:common_descriptions.object_id = ?:images.image_id AND ?:common_descriptions.object_holder = ?s AND ?:common_descriptions.lang_code = ?s", 'images', CART_LANGUAGE);
                    
                    $all_posts_images = db_get_hash_array("
                        SELECT ?:images_links.*, ?:images.*, ?:common_descriptions.description AS alt FROM ?:images_links 
                        LEFT JOIN ?:images ON ?:images.image_id = ?:images_links.detailed_id
                        LEFT JOIN ?:cp_review_images ON ?:cp_review_images.post_image_id = ?:images_links.pair_id
                        LEFT JOIN ?:discussion_posts ON ?:discussion_posts.post_id = ?:images_links.object_id ?p ?p
                        WHERE ?:discussion_posts.thread_id IN (?n) AND ?:discussion_posts.status = ?s AND ?:cp_review_images.status = ?s AND ?:discussion_posts.cp_pr_user_delete = ?s ?p $limit", 'pair_id', $img_join, $img_descr_join, $all_thread_ids, 'A', 'A', 'N', $img_condition
                    );
                    if (!empty($all_posts_images)) {
                        foreach($all_posts_images as &$pair_data) {
                            $temp_pair = $pair_data;
                            $detailed = fn_attach_absolute_image_paths($temp_pair, 'detailed');
                            $pair_data['detailed'] = [
                                'object_id'         => $pair_data['object_id'],
                                'object_type'       => $pair_data['object_type'],
                                'type'              => $pair_data['type'],
                                'image_path'        => $detailed['image_path'],
                                'alt'               => !empty($detailed['alt']) ? $detailed['alt'] : '',
                                'image_x'           => $detailed['image_x'],
                                'image_y'           => $detailed['image_y'],
                                'http_image_path'   => $detailed['http_image_path'],
                                'https_image_path'  => $detailed['https_image_path'],
                                'absolute_path'     => $detailed['absolute_path'],
                                'relative_path'     => $detailed['relative_path']
                            ];
                        }
                        $discussion['cp_pr_slider']['images'] = $all_posts_images;
                    }
                    if (!empty($add_videos) && $add_videos == 'Y') {
                        $videos = db_get_array("
                            SELECT ?:cp_pr_video_links.* FROM ?:cp_pr_video_links 
                            LEFT JOIN ?:discussion_posts ON ?:discussion_posts.post_id = ?:cp_pr_video_links.post_id ?p
                            WHERE ?:discussion_posts.thread_id IN (?n) AND ?:cp_pr_video_links.status = ?s AND ?:discussion_posts.status = ?s AND ?:discussion_posts.cp_pr_user_delete = ?s ?p", $img_join, $all_thread_ids, 'A', 'A', 'N', $img_condition
                        );
                        if (!empty($videos)) {
                            foreach($videos as &$v_data) {
                                $v_data['preview'] = fn_get_image_pairs($v_data['video_id'], 'cp_pr_video_preview', 'M', true, true, CART_LANGUAGE);
                                if (empty($v_data['preview'])) { // add default preview img
                                    $v_data['preview_def'] = Storage::instance('images')->getUrl('cp_pr_youtube.jpg');
                                }
                            }
                            $discussion['cp_pr_slider']['videos'] = $videos;
                        }
                    }
                }
            }
        }
    }
    if (!empty($params['cp_fill_type']) || !empty($params['cp_sort_by'])) {
        if (!empty($params['product_id']) && empty($params['thread_id'])) {
            if (fn_allowed_for('ULTIMATE') && !empty($comp_id)) {
                $params['thread_id'] = db_get_field("SELECT thread_id FROM ?:discussion WHERE object_type = ?s AND object_id = ?i AND company_id = ?i", 'P', $params['product_id'], $comp_id);
            } else {
                $params['thread_id'] = db_get_field("SELECT thread_id FROM ?:discussion WHERE object_type = ?s AND object_id = ?i", 'P', $params['product_id']);
            }
        } elseif ($object_type == 'M' && empty($params['thread_id']) && !empty($params['company_id'])) {
            $params['thread_id'] = db_get_field("SELECT thread_id FROM ?:discussion WHERE object_type = ?s AND object_id = ?i", 'M', $params['company_id']);
        }
        if (empty($skip_next_get_posts)) {
            if (!empty($params['cp_sort_by'])) {
                list($discussion['posts'], ) = fn_get_discussion_posts($params, Registry::get('addons.discussion.product_posts_per_page'));
            } else {
                list($discussion['posts'], ) = fn_get_discussion_posts($params, $params['limit']);
            }
        }
        if (!empty($discussion['posts']) && !empty($params['truncate_text_length'])) {
            foreach($discussion['posts'] as $k => $v) {
                if (mb_strlen($v['message'], 'UTF-8') > $params['truncate_text_length']) {
                    $v['message'] = trim($v['message']);
                
                    $truncated = explode(' ', $v['message']);
                    
                    $discussion['posts'][$k]['message_last_words'] = [];
                    $kkk = 1;
                    while ($kkk <= $params['show_last_trunc_words']) {
                        $discussion['posts'][$k]['message_last_words'][] = $truncated[count($truncated)-$kkk];
                        $kkk ++;
                    }
                    
                    $discussion['posts'][$k]['message_last_words'] = array_reverse($discussion['posts'][$k]['message_last_words']);
                    $discussion['posts'][$k]['message_last_words'] = implode(' ', $discussion['posts'][$k]['message_last_words']);
                }   
            }
        }
    }
    if (!empty($discussion['thread_id']) && $object_type == 'P') {
        $discussion['cp_seo'] = db_get_row("SELECT * FROM ?:cp_pr_for_seo WHERE thread_id = ?i AND lang_code = ?s", $discussion['thread_id'],DESCR_SL);
        if (empty($discussion['cp_seo'])) {
            $disc_data = array(
                'thread_id' => $discussion['thread_id'],
                'object_type' => $object_type,
                'object_id' => $object_id
            );
            fn_cp_pr_update_discussion_seo($disc_data);
        }
        $discussion['cp_seo'] = db_get_row("SELECT * FROM ?:cp_pr_for_seo WHERE thread_id = ?i AND lang_code = ?s", $discussion['thread_id'],DESCR_SL);
        if (Registry::get('addons.seo.status') == 'A') {
            $discussion['cp_seo']['seo_name'] = fn_seo_get_name(CP_PR_OBJECT_SEO_KEY, $discussion['thread_id'], '', null, DESCR_SL);
        }
    }
}

function fn_cp_power_reviews_get_discussion_posts_post($params, $items_per_page, &$posts) {
    if (!empty($posts) && !empty($params['thread_id'])) {
        if (!empty($params['product_id'])) {
            $object_type = 'P';
        } else {
            $object_type = db_get_field("SELECT object_type FROM ?:discussion WHERE thread_id = ?i", $params['thread_id']);
        }
        $cp_skip_img = $show_img = false;
        if (!empty($object_type) && $object_type == 'P') {
            $cp_type = 'P';
            if (!empty($params['cp_fill_type']) && !empty($params['skip_reviews_wtihout_img']) && $params['skip_reviews_wtihout_img'] == 'Y') {
                $cp_skip_img = true;
            }
            if (!empty($params['show_review_image']) && $params['show_review_image'] == 'Y') {
                $show_img = true;
            }
        } elseif (!empty($object_type) && $object_type == 'E') {
            $cp_type = 'E';
        } elseif (!empty($object_type) && $object_type == 'M') {
            $cp_type = 'M';
        } elseif (!empty($object_type) && $object_type == 'C') {
            $cp_type = 'C';
        } elseif (!empty($object_type) && $object_type == 'A') {
            $cp_type = 'A';
        } elseif (!empty($object_type) && $object_type == 'B') {
            $cp_type = 'B';
        }
        if (!empty($cp_type)) {
            fn_cp_power_reviews_get_more_post_data($posts, $cp_type, $cp_skip_img, $show_img);
        }
    }
    
}
function fn_cp_power_reviews_get_discussion_posts(&$params, $items_per_page, &$fields, &$join, &$condition, &$order_by, &$limit) {
    if (!empty($params['thread_id'])) {
        $obj_type = db_get_field("SELECT type FROM ?:discussion WHERE thread_id = ?i", $params['thread_id']);
    }
    $thread_data = db_get_row(
        "SELECT thread_id, type, object_type, object_id FROM ?:discussion WHERE thread_id = ?i ?p",
        $params['thread_id'], fn_get_discussion_company_condition('?:discussion.company_id')
    );
    if (AREA == 'C') {
        $condition .= db_quote(" AND ?:discussion_posts.cp_pr_user_delete = ?s", 'N');
        
        if (fn_allowed_for('MULTIVENDOR') && Registry::get('addons.cp_power_reviews.split_storefronts') == 'Y') {
            $store_id = Tygh::$app['storefront']->storefront_id;
            if (!empty($store_id)) {
                $join .= " LEFT JOIN ?:cp_pr_reviews_storefronts ON ?:cp_pr_reviews_storefronts.post_id = ?:discussion_posts.post_id";
                $condition .= db_quote(" AND (?:cp_pr_reviews_storefronts.storefront_id = ?i OR ?:cp_pr_reviews_storefronts.storefront_id IS NULL)", $store_id);
            }
        }
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:discussion_posts $join WHERE $condition");
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
        
    }
    if ($thread_data['type'] == 'C' || $thread_data['type'] == 'B') {
        $fields .= ", ?:discussion_messages.cp_pr_title, ?:discussion_messages.cp_pr_advantages, ?:discussion_messages.cp_pr_disadvantages";
    }
    if (!empty($params['cp_sort_by'])) {
        if ($params['cp_sort_by'] == 'MH') {
            $order_by = '?:discussion_posts.cp_pos_post desc,?:discussion_posts.timestamp desc';
        } elseif ($params['cp_sort_by'] == 'HR' && ($obj_type == 'R' || $obj_type == 'B')) {
            $order_by = '?:discussion_rating.cp_sred_rate desc,?:discussion_posts.timestamp desc';
        } elseif ($params['cp_sort_by'] == 'LR' && ($obj_type == 'R' || $obj_type == 'B')) {
            $order_by = '?:discussion_rating.cp_sred_rate asc,?:discussion_posts.timestamp desc';
        } elseif ($params['cp_sort_by'] == 'NW') {
            $order_by = '?:discussion_posts.timestamp desc';
        } elseif ($params['cp_sort_by'] == 'OD') {
            $order_by = '?:discussion_posts.timestamp asc';
        }
        $params['avail_only'] = true;
        if (AREA == 'C') {
            $condition .= " AND ?:discussion_posts.status = 'A'";
        }
    }
    if (!empty($params['cp_filter_stars']) && $params['cp_filter_stars'] > 0) {
        $condition .= db_quote(" AND ?:discussion_rating.rating_value = ?i", $params['cp_filter_stars']);
    }
    if (!empty($params['thread_id']) && !empty($params['cp_pr_with_images']) && $params['cp_pr_with_images'] == 'Y') {
        $all_images_posts = db_get_fields("SELECT DISTINCT(?:cp_review_images.post_id) FROM ?:cp_review_images 
            LEFT JOIN ?:discussion_posts ON ?:discussion_posts.post_id = ?:cp_review_images.post_id
            WHERE ?:discussion_posts.thread_id = ?i", $params['thread_id']);
            
        if (!empty($all_images_posts)) {
            $condition .= db_quote(" AND ?:discussion_posts.post_id IN (?n)", $all_images_posts);
        }
    }
    if (!empty($params['cp_fill_type']) && !empty($params['thread_id'])) {
        if ($params['cp_fill_type'] == 'RND') {
            //$params['random'] = 'Y';
            $order_by = 'RAND()';
        } elseif ($params['cp_fill_type'] == 'TPR' && ($obj_type == 'R' || $obj_type == 'B')) {
            $order_by = '?:discussion_rating.cp_sred_rate desc';
        } elseif ($params['cp_fill_type'] == 'LWR' && ($obj_type == 'R' || $obj_type == 'B')) {
            $order_by = '?:discussion_rating.cp_sred_rate asc';
        } elseif ($params['cp_fill_type'] == 'MSH') {
            $order_by = '?:discussion_posts.cp_pos_post desc';
        } elseif ($params['cp_fill_type'] == 'NEW') {
            $order_by = '?:discussion_posts.timestamp desc';
        } elseif ($params['cp_fill_type'] == 'OLD') {
            $order_by = '?:discussion_posts.timestamp asc';
        } elseif ($params['cp_fill_type'] == 'NEW') {
            $order_by = '?:discussion_posts.timestamp desc';
        }
        if (!empty($params['cp_sort_attributes_only']) && $params['cp_sort_attributes_only'] == 'Y') {
            $join .= " LEFT JOIN ?:cp_pow_attr_ratings ON ?:cp_pow_attr_ratings.post_id = ?:discussion_posts.post_id ";
            $condition .= db_quote(" AND (SELECT COUNT(?:cp_pow_attr_ratings.rating) FROM ?:cp_pow_attr_ratings WHERE ?:cp_pow_attr_ratings.post_id = ?:discussion_posts.post_id) > ?i", 1);
        }
        if (!empty($params['min_rating_limit']) && ($obj_type == 'R' || $obj_type == 'B')) {
            $condition .= db_quote(" AND ?:discussion_rating.cp_sred_rate >= ?i", $params['min_rating_limit']);
        }
        if (!empty($params['max_rating_limit']) && ($obj_type == 'R' || $obj_type == 'B')) {
            $condition .= db_quote(" AND ?:discussion_rating.cp_sred_rate < ?i", $params['max_rating_limit']);
        }
        $params['avail_only'] = true;
        if (AREA == 'C') {
            $condition .= " AND ?:discussion_posts.status = 'A'";
        }
    } elseif (!empty($params['cp_post_kind']) && !empty($params['r_limit'])) {
        if ($params['cp_post_kind'] == 'pos') {
            $condition .= db_quote(" AND ?:discussion_rating.cp_sred_rate >= ?i", $params['r_limit']);
        } else {
            $condition .= db_quote(" AND ?:discussion_rating.cp_sred_rate < ?i", $params['r_limit']);
        }
    }
}

function fn_cp_power_reviews_update_product_post($product_data, $product_id, $lang_code, $create) {
    if (!empty($product_id)) {
        if (fn_allowed_for('ULTIMATE')) {
            $comp_id = Registry::get('runtime.company_id');
            if (!empty($comp_id)) {
                $condition = db_quote(" AND ?:cp_power_ext_reviews.company_id = ?i", $comp_id);
                $check_exist = db_get_fields("SELECT ?:cp_power_rev_products.cp_attr_id FROM ?:cp_power_rev_products 
                    LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_power_rev_products.cp_attr_id
                    WHERE ?:cp_power_rev_products.product_id = ?i AND ?:cp_power_ext_reviews.company_id = ?i", $product_id, $comp_id);
            } else {
                $check_exist = db_get_fields("SELECT cp_attr_id FROM ?:cp_power_rev_products WHERE product_id = ?i", $product_id);
                $condition = '';
            }
        } else {
            $check_exist = db_get_fields("SELECT cp_attr_id FROM ?:cp_power_rev_products WHERE product_id = ?i", $product_id);
            $condition = '';
        }
        if (!empty($check_exist)) {
            if (!empty($product_data['cp_rew_attr'])) {
                $glob_attr = $prod_attr = [];
                foreach($product_data['cp_rew_attr'] as $key => $attr_data) {
                    if (!empty($attr_data['cp_attr_name'])) {
                        $trim_attr_name = trim($attr_data['cp_attr_name']);
                    } else {
                        $trim_attr_name = '';
                    }
                    if (!empty($attr_data['cp_attr_id']) && !in_array($attr_data['cp_attr_id'], $check_exist) && $attr_data['object_type'] == 'G') {
                        $n_data = array(
                            'cp_attr_id' => $attr_data['cp_attr_id'],
                            'attr_pos' => $attr_data['attr_pos'],
                            'product_id' => $product_id
                        );
                        $glob_attr[] = $attr_data['cp_attr_id'];
                        db_query("REPLACE INTO ?:cp_power_rev_products ?e", $n_data);
                    } elseif (!empty($attr_data['cp_attr_id']) && in_array($attr_data['cp_attr_id'], $check_exist) ) {
                        if ($attr_data['object_type'] == 'P') {
                            $data = array(
                                'cp_attr_id' => $attr_data['cp_attr_id'],
                                'object_id' => $product_id,
                                'attr_pos' => $attr_data['attr_pos'],
                                'object_type' => $attr_data['object_type'],
                                'status' => $attr_data['status'],
                                'cp_attr_name' => trim($attr_data['cp_attr_name']),
                                'view_type' => isset($attr_data['view_type']) ? $attr_data['view_type'] : 'D',
                                'required' => isset($attr_data['required']) ? $attr_data['required'] : 'Y',
                            );
                            if (!empty($data['view_type']) && $data['view_type'] == 'E' && !empty($attr_data['view_type_txt'])) {
                                $data['view_type_txt'] = $attr_data['view_type_txt'];
                            }
                            $prod_attr[] = $attr_data['cp_attr_id'];
                            fn_cp_power_reviews_update_attribute ($data, $attr_data['cp_attr_id'], $product_data['company_id'], $lang_code, $product_id);
                        } else {
                            $n_data = array(
                                'cp_attr_id' => $attr_data['cp_attr_id'],
                                'attr_pos' => $attr_data['attr_pos'],
                                'product_id' => $product_id
                            );
                            $glob_attr[] = $attr_data['cp_attr_id'];
                            db_query("UPDATE ?:cp_power_rev_products SET ?u WHERE cp_attr_id = ?i AND product_id = ?i", $n_data, $attr_data['cp_attr_id'], $product_id);
                        }
                    } elseif (empty($attr_data['cp_attr_id']) && !empty($attr_data['new']) && !empty($trim_attr_name)) {
                        $data = array(
                            'cp_attr_id' => 0,
                            'object_id' => $product_id,
                            'attr_pos' => $attr_data['attr_pos'],
                            'object_type' => $attr_data['object_type'],
                            'status' => $attr_data['status'],
                            'cp_attr_name' => trim($attr_data['cp_attr_name']),
                            'view_type' => isset($attr_data['view_type']) ? $attr_data['view_type'] : 'D',
                            'required' => isset($attr_data['required']) ? $attr_data['required'] : 'Y',
                        );
                        if (fn_allowed_for('ULTIMATE')) {
                            $data['company_id'] = $attr_data['company_id'];
                        }
                        if (!empty($data['view_type']) && $data['view_type'] == 'E' && !empty($attr_data['view_type_txt'])) {
                            $data['view_type_txt'] = $attr_data['view_type_txt'];
                        }
                        $new_attr_id = fn_cp_power_reviews_update_attribute ($data, 0, $product_data['company_id'], $lang_code, $product_id);
                        if (!empty($new_attr_id)) {
                            $prod_attr[] = $new_attr_id;
                        }
                    }
                }
                if (!empty($glob_attr)) {
                    $check_glob_prod_exist = db_get_fields("SELECT ?:cp_power_rev_products.cp_attr_id FROM ?:cp_power_rev_products 
                        LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_power_rev_products.cp_attr_id 
                        WHERE ?:cp_power_rev_products.product_id = ?i AND ?:cp_power_ext_reviews.object_type = ?s AND ?:cp_power_rev_products.cp_attr_id NOT IN (?n) ?p", $product_id, 'G', $glob_attr, $condition);
                    if (!empty($check_glob_prod_exist)) {
                        db_query("DELETE FROM ?:cp_power_rev_products WHERE product_id = ?i AND cp_attr_id IN (?n)", $product_id, $check_glob_prod_exist);
                    }
                } else {
                    $check_glob_prod_exist = db_get_fields("SELECT ?:cp_power_rev_products.cp_attr_id FROM ?:cp_power_rev_products 
                        LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_power_rev_products.cp_attr_id 
                        WHERE ?:cp_power_rev_products.product_id = ?i AND ?:cp_power_ext_reviews.object_type = ?s ?p", $product_id, 'G', $condition);
                    if (!empty($check_glob_prod_exist)) {
                        db_query("DELETE FROM ?:cp_power_rev_products WHERE product_id = ?i AND cp_attr_id IN (?n)", $product_id, $check_glob_prod_exist);
                    }
                }
                if (!empty($prod_attr)) {
                    $check_products_exist = db_get_fields("SELECT ?:cp_power_rev_products.cp_attr_id FROM ?:cp_power_rev_products 
                        LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_power_rev_products.cp_attr_id 
                        WHERE ?:cp_power_rev_products.product_id = ?i AND ?:cp_power_ext_reviews.object_type = ?s AND ?:cp_power_rev_products.cp_attr_id NOT IN (?n) ?p", $product_id, 'P', $prod_attr, $condition);
                    if (!empty($check_products_exist)) {
                        fn_cp_power_reviews_del_attrs($check_products_exist);
                    }
                } else {
                    $check_products_exist = db_get_fields("SELECT ?:cp_power_rev_products.cp_attr_id FROM ?:cp_power_rev_products 
                        LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_power_rev_products.cp_attr_id 
                        WHERE ?:cp_power_rev_products.product_id = ?i AND ?:cp_power_ext_reviews.object_type = ?s ?p", $product_id, 'P', $condition);
                    if (!empty($check_products_exist)) {
                        fn_cp_power_reviews_del_attrs($check_products_exist);
                    }
                }
            } elseif (isset($product_data['cp_rew_attr']) && empty($product_data['cp_rew_attr'])) {
            
                if (fn_allowed_for('ULTIMATE')) {
                    if (!empty($comp_id)) {
                        $product_only_atrs = db_get_fields("SELECT cp_attr_id FROM ?:cp_power_ext_reviews WHERE cp_attr_id IN (?n) AND object_type = ?s AND company_id = ?i", $check_exist, 'P', $comp_id);
                    } else {
                        $product_only_atrs = db_get_fields("SELECT cp_attr_id FROM ?:cp_power_ext_reviews WHERE cp_attr_id IN (?n) AND object_type = ?s", $check_exist, 'P');
                    }
                } else {
                    $product_only_atrs = db_get_fields("SELECT cp_attr_id FROM ?:cp_power_ext_reviews WHERE cp_attr_id IN (?n) AND object_type = ?s", $check_exist, 'P');
                }
                if (!empty($product_only_atrs)) {
                    fn_cp_power_reviews_del_attrs($product_only_atrs);
                }
            }
        } else {
            if (!empty($product_data['cp_rew_attr'])) {
                foreach($product_data['cp_rew_attr'] as $key => $attr_data) {
                    if (!empty($attr_data['cp_attr_name'])) {
                        $trim_atr_name = trim($attr_data['cp_attr_name']);
                    } else {
                        $trim_atr_name = '';
                    }
                    if (!empty($attr_data['cp_attr_id'])) {
                        $n_data = [
                            'cp_attr_id'=> $attr_data['cp_attr_id'],
                            'attr_pos'  => $attr_data['attr_pos'],
                            'product_id'=> $product_id
                        ];
                        db_query("INSERT INTO ?:cp_power_rev_products ?e", $n_data);
                    } elseif (!empty($trim_atr_name)) {
                        $data = [
                            'cp_attr_id'    => 0,
                            'object_id'     => $product_id,
                            'attr_pos'      => $attr_data['attr_pos'],
                            'object_type'   => $attr_data['object_type'],
                            'status'        => $attr_data['status'],
                            'cp_attr_name'  => trim($attr_data['cp_attr_name']),
                            'view_type'     => isset($attr_data['view_type']) ? $attr_data['view_type'] : 'D',
                            'required'      => isset($attr_data['required']) ? $attr_data['required'] : 'Y',
                        ];
                        if (fn_allowed_for('ULTIMATE')) {
                            $data['company_id'] = $attr_data['company_id'];
                        }
                        if (!empty($data['view_type']) && $data['view_type'] == 'E' && !empty($attr_data['view_type_txt'])) {
                            $data['view_type_txt'] = $attr_data['view_type_txt'];
                        }
                        fn_cp_power_reviews_update_attribute($data, 0, $product_data['company_id'], $lang_code, $product_id);
                    }
                }
            }
        }
    }
}
function fn_cp_power_reviews_get_product_data_post (&$product_data, $auth, $preview, $lang_code) {
    
    if (!empty($product_data['product_id']) && AREA == 'A') {
        if (fn_allowed_for('ULTIMATE')) {
            $comp_id = Registry::get('runtime.company_id');
            if (!empty($comp_id)) {
                $condition = db_quote(" AND ?:cp_power_ext_reviews.company_id = ?i", $comp_id);
            } else {
                $condition = '';
            }
        } else {
            $comp_id = Registry::get('runtime.company_id');
            if (!empty($comp_id)) {
                $comps = array($comp_id, 0);
                $condition = db_quote(" AND ?:cp_power_ext_reviews.company_id IN (?n)", $comps);
            } else {
                $condition = '';
            }
        }
        $product_data['cp_prod_rew_attrs'] = db_get_array("SELECT ?:cp_power_rev_products.*, ?:cp_pow_attr_descr.cp_attr_name, ?:cp_power_ext_reviews.* FROM ?:cp_power_rev_products 
            LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_power_rev_products.cp_attr_id 
            LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_power_rev_products.cp_attr_id 
            WHERE ?:cp_power_rev_products.product_id =?i AND ?:cp_pow_attr_descr.lang_code = ?s ?p ORDER BY ?:cp_power_rev_products.attr_pos", $product_data['product_id'], $lang_code, $condition);
        $all_prod_glob_attr = db_get_fields("SELECT ?:cp_power_ext_reviews.cp_attr_id FROM ?:cp_power_ext_reviews 
            LEFT JOIN ?:cp_power_rev_products ON ?:cp_power_rev_products.cp_attr_id = ?:cp_power_ext_reviews.cp_attr_id 
            WHERE ?:cp_power_rev_products.product_id =?i AND ?:cp_power_ext_reviews.object_type = ?s ?p", $product_data['product_id'], 'G', $condition);
        if (!empty($all_prod_glob_attr)) {
            $product_data['cp_other_glob_attr'] = db_get_array("SELECT ?:cp_pow_attr_descr.cp_attr_name, ?:cp_power_ext_reviews.cp_attr_id, ?:cp_power_ext_reviews.company_id FROM ?:cp_power_ext_reviews 
            LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_power_ext_reviews.cp_attr_id 
            WHERE ?:cp_power_ext_reviews.object_type = ?s AND ?:cp_pow_attr_descr.lang_code = ?s AND ?:cp_power_ext_reviews.cp_attr_id NOT IN (?n) ?p",'G', $lang_code, $all_prod_glob_attr, $condition);
        } else {
            $product_data['cp_other_glob_attr'] = db_get_array("SELECT ?:cp_pow_attr_descr.cp_attr_name, ?:cp_power_ext_reviews.cp_attr_id, ?:cp_power_ext_reviews.company_id  FROM ?:cp_power_ext_reviews 
                LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_power_ext_reviews.cp_attr_id 
                WHERE ?:cp_power_ext_reviews.object_type = ?s AND ?:cp_pow_attr_descr.lang_code = ?s ?p", 'G', $lang_code, $condition);
        }
        
        $product_data['cat_prod_attr'] = db_get_hash_array("SELECT ?:cp_power_rev_cats.*, ?:cp_pow_attr_descr.cp_attr_name, ?:cp_power_ext_reviews.* FROM ?:cp_power_rev_cats 
                    LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_power_rev_cats.cp_attr_id 
                    LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_power_rev_cats.cp_attr_id 
                    WHERE ?:cp_power_rev_cats.category_id = ?i AND ?:cp_pow_attr_descr.lang_code = ?s ?p 
                    ORDER BY ?:cp_power_rev_cats.attr_pos", 'cp_attr_id', $product_data['main_category'], $lang_code, $condition);
        
        if (!empty($product_data['cp_prod_rew_attrs'])) {
            $product_data['cp_prod_rew_attrs'] = fn_cp_pr_get_view_type_txts($product_data['cp_prod_rew_attrs'], $lang_code);
        }
        if (!empty($product_data['cp_other_glob_attr'])) {
            $product_data['cp_other_glob_attr'] = fn_cp_pr_get_view_type_txts($product_data['cp_other_glob_attr'], $lang_code);
        }
        if (!empty($product_data['cat_prod_attr'])) {
            $product_data['cat_prod_attr'] = fn_cp_pr_get_view_type_txts($product_data['cat_prod_attr'], $lang_code);
        }
    }
    if (AREA == 'C' && !empty($product_data['product_id'])) {
        $thread_id = db_get_field("SELECT thread_id FROM ?:discussion WHERE object_id = ?i AND object_type = ?s AND type IN (?a)", $product_data['product_id'], 'P', array('B','R'));
        $stars = array_reverse(fn_cp_pr_get_discussion_ratings_revers(), true);
        $product_data['cp_pr_by_each_star'] = [];
        $product_data['cp_pr_total_rated'] = 0;
        $each_condition = $each_join = '';
        
        if (fn_allowed_for('MULTIVENDOR') && Registry::get('addons.cp_power_reviews.split_storefronts') == 'Y') {
            $store_id = Tygh::$app['storefront']->storefront_id;
            if (!empty($store_id)) {
                $each_join .= " LEFT JOIN ?:cp_pr_reviews_storefronts ON ?:cp_pr_reviews_storefronts.post_id = ?:discussion_posts.post_id";
                $each_condition .= db_quote(" AND (?:cp_pr_reviews_storefronts.storefront_id = ?i OR ?:cp_pr_reviews_storefronts.storefront_id IS NULL)", $store_id);
            }
        }
        foreach($stars as $val => $txt) {
            $product_data['cp_pr_total_rated'] += $product_data['cp_pr_by_each_star'][$val] = db_get_field("SELECT COUNT(?:discussion_rating.rating_value) FROM ?:discussion_rating 
                        LEFT JOIN ?:discussion_posts ON ?:discussion_posts.post_id = ?:discussion_rating.post_id ?p
                        WHERE ?:discussion_rating.thread_id = ?i AND ?:discussion_rating.rating_value = ?i 
                            AND ?:discussion_posts.status = ?s AND ?:discussion_posts.cp_pr_user_delete = ?s ?p", $each_join, $thread_id, $val, 'A', 'N', $each_condition);
        }
    }
}

function fn_cp_pr_get_view_type_txts($attrs, $lang_code = CART_LANGUAGE, $thread_ids = [])
{
    if (!empty($attrs)) {
        $store_join = $store_condition = '';
        if (fn_allowed_for('MULTIVENDOR') && AREA == 'C' && Registry::get('addons.cp_power_reviews.split_storefronts') == 'Y') {
            $store_id = Tygh::$app['storefront']->storefront_id;
            if (!empty($store_id)) {
                $store_join .= " LEFT JOIN ?:cp_pr_reviews_storefronts ON ?:cp_pr_reviews_storefronts.post_id = ?:discussion_posts.post_id";
                $store_condition .= db_quote(" AND (?:cp_pr_reviews_storefronts.storefront_id = ?i OR ?:cp_pr_reviews_storefronts.storefront_id IS NULL)", $store_id);
            }
        }
        $stars = array_reverse(fn_cp_pr_get_discussion_ratings_revers(), true);
        if (!is_array($thread_ids)) {
            $thread_ids = (array) $thread_ids;
        }
        foreach($attrs as &$att) {
            if (!empty($att['view_type']) && $att['view_type'] == 'E') {
                $att['view_type_txt'] = db_get_hash_array("SELECT ?:cp_pr_attr_name_descriptions.*, ?:cp_pr_attr_names.* FROM ?:cp_pr_attr_names
                LEFT JOIN ?:cp_pr_attr_name_descriptions ON ?:cp_pr_attr_name_descriptions.name_id = ?:cp_pr_attr_names.name_id AND ?:cp_pr_attr_name_descriptions.lang_code = ?s
                WHERE ?:cp_pr_attr_names.cp_attr_id = ?i ORDER BY FIELD(?:cp_pr_attr_names.type, 'L','LM','M','MT','T')", 'type', $lang_code, $att['cp_attr_id']);
                if (AREA == 'C' && ((!empty($att['atr_aver_rate']) && !empty($thread_ids)) || !empty($att['rating'])) && !empty($att['view_type_txt'])) {
                    if (!empty($att['atr_aver_rate'])) {
                        $av_rate = round($att['atr_aver_rate'], 0);
                    } else {
                        $av_rate = $att['rating'];
                    }
                    if ($av_rate == 1 && !empty($att['view_type_txt']['L']['name'])) {
                        $att['attr_extr_name'] = $att['view_type_txt']['L']['name'];
                    } elseif($av_rate == 2 && !empty($att['view_type_txt']['LM']['name'])) {
                        $att['attr_extr_name'] = $att['view_type_txt']['LM']['name'];
                    } elseif($av_rate == 3 && !empty($att['view_type_txt']['M']['name'])) {
                        $att['attr_extr_name'] = $att['view_type_txt']['M']['name'];
                    } elseif($av_rate == 4 && !empty($att['view_type_txt']['MT']['name'])) {
                        $att['attr_extr_name'] = $att['view_type_txt']['MT']['name'];
                    } elseif($av_rate == 5 && !empty($att['view_type_txt']['LM']['name'])) {
                        $att['attr_extr_name'] = $att['view_type_txt']['T']['name'];
                    }
                    if (!empty($att['atr_aver_rate']) && !empty($thread_ids)) {
                        $att['by_each_value'] = [];
                        $att['total_rated'] = 0;
                        foreach($stars as $val => $txt) {
                            $att['by_each_value'][$val] = [];
                            if ($val == 1 && !empty($att['view_type_txt']['L']['name'])) {
                                $att['by_each_value'][$val]['name'] = $att['view_type_txt']['L']['name'];
                            } elseif ($val == 2 && !empty($att['view_type_txt']['LM']['name'])) {
                                $att['by_each_value'][$val]['name'] = $att['view_type_txt']['LM']['name'];
                            } elseif ($val == 3 && !empty($att['view_type_txt']['M']['name'])) {
                                $att['by_each_value'][$val]['name'] = $att['view_type_txt']['M']['name'];
                            } elseif ($val == 4 && !empty($att['view_type_txt']['MT']['name'])) {
                                $att['by_each_value'][$val]['name'] = $att['view_type_txt']['MT']['name'];
                            } elseif ($val == 5 && !empty($att['view_type_txt']['T']['name'])) {
                                $att['by_each_value'][$val]['name'] = $att['view_type_txt']['T']['name'];
                            }
                            $att['total_rated'] += $att['by_each_value'][$val]['total'] = db_get_field("SELECT COUNT(DISTINCT(?:cp_pow_attr_ratings.post_id)) FROM ?:cp_pow_attr_ratings
                                LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_pow_attr_ratings.cp_attr_id
                                LEFT JOIN ?:discussion_posts ON ?:discussion_posts.post_id = ?:cp_pow_attr_ratings.post_id ?p
                                WHERE ?:discussion_posts.thread_id IN (?n) AND ?:cp_pow_attr_ratings.cp_attr_id = ?i AND ?:cp_pow_attr_ratings.rating = ?i AND ?:cp_power_ext_reviews.status = ?s 
                                    AND ?:discussion_posts.status = ?s AND ?:discussion_posts.cp_pr_user_delete = ?s ?p", $store_join, $thread_ids, $att['cp_attr_id'], $val, 'A', 'A', 'N', $store_condition
                            );
                        }
                    }
                }
            } elseif (AREA == 'C' && !empty($att['atr_aver_rate']) && !empty($thread_ids)) {
                $att['by_each_value'] = [];
                $att['total_rated'] = 0;
                foreach($stars as $val => $txt) {
                    $att['by_each_value'][$val] = [];
                    $att['total_rated'] += $att['by_each_value'][$val]['total'] = db_get_field("SELECT COUNT(DISTINCT(?:cp_pow_attr_ratings.post_id)) FROM ?:cp_pow_attr_ratings
                        LEFT JOIN ?:cp_power_ext_reviews ON ?:cp_power_ext_reviews.cp_attr_id = ?:cp_pow_attr_ratings.cp_attr_id
                        LEFT JOIN ?:discussion_posts ON ?:discussion_posts.post_id = ?:cp_pow_attr_ratings.post_id ?p
                        WHERE ?:discussion_posts.thread_id IN (?n) AND ?:cp_pow_attr_ratings.cp_attr_id = ?i AND ?:cp_pow_attr_ratings.rating = ?i AND ?:cp_power_ext_reviews.status = ?s 
                            AND ?:discussion_posts.status = ?s AND ?:discussion_posts.cp_pr_user_delete = ?s ?p", $store_join, $thread_ids, $att['cp_attr_id'], $val, 'A', 'A', 'N', $store_condition
                    );
                }
            }
        }
    }
    return $attrs;
}

function fn_cp_power_reviews_apply_attrs_to_cats ($cp_attr_ids, $cat_ids, $action) {
    if (!empty($cp_attr_ids) && !empty($cat_ids)) {
        $cat_ids = explode(',', $cat_ids);
        if (!empty($action) && !empty($cat_ids)) {
            if ($action == 'del_sel') {
                foreach($cat_ids as $cat_id) {
                    db_query("DELETE FROM ?:cp_power_rev_cats WHERE category_id = ?i AND cp_attr_id IN (?n)", $cat_id, $cp_attr_ids);
                }
            } elseif ($action == 'add_sel') {
                foreach($cp_attr_ids as $attr_id) {
                    foreach($cat_ids as $cat_id) {
                        $check = db_get_field("SELECT cp_attr_id FROM ?:cp_power_rev_cats WHERE cp_attr_id = ?i AND category_id = ?i", $attr_id, $cat_id);
                        if (empty($check)) {
                            $data = array(
                                'cp_attr_id' => $attr_id,
                                'attr_pos' => 0,
                                'category_id' => $cat_id
                            );
                            db_query("INSERT INTO ?:cp_power_rev_cats ?e", $data);
                        }
                    }
                }
            }
        }
    }
    return true;
}
function fn_cp_power_reviews_apply_attrs_to_all_objects ($cp_attr_ids, $action, $type) {
    if (!empty($cp_attr_ids) && !empty($action) && !empty($type)) {
        $cur_company_id = Registry::get('runtime.company_id');
        $main_delete = false;
        $prod_ids = $cats_ids = [];
        if ($type == 'P') {
            if (fn_allowed_for('MULTIVENDOR')) {
                if (!empty($cur_company_id)) {
                    $prod_ids = db_get_fields("SELECT product_id FROM ?:products WHERE company_id = ?i", $cur_company_id);
                } else {
                    $prod_ids = db_get_fields("SELECT product_id FROM ?:products");
                    $main_delete = true;
                }
            } else {
                if (!empty($cur_company_id)) {
                    $prod_ids = db_get_fields("SELECT ?:products_categories.product_id FROM ?:products_categories 
                        LEFT JOIN ?:categories ON ?:categories.category_id = ?:products_categories.category_id
                        WHERE ?:categories.company_id = ?i", $cur_company_id);
                } else {
                    $prod_ids = db_get_fields("SELECT product_id FROM ?:products");
                    $main_delete = true;
                }
            }
            if (!empty($prod_ids)) {
                foreach($cp_attr_ids as $attr_id) {
                    if ($action == 'add') {
                        foreach($prod_ids as $prod_id) {
                            $data = array(
                                'cp_attr_id' => $attr_id,
                                //'attr_pos' => 0,
                                'product_id' => $prod_id
                            );
                            db_query("INSERT INTO ?:cp_power_rev_products ?e ON DUPLICATE KEY UPDATE ?u", $data, $data);
                        }
                    } elseif ($action == 'del') {
                        if (!empty($main_delete)) {
                            db_query("DELETE FROM ?:cp_power_rev_products WHERE cp_attr_id = ?i", $attr_id);
                        } else {
                            if (fn_allowed_for('MULTIVENDOR')) {
                                db_query("DELETE ?:cp_power_rev_products FROM ?:cp_power_rev_products
                                    LEFT JOIN ?:products ON ?:products.product_id = ?:cp_power_rev_products.product_id 
                                    WHERE ?:products.company_id = ?i AND ?:cp_power_rev_products.cp_attr_id = ?i", $cur_company_id, $attr_id);
                            } else {
                                db_query("DELETE ?:cp_power_rev_products FROM ?:cp_power_rev_products
                                    LEFT JOIN ?:products_categories ON ?:products_categories.product_id = ?:cp_power_rev_products.product_id 
                                    LEFT JOIN ?:categories ON ?:categories.category_id = ?:products_categories.category_id
                                    WHERE ?:categories.company_id = ?i AND ?:cp_power_rev_products.cp_attr_id = ?i", $cur_company_id, $attr_id);
                            }
                        }
                    }
                }
            }
        } elseif ($type == 'C') {
            if (fn_allowed_for('MULTIVENDOR')) {
                if (empty($cur_company_id)) {
                    $cats_ids = db_get_fields("SELECT category_id FROM ?:categories");
                    $main_delete = true;
                }
            } else {
                if (!empty($cur_company_id)) {
                    $cats_ids = db_get_fields("SELECT category_id FROM ?:categories WHERE company_id = ?i", $cur_company_id);
                } else {
                    $cats_ids = db_get_fields("SELECT category_id FROM ?:categories");
                    $main_delete = true;
                }
            }
            if (!empty($cats_ids)) {
                foreach($cp_attr_ids as $attr_id) {
                    if ($action == 'add') {
                        foreach($cats_ids as $cat_id) {
                            $data = array(
                                'cp_attr_id' => $attr_id,
                                'category_id' => $cat_id
                            );
                            db_query("INSERT INTO ?:cp_power_rev_cats ?e ON DUPLICATE KEY UPDATE ?u", $data, $data);
                        }
                    } elseif ($action == 'del') {
                        if (!empty($main_delete)) {
                            db_query("DELETE FROM ?:cp_power_rev_cats WHERE cp_attr_id = ?i", $attr_id);
                        } else {
                            db_query("DELETE ?:cp_power_rev_cats FROM ?:cp_power_rev_cats
                                    LEFT JOIN ?:categories ON ?:categories.category_id = ?:cp_power_rev_cats.category_id 
                                    WHERE ?:categories.company_id = ?i AND ?:cp_power_rev_cats.cp_attr_id = ?i", $cur_company_id, $attr_id);
                        }
                    }
                }
            }
        }
    }
    return true;
}
function fn_cp_power_reviews_apply_attrs_to_prods ($cp_attr_ids, $prod_ids, $action) {
    if (!empty($cp_attr_ids) && !empty($prod_ids)) {
        $prod_ids = explode(',', $prod_ids);
        if (!empty($action) && !empty($prod_ids)) {
            if ($action == 'del_sel') {
                foreach($prod_ids as $prod_id) {
                    db_query("DELETE FROM ?:cp_power_rev_products WHERE product_id = ?i AND cp_attr_id IN (?n)", $prod_id, $cp_attr_ids);
                }
            } elseif ($action == 'add_sel') {
                foreach($cp_attr_ids as $attr_id) {
                    foreach($prod_ids as $prod_id) {
                        $check = db_get_field("SELECT cp_attr_id FROM ?:cp_power_rev_products WHERE cp_attr_id = ?i AND product_id = ?i", $attr_id, $prod_id);
                        if (empty($check)) {
                            $data = array(
                                'cp_attr_id' => $attr_id,
                                'attr_pos' => 0,
                                'product_id' => $prod_id
                            );
                            db_query("INSERT INTO ?:cp_power_rev_products ?e", $data);
                        }
                    }
                }
            }
        }
    }
    return true;
}

function fn_cp_power_reviews_del_attrs ($cp_attr_ids) {
    if (!empty($cp_attr_ids)) {
        if (!is_array($cp_attr_ids)) {
            $cp_attr_ids = array($cp_attr_ids);
        }
        db_query("DELETE FROM ?:cp_power_ext_reviews WHERE cp_attr_id IN (?n)", $cp_attr_ids);
        db_query("DELETE FROM ?:cp_pow_attr_descr WHERE cp_attr_id IN (?n)", $cp_attr_ids);
        db_query("DELETE FROM ?:cp_power_rev_products WHERE cp_attr_id IN (?n)", $cp_attr_ids);
        db_query("DELETE FROM ?:cp_power_rev_cats WHERE cp_attr_id IN (?n)", $cp_attr_ids);
        db_query("DELETE FROM ?:cp_pow_attr_ratings WHERE cp_attr_id IN (?n)", $cp_attr_ids);
        
        $name_ids = db_get_fields("SELECT name_id FROM ?:cp_pr_attr_names WHERE cp_attr_id IN (?n)", $cp_attr_ids);
        if (!empty($name_ids)) {
            db_query("DELETE FROM ?:cp_pr_attr_names WHERE cp_attr_id IN (?n)", $cp_attr_ids);
            db_query("DELETE FROM ?:cp_pr_attr_name_descriptions WHERE name_id IN (?n)", $name_ids);
        }
    }
    return true;
}
function fn_cp_power_reviews_m_update_attributes($data, $lang_code = DESCR_SL) {
    if (!empty($data)) {
        foreach($data as $cp_attr_id => $attr_data) {
            if (isset($attr_data['position'])) {
                db_query("UPDATE ?:cp_power_ext_reviews SET position = ?i, required = ?s  WHERE cp_attr_id = ?i", $attr_data['position'], $attr_data['required'], $cp_attr_id);
            }
            db_query('UPDATE ?:cp_pow_attr_descr SET cp_attr_name = ?s WHERE cp_attr_id = ?i AND lang_code = ?s', $attr_data['cp_attr_name'], $cp_attr_id, $lang_code);
            if (!empty($attr_data['view_type_txt'])) {
                foreach($attr_data['view_type_txt'] as $type => $val) {
                    db_query("UPDATE ?:cp_pr_attr_name_descriptions SET name = ?s WHERE name_id = ?i AND lang_code = ?s", $val['name'], $val['name_id'], $lang_code);
                }
            }
        }
    }
    return true;
}

function fn_cp_power_reviews_update_attribute ($data, $cp_attr_id, $company_id, $lang_code = DESCR_SL, $obj_id = 0, $obj_type = 'P') {
    if (!empty($cp_attr_id)) {
        db_query("UPDATE ?:cp_power_ext_reviews SET ?u WHERE cp_attr_id = ?i", $data, $cp_attr_id);
        db_query('UPDATE ?:cp_pow_attr_descr SET ?u WHERE cp_attr_id = ?i AND lang_code = ?s', $data, $cp_attr_id, $lang_code);
        if (!empty($obj_id)) {
            $n_data = array(
                'cp_attr_id' => $data['cp_attr_id'],
                'attr_pos' => $data['attr_pos'],
            );
            if ($obj_type == 'C') {
                $n_data['category_id'] = $obj_id;
                db_query("UPDATE ?:cp_power_rev_cats SET ?u WHERE cp_attr_id = ?i AND category_id = ?i", $n_data, $data['cp_attr_id'], $obj_id);
            } else {
                $n_data['product_id'] = $obj_id;
                db_query("UPDATE ?:cp_power_rev_products SET ?u WHERE cp_attr_id = ?i AND product_id = ?i", $n_data, $data['cp_attr_id'], $obj_id);
            }
        }
    } else {
        if (fn_allowed_for('MULTIVENDOR')) {
            $data['company_id'] = $company_id;
        }
        $cp_attr_id = $data['cp_attr_id'] = db_query("REPLACE INTO ?:cp_power_ext_reviews ?e", $data);
        foreach (fn_get_translation_languages() as $data['lang_code'] => $_v) {
            db_query("REPLACE INTO ?:cp_pow_attr_descr ?e", $data);
        }
        if (!empty($obj_id)) {
            $n_data = array(
                'cp_attr_id' => $cp_attr_id,
                'attr_pos' => $data['attr_pos'],
            );
            if ($obj_type == 'C') {
                $n_data['category_id'] = $obj_id;
                db_query("INSERT INTO ?:cp_power_rev_cats ?e", $n_data);
            } else {
                $n_data['product_id'] = $obj_id;
                db_query("INSERT INTO ?:cp_power_rev_products ?e", $n_data);
            }
        }
    }
    if (!empty($cp_attr_id) && !empty($data['view_type_txt']) && !empty($data['view_type']) && $data['view_type'] == 'E') {
        $exist_vals = db_get_hash_array("SELECT * FROM ?:cp_pr_attr_names WHERE cp_attr_id = ?i", 'type', $cp_attr_id);
        foreach($data['view_type_txt'] as $e_type => $e_val) {
            $name_id = 0;
            if (empty($exist_vals) || (!empty($exist_vals) && empty($exist_vals[$e_type]))) {
                $e_data = array(
                    'type' => $e_type,
                    'cp_attr_id' => $cp_attr_id,
                    'name' => $e_val
                );
                $name_id = $e_data['name_id'] = db_query("INSERT INTO ?:cp_pr_attr_names ?e", $e_data);
                if (!empty($name_id)) {
                    
                    foreach (fn_get_translation_languages() as $e_data['lang_code'] => $_v) {
                        db_query("REPLACE INTO ?:cp_pr_attr_name_descriptions ?e", $e_data);
                    }
                }
            } elseif (!empty($exist_vals) && !empty($exist_vals[$e_type])) {
                db_query("UPDATE ?:cp_pr_attr_name_descriptions SET name = ?s WHERE name_id = ?i AND lang_code = ?s", $e_val['name'], $e_val['name_id'], $lang_code);
            }
        }
    }
    
    return $cp_attr_id;
}
function fn_cp_power_reviews_get_attribute_data ($cp_attr_id, $lang_code = DESCR_SL) {
    
    $fields = $joins = [];
    $condition = '';
    $fields = array (
        "?:cp_power_ext_reviews.*",
        "?:cp_pow_attr_descr.*",
    );
    
    $joins[] = db_quote("LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_power_ext_reviews.cp_attr_id AND ?:cp_pow_attr_descr.lang_code = ?s", $lang_code);
    
    $condition = db_quote("WHERE ?:cp_power_ext_reviews.cp_attr_id = ?i", $cp_attr_id);

    $att_data = db_get_row("SELECT " . implode(", ", $fields) . " FROM ?:cp_power_ext_reviews " . implode(" ", $joins) ." $condition");
    if (empty($att_data['cp_attr_id'])) {
        $att_data['cp_attr_id'] = $cp_attr_id;
    }
    return $att_data;
}
//get all attrs
function fn_cp_power_reviews_get_attrs($params = [], $items_per_page = 0, $lang_code = CART_LANGUAGE) {
    $params['cp_attr_id'] = 0;
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page,
        'get_hidden' => true
    );
    $params = array_merge($default_params, $params);
    $fields = array (
        "?:cp_power_ext_reviews.*",
        "?:cp_pow_attr_descr.*",
    );
    $sortings = array (
        'cp_attr_name' => "?:cp_pow_attr_descr.cp_attr_name",
        'object_type' => "?:cp_power_ext_reviews.object_type",
        'status' => "?:cp_power_ext_reviews.status",
        'view_type' => "?:cp_power_ext_reviews.view_type",
        'position' => "?:cp_power_ext_reviews.position",
        'required' => "?:cp_power_ext_reviews.required",
    );
    $condition = $join = $group = '';
    
    $condition .= fn_get_company_condition('?:cp_power_ext_reviews.company_id');
    
    $statuses = array('A');
    if (!empty($params['get_hidden'])) {
        $statuses[] = 'H';
    }
    if (!empty($params['attr_type'])) {
        if (!is_array($params['attr_type'])) {
            $params['attr_type'] = array($params['attr_type']);
        }
        $condition .= db_quote(" AND ?:cp_power_ext_reviews.object_type IN (?a)", $params['attr_type']);
    }
    if (!empty($params['attrs'])) {
        $condition .= db_quote(" AND ?:cp_power_ext_reviews.cp_attr_id IN (?n)", explode(',', $params['attrs']));
    }
    if (!empty($params['active'])) {
        $condition .= db_quote(" AND ?:cp_power_ext_reviews.status IN (?a)", $statuses);
    }
    
    $join .= db_quote(" LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_power_ext_reviews.cp_attr_id AND ?:cp_pow_attr_descr.lang_code = ?s", $lang_code);
    if (!empty($params['cp_go_by_type']) && !defined('AJAX_REQUEST')) {
        $params['sort_by'] = 'object_type';
        $params['sort_order'] = 'asc';
    }
    $sorting = db_sort($params, $sortings, 'cp_attr_name', 'asc');
    
    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:cp_power_ext_reviews $join WHERE 1 $condition $group");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $attributes = db_get_hash_array('SELECT ' . implode(', ', $fields) . " FROM ?:cp_power_ext_reviews $join WHERE 1 $condition $group $sorting $limit", 'cp_attr_id');
    
    if (!empty($attributes)) {
        $attributes = fn_cp_pr_get_view_type_txts($attributes, $lang_code);
    }
    return array($attributes, $params);
}

function fn_cp_power_reviews_install_func () {
    
    db_query("UPDATE ?:discussion_rating SET cp_sred_rate = rating_value");
    $tab_id = db_get_field("SELECT tab_id FROM ?:product_tabs WHERE addon = ?s", 'cp_power_reviews');
    if (!empty($tab_id)) {
        db_query("UPDATE ?:product_tabs_descriptions SET name = ?s WHERE tab_id = ?i AND lang_code = ?s", 'Расширенные отзывы', $tab_id, 'ru');
    }
    $buld = PRODUCT_BUILD;
    if (!empty($buld) && $buld == 'RU') {
        db_query("UPDATE ?:addons SET priority = ?i WHERE addon = ?s", 4999, 'cp_power_reviews');
    }
    
    if (Registry::get('addons.seo.status')) {
        fn_cp_pr_create_seo_settings();
    }
//
    return true;
}
function fn_cp_power_reviews_uninstall_func () {
    $faq_tab = db_get_fields("SELECT tab_id FROM ?:product_tabs WHERE template = ?s", 'addons/cp_power_reviews/components/discussion.tpl');
    if (!empty($faq_tab)) {
        foreach($faq_tab as $cp_tab) {
            ProductTabs::instance()->delete($cp_tab);
        }
    }
    fn_cp_pr_delete_seo_settings();
    
    $videos = db_get_fields("SELECT video_id FROM ?:cp_pr_video_links");
    if (!empty($videos)) {
        foreach($videos as $video_id) {
            fn_delete_image_pairs($video_id, 'cp_pr_video_preview');
        }
    }
    $all_posts_ids = db_get_fields("SELECT DISTINCT(post_id) FROM ?:cp_review_images");
    if (!empty($all_posts_ids)) {
        foreach($all_posts_ids as $post_id) {
            fn_delete_image_pairs($post_id, 'cp_rev_post');
        }
    }
    
    return true;
}
function fn_cp_power_reviews_update_language_post($language_data, $lang_id, $action) {

    if ($action == 'add') {
        $attributes = db_get_hash_multi_array("SELECT ?:cp_power_ext_reviews.cp_attr_id, ?:cp_pow_attr_descr.cp_attr_name, ?:cp_pow_attr_descr.lang_code FROM ?:cp_power_ext_reviews 
            LEFT JOIN ?:cp_pow_attr_descr ON ?:cp_pow_attr_descr.cp_attr_id = ?:cp_power_ext_reviews.cp_attr_id", array('lang_code', 'cp_attr_id'));
        if (!empty($attributes['en'])) {
            foreach ($attributes['en'] as $q_a) {
                $q_a['lang_code'] = $language_data['lang_code'];
                db_query("REPLACE INTO ?:cp_pow_attr_descr ?e", $q_a);
            }
        }
    }
}
function fn_cp_power_reviews_delete_languages_post($lang_ids, $lang_codes, $deleted_lang_codes) {
    
    foreach ($deleted_lang_codes as $lang_code) {
        db_query("DELETE FROM ?:cp_pow_attr_descr WHERE lang_code = ?s", $lang_code);
    }
}
function fn_cp_power_reviews_delete_product_post($product_id, $product_deleted) {
    if (!empty($product_id) && !empty($product_deleted)) {
        $all_prod_attrs = db_get_fields("SELECT cp_attr_id FROM ?:cp_power_ext_reviews WHERE object_id = ?i AND object_type = ?s", $product_id, 'P');
        if (!empty($all_prod_attrs)) {
            fn_cp_power_reviews_del_attrs($all_prod_attrs);
        }
    }
}
function fn_cp_power_reviews_parse_datetime($datetime) {
    $timestamp = 0;

    if (!empty($datetime)) {
        if (is_numeric($datetime)) {
            return $datetime;
        }

        list($date, $time) = explode(' ', $datetime);

        $timestamp = fn_parse_date($date);
        $time = str_replace(':', '', $time);
        $h = $m = 0;
        sscanf($time, '%2d%2d', $h, $m);

        $timestamp += $h * SECONDS_IN_HOUR + $m * 60;

    }

    return !empty($timestamp) ? $timestamp : TIME;
}
function fn_cp_power_reviews_get_http_files_dir_path() {
    $path = fn_get_rel_dir(fn_get_files_dir_path());
    $path = Registry::get('config.http_location') . '/' . $path;

    return $path;
}
function fn_cp_power_reviews_get_public_files_path() {
    $path = Storage::instance('images')->getAbsolutePath('');
    $company_id = Registry::get('runtime.simple_ultimate') ? Registry::get('runtime.forced_company_id') : Registry::get('runtime.company_id');

    if (!empty($company_id)) {
        $path .=  'companies/' . $company_id . '/';
    }
    return $path;
}
//end

function fn_cp_power_reviews_all_review_info () {
    if (fn_allowed_for('MULTIVENDOR')) {
        $company_id = 0;
    } else {
        $company_id = Registry::get('runtime.company_id');
        if (empty($company_id)) {
            $company_id = 1;
        }
    }
    $reviews_id = db_get_field("SELECT id FROM ?:cp_pr_reviews_seo WHERE company_id = ?i", $company_id);
    
    if (fn_allowed_for('MULTIVENDOR')) {
        $storefront_id = isset($_REQUEST['storefront_id']) ? (int) $_REQUEST['storefront_id'] : 0;
        if (!empty($storefront_id)) {
            $site_url = fn_url('cp_pow_rev.all_reviews?id=' . $reviews_id . '&storefront_id=' . $storefront_id, 'C', fn_get_storefront_protocol(null, $storefront_id));
        } else {
            $site_url = fn_url('cp_pow_rev.all_reviews?id=' . $reviews_id, 'C', fn_get_storefront_protocol(null, $storefront_id));
        }
        
    } else {
        $site_url = fn_url('cp_pow_rev.all_reviews?id=' . $reviews_id, 'C');
    }
    $hint = '<b>' . __('cp_pr_site_link') . ':</b> ' .$site_url . '<br /><b>';
    $hint .= __('cp_use_link_in_menu') . ':</b> cp_pow_rev.all_reviews?id=' . $reviews_id . '<br />';
    $hint .= '<b>' . __('cp_pr_for_smarty') . ':</b> {"cp_pow_rev.all_reviews?id=' . $reviews_id . '"|fn_url}';
    return $hint;
}

function fn_cp_pr_generate_purchase_info()
{
    $site_url = fn_url('cp_pow_rev.check_purchased', 'A');
    $hint = '<b>' . __('cp_pr_set_purchase_status') . ':</b> <a class="cm-ajax" href="' . $site_url . '">' . __('link') . '</a>';
    return $hint;
}

function fn_settings_variants_addons_cp_power_reviews_objects_for_all()
{
    $result = array(
        'p' => __('products'),
        'c' => __('categories'),
        'a' => __('pages'),
        'e' => __('cp_test_reviews'),
    );
    if (fn_allowed_for('MULTIVENDOR')) {
        $result['m'] = __('vendors');
    }
    if (Registry::get('addons.cp_power_blog.status') == 'A') {
        $result['b'] = __('cp_blog_posts_title_text');
    }
    return $result;
}

function fn_cp_pr_check_purchased_posts()
{
    $verif_statuses_set = Registry::get('addons.cp_power_reviews.orders_for_purchase');
    if (!empty($verif_statuses_set)) {
        $verif_statuses = [];
        foreach($verif_statuses_set as $st => $val) {
            if (!empty($val) && $val == 'Y') {
                $verif_statuses[] = $st;
            }
        }
        if (!empty($verif_statuses)) {
            //unset purchase
            db_query("
                UPDATE ?:discussion_posts 
                LEFT JOIN ?:discussion ON ?:discussion.thread_id = ?:discussion_posts.thread_id 
                LEFT JOIN ?:order_details ON ?:order_details.product_id = ?:discussion.object_id 
                LEFT JOIN ?:orders ON ?:orders.order_id = ?:order_details.order_id AND ?:orders.user_id = ?:discussion_posts.user_id
                SET ?:discussion_posts.cp_pr_verified_purchase = ?s 
                WHERE ?:orders.status NOT IN (?a) AND ?:discussion.object_type = ?s", 'N', $verif_statuses, 'P'
            );
            //set purchase
            $posts = db_query("
                UPDATE ?:discussion_posts 
                LEFT JOIN ?:discussion ON ?:discussion.thread_id = ?:discussion_posts.thread_id 
                LEFT JOIN ?:order_details ON ?:order_details.product_id = ?:discussion.object_id 
                LEFT JOIN ?:orders ON ?:orders.order_id = ?:order_details.order_id AND ?:orders.user_id = ?:discussion_posts.user_id
                SET ?:discussion_posts.cp_pr_verified_purchase = ?s 
                WHERE ?:orders.status IN (?a) AND ?:discussion.object_type = ?s", 'Y', $verif_statuses, 'P'
            );
            fn_set_notification('N', __('notice'), __('done'));
        }
    }
    return true;
}

function fn_cp_pr_get_object_data($params)
{   
    $data = [];
    if (!empty($params['thread_id'])) {
        $data = db_get_row("SELECT * FROM ?:discussion WHERE thread_id = ?i", $params['thread_id']);
    } elseif (!empty($params['object_id']) && !empty($params['object_type'])) {
        $data = $params;
    } elseif (!empty($params['productid'])) {
        $params['productid'] = explode(',', $params['productid']);
        $total = count($params['productid']);
        if ($total == 1) {
            $data = db_get_row("SELECT * FROM ?:discussion WHERE object_id IN (?n) AND object_type = ?s", $params['productid'], 'P');
        } else {
            $data['cp_name'] = __('products') . ' (' . $total . ')';
        }
    } elseif (!empty($params['pageid'])) {
        $params['pageid'] = explode(',', $params['pageid']);
        $total = count($params['pageid']);
        if ($total == 1) {
            $data = db_get_row("SELECT * FROM ?:discussion WHERE object_id IN (?n) AND object_type = ?s", $params['pageid'], 'A');
        } else {
            $data['cp_name'] = __('pages') . ' (' . $total . ')';
        }
    } elseif (!empty($params['cid'])) {
        $params['cid'] = explode(',', $params['cid']);
        $total = count($params['cid']);
        if ($total == 1) {
            $data = db_get_row("SELECT * FROM ?:discussion WHERE object_id IN (?n) AND object_type = ?s", $params['cid'], 'C');
        } else {
            $data['cp_name'] = __('categories') . ' (' . $total . ')';
        }
    } elseif (!empty($params['vend_ids'])) {
        $params['vend_ids'] = explode(',', $params['vend_ids']);
        $total = count($params['vend_ids']);
        if ($total == 1) {
            $data = db_get_row("SELECT * FROM ?:discussion WHERE object_id IN (?n) AND object_type = ?s", $params['vend_ids'], 'M');
        } else {
            $data['cp_name'] = __('vendors') . ' (' . $total . ')';
        }
    } elseif (!empty($params['object_type']) && $params['object_type'] == 'P') {
        $data['cp_name'] = __('products');
    } elseif (!empty($params['object_type']) && $params['object_type'] == 'A') {
        $data['cp_name'] = __('pages');
    } elseif (!empty($params['object_type']) && $params['object_type'] == 'C') {
        $data['cp_name'] = __('categories');
    } elseif (!empty($params['object_type']) && $params['object_type'] == 'M') {
        $data['cp_name'] = __('vendors');
    } elseif (!empty($params['object_type']) && $params['object_type'] == 'E') {
        $data['cp_name'] = __('testimonials');
    }
    return $data;
}
function fn_settings_variants_addons_cp_power_reviews_orders_for_purchase()
{
    $order_statuses = fn_get_statuses(STATUSES_ORDER, [], true, false, CART_LANGUAGE, 0);
    $result = [];
    if (!empty($order_statuses)) {
        foreach($order_statuses as $o_status) {
            if (!empty($o_status['params']['inventory']) && $o_status['params']['inventory'] == 'D') {
                $result[$o_status['status']] = $o_status['description'];
            }
        }
    }
    return $result;
}

function fn_cp_pp_remove_post_video($video_id) 
{
    $result = false;
    if (!empty($video_id)) {
        $result = db_query("DELETE FROM ?:cp_pr_video_links WHERE video_id = ?i", $video_id);
        if (!empty($result)) {
            fn_delete_image_pairs($video_id, 'cp_pr_video_preview');
        }
    }
    return $result;
}

function fn_cp_pp_delete_post_additional_data($post_ids)
{
    if (!empty($post_ids)) {
        db_query("DELETE FROM ?:cp_pow_attr_likes_users WHERE post_id IN (?n)", $post_ids);
        $videos = db_get_fields("SELECT video_id FROM ?:cp_pr_video_links WHERE post_id IN (?n)", $post_ids);
        if (!empty($videos)) {
            db_query("DELETE FROM ?:cp_pr_video_links WHERE post_id IN (?n)", $post_ids);
            foreach($videos as $video_id) {
                fn_delete_image_pairs($video_id, 'cp_pr_video_preview');
            }
        }
        db_query("DELETE FROM ?:cp_review_images WHERE post_id IN (?n)", $post_ids);
        foreach($post_ids as $post_id) {
            fn_delete_image_pairs($post_id, 'cp_rev_post');
        }
    }
    return true;
}

function fn_cp_pr_thread_object_tables() 
{
    $tables = array(
        'A' => array(
            'id' => 'page_id',
            'table' => 'page_descriptions',
            'column' => 'page',
            'link' => 'pages.view?page_id=',
            'lang_var' => defined('CART_LANGUAGE') ? __('cp_pr_page_txt') : 'page'
        ),
        'B' => array(
            'id' => 'post_id',
            'table' => 'cp_blog_post_descriptions',
            'column' => 'name',
            'link' => 'cp_blog.view?post_id=',
            'lang_var' => defined('CART_LANGUAGE') ? __('cp_pr_article_txt') : 'article'
        ),
        'C' => array(
            'id' => 'category_id',
            'table' => 'category_descriptions',
            'column' => 'category',
            'link' => 'categories.view?category_id=',
            'lang_var' => defined('CART_LANGUAGE') ? __('cp_pr_category_txt') : 'category'
        ),
        'M' => array(
            'id' => 'company_id',
            'table' => 'companies',
            'column' => 'company',
            'link' => 'companies.products?company_id=',
            'lang_var' => defined('CART_LANGUAGE') ? __('cp_pr_vendor_txt') : 'vendor' 
        ),
        'P' => array(
            'id' => 'product_id',
            'table' => 'product_descriptions',
            'column' => 'product',
            'link' => 'products.view?product_id=',
            'lang_var' => defined('CART_LANGUAGE') ? __('cp_pr_product_txt') : 'product'
        ),
    );
    
    fn_set_hook('cp_pr_thread_tables_post', $tables);
    
    return $tables;
}

function fn_cp_pr_get_object_link($thread_data, $lang_code = CART_LANGUAGE) 
{
    $link = $object_name = $object_type = '';
    if (!empty($thread_data) && !empty($thread_data['object_type']) && $thread_data['object_id']) {
        $thread_tables = fn_cp_pr_thread_object_tables();
        if (!empty($thread_tables) && !empty($thread_tables[$thread_data['object_type']])) {
            $table_info = $thread_tables[$thread_data['object_type']];
            $object_name = db_get_field("SELECT " . $table_info['column'] . " FROM ?:" . $table_info['table'] . " WHERE " . $table_info['id'] . " = ?i AND lang_code = ?s", $thread_data['object_id'], $lang_code);
            if (!empty($object_name)) {
                $link = $thread_tables[$thread_data['object_type']]['link'] . $thread_data['object_id'];
            }
            $object_type = $thread_tables[$thread_data['object_type']]['lang_var'];
        }
    }
    return array($link, $object_name, $object_type);
}

function fn_cp_pr_get_additional_data_for_object($discussion, $lang_code = CART_LANGUAGE)
{
    $discussion['object_data'] = [];
    if (!empty($discussion) && !empty($discussion['object_id']) && !empty($discussion['object_type'])) {
        if ($discussion['object_type'] == 'A') {
            $discussion['object_data'] = db_get_row("SELECT page as name, description as descr FROM ?:page_descriptions WHERE page_id = ?i AND lang_code = ?s", $discussion['object_id'], $lang_code);
            $discussion['object_data']['main_pair'] = fn_get_image_pairs($discussion['object_id'], 'blog', 'M', true, true, $lang_code);
        } elseif ($discussion['object_type'] == 'B' && Registry::get('addons.cp_power_blog.status') == 'A') {
            $discussion['object_data'] = db_get_row("SELECT name, description as descr FROM ?:cp_blog_post_descriptions WHERE post_id = ?i AND lang_code = ?s", $discussion['object_id'], $lang_code);
            $discussion['object_data']['main_pair'] = fn_get_image_pairs($discussion['object_id'], 'cp_blog_post', 'M', true, true);
        } elseif ($discussion['object_type'] == 'C') {
            $discussion['object_data'] = db_get_row("SELECT category as name, description as descr FROM ?:category_descriptions WHERE category_id = ?i AND lang_code = ?s", $discussion['object_id'], $lang_code);
            $discussion['object_data']['main_pair'] = fn_get_image_pairs($discussion['object_id'], 'category', 'M', true, true, $lang_code);
        } elseif ($discussion['object_type'] == 'M') {
            $discussion['object_data'] = db_get_row("SELECT ?:companies.company as name, ?:company_descriptions.company_description as descr FROM ?:companies 
                LEFT JOIN ?:company_descriptions ON ?:company_descriptions.company_id AND ?:company_descriptions.lang_code = ?s WHERE ?:companies.company_id = ?i", $lang_code, $discussion['object_id']);
            $logos = fn_get_logos($discussion['object_id']);
            if (!empty($logos) && !empty($logos['theme'])) {
                $discussion['object_data']['main_pair'] = $logos['theme'];
            }
        } elseif ($discussion['object_type'] == 'P') {
//             $discussion['object_data'] = db_get_row("SELECT product as name, full_description as descr FROM ?:product_descriptions WHERE product_id = ?i AND lang_code = ?s", $discussion['object_id'], $lang_code);
//             $discussion['object_data']['main_pair'] = fn_get_image_pairs($discussion['object_id'], 'product', 'M', true, true, $lang_code);
            
            $discussion['object_data'] = fn_get_product_data($discussion['object_id'], Tygh::$app['session']['auth'], $lang_code, '', false, true, false, false, false, false, false, false);
        }
    }
    
    fn_set_hook('cp_pr_get_additional_data_post', $tables);
    
    return $discussion;
}

function fn_cp_pr_update_discussion_seo($object_data)
{
    if (!empty($object_data) && !empty($object_data['thread_id']) && !empty($object_data['object_type']) && $object_data['object_type'] == 'P') {
        $thread_tables = fn_cp_pr_thread_object_tables();
        if (!empty($thread_tables) && !empty($thread_tables[$object_data['object_type']])) {
            $table_info = $thread_tables[$object_data['object_type']];
            if (defined('CP_PR_VARIATIONS_TYPE') && CP_PR_VARIATIONS_TYPE == 'S' && !empty($object_data['cp_pr_replace_for_vars']) && $object_data['cp_pr_replace_for_vars'] == 'Y') {
                $child_vars_ids = db_get_fields("SELECT product_id FROM ?:product_variation_group_products WHERE parent_product_id = ?i", $object_data['object_id']);
                if (!empty($child_vars_ids)) {
                    $thread_ids = db_get_hash_array("SELECT thread_id, object_id FROM ?:discussion WHERE object_id IN (?n) AND object_type = ?s", 'object_id', $child_vars_ids, 'P');
                    $group_data = db_get_row("SELECT * FROM ?:discussion WHERE thread_id = ?i", $object_data['thread_id']);
                    if (!empty($group_data)) {
                        unset($group_data['thread_id']);
                        foreach($child_vars_ids as $var_pr_id) {
                            if (empty($thread_ids[$var_pr_id])) {
                                $put_var = $group_data;
                                $put_var['object_id'] = $var_pr_id;
                                $thread_ids[$var_pr_id]['thread_id'] = db_query("INSERT INTO ?:discussion ?e", $put_var);
                            }
                        }
                    }
                }
            }
            foreach (Languages::getAll() as $object_data['lang_code'] => $_v) {
                if ($object_data['lang_code'] == DESCR_SL) {
                    if ($object_data['object_type'] != 'E') {
                        
                        $product_id = db_get_field("SELECT object_id FROM ?:discussion WHERE thread_id = ?i AND object_type = ?s", $object_data['thread_id'], 'P');
                        if (!empty($product_id)) {
                            $object_data['name'] = db_get_field("SELECT name FROM ?:seo_names WHERE object_id = ?i AND type = ?s", $product_id, 'p');
                        } else {
                            $object_data['name'] = db_get_field("SELECT " . $table_info['column'] . " FROM ?:" . $table_info['table'] . " WHERE " . $table_info['id'] . " = ?i AND lang_code = ?s", $object_data['object_id'], $object_data['lang_code']);
                        }
                    } else {
                        if (empty($object_data['name'])) {
                            $object_data['name'] = db_get_field("SELECT name FROM ?:cp_pr_for_seo WHERE thread_id = ?i AND lng_code = ?s", $object_data['object_id'], $object_data['lang_code']);
                            if (empty($object_data['name'])) {
                                $object_data['name'] = 'store-reviews';
                            }
                        }
                    }

                    db_replace_into('cp_pr_for_seo', $object_data);
                    if (Registry::get('addons.seo.status') == 'A') {
                        fn_seo_update_object($object_data, $object_data['thread_id'], CP_PR_OBJECT_SEO_KEY, $object_data['lang_code']);
                    }
                    if (defined('CP_PR_VARIATIONS_TYPE') && CP_PR_VARIATIONS_TYPE == 'S' && !empty($object_data['cp_pr_replace_for_vars']) && $object_data['cp_pr_replace_for_vars'] == 'Y' && !empty($thread_ids)) {
                        foreach($thread_ids as $v_thread_id) {
                            $new_var_data = $object_data;
                            $new_var_data['thread_id'] = $v_thread_id['thread_id'];
                            
                            db_replace_into('cp_pr_for_seo', $new_var_data);
                        }
                    }
                }
            }
        }
    }
    return true;
}

function fn_cp_sitemap_get_cp_pr_threads()
{
    $threads = [];
    $addon_settings = Registry::get('addons.cp_power_reviews');
    if (!empty($addon_settings) && !empty($addon_settings['objects_for_sitemap'])) {
        $join = $condition = '';
        if ($addon_settings['with_reviews'] == 'Y') {
            $join .= db_quote(" INNER JOIN ?:discussion_posts ON ?:discussion_posts.thread_id = pr_disc.thread_id");
            $condition .= db_quote('pr_disc.thread_id > ?i AND pr_disc.type != ?s AND ?:discussion_posts.status = ?s AND ?:discussion_posts.cp_pr_user_delete = ?s', 0, 'D', 'A', 'N');
        } else {
            $condition .= db_quote('pr_disc.thread_id > ?i AND pr_disc.type != ?s', 0, 'D');
        }
        if (Registry::get('runtime.company_id') && fn_allowed_for('ULTIMATE')) {
            $condition .= db_quote(" AND pr_disc.company_id = ?i", Registry::get('runtime.company_id'));
            
        }
        foreach($addon_settings['objects_for_sitemap'] as $object_type => $val) {
            if ($val == 'Y') {
                $type_threads = [];
                $type_condition = $condition;
                if ($object_type == 'P') {
                    $type_condition .= db_quote(" AND ?:products.status = ?s", 'A');
                    $type_threads = db_get_array("SELECT DISTINCT(pr_disc.thread_id) FROM ?:products 
                        LEFT JOIN ?:discussion as pr_disc ON pr_disc.object_id = ?:products.product_id AND pr_disc.object_type = ?s ?p 
                        WHERE ?p", 'P', $join, $type_condition);
                } elseif ($object_type == 'C') {
                    $type_condition .= db_quote(" AND ?:categories.status = ?s", 'A');
                    $type_threads = db_get_array("SELECT DISTINCT(pr_disc.thread_id) FROM ?:categories 
                        LEFT JOIN ?:discussion as pr_disc ON pr_disc.object_id = ?:categories.category_id AND pr_disc.object_type = ?s ?p 
                        WHERE ?p", 'C', $join, $type_condition);
                } elseif ($object_type == 'A') {
                    $type_condition .= db_quote(" AND ?:pages.status = ?s", 'A');
                    $type_threads = db_get_array("SELECT DISTINCT(pr_disc.thread_id) FROM ?:pages 
                        LEFT JOIN ?:discussion as pr_disc ON pr_disc.object_id = ?:pages.page_id AND pr_disc.object_type = ?s ?p 
                        WHERE ?p", 'A', $join, $type_condition);
                } elseif ($object_type == 'B') {
                    $type_condition .= db_quote(" AND ?:cp_blog_posts.status = ?s", 'A');
                    $type_threads = db_get_array("SELECT DISTINCT(pr_disc.thread_id) FROM ?:cp_blog_posts 
                        LEFT JOIN ?:discussion as pr_disc ON pr_disc.object_id = ?:cp_blog_posts.post_id AND pr_disc.object_type = ?s ?p 
                        WHERE ?p", 'B', $join, $type_condition);
                } elseif ($object_type == 'M') {
                    $type_condition .= db_quote(" AND ?:companies.status = ?s", 'A');
                    $type_threads = db_get_array("SELECT DISTINCT(pr_disc.thread_id) FROM ?:companies 
                        LEFT JOIN ?:discussion as pr_disc ON pr_disc.object_id = ?:companies.company_id AND pr_disc.object_type = ?s ?p 
                        WHERE ?p", 'M', $join, $type_condition);
                }
                if (!empty($type_threads)) {
                    $threads = array_merge($threads, $type_threads);
                }
            }
        }
    }
    return $threads;
}

function fn_settings_variants_addons_cp_power_reviews_objects_for_sitemap()
{
    $result = array(
//         'A' => __('pages'),
//         'B' => __('cp_pr_blogs_text'),
//         'C' => __('categories'),
        'P' => __('products'),
    );
    if (fn_allowed_for('MULTIVENDOR')) {
//         $result['M'] = __('vendors');
    }
    
    fn_set_hook('cp_pr_setting_objects_for_sitemap', $result);
    
    return $result;
}

function fn_cp_pr_get_thread_recommendations($thread_id)
{
    $data = [];
    if (!empty($thread_id)) {
        $data = db_get_array("SELECT * FROM ?:cp_pow_recomends WHERE thread_id = ?i ORDER BY timestamp DESC", $thread_id);
        if (!empty($data)) {
            foreach($data as &$rec_data) {
                if (!empty($rec_data['ip_address'])) {
                    $rec_data['ip'] = fn_ip_from_db($rec_data['ip_address']);
                }
            }
        }
    }
    return $data;
}

function fn_cp_pr_delete_thread_recommendation($thread_id, $ip)
{
    if (!empty($thread_id) && !empty($ip)) {
        db_query("DELETE FROM ?:cp_pow_recomends WHERE thread_id = ?i AND ip_address = ?s", $thread_id, $ip);
    }
    return true;
}

function fn_cp_pr_delete_seo_settings()
{
    $seo_settings = array(
        'seo_cp_pr_review_type'
    );

    foreach ($seo_settings as $setting_name) {
        $setting_id = Settings::instance()->getId($setting_name);

        if (!empty($setting_id)) {
            $result = Settings::instance()->removeById($setting_id);
        }
    }

    return true;
}

function fn_cp_pr_get_discussion_ratings_revers()
{
    $rates = array(
        1 => __("poor"),
        2 => __("fair"),
        3 => __("average"),
        4 => __("very_good"),
        5 => __("excellent")
    );

    return $rates;
}
function fn_cp_pr_replace_preview_img($video_id, $lang_code = CART_LANGUAGE)
{
    if (!empty($video_id)) {
        $yuorube_id = db_get_field("SELECT youtube_id FROM ?:cp_pr_video_links WHERE video_id = ?i", $video_id);
        fn_delete_image_pairs($video_id, 'cp_pr_video_preview');
        if (!empty($yuorube_id)) {
            $url = str_replace('[VIDEO_ID]', $yuorube_id, CP_PR_YOUTUBE_PREVIEW_URL);
            fn_cp_pr_video_attach_image('url', $url, 'cp_pr_video_preview', $video_id, false, true, CART_LANGUAGE);
        }
    }
    return true;
}

function fn_cp_pr_get_view_type_langs()
{
    $extrem_types = array(
        'L' => __('cp_pr_limits_min_txt'),
        'LM' => __('cp_pr_below_mid'),
        'M' => __('cp_pr_limits_mid_txt'),
        'MT' => __('cp_pr_above_mid'),
        'T' => __('cp_pr_limits_max_txt'),
    );
    return $extrem_types;
}

function fn_cp_pr_get_frequency()
{
    $frequency = array(
        'always' => __('always'),
        'hourly' => __('hourly'),
        'daily' => __('daily'),
        'weekly' => __('weekly'),
        'monthly' => __('monthly'),
        'yearly' => __('yearly'),
        'never' => __('never'),
    );

    return $frequency;
}

function fn_cp_pr_get_priority()
{
    $priority = [];

    for ($i = 0.1; $i <= 1; $i += 0.1) {
        $priority[(string) $i] = (string) $i;
    }

    return $priority;
}

function fn_google_sitemap_write_test_reviews_to_sitemap(
    Storefront $storefront,
    $last_modified_time,
    $change_frequency,
    $priority,
    $file,
    $link_counter,
    $file_counter,
    $sitemap_header,
    $sitemap_footer,
    array $languages
) {
    fn_set_progress('step_scale', 1);
        
    $links = fn_google_sitemap_generate_link('cp_test_review', 0, $languages);
    $item = fn_google_sitemap_print_item_info($links, $last_modified_time, $change_frequency, $priority);

    fn_google_sitemap_check_counter($file, $link_counter, $file_counter, $links, $sitemap_header, $sitemap_footer, 'cp_test_reviews', $storefront);

    fwrite($file, $item);
    
    return [$file, $link_counter, $file_counter];
}

function fn_google_sitemap_write_all_reviews_to_sitemap(
    Storefront $storefront,
    $last_modified_time,
    $change_frequency,
    $priority,
    $file,
    $link_counter,
    $file_counter,
    $sitemap_header,
    $sitemap_footer,
    array $languages
) {
    fn_set_progress('step_scale', 1);
    
    if (fn_allowed_for('MULTIVENDOR')) {
        $cur_comp_id = 0;
    } else {
        $cur_comp_id = Registry::get('runtime.company_id');
    }
    $all_reviews_id = db_get_field("SELECT id FROM ?:cp_pr_reviews_seo WHERE company_id = ?i", $cur_comp_id);
        
    $links = fn_google_sitemap_generate_link('cp_all_review', $all_reviews_id, $languages);
    $item = fn_google_sitemap_print_item_info($links, $last_modified_time, $change_frequency, $priority);

    fn_google_sitemap_check_counter($file, $link_counter, $file_counter, $links, $sitemap_header, $sitemap_footer, 'cp_all_reviews', $storefront);

    fwrite($file, $item);
    
    return [$file, $link_counter, $file_counter];
}

function fn_google_sitemap_write_cp_product_reviews_to_sitemap(
    Storefront $storefront,
    $last_modified_time,
    $change_frequency,
    $priority,
    $file,
    $link_counter,
    $file_counter,
    $sitemap_header,
    $sitemap_footer,
    array $languages
) {
    $params = $_REQUEST;
    $params['custom_extend'] = array('categories');
    $params['sort_by'] = 'null';
    $params['get_conditions'] = true;
    $params['area'] = 'C';
    if ($storefront->getCompanyIds()) {
        $params['only_for_storefront_id'] = array_merge([0], $storefront->getCompanyIds());
    }

    $original_auth = Tygh::$app['session']['auth'];
    Tygh::$app['session']['auth'] = fn_fill_auth([], [], false, 'C');

    list($fields, $join, $condition) = fn_get_products($params, 0);
    
    $join .= db_quote(" INNER JOIN ?:discussion_posts ON ?:discussion_posts.thread_id = cp_disc.thread_id");
    $condition .= db_quote(' AND ?:discussion_posts.status = ?s AND ?:discussion_posts.cp_pr_user_delete = ?s', 'A', 'N');
    
    $thread_ids = db_get_fields("SELECT DISTINCT(cp_disc.thread_id) FROM ?:products as products
        LEFT JOIN ?:discussion as cp_disc ON cp_disc.object_id = products.product_id ?p
        WHERE cp_disc.object_type = ?s AND cp_disc.type != ?s ?p", $join, 'P', 'D', $condition);

    Tygh::$app['session']['auth'] = $original_auth;
    
    if (!empty($thread_ids)) {
    
        fn_set_progress('step_scale', count($thread_ids));
        
        foreach ($thread_ids as $thread_id) {
            $links = fn_google_sitemap_generate_link('cp_prod_review', $thread_id, $languages);
            $item = fn_google_sitemap_print_item_info($links, $last_modified_time, $change_frequency, $priority);

            fn_google_sitemap_check_counter($file, $link_counter, $file_counter, $links, $sitemap_header, $sitemap_footer, 'cp_prod_reviews', $storefront);

            fwrite($file, $item);
        }
    }
    

    return [$file, $link_counter, $file_counter];
}

function fn_cp_pr_update_own_review($p_id, $data)
{
    if (!empty($p_id) && !empty($data) && !empty($data['thread_id'])) {
        $addon_settings = Registry::get('addons.cp_power_reviews');
        $object_suffix = fn_cp_pr_get_object_suffixes();
        
        $object_type = db_get_field("SELECT object_type FROM ?:discussion WHERE thread_id = ?i", $data['thread_id']);
        $message_exist = db_get_fields("SELECT post_id FROM ?:discussion_messages WHERE post_id = ?i", $p_id);
        $rating_exist = db_get_fields("SELECT post_id FROM ?:discussion_rating WHERE post_id = ?i", $p_id);
        if (!empty($data['ratings'])) {
            $sred_rat = $counter = 0;
            foreach($data['ratings'] as $cp_attr_id => $rating) {
                if (!in_array($rating, array_keys(fn_get_discussion_ratings()))) {
                    unset($data['ratings'][$cp_attr_id]);
                } else {
                    $sred_rat = $sred_rat + $rating;
                    $counter = $counter + 1;
                }
            }
            if (!empty($sred_rat)) {
                $data['cp_sred_rate'] = $sred_rat/$counter;
                if (empty($data['cp_pr_common_rate_exist'])) {
                    $data['rating_value'] = floor($sred_rat/$counter);
                } elseif(!empty($data['rating_value'])) {
                    $data['cp_sred_rate'] = $data['rating_value'];
                }
            }
        }
        if (!empty($data['rating_value'])) {
            if (!in_array($data['rating_value'], array_keys(fn_get_discussion_ratings()))) { 
                unset($data['rating_value']);
            } else {
                $data['cp_sred_rate'] = $data['rating_value'];
            }
        }

        db_query("UPDATE ?:discussion_posts SET ?u WHERE post_id = ?i", $data, $p_id);

        if (in_array($p_id, $message_exist)) {
            db_query("UPDATE ?:discussion_messages SET ?u WHERE post_id = ?i", $data, $p_id);
        } else {
            $data['post_id'] = $p_id;
            db_query("INSERT INTO ?:discussion_messages ?e", $data);
        }

        if (in_array($p_id, $rating_exist)) {
            db_query("UPDATE ?:discussion_rating SET ?u WHERE post_id = ?i", $data, $p_id);
            
        } else {
            $data['post_id'] = $p_id;
            db_query("INSERT INTO ?:discussion_rating ?e", $data);
        }
        if (!empty($data['ratings']) && !empty($p_id)) {
            $post_status = db_get_field("SELECT status FROM ?:discussion_posts WHERE post_id = ?i", $p_id);
            foreach($data['ratings'] as $cp_attr_id => $rating) {
                $pt_data = array(
                    'cp_attr_id' => $cp_attr_id,
                    'post_id' => $p_id,
                    'rating' => $rating,
                    'post_status' => $post_status
                );
                db_query("REPLACE INTO ?:cp_pow_attr_ratings ?e", $pt_data);
            }
        }
        
        $pairs_data = fn_attach_image_pairs('cp_review_post', 'cp_rev_post', $p_id, CART_LANGUAGE);
        
        if (!empty($pairs_data)) {
            if (!is_array($pairs_data)) {
                $pairs_data = array($pairs_data);
            }
            foreach($pairs_data as $kry => $img_pair_id) {
                $data_rev_image = array(
                    'post_image_id' => $img_pair_id,
                    'post_id' => $p_id,
                    'status' => 'A'
                );
                $check_exists = db_get_field("SELECT post_image_id FROM ?:cp_review_images WHERE post_image_id = ?i AND post_id = ?i", $img_pair_id, $p_id);
                if (empty($check_exists)) {
                    db_query("INSERT INTO ?:cp_review_images ?e", $data_rev_image);
                }
            }
        }
        
        $pairs_data_n = fn_attach_image_pairs('cp_review_post_add', 'cp_rev_post', $p_id, CART_LANGUAGE);
        
        if (!empty($pairs_data_n)) {
            if (!is_array($pairs_data_n)) {
                $pairs_data_n = array($pairs_data_n);
            }
            foreach($pairs_data_n as $kry => $img_pair_id) {
                $data_rev_image = array(
                    'post_image_id' => $img_pair_id,
                    'post_id' => $p_id,
                    'status' => 'A'
                );
                $check_exists_n = db_get_field("SELECT post_image_id FROM ?:cp_review_images WHERE post_image_id = ?i AND post_id = ?i", $img_pair_id, $p_id);
                if (empty($check_exists_n)) {
                    db_query("INSERT INTO ?:cp_review_images ?e", $data_rev_image);
                }
            }
        }
        
        if (!empty($data['removed_image_pair_ids'])) {
            foreach($data['removed_image_pair_ids'] as $pair_id) {
                if (!empty($pair_id)) {
                    db_query("DELETE FROM ?:cp_review_images WHERE post_image_id = ?i AND post_id = ?i", $pair_id, $p_id);
                    fn_delete_image_pair($pair_id, 'cp_rev_post');
                }
            }
        }
        
        if (!empty($p_id) && !empty($data['youtube_id']) && isset($object_suffix[$object_type]) 
            && !empty($addon_settings['show_video_uploader' . $object_suffix[$object_type]]) && $addon_settings['show_video_uploader' . $object_suffix[$object_type]] == 'Y') 
        {
            $video_data = array(
                'youtube_id' => $data['youtube_id'],
                'post_id' => $p_id
            );
            $video_id = db_query("INSERT INTO ?:cp_pr_video_links ?e", $video_data);
            if (isset($data['upload_from_youtube']) && $data['upload_from_youtube'] == 'Y') {
                $url = str_replace('[VIDEO_ID]', $data['youtube_id'], CP_PR_YOUTUBE_PREVIEW_URL);
                fn_cp_pr_video_attach_image('url', $url, 'cp_pr_video_preview', $video_id, false, true, CART_LANGUAGE);
            } else {
                fn_attach_image_pairs('cp_pr_video_preview', 'cp_pr_video_preview', $video_id, CART_LANGUAGE);
            }
        }
        
    }
    return true;
}

function fn_cp_check_permissions_for_reviews($post_id, $type, $user_id)
{
    if (!empty($post_id) && !empty($type) && in_array($type, array('E','D')) && !empty($user_id)) {
        $post_data = db_get_row("SELECT timestamp, cp_pr_can_edit, user_id FROM ?:discussion_posts WHERE post_id = ?i", $post_id);
        if (!empty($post_data) && !empty($post_data['user_id']) && $post_data['user_id'] == $user_id) {
            $addon_settings = Registry::get('addons.cp_power_reviews');
            if ($type == 'D' && !empty($addon_settings['allow_review_d']) && $addon_settings['allow_review_d'] == 'Y') {
                return true;
            } elseif ($type == 'E' && !empty($addon_settings['allow_review_e']) && $addon_settings['allow_review_e'] == 'Y' && !empty($post_data['cp_pr_can_edit']) && $post_data['cp_pr_can_edit'] == 'Y') {
                if ((!empty($addon_settings['minutes_for_editing']) && $post_data['timestamp'] > (time() - 60*$addon_settings['minutes_for_editing'])) || (empty($addon_settings['minutes_for_editing']))) {
                    return true;
                }
            }
        }
    }
    fn_set_notification('E', __('error'), __('cp_pr_you_cand_edit_this_review'));
    return false;
}

function fn_cp_pr_switch_can_edit_review_trigger($post_id)
{
    if (!empty($post_id)) {
        db_query("UPDATE ?:discussion_posts SET cp_pr_can_edit = ?s WHERE post_id = ?i", 'N', $post_id);
    }
    return true;
}

function fn_cp_pr_delete_by_user($post_id)
{
    if (!empty($post_id)) {
        db_query("UPDATE ?:discussion_posts SET cp_pr_user_delete = ?s WHERE post_id = ?i", 'Y', $post_id);
    }
    return true;
}

function fn_cp_pr_create_reviews_page_seo_data($company_id)
{
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
            if (Registry::get('addons.seo.status') == 'A') {
                fn_seo_update_object($data_reviews, $reviews_id, CP_PR_REVIEWS_SEO, $lang_code);
            }
        }
    }
    return true;
}

function fn_cp_pr_get_reviews_page_data($lang_code = CART_LANGUAGE)
{
    if (fn_allowed_for('MULTIVENDOR')) {
        $company_id = 0;
    } else {
        $company_id = Registry::get('runtime.company_id');
    }
    $review_page_data = db_get_row("SELECT ?:cp_pr_reviews_seo.id, ?:cp_pr_reviews_seo_descr.name FROM ?:cp_pr_reviews_seo 
        LEFT JOIN ?:cp_pr_reviews_seo_descr ON ?:cp_pr_reviews_seo_descr.id = ?:cp_pr_reviews_seo.id AND ?:cp_pr_reviews_seo_descr.lang_code = ?s
        WHERE ?:cp_pr_reviews_seo.company_id = ?i", $lang_code, $company_id);
    
    if (Registry::get('addons.seo.status') == 'A') {
        $review_page_data['seo_name'] = fn_seo_get_name(CP_PR_REVIEWS_SEO, $review_page_data['id'], '', null, $lang_code);
    }
    
    return $review_page_data;
}

function fn_cp_pr_update_reviews_page_seo($id, $data, $lang_code = CART_LANGUAGE)
{
    if (!empty($id) && !empty($data['name'])) {
        $trim_name = trim($data['name']);
        if (!empty($trim_name)) {
            
            $data['id'] = $id;
            $data['name'] = $trim_name;
            $data['lang_code'] = $lang_code;
            db_replace_into('cp_pr_reviews_seo_descr', $data);
            
            if (Registry::get('addons.seo.status') == 'A') {
                fn_seo_update_object($data, $data['id'], CP_PR_REVIEWS_SEO, $data['lang_code']);
            }
        }
    }
    return true;
}

function fn_cp_pr_import_from_pro_reviews_info()
{
    $hint = '';
    
    $prod_reviews = Registry::get('addons.cp_power_reviews');
    if (!empty($prod_reviews)) {
        $site_url = fn_url('cp_pow_rev.inport_from_def', 'A');
        $hint = '<b>' . __('cp_pr_import_from_default_prod_rev') . ':</b> <a class="btn cm-ajax cm-comet" href="' . $site_url . '">' . __('cp_pr_import_txt') . '</a>';
        $hint .= '<div class="muted description">' . __('cp_pr_import_reviews_descr') . '</div>';
    }
    
    return $hint;
}

function fn_cp_pr_import_reviews_from_default()
{
    
    $default_params = [
        'page'              => 1,
        'items_per_page'    => 0,
        'load_product_data' => false,
    ];

    $fields = [
        '?:product_reviews.*',
        '?:users.company_id as reply_company_id',
        '?:companies.company as reply_company',
    ];

    $sortings = [
        'helpfulness'              => '?:product_reviews.helpfulness',
        'rating_value'             => '?:product_reviews.rating_value',
        'product_review_timestamp' => '?:product_reviews.product_review_timestamp',
    ];

    $params['available_filters'] = [
        'with_images',
        'only_buyers',
    ];
    
    $prod_rev_install_time = db_get_field("SELECT install_datetime FROM ?:addons WHERE addon = ?s", 'product_reviews');
    if (empty($prod_rev_install_time)) {
        return false;
    }

    $params = array_merge($default_params, $params);
    $params['page'] = (int) $params['page'];
    $params['items_per_page'] = (int) $params['items_per_page'];
    $params['message'] = isset($params['message']) ? trim($params['message']) : null;
    $params['comment'] = isset($params['comment']) ? trim($params['comment']) : null;
    $params['advantages'] = isset($params['advantages']) ? trim($params['advantages']) : null;
    $params['disadvantages'] = isset($params['disadvantages']) ? trim($params['disadvantages']) : null;

    $condition = $join = '';


    $join .= db_quote(
        ' LEFT JOIN ?:users'
            . ' ON ?:product_reviews.reply_user_id = ?:users.user_id'
        . ' LEFT JOIN ?:companies'
            . ' ON ?:users.company_id = ?:companies.company_id'
    );
    //$condition .= db_quote(" AND ?:product_reviews.product_review_timestamp > ?i AND ?:product_reviews.product_id > ?i", $prod_rev_install_time, 0);
    $condition .= db_quote(" AND ?:product_reviews.product_id > ?i", 0);
    $limit = '';
    if (!empty($params['items_per_page'])) {
        // FIXME must be COUNT(*)
        $params['total_items'] = count(db_get_fields('SELECT product_review_id FROM ?:product_reviews ?p WHERE 1 ?p GROUP BY ?:product_reviews.product_review_id', $join, $condition));
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }
    $order_by = db_sort($params, $sortings, 'product_review_timestamp', 'desc');

    $product_reviews = db_get_hash_array(
        'SELECT ?p'
        . ' FROM ?:product_reviews'
            . ' ?p'
        . ' WHERE 1 ?p'
        . ' GROUP BY ?:product_reviews.product_review_id'
        . ' ?p ?p',
        'product_review_id',
        implode(',', $fields),
        $join,
        $condition,
        $order_by,
        $limit
    );
    if (!empty($product_reviews)) {
    
        fn_set_progress('parts', count($product_reviews));
        fn_set_progress('step_scale', 1);
        fn_set_progress('title', __('cp_pr_import_default_reviews'));
        
        $default_store_id = db_get_field("SELECT storefront_id FROM ?:storefronts WHERE is_default = ?s", 'Y');
        
        foreach ($product_reviews as $product_review_id => &$product_review) {
            $thread_id = db_get_field("SELECT thread_id FROM ?:discussion WHERE object_id = ?i AND object_type = ?s", $product_review['product_id'], 'P');
            $check_exists = db_get_field("SELECT post_id FROM ?:discussion_posts WHERE cp_product_review_id = ?i", $product_review['product_review_id']);
            if (!empty($check_exists)) {
                continue;
            }
            if (!empty($thread_id)) {
                fn_set_progress('echo', $product_review['product_review_id']);
                    
                $vote_tdata = db_get_hash_array(
                    'SELECT'
                        . ' (CASE'
                            . " WHEN(value > 0) THEN 'vote_up'"
                            . " ELSE 'vote_down'"
                        . ' END) AS vote_type,'
                        . ' COUNT(1) count'
                    . ' FROM ?:product_review_votes'
                    . ' WHERE product_review_id = ?i'
                    . ' GROUP BY vote_type',
                    'vote_type',
                    $product_review_id
                );
                $product_review['vote_up'] = empty($vote_tdata['vote_up']['count']) ? 0 : $vote_tdata['vote_up']['count'];
                $product_review['vote_down'] = empty($vote_tdata['vote_down']['count']) ? 0 : $vote_tdata['vote_down']['count'];
                
                $reply_name = '';
                if (!empty($product_review['reply_user_id'])) {
                    $user_name = db_get_row("SELECT firstname, lastname FROM ?:users WHERE user_id = ?i", $product_review['reply_user_id']);
                    if (!empty($user_name)) {
                        $reply_name = $user_name['firstname'] . ' ' . $user_name['lastname'];
                    }
                }
                
                $post_data = [
                    'thread_id'             => $thread_id,
                    'object_type'           => 'P',
                    'name'                  => !empty($product_review['name']) ? $product_review['name'] : __('cp_pr_anonym_customer'),
                    'rating_value'          => $product_review['rating_value'],
                    'message'               => $product_review['comment'],
                    'cp_pr_advantages'      => $product_review['advantages'],
                    'cp_pr_disadvantages'   => $product_review['disadvantages'],
                    'status'                => $product_review['status'],
                    'ip_address'            => isset($product_review['ip_address']) ? $product_review['ip_address'] : '',
                    'user_id'               => $product_review['user_id'],
                    'timestamp'             => $product_review['product_review_timestamp'],
                    'cp_pos_post'           => $product_review['vote_up'],
                    'cp_neg_post'           => $product_review['vote_down'],
                    'cp_admin_answ'         => !empty($product_review['reply']) ? $product_review['reply'] : '',
                    'cp_admin_id'           => $reply_name,
                    'cp_admin_answ_time'    => !empty($product_review['reply_timestamp']) ? $product_review['reply_timestamp'] : 0,
                    'cp_sred_rate'          => $product_review['rating_value'],
                    'cp_product_review_id'  => $product_review['product_review_id'],
                    'storefront_id'         => !empty($product_review['storefront_id']) ? $product_review['storefront_id'] : $default_store_id
                ];
                $is_update = false;
                if ($product_review['product_review_timestamp'] < $prod_rev_install_time) {
                    $post_data['post_id'] = db_get_field("SELECT post_id FROM ?:discussion_posts WHERE thread_id = ?i AND timestamp = ?i", $thread_id, $product_review['product_review_timestamp']);
                    if (empty($post_data['post_id'])) {
                        $post_data['post_id'] = db_query("INSERT INTO ?:discussion_posts ?e", $post_data);
                    } else {
                        $is_update = true;
                    }
                } else {
                    $post_data['post_id'] = db_query("INSERT INTO ?:discussion_posts ?e", $post_data);
                }
                if (!empty($post_data['post_id'])) {
                    if (!empty($is_update)) {
                        db_query("UPDATE ?:discussion_posts SET ?u WHERE post_id = ?i", $post_data, $post_data['post_id']);
                        db_query("UPDATE ?:discussion_messages SET ?u WHERE post_id = ?i", $post_data, $post_data['post_id']);
                        db_query("UPDATE ?:discussion_rating SET ?u WHERE post_id = ?i", $post_data, $post_data['post_id']);
                    } else {
                        db_query("REPLACE INTO ?:discussion_messages ?e", $post_data);
                        db_query("REPLACE INTO ?:discussion_rating ?e", $post_data);
                    }
                    
                    db_replace_into('cp_pr_reviews_storefronts', $post_data);
                    
                    fn_cp_pr_clone_image_pairs_for_reviews($post_data['post_id'], 'cp_rev_post', $product_review['product_review_id'], 'product_reviews');
                }
            }

        }
        unset($product_review);
    }
    fn_set_notification('N', __('notice'), __('cp_pr_reviews_was_inported'));
    
    return true;
}

function fn_cp_pr_clone_image_pairs_for_reviews($target_object_id, $target_object_type, $from_object_id, $from_object_type, $lang_code = CART_LANGUAGE)
{
    // Get all pairs
    $pair_data = db_get_hash_array(
        'SELECT pair_id, image_id, detailed_id, type, position FROM ?:images_links WHERE object_id = ?i AND object_type = ?s',
        'pair_id', $from_object_id, $from_object_type
    );

    if (empty($pair_data)) {
        return false;
    }

    $icons = $detailed = $pairs_data = [];

    foreach ($pair_data as $pair_id => $p_data) {
        if (!empty($p_data['image_id'])) {
            $icons[$pair_id] = fn_get_image($p_data['image_id'], $from_object_type, $lang_code, true);

            if (!empty($icons[$pair_id])) {
                $p_data['image_alt'] = empty($icons[$pair_id]['alt']) ? '' : $icons[$pair_id]['alt'];

                $tmp_name = fn_create_temp_file();
                Storage::instance('images')->export($icons[$pair_id]['relative_path'], $tmp_name);
                $name = fn_basename($icons[$pair_id]['image_path']);

                $icons[$pair_id] = array(
                    'path' => $tmp_name,
                    'size' => filesize($tmp_name),
                    'error' => 0,
                    'name' => $name,
                    'clone_from' => $p_data['image_id'],
                );
            }
        }
        if (!empty($p_data['detailed_id'])) {
            $detailed[$pair_id] = fn_get_image($p_data['detailed_id'], 'detailed', $lang_code, true);
            if (!empty($detailed[$pair_id])) {
                $p_data['detailed_alt'] = empty($detailed[$pair_id]['alt']) ? '' : $detailed[$pair_id]['alt'];

                $tmp_name = fn_create_temp_file();
                Storage::instance('images')->export($detailed[$pair_id]['relative_path'], $tmp_name);

                $name = fn_basename($detailed[$pair_id]['image_path']);

                $detailed[$pair_id] = array(
                    'path' => $tmp_name,
                    'size' => filesize($tmp_name),
                    'error' => 0,
                    'name' => $name,
                    'clone_from' => $p_data['detailed_id'],
                );
            }
        }

        $pairs_data = array(
            $pair_id => array(
                'type' => $p_data['type'],
                'image_alt' => (!empty($p_data['image_alt'])) ? $p_data['image_alt'] : '',
                'detailed_alt' => (!empty($p_data['detailed_alt'])) ? $p_data['detailed_alt'] : '',
                'position' => $p_data['position']
            )
        );

        $pair_dis = fn_update_image_pairs($icons, $detailed, $pairs_data, $target_object_id, $target_object_type, [], true, $lang_code, true);
        
        if (!empty($pair_dis)) {
            foreach($pair_dis as $img_pair_id) {
                $data_rev_image = array(
                    'post_image_id' => $img_pair_id,
                    'post_id'       => $target_object_id,
                    'status'        => 'A'
                );
                db_query("INSERT INTO ?:cp_review_images ?e", $data_rev_image);
            }
        }
        
    }
    return true;
}

function fn_cp_pr_get_default_storefront_id()
{
    $default_store_id = db_get_field("SELECT storefront_id FROM ?:storefronts WHERE is_default = ?s", 'Y');
    return $default_store_id;
}

function fn_cp_pr_get_alt_reviews($template = [])
{
    $reviews = [];
    $item_ids = null;
    if (!empty($template['conditions']['conditions'])) {
        $query_conditions = '';
        $join = db_quote(" LEFT JOIN ?:discussion ON ?:discussion.thread_id = ?:discussion_posts.thread_id");
        $condition_data = $template['conditions'];
        $set = !empty($condition_data['set']) ? $condition_data['set'] : 'all';
        $unite_operator = ($set == 'all') ? ' AND ' : ' OR ';
        foreach ($condition_data['conditions'] as $condition) {
            if (empty($condition['value'])) {
                continue;
            }
            $db_operator = (!empty($condition['operator']) && $condition['operator'] == 'nin') ? 'NOT IN' : 'IN';
            $item_ids = is_array($condition['value']) ? $condition['value'] : explode(',', $condition['value']);
            $query_conditions[] = db_quote('?:discussion_posts.post_id ?p (?n)', $db_operator, $item_ids);
        }
        $company_condition = !empty($template['company_id']) ? db_quote(' AND ?:discussion.company_id = ?i', $template['company_id']) : '';
        $item_ids = db_get_fields(
            'SELECT DISTINCT ?:discussion_posts.post_id ?p FROM ?:discussion_posts WHERE 1 ?p AND (?p)',
            $join, $company_condition, implode($unite_operator, $query_conditions)
        );
    }

    if (!empty($item_ids) || $item_ids === null) {
        $object_params = [
            'post_ids'          => $item_ids,
            'cp_pr_with_images' => true,
            'cp_is_all_page'    => true
        ];
        if (!empty($template['settings']['only_new']) && $template['settings']['only_new'] == 'Y') {
            $object_params['cp_ait_only_new'] = 'Y';
            if (!empty($template['img_renamed'])) {
                $object_params['cp_ait_only_new'] = 'A';
            }
        }
        list($reviews, ) = fn_get_discussions($object_params, 0);
        if (!empty($reviews)) {
            $thread_tables = fn_cp_pr_thread_object_tables();
            foreach($reviews as &$rev_data) {
                if (!empty($thread_tables) && !empty($thread_tables[$rev_data['object_type']]) && !empty($rev_data['cp_review_pairs'])) {
                    $rev_data['image_pairs'] = $rev_data['cp_review_pairs'];
                    unset($rev_data['cp_review_pairs']);
                    $table_info = $thread_tables[$rev_data['object_type']];
                    $rev_data['object_name'] = db_get_field("SELECT " . $table_info['column'] . " FROM ?:" . $table_info['table'] . " WHERE " . $table_info['id'] . " = ?i AND lang_code = ?s", $rev_data['object_id'], CART_LANGUAGE);
                    
                }
            }
        }
    }
    return $reviews;
}

function fn_cp_pr_get_all_group_vars_for_pid($p_id, $make_array = false, $all = false)
{
    if (!empty($make_array)) {
        $parent_product_ids = [];
    } else {
        $parent_product_ids = '';
    }
    if (!empty($p_id)) {
        $product_id_map = ProductVariationsServiceProvider::getProductIdMap();
        $group_repository = ProductVariationsServiceProvider::getGroupRepository();

        $group = $group_repository->findGroupByProductId($p_id);
        
        if (!$group instanceof VariationsGroup) {
            return;
        }

        $group_product_ids = $group->getProductIds();
        if (!empty($all)) {
            if (!empty($make_array)) {
                return $group_product_ids;
            } else {
                return implode(',', $group_product_ids);
            }
        } else {
            foreach ($group_product_ids as $group_product_id) {
                if (!$product_id_map->isParentProduct($group_product_id)) {
                    continue;
                }
                if (!empty($make_array)) {
                    $parent_product_ids[] = $group_product_id;
                } else {
                    if (!empty($parent_product_ids)) {
                        $parent_product_ids .= ',';
                    }
                    $parent_product_ids .= $group_product_id;
                }
            }
        }
    }
    return $parent_product_ids;
}

function fn_cp_pr_get_posts_for_diff_variations($params, $parent_product_ids)
{
    if (empty($parent_product_ids)) {
        return [[], []];
    }
    $params['not_this_types'] = 'D';
    $params['status'] = 'A';
    $params['object_ids'] = $parent_product_ids;
    $params['object_type'] = 'P';
    
    $all_p_limit = !empty($params['limit']) ? $params['limit'] : Registry::get('addons.discussion.product_posts_per_page');
    if (isset($params['type'])) {
        unset($params['type']);
    }
    if (isset($params['object_id'])) {
        unset($params['object_id']);
    }
    if (isset($params['thread_id'])) {
        unset($params['thread_id']);
    }
    list($all_posts, $all_post_search) = fn_cp_power_reviews_get_power_reviews($params, $all_p_limit);
    return [$all_posts, $all_post_search];
}

function fn_cp_power_reviews_set_disc_to_vars()
{
    if (defined('CP_PR_VARIATIONS_TYPE') && CP_PR_VARIATIONS_TYPE != 'N') {
        $company_id = Registry::get('runtime.company_id');
        $simple_ult = Registry::get('runtime.simple_ultimate');
        if (!empty($company_id) || $simple_ult || fn_allowed_for('MULTIVENDOR')) {
            $dir            = Registry::get('config.dir.root');
            $url            = Registry::get('config.current_location');
            $admin_index    = Registry::get('config.admin_index');
            $company_id     = !empty($simple_ult) ? fn_get_default_company_id() : $company_id;
            
            if (fn_allowed_for('MULTIVENDOR')) {
                $more_args_link = '';
            } else {
                $more_args_link = '&company_id=' . $company_id . '&switch_company_id=' . $company_id;
            }
            $hint = '<strong>' . __('cp_pr_apply_vars_info_hint') . ':</strong><br />';
            $hint .= '<a href="' . $url . '/' . $admin_index . '?dispatch=cp_pow_rev.apply_to_vars' . $more_args_link . '" class="btn cm-ajax cm-comet">' . __('apply') . '</a>';
        
            return $hint;
        } else {
            return '<strong>' . __('cp_pr_select_store') . '</strong>';
        }
    } else {
        return '';
    }
}

function fn_cp_pr_set_vars_from_parent($params = [])
{
    if (defined('CP_PR_VARIATIONS_TYPE') && CP_PR_VARIATIONS_TYPE != 'N') {
        $condition = '';
        if (!empty($params['company_id'])) {
            $condition .= db_quote(" AND ?:discussion.company_id = ?i", $params['company_id']);
        }
        
        $all_parents_ids = db_get_array("SELECT ?:discussion.* FROM ?:discussion 
            LEFT JOIN ?:product_variation_group_products as pvgp ON pvgp.product_id = ?:discussion.object_id 
            WHERE ?:discussion.object_type = ?s AND pvgp.parent_product_id = ?i ?p", 'P', 0, $condition
        );
        if (!empty($all_parents_ids)) {
            fn_set_progress('parts', count($all_parents_ids));
            fn_set_progress('step_scale', 1);
            fn_set_progress('title', __('cp_pr_import_default_reviews'));
            
            foreach($all_parents_ids as $per_data) {
                fn_set_progress('echo', $per_data['object_id']);
                $childs = db_get_fields("SELECT pvgp.product_id FROM ?:product_variation_group_products as pvgp 
                    LEFT JOIN ?:discussion ON ?:discussion.object_id = pvgp.product_id AND ?:discussion.object_type = ?s
                    WHERE pvgp.parent_product_id = ?i AND ?:discussion.thread_id IS NULL", 'P', $per_data['object_id']);
                if (!empty($childs)) {
                    foreach($childs as $ob_id) {
                        $put_data = $per_data;
                        $put_data['object_id'] = $ob_id;
                        unset($put_data['thread_id']);
                        
                        db_query("INSERT INTO ?:discussion ?e", $put_data);
                    }
                }
            }
        }
    }
    return true;
}

include_once(Registry::get('config.dir.addons') . 'cp_power_reviews/src/more_funcs.php');
