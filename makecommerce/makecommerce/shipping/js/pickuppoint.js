jQuery(function () {
	jQuery(document).ready(function() {
		initPickupPointInjection();
	});
	jQuery(document).on('updated_checkout', initPickupPointInjection);
});

// Temporary workaround, custom theme
jQuery(document).ajaxSuccess(function(event, xhr, settings) {
	if (settings.url.includes('admin-ajax.php') && settings.data?.includes('action=uae_woo_checkout_update_order_review')) {
		initPickupPointInjection();
	}
});

function initPickupPointInjection() {
	jQuery('.makecommerce-shipping-container').remove();

	let method = jQuery('input[name="shipping_method[0]"]:checked').val()
		|| jQuery('input[type="hidden"][name="shipping_method[0]"]').val();

	if (!method?.includes('mc_pickuppoint_')) return;

	const carrier = method.split('_')[2];
	const country = jQuery('#mcCustomerCountry').val();

	const $select = insertLoadingSelect(carrier);

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
			populatePickupPointSelect($select, response.machines);
		},
		error: function (error) {
			console.error(error);
		}
	});
}


function insertLoadingSelect(carrier) {
	const select = document.createElement('select');
	select.className = 'pickup-point-select-box';
	select.name = '_mc_machine_id';
	const placeholderText = MC_PARCELMACHINE_JS['loadingPlaceholder'] ?? 'Loading pickup points...';

	const loadingOption = new Option(placeholderText, '', true, true);
	loadingOption.disabled = true;
	select.add(loadingOption);

	const div = document.createElement('div');
	div.id = `makecommerce-shipping-${carrier}-container`;
	div.className = 'mcSelectContainer';
	div.style.padding = '0 5px';
	div.style.maxWidth = getWidth();

	div.appendChild(select);

	injectSelectBox(div);

	const $selectBox = jQuery(select).prop('disabled', true);
	applySelectBox($selectBox, {
		placeholder: placeholderText,
		width: '100%',
		minimumResultsForSearch: Infinity
	});

	return $selectBox;
}

function populatePickupPointSelect($select, machines) {
	$select.empty();
	$select.prop('disabled', false)
	const placeholderText = MC_PARCELMACHINE_JS['placeholder'] ?? 'Select pickup point';
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
			if (item.selected) $option.prop('selected', true);
			$optgroup.append($option);
		});
		$select.append($optgroup);
	}

	applySelectBox($select, {
		placeholder: placeholderText,
		width: '100%',
		dropdownAutoWidth: true,
		dropdownCssClass: 'mcShippingSelectDropdown',
		templateResult: customOption
	});
}

function applySelectBox($element, options) {
	if (typeof $element.selectWoo === 'function') {
		$element.selectWoo(options);
		focusSelect2Search($element);
	} else if (typeof $element.select2 === 'function') {
		$element.select2(options);
		focusSelect2Search($element);
	} else {
		console.warn('No Select2 or SelectWoo found');
	}
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
		const searchField = jQuery('.select2-container--open').find('input.select2-search__field')[0];
		setTimeout(() => searchField?.focus(), 300);
	});
}

function getWidth() {
	const shippingWidth = jQuery('.woocommerce-shipping-totals').width();
	if (shippingWidth) return shippingWidth + 'px';

	const methodParentWidth = jQuery('.woocommerce-shipping-methods').parent().width();
	if (methodParentWidth) return methodParentWidth + 'px';

	// Fallback width
	return '300px';
}

// Various fallbacks where to inject the MakeCommerce selectBox
function injectSelectBox(div) {
	const $tableData = jQuery('.makecommerce-pickuppoint-table-data');

	if ($tableData.length > 0) {
		return wrapAndAppend(div, $tableData);
	}

	const $shippingTotals = jQuery('.woocommerce-shipping-totals.shipping');
	if ($shippingTotals.length > 0) {
		return injectAfterShippingTotals(div, $shippingTotals);
	}

	const $methods = jQuery('.woocommerce-shipping-methods');
	if ($methods.length > 0) {
		return wrapAndInsertAfter(div, $methods.parent());
	}

	const $fallbackInput = jQuery('input.shipping_method:checked').filter(function () {
		return this.value.includes('mc_pickuppoint');
	});

	if ($fallbackInput.length > 0) {
		return wrapAndInsertAfter(div, $fallbackInput.parent(), 'li');
	}

	console.warn('No suitable element found for pickup point injection');
}

function wrapAndAppend(div, $target) {
	const container = document.createElement('div');
	container.className = 'makecommerce-shipping-container';
	container.appendChild(div);
	$target.append(container);
}

function injectAfterShippingTotals(div, $target) {
	const tr = document.createElement('tr');
	tr.className = 'makecommerce-shipping-container';
	const td = document.createElement('td');
	td.className = 'pickup_point_checkout';
	td.colSpan = 2;
	td.appendChild(div);
	tr.appendChild(td);
	$target.last().after(tr);
}

function wrapAndInsertAfter(div, $target, tag = 'div') {
	const container = document.createElement(tag);
	container.className = 'makecommerce-shipping-container';
	container.appendChild(div);
	$target.after(container);
}

