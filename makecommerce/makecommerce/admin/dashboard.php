<?php

namespace MakeCommerce\Admin;

// Ensure the Composer autoloader is included
require_once plugin_dir_path(__FILE__) . '../vendor-prefixed/autoload.php';

use MakeCommercePrefix\MakeCommerceShipping\SDK\Http\MakeCommerceClient;
use MakeCommercePrefix\Twig\Environment;
use MakeCommercePrefix\Twig\Loader\FilesystemLoader;
use MakeCommercePrefix\Twig\TwigFunction;

class Dashboard
{
    public const DASHBOARD_SLUG = 'makecommerce_dashboard';
    public const PAYMENTS_ONLY_SLUG = 'makecommerce_payments_only';
    public const CONF_SLUG = 'makecommerce_configure';

    private string $api_mode;
    private string $shop_id;
    private string $secret_key;
    private string $public_key;
    private string $test_shop_id;
    private string $test_secret_key;
    private string $test_public_key;

    private string $instance_id;
    private string $shipping;
    private string $payments;

    public function initialize(): void
    {
        // Live
        $this->shop_id = get_option('mc_shop_id', '');
        $this->secret_key = get_option('mc_secret_key', '');
        $this->public_key = get_option('mc_public_key', '');

        // Test
        $this->test_shop_id = get_option('mc_test_shop_id', '');
        $this->test_secret_key = get_option('mc_test_secret_key', '');
        $this->test_public_key = get_option('mc_test_public_key', '');

        $this->api_mode = get_option('mc_api_mode', 'live');
        $this->instance_id = get_option('mc_instance_id', '');
        $this->shipping = get_option('mc_shipping', 'off');
        $this->payments = get_option('mc_payments', 'off');
    }


    /**
     * Add MakeCommerce menu to WordPress
     * @return void
     */
    public function add_menu()
    {
        $icon = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDYiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0NiA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTQyLjEyMzUgNDBDNDEuMDA2NiA0MCAzOS45NDI5IDM5LjQxMjYgMzkuMjUxNSAzOC41MDQ4TDI4LjYxNDMgMjIuNTM4N0MyOC42MTQzIDIyLjQ4NTMgMjguNjE0MyAyMi40ODUzIDI4LjYxNDMgMjIuNDMxOUwzOC40NTM3IDEyLjU1MzFDMzkuODM2NSAxMS4xNjQ4IDQxLjk2MzkgMTEuMTY0OCA0My4yNDA0IDEyLjU1MzFDNDQuNjIzMiAxMy45NDE1IDQ0LjYyMzIgMTYuMDc3NSA0My4yNDA0IDE3LjM1OUwzNy4yODM2IDIzLjM5MzFDMzcuMjgzNiAyMy4zOTMxIDM3LjIzMDQgMjMuNDQ2NCAzNy4yODM2IDIzLjQ5OThMNDQuNzgyOCAzNC44NzM3QzQ1Ljc5MzMgMzYuNDc1NyA0NS4zNjc4IDM4LjUwNDggNDMuODc4NiAzOS42Nzk2QzQzLjUwNjMgMzkuNzg2NCA0Mi44MTQ5IDQwIDQyLjEyMzUgNDBaTTIzLjkzMzkgNDBDMjIuMDE5MiA0MCAyMC41MyAzOC41MDQ4IDIwLjUzIDM2LjU4MjVWMy42ODg5OUMyMC41MyAxLjcxMzI0IDIyLjE3ODggLTAuMTAyMzA5IDI0LjE0NjcgMC4wMDQ0ODgzOUMyNi4wMDgyIDAuMTExMjg1IDI3LjM5MSAxLjU1MzA1IDI3LjM5MSAzLjQyMlYzNi41ODI1QzI3LjMzNzggMzguNTA0OCAyNS44NDg2IDQwIDIzLjkzMzkgNDBaTTEzLjY2OSA0MEMxMS43NTQzIDQwIDEwLjI2NTEgMzguNTA0OCAxMC4yNjUxIDM2LjU4MjVWMTUuMTY5N0MxMC4yNjUxIDEzLjE5MzkgMTEuOTEzOSAxMS4zNzg0IDEzLjg4MTggMTEuNDg1MkMxNS43NDMzIDExLjU5MiAxNy4xMjYxIDEzLjAzMzcgMTcuMTI2MSAxNC45MDI3VjM2LjYzNTlDMTcuMDcyOSAzOC41MDQ4IDE1LjU4MzcgNDAgMTMuNjY5IDQwWk0zLjQwNDE0IDQwQzEuNDg5NDUgNDAgMC4wMDAyNDQxNDEgMzguNTA0OCAwLjAwMDI0NDE0MSAzNi41ODI1VjI2LjU5N0MwLjAwMDI0NDE0MSAyNC42MjEyIDEuNjQ5MDEgMjIuODA1NyAzLjYxNjg5IDIyLjkxMjVDNS40NzgzOSAyMy4wMTkzIDYuODYxMjMgMjQuNDYxIDYuODYxMjMgMjYuMzNWMzYuNjM1OUM2LjgwODA0IDM4LjUwNDggNS4zNzIwMiA0MCAzLjQwNDE0IDQwWiIgZmlsbD0iIzMyQ0I2NCIvPgo8L3N2Zz4K';
        if (!self::check_store_setup()) {
            add_menu_page(
                'MakeCommerce',
                'MakeCommerce',
                'manage_options',
                self::DASHBOARD_SLUG,
                [$this, 'render_setup_wizard'],
                $icon,
                58
            );

            return;
        }

        add_menu_page(
            'MakeCommerce',
            'MakeCommerce',
            'manage_options',
            self::DASHBOARD_SLUG,
            [$this, 'render_shipping_configuration_iframe'],
            $icon,
            58
        );

        add_submenu_page(
            '_empty_parent',
            'MakeCommerce Configure',
            'MakeCommerce Configure',
            'manage_options',
            self::CONF_SLUG,
            [$this, 'render_plugin_configure_page']
        );

        if ($this->only_payments_configured()){
            add_submenu_page(
                '_empty_parent',
                'MakeCommerce Payments',
                'MakeCommerce Payments',
                'manage_options',
                self::PAYMENTS_ONLY_SLUG,
                [$this, 'render_payments_only_page']
            );
        }
    }

