<?php

namespace MakeCommerce;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://makecommerce.net/
 * @since      3.0.0
 *
 * @package    Makecommerce
 * @subpackage Makecommerce/shipping
 */

/**
 * The shipping-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    MakeCommerce
 * @subpackage MakeCommerce/shipping
 * @author     Maksekeskus AS <support@maksekeskus.ee>
 */
require_once plugin_dir_path( __FILE__ ) . '../vendor-prefixed/autoload.php';

use MakeCommerce;
use MakeCommercePrefix\MakeCommerceShipping\SDK\Http\MakeCommerceClient;
use Automattic\WooCommerce\Blocks\Registries\BlockRegistry;
use MakeCommercePrefix\Twig\Environment;
use MakeCommercePrefix\Twig\Loader\FilesystemLoader;
use MakeCommercePrefix\Twig\TwigFunction;
use MakeCommerceShippingBlocks;

class Shipping {

    /**
     * The ID of this plugin.
     *
     * @since    3.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;
    /**
     * The version of this plugin.
     *
     * @since    3.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    protected $client;
    private $version;
    private $loader;
    private Shipping\Label $label;
    private Shipping\Product $product;
    private Shipping\Order $order;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     *
     * @since    3.0.0
     */
    public function __construct( $plugin_name, $version, Loader $loader ) {

        $this->plugin_name   = $plugin_name;
        $this->version       = $version;
        $this->loader        = $loader;

        // Initialize MK client
        $this->client = self::init_client();

        //loads all child classes such as label, admin specific functions, email functions
        $this->load_children();

        // Register WooCommerce Hooks
        $this->define_hooks();

        // Add blocks support
        add_action( 'woocommerce_blocks_loaded', [$this, 'woocommerce_blocks_support'] );
    }

    public static function init_client(): MakeCommerceClient {
        $mode = get_option( 'mc_api_mode', 'live' );
        $shop_id     = get_option( $mode === 'test' ? 'mc_test_shop_id' : 'mc_shop_id' );
        $secret_key  = get_option( $mode === 'test' ? 'mc_test_secret_key' : 'mc_secret_key' );
        $instance_id = get_option('mc_instance_id', '');

        $client_conf = \MakeCommerce::get_sdk_config();

        $client = new MakeCommerceClient($mode, $shop_id, $secret_key, $instance_id, $client_conf);

        $shortLocale = \MakeCommerce\i18n::get_locale() ?: substr(get_user_locale() ?: get_locale(), 0, 2);

        if (in_array($shortLocale, ['et', 'en', 'lv', 'lt'], true)) {
            $client->setLocale($shortLocale);
        }

        return $client;
    }

    /**
     * Load all supporting child classes
     *
     * @since 3.0.0
     */
    public function load_children()
    {

        //load label class (everything to do with shipping labels and printing)
        $this->label = new Shipping\Label( $this->loader );

        //load product class (everything to do with shipping and products)
        $this->product = new Shipping\Product( $this->loader );

        //load order class (everything to do with shipping and orders)
        $this->order = new Shipping\Order( $this->loader );
    }

    /**
     * Register all WooCommerce actions and filters
     */
    private function define_hooks()
    {
        //load scripts and styles
        $this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts' );

        //load scripts and styles
        $this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts' );

        // Refresh WC shipping cache, so MakeCommerce methods are loaded
        $this->loader->add_action( 'add_option_mc_shipping', $this, 'remove_wc_shipping_cache' );
        $this->loader->add_action( 'update_option_mc_shipping', $this, 'remove_wc_shipping_cache' );

        //add MC shipping method
        $this->loader->add_filter('woocommerce_shipping_methods', $this, 'add_shipping_methods');
        // Remove MC shipping section under WC Shipping settings
        $this->loader->add_filter('woocommerce_get_sections_shipping', $this, 'remove_mc_shipping_section');

        // Register shipment on status processing
        $this->loader->add_filter('woocommerce_order_status_processing', $this, 'register_shipment');

        // Add shipping metadata to new order
        $this->loader->add_action('woocommerce_new_order', $this, 'add_order_shipping_meta');

        // AJAX calls to render the pickup points
        $this->loader->add_action('wp_ajax_get_carrier_machines', $this, 'get_carrier_machines');
        $this->loader->add_action('wp_ajax_nopriv_get_carrier_machines', $this, 'get_carrier_machines');

        // Phone number is needed to register shipment
        $this->loader->add_filter('woocommerce_billing_fields', $this, 'set_phone_required');

        $this->loader->add_action( 'woocommerce_review_order_after_shipping', $this, 'mc_pickuppoint_after_shipping_details');
    }

