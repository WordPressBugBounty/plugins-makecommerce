jQuery(document).ready(function($) {
    function method_list_set() {
        let selectedCountry = $('#makecommerce_customer_country').val();

        makecommercePick(selectedCountry);

        // Handle country change
        $('body').on('change', 'select[name=makecommerce_country_picker]', function() {
            selectedCountry = $(this).val();
            makecommercePick(selectedCountry);
        });

        // Handle clicks on payment methods
        $('body').on('change', 'input[name="payment"]', function () {
            const banklink_id = $(this).attr('banklink_id');
            $('select#' + MC_METHOD_LIST.id).val(banklink_id);

            $('div.payment-method').removeClass('payment-method-selected');
            $(this).parent().addClass('payment-method-selected');
        });

        // Reset view and show for selected country methods
        function makecommercePick(country) {
            $('select#' + MC_METHOD_LIST.id).val('');
            $('div.payment-method').removeClass('payment-method-selected');
            $('input[name="payment"]').prop('checked', false);

            if (country) {
                $('div.makecommerce_country_methods').hide();

                // Show current country methods
                $('div#makecommerce_country_methods_' + country).show();
            }
        }
    }

    method_list_set();

    //update also on checkout ajax updates
    $(document.body).on('updated_checkout', function() {
        method_list_set();
    });
});

