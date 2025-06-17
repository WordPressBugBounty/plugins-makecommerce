<?php

namespace MakeCommerce\Shipping;

/**
 * All functionality that has to do with shipping and orders
 *
 * @since 3.0.0
 */
class Order extends \MakeCommerce\Shipping
{

    // TODO: if in Woo order view, shipping/billing details change, then should be changed for actual parcel (in manager view changed as well)

    private $loader;

    /**
     * Constructs Order class, defines hooks
     *
     * @since 3.0.0
     */
    public function __construct(\MakeCommerce\Loader $loader)
    {
        $this->loader = $loader;

        $this->define_hooks();
    }

    /**
     * Define all wordpress hooks
     *
     * @since 3.0.0
     */
    public function define_hooks()
    {
        // Add Shipping details to Order view page
        $this->loader->add_filter( 'woocommerce_admin_order_data_after_shipping_address', $this, 'pickup_point_details_order' );

        // Add Shipping details to Order confirmed page
        $this->loader->add_filter( 'woocommerce_order_details_after_customer_details', $this, 'pickup_point_details_thankyou');

        // Add shipping details to confirmation email
        $this->loader->add_action('woocommerce_email_after_order_table', $this, 'add_mc_shipping_info_to_email', 20, 4);
    }

    /**
     * Add shipment data to email
     *
     * @since 4.0.0
     */
    function add_mc_shipping_info_to_email( $order, $sent_to_admin, $plain_text, $email ) {
        // Only add for customer emails (optional filter)
        if ( ! in_array( $email->id, [ 'customer_processing_order', 'customer_completed_order' ] ) ) {
            return;
        }

        $order = wc_get_order($order->get_id());

        $shipment_id   = $order->get_meta('_mc_shipment_id', true);
        $tracking_link = $order->get_meta('_mc_tracking_link', true);
        $carrier       = $order->get_meta('_mc_shipping_carrier', true);
        $machine_id    = $order->get_meta('_mc_machine_id', true);
        $country       = $order->get_shipping_country();
        $method        = $order->get_shipping_method();

        if ( $plain_text ) {
            echo "=== MakeCommerce Shipping Details ===\n";

            if ( $machine_id ) {
                $machine = self::mk_get_machine( $carrier, $machine_id, $country );
                echo __( 'Chosen shipping method:', 'wc_makecommerce_domain' ). "{$method} - {$machine['name']} - {$machine['address']}\n";
            } else {
                echo __( 'Chosen shipping method:', 'wc_makecommerce_domain' ). " {$method}\n";
            }

            if ( $tracking_link ) {
                echo __( 'Shipment tracking info:', 'wc_makecommerce_domain' ) . " {$shipment_id} - {$tracking_link}\n";
            } elseif ( $shipment_id ) {
                echo __( 'Shipment tracking info:', 'wc_makecommerce_domain' ) . " {$shipment_id}\n";
            }

        } else {
            echo '<h2>' . esc_html__( 'MakeCommerce Shipping Details', 'wc_makecommerce_domain' ) . '</h2>';
            echo '<ul>';

            if ( $machine_id ) {
                $machine = self::mk_get_machine( $carrier, $machine_id, $country );
                echo '<li><strong>' . esc_html__( 'Chosen shipping method:', 'wc_makecommerce_domain' ) . '</strong> '
                    . esc_html( $method . ' – ' . $machine['name'] . ', ' . $machine['address'] ) . '</li>';
            } else {
                echo '<li><strong>' . esc_html__( 'Chosen shipping method:', 'wc_makecommerce_domain' ) . '</strong> '
                    . esc_html( $method ) . '</li>';
            }

            if ( $tracking_link ) {
                echo '<li><strong>' . esc_html__( 'Shipment tracking info:', 'wc_makecommerce_domain' ) . '</strong> '
                    . '<a href="' . esc_url( $tracking_link ) . '" target="_blank">' . esc_html( $shipment_id ) . '</a></li>';
            } elseif ( $shipment_id ) {
                echo '<li><strong>' . esc_html__( 'Shipment tracking info:', 'wc_makecommerce_domain' ) . '</strong> '
                    . esc_html( $shipment_id ) . '</li>';
            }

            echo '</ul>';
        }
    }

