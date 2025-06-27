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

$menu = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'cp_pow_rev.attr_manage',
    'position' => 600,
);

if (isset($schema['central']['cart_power_addons'])) {
    $schema['central']['cart_power_addons']['items']['reviews_rating_attr'] = $menu;
} else {
    $schema['central']['website']['items']['comments_and_reviews']['subitems']['reviews_rating_attr'] = $menu;
    
    if (Registry::get('addons.seo.status') == 'A') {
        $schema['central']['website']['items']['comments_and_reviews']['subitems']['cp_reviews_page_seo'] = array(
            'attrs' => array(
                'class'=>'is-addon'
            ),
            'href' => 'cp_pow_rev.reviews_page_seo',
            'position' => 650,
        );
    }
}
return $schema;



