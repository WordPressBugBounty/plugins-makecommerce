<div class="container">
    <main>
        <div class="pt-5 text-center">
            <img class="d-block mx-auto mb-4" src="<?php echo plugin_dir_url(dirname(__DIR__)) ?>makecommerce/assets/mc.svg" alt="" width="72" height="57">
            <h2>MakeCommerce</h2>
        </div>
        <div class="row">
            <div class="offset-3 col-6 g-3 text-center">
                <div class="p-4 rounded text-start">
                    <h3 class="mb-4"><?php _e('Changes in new version, please read before migrating', 'wc_makecommerce_domain'); ?></h3>

                    <!-- Payments Section -->
                    <h5 class="mb-3"><?php _e('Payments', 'wc_makecommerce_domain'); ?></h5>
                    <ul class="list-group list-group-numbered mb-4">
                        <li class="list-group-item d-flex" style="border: none !important;">
                            <div class="ms-2">
                                <div class="fw-bold"><?php _e('Simplified default payment method setup', 'wc_makecommerce_domain'); ?></div>
                                <?php _e('Default method selection is now streamlined and follows the order set in your WooCommerce settings — no extra configuration needed.', 'wc_makecommerce_domain'); ?>
                            </div>
                        </li>
                        <li class="list-group-item d-flex" style="border: none !important;">
                            <div class="ms-2">
                                <div class="fw-bold"><?php _e('Modern checkout design, no manual styling required', 'wc_makecommerce_domain'); ?></div>
                                <?php _e('We’ve replaced manual design options with a sleek, automated checkout layout that ensures a consistent and up-to-date look.', 'wc_makecommerce_domain'); ?>
                            </div>
                        </li>
                        <li class="list-group-item d-flex" style="border: none !important;">
                            <div class="ms-2">
                                <div class="fw-bold"><?php _e('Seamless multilingual support', 'wc_makecommerce_domain'); ?></div>
                                <?php _e('No need for manual translations — all texts are now automatically localized for your customers.', 'wc_makecommerce_domain'); ?>
                            </div>
                        </li>
                        <li class="list-group-item d-flex" style="border: none !important;">
                            <div class="ms-2">
                                <div class="fw-bold"><?php _e('Smarter country selection', 'wc_makecommerce_domain'); ?></div>
                                <?php _e('Country selection now only appears when relevant and is pre-filled for your shopper’s convenience.', 'wc_makecommerce_domain'); ?>
                            </div>
                        </li>
                        <li class="list-group-item d-flex" style="border: none !important;">
                            <div class="ms-2">
                                <div class="fw-bold"><?php _e('Payment method order is optimized', 'wc_makecommerce_domain'); ?></div>
                                <?php _e('Methods are automatically sorted based on popularity and region — ensuring the most relevant options are shown first.', 'wc_makecommerce_domain'); ?>
                            </div>
                        </li>
                        <li class="list-group-item d-flex" style="border: none !important;">
                            <div class="ms-2">
                                <div class="fw-bold"><?php _e('Improved expired payment handling', 'wc_makecommerce_domain'); ?></div>
                                <?php _e('Expired payments are no longer automatically marked as cancelled, giving you more flexibility. You can manage this via WooCommerce’s stock settings. Transactions still expire after 30 minutes.', 'wc_makecommerce_domain'); ?>
                                (<a href="https://developer.makecommerce.net/guides/custom-api/RegularPaymentFlow#transaction-statuses" target="_blank"><?php _e('Read more', 'wc_makecommerce_domain'); ?></a>)
                            </div>
                        </li>
                    </ul>

                    <!-- Shipping Section -->
                    <h5 class="mb-3"><?php _e('Shipping', 'wc_makecommerce_domain'); ?></h5>
                    <ul class="list-group list-group-numbered mb-4">
                        <li class="list-group-item d-flex" style="border: none !important;">
                            <div class="ms-2">
                                <div class="fw-bold"><?php _e('Shipping method setup fully automated', 'wc_makecommerce_domain'); ?></div>
                                <?php _e('Zones and manual configuration in WooCommerce is no longer needed — dynamic shipping rates are now handled automatically.', 'wc_makecommerce_domain'); ?>
                            </div>
                        </li>
                        <li class="list-group-item d-flex" style="border: none !important;">
                            <div class="ms-2">
                                <div class="fw-bold"><?php _e('Support for Venipak and Unisend added', 'wc_makecommerce_domain'); ?></div>
                                <?php _e('Two new carriers are now fully integrated into our shipping module.', 'wc_makecommerce_domain'); ?>
                            </div>
                        </li>
                        <li class="list-group-item d-flex" style="border: none !important;">
                            <div class="ms-2">
                                <div class="fw-bold"><?php _e('Simplified carrier setup', 'wc_makecommerce_domain'); ?></div>
                                <?php _e('All necessary logic is now built-in or handled automatically, making setup easier than ever.', 'wc_makecommerce_domain'); ?>
                            </div>
                        </li>
                        <li class="list-group-item d-flex" style="border: none !important;">
                            <div class="ms-2">
                                <div class="fw-bold"><?php _e('“Does not fit parcel machine” handled more smartly', 'wc_makecommerce_domain'); ?></div>
                                <?php _e('This is now based on package weight and dimensions — no need for manual tagging.', 'wc_makecommerce_domain'); ?>
                            </div>
                        </li>
                        <li class="list-group-item d-flex" style="border: none !important;">
                            <div class="ms-2">
                                <div class="fw-bold"><?php _e('Free shipping and coupon logic evolving', 'wc_makecommerce_domain'); ?></div>
                                <?php _e('Settings for free shipping from a certain amount and coupon combinations will return soon in a new, improved interface.', 'wc_makecommerce_domain'); ?>
                            </div>
                        </li>
                        <li class="list-group-item d-flex" style="border: none !important;">
                            <div class="ms-2">
                                <div class="fw-bold"><?php _e('Map view temporarily removed', 'wc_makecommerce_domain'); ?></div>
                                <?php _e('We’re working on a cleaner and more intuitive version that will be reintroduced shortly.', 'wc_makecommerce_domain'); ?>
                            </div>
                        </li>
                        <li class="list-group-item d-flex" style="border: none !important;">
                            <div class="ms-2">
                                <div class="fw-bold"><?php _e('Bulk label printing is on the way', 'wc_makecommerce_domain'); ?></div>
                                <?php _e('Coming very soon — we’re putting final touches on this feature to make it even better.', 'wc_makecommerce_domain'); ?>
                            </div>
                        </li>
                    </ul>

                    <!-- Important Warning Section -->
                    <div class="fw-bold mt-4 text-danger">
                        🚨 <?php _e('Very important:', 'wc_makecommerce_domain'); ?>
                        <ul class="mt-2 ps-3">
                            <li class="mb-2"><?php _e('After migration, label printing and access to previous shipment data will no longer be available. Please ensure all shipments are printed and dispatched before upgrading.', 'wc_makecommerce_domain'); ?></li>
                        </ul>
                    </div>
                </div>


                <form method="post">
                    <div class="">
                        <input class="" type="checkbox" id="agree_terms" name="agree_terms" required>
                        <label class="" for="agree_terms">
                            <?php _e('I have read and understood the changes and agree to the <a href="https://makecommerce.net/shipping-plus-pricing/" target="_blank">terms and fees</a>', 'wc_makecommerce_domain'); ?>
                        </label>
                    </div>
                    <hr class="my-4">
                    <button class="btn btn-primary btn-large" type="submit" name="accept_shipping"><?php _e('Agree to changes and migrate', 'wc_makecommerce_domain'); ?></button>
                </form>
            </div>
        </div>
    </main>

    <footer class="my-5 text-muted text-center text-small">
        <p class="mb-1">&copy;Maksekeskus AS</p>
        <ul class="list-inline">
            <li class="list-inline-item"><?php _e('<a href="https://makecommerce.net/general-terms/" target="_blank">Terms</a>', 'wc_makecommerce_domain'); ?>
            </li>
            <li class="list-inline-item"><?php _e('<a href="https://makecommerce.net/contact/" target="_blank">Support</a>', 'wc_makecommerce_domain'); ?></li>
        </ul>
    </footer>
</div>