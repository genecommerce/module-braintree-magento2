/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'underscore',
    'Magento_Braintree/js/view/payment/method-renderer/paypal'
], function (
    $,
    _,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Braintree/payment/paypal-credit',
            code: 'braintree_paypal_credit',

            /**
             * PayPal client configuration
             * {Object}
             */
            clientConfig: {
                offerCredit: true,
                buttonId: 'braintree_paypal_credit_placeholder'
            }
        }
    });
});