    /**
     * Inject a MakeCommerce container after shipping details on WooCommerce checkout.
     * To that container pickup point list will be rendered in
     *
     * @since 4.0.2
     */
    public function mc_pickuppoint_after_shipping_details() {
        echo '<tr class="makecommerce-pickuppoint-wrapper" hidden>
                <td colspan="2" class="makecommerce-pickuppoint-table-data"></td>
            </tr>';
    }

    /**
     * Removes WC shipping transients
     *
     * @since 4.0.0
     */
    public function remove_wc_shipping_cache()
    {
        delete_transient('wc_shipping_method_count');
        delete_transient('timeout_wc_shipping_method_count');
    }

    /**
     * At least one phone nr is needed to create shipment
     * Set at least billing_phone required
     * @since 4.0.0
     */
    public function set_phone_required($fields)
    {
        if (isset($fields['billing_phone'])) {
            $fields['billing_phone']['required'] = true;
        }
        return $fields;
    }

    /**
     * Add all different shipping methods to WooCommerce
     *
     * @since 4.0.0
     */
    public function add_shipping_methods($methods)
    {
        $methods['makecommerce_shipping'] = 'MakeCommerce\Shipping\Method';
        return $methods;
    }

    /**
     * Remove MakeCommerce shipping section from Woocommerce shipping settings
     *
     * @since 3.0.0
     */
    public function remove_mc_shipping_section($sections)
    {
        unset($sections['makecommerce_shipping']);
        return $sections;
    }

    /**
     * Adds metadata to order about shipping
     *
     * @since 3.0.0
     */
    public function add_order_shipping_meta($order_id)
    {

        // Ensure the shipping method and pickup point ID are retrieved safely
        $machine = isset($_POST['_mc_machine_id']) ? sanitize_text_field($_POST['_mc_machine_id']) : null;
        $method = isset($_POST['shipping_method'][0]) ? sanitize_text_field($_POST['shipping_method'][0]) : null;

        $order = wc_get_order($order_id);

        if ($method && str_starts_with($method, 'mc_')) {
            // Remove "courier_" and "pickuppoint_" from the method to extract the carrier name
            $carrier = str_replace(['mc_courier_', 'mc_pickuppoint_'], '', $method);
            $method = str_replace('mc_', '', $method);
            $order->add_meta_data('_mc_shipping_method', $method, true);
            $order->add_meta_data('_mc_shipping_carrier', $carrier, true);
        }

        if ($machine && (stripos($method, 'pickuppoint') !== false)) {
            $order->add_meta_data('_mc_machine_id', $machine, true);
        }

        $order->save();
    }

    /**
     * Registers shipment(s) via MakeCommerce API
     *
     * @since 3.0.0
     */
    public function register_shipment($post_id)
    {
        $order = wc_get_order($post_id);

        if (!$this->is_shipment_eligible($order)) {
            return;
        }

        $carrier = $order->get_meta('_mc_shipping_carrier');
        $machine = $order->get_meta('_mc_machine_id');
        $full_method_name = $order->get_meta('_mc_shipping_method');
        $shipping_info = $this->get_order_shipping_information($order);
        $destination_data = $this->get_destination_data($order, $machine);
        $method = ($machine && (stripos($full_method_name, 'pickuppoint') !== false)) ? $this->client::TYPE_PICKUPPOINT : $this->client::TYPE_COURIER;

        $shipment = [
            'order' => [
                'id' => $order->get_id(),
                'reference' => $order->get_order_number()
            ],
            'recipient' => [
                'name' => $shipping_info['recipient_name'],
                'phone' => $shipping_info['phone'],
                'email' => $shipping_info['email'],
            ],
        ];

        $shipment = [array_merge($shipment, $destination_data)];

        try {
            $shipment = $this->client->createShipment($carrier, $shipment, $method);
        } catch (\Exception $e) {
            error_log('Error while creating shipment [' . $e->getMessage() . ']');
            return;
        }

        if (!empty($shipment->trackingId)) {
            $order->update_meta_data('_mc_shipment_id', sanitize_text_field($shipment->trackingId));
            $order->delete_meta_data('_mc_shipment_error');
        } elseif (!empty($shipment->errorMessage)) {
            $order->update_meta_data('_mc_shipment_error', sanitize_text_field($shipment->message));
        }

        if (!empty($shipment->trackingLink)) {
            $order->update_meta_data('_mc_tracking_link', sanitize_text_field($shipment->trackingLink));
        }

        $order->save();
    }

