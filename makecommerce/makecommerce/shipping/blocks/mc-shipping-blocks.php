<?php

//TODO: Does this class always exist?
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;
use MakeCommerce\Shipping;

final class MakeCommerceShippingBlocks {
    const EXTENSION_NAMESPACE = 'makecommerce';

    public function init(): void {
        require_once __DIR__ . '/mc-shipping-blocks-blocks-integration.php';

        add_action( 'woocommerce_init', [ $this, 'register_store_api_data' ] );
        add_action( 'woocommerce_init', [ $this, 'blocks_update_checkout_country' ] );
        add_action( 'woocommerce_blocks_checkout_block_registration', [ $this, 'register_checkout_block' ] );
        add_action( 'woocommerce_store_api_checkout_update_order_from_request', [ $this, 'update_order_shipping_meta' ], 10, 2 );
        add_action( 'woocommerce_thankyou', [$this, 'pickup_point_details'] );
    }

    public function blocks_update_checkout_country(): void {
        woocommerce_store_api_register_update_callback(
            [
                'namespace' => self::EXTENSION_NAMESPACE,
                'callback'  => function( $data ) {
                    if ( isset($data['country']) ) {
                        $country = sanitize_text_field( $data['country'] );
                        WC()->customer->set_shipping_country( $country );
                        WC()->customer->save();
                    }
                }
            ]);
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
        } else {
            // Customer switched away from a MakeCommerce method (e.g. to WooCommerce's own local pickup).
            // Clear any stale meta a previous draft-order sync may have written, otherwise register_shipment()
            // will still treat this order as eligible for a MakeCommerce shipment.
            $order->delete_meta_data( '_mc_shipping_method' );
            $order->delete_meta_data( '_mc_shipping_carrier' );
        }

        if ( $machine_id && (stripos($shipping_method, 'pickuppoint') !== false) ) {
            $order->update_meta_data( '_mc_machine_id', $machine_id );
        } else {
            $order->delete_meta_data( '_mc_machine_id' );
        }

        $order->save();
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
        $shipment_id = $order->get_meta('_mc_shipment_id', true);
        $country = $order->get_shipping_country();

        if (empty($country) && function_exists( 'WC' ) && WC()->countries ) {
            $country = WC()->countries->get_base_country();
        }

        // if _mc_shipment_id empty, then not mc shipment and do not add details
        if ( empty( $shipment_id ) ) {
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
