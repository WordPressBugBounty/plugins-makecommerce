import {CreditCardSelector} from "../selectors/CreditCardSelector";

export const CreditCardWidget = ({ methods }) => (
    <div className="makecommerce-payment-methods">
        <div className="makecommerce_cc_widget makecommerce-picker bg-white p-3">
            <CreditCardSelector methods={methods?.cards} />
        </div>
    </div>
);