    /**
     * Returns shipping information for an order.
     *
     * @since 3.0.9
     */
    public function get_order_shipping_information($order)
    {
        $shipping_address = $order->get_address('shipping');
        $billing_address = $order->get_address('billing');

        return [
            'first_name' => !empty(trim($shipping_address['first_name'])) ? $shipping_address['first_name'] : ($billing_address['first_name']),
            'last_name' => !empty(trim($shipping_address['last_name'])) ? $shipping_address['last_name'] : ($billing_address['last_name']),
            'phone' => !empty( trim( $shipping_address['phone'] ) ) ? $shipping_address['phone'] : ( !empty( trim( $billing_address['phone'] ) ) ? $billing_address['phone'] : '-' ),
            'email' => !empty(trim($order->get_meta('_shipping_email', true))) ? $order->get_meta('_shipping_email', true) : ($billing_address['email']),
            'recipient_name' => trim(
                (!empty(trim($shipping_address['first_name'])) ? $shipping_address['first_name'] : ($billing_address['first_name'])) . ' ' .
                (!empty(trim($shipping_address['last_name'])) ? $shipping_address['last_name'] : ($billing_address['last_name']))
            )
        ];
    }

    /**
     * Checks if an order is eligible for shipment
     *
     * @param WC_Order $order
     * @return bool
     */
    private function is_shipment_eligible($order)
    {
        if (!$order) return false;

        $oldId = $order->get_meta('_mc_shipment_id', true);
        if (!empty($oldId) && strlen($oldId) > 6) return false;

        $status = (string)$order->get_status();
        if (!in_array($status, ['completed', 'processing'], true)) return false;

        return !empty($order->get_meta('_mc_shipping_method', true));
    }

    /**
     * Gets destination data for an order
     *
     * @param WC_Order $order
     * @return array
     */
    private function get_destination_data($order, $machine)
    {
        $country = $order->get_shipping_country() ?: WC()->countries->get_base_country();
        if ($machine) {
            return [
                'destination' => [
                    'id' => $machine,
                    'country' => $country,
                ],
            ];
        }

        return [
            'destination' => [
                'zip' => $order->get_shipping_postcode(),
                'country' => $country,
                'city' => $order->get_shipping_city(),
                'street' => !empty($order->get_shipping_address_2())
                    ? $order->get_shipping_address_2()
                    : $order->get_shipping_address_1(),
            ],
        ];
    }


    /**
     * AJAX handler to return carrier pickup machines for the current customer's shipping country.
     *
     * Retrieves carrier ID and optionally selected machine ID from the request,
     * fetches available machines for the current shipping country, and returns them as JSON.
     *
     * @return void
     */
    public function get_carrier_machines()
    {
        $carrier = isset($_REQUEST['mc_carrier_id']) ? sanitize_text_field($_REQUEST['mc_carrier_id']) : '';
        $selected_machine = isset($_REQUEST['selected_machine']) ? sanitize_text_field($_REQUEST['selected_machine']) : '';
        $country = isset($_REQUEST['country']) ? sanitize_text_field($_REQUEST['country']) : WC()->customer->get_shipping_country();

        if (empty($country)) {
            $country = self::get_shipping_country();
        }

        if (!$carrier || !$country) {
            return;
        }

        if (function_exists( 'WC' ) && WC()->session &&
            WC()->session->get('mc_selected_country') !== $country) {
            // Country has changed → remove shipping cache, so new rates are fetched for country
            $this->remove_wc_shipping_cache();
            // Update session
            WC()->session->set('mc_selected_country', $country);
        }

        $machines = self::get_carrier_country_machines($carrier, $country, $selected_machine);


        if ( $selected_machine === '') {
            return wp_send_json(['machines' => $this->select_closest_machine($machines)]);
        }

        wp_send_json(['machines' => $machines]);
    }

