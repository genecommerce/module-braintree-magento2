define(
    [
        'Magento_Checkout/js/view/payment/default',
        'braintree',
        'braintreeDataCollector',
        'braintreeVenmo',
        'Magento_Braintree/js/form-builder',
        'mage/translate'
    ],
    function (
        Component,
        braintree,
        dataCollector,
        venmo,
        formBuilder,
        $t
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Braintree/payment/venmo',
                deviceData: null
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
                let self = this;

                braintree.create({
                    authorization: self.getClientToken()
                }, function (clientErr, clientInstance) {
                    if (clientErr) {
                        console.error('Error creating Client:', clientErr);
                        return;
                    }

                    self.collectDeviceData(clientInstance);

                    venmo.create({
                        client: clientInstance,
                        allowNewBrowserTab: false
                    }, function (venmoErr, venmoInstance) {
                        if (venmoErr) {
                            console.error('Error creating Venmo:', venmoErr);
                            return;
                        }

                        if (!venmoInstance.isBrowserSupported()) {
                            console.warn('Browser does not support Venmo');
                            return;
                        }

                        venmoInstance.tokenize(function (tokenizeErr, payload) {
                            if (tokenizeErr) {
                                console.error(tokenizeErr);
                            } else {
                                this.handleVenmoSuccess(payload);
                            }
                         });
                    });
                });
            },

            collectDeviceData: function (clientInstance) {
                let self = this;
                dataCollector.create({
                    client: clientInstance,
                    paypal: true
                }, function (dataCollectorErr, dataCollectorInstance) {
                    if (dataCollectorErr) {
                        console.error('Error collecting device data:', dataCollectorErr);
                        return;
                    }
                    console.log('Got device data:', dataCollectorInstance.deviceData);
                    self.deviceData = dataCollectorInstance.deviceData;
                });
            },

            getData: function () {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'payment_method_nonce': this.paymentMethodNonce
                    }
                };
            },

            handleVenmoSuccess: function (payload) {
                // Send payload.nonce to your server.
                console.log('Got a payment method nonce:', payload.nonce);
                // Display the Venmo username in your checkout UI.
                console.log('Venmo user:', payload.details.username);

                this.setPaymentMethodNonce(payload.nonce);
                this.placeOrder();
            },

            getClientToken: function () {
                return window.checkoutConfig.payment['braintree'].clientToken; // use braintree token for the time being, should be fine?
            }
        });
    }
);