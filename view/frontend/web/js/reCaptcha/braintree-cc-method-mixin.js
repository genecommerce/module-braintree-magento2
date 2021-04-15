/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Checkout/js/model/payment/additional-validators'
], function ($, additionalValidators) {
    'use strict';

    return function (originalComponent) {
        return originalComponent.extend({
            /**
             * Initializes reCaptcha
             */
            placeOrder: function () {
                var original = this._super.bind(this),
                    // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    isEnabled = window.checkoutConfig.msp_recaptcha_braintree,
                    // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                    paymentFormSelector = $('#co-payment-form'),
                    startEvent = 'captcha:startExecute',
                    endEvent = 'captcha:endExecute';

                if (!additionalValidators.validate() || !isEnabled) {
                    return original();
                }

                paymentFormSelector.off(endEvent).on(endEvent, function () {
                        original();
                        paymentFormSelector.off(endEvent);
                    }
                );

                paymentFormSelector.trigger(startEvent);
            }
        });
    };
});