    /**
     * Remove the parent menu's submenu
     * @return void
     */
    public function remove_parent_menu(): void
    {
        remove_submenu_page('makecommerce_dashboard', 'makecommerce_dashboard');
    }


    /**
     * MC Payment method needs enabled field, so keep in sync with mc_payments setting
     * @return void
     */
    public function sync_enabled_with_mc_payments($old_value, $new_value)
    {
        $option_key = 'woocommerce_' . MAKECOMMERCE_PLUGIN_ID . '_settings';

        $settings = get_option($option_key, []);

        if (!is_array($settings)) {
            $settings = [];
        }

        $settings['enabled'] = ($new_value === 'on') ? 'yes' : 'no';

        update_option($option_key, $settings);
    }

    public function render_setup_wizard()
    {
        if ($this->payments !== 'on' && $this->shipping !== 'on') {
            $this->render_template('productSelection.twig', [
                'shipping' => $this->shipping,
                'payments' => $this->payments,
                'setupSelection' => true,
                'newInstall' => $this->is_new_install()
            ]);
        } else {
            $this->render_template('credentials.twig');
        }
    }

    function render_plugin_configure_page()
    {
        $this->render_template('productSelection.twig', [
            'shipping' => $this->shipping,
            'payments' => $this->payments,
            'newInstall' => $this->is_new_install()
        ]);
    }

    function render_payments_only_page()
    {
        $this->render_template('onlyPayments.twig', [
            'configure_url' =>  admin_url('admin.php?page=' . self::CONF_SLUG)
        ]);
    }

    private function render_template(string $template, array $data = []): void
    {
        $loader = new FilesystemLoader(plugin_dir_path(__FILE__) . 'templates');
        $twig = new Environment($loader);

        $twig->addFunction(new TwigFunction('__', function (string $text, string $domain = 'wc_makecommerce_domain') {
            return __($text, $domain);
        }));

        $baseData = [
            'path' => plugin_dir_url(__DIR__),
            's3_path' => 'https://static.maksekeskus.ee/img/woocommerce/',
            'shopId' => $this->shop_id,
            'secretKey' => $this->secret_key,
            'publicKey' => $this->public_key,
            'testShopId' => $this->test_shop_id,
            'testSecretKey' => $this->test_secret_key,
            'testPublicKey' => $this->test_public_key,
            'apiMode' => $this->api_mode,
            'instanceId' => $this->instance_id,
            'shopException' => get_option('mc_credentials_error'),
        ];

        echo $twig->render($template, array_merge($baseData, $data));
    }

