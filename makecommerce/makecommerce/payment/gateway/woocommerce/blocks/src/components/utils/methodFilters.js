import { useSelect } from '@wordpress/data';

/**
 * Checks if a payment method is allowed by comparing cart total with min/max constraints.
 */
export const isAllowedMethod = (method, cartTotal) => {
    if (cartTotal === null) return true; // if for some reason cart Total price not found
    const min = method.min_amount ?? 0;
    const max = method.max_amount ?? Infinity;
    return cartTotal >= min && cartTotal <= max;
};


/**
 * Filters all method groups and subgroups against the cart total.
 */
export const filterMethods = (methods) => {

    const { cartTotal } = useSelect((select) => {
        const store = select('wc/store/cart');
        const cart = store?.getCartData();
        const total = parseInt(cart?.totals?.total_price); // can be NaN
        return {
            cartTotal: isNaN(total) ? null : (total / 100),
        };
    }, []);

    const result = {};

    for (const [key, group] of Object.entries(methods)) {
        if (Array.isArray(group)) {
            // Flat array group (e.g., cards)
            result[key] = group.filter((method) => isAllowedMethod(method, cartTotal));
        } else if (typeof group === 'object' && group !== null) {
            // Nested grouped methods (e.g., banklinks_grouped)
            result[key] = {};
            for (const [country, countryMethods] of Object.entries(group)) {
                result[key][country] = countryMethods.filter((method) => isAllowedMethod(method, cartTotal));
            }
        } else {
            result[key] = group;
        }
    }

    return result;
};