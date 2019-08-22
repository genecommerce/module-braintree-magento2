define(
    [
        'Magento_Checkout/js/view/payment/default',
        'braintree',
        'braintreeVenmo',
        'mage/translate'
    ],
    function (
        Component,
        braintree,
        venmo,
        $t
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Braintree/payment/venmo'
            },

            isBrowserSupported: function () {
                return venmo.isBrowserSupported();
            },

            getCode: function() {
                return 'braintree_venmo';
            },

            getTitle: function() {
                return 'Venmo';
            },

            getVenmoBtn: function () {
                let button = document.createElement('button');
                button.innerHTML = $t('Pay with Venmo');
                return button.outerHTML;
            },

            clickVenmoBtn: function () {
                braintree.create({
                    authorization: this.getClientToken()
                }, function (clientErr, clientInstance) {
                    if (clientErr) {
                        console.error('Error creating Client:', clientErr);
                        return;
                    }

                    venmo.create({
                        client: clientInstance,
                        allowNewBrowserTab: true
                    }, function (venmoErr, venmoInstance) {
                        if (venmoErr) {
                            console.error('Error creating Venmo:', venmoErr);
                            return;
                        }

                        if (!venmoInstance.isBrowserSupported()) {
                            console.log('Browser does not support Venmo');
                            return;
                        }

                        console.log(venmoInstance);
                    });
                });
            },

            getClientToken: function () {
                return window.checkoutConfig.payment['braintree'].clientToken; // use braintree token for the time being, should be fine?
            }
        });
    }
);