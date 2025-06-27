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

$schema['cp_pow_rev']['all_reviews'] = array(
    'base_url' => 'cp_pow_rev.all_reviews?id=[id]',
    'request_handlers' => array(
        'id' => true
    ),
    'search' => true
);
$schema['cp_pow_rev']['store_reviews'] = array(
    'base_url' => 'cp_pow_rev.store_reviews',
    'search' => false
);
$schema['cp_pow_rev']['view'] = array(
    'base_url' => 'cp_pow_rev.view?thread_id=[thread_id]',
    'request_handlers' => array(
        'thread_id' => true
    ),
    'search' => false
);

return $schema;
