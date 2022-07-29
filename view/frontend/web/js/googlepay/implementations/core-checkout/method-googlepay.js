/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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

        let config = window.checkoutConfig.payment;

        if (config['braintree_googlepay'].clientToken) {
            rendererList.push({
                type: 'braintree_googlepay',
                component: 'Magento_Braintree/js/googlepay/implementations/core-checkout/method-renderer/googlepay'
            });
        }

        return Component.extend({});
    }
);
