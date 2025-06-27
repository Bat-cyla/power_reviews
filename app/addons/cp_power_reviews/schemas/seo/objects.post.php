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

if (!defined('CP_PR_REVIEWS_SEO')) {
    fn_define('CP_PR_REVIEWS_SEO', 'r');
}
if (!defined('CP_PR_OBJECT_SEO_KEY')) {
    fn_define('CP_PR_OBJECT_SEO_KEY', 'd');
}
$schema[CP_PR_REVIEWS_SEO] = [
    'tree'          => true,
    'tree_options'  => ['cp_pr_reviews','cp_pr_reviews_nohtml'],
    'path_function' => function ($object_id, $company_id = 0) {
        return '';
    },
    'table'         => '?:cp_pr_reviews_seo_descr',
    'description'   => 'name',
    'dispatch'      => 'cp_pow_rev.all_reviews',
    'item'          => 'id',
    'not_shared'    => true,
    'condition'     => '',
    'name'          => 'name',
    'html_options'  => ['cp_pr_reviews'],
    'option'        => 'seo_cp_pr_reviews_page',
    'pager'         => true,
    'exist_function'=> function($id) {
        return db_get_field('SELECT 1 FROM ?:cp_pr_reviews_seo WHERE id = ?i', $id);
    },
];
$schema[CP_PR_OBJECT_SEO_KEY] = [
    'tree'          => true,
    'tree_options'  => ['cp_pr_review','cp_pr_review_nohtml'],
    'path_function' => function ($object_id, $company_id = 0, $lang_code = CART_LANGUAGE ) {
        if (fn_allowed_for('MULTIVENDOR')) {
            $company_id = 0;
        }
        $path = db_get_field("SELECT id FROM ?:cp_pr_reviews_seo 
            WHERE 1 ?p ", fn_get_seo_company_condition('?:cp_pr_reviews_seo.company_id', '', $company_id));
        return $path;
    },
    'exist_function' => function ($object_id) {
        $query = db_quote ( "SELECT * FROM ?:cp_pr_for_seo WHERE thread_id = ?i", $object_id);
        $res = db_get_row( $query );
        if (!empty($res)) {
            return $res;
        } else {
            return false;
        }
    },
    'parent_type'   => CP_PR_REVIEWS_SEO,
    'table'         => '?:cp_pr_for_seo',
    'description'   => 'name',
    'dispatch'      => 'cp_pow_rev.view',
    'item'          => 'thread_id',
    'condition'     => '',
    'not_shared'    => true,
    'name'          => 'name',
    'html_options'  => ['cp_pr_review'],
    'option'        => 'seo_cp_pr_review_type',
    'skip_lang_condition' => false,
];
return $schema;