<?php

namespace MakeCommerce\Payment\Gateway;

use MakeCommerce\Payment\Gateway;
use MakeCommerce\Payment\Gateway\WooCommerce\Banklink;
use MakeCommerce\Payment\Gateway\WooCommerce\Creditcard;

class WooCommerce extends Gateway {
    use Banklink;
    use Creditcard;

    public $id = MAKECOMMERCE_PLUGIN_ID;

    public $version = '4.0.7';
    
    public $payment_return_url;
    public $payment_return_url_m2m;
    public $payment_return_url_cancel;

    public $title = 'Bank-links or credit card';
    public $method_title = 'MakeCommerce';
    public $method_description = 'Accept payments from all major Baltic and Finnish banks, Apple Pay, Google Pay, Visa and MasterCard and buy now pay later providers';

    protected $init;

    /**
     * Payment methods
     */
    private WooCommerce\Methods $methods;

    public function get_methods() {
        return $this->methods;
    }

    /**
     * Construct the parent and payment gateway
     * 
     * @since 3.0.0
     */
    public function __construct( $init = false ) {

        $this->init = $init;

        parent::__construct();

        //set return urls
        $this->set_return_urls();

        $this->mc_version_check( $this->version );

        //initialize all payment methods
        $this->methods = new WooCommerce\Methods( $this->id, $this->init, $this->settings );

        $this->setPaymentMethodTitle();

        //has_fields enables our methods for WooCommerce
        $this->has_fields = true;

        $this->icon = plugin_dir_url(dirname( __FILE__ , 3)) . 'assets/mc.svg';

        //Add Blocks
        add_action( 'woocommerce_blocks_loaded', [$this, 'woocommerce_blocks_support'] );
    }


    /**
     * Set return urls
     * 
     * @since 3.0.0
     */
    private function set_return_urls() {

        $locale = \MakeCommerce\i18n::get_two_char_locale();
        
        //set return url's
        $return_url = add_query_arg([
            'makecommerce_return' => '1',
            'lang1' => $locale,
        ], home_url('/'));

        $this->payment_return_url = $return_url;
        $this->payment_return_url_m2m = add_query_arg('ajax_content', '1', $return_url);
        $this->payment_return_url_cancel = $return_url;
    }


