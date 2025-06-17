<?php

namespace MakeCommerce\Payment;

/**
 * Creates minimal functionality needed for payment gateways
 * 
 * @since 3.0.0
 */

use WC_Payment_Gateway;
use MakeCommerce\Payment\Gateway\Refund;
use MakeCommerce\Payment\Gateway\Subscription;

abstract class Gateway extends WC_Payment_Gateway {
    use Refund;
    use Subscription;

    public $id;
    public $MK;

    /**
     * Defines which WooCommerce options this payment gateway supports
     */
    public $supports = array(
        'subscriptions',
        'subscription_cancellation', 
        'subscription_suspension', 
        'subscription_reactivation',
        'subscription_amount_changes',
        'subscription_date_changes',
        'subscription_payment_method_change_admin',
        'subscription_payment_method_change_customer',
        'products',
        'refunds'
    );
    
    /**
     * Construct payment gateway
     * 
     * @since 3.0.0
     */
    public function __construct() {

        // If user has not enabled mc_payments, then do not display method at all
        if ( !$this->mc_payments_enabled() ) {
            return;
        }

        //Set api
        $this->MK = \MakeCommerce::get_api();

        //get settings from \WC_Payment_Gateway
        $this->init_settings();

        $this->enabled = 'yes' === $this->get_option( 'enabled' ) ? 'yes' : 'no';

        //set hooks
        $this->set_hooks();

        //set gateway specific hooks
        if ( $this->enabled() ) {
            $this->set_gateway_hooks();
        }
    }

    /**
     * If user clicks on manage PM settings then redirect to our plugin admin view
     *
     * @since 3.0.0
     */
    public function admin_options() {
        if ( is_admin() && current_user_can('manage_woocommerce') ) {
            wp_redirect( admin_url( 'admin.php?page=makecommerce_configure' ) );
            exit;
        }
    }

    /**
     * Check if the gateway is available
     * Automatically called by \WC_Payment_Gateway
     * 
     * @since 3.0.0
     */
    public function is_available() {

        if ( ! \MakeCommerce::is_api_set() ) {
            return false;
        }

        return parent::is_available();
    }

    /**
     * Set hooks used by all WooCommerce payment gateways
     * 
     * @since 3.0.0
     */
    final public function set_hooks() {
        
        add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_payment_gateways', array( $this, 'add_payment_gateway' ) );

    }

    /**
     * Adds payment gateway to woocommerce
     * 
     * @since 3.0.0
     */
    final public function add_payment_gateway( $methods ) {

        $methods[] = get_class( $this );

		return $methods;
    }

    /**
     * Force creating a function for setting gateway specific hooks
     * 
     * @since 3.0.0
     */
    abstract public function set_gateway_hooks();

    /**
     * Checks if the gateway is enabled
     * 
     * @since 3.0.0
     */
    abstract public function enabled();

    /**
     * Checks if the mc_payments module is enabled
     *
     * @since 3.0.0
     */
    abstract public function mc_payments_enabled();
}
