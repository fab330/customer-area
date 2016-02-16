<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

class CUAR_PaymentsHelper
{
    /** @var CUAR_Plugin */
    private $plugin;

    /** @var CUAR_PaymentsAddOn */
    private $pa_addon;

    /**
     * Constructor
     *
     * @param CUAR_Plugin        $plugin
     * @param CUAR_PaymentsAddOn $pa_addon
     */
    public function __construct($plugin, $pa_addon)
    {
        $this->plugin = $plugin;
        $this->pa_addon = $pa_addon;
    }

    //----------- MANAGEMENT ------------------------------------------------------------------------------------------/

    /**
     * @param string $object_type
     * @param int    $object_id
     * @param string $title
     * @param string $gateway
     * @param double $amount
     * @param string $currency
     * @param int    $user_id
     * @param array  $user_address
     * @param array  $extra_data
     * @param string $status
     *
     * @return bool|int Payment ID if payment is inserted, false otherwise
     */
    public function add($object_type, $object_id, $title,
                        $gateway, $amount, $currency,
                        $user_id, $user_address,
                        $extra_data = array(),
                        $status = 'pending')
    {
        // Make sure the payment is inserted with the correct timezone
        date_default_timezone_set(CUAR_GeneralHelper::get_timezone_id());

        $args = apply_filters('cuar/core/payments/create-payment-args', array(
            'post_title'    => $title,
            'post_status'   => !empty($status) ? $status : 'pending',
            'post_type'     => CUAR_Payment::$POST_TYPE,
            'post_date'     => isset($extra_data['post_date']) ? $extra_data['post_date'] : null,
            'post_date_gmt' => isset($extra_data['post_date']) ? get_gmt_from_date($extra_data['post_date']) : null,
        ), $object_type, $object_id, $title,
            $gateway, $amount, $currency,
            $user_id, $user_address,
            $extra_data, $status);

        // Create a blank payment
        $payment = wp_insert_post($args);

        if (is_wp_error($payment) || $payment == 0) return false;

        $payment = new CUAR_Payment($payment);

        $payment->set_object($object_type, $object_id);

        $payment->set_gateway($gateway);
        $payment->set_amount($amount);
        $payment->set_currency($currency);

        $payment->set_user_id($user_id);
        $payment->set_user_ip(CUAR_GeneralHelper::get_ip());
        $payment->set_address($user_address);

        do_action('cuar/core/payments/on-payment-added', $payment);

        return $payment->ID;
    }

}