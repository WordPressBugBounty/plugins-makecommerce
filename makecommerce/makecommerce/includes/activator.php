<?php

namespace MakeCommerce;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      3.0.0
 * @package    Makecommerce
 * @subpackage Makecommerce/includes
 * @author     Maksekeskus AS <support@maksekeskus.ee>
 */
class Activator
{
    public static function activate()
    {

        global $wpdb;

        $table_name = $wpdb->prefix . MAKECOMMERCE_TABLENAME;

        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name;

        // Save install status
        if (!$table_exists) {
            add_option('makecommerce_install_status', 'new_install');
            update_option('mc_shipping_plus', 'yes');
            //Create table
            $wpdb->query
            ("
			CREATE TABLE IF NOT EXISTS 
				`" . $table_name . "`
				(
					`id` mediumint(9) NOT NULL AUTO_INCREMENT, 
					`type` varchar(10) NOT NULL, 
					`country` char(2) NOT NULL, 
					`name` varchar(25) NOT NULL, 
					`url` varchar(250) NOT NULL, 
					`logo_url` varchar(250), 
					`channel` varchar(250),
					`display_name` varchar(250),
					`min_amount` mediumint(9), 
					`max_amount` mediumint(9), 
					PRIMARY KEY `id` (`id`)
				) 
                " . $wpdb->get_charset_collate() . ";
            ");
        } else {
            update_option('makecommerce_install_status', 'upgrade');
        }
    }
}
