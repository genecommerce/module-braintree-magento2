define(
    [
        'Magento_Checkout/js/view/payment/default',
        'braintree',
        'braintreeDataCollector',
        'braintreeVenmo',
        'Magento_Braintree/js/form-builder',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/translate'
    ],
    function (
        Component,
        braintree,
        dataCollector,
        venmo,
        formBuilder,
        messageList,
        fullScreenLoader,
        $t
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                deviceData: null,
                paymentMethodNonce: null,
                template: 'Magento_Braintree/payment/venmo',
                venmoInstance: null
            },

            clickVenmoBtn: function () {
                let self = this;

                if (!this.venmoInstance) {
                    this.setErrorMsg('Venmo not initialized, please try reloading');
                    return;
                }

                this.venmoInstance.tokenize(function (tokenizeErr, payload) {
                    if (tokenizeErr) {
                        self.setErrorMsg(tokenizeErr);
                    } else {
                        self.handleVenmoSuccess(payload);
                    }
                });
            },

            collectDeviceData: function (clientInstance, callback) {
                let self = this;
                dataCollector.create({
                    client: clientInstance,
                    paypal: true
                }, function (dataCollectorErr, dataCollectorInstance) {
                    if (dataCollectorErr) {
                        console.error('Error collecting device data:', dataCollectorErr);
                        return;
                    }
                    self.deviceData = dataCollectorInstance.deviceData;
                    callback();
                });
            },

            getClientToken: function () {
                return window.checkoutConfig.payment[this.getCode()].clientToken;
            },

            getCode: function() {
                return 'braintree_venmo';
            },

            getData: function () {
                let data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'payment_method_nonce': this.paymentMethodNonce,
                        'device_data': this.deviceData
                    }
                };

                data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

                return data;
            },

            getPaymentMarkSrc: function () {
                return window.checkoutConfig.payment[this.getCode()].paymentMarkSrc;
            },

            getTitle: function() {
                return 'Venmo';
            },

            getVenmoBtn: function () {
                let button = document.createElement('button');
                button.innerHTML = $t('Pay with Venmo');
                return button.outerHTML;
            },

            handleVenmoSuccess: function (payload) {
                this.setPaymentMethodNonce(payload.nonce);
                this.placeOrder();
            },

            initialize: function () {
                this._super();

                let self = this;

                braintree.create({
                    authorization: self.getClientToken()
                }, function (clientError, clientInstance) {
                    if (clientError) {
                        this.setErrorMsg('Unable to initialize Braintree Client.');
                        return;
                    }

                    // Collect device data
                    self.collectDeviceData(clientInstance, function () {
                        // callback from collectDeviceData
                        venmo.create({
                            client: clientInstance,
                            allowNewBrowserTab: false
                        }, function (venmoErr, venmoInstance) {
                            if (venmoErr) {
                                self.setErrorMsg('Error initializing Venmo ' + venmoErr);
                                return;
                            }

                            if (!venmoInstance.isBrowserSupported()) {
                                self.setErrorMsg('Browser does not support Venmo.');
                                return;
                            }

                            self.setVenmoInstance(venmoInstance);
                        });
                    });
                });

                return this;
            },

            initObservable: function () {
                this._super();
                return this;
            },

            isBrowserSupported: function () {
                return venmo.isBrowserSupported();
            },

            setErrorMsg: function (message) {
                messageList.addErrorMessage({
                    message: $t(message)
                });
            },

            setPaymentMethodNonce: function (nonce) {
                this.paymentMethodNonce = nonce;
            },

            setVenmoInstance: function (instance) {
                this.venmoInstance = instance;
            }
        });
    }
);