    public function render_shipping_configuration_iframe()
    {
        $shipping = get_option('mc_shipping', 'off');
        $payments = get_option('mc_payments', 'off');

        if ($shipping !== 'off') {
            try {
                $client = $this->get_client();

                $token = $client->connectShop(
                    $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                        get_site_url() ?? $_SERVER['REMOTE_ADDR'],
                    get_site_url() . '/wp-admin/post.php?post={id}&action=edit'
                );

                if (empty($token->body->jwt)) {
                    throw new \Exception('JWT token is missing in the response.');
                }

                $url = $client->getIframeUrl($token->body->jwt);

                if (empty($url)) {
                    throw new \Exception('Iframe URL could not be generated.');
                }

                echo '<iframe id="mcIframe" src="' . esc_url($url) . '" width="100%"></iframe>';
                $this->render_template('credentialsPopup.twig', ['render_footer' => false]);
            } catch (\Throwable $e) {
                // Problem with getting iframe
                $this->render_template('error.twig', ['mc_error' => __('Failed to load Iframe. Please try again later.', 'wc_makecommerce_domain')]);
            }
        } else if ($payments !== 'off'){
            wp_redirect(admin_url('admin.php?page=' . self::PAYMENTS_ONLY_SLUG));
        } else {
            wp_redirect(admin_url('admin.php?page=' . self::CONF_SLUG));
        }
    }

    public function save_settings(): void {
        $current_page = $_GET['page'] ?? '';

        // if not our page
        if (
            $current_page !== self::DASHBOARD_SLUG &&
            $current_page !== self::CONF_SLUG &&
            $current_page !== self::PAYMENTS_ONLY_SLUG
        ) {
            return;
        }

        // Switch back to old module
        if(isset($_POST['switch']) && $_POST['switch'] === 'switch_to_old') {
            $this->switch_to_old_plugin();
        }

        // Save settings on setup selection
        if (isset($_POST['setup_selection']) && $_POST['setup_selection'] === 'mc_product'){
            $this->save_product_selection_settings(true);
        }

        // Save settings on product selection
        if (isset($_POST['product_selection']) && $_POST['product_selection'] === 'mc_product'){
            $this->save_product_selection_settings();
        }

        // Save credentials
        if (isset($_POST['save_credentials']) && $_POST['save_credentials'] === 'mc_credentials'){
            $this->save_credentials_settings();
        }
    }

    private function switch_to_old_plugin(){
        update_option('mc_shipping_plus', 'no');
        wp_redirect(admin_url('admin.php?page=wc-settings&tab=advanced&section=mk_api'));
        exit;
    }

    private function save_product_selection_settings($setupSelection = false){
        $this->payments = $_POST['payments'] ?? 'off';
        $this->shipping = $_POST['shipping'] ?? 'off';

        update_option('mc_payments', $this->payments);
        update_option('mc_shipping', $this->shipping);

        if(!$setupSelection){
            if ($this->payments !== 'off' && $this->shipping !== 'off') {
                wp_redirect(admin_url('admin.php?page=' . self::DASHBOARD_SLUG));
            } elseif ($this->payments !== 'off') {
                wp_redirect(admin_url('admin.php?page=' . self::PAYMENTS_ONLY_SLUG));
            } else {
                wp_redirect(admin_url('admin.php?page=' . self::DASHBOARD_SLUG));
            }
        }
    }

    private function save_credentials_settings(): void
    {
        $api_mode = ($_POST['mc_api_mode'] ?? '') === 'test' ? 'test' : 'live';
        update_option('mc_api_mode', $api_mode);
        $this->api_mode = $api_mode;

        $shop_id     = $_POST['shopId'] ?? '';
        $secret_key  = $_POST['secretKey'] ?? '';
        $public_key  = $_POST['publicKey'] ?? '';

        $prefix = $api_mode === 'test' ? 'mc_test_' : 'mc_';

        update_option($prefix . 'shop_id', $shop_id);
        update_option($prefix . 'secret_key', $secret_key);
        update_option($prefix . 'public_key', $public_key);

        if ($api_mode === 'test') {
            $this->test_shop_id    = $shop_id;
            $this->test_secret_key = $secret_key;
            $this->test_public_key = $public_key;
        } else {
            $this->shop_id    = $shop_id;
            $this->secret_key = $secret_key;
            $this->public_key = $public_key;
        }

        try {
            $client = $this->get_client();
            $client->connectShop(
                'userAgent',
                'myIP',
                get_site_url() . '/wp-admin/post.php?post={id}&action=edit'
            );
            update_option('mc_credentials_error', '');
        } catch (\Exception $e) {
            $label = $api_mode === 'test' ? 'sandbox' : 'live';
            update_option('mc_credentials_error',
                sprintf(
                    /* translators: %s: Environment name */
                    __('Unable to verify %s credentials', 'wc_makecommerce_domain'), $label)
            );
        }

        // When credentials correctly submitted, update banklinks
        \MakeCommerce\Payment::update_banklinks();
        wp_redirect(admin_url('admin.php?page=' . self::DASHBOARD_SLUG));
        exit;
    }

