<?php

namespace MakeCommerce\Payment\Gateway\WooCommerce;

use MakeCommerce\Payment;

class Methods
{

    public $banklinks = array();
    public $banklinks_grouped = array();
    public $cards = array();
    public $paylater = array();
    public $paylater_grouped = array();

    private $id;
    private $init;
    private $settings;

    private $bank_orderings;

    /**
     * Construct payment methods
     *
     * @since 3.0.0
     */
    public function __construct($id, $init, $settings)
    {

        $this->id = $id;
        $this->init = $init;
        $this->settings = $settings;

        $this->load_methods();
    }

    /**
     * Loads all payment methods from the database
     *
     * @since 3.0.0
     */
    public function load_methods()
    {

        global $wpdb;

        $internationalMethods = [
            'revolut' => 'revolut',
            'n26' => 'n26',
            'wise' => 'wise'
        ];

        $manual_renewals = false;
        $has_subscriptions = false;
        if (class_exists('\WC_Subscriptions_Cart')) {
            // On change payment method, check for subscription
            $subscription_order = false;
            if (isset($_GET['change_payment_method'])) {
                $post_type = get_post_type($_GET['change_payment_method']);
                if ($post_type == 'shop_subscription') {
                    $subscription_order = true;
                }
            }
            if (\WC_Subscriptions_Cart::cart_contains_subscription() || $subscription_order) {
                $has_subscriptions = true;

                if (class_exists('\WC_Subscriptions_Admin')
                    && get_option(\WC_Subscriptions_Admin::$option_prefix . '_accept_manual_renewals', 'no') === 'yes'
                    && get_option(\WC_Subscriptions_Admin::$option_prefix . '_turn_off_automatic_payments', 'no') === 'yes'
                ) {
                    $manual_renewals = true;
                }
            }
        }

        $methods = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . MAKECOMMERCE_TABLENAME);

