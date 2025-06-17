export const PaymentMethod = ({method, id}) => {
    return (
        <>
            <div key={id} className="payment-method-wrapper col-6">
                <div
                    className="payment-method col-12 d-flex flex-row justify-content-center align-items-center shadow-sm rounded position-relative"
                    style={{ height: '48px', cursor: 'pointer' }}
                >
                    <div className="check"></div>
                    <input
                        type="radio"
                        className="btn-check makecommerce_payment_option"
                        name="payment"
                        id={id}
                    />
                    <label
                        className="payment-control w-100 h-100 d-flex justify-content-center align-items-center m-0"
                        htmlFor={id}
                        style={{ cursor: 'pointer' }}
                    >
                        <img
                            src={method.logo_url}
                            className="mc-payment-logo"
                            style={{ height: '30px' }}
                            alt={
                                method.display_name.charAt(0).toUpperCase() +
                                method.display_name.slice(1)
                            }
                        />
                    </label>
                </div>
            </div>
        </>
    )
}