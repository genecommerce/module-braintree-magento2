define(
    ['uiComponent', 'Magento_Checkout/js/model/payment/renderer-list'],
    function (Component, rendererList) {
        'use strict';

        rendererList.push(
            {
                type: 'braintree_googlepay',
                component: 'Magento_Braintree/js/googlepay/implementations/core-checkout/method-renderer/googlepay'
            }
        );

        return Component.extend({});
    }
);