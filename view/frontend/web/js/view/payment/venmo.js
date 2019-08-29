define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'braintree_venmo',
                component: 'Magento_Braintree/js/view/payment/method-renderer/venmo'
            }
        );

        return Component.extend({});
    }
);
