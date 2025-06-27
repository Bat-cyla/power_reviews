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

if ($mode == 'view' || $mode == 'quick_view') {
    $product = Registry::get('view')->getTemplateVars('product');
    //Add total posts to tab title
    $addons = Registry::get('addons');
    $skip = false;
    if (!empty($addons['abt__unitheme2']) && $addons['abt__unitheme2']['status'] == 'A') {
        $skip = true;
    }
    if (!empty($product) && !empty($product['discussion']['search']['total_items']) && empty($skip)) {
        $tabs = Registry::get('navigation.tabs');
        if (!empty($tabs) && !empty($tabs['discussion'])) {
            $tabs['discussion']['title'] .= ' (' . $product['discussion']['search']['total_items'] . ')';
            Registry::set('navigation.tabs', $tabs);
        }
    }
}
