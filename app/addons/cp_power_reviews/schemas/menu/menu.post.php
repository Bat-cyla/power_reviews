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
    'position' => 605,
);

$menu_reply = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'cp_pow_rev.premoderation',
    'position' => 700,
);

$discussion_menu = [
    'attrs' => [
        'class'=>'is-addon'
    ],
    'href' => 'discussion_manager.manage',
    'position' => 601,
    'title' => __('discussion.comments_and_reviews_menu'),
];

if (isset($schema['central']['cart_power_addons'])) {
    $schema['central']['cart_power_addons']['items']['reviews_rating_attr'] = $menu;
    if (Registry::get('addons.cp_power_reviews.allow_reply_rev') == 'Y') {
        $schema['central']['cart_power_addons']['items']['cp_power_reviews.reply_moder'] = $menu_reply;
    }
} else {
    $schema['central']['website']['items']['comments_and_reviews']['subitems']['reviews_rating_attr'] = $menu;
    if (version_compare(PRODUCT_VERSION, '4.18.1', '>=')) {
        $schema['central']['website']['items']['comments_and_reviews']['subitems']['comments_and_reviews_menu'] = $discussion_menu;
    }
    if (Registry::get('addons.cp_power_reviews.allow_reply_rev') == 'Y') {
        $schema['central']['website']['items']['comments_and_reviews']['subitems']['cp_power_reviews.reply_moder'] = $menu_reply;
    }
    
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



