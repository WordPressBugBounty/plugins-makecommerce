import { useEffect } from '@wordpress/element';
import { addAction, removeAction } from '@wordpress/hooks';

// When user changes shipping address, change selected country
export const useSyncShippingCountry = (methods, setSelectedCountry) => {
    useEffect(() => {
        const hookCallback = (e) => {
            const country = e?.storeCart?.shippingAddress?.country?.toLowerCase() || 'other';
            const validCountries = Object.keys(methods?.banklinks_grouped || {});

            setSelectedCountry(validCountries.includes(country) ? country : 'other');
        };

        addAction(
            'experimental__woocommerce_blocks-checkout-set-shipping-address',
            'makecommerce',
            hookCallback
        );

        return () => {
            removeAction(
                'experimental__woocommerce_blocks-checkout-set-shipping-address',
                'makecommerce'
            );
        };
    }, [methods, setSelectedCountry]);
};
