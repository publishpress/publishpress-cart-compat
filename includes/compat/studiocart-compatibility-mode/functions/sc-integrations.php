<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart function wrappers — sc_add_*.
 * Loaded only when Compatibility Mode is on (see functions/index.php).
 */

if (! function_exists('sc_add_remove_activecampaign_subscriber')) {
    function sc_add_remove_activecampaign_subscriber($order_id, $service_id, $action_name, $list_id, $_sc_mail_tags, $_sc_mail_groups, $email, $phone, $fname, $lname, $fieldmap)
    {
        return ppcart_add_remove_activecampaign_subscriber($order_id, $service_id, $action_name, $list_id, $_sc_mail_tags, $_sc_mail_groups, $email, $phone, $fname, $lname, $fieldmap);
    }
}
if (! function_exists('sc_add_remove_mailchimp_subscriber')) {
    function sc_add_remove_mailchimp_subscriber($order_id, $service_id, $action_name, $list_id, $_sc_mail_tags, $_sc_mail_groups, $email, $phone, $fname, $lname, $intg, $order)
    {
        return ppcart_add_remove_mailchimp_subscriber($order_id, $service_id, $action_name, $list_id, $_sc_mail_tags, $_sc_mail_groups, $email, $phone, $fname, $lname, $intg, $order);
    }
}
if (! function_exists('sc_add_remove_mailpoet_subscriber')) {
    function sc_add_remove_mailpoet_subscriber($order_id, $_sc_services, $_sc_service_action, $mailpoet_list, $customerEmail, $first_name, $last_name)
    {
        return ppcart_add_remove_mailpoet_subscriber($order_id, $_sc_services, $_sc_service_action, $mailpoet_list, $customerEmail, $first_name, $last_name);
    }
}
if (! function_exists('sc_add_remove_membervault_subscriber')) {
    function sc_add_remove_membervault_subscriber($order_id, $service_id, $action_name, $member_vault_course_id, $email, $phone, $fname, $lname)
    {
        return ppcart_add_remove_membervault_subscriber($order_id, $service_id, $action_name, $member_vault_course_id, $email, $phone, $fname, $lname);
    }
}
if (! function_exists('sc_add_remove_sendfox_subscriber')) {
    function sc_add_remove_sendfox_subscriber($order_id, $_sc_services, $_sc_service_action, $sendfox_list, $customerEmail, $first_name, $last_name)
    {
        return ppcart_add_remove_sendfox_subscriber($order_id, $_sc_services, $_sc_service_action, $sendfox_list, $customerEmail, $first_name, $last_name);
    }
}
