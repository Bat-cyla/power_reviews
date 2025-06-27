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

$schema = array( 
    'reviews_count' => [
        'pl_body'       => '{{ reviews_count }}',
        'field'         => 'search///total_items',
        'description'   => 'cp_pl_reviews_total_count',
        'extra_lang'    => 'cp_pr_n_reviews',
    ],
    'object_name' => [
        'pl_body'       => '{{ object_name }}',
        'field' => 'object_data///product',
        'description' => 'cp_pl_object_reviews_name'
    ]
);

return $schema;
