import { PaymentMethod } from "../templates/PaymentMethod";
import {__} from "@wordpress/i18n";

export const PayLaterSelector = ({ country, methods })  => {
    return (
        <>
            <div className="d-flex align-items-center gap-2 my-3 text-muted fw-medium fs-6">
                <span>{ __('Pay later', 'wc_makecommerce_domain') }</span>
                <div className="flex-grow-1 border-top"></div>
            </div>
            <div className="row g-3">
                {methods.map((method) => {
                    const pay_later_id = `${country}_${method.name}`;
                    return <PaymentMethod key={pay_later_id} method={method} id={pay_later_id}/>
                })}
            </div>
        </>
    );
}