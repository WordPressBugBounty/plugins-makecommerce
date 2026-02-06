<?php

namespace MakeCommerce\Payment\Gateway\WooCommerce;

trait Banklink {

    /**
     * Reloads banklinks
     * 
     * @since 3.0.0
     */
    public function mc_banklinks_reload( $force = false) {

        if ( $force ) {

            $updated = $this->banklinks_update();

            $this->methods->load_methods();

            return $updated;
        }

        die();
    }

    /**
     * Updates banklinks database
     * 
     * @since 3.0.9
     */
    public function banklinks_update() {

        $methods = self::get_payment_methods();
        $updated = self::insert_payment_methods( $methods );
        
        return $updated;
    }


    /**
     * Inserts payment methods into DB
     * 
     * @since 3.0.12
     */
    public static function insert_payment_methods( $methods ) {

        if ( !is_object( $methods ) ) {
            return false;
        }

        global $wpdb;

        $tableName = $wpdb->prefix . MAKECOMMERCE_TABLENAME;

        $wpdb->query( 'TRUNCATE TABLE `'.$tableName.'`' );
        
        if ( isset( $methods->banklinks ) ) {
            foreach( $methods->banklinks as $method ) {
                $wpdb->insert( $tableName, array( 'type' => 'banklink', 'country' => $method->country, 'name' => $method->name, 'url' => $method->url, 'logo_url' => $method->logo_url, 'min_amount' => $method->min_amount ?? NULL, 'max_amount' => $method->max_amount ?? NULL, 'channel' => $method->channel, 'display_name' => $method->display_name ) );
            }
        }
        
        if ( isset( $methods->cards ) ) {
            foreach( $methods->cards as $method ) {
                $wpdb->insert( $tableName, array( 'type' => 'card', 'name' => $method->name, 'url' => $method->url, 'logo_url' => $method->logo_url, 'min_amount' => $method->min_amount ?? NULL, 'max_amount' => $method->max_amount ?? NULL, 'channel' => $method->channel, 'display_name' => $method->display_name ) );
            }
        }

        if ( isset( $methods->other ) ) {
            foreach( $methods->other as $method ) {
                $wpdb->insert( $tableName, array( 'type' => 'other', 'country' => $method->country, 'name' => $method->name, 'url' => $method->url, 'logo_url' => $method->logo_url, 'min_amount' => $method->min_amount ?? NULL, 'max_amount' => $method->max_amount ?? NULL, 'channel' => $method->channel, 'display_name' => $method->display_name ) );
            }
        }

        if ( isset( $methods->payLater ) ) {
            foreach( $methods->payLater as $method ) {
                $wpdb->insert( $tableName, array( 'type' => 'payLater', 'country' => $method->country, 'name' => $method->name, 'url' => $method->url, 'logo_url' => $method->logo_url, 'min_amount' => $method->min_amount ?? NULL, 'max_amount' => $method->max_amount ?? NULL, 'channel' => $method->channel, 'display_name' => $method->display_name ) );
            }
        }

        // Check if methods from API and table are the same
        $updated = false;
        $db_methods = $wpdb->get_results( "SELECT `name`,`country` FROM $tableName" );

        // Gather all payment methods from API by name and country
        $all_methods = [];
        foreach( $methods as $method_type ) {
            foreach( $method_type as $method ) {
                if ( isset( $method->country )) {
                    $api_method_name = $method->name . $method->country;
                } else {
                    $api_method_name = $method->name;
                }

                array_push( $all_methods, $api_method_name );
            }
        }
            
        // Check if db contains correct methods
        foreach( $db_methods as $method ) {
            $db_method_name = $method->name . $method->country;
            if ( in_array( $db_method_name, $all_methods ) ) {
                $key = array_search( $db_method_name, $all_methods );
                unset( $all_methods[$key] );
            } else {
                // Methods don't match
                break;
            }
        }

        // Check if all methods were included
        if ( count( $all_methods ) == 0 ) {
            $updated = true;
        }

        if ( isset( $shopConfig ) && strlen( $shopConfig->name ) ) {
            update_option( 'mc_shop_name', $shopConfig->name );
        }

        if ( $updated ) {
            update_option( 'mc_banklinks_api_type', get_option( 'mk_api_type', false) );
        }

        return $updated;
    }


    /**
     * Gets payment methods
     * 
     * @since 3.0.12
     */
    public static function get_payment_methods() {

        $MK = \MakeCommerce::get_api();

        if ( !$MK ) {
            return false;
        }

        try {
            $shopConfig = $MK->getShopConfig( \MakeCommerce::config_request_parameters( MAKECOMMERCE_PLUGIN_ID.' '.MAKECOMMERCE_VERSION ) );
            $methods = $shopConfig->paymentMethods;	
        } catch ( \Throwable $e ) {
            error_log( print_r( $e, 1 ) );
            return false;
        }

        return $methods;
    }


}
