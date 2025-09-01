import { useState } from '@wordpress/element';
import { CountrySelector } from '../selectors/CountrySelector.js';
import { BankLinkSelector } from "../selectors/BankLinkSelector";
import { CreditCardSelector } from '../selectors/CreditCardSelector';
import { PayLaterSelector } from '../selectors/PayLaterSelector';
import { useSyncShippingCountry } from "../hooks/useSyncShippingCountry";

export const PaymentWidget = ({ methods, defaultCountry }) => {
    const normalizedDefaultCountry = methods?.banklinks_grouped?.[defaultCountry.toLowerCase()]
        ? defaultCountry.toLowerCase() : 'other';
    const [selectedCountry, setSelectedCountry] = useState(normalizedDefaultCountry);

    // Sync WooCommerce checkout shipping address with selected country
    useSyncShippingCountry(methods, setSelectedCountry);
    const handleCountryChange = (country) => {
        setSelectedCountry(country);
    };

    const countryBanklinks = methods?.banklinks_grouped?.[selectedCountry] || [];
    const cardMethods = methods?.cards || [];
    const countryPayLaterMethods = methods?.paylater_grouped?.[selectedCountry] || [];

    return (
        <div className="makecommerce-payment-methods">
            <input type="hidden" id="makecommerce_customer_country" value={normalizedDefaultCountry}/>
            <div className="makecommerce-picker bg-white p-3">
                <CountrySelector
                    selected={selectedCountry}
                    onChange={handleCountryChange}
                    countriesList={methods['banklinks_grouped']}
                />

                <div
                    className="makecommerce_country_methods"
                    id={`makecommerce_country_methods_${selectedCountry}`}
                >
                    {countryBanklinks.length > 0 && (
                        <BankLinkSelector country={selectedCountry} methods={countryBanklinks} />
                    )}

                    {cardMethods.length > 0 && (
                        <CreditCardSelector methods={cardMethods} />
                    )}

                    {countryPayLaterMethods.length > 0 && (
                        <PayLaterSelector country={selectedCountry} methods={countryPayLaterMethods} />
                    )}
                </div>
            </div>
        </div>
    );
};
