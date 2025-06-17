<?php

//TODO: Does this class always exist?
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;
use MakeCommerce\Shipping;

final class MakeCommerceShippingBlocks {
    const EXTENSION_NAMESPACE = 'makecommerce';

    public function init(): void {
        require_once __DIR__ . '/mc-shipping-blocks-blocks-integration.php';

        add_action( 'woocommerce_init', [ $this, 'register_store_api_data' ] );
        add_action( 'woocommerce_blocks_checkout_block_registration', [ $this, 'register_checkout_block' ] );
        add_action( 'woocommerce_store_api_checkout_update_order_from_request', [ $this, 'update_order_shipping_meta' ], 10, 2 );
        add_action( 'woocommerce_thankyou', [$this, 'pickup_point_details'] );

        add_action( 'woocommerce_store_api_cart_errors', [ $this, 'validate_phone' ], 10, 2 );
    }


    public function register_checkout_block( $registry ): void {
        $registry->register( new McShippingBlocks_Blocks_Integration() );
    }

    public function register_store_api_data(): void {
        woocommerce_store_api_register_endpoint_data( [
            'endpoint'        => CheckoutSchema::IDENTIFIER,
            'namespace'       => self::EXTENSION_NAMESPACE,
            'data_callback'   => [ $this, 'store_api_data' ],
            'schema_callback' => [ $this, 'store_api_schema' ],
            'schema_type'     => ARRAY_A, //TODO: is this some Woo gobal const?
        ] );
    }

    public function store_api_data(): array {
        return [
            'machine_id'      => '',
            'shipping_method' => '',
        ];
    }

    public function store_api_schema(): array {
        return [
            'machine_id' => [
                'description' => __( 'Selected Pickup Point ID', 'wc_makecommerce_domain' ),
                'type'        => [ 'string', 'null' ],
                'readonly'    => true,
            ],
            'shipping_method' => [
                'description' => __( 'Selected Shipping Method', 'wc_makecommerce_domain' ),
                'type'        => [ 'string', 'null' ],
                'readonly'    => true,
            ],
        ];
    }

    /**
     * Checks that phone number is not empty AND the chosen shipping rate ID starts with 'mc_'.
     * Adds an error if both conditions are met.
     *
     * @param \WP_Error $errors A WP_Error object for storing validation errors.
     * @param \WC_Cart  $cart   The WooCommerce cart object.
     * @since 4.0.0
     */
    public function validate_phone( $errors, $cart ) {
        $billing_phone = $cart->get_customer()->get_billing_phone();

        if ( empty( $billing_phone ) ) {

            $chosen_shipping_method_id = null;
            if ( WC()->session && WC()->session->get( 'chosen_shipping_methods' ) ) {
                $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
                if ( ! empty( $chosen_methods[0] ) ) {
                    $chosen_shipping_method_id = $chosen_methods[0];
                }
            }
            
            // 3. Check if a shipping method is chosen and its ID starts with 'mc_'
            if ( $chosen_shipping_method_id && str_starts_with( $chosen_shipping_method_id, 'mc_' ) ) {
                $errors->add(
                    'missing_phone_mc_shipping',
                    __( 'Phone number is required for the chosen shipping method.', 'wc_makecommerce_domain' ),
                    [ 'field' => 'billing_phone' ]
                );
            }
        }
    }

    /**
     * Update order meta from checkout details
     *
     * @since 3.0.0
     */
    public function update_order_shipping_meta( $order, $request ): void {
        $data = [];

        if ( isset( $request['extensions'] ) && is_array( $request['extensions'] ) ) {
            $data = $request['extensions'][ self::EXTENSION_NAMESPACE ] ?? [];
        }

        if ( empty( $data ) ) return;

        $shipping_method = sanitize_text_field( $data['shipping_method'] ?? '' );
        $machine_id      = sanitize_text_field( $data['machine_id'] ?? '' );

        if ( $shipping_method && str_starts_with( $shipping_method, 'mc_' ) ) {
            $carrier = str_replace( [ 'mc_courier_', 'mc_pickuppoint_' ], '', $shipping_method );
            $order->update_meta_data( '_mc_shipping_method', str_replace( 'mc_', '', $shipping_method ) );
            $order->update_meta_data( '_mc_shipping_carrier', $carrier );
        }

        if ( $machine_id && (stripos($shipping_method, 'pickuppoint') !== false) ) {
            $order->update_meta_data( '_mc_machine_id', $machine_id );
        }
    }

    /**
     * Add parcel machine information to order view (Customer)
     *
     * @since 3.0.0
     */
    public function pickup_point_details( $order_id ) {
        // Only run this on block themes, otherwise info added on hook woocommerce_order_details_after_customer_details
        if ( ! wp_is_block_theme() ) {
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) return;

        $machine_id = $order->get_meta('_mc_machine_id', true);
        $carrier = $order->get_meta('_mc_shipping_carrier', true);
        $country = $order->get_shipping_country();

        if ( empty( $carrier ) ) {
            return;
        }

        if ( empty( $machine_id ) ) {
            echo '
            <div class="mc-shipping-block-info">
                <h2 class="wp-block-heading" style="font-size:clamp(15.747px, 0.984rem + ((1vw - 3.2px) * 0.503), 24px);">
                ' . __( 'MakeCommerce Shipping Details', 'wc_makecommerce_domain' ) .'</h2>
                <div style="border: 1px solid hsla(0, 0%, 7%, .11); border-radius: 4px; padding: 16px;">
                    <p>'.esc_html(ucfirst( $carrier )).' - '.__( 'Courier', 'wc_makecommerce_domain' ).' </p>
                 </div>
            </div>
            ';
            return;
        }

        $machine = Shipping::mk_get_machine($carrier, $machine_id, $country);;

        if ( !$machine ) {
            return;
        }

        echo '
        <div class="mc-shipping-block-info">
            <h2 class="wp-block-heading" style="font-size:clamp(15.747px, 0.984rem + ((1vw - 3.2px) * 0.503), 24px);">
            ' . __( 'MakeCommerce Shipping Details', 'wc_makecommerce_domain' ) .'</h2>
            <div style="border: 1px solid hsla(0, 0%, 7%, .11); border-radius: 4px; padding: 16px;">
                <p>'.ucfirst( $carrier ).' - '.__( 'Pickup point', 'wc_makecommerce_domain' ).' <br/>
                '.esc_html($machine['name']).' <br/> '.esc_html($machine['address']).'</p>
            </div>
        </div>
        ';
    }
}
