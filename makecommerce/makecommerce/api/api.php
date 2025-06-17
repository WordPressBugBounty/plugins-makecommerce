<?php

/**
 * The api functionality of the plugin.
 *
 * @link       https://makecommerce.net/
 * @since      3.0.0
 *
 * @package    Makecommerce
 * @subpackage Makecommerce/api
 */

namespace MakeCommerce;

use MakeCommerce\Admin\Dashboard;

/**
 * The payment functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Makecommerce
 * @subpackage Makecommerce/api
 * @author     Maksekeskus AS <support@maksekeskus.ee>
 */
class API {

	/**
	 * The ID of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private Loader $loader;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    3.0.0
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, Loader $loader ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->loader = $loader;
	}

	/**
	 * Gives error in admin if curl is not loaded
	 */
	public function check_if_curl_is_loaded() {

		if ( !extension_loaded('curl') ) {

			$class = 'notice notice-error';
			$message = __( 'You have enabled MakeCommerce module but it seems that you don\'t have CURL enabled. This way MakeCommerce unfortunately does not work!', 'wc_makecommerce_domain' );
			
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		}
	}

	/**
	 * Adds new column (payment method) in order view
	 * 
	 * @since 3.0.0
	 */
	public function add_ordersview_paymentmethod_column( $columns ) {
		
		$columns['makecommerce_payment_method'] = __('Payment method (MC)', 'wc_makecommerce_domain');

		return $columns;
	}


	/**
	 * Makes payment method column in order view sortable
	 * 
	 * @since 3.0.0
	 */
	public function make_ordersview_paymentmethod_column_sortable( $columns ) {

		return wp_parse_args (array( 'makecommerce_payment_method' => 'makecommerce_payment_method' ), $columns );
	}
		
	/**
	 * Inserts content for payment method column in order view
	 * 
	 * @since 3.0.0
	 */
	public function fill_ordersview_paymentmethod_column( $column, $post_id ) {

		if ( 'makecommerce_payment_method' === $column ) {
			$order = wc_get_order( $post_id );
			echo $order->get_meta( '_makecommerce_preselected_method', true );
		}
	}

	/**
	 * Update banklinks when an admin logs in
	 * 
	 * @since 3.0.0
	 */
	public function admin_login( $user_login, $user ) {

	    if ( user_can( $user, 'administrator' ) ) {
	    	Payment::update_banklinks();
	    }
	}

	/**
	 * Adds settings link to plugins page
	 * 
	 * @since 3.0.0
	 */
	public function add_plugin_settings_link( $links ) {

        if (!\MakeCommerce\Admin\Dashboard::check_store_setup()) {
            $settings_link = '<a href="admin.php?page=makecommerce_dashboard">'.__('Settings').'</a>';

            array_unshift( $links, $settings_link );
        } else {
            $settings_link = '<a href="admin.php?page=makecommerce_configure">'.__('Settings').'</a>';

            array_unshift( $links, $settings_link );
        }

		return $links;
	}

	/**
     * Function for resetting payment methods when API type is changed
     * 
     * @since 3.2.1
     */
	public function mk_delete_api_cache() {

		global $wpdb;
		$tableName = $wpdb->prefix . MAKECOMMERCE_TABLENAME;

		// Delete payment methods
        $wpdb->query( 'TRUNCATE TABLE `'.$tableName.'`' );
	}

	/**
     * Notice for missing API information
     * 
     * @since 3.0.0
     */
    public function api_info_missing() {

        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php echo __( 'The MakeCommerce plugin is almost ready, but it needs to be configured before it can function properly.', 'wc_makecommerce_domain' ); ?>
                <a href="<?php echo admin_url( 'admin.php?page=' . Dashboard::DASHBOARD_SLUG ); ?>"><?php echo __( 'Click here to configure the plugin', 'wc_makecommerce_domain' ); ?></a>
            </p>
        </div>
        <?php
    }
}
