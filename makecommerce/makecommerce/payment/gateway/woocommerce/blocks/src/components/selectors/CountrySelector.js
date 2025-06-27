import { getSetting } from '@woocommerce/settings';
const wcCountries = getSetting('countries', {});
import { __ } from '@wordpress/i18n'

export const CountrySelector = ({ selected, onChange, countriesList }) => (
    <div className="mb-3">
        <label htmlFor="makecommerce_customer_country_picker" className="d-none"></label>
        <select
            className="form-select"
            name="makecommerce_country_picker"
            id="makecommerce_customer_country_picker"
            value={selected}
            onChange={(e) => onChange(e.target.value)}
        >
            {Object.keys(countriesList).map((country) => {
                const code = country.toUpperCase();
                const label =
                    wcCountries[code] ||
                    (country.toLowerCase() === 'other' ? 'International' : country);

                return (
                    <option key={country} value={country}>
                        {__(label, 'wc_makecommerce_domain')}
                    </option>
                );
            })}
        </select>
    </div>
);