        if (count($methods)) {

            foreach ($methods as $method) {
                // TODO: Old plugin uses .png, new design uses .svg. How to keep compatible?
                // Exception for kniks
                if (preg_match('/gift_card\.png$/i', $method->logo_url)) {
                    $method->logo_url = preg_replace('/gift_card\.png$/i', 'kniks.svg', $method->logo_url);
                } else {
                    $method->logo_url = preg_replace('/\.png$/i', '.svg', $method->logo_url);
                }

                if ($method->type == 'banklink' || $method->type == 'other') {
                    # If manual renewals are enabled, allow banklinks
                    if (!$has_subscriptions || $manual_renewals) {
                        $banklinks[] = $banklinks_grouped[$method->country][] = $method;
                        if (in_array($method->name, $internationalMethods)) {
                            $intMethod = clone($method);
                            $intMethod->country = 'other';
                            $banklinks[] = $banklinks_grouped['other'][] = $intMethod;
                            unset($internationalMethods[$method->name]);
                        }
                    }
                } elseif ($method->type == 'card') {
                    if ($has_subscriptions && !$manual_renewals) {
                        if (in_array($method->name, ['visa', 'mastercard'])) {
                            $cards[] = $method;
                        }
                    } else {
                        $cards[] = $method;
                    }
                } elseif ($method->type == 'payLater') {
                    if (!$has_subscriptions) {
                        $paylater[] = $paylater_grouped[$method->country][] = $method;
                    }
                }
            }


            if (!$has_subscriptions) {

                if (isset($paylater)) {
                    $this->paylater = $paylater;
                }

                if (isset($paylater_grouped)) {
                    $this->paylater_grouped = $paylater_grouped;
                }
            }

            # Only when the order does not contain subscriptions or has manual renewals enabled
            if (!$has_subscriptions || $manual_renewals) {
                if (isset($banklinks)) {
                    $this->banklinks = $banklinks;
                }

                if (isset($banklinks_grouped)) {
                    $this->banklinks_grouped = $banklinks_grouped;
                }
            }

            if (isset($cards)) {
                $this->cards = $cards;
            }

        }
    }

    /**
     * Returns customers default country if they are logged in
     * if not, returns default country by selected language
     *
     * @since 3.0.0
     */
    private function get_default_country()
    {

        global $woocommerce;

        if ($woocommerce->customer) {

            $customerCountry = strtolower($woocommerce->customer->get_billing_country());
            if (array_key_exists($customerCountry, $this->banklinks_grouped)) {
                return $customerCountry;
            } else {
                return 'other';
            }
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

        return key($this->banklinks_grouped);
    }

    /**
     * Show available payment methods in checkout
     *
     * @since 3.0.0
     */
    public function show_methods()
    {
        ?>
        <div class="makecommerce-payment-methods">

            <?php
            $this->hidden_select_box_payment_methods();

            $this->method_list();
            ?>
        </div>
        <?php
    }

    /**
     * Display country selector for payment methods
     *
     * @since 3.0.1
     */
    private function method_list_countries()
    {

        ?>
        <div class="mb-3">
            <label for="makecommerce_customer_country_picker" class="d-none"></label>
            <select class="form-select" name="makecommerce_country_picker" id="makecommerce_customer_country_picker">
                <?php foreach (array_keys($this->banklinks_grouped) as $country): ?>
                    <option value="<?php echo $country; ?>" <?php if ($this->get_default_country() == $country) echo 'selected'; ?>>
                        <?php echo __($this->get_country_name_from_code($country), 'wc_makecommerce_domain'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /**
     * Hidden selectbox for all payment methods
     *
     * @since 3.0.1
     */
    private function hidden_select_box_payment_methods()
    {

        ?>
        <select id="<?php echo $this->id; ?>" class="d-none" name="PRESELECTED_METHOD_<?php echo $this->id; ?>">
            <option value=""></option>
            <?php foreach ($this->banklinks as $method): ?>
                <option value="<?php echo $method->country . '_' . $method->name; ?>"><?php echo strtoupper($method->country) . ' - ' . ucfirst($method->display_name); ?></option>
            <?php endforeach; ?>

            <?php foreach ($this->cards as $method): ?>
                <option value="card_<?php echo $method->name; ?>"><?php echo ucfirst($method->display_name); ?></option>
            <?php endforeach; ?>

            <?php foreach ($this->paylater as $method): ?>
                <option value="<?php echo $method->country . '_' . $method->name; ?>"><?php echo strtoupper($method->country) . ' - ' . ucfirst($method->display_name); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Display payment methods as a widget
     *
     * @since 3.0.0
     */
    private function method_list()
    {

        echo '<input type="hidden" id="makecommerce_customer_country" value="' . $this->get_default_country() . '"/>
        <div class="makecommerce-picker bg-white p-3">';

        global $woocommerce;

        $cartTotal = $woocommerce->cart->total;

        if ($this->banklinks || $this->cards) {

            //dont show other countries if there are no card payments available.
            if ($this->cards && empty($this->banklinks_grouped['other'])) {
                $this->banklinks_grouped['other'] = array();
            }

            $this->method_list_countries();

            foreach ($this->banklinks_grouped as $country => $methods) {

                echo '<div class="makecommerce_country_methods" id="makecommerce_country_methods_' . $country . '">';

                $this->banklink_list($cartTotal, $methods);

                $this->creditcard_list($cartTotal, $country);

                $this->paylater_list($cartTotal, $country);

                echo '</div>';
            }
        }

        echo '</div>';
    }

    /**
     * Widget version of a method item
     *
     * @since 3.0.1
     */
    private function widget_version_item($method, $cardCountry = '')
    {
        if (!empty($cardCountry)) {
            // For creating unique id for radio btn, card country needed
            $radio_id = $cardCountry . '_card_' . $method->name;
            $banklink_id = 'card_' . $method->name;
            echo '
                <div class="payment-method-wrapper col-6">
                    <div class="payment-method col-12 d-flex flex-row justify-content-center align-items-center shadow-sm rounded position-relative" style="height: 48px;cursor: pointer;">
                        <div class="check"></div>
                        <input type="radio" class="btn-check" name="payment" id="' . $radio_id . '" banklink_id="' . $banklink_id . '"/>
                        <label class="payment-control w-100 h-100 d-flex justify-content-center align-items-center m-0" style="cursor: pointer;" for="' . $radio_id . '">
                            <img class="mc-payment-logo" src="' . $method->logo_url . '" alt="' . ucfirst($method->display_name) . '" />
                        </label>
                    </div>
                </div>
            ';
        } else {
            $banklink_id = $method->country . '_' . $method->name;
            echo '
            <div class="payment-method-wrapper col-6">
                <div class="payment-method col-12 d-flex flex-row justify-content-center align-items-center shadow-sm rounded position-relative" style="height: 48px;cursor: pointer;">
                    <div class="check"></div>
                    <input type="radio" class="btn-check" name="payment" id="' . $banklink_id . '" banklink_id="' . $banklink_id . '"/>
                    <label class="payment-control w-100 h-100 d-flex justify-content-center align-items-center m-0" style="cursor: pointer;" for="' . $banklink_id . '">
                        <img class="mc-payment-logo" src="' . $method->logo_url . '" alt="' . ucfirst($method->display_name) . '" />
                    </label>
                </div>
            </div>
        ';
        }
    }

    /**
     * Show list of banklinks
     *
     * @since 3.0.1
     */
    private function banklink_list($cartTotal, $methods)
    {
        if (!empty($methods)) {

            $bank_links_html = '';
            foreach ($methods as $method) {
                // Check min max values
                if ($this->is_allowed_method($method, $cartTotal)) {
                    ob_start();
                    $this->widget_version_item($method);
                    $bank_links_html .= ob_get_clean();
                }
            }

            // Div will be rendered when at least 1 method passes is_allowed_method
            $this->render_payment_method_block(__( 'Bank payments', 'wc_makecommerce_domain' ), $bank_links_html);
        }
    }

    /**
     * Show list of paylater methodss
     *
     * @since 3.0.1
     */
    private function paylater_list( $cartTotal, $selected_country ) {

        foreach ( $this->paylater_grouped as $country => $methods ) {

            if ( $country == $selected_country ) {
                $pay_later_methods_html = '';
                foreach ( $methods as $method ) {
                    // Check min max values
                    if ( $this->is_allowed_method( $method, $cartTotal ) ) {
                        ob_start();
                        $this->widget_version_item( $method );
                        $pay_later_methods_html .= ob_get_clean();
                    }
                }

                $this->render_payment_method_block(__('Pay later', 'wc_makecommerce_domain' ), $pay_later_methods_html);
                if ( $country == 'other' ) {
                    echo '<p class="no-methods">'. _e( 'No payment methods for selected country' ,'wc_makecommerce_domain' ) .'</p>';
                }
            }
        }
    }

    /**
     * Show list of credit cards
     *
     * @since 3.0.1
     */
    private function creditcard_list($cartTotal, $country)
    {

        if ($this->cards) {

            $cc_methods_html = '';
            foreach ($this->cards as $method) {
                // Check min max values
                if ($this->is_allowed_method($method, $cartTotal)) {
                    ob_start();
                    $this->widget_version_item($method, $country);
                    $cc_methods_html .= ob_get_clean();
                }
            }

            $this->render_payment_method_block(__( 'Cards', 'wc_makecommerce_domain' ), $cc_methods_html);

        } else if ($country == 'other' && empty($this->banklinks_grouped['other'])) {
            echo '<p class="no-methods">' . _e('No payment methods for selected country', 'wc_makecommerce_domain') . '</p>';
        }

    }


    /**
     * Return list of all payment methods
     *
     * @since 3.0.12
     */
    static function get_payment_methods()
    {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . MAKECOMMERCE_TABLENAME);
    }

    /**
     * Return bool whether the payment method can be used based on amount constraints
     *
     * @since 3.3.0
     */
    private function is_allowed_method($method, $cartTotal)
    {
        $min = $method->min_amount ?? 0;
        $max = $method->max_amount ?? INF;

        return $min <= $cartTotal && $max >= $cartTotal;
    }

    /**
     * Common function to render payment method block
     *
     * @since 3.3.0
     */
    private function render_payment_method_block($sectionLabel, $content)
    {
        if ( !empty( $content ) ) {
            echo '<div class="d-flex align-items-center gap-2 my-3 text-muted fw-medium fs-6">
                      <span>' . esc_html($sectionLabel) . '</span>
                      <div class="flex-grow-1 border-top"></div>
                  </div>';
            echo '<div class="row g-3">' . $content . '</div>';
        }
    }

    private function get_country_name_from_code(string $code): string
    {
        if (strtolower($code) === 'other') {
            return 'International';
        }

        if (function_exists('WC') && WC()->countries) {
            $countries = WC()->countries->get_countries();
            $code = strtoupper($code);
            return $countries[$code] ?? '';
        }
        return '';
    }
}
