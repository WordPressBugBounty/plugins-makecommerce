<?php

namespace MakeCommerce\Payment\Gateway;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\Blocks\Payments\PaymentResult;
use Automattic\WooCommerce\Blocks\Payments\PaymentContext;
use MakeCommerce\Shipping;
use MakeCommerce\Shipping\Method\Courier\Smartpost;

final class WooCommerceBlocks extends AbstractPaymentMethodType {
    public $name = 'makecommerce';

    protected $gateway;
    protected $settings;

    /**
     * Initialize the class and register actions
     *
     * @since 3.5.0
     */
    public function initialize() {
        $this->gateway = new WooCommerce( true );
        $this->settings = get_option( 'woocommerce_makecommerce_settings', [] );

    }

    /**
     * Check if the parent/legacy gateway is active
     *
     * @since 3.5.0
     */
    public function is_active() {
        return filter_var( $this->get_setting( 'enabled', false ), FILTER_VALIDATE_BOOLEAN );
    }

    /**
     * Register/enqueue all the necessary js files
     *
     * @since 3.5.0
     */
    public function get_payment_method_script_handles() {

        $script_path = '/build/index.js';

        $script_url = \MakeCommerce::get_static_url() . 'modules/woocommerce/js/blocks/payments/index.js';

        $script_asset_path = dirname( __FILE__ ) . '/build/index.asset.php';
        $script_asset      = file_exists( $script_asset_path )
            ? require $script_asset_path
            : array(
                'dependencies' => array(),
                'version'      => $this->get_file_version( $script_path ),
            );

        wp_register_script(
            'mc-payment-blocks-integration',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );

        wp_set_script_translations(
            'mc-payment-blocks-integration',
            'wc_makecommerce_domain',
            dirname(plugin_dir_path(__FILE__), 4) . '/languages'
        );

        return [
            'mc-payment-blocks-integration'
        ];
    }

    /**
     * Add all the necessary data for creating the React payment block
     *
     * @since 3.5.0
     */
    public function get_payment_method_data() {

        $manual_renewals = false;
        if ( class_exists( '\WC_Subscriptions_Admin' )
             && get_option( \WC_Subscriptions_Admin::$option_prefix . '_accept_manual_renewals', 'no' ) === 'yes'
             && get_option( \WC_Subscriptions_Admin::$option_prefix . '_turn_off_automatic_payments', 'no' ) === 'yes'
        ) {
            $manual_renewals = true;
        }

        $default_country = WC()->countries->get_base_country();

        if ( WC()->customer ) {
            if ( WC()->customer->get_billing_country() ) {
                $default_country = WC()->customer->get_billing_country();
            } elseif ( WC()->customer->get_shipping_country() ) {
                $default_country = WC()->customer->get_shipping_country();
            }
        }

        return [
            'name' => $this->name,
            'label' => __($this->gateway->title, 'wc_makecommerce_domain'),
            'description' => $this->gateway->description,
            'gatewayId' => $this->gateway->id,
            'methods' => $this->gateway->get_methods(),
            'path' => plugins_url( '../images/', __FILE__ ),
            'paymentError' => __( 'Please select suitable payment option!', 'wc_makecommerce_domain' ),
            'manualRenewals' => $manual_renewals,
            'defaultCountry' => $default_country
        ];
    }

}
