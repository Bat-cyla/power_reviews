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
use Tygh\Enum\UserTypes;
use Tygh\Enum\Addons\Discussion\DiscussionObjectTypes;
use Tygh\Addons\Discussion\Notifications\EventIdProviders\DiscussionProvider;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_cp_pr_send_emails_event_disp($auth, $object, $object_data, $object_name, $post_data, $fn_prepare_subject, $discussion_object_types, $lang_code)
{
    /** @var \Tygh\Notifications\EventDispatcher $event_dispatcher */
    $event_dispatcher = Tygh::$app['event.dispatcher'];

    $receivers = [
        UserTypes::ADMIN    => true,
        UserTypes::CUSTOMER => true,
    ];
    if (fn_allowed_for('MULTIVENDOR')) {
        $receivers[UserTypes::VENDOR] = true;
    }
    $receivers[$auth['user_type']] = false;

    /** @var \Tygh\Notifications\Settings\Factory $notification_settings_factory */
    $notification_settings_factory = Tygh::$app['event.notification_settings.factory'];
    $notification_rules = $notification_settings_factory->create($receivers);

    $url = "discussion_manager.manage?object_type={$object['object_type']}&post_id={$post_data['post_id']}";
    $lang_code = fn_get_company_language(Registry::get('runtime.company_id'));
    $discussion_data = [
        'post_id'       => $post_data['post_id'],
        'object'        => $object,
        'object_data'   => $object_data,
        'post_data'     => $post_data,
        'object_name'   => $object_name,
        'subject'       => $fn_prepare_subject($discussion_object_types[$object['object_type']], $lang_code),
        'url'           => $url,
    ];
    switch ($object['object_type']) {
        case DiscussionObjectTypes::ORDER:
            $order_info = db_get_row(
                'SELECT email FROM ?:orders WHERE order_id = ?i',
                $object['object_id']
            );

            $lang_code = Registry::get('settings.Appearance.backend_default_language');
            $discussion_data = [
                'post_id'       => $post_data['post_id'],
                'object'        => $object,
                'object_data'   => $object_data,
                'post_data'     => $post_data,
                'object_name'   => $object_name,
                'email'         => $order_info['email'],
                'subject'       => $fn_prepare_subject($discussion_object_types[$object['object_type']], $lang_code),
            ];

            $event_dispatcher->dispatch(
                'discussion.orders.new_post',
                $discussion_data,
                $notification_rules,
                new DiscussionProvider($discussion_data)
            );

            break;
        case DiscussionObjectTypes::PAGE:
            $event_dispatcher->dispatch(
                'discussion.pages.new_post',
                $discussion_data,
                $notification_rules,
                new DiscussionProvider($discussion_data)
            );
            break;
        case DiscussionObjectTypes::CATEGORY:
            $event_dispatcher->dispatch(
                'discussion.categories.new_post',
                $discussion_data,
                $notification_rules,
                new DiscussionProvider($discussion_data)
            );
            break;
        case DiscussionObjectTypes::PRODUCT:
            $event_dispatcher->dispatch(
                'discussion.products.new_post',
                $discussion_data,
                $notification_rules,
                new DiscussionProvider($discussion_data)
            );
            break;
        case DiscussionObjectTypes::TESTIMONIALS_AND_LAYOUT:
            $event_dispatcher->dispatch(
                'discussion.testimonials.new_post',
                $discussion_data,
                $notification_rules,
                new DiscussionProvider($discussion_data)
            );
            break;
        case DiscussionObjectTypes::COMPANY:
            $event_dispatcher->dispatch(
                'discussion.vendors.new_post',
                $discussion_data,
                $notification_rules,
                new DiscussionProvider($discussion_data)
            );
            break;
    }
    return true;
}