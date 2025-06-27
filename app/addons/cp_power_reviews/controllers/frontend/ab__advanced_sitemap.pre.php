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

if (Registry::get('addons.ab__advanced_sitemap.status') == 'A') {
    $path = fn_ab__as_get_sitemap_dir(Tygh::$app['storefront']->storefront_id);
    if (in_array($mode, ['product_reviews'])) {
        $filename = $path . $mode . intval($_REQUEST['page']) . '.xml';
    }
    if (!empty($filename) && file_exists($filename)) {
        header('Content-Type: text/xml;charset=utf-8');
        readfile($filename);
        exit();
    }
}