    /**
     * Set gateway specific hooks/filters
     * 
     * @since 3.0.0
     */
    public function set_gateway_hooks() {
        global $wp_filter;

        add_filter( 'query_vars', array( $this, 'return_trigger' ) );
        add_action( 'template_redirect', array( $this, 'return_trigger_check' ) );

        add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'process_subscription_payment_start' ), 10, 2 );
        add_action( 'scheduled_subscription_payment_' . $this->id, array( $this, 'process_subscription_payment_start' ), 10, 2 );

        add_action( 'woocommerce_checkout_update_order_meta', [$this, 'set_received_order_url'] );

        if ( $this->init == false ) {
            if ( !isset( $wp_filter['woocommerce_receipt_' . $this->id] ) ) {
                add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
            }
            wp_enqueue_script( 'jquery');
            wp_enqueue_style( 'makecommerce', \MakeCommerce::get_static_url() . "modules/woocommerce/css/makecommerce.css", array(), $this->version );

            wp_enqueue_style('makecommerce-bootstrap', \MakeCommerce::get_static_url() . "modules/woocommerce/css/bootstrap-mk-scoped.css");

            //enqueue scripts for payment methods checkout
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_filter( 'woocommerce_blocks_checkout_enqueue_data', array( $this, 'enqueue_scripts' ) );
        }
    }

    /**
     * Enqueue scripts for payment methods checkout
     * 
     * @since 3.2.0
     */
    public function enqueue_scripts() {
        \MakeCommerce::mc_enqueue_script( 
            'MC_METHOD_LIST',
            \MakeCommerce::get_static_url() . "modules/woocommerce/js/mc_method_list.js",
            [
                'id' => $this->id,
                'settings' => $this->settings,
            ], 
            [ 'jquery' ],
            true
        );
    }
    
    /**
     * Overrides \WC_Payment_Gateway payment fields
     * Shows payment methods in checkout
     * 
     * @since 3.0.0
     */
    public function payment_fields() {

        $this->methods->show_methods();
    }

    /**
     * Checks if a payment option has been selected
     * 
     * @since 3.0.0
     */
    public function validate_fields() {

        global $woocommerce;

        $selected = false;

        if ( isset( $_POST['PRESELECTED_METHOD_' . $this->id] ) ) {
            $selected = sanitize_text_field( $_POST['PRESELECTED_METHOD_' . $this->id] );
        } elseif ( isset( $_POST['preselected_method_' . $this->id] ) ) {
            $selected = sanitize_text_field( $_POST['preselected_method_' . $this->id] );
        }

        if ( !$selected ) {
            wc_add_notice( __( 'Please select suitable payment option!', 'wc_makecommerce_domain' ), 'error' );
        } else {
            // is this used?? 
            $woocommerce->session->makecommerce_preselected_method = $selected;
        }

        return true;
    }

    /**
     * Processes payments, called by \WC_Payment_Gateway
     * 
     * @since 3.0.0
     */
    public function process_payment( $orderId ) {

        $order = wc_get_order( $orderId );

        $selected = false;

        if ( isset( $_POST['PRESELECTED_METHOD_' . $this->id] ) ) {
            $selected = sanitize_text_field( $_POST['PRESELECTED_METHOD_' . $this->id] );
        } elseif ( isset( $_POST['preselected_method_' . $this->id] ) ) {
            $selected = sanitize_text_field( $_POST['preselected_method_' . $this->id] );
        }

        if ( !empty( $selected ) ) {
            $order->update_meta_data( '_makecommerce_preselected_method', $selected );
                
            $request_body = array(
                'transaction' => array(
                    'amount' => ( string )sprintf( "%.2f", $order->get_total() ),
                    'currency' => method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->currency,
                    'reference' => $order->get_order_number(),
                    'transaction_url' => array(
                        'return_url' => array(
                            'url' => $this->payment_return_url,
                            'method' => 'POST',
                        ),
                        'cancel_url' => array(
                            'url' => $this->payment_return_url_cancel,
                            'method' => 'POST',
                        ),
                        'notification_url' => array(
                            'url' => $this->payment_return_url_m2m,
                            'method' => 'POST',
                        ),
                    ),
                ),
                'customer' => array(
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'country' => strtolower( $order->get_billing_country() ),
                    'locale' => strtolower( \MakeCommerce\i18n::get_two_char_locale() ),
                ),
            );

            $transaction = $this->MK->createTransaction( $request_body );
            
            if ( isset( $transaction->id ) ) {
                $order->update_meta_data( '_makecommerce_transaction_id', $transaction->id );

                if ( substr( $selected, 0, 5 ) == 'card_' ) {
                    $has_subscription = function_exists( 'wcs_order_contains_subscription' ) && wcs_order_contains_subscription( $order ) || $order->get_status() === 'active';

                    if ( $has_subscription ) {
                        $redirect_url = $order->get_checkout_payment_url( true );
                    } else {
                        $redirect_url = false;
                        foreach ( $transaction->payment_methods->cards as $card ) {
                            if ( 'card_'.$card->name === $selected ) {
                                $redirect_url = $card->url;
                            }
                        }

                        if ( !$redirect_url ) {
                            $redirect_url = $this->_getRedirectUrl( $selected ).$transaction->id;
                        }
                    }
                } else {

                    $redirect_url = false;
                    foreach ( $transaction->payment_methods->banklinks as $banklink ) {
                        if ( $banklink->country.'_'.$banklink->name === $selected ) {
                            $redirect_url = $banklink->url;
                        }
                    }

                    if ( !$redirect_url ) {
                        $redirect_url = $this->_getRedirectUrl( $selected ).$transaction->id;
                    }
                }

                $order->save();

                return array(
                    'result' => 'success',
                    'redirect' => $redirect_url
                );
            }
            // Save in case no transaction id
            $order->save();
        }
        
        wc_add_notice( __( 'An error occured when trying to process payment!', 'wc_makecommerce_domain' ), 'error' );

        return array(
            'result' => 'failure',
        );
    }
    
    /**
     * Return redirect uri for payment method
     * 
     * @since 3.0.0
     */
    protected function _getRedirectUrl( $selected ) {

        foreach ( $this->methods->banklinks as $method ) {

            if ( $selected == $method->country.'_'.$method->name ) {
                return $method->url;
            }
        }

        foreach ( $this->methods->cards as $method ) {

            if ( $selected == 'card_'.$method->name ) {
                return $method->url;
            }
        }

        foreach ( $this->methods->paylater as $method ) {

            if ( $selected == $method->country.'_'.$method->name ) {
                return $method->url;
            }
        }
        
        return false;
    }
    
    /**
     * Variables that trigger return check
     * 
     * @since 3.0.0
     */
    public function return_trigger( $vars ) {

        $vars[] = 'makecommerce_return';
        $vars[] = 'ajax_content';
        $vars[] = 'makecommerce_card_pay';
        $vars[] = 'lang1';

        return $vars;
    }
    
    /**
     * Process return from payment, also handles cart updates among other things
     * 
     * @since 3.0.0
     */
    public function return_trigger_check() {

        if ( intval( get_query_var( 'makecommerce_return' ) ) > 0 ) {

            $return_url = \MakeCommerce\Payment::check_payment( $this->settings );
            
            if ( intval( get_query_var( 'ajax_content' ) ) ) {
                echo json_encode( array( 'redirect' => $return_url ) );
            } else {
                wp_redirect( $return_url );
            }

            exit;
        }
    }

    /**
     * Checks whether this payment gateway is enabled
     * returns true or false
     * 
     * @since 3.0.0
     */
    public function enabled() {
        return filter_var( $this->get_option( 'enabled', 'no' ), FILTER_VALIDATE_BOOLEAN );
    }

    /**
     * Checks whether mc_payments are enabled
     * returns true or false
     *
     * @since 3.0.0
     */
    public function mc_payments_enabled() {
        return get_option( 'mc_payments', 'off' ) === 'on';
    }


    /**
	 * Checks for MK version in  wp_options_table
	 * If it does not exist / is outdated, refresh tables and update version
	 * 
	 * @since	3.0.12
	 */
	public static function mc_version_check( $version ) {

		global $wpdb;
		$banklinks_table = $wpdb->prefix . MAKECOMMERCE_TABLENAME;

		$mc_version = get_option( 'mc_version', 'unset' );

		if ( $mc_version != $version ) {
			// Get payment methods
			$methods = self::get_payment_methods();

			// Drop tables
			$sql = "DROP TABLE IF EXISTS `".$banklinks_table."`";
			$wpdb->query( $sql );

			// Create tables
			\MakeCommerce\Activator::activate();

			// Update tables
			$update = self::insert_payment_methods( $methods );

			// Add version into wp_options
			if ( $mc_version === 'unset' ) {
				add_option( 'mc_version', $version, '', 'yes' );
			} else {
				// Update version in wp_options
				update_option( 'mc_version', $version );
			}
		} 
	}

	/**
	 * Adds correct order received url to an order based on wpml language
	 *
	 * @since 3.5.0
	 */
	public function set_received_order_url( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( !$order ) {
			return;
		}
		$order->update_meta_data( '_makecommerce_order_received_url', $order->get_checkout_order_received_url() );
		$order->save();
	}

	public function woocommerce_blocks_support() {
		if ( class_exists( \Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType::class ) ) {
			require_once plugin_dir_path(__FILE__) . 'blocks/woocommerceblocks.php';
			add_action(
				'woocommerce_blocks_payment_method_type_registration',
				function( \Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
					$payment_method_registry->register( new WooCommerceBlocks );
				}
			);
		}
	}

    /**
     * Sets Payment method Title dynamically, what methods exist
     * @return void
     */
    public function setPaymentMethodTitle(): void
    {
        if (!empty($this->methods->banklinks) && !empty($this->methods->cards)) {
            $this->title = __( 'Bank-links or credit card', 'wc_makecommerce_domain');
        } elseif (!empty($this->methods->banklinks)) {
            $this->title = __( 'Bank-links', 'wc_makecommerce_domain');
        } elseif (!empty($this->methods->cards)) {
            $this->title = __( 'Credit card', 'wc_makecommerce_domain');
        } else {
            $this->title = __( 'Bank-links or credit card', 'wc_makecommerce_domain');
        }
    }
}
