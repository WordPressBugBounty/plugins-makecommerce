const wpHooks = window.wp.hooks;

var shippingMethods = [];

/**
 * Function for setting the shipping methods
 *
 * @since 3.5.0
 */
function setShippingMethods(input) {
    shippingMethods = input;
}

/**
 * Function for getting the shipping methods
 *
 * @since 3.5.0
 */
function getShippingMethods() {
    return shippingMethods;
}

/**
 * Function for adding parcel machine selection fields to checkout
 *
 * @since 3.5.0
 */
function addParcelMachineFields(method) {
    let input = jQuery('input[id*="' + method + '"]:checked');
    let parent = input.parent();

    parent.fadeTo(1, 0.2);

    jQuery('.wc-block-components-checkout-place-order-button').prop('disabled', true);

    jQuery.ajax({
        url: '/wp-admin/admin-ajax.php',
        type: 'post',
        data: {
            action: 'get_parcel_machine_html',
            mc_carrier_id: method

        },
        success: function (response) {
            // Check if the select box is missing
            // The hook runs twice, therefore this is necessary
            if ( parent.find('p[id*="' + method + '"]').length === 0) {
                parent.append(response.html);

                let container = parent.find('p[id*="' + method + '"]');
                container.css('width', parent.width());

                jQuery(window).on('resize', function() {
                    container.css('width', parent.width() - 5);
                } );

                let selectBox = parent.find('select[id*="' + method + '"]');

                // Initialize select2 / selectWoo from parcelmachine_searchable.js
                if (typeof searchable_selectbox === 'function') {
                    searchable_selectbox(parent.find('.parcel-machine-select-box-searchable'));
                }

                // Add red border for selectboxes
                selectBox.addClass('mc-machine-unselected');
                parent.find('.parcel-machine-select-box').addClass('mc-machine-unselected');

                // When selection is made, remove border and enable button
                selectBox.on('change', function() {
                    jQuery(this).removeClass('mc-machine-unselected');
                    jQuery(this).parent().find('.parcel-machine-select-box').removeClass('mc-machine-unselected');
                    if (jQuery('.mc-machine-unselected').length === 0) {
                        jQuery('.wc-block-components-checkout-place-order-button').prop('disabled', false);
                    }
                });

                parent.fadeTo(500, 1);
            }
        },
        error: function (error) {
            jQuery('.wc-block-components-checkout-place-order-button').prop('disabled', false);
        },
    });
}

/**
 * Function for adding SmartPost courier fields to checkout
 *
 * @since 3.5.0
 */
function addCourierFields(method) {
    let input = jQuery('input[id*="' + method + '"]:checked');
    let parent = input.parent();
    parent.fadeTo(1, 0.2);

    jQuery.ajax({
        url: '/wp-admin/admin-ajax.php',
        type: 'post',
        data: {
            action: 'get_smartpost_courier_html',
            mc_carrier_id: method

        },
        success: function (response) {
            if ( parent.find('p[id*="' + method + '"]').length === 0) {
                parent.append(response.html);

                let container = parent.find('p[id*="' + method + '"]');
                container.css('width', parent.width());

                jQuery(window).on('resize', function() {
                    container.css('width', parent.width() - 5);
                } );
            }

            parent.fadeTo(500, 1);
        },
        error: function (error) {
            //Nothing here yet
        },
    });
}

/**
 * Get the shipping methods from the checkout and update the variable
 *
 * @since 3.5.0
 */
function setChosenShippingMethods() {
    let newMethods = [];

    jQuery('.wc-block-components-shipping-rates-control__package').each(function() {
        let checkedInput = jQuery(this).find('input[type="radio"]:checked');

        if (checkedInput.length > 0) {
            // Get the value of the checked radio input
            var selectedMethod = checkedInput.val().split(':')[0];
            // Push the selected method to the array
            newMethods.push(selectedMethod);
        }
    });

    setShippingMethods(newMethods);
}

function setupShippingRates() {
    jQuery('p[id*="parcelmachine_"]').remove();
    jQuery('.smartpost-courier-text').remove();

    setChosenShippingMethods();

    getShippingMethods().forEach(function(method) {
        if (method.includes('parcelmachine_')) {
            addParcelMachineFields(method);
        }

        if (method.includes('courier_smartpost')) {
            addCourierFields(method);
        }
    });
}

/**
 * Hook which is run every time a shipping method is selected
 * Runs when
 * the checkout loads,
 * the country is changed and methods change,
 * and when the customer selects another method
 *
 * @since 3.5.0
 */
wpHooks.addAction( 'experimental__woocommerce_blocks-checkout-set-selected-shipping-rate', 'makecommerce', function() {
    setupShippingRates();
});

/**
 * Hook which is run when place order button is pressed
 *
 * @since 3.5.0
 */
wpHooks.addAction( 'experimental__woocommerce_blocks-checkout-submit', 'makecommerce', function() {
    var values = [];
    let methods = getShippingMethods();

    jQuery('.wc-block-components-shipping-rates-control__package').each(function(index) {
        if (methods[index] === 'courier_smartpost') {
            time = jQuery(this).find('select[id="' + methods[index] + '"]').val();
            values.push({'time': time});
        } else if (methods[index].includes('parcelmachine_')) {
            machine = jQuery(this).find('select[id="' + methods[index] + '"]').val();
            values.push({'machine': machine});
        }
    });

    jQuery.ajax({
        url: '/wp-admin/admin-ajax.php',
        type: 'post',
        data: {
            action: 'mc_blocks_save_shipping_data',
            mc_shipping_data: values
        }
    });
});

/**
 * Need to run the setup because the rates hook is not triggered at load anymore
 * @since patch
 */
jQuery(window).load(function() {
    setupShippingRates();

    const orderButton = jQuery('.wc-block-components-checkout-place-order-button');

    // Callback function to execute when mutations are observed
    const callback = (mutationList) => {
        for (const mutation of mutationList) {
            if (mutation.type === "attributes" && mutation.attributeName === "disabled") {
                // Button not disabled, check if machine / time is selected
                if (!orderButton.prop('disabled')) {
                    setChosenShippingMethods();
                    let method = getShippingMethods()[0];

                    let chosenVal = jQuery('.wc-block-components-shipping-rates-control__package').find('select[id="' + method + '"]').val();

                    if (method.includes('parcelmachine_') && chosenVal !== undefined && !chosenVal) {
                        observer.disconnect();
                        // No machine selected, disable the button again
                        orderButton.prop('disabled', true);

                        observer.observe(orderButton[0], config);
                    }
                }
            }
        }
    };
    const config = {
        attributes: true,
        attributeFilter: ['disabled'],
    };

    const observer = new MutationObserver(callback);

    if (orderButton.length > 0 && orderButton[0] instanceof Node) {
        observer.observe(orderButton[0], config);
    }
});
