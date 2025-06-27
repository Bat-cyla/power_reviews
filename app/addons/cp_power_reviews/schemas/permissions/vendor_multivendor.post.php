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

$schema['controllers']['cp_pow_rev'] = array (
    'modes' => array(
        'attr_manage' => array(
            'permissions' => true
        ),
        'attr_update' => array(
            'permissions' => true
        ),
		'm_update_attrs' => array(
            'permissions' => true
        ),
        'm_delete_attrs' => array(
            'permissions' => true
        ),
        'delete_attr' => array(
            'permissions' => true
        ),
        'apply_attr_to_pr' => array(
            'permissions' => true
        ),
        'apply_attr_to_prods' => array(
            'permissions' => true
        ),
        'apply_attr_to_cat' => array(
            'permissions' => true
        ),
        'apply_attr_to_categors' => array(
            'permissions' => true
        ),
        'premoderation' => array(
            'permissions' => true
        ),
        'premoderation_popup' => array(
            'permissions' => true
        )
    ),
    'permissions' => false,
);

$schema['controllers']['discussion']['modes']['cp_reply_vendor'] = array (
    'permissions' => true
);

return $schema;
