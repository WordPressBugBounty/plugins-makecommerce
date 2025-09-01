<?php

namespace MakeCommerce\Shipping;

/**
 * Provides all the shipping methods available from MK
 *
 * @since 4.0.0
 */

use MakeCommercePrefix\GuzzleHttp\Exception\GuzzleException;
use WC_Shipping_Method;
use MakeCommerce\Shipping;

class Method extends WC_Shipping_Method
{
    protected $logger;
    protected $log_context;

    public function __construct()
    {
        $this->logger = wc_get_logger();
        $this->log_context = [ 'source' => 'makecommerce-errors' ];

        $this->id = 'makecommerce_shipping';
        $this->method_title = __('MakeCommerce Shipping Method');
        $this->method_description = __('All shipping methods provided by MakeCommerce');

        $this->enabled = 'yes';
        $this->title = "MakeCommerce Shipping+";

        $this->init();

        $this->set_hooks();
    }

    /**
     * Init your settings
     *
     * @access public
     * @return void
     */
    public function init()
    {
        $this->init_form_fields();
        $this->init_settings();
    }

    public function set_hooks()
    {
        add_action('woocommerce_after_checkout_validation', array($this, 'check_checkout_fields'));
        add_filter('woocommerce_package_rates', array($this, 'hide_mc_shipping_for_subscriptions'), 10, 2);
    }

    /**
     * Remove MakeCommerce shipping if package has subscription
     *
     * @access public
     */
    public function hide_mc_shipping_for_subscriptions( $rates, $package ) {

        // Ensure the WooCommerce and Subscriptions plugin is active
        if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Subscriptions_Product' ) ) {
            return $rates;
        }

        $found_subscription_in_package = false;

        foreach ( $package['contents'] as $cart_item_key => $cart_item ) {
            $product = $cart_item['data'];

            // Check if the product is a subscription type
            if ( \WC_Subscriptions_Product::is_subscription( $product ) ) {
                $found_subscription_in_package = true;
                break;
            }
        }

        // If a subscription product was found in this package, hide ALL MakeCommerce rates.
        if ( $found_subscription_in_package ) {
            // Check if the rate ID starts with 'mc_'
            $rates_to_keep = array_filter($rates, function ($rate_id) {
                return !(str_starts_with($rate_id, 'mc_'));
            }, ARRAY_FILTER_USE_KEY);
            $rates = $rates_to_keep;
        }

        return $rates;
    }

    /**
     * Add shipping methods based on destination country
     *
     * @access public
     * @param array $package
     * @return void
     * @throws GuzzleException
     */
    public function calculate_shipping($package = [])
    {
        $mc_shipping_setting = get_option('mc_shipping', 'off');
        if ($mc_shipping_setting !== 'on') {
            return;
        }

        $totalWeight = $this->getCartTotalWeight($package['contents']);
        $dst = $package['destination']['country'];
        $address = $package['destination']['address'];
        $city = $package['destination']['city'];
        $postcode = $package['destination']['postcode'];

        $details = ['package' => $this->normalize_data($package)];
        $details = $this->add_woo_conf($details);

        $location = [];
        try {
            $client = Shipping::init_client();
            $rates = $client->getRates([
                'details' => $details,
                'weight' => $totalWeight,
                'destination' => $dst,
                'location' => [
                    'address' => $address,
                    'city' => $city,
                    'zip' => $postcode,
                ]
            ], $location);
            
            $decoded_location = is_string($location) ? json_decode($location) : $location;
            if (
                is_array($decoded_location) &&
                isset($decoded_location[0]->latitude, $decoded_location[0]->longitude) &&
                WC()->session
            ) {
                WC()->session->set('mc_coordinates', [
                    'lat' => (float) $decoded_location[0]->latitude,
                    'lng' => (float) $decoded_location[0]->longitude,
                ]);
            }

            foreach ($rates as $method => $carriers) {
                // If does not fit and is pickuppoint, then do not add shipping rate
                if ($method === 'pickuppoint' && !$this->fits_parcel_machine($package)) {
                    continue;
                }

                foreach ($carriers as $carrier) {
                    $this->add_rate([
                        'id'        => 'mc_' . $method . '_' . $carrier->carrier,
                        'label'     => $carrier->title,
                        'cost'      => $carrier->price / 100,
                        'taxes'     => '',
                        'calc_tax'  => 'per_order',
                    ]);
                }

            }

        } catch (\Exception $e) {
            $this->logger->error(__('Unable to retrieve MakeCommerce shipping rates. ' . json_encode($e->getMessage()), 'wc_makecommerce_domain'), $this->log_context);
        }
    }

    /**
     * Checks if there is a product that doesn't fit in parcelmachine
     *
     * @since 3.0.0
     */
    private function fits_parcel_machine( $package ) {

        foreach ( $package['contents'] as $line ) {
            if ( get_post_meta( $line['product_id'], '_no_parcel_machine', true ) === 'yes' ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add check for pickup point selectbox
     * Checks if something is chosen
     *
     * @since 3.0.4
     */
    public function check_checkout_fields()
    {
        $shipping_method = !empty($_POST['shipping_method']) ? $_POST['shipping_method'] : false;

        if ($shipping_method[0] && str_contains($shipping_method[0], 'pickuppoint')) {
            if (empty($_POST['_mc_machine_id'])) {
                wc_add_notice(
                    __('<strong>Parcel machine</strong> is a required field.', 'wc_makecommerce_domain'),
                    'error'
                );
            }
        }
    }

    /**
     * @param $contents
     * @return int
     */
    public function getCartTotalWeight($contents): int
    {
        $total_weight = 0;

        foreach ($contents as $item) {
            $productWeight = $this->get_product_weight_in_grams($item['product_id']);

            if ($productWeight) {
                $total_weight += ($productWeight * $item['quantity']);
            }
        }

        return $total_weight;
    }


    /**
     * Convert product weight to grams.
     *
     * @param int $product_id The product ID.
     * @return float The weight in grams.
     */
    function get_product_weight_in_grams($product_id)
    {
        $product = wc_get_product($product_id);
        if (!$product) {
            return 0;
        }

        $weight = $product->get_weight();
        $unit = get_option('woocommerce_weight_unit');

        if (!$weight) {
            return 0;
        }

        // Convert all weights to grams
        switch ($unit) {
            case 'kg': // Kilograms to grams
                $weight *= 1000;
                break;
            case 'lbs': // Pounds to grams (1 lb = 453.592 g)
                $weight *= 453.592;
                break;
            case 'oz': // Ounces to grams (1 oz = 28.3495 g)
                $weight *= 28.3495;
                break;
            case 'g':
            default:
                break;
        }

        return $weight;
    }

    /**
     * Recursively convert all objects to arrays.
     */
    private function normalize_data($data)
    {
        if (is_object($data)) {
            if (method_exists($data, 'get_data')) {
                return $this->normalize_data($data->get_data());
            }
            return $this->normalize_data(get_object_vars($data));
        }

        if (is_array($data)) {
            return array_map([$this, 'normalize_data'], $data);
        }

        return $data;
    }

    /**
     * Add needed conf to details array
     */
    private function add_woo_conf(array $details): array
    {
        $details['configuration'] = [
            'weight_unit' => get_option('woocommerce_weight_unit', 'kg')
        ];

        return $details;
    }
}
