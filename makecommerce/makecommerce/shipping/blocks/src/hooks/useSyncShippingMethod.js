import { useEffect } from '@wordpress/element';
import { addAction, removeAction } from '@wordpress/hooks';

/**
 * Sync MakeCommerce shipping logic with shipping address changes
 *
 * @param {Function} onShippingCountryChange - callback when shipping country changes
 */
export const useSyncShippingMethod = (onShippingCountryChange) => {
    useEffect(() => {
        const handleShippingChange = (e) => {
            const country = e?.storeCart?.shippingAddress?.country?.toUpperCase() || 'other';

            if (typeof onShippingCountryChange === 'function') {
                onShippingCountryChange(country, e?.storeCart);
            }
        };

        addAction(
            'experimental__woocommerce_blocks-checkout-set-shipping-address',
            'makecommerce/shipping',
            handleShippingChange
        );

        return () => {
            removeAction(
                'experimental__woocommerce_blocks-checkout-set-shipping-address',
                'makecommerce/shipping'
            );
        };
    }, [onShippingCountryChange]);
};
