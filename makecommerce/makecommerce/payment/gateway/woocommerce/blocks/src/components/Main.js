import { useEffect } from '@wordpress/element';
import { getSetting } from '@woocommerce/settings';
import { PaymentWidget } from './widgets/PaymentWidget';
import { CreditCardWidget } from './widgets/CreditCardWidget';
import { filterMethods } from "./utils/methodFilters";

const settings = getSetting('makecommerce_data', {});
const methods = settings.methods || [];
const manualRenewals = settings.manualRenewals || false;
let customerCountry = settings.defaultCountry || '';

export const Main = (props) => {
    const { eventRegistration, emitResponse, cartData } = props;
    const { onPaymentSetup } = eventRegistration;

    const subscription = cartData?.cartItems?.some((item) => item.type === 'subscription');

    let allowedMethods = filterMethods(methods);

    // If no bank links loop once to add cards
    if (typeof methods['banklinks_grouped']['other'] === 'undefined') {
        methods['banklinks_grouped']['other'] = [];
    }

    useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            const selected = document.querySelector('input.makecommerce_payment_option:checked');

            if (selected) {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            preselected_method_makecommerce: selected.id,
                        },
                    },
                };
            }
            return {
                type: emitResponse.responseTypes.ERROR,
                message: settings.paymentError,
            };
        });

        return () => unsubscribe();
    }, [emitResponse.responseTypes, onPaymentSetup]);

    return subscription && !manualRenewals ? <CreditCardWidget methods={allowedMethods} /> : <PaymentWidget methods={allowedMethods} defaultCountry={customerCountry} />;
};
