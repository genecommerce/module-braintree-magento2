define(
    [
        'jquery',
        'braintree',
        'braintreeLpm',
        'Magento_Ui/js/model/messageList',
        'Magento_Ui/js/modal/alert',
        'mage/translate'
    ], function (
        $,
        braintree,
        lpm,
        messageList,
        alert,
        $t
    ) {
        'use strict';
        $.widget('braintree.fallback', {
            _create: function () {
                var self = this;
                $('body').trigger('processStart');

                braintree.create({
                    authorization: self.options.clientToken
                }, function (clientError, clientInstance) {
                    if (clientError) {
                        $('body').trigger('processStop');
                        self.createAlert(clientError.message);
                        return;
                    }

                    lpm.create({
                        client: clientInstance
                    }, function (lpmError, lpmInstance) {
                        if (lpmError) {
                            $('body').trigger('processStop');
                            self.createAlert(lpmError.message);
                            return;
                        }

                        if (lpmInstance.hasTokenizationParams()) {
                            lpmInstance.tokenize(function (tokenizeError, payload) {
                                if (tokenizeError) {
                                    // handle tokenization error
                                    $('body').trigger('processStop');
                                    self.createAlert(tokenizeError.message);
                                    return;
                                }

                                // send payload.nonce to your server
                                console.log(payload);

                                $('body').trigger('processStop');
                            });
                        } else {
                            // if this page should only be reached when
                            // recovering from a mobile app switch,
                            // display an error for not having the
                            // correct params in the query string
                            $('body').trigger('processStop');
                        }
                    });
                });
            },

            createAlert: function (message, title = 'Whoops, something went wrong!', buttonText = 'Dismiss') {
                alert({
                    buttons: [{
                        class: 'action-primary action-accept',
                        click: function () {
                            this.closeModal(true);
                        },
                        text: $t(buttonText)
                    }],
                    content: message,
                    modalClass: 'alert',
                    title: $t(title)
                });
            }
        });

        return $.braintree.fallback;
    }
);
