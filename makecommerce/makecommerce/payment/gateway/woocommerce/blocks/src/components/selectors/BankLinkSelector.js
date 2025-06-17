import { PaymentMethod } from "../templates/PaymentMethod";
import { __ } from '@wordpress/i18n'

export const BankLinkSelector = ({ country, methods }) => {
    return (
        <>
            <div className="d-flex align-items-center gap-2 my-3 text-muted fw-medium fs-6">
                <span>{ __('Bank payments', 'wc_makecommerce_domain') }</span>
                <div className="flex-grow-1 border-top"></div>
            </div>
            <div className="row g-3">
                {methods.map((method) => {
                    const bank_link_id = `${country}_${method.name}`;
                    return <PaymentMethod key={bank_link_id} method={method} id={bank_link_id}/>
                })}
            </div>
        </>
    );
};