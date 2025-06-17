import { PaymentMethod } from "../templates/PaymentMethod";
import {__} from "@wordpress/i18n";

export const CreditCardSelector = ({ methods }) => {
    return (
        <>
            <div className="d-flex align-items-center gap-2 my-3 text-muted fw-medium fs-6">
                <span>{ __('Cards', 'wc_makecommerce_domain') }</span>
                <div className="flex-grow-1 border-top"></div>
            </div>
            <div className="row g-3">
                {methods.map((method) => {
                    const cc_id = `card_${method.name}`;
                    return <PaymentMethod key={cc_id} method={method} id={cc_id}/>
                })}
            </div>
        </>
    );
};