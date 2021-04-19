var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/model/step-navigator': {
                'Magento_Braintree/js/model/step-navigator-mixin': true
            },
            'Magento_Braintree/js/view/payment/method-renderer/cc-form': {
                'Magento_Braintree/js/reCaptcha/braintree-cc-method-mixin': true
            }
        }
    },
    map: {
        '*': {
            braintreeCheckoutPayPalAdapter: 'Magento_Braintree/js/view/payment/adapter'
        }
    }
};
