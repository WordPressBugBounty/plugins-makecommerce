<?php

namespace MakeCommerce\Shipping;

use MakeCommercePrefix\MakeCommerceShipping\SDK\Http\MakeCommerceClient;

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
                $pdf = $this->client->getLabel( $carrier, $shipment_id, $shipment_type );
            } catch ( \Exception $e ) {
                $error_msg = __('Label fetch failed: ', 'wc_makecommerce_domain' )  . $e->getMessage();
                $this->render_template('error.twig', [
                    'mc_error' => $error_msg ] );
                exit;
            }

            if (!$pdf) {
                $error_msg = __('No PDF content received.', 'wc_makecommerce_domain' );
                $this->render_template('error.twig', [
                    'mc_error' => $error_msg ] );
                exit;
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="'.$shipment_id.'.pdf"');

            echo $pdf;

            die();
        }
    }
}
