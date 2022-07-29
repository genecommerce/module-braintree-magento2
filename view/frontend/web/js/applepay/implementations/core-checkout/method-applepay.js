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

        if (config['braintree_applepay'].clientToken) {
            rendererList.push({
                type: 'braintree_applepay',
                component: 'Magento_Braintree/js/applepay/implementations/core-checkout/method-renderer/applepay'
            });
        }

        return Component.extend({});
    }
);
