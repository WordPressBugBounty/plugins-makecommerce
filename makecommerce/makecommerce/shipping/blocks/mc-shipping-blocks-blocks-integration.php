<?php
use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

define ( 'McShippingBlocks_VERSION', '0.1.0' );

/**
 * Class for integrating with WooCommerce Blocks
 */
class McShippingBlocks_Blocks_Integration implements IntegrationInterface {

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'mc-shipping-blocks';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 *
	 */
	public function initialize() {
		$script_path = '/build/index.js';

		$script_url = 'https://static.maksekeskus.ee/modules/woocommerce/js/blocks/shipping/index.js';

		$script_asset_path = dirname( __FILE__ ) . '/build/index.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_path ),
			);

		wp_register_script(
			'mc-shipping-blocks-blocks-integration',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( 'mc-shipping-blocks-blocks-integration' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array( 'mc-shipping-blocks-blocks-integration' );
	}

    /**
     * An array of key, value pairs of data made available to the block on the client side.
     *
     * @return array
     */
    public function get_script_data() {
        $data = [
            'mc-shipping-blocks-active'    => true,
            'defaultCountry'               => $this->get_default_country(),
            'pickupPointPlaceholder'       => __('Select pickup point', 'wc_makecommerce_domain')
        ];
        return $data;
    }

    private function get_default_country(){
        $defaultCountry = 'EE'; // fallback

        if ( function_exists( 'WC' ) && WC()->customer && WC()->countries ) {
            $shipping_country = WC()->customer->get_shipping_country();
            $base_country     = WC()->countries->get_base_country();
            $defaultCountry   = $shipping_country ?: $base_country;
        }
        return $defaultCountry;
    }

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}
		return McShippingBlocks_VERSION;
	}
}
