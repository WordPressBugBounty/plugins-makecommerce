<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://makecommerce.net/
 * @since      3.0.0
 *
 * @package    Makecommerce
 * @subpackage Makecommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      3.0.0
 * @package    Makecommerce
 * @subpackage Makecommerce/includes
 * @author     Maksekeskus AS <support@maksekeskus.ee>
 */
class MakeCommerce {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    3.0.0
     * @access   protected
     * @var      MakeCommerce\Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    3.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    3.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    3.0.0
     */
    public function __construct() {

        $this->version = MAKECOMMERCE_VERSION;

        $this->plugin_name = 'makecommerce';

        //configure autoloader
        require_once plugin_dir_path( __FILE__ ) . "autoloader.php";

        $autoLoader = new MakeCommerce\Autoloader;
        $autoLoader->register();

        //register includes and the complete plugin path as MakeCommerce namespace
        $autoLoader->addNamespace( 'MakeCommerce', __DIR__ );
        $autoLoader->addNamespace( 'MakeCommerce', plugin_dir_path( __DIR__ ) );

        //get Maksekeskus API
        require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
        require_once plugin_dir_path( __FILE__ ) . 'vendor/Maksekeskus.php';

        $this->loader = new MakeCommerce\Loader();

        $this->set_locale();

        $plugin_payment = new MakeCommerce\Payment ( $this->get_plugin_name(), $this->get_version(), $this->loader );

        $this->define_api_hooks();

        $plugin_shipping = new MakeCommerce\Shipping( $this->get_plugin_name(), $this->get_version(), $this->loader );
        $this->define_cron_hooks();
        $this->define_admin_hooks();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Makecommerce_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    3.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new MakeCommerce\i18n();

        $this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to cron functionality
     * of the plugin.
     *
     * @since    3.0.0
     * @access   private
     */
    private function define_cron_hooks() {

        $plugin_cron = new MakeCommerce\Cron( $this->get_plugin_name(), $this->get_version() );

        //cron define query vars
        $this->loader->add_filter( 'query_vars', $plugin_cron, 'query_vars' );

        // WP schedule callable action
        $this->loader->add_action( 'mc_banklinks_update_cron', $plugin_cron, 'update_banklinks' );

        $this->loader->add_action( 'parse_request', $plugin_cron, 'parse_update_vars' );
    }

    /**
     * Register all of the hooks related to the api functionality
     * of the plugin.
     *
     * @since    3.0.0
     * @access   private
     */
    private function define_api_hooks() {

        $api = new MakeCommerce\API( $this->get_plugin_name(), $this->get_version(), $this->loader );

        //check if CURL is installed
        $this->loader->add_action( 'admin_notices',	$api, 'check_if_curl_is_loaded');

        //Add admin notice if api is not set up
        if ( !\MakeCommerce::get_api() ) {
            $this->loader->add_action( 'admin_notices', $api, 'api_info_missing' );
        }

        //if API type is updated then refresh the cache
        $this->loader->add_action( 'update_option_mc_api_mode', $api, 'mk_delete_api_cache' );

        //add new customer column to orders view, make it sortable and fill with values
        $this->loader->add_filter( 'manage_edit-shop_order_columns', $api, 'add_ordersview_paymentmethod_column' );
        $this->loader->add_filter( 'manage_edit-shop_order_sortable_columns', $api, 'make_ordersview_paymentmethod_column_sortable' );
        $this->loader->add_action( 'manage_shop_order_posts_custom_column', $api, 'fill_ordersview_paymentmethod_column', 10, 2 );

        // Compatibility with WC HPOS
        $this->loader->add_filter( 'manage_woocommerce_page_wc-orders_columns', $api, 'add_ordersview_paymentmethod_column' );
        $this->loader->add_action( 'manage_woocommerce_page_wc-orders_custom_column', $api, 'fill_ordersview_paymentmethod_column', 10, 2 );

        //hooks that update banklinks
        $this->loader->add_action( 'wp_login', $api, 'admin_login', 10, 2 );

        //adds settings link
        $this->loader->add_filter( 'plugin_action_links_makecommerce/makecommerce.php', $api, 'add_plugin_settings_link' );

    }


    /**
     * Register all of the hooks related to the admin dashboard
     *
     * @since    4.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $dashboard = new MakeCommerce\Admin\Dashboard();

        $this->loader->add_action( 'admin_init', $dashboard, 'initialize' );
        $this->loader->add_action( 'admin_init', $dashboard, 'save_settings' );
        $this->loader->add_action( 'admin_head', $dashboard, 'hide_admin_notices' );
        $this->loader->add_action( 'admin_menu', $dashboard, 'add_menu' );
        $this->loader->add_action( 'admin_menu', $dashboard, 'remove_parent_menu', 15 );
        $this->loader->add_action( 'admin_enqueue_scripts', $dashboard, 'enqueue_dashboard_scripts' );
        $this->loader->add_action( 'add_option_mc_payments', $dashboard, 'sync_enabled_with_mc_payments', 10, 2 );
        $this->loader->add_action( 'update_option_mc_payments', $dashboard, 'sync_enabled_with_mc_payments', 10, 2 );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    3.0.0
     */
    public function run() {

        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     3.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {

        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     3.0.0
     * @return    Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {

        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     3.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {

        return $this->version;
    }

    /**
     * Checks if API is set.
     *
     * @since 3.0.0
     */
    public static function is_api_set() {

        return self::get_api( true );
    }

    /**
     * Displays error in admin that API access is not configured properly
     *
     * @since 3.0.0
     */
    public function mk_admin_error() {

        printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', __( 'Please check that you have configured correctly MakeCommerce API accesses', 'wc_makecommerce_domain' ) );
    }

    /**
     * Returns MK api, unless check is set to true, then returns if api is set
     *
     * @since 3.0.0
     */
    public static function get_api( $check = false ) {

        $mc_api_type = get_option( 'mc_api_mode', false );

        if ( !$mc_api_type ) {
            return false;
        }

        $key_prefix = '';
        if ( $mc_api_type !== 'live' ) {
            $key_prefix = $mc_api_type.'_';
        }

        $mc_shop_id = get_option( 'mc_'.$key_prefix.'shop_id', '' );
        $mc_public_key = get_option( 'mc_'.$key_prefix.'public_key', '' );
        $mc_secret_key = get_option( 'mc_'.$key_prefix.'secret_key', '' );

        if ( !$mc_shop_id || !$mc_public_key || !$mc_shop_id ) {
            return false;
        }

        //if this was just a check, return true
        if ( $check === true ) {
            return true;
        }

        global $MKAPI;
        $MKAPI = new \Maksekeskus\Maksekeskus( $mc_shop_id, $mc_public_key, $mc_secret_key, $mc_api_type === 'live' ? false : true );

        return $MKAPI;
    }

    /**
     * Sets up shop getConfig request parameters
     *
     * @since 3.0.0
     */
    public static function config_request_parameters( $module = "" ) {

        return array(
            'environment' => json_encode( array(
                'system' => array( 'wordpress' => get_bloginfo( 'version' ), "php" => phpversion() ),
                'platform' => 'woocommerce '. WC_VERSION,
                'module' => $module,
            )),
        );
    }

    /**
     * Sets up shop getConfig request parameters
     *
     * @since 3.0.0
     */
    public static function get_sdk_config() {
        $conf = [
            "module"           => "MakeCommerce",
            "module_version"   => MAKECOMMERCE_VERSION,
            "platform"         => "WooCommerce",
            "hpos_enabled"     => filter_var( get_option( 'woocommerce_custom_orders_table_enabled' ), FILTER_VALIDATE_BOOLEAN ),
            "payments_enabled" => filter_var( get_option( 'mc_payments' ), FILTER_VALIDATE_BOOLEAN ),
            "shipping_enabled" => filter_var( get_option( 'mc_shipping' ), FILTER_VALIDATE_BOOLEAN )
        ];

        if (class_exists('WooCommerce')) {
            $conf['platform_version'] = WC()->version;
        }

        return $conf;
    }

    /**
     * Enqueues javascript with parameters.
     *
     * @since	3.0.9
     */
    public static function mc_enqueue_script( $handle, $src, $data = [], $deps = [], $external = false ) {
        //check if the script is already enqueued. Fixes issue for misbehaving plugins who the queue again which causes inline script to be added more than once.
        //https://github.com/wp-media/wp-rocket/issues/3125
        if ( wp_script_is( $handle, 'enqueued' ) ) {
            return;
        }

        $version = null;
        if ( !$external ) {

            $version = filemtime( $src );
            $src = plugin_dir_url( $src ) . basename( $src );
        }

        wp_register_script( $handle, $src, $deps, $version );
        
        if ( !empty( $data ) ) {
            wp_add_inline_script( $handle, 'const ' . $handle . ' = ' . json_encode( $data ), 'before' );
        }

        wp_enqueue_script( $handle );
    }
}