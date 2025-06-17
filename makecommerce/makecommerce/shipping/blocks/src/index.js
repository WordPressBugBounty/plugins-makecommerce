import metadata from '../block.json';
import { registerCheckoutBlock } from '@woocommerce/blocks-checkout';
import { PickupPointSelect } from './components/PickupPointSelect';

registerCheckoutBlock({
    metadata,
    component: PickupPointSelect,
});