    private function get_client(): MakeCommerceClient
    {
        $instance_id = $this->instance_id ?? get_option('mc_instance_id', '');

        if (empty($instance_id)) {
            $instance_id = uniqid('', true);
            update_option('mc_instance_id', $instance_id);
        }
        $this->instance_id = $instance_id;
        $client_conf = \MakeCommerce::get_sdk_config();

        $client = new MakeCommerceClient(
            $this->api_mode,
            $this->api_mode === 'test' ? $this->test_shop_id : $this->shop_id,
            $this->api_mode === 'test' ? $this->test_secret_key : $this->secret_key,
            $instance_id,
            $client_conf
        );

        $shortLocale = substr(get_user_locale() ?: get_locale(), 0, 2);

        if (in_array($shortLocale, ['et', 'en', 'lv', 'lt'], true)) {
            $client->setLocale($shortLocale);
        }

        return $client;
    }

    public function enqueue_dashboard_scripts($hook)
    {
        if ($hook === 'toplevel_page_makecommerce_dashboard') {
            wp_enqueue_style('makecommerce-iframe-style', "https://static.maksekeskus.ee/modules/woocommerce/css/iframe.css");
            wp_enqueue_script('bootstrap-bundle', plugin_dir_url(__DIR__) . 'assets/bootstrap.bundle.min.js', [], '5.3.3', true);
            wp_enqueue_script('mc-shop-credentials', plugin_dir_url(__FILE__) . 'js/mc-shop-credentials.js', ['bootstrap-bundle'], null, true);
            wp_enqueue_script('mc-module-config', plugin_dir_url(__FILE__) . 'js/mc-module-config.js', ['bootstrap-bundle'], null, true);
            wp_enqueue_script('mc-iframe-height', plugin_dir_url(__FILE__) . 'js/mc-iframe-height.js', [], null, true);
            // Pass redirect url to javascript
            wp_localize_script('mc-module-config', 'mcApiData', ['redirect_url' => admin_url('admin.php?page=' . self::CONF_SLUG)]);
        }

        wp_enqueue_script('mc-api-toggle', plugin_dir_url(__FILE__) . 'js/mc-api-toggle.js', ['jquery'], null, true);
    }

    public function hide_admin_notices()
    {
        $screen = get_current_screen();
        if ($screen && in_array($screen->id, ['toplevel_page_makecommerce_dashboard', 'makecommerce_page_makecommerce',
                'admin_page_' . self::CONF_SLUG, 'admin_page_' . self::PAYMENTS_ONLY_SLUG])) {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
        }
    }

    private function only_payments_configured(): bool {
        $shipping    = get_option( 'mc_shipping', 'off' );
        $payments    = get_option( 'mc_payments', 'off' );
        return $shipping !== 'on' && $payments === 'on';
    }

    public static function check_store_setup(): bool
    {
        $mode = get_option( 'mc_api_mode', 'live' );

        $shipping    = get_option( 'mc_shipping', 'off' );
        $payments    = get_option( 'mc_payments', 'off' );

        $shop_id     = get_option( $mode === 'test' ? 'mc_test_shop_id' : 'mc_shop_id' );
        $public_key  = get_option( $mode === 'test' ? 'mc_test_public_key' : 'mc_public_key' );
        $secret_key  = get_option( $mode === 'test' ? 'mc_test_secret_key' : 'mc_secret_key' );

        $mc_credentials_error = get_option('mc_credentials_error', '');

        // Problem with credentials
        if (!empty($mc_credentials_error)) {
            return false;
        }

        return ( $shipping === 'on' || $payments === 'on' )
            && ( $shop_id || $public_key || $secret_key );
    }

    private function is_new_install() {
        return get_option('makecommerce_install_status', 'upgrade') === 'new_install';
    }
}
