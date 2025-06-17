jQuery(function () {
	jQuery(document).on('updated_checkout', function () {
		addClassicCheckoutPickupField();
	});
});

function addClassicCheckoutPickupField() {
	jQuery('.makecommerce-shipping-container').remove();

	let method = jQuery('input[name="shipping_method[0]"]:checked').val();
	// Fallback if no radio input is checked (only one shipping method with hidden input)
	if (!method) {
		method = jQuery('input[type="hidden"][name="shipping_method[0]"]').val();
	}

	if (!method?.includes('mc_pickuppoint_')) return;

	let carrier = method.split('_')[2];
	let country = jQuery('#mcCustomerCountry').val();
	let widthPx = (jQuery('table.shop_table').width() - 40) + 'px';

	// Initialize Select2 for loading state
	const $select = insertLoadingSelect(carrier, widthPx);

	// Fetch machine list and populate the same select box
	jQuery.ajax({
		url: '/wp-admin/admin-ajax.php',
		type: 'post',
		data: {
			action: 'get_carrier_machines',
			mc_carrier_id: carrier,
			country: country,
			selected_machine: ''
		},
		success: function (response) {
			populatePickupPointSelect($select, response.machines, widthPx);
		},
		error: function (error) {
			console.error(error);
		}
	});
}

function insertLoadingSelect(carrier, widthPx) {
	const select = document.createElement('select');
	select.className = 'pickup-point-select-box';
	select.name = '_mc_machine_id';
	let placeholderText = MC_PARCELMACHINE_JS['loadingPlaceholder'] ?? 'Loading pickup points...';

	const loadingOption = new Option(placeholderText, '', true, true);
	loadingOption.disabled = true;
	select.add(loadingOption);

	const div = document.createElement('div');
	div.id = `makecommerce-shipping-${carrier}-container`;
	div.className = 'mcSelectContainer';
	div.appendChild(select);

	const tr = document.createElement('tr');
	tr.className = 'makecommerce-shipping-container';

	const td = document.createElement('td');
	td.className = 'pickup_point_checkout';
	td.colSpan = 2;
	td.appendChild(div);

	tr.appendChild(td);

	jQuery('.woocommerce-shipping-totals.shipping').after(tr);

	const $select = jQuery(select);
	$select.select2({
		placeholder: placeholderText,
		width: widthPx,
		minimumResultsForSearch: Infinity
	}).prop('disabled', true);

	return $select;
}

function populatePickupPointSelect($select, machines, widthPx) {
	$select.empty(); // Clear loading state

	let placeholderText = MC_PARCELMACHINE_JS['placeholder'] ?? 'Select pickup point';
	const placeholder = new Option(placeholderText, '', true, true);
	placeholder.disabled = true;
	$select.append(placeholder);

	for (const key in machines) {
		const $optgroup = jQuery(`<optgroup label="${key}"></optgroup>`);
		machines[key].forEach(item => {
			const $option = jQuery('<option>', {
				value: item.id,
				text: item.name
			}).data({
				name: item.name,
				address: item.address,
				city: item.city,
				zip: item.zip,
				availability: item.availability
			});

			if (item.selected) {
				$option.prop('selected', true);
			}

			$optgroup.append($option);
		});
		$select.append($optgroup);
	}

	// Re-enable and refresh Select2
	$select.prop('disabled', false).select2({
		placeholder: placeholderText,
		width: widthPx,
		dropdownAutoWidth: true,
		dropdownCssClass: 'mcShippingSelectDropdown',
		templateResult: customOption
	});

	focusSelect2Search($select);
}

function customOption(option) {
	if (!option.id) return option.text;

	const el = jQuery(option.element);
	const name = el.data('name') || option.text || '';
	const city = el.data('city') || '';
	const address = el.data('address') || '';
	const zip = el.data('zip') || '';

	return jQuery(`<span>${name} - ${city}, ${address}, ${zip}</span>`);
}

function focusSelect2Search(select) {
	jQuery(select).on('select2:open', function () {
		let searchField = jQuery('.select2-container--open').find('input.select2-search__field')[0];
		setTimeout(() => searchField?.focus(), 300);
	});
}
