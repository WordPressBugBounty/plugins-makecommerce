import { useEffect } from '@wordpress/element';
import { addAction, removeAction } from '@wordpress/hooks';

/**
 * Sync MakeCommerce shipping logic with shipping address changes
 *
 * @param {Function} onShippingCountryChange - callback when shipping country changes
 */
/**
 * Sync MakeCommerce shipping logic with shipping country changes only.
 */
export const useSyncShippingMethod = (setSelectedCountry) => {
    useEffect(() => {
        const handleShippingChange = async (e) => {
            const currentCountry = e?.storeCart?.shippingAddress?.country?.toUpperCase() || 'OTHER';

            setSelectedCountry((previousCountry) => {
                // Only update country when country changes, not when city, zip changes
                if (previousCountry !== currentCountry) {
                    updateWooCommerceCountry(currentCountry)
                    return currentCountry;
                }
                return previousCountry;
            });
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
    }, [setSelectedCountry]);
};

/**
 * Update WooCommerce customer country in Blocks Checkout
 * via extensionCartUpdate.
 */
const updateWooCommerceCountry = async (country, updateBilling = true) => {
    const extensionCartUpdate = wc?.blocksCheckout?.extensionCartUpdate;

    if (!extensionCartUpdate) {
        console.error('extensionCartUpdate is not available. Must be run on Blocks Checkout.');
        return Promise.reject('extensionCartUpdate is not available');
    }

    const payload = {
        country: country.toUpperCase(),
    };

    return extensionCartUpdate({
        namespace: 'makecommerce',
        data: payload,
    }).catch((error) => {
            console.error('Failed to update country:', error);
        });
};


