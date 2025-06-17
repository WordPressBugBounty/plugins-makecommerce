import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';
import {Main} from "./components/Main";
import {PaymentWidget} from "./components/widgets/PaymentWidget";

const settings = getSetting('makecommerce_data', {});

registerPaymentMethod({
    name: settings.name,
    label: settings.label,
    description: settings.description,
    gatewayId: settings.gatewayId,
    content: <Main />,
    edit: <Main />,
    savedTokenComponent: <PaymentWidget />,
    icons: [],
    canMakePayment: () => true,
    ariaLabel: 'MakeCommerce payment option',
    supports: {
        features: ['products', 'subscriptions'],
    },
});
