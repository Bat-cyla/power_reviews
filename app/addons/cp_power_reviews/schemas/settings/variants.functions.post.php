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

function fn_settings_variants_addons_cp_power_reviews_product_reviews_change()
{
    return fn_cp_pr_get_frequency();
}

function fn_settings_variants_addons_cp_power_reviews_product_reviews_priority()
{
    return fn_cp_pr_get_priority();
}

function fn_settings_variants_addons_cp_power_reviews_test_reviews_change()
{
    return fn_cp_pr_get_frequency();
}

function fn_settings_variants_addons_cp_power_reviews_test_reviews_priority()
{
    return fn_cp_pr_get_priority();
}

function fn_settings_variants_addons_cp_power_reviews_all_reviews_change()
{
    return fn_cp_pr_get_frequency();
}

function fn_settings_variants_addons_cp_power_reviews_all_reviews_priority()
{
    return fn_cp_pr_get_priority();
}

if (!function_exists('fn_cp_pr_get_frequency')) {
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
}
if (!function_exists('fn_cp_pr_get_priority')) {
    function fn_cp_pr_get_priority()
    {
        $priority = array();

        for ($i = 0.1; $i <= 1; $i += 0.1) {
            $priority[(string) $i] = (string) $i;
        }

        return $priority;
    }
}