    public function select_closest_machine(array $machines): array
    {
        $closestMachine = null;
        $closestDistance = INF;

        $coords = WC()->session->get('mc_coordinates');
        $lat = isset($coords['lat']) && is_numeric($coords['lat']) ? (float) $coords['lat'] : null;
        $long = isset($coords['lng']) && is_numeric($coords['lng']) ? (float) $coords['lng'] : null;

        // If no lat and long then do not proceed
        if ($lat === null || $long === null) {
            return $machines;
        }

        foreach ($machines as $city => &$cityMachines) {
            foreach ($cityMachines as &$machine) {
                if (!isset($machine['latitude'], $machine['longitude'])) {
                    continue;
                }

                $machineLat = (float) $machine['latitude'];
                $machineLng = (float) $machine['longitude'];

                $distance = $this->haversineDistance($lat, $long, $machineLat, $machineLng);

                if ($distance < $closestDistance) {
                    $closestDistance = $distance;
                    $closestMachine = &$machine;
                }
            }
        }

        if ($closestMachine !== null) {
            $closestMachine['selected'] = true;
        }

        return $machines;
    }

    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        // use Haversine Formula to calculate
        // Computes the square of half the chord length between points
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        // Applies arc haversine to get the central angle in radians.
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        // Calculate distance in km
        return $earthRadius * $c;
    }


    /**
     * Retrieve a specific machine by ID from a given carrier and country.
     *
     * Iterates through all machines in the given country for the carrier and returns the one matching the ID.
     *
     * @param string $carrier The carrier identifier.
     * @param string $id The ID of the machine to find.
     * @param string $country The 2-letter country code.
     * @return array|false    The matched machine data or false if not found.
     */
    public static function mk_get_machine($carrier, $id, $country)
    {
        $machines = self::get_carrier_country_machines($carrier, $country);

        foreach ($machines as $city) {
            foreach ($city as $machine) {
                if ($machine['id'] == $id) {
                    return $machine;
                }
            }
        }

        return false;
    }

    /**
     * Fetches all pickup machines for a carrier and country, grouped by city.
     *
     * Uses the MakeCommerce client to request destination data, transforms it into a
     * structured format grouped by city, and flags the selected machine if matched.
     *
     * @param string $carrier The carrier identifier.
     * @param string $country The 2-letter country code.
     * @param string $selected_machine (optional) ID of the machine to mark as selected.
     * @return array                   List of machines grouped by city.
     */
    public static function get_carrier_country_machines($carrier, $country, $selected_machine = '')
    {
        try{
            $client = self::init_client();
            // Fallback if the country should be empty
            if (empty($country)) {
                self::get_shipping_country();
            }

            $raw_machines = $client->listCarrierDestinations($carrier, $country);

            return array_reduce($raw_machines, function ($result, $item) use ($selected_machine) {
                $result[$item->city][] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'address' => $item->address ?? '',
                    'availability' => $item->availability ?? '',
                    'city' => $item->city ?? '',
                    'zip' => $item->zip ?? '',
                    'longitude' => $item->x ?? '',
                    'latitude' => $item->y ?? '',
                    'selected' => $item->id === $selected_machine
                ];
                return $result;
            }, []);
        } catch (\Exception $e) {
            error_log('Error while fetching pickup points [' . $e->getMessage() . ']');
            return [];
        }
    }

    /**
     * Register the JavaScript.
     *
     * @since 3.0.0
     */
    public function enqueue_scripts()
    {
        if (!is_cart()){
            wp_enqueue_style('pickup-point-style', "https://static.maksekeskus.ee/modules/woocommerce/css/pickup-point.css");

            MakeCommerce::mc_enqueue_script(
                'MC_PARCELMACHINE_JS',
                "https://static.maksekeskus.ee/modules/woocommerce/js/pickuppoint.js",
                [
                    'placeholder' => __('Select pickup point', 'wc_makecommerce_domain'),
                    'loadingPlaceholder' => __('Loading pickup points...', 'wc_makecommerce_domain'),
                    'ajaxurl' => admin_url( 'admin-ajax.php' )
                ],
                ['jquery'],
                true
            );
        }
    }

    protected function render_template(string $template, array $data = []): void
    {
        $loader = new FilesystemLoader(plugin_dir_path(__DIR__)  . 'admin/templates');
        $twig = new Environment($loader);

        $twig->addFunction(new TwigFunction('__', function (string $text, string $domain = 'wc_makecommerce_domain') {
            return __($text, $domain);
        }));

        $baseData = [
            'path' => plugin_dir_url(__DIR__),
            's3_path' => 'https://static.maksekeskus.ee/img/woocommerce/'
        ];

        echo $twig->render($template, array_merge($baseData, $data));
    }

    /**
     * Returns customers shipping country
     * if not, returns shop base country
     * if not, default country by selected language
     *
     * @since 4.0.3
     */
    protected static function get_shipping_country()
    {

        global $woocommerce;

        if ($woocommerce->customer && $woocommerce->customer->get_shipping_country()) {
            return $woocommerce->customer->get_shipping_country();
        }

        if ($woocommerce->countries && $woocommerce->countries->get_base_country()) {
            return $woocommerce->countries->get_base_country();
        }

        $localeToCountry = array(
            'et' => 'ee',
            'lv' => 'lv',
            'lt' => 'lt',
            'fi' => 'fi',
        );

        $locale = \MakeCommerce\i18n::get_two_char_locale();
        if (array_key_exists($locale, $localeToCountry)) {
            return $localeToCountry[$locale];
        }

        return 'EE';
    }

    /**
     * Initializes WooCommerce Blocks support for MakeCommerce shipping features.
     *
     * Loads the block integration class and calls its initialization method to:
     * - Register shipping-related blocks
     * - Extend the Store API schema
     *
     * @return void
     */
    public function woocommerce_blocks_support(): void
    {
        if ( class_exists( '\Automattic\WooCommerce\Blocks\Package' ) ) {
            require_once __DIR__ . '/blocks/mc-shipping-blocks.php';

            $extend_core = new MakeCommerceShippingBlocks();
            $extend_core->init();
        }
    }
}
