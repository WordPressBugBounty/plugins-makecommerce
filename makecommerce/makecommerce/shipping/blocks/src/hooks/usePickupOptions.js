import { useEffect, useRef } from '@wordpress/element';
import {getSetting} from "@woocommerce/settings";

const mcShippingBlocksData = getSetting('mc-shipping-blocks_data', {});

const fetchCarrierMachines = async (carrier, selectedCountry) => {
    const ajaxUrl = mcShippingBlocksData?.ajaxUrl || '/wp-admin/admin-ajax.php';
    const response = await fetch(ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'get_carrier_machines',
            mc_carrier_id: carrier,
            country: selectedCountry,
            selected_machine: '',
        }),
    });

    return response.json();
};

const extractPickupPoints = (data) => {
    const machines = [];
    for (const city in data.machines) {
        data.machines[city].forEach((m) => {
            machines.push({
                value: m.id,
                label: `${m.name} - ${m.city}, ${m.address}, ${m.zip}`,
                meta: m,
            });
        });
    }
    return machines;
};

export const usePickupOptions = ({
                                     shippingRates,
                                     selectedCountry,
                                     setIsMcShipping,
                                     setLoading,
                                     setPickupPointOptions,
                                     setSelectedPickupPoint,
                                     setExtensionData,
                                 }) => {
    // If country and rate id are same, then do not fetch the machines.
    const previousRateRef = useRef('');
    const previousCountryRef = useRef('');

    // WooCommerce unmounts this whole block (it's a child of
    // woocommerce/checkout-shipping-methods-block) the instant the customer
    // switches to WooCommerce's blocks own local pickup, before the effect below ever
    // sees the new shippingRates. Clear our extension data on unmount so a
    // stale MC selection isn't left behind for the order.
    useEffect(() => {
        return () => {
            setExtensionData('makecommerce', 'shipping_method', '');
            setExtensionData('makecommerce', 'machine_id', '');
        };
    }, [setExtensionData]);

    useEffect(() => {
        const currentPackage = shippingRates?.[0];
        if (!currentPackage) return;

        const selectedRate = currentPackage.shipping_rates.find((rate) => rate.selected);
        if (!selectedRate) return;

        const rateId = selectedRate.rate_id;
        setExtensionData('makecommerce', 'shipping_method', rateId);

        if (rateId?.startsWith('mc_courier_')) {
            setPickupPointOptions([]);
            setSelectedPickupPoint(null);
            setExtensionData('makecommerce', 'machine_id', '');
            previousRateRef.current = rateId;
            previousCountryRef.current = selectedCountry;
            return;
        }

        if (!rateId?.startsWith('mc_pickuppoint_')) {
            setIsMcShipping(false);
            setPickupPointOptions([]);
            setSelectedPickupPoint(null);
            setExtensionData('makecommerce', 'machine_id', '');
            previousRateRef.current = rateId;
            previousCountryRef.current = selectedCountry;
            return;
        }

        const carrier = rateId.split('_')[2];
        if (!carrier) return;

        if (previousRateRef.current === rateId &&
            previousCountryRef.current === selectedCountry) {
            // No changes, skip fetching
            setIsMcShipping(true);
            return;
        }
        previousRateRef.current = rateId;
        previousCountryRef.current = selectedCountry;

        setIsMcShipping(true);
        setLoading(true);
        // reset fields, fetch machines
        setPickupPointOptions([]);
        setSelectedPickupPoint(null);

        fetchCarrierMachines(carrier, selectedCountry)
            .then((data) => {
                const machines = extractPickupPoints(data);
                setPickupPointOptions(machines);
                setLoading(false);

                if (machines.length > 0) {
                    const selected = machines.find(opt => opt.meta?.selected === true);
                    if (selected) {
                        setSelectedPickupPoint(selected);
                        setExtensionData('makecommerce', 'machine_id', selected.value);
                    } else {
                        setSelectedPickupPoint(machines[0]);
                        setExtensionData('makecommerce', 'machine_id', machines[0].value);
                    }
                }
            })
            .catch(() => setLoading(false));
    }, [shippingRates, selectedCountry]);
};
