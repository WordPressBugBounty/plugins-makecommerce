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

        $details = ['package' => $this->filter_package_details($package)];
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

        } catch (\Throwable $e) {
            $this->logger->error(
                __('Unable to retrieve MakeCommerce shipping rates. ' . json_encode($e->getMessage()), 'wc_makecommerce_domain'),
                $this->log_context
            );
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

     *
     * @param array $package The full WooCommerce package.
     * @return array Filtered package with only required fields.
     */
    private function filter_package_details($package)
    {
        try {
            $package['contents'] = $this->filter_package_contents($package['contents'] ?? []);
            unset($package['rates']);

            return $package;
        } catch (\Exception $e) {
            $this->logger->error(__('Error filtering package details: ' . $e->getMessage(), 'wc_makecommerce_domain'), $this->log_context);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Filter package details to only include necessary fields for API request.
     * Reduces payload size by excluding unnecessary data.
     *
     * @param array $contents Cart contents from package.
     * @return array Filtered contents.
     */
    private function filter_package_contents($contents)
    {
        $filtered_contents = [];

        foreach ($contents as $cart_item_key => $cart_item) {
            $filtered_contents[$cart_item_key] = [
                'product_id' => $cart_item['product_id'] ?? null,
                'variation_id' => $cart_item['variation_id'] ?? null,
                'quantity' => $cart_item['quantity'] ?? 0,
                'line_subtotal' => $cart_item['line_subtotal'] ?? 0,
                'line_subtotal_tax' => $cart_item['line_subtotal_tax'] ?? 0,
                'line_total' => $cart_item['line_total'] ?? 0,
                'line_tax' => $cart_item['line_tax'] ?? 0,
                'data' => $this->extract_product_data($cart_item['data'] ?? null),
            ];
        }

        return $filtered_contents;
    }

    /**
     * Extract only needed product data fields.
     *
     * @param mixed $product_data WC_Product object or data array.
     * @return array Filtered product data.
     */
    private function extract_product_data($product_data)
    {
        if (empty($product_data)) {
            return [];
        }

        // If it's a WC_Product object, extract data
        if (is_object($product_data) && method_exists($product_data, 'get_data')) {
            $data = $product_data->get_data();
        } elseif (is_object($product_data)) {
            $data = get_object_vars($product_data);
        } else {
            $data = $product_data;
        }

        // Return only needed fields
        return [
            'id' => $data['id'] ?? '',
            'name' => $data['name'] ?? '',
            'sku' => $data['sku'] ?? '',
            'global_unique_id' => $data['global_unique_id'] ?? '',
            'price' => $data['price'] ?? '',
            'regular_price' => $data['regular_price'] ?? '',
            'sale_price' => $data['sale_price'] ?? '',
            'total_sales' => $data['total_sales'] ?? 0,
            'tax_status' => $data['tax_status'] ?? '',
            'tax_class' => $data['tax_class'] ?? '',
            'weight' => $data['weight'] ?? '',
            'length' => $data['length'] ?? '',
            'width' => $data['width'] ?? '',
            'height' => $data['height'] ?? '',
            'parent_id' => $data['parent_id'] ?? '',
            'virtual' => $data['virtual'] ?? '',
            'downloadable' => $data['downloadable'] ?? '',
            'category_ids' => $data['category_ids'] ?? [],
            'shipping_class_id' => $data['shipping_class_id'] ?? [],
        ];
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
