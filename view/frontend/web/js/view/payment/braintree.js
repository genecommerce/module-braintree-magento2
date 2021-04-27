/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
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

        let config = window.checkoutConfig.payment,
            braintreeType = 'braintree',
            payPalType = 'braintree_paypal';

        if (config[braintreeType].isActive) {
            rendererList.push(
                {
                    type: braintreeType,
                    component: 'Magento_Braintree/js/view/payment/method-renderer/hosted-fields'
                }
            );
        }

        if (config[payPalType].isActive) {
            rendererList.push(
                {
                    type: payPalType,
                    component: 'Magento_Braintree/js/view/payment/method-renderer/paypal'
                }
            );
        }

        rendererList.push(
            {
                type: 'braintree_venmo',
                component: 'Magento_Braintree/js/view/payment/method-renderer/venmo'
            },
            {
                type: 'braintree_ach_direct_debit',
                component: 'Magento_Braintree/js/view/payment/method-renderer/ach'
            },
            {
                type: 'braintree_local_payment',
                component: 'Magento_Braintree/js/view/payment/method-renderer/lpm'
            }
        );

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
