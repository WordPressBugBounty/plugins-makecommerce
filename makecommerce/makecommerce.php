<?php
/**
 * @link            	  https://makecommerce.net/
 * @since           	  4.0.0
 * @package           	  Makecommerce
 *
 * @wordpress-plugin
 * Plugin Name: 	      MakeCommerce
 * Plugin URI:      	  https://makecommerce.net/
 * Description:	    	  Adds MakeCommerce payment gateway and shipping methods to WooCommerce checkout
 * Version:     	      4.0.3
 * Author:        		  Maksekeskus AS
 * Author URI:        	  https://makecommerce.net/
 * License:               GPL-2.0+
 * License URI:           http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:           makecommerce
 * Domain Path:           /languages
 * Requires Plugins:      woocommerce
 * Requires at least:	  6.8.1
 * Requires PHP: 		  8.1
 * WC requires at least:  9.9.3
 * WC tested up to:       9.9.3
 * WC HPOS compatibility: yes
 * WC Legacy Order Table Compatibility: yes
 */

function mc_table_exists(string $table): bool {
    global $wpdb;
    $table_name = $wpdb->prefix . $table;
    return $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name;
}


if (
    (
        get_option('mc_version') !== false ||
        get_option('wc_mc_version') !== false ||
        mc_table_exists('mc_banklinks')
    ) &&
    !filter_var(get_option('mc_shipping_plus'), FILTER_VALIDATE_BOOLEAN)
) {
    require_once plugin_dir_path(__FILE__) . 'config.php';
} else {
    require_once plugin_dir_path(__FILE__) . 'makecommerce/makecommerce.php';
}

