define(
    ['uiComponent', 'Magento_Checkout/js/model/payment/renderer-list'],
    function (Component, rendererList) {
        'use strict';

        rendererList.push(
            {
                type: 'braintree_applepay',
                component: 'Magento_Braintree/js/applepay/implementations/core-checkout/method-renderer/applepay'
            }
        );

        return Component.extend({});
    }
);