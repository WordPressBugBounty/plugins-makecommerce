import { useState, useCallback } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { getSetting } from '@woocommerce/settings';
import Select from 'react-select';
import { usePickupOptions } from '../hooks/usePickupOptions';
import { useSyncShippingMethod } from '../hooks/useSyncShippingMethod';

const mcShippingBlocksData = getSetting('mc-shipping-blocks_data', {});

export const PickupPointSelect = ({ checkoutExtensionData }) => {
    const defaultCountry = mcShippingBlocksData?.defaultCountry || 'EE';
    const pickupPointPlaceholder = mcShippingBlocksData?.pickupPointPlaceholder || 'Select pickup point';
    const [selectedCountry, setSelectedCountry] = useState(defaultCountry);
    const [selectedPickupPoint, setSelectedPickupPoint] = useState(null);
    const [pickupPointOptions, setPickupPointOptions] = useState([]);
    const [isMcShipping, setIsMcShipping] = useState(false);
    const [loading, setLoading] = useState(false);
    const { setExtensionData } = checkoutExtensionData;

    useSyncShippingMethod(setSelectedCountry);

    const { shippingRates } = useSelect((select) => {
        const store = select('wc/store/cart');
        const cart = store?.getCartData?.();

        return {
            shippingRates: cart?.shippingRates ?? [],
        };
    }, []);

    usePickupOptions({
        shippingRates,
        selectedCountry,
        setIsMcShipping,
        setLoading,
        setPickupPointOptions,
        setSelectedPickupPoint,
        setExtensionData,
    });

    const onChange = useCallback(
        (val) => {
            setSelectedPickupPoint(val);
            setExtensionData('makecommerce', 'machine_id', val.value);
        },
        [setExtensionData]
    );

    // If it is not MakeCommerce shipping method or is courier
    if (!isMcShipping || pickupPointOptions.length === 0) return null;

    return (
        <div className="mc-block-pickup-point">
            <div className="wc-block-components-checkout-step__heading">
                <h2
                className="wc-block-components-title wc-block-components-checkout-step__title">
                    {pickupPointPlaceholder}
                </h2>
            </div>
            <div className="wc-block-components-checkout-step__container">
                <div className="wc-block-components-checkout-step__content">
                    <Select
                        id="mc_pickup_point_select"
                        options={pickupPointOptions}
                        value={selectedPickupPoint}
                        isLoading={loading}
                        onChange={onChange}
                        styles={{
                            container: (base) => ({
                                ...base,
                                display: 'grid',
                                gridTemplateColumns: 'minmax(0, 1fr)',
                            }),
                          }}
                    />
                </div>
            </div>
        </div>
    );
};