    /**
     * Add shipping information to order view (Admin panel)
     *
     * @since 3.0.0
     * @param WC_Order $order The WooCommerce order object.
     */
    public function pickup_point_details_order( $order ) {
        $this->_display_shipping_details( $order, 'admin' );
    }

    /**
     * Add shipping information to thank you page (Customer)
     *
     * @since 3.0.0
     * @param WC_Order $order The WooCommerce order object.
     */
    public function pickup_point_details_thankyou( $order ) {
        $this->_display_shipping_details( $order, 'customer' );
    }

    /**
     * Helper function to display MakeCommerce shipping details HTML directly.
     *
     * @param WC_Order $order The WooCommerce order object.
     * @param string   $context The context for displaying details ('admin' or 'customer').
     */
    private function _display_shipping_details( $order, $context ) {
        $machine_id    = $order->get_meta( '_mc_machine_id', true );
        $carrier       = $order->get_meta( '_mc_shipping_carrier', true );
        $tracking_link = $order->get_meta( '_mc_tracking_link', true );
        $shipment_id   = $order->get_meta( '_mc_shipment_id', true );
        $country       = $order->get_shipping_country();

        if ( empty( $carrier ) ) {
            $old_machine = $order->get_meta( '_parcel_machine', true );
            if (!empty( $old_machine ) ) {
                echo '<p style="color:red">' . __( 'Unable to display shipment details<br> This MakeCommerce shipment was created with v3.4 or lower plugin', 'wc_makecommerce_domain' ) . '</p>';
            }
            return;
        }

        // --- Common Header / Wrapper Start ---
        if ( 'admin' === $context ) {
            echo '<h3>' . __( 'MakeCommerce Shipping Details', 'wc_makecommerce_domain' ) . '</h3>';
            echo '<div class="address">';
        } else {
            echo '<div class="mc-shipping-block-info">';
            echo '<h2>' . __( 'MakeCommerce Shipping Details', 'wc_makecommerce_domain' ) . '</h2>';
            echo '<div><address>';
        }

        if ( empty( $machine_id ) ) {
            // --- Courier Only Display ---
            if ( 'admin' === $context ) {
                echo '<p>' . esc_html( ucfirst( $carrier ) ) . ' - ' . __( 'Courier', 'wc_makecommerce_domain' ) . '</p>';
                echo '</div>';
            } else {
                echo esc_html( ucfirst( $carrier ) ) . ' - ' . __( 'Courier', 'wc_makecommerce_domain' );
                echo '</address></div></div>';
            }
        } else {
            // --- Pickup Point Display ---
            $machine = self::mk_get_machine( $carrier, $machine_id, $country );

            if ( ! $machine ) {
                // If machine not found, close previously opened tags and return
                if ( 'admin' === $context ) {
                    echo '</div>';
                } else {
                    echo '</address></div></div>';
                }
                return;
            }

            if ( 'admin' === $context ) {
                echo '<p>';
                echo ucfirst( $carrier ) . ' - ' . __( 'Pickup point', 'wc_makecommerce_domain' ) . ' <br/> ';
                echo esc_html( $machine['name'] ) . ' <br/> ' . esc_html( $machine['address'] );
                echo '</p></div>';

            } else {
                echo ucfirst( $carrier ) . ' - ' . __( 'Pickup point', 'wc_makecommerce_domain' ) . ' <br/> ';
                echo esc_html( $machine['name'] ) . ' <br/> ' . esc_html( $machine['address'] );
                echo '</address></div></div>';
            }
        }


        // --- Add Tracking info if possible ---
        if ( ! empty( $tracking_link ) ) {
            echo '<h3>' . __( 'Shipment tracking info:', 'wc_makecommerce_domain' ) . '</h3> ';
            echo '<a target="_blank" href="' . esc_url( $tracking_link ) . '">' . esc_html( $shipment_id ) . '</a>';
        }
    }
}
