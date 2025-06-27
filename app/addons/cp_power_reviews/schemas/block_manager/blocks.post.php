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

if (fn_allowed_for('MULTIVENDOR')) {
    $schema['cp_vendor_attr_rating'] = [
        'templates' => [
            'addons/cp_power_reviews/blocks/cp_vendor_attr_rating.tpl' => [],
        ],
        'wrappers' => 'blocks/wrappers',
        'content' => [
            'cp_attr_data' => [
                'type'      => 'function',
                'function'  => ['fn_cp_power_reviews_bl_get_vendor_attr_info'],
            ]
        ],
        'cache' => [
            'update_handlers'   => ['companies', 'company_descriptions', 'logos', 'images_links', 'images', 'discussion', 'discussion_posts'],
            'request_handlers'  => ['company_id']
        ],
    ];
    $schema['cp_vendor_logo_rating'] = [
        'templates' => [
            'addons/cp_power_reviews/blocks/cp_vendor_logo_rating.tpl' => [],
        ],
        'wrappers'  => 'blocks/wrappers',
        'content'   => [
            'vendor_info' => [
                'type'      => 'function',
                'function'  => ['fn_cp_power_reviews_bl_get_vendor_info'],
            ]
        ],
        'cache' => [
            'update_handlers' => ['companies', 'company_descriptions', 'logos', 'images_links', 'images', 'discussion', 'discussion_posts'],
            'request_handlers'=> ['company_id']
        ],
        'settings' => [
            'cp_show_vend_name' => [
                'type'          => 'checkbox',
                'default_value' => 'N'
            ],
            'cp_show_vend_btn' => [
                'type'          => 'checkbox',
                'default_value' => 'N'
            ],
            'cp_show_vend_rate' => [
                'type'          => 'checkbox',
                'default_value' => 'N'
            ],
        ],
    ];
}
$schema['cp_prev_power_reviews'] = [
    'content' => [
        'items' => [
            'remove_indent' => true,
            'hide_label'    => true,
            'type'          => 'enum',
            'object'        => 'power_reviews',
            'items_function'=> 'fn_cp_power_reviews_get_power_reviews',
            'fillings' => [
                'manually' => [
                    'picker'        => 'addons/cp_power_reviews/pickers/power_reviews/picker.tpl',
                    'picker_params' => [
                        'type' => 'links',
                    ],
                    'params' => [
                        'sort_by'   => 'timestamp',
                        'sort_order'=> 'desc'
                    ]
                ],
                'prod_reviews_from_current_object' => [
                    'params' => [
                        'sort_by'   => 'timestamp',
                        'sort_order'=> 'desc',
                        'request'   => [
                            'cid' => '%CATEGORY_ID%',
                        ]
                    ]
                ],
                'cp_pr_reviews' => [
                    'params' => [
                        'sort_by'   => 'timestamp',
                        'sort_order'=> 'desc',
                        'request'   => [
                            'ccid' => '%CATEGORY_ID%',
                        ]
                    ]
                ],
                'cp_pr_testimonials' => [
                    'params' => [
                        'sort_by'   => 'timestamp',
                        'sort_order'=> 'desc'
                    ]
                ],
                'cp_pr_pages' => [
                    'params' => [
                        'sort_by'   => 'timestamp',
                        'sort_order'=> 'desc',
                        'request'   => [
                            'pgid' => '%PAGE_ID%'
                        ]
                    ]
                ],
                'cp_pr_categories' => [
                    'params' => [
                        'sort_by'   => 'timestamp',
                        'sort_order'=> 'desc',
                        'request'   => [
                            'cid' => '%CATEGORY_ID%',
                        ]
                    ]
                ],
            ],
        ],
    ],
    'templates' => [
        'addons/cp_power_reviews/blocks/slider.tpl' => [
        'settings' => [
                'limit' => [
                    'type'          => 'input',
                    'default_value' => '20'
                ],
                'cp_last_days' => [
                    'type'          => 'input',
                    'default_value' => '10'
                ], 
                'cp_fill_type' => [
                    'type'  => 'selectbox',
                    'values'=> [
                        'RND' => 'random',
                        'TPR' => 'cp_sort_hight_rate',
                        'LWR' => 'cp_sort_low_rate',
                        'MSH' => 'cp_sort_most_help',
                        'NEW' => 'cp_sort_newest',
                    ],
                    'default_value' => 'random'
                ],
                'max_rating_limit' => [
                    'type'          => 'input',
                    'default_value' => '5'
                ],
                'min_rating_limit' => [
                    'type'          => 'input',
                    'default_value' => '1'
                ],
                'cp_sort_attributes_only' => [
                    'type'          => 'checkbox',
                    'default_value' => 'ATR'
                ],
                'show_object_icon' => [
                    'type'          => 'checkbox',
                    'default_value' => 'Y'
                ],  
                'object_image_width' => [
                    'type'          => 'input',
                    'default_value' => '80'
                ], 
                'object_image_height' => [
                    'type'          => 'input',
                    'default_value' => '80'
                ],
                'show_review_attrs' => [
                    'type'          => 'checkbox',
                    'default_value' => 'Y'
                ],
                'cp_pr_show_msg_title' => [
                    'type'          => 'checkbox',
                    'default_value' => 'N'
                ],
                'cp_pr_show_adv' => [
                    'type'          => 'checkbox',
                    'default_value' => 'N'
                ],
                'cp_pr_message_height' =>  [
                    'type'          => 'input',
                    'default_value' => 200
                ],
                'not_scroll_automatically' => [
                    'type'          => 'checkbox',
                    'default_value' => 'N'
                ],
                'speed' =>  [
                    'type'          => 'input',
                    'default_value' => 400
                ],
                'pause_delay' =>  [
                    'type'          => 'input',
                    'default_value' => 3
                ],
                'item_quantity' =>  [
                    'type'          => 'input',
                    'default_value' => 3
                ],
                'outside_navigation' => [
                    'type'          => 'checkbox',
                    'default_value' => 'Y'
                ],
                'cp_show_view_all_btn' => [
                    'type'          => 'checkbox',
                    'default_value' => 'N'
                ],
            ], 
        ],
    ],
    'wrappers' => 'blocks/wrappers'
];
if (fn_allowed_for('MULTIVENDOR')) {
    $schema['cp_prev_power_reviews']['content']['items']['fillings']['cp_pr_vendors'] = [
        'params' => [
            'sort_by'   => 'timestamp',
            'sort_order'=> 'desc',
            'request'   => [
                'cmpid' => '%COMPANY_ID%',
                'cpid' => '%PRODUCT_ID%',
            ]
        ]
    ];
    $schema['content']['items']['fillings']['prod_reviews_from_current_object']['params']['request']['cmpid'] = '%COMPANY_ID%';
}
if (Registry::get('addons.cp_power_blog.status') == 'A') {
    $schema['cp_prev_power_reviews']['content']['items']['fillings']['cp_pr_power_blog'] = [
        'params' => [
            'sort_by'   => 'timestamp',
            'sort_order'=> 'desc',
            'request'   => [
                'postid' => '%POST_ID%'
            ]
        ]
    ];
}
$schema['main']['cache_overrides_by_dispatch']['products.view']['disable_cache_when']['request_handlers'][] = 'cp_filter_stars';

return $schema;
