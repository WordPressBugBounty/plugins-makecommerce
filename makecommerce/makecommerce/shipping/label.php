<?php

namespace MakeCommerce\Shipping;

use MakeCommerce\Admin\Dashboard;

/**
 * All functionality that has to do with shipping labels and printing
 * 
 * @since 3.0.0
 */

class Label extends \MakeCommerce\Shipping {

	private $loader;

	/**
	 * Constructs Label class, defines loader and hooks
	 * 
	 * @since 3.0.0
	 */
    public function __construct( \MakeCommerce\Loader $loader ) {
    
		$this->loader = $loader;

		$this->define_hooks();
	}

	/**
	 * Define all wordpress hooks needed for printing stuff and creating labels
	 * 
	 * @since 3.0.0
	 */
    public function define_hooks() {

        $this->loader->add_action( 'woocommerce_order_actions_end', $this, 'print_button' );
        $this->loader->add_action('admin_init', $this, 'display_label');
    }

	/**
	 * Supporting function for print_labels. Creates needed javasript for printing button
	 * 
	 * @since 3.0.0
	 */
    public function print_button( $post_id ) {
        $order = wc_get_order( $post_id );

        $shipping_id = $order->get_meta( '_mc_shipment_id', true );
        if ( !$shipping_id ) {
            return;
        }

        $method = $order->get_meta( '_mc_shipping_method', true );
        $carrier = $order->get_meta( '_mc_shipping_carrier', true );

        $type = 'pickuppoint';
        if ( str_contains($method, 'courier')) {
            $type = 'courier';
        }

        $url = add_query_arg(
            [
                'mc_carrier_id' => $carrier,
                'mc_label_id' => $shipping_id,
                'mc_shipment_type' => $type,
            ],
            admin_url()
        );

        echo '
        <li class="wide">
            <a id="print_label" href="' . esc_url($url) . '" target="_blank" class="button">
                <img src=' . plugin_dir_url(__DIR__)  . 'assets/mc.svg' . ' alt="" style="height: 1em; vertical-align: middle; margin-right: 0.5em;">
                ' . __( 'Print parcel label', 'wc_makecommerce_domain' ) . '
            </a>
        </li>';
    }

    /**
     * Process label printing
     *
     * @since 4.0.0
     */
    public function display_label() {

        if ( !isset($_GET['mc_carrier_id']) || !isset($_GET['mc_label_id']) || !isset($_GET['mc_shipment_type']) ) {
            return;
        }

        $carrier = sanitize_text_field($_GET['mc_carrier_id']);
        $shipment_id = sanitize_text_field($_GET['mc_label_id']);
        $shipment_type = sanitize_text_field($_GET['mc_shipment_type']);

        if ( $carrier && $shipment_id && $shipment_type ) {
            if ( !$this->client ) {
                $this->client = self::init_client();
            }
            try {
                $label = $this->client->getLabel( $carrier, $shipment_id, $shipment_type );
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . $shipment_id . '.pdf"');
                echo $label;
                exit;

            } catch ( \Throwable $e ) {
                $init_print_label_via_manager_url = add_query_arg( [
                    'mc_print_label'  => 1,
                    'mc_shipment_id'  => $shipment_id,
                ], admin_url('admin.php?page=' . Dashboard::DASHBOARD_SLUG) );

                wp_redirect( $init_print_label_via_manager_url );
                exit;
            }
        }
    }
}
