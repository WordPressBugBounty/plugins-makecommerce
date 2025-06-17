import { useEffect } from '@wordpress/element';

const fetchCarrierMachines = async (carrier, selectedCountry) => {
    const response = await fetch('/wp-admin/admin-ajax.php', {
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
    useEffect(() => {
        setSelectedPickupPoint(null);
        setPickupPointOptions([])

        const currentPackage = shippingRates?.[0];
        if (!currentPackage) return;

        const selectedRate = currentPackage.shipping_rates.find((rate) => rate.selected);
        if (!selectedRate) return;

        const rateId = selectedRate.rate_id;
        if (rateId?.startsWith('mc_courier_')) {
            setExtensionData('makecommerce', 'shipping_method', rateId);
            return;
        }

        if (!rateId?.startsWith('mc_pickuppoint_')) {
            setIsMcShipping(false);
            return;
        }

        const carrier = rateId.split('_')[2];
        if (!carrier) return;

        setExtensionData('makecommerce', 'shipping_method', rateId);
        setIsMcShipping(true);
        setLoading(true);

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
