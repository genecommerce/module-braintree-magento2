require(['jquery', 'Magento_Ui/js/modal/alert', 'mage/translate'], function ($, alert, $t) {
    window.braintreeValidator = function (endpoint, env_id, merch_id, public_id, private_id) {
        env_id = $('[data-ui-id="' + env_id + '"]').val();
        merch_id = $('[data-ui-id="' + merch_id + '"]').val();
        public_id = $('[data-ui-id="' + public_id + '"]').val();
        private_id = $('[data-ui-id="' + private_id + '"]').val();

        /* Remove previous success message if present */
        if ($(".braintree-credentials-success-message")) {
            $(".braintree-credentials-success-message").remove();
        }

        /* Basic field validation */
        var errors = [];

        if (!env_id || env_id !== 'sandbox' && env_id !== 'production') {
            errors.push($t("Please select an Environment"));
        }

        if (!merch_id) {
            errors.push($t("Please enter a Merchant ID"));
        }

        if (!public_id) {
            errors.push($t('Please enter a Public Key'));
        }

        if (!private_id) {
            errors.push($t('Please enter a Private Key'));
        }

        if (errors.length > 0) {
            alert({
                title: $t('Braintree Credential Validation Failed'),
                content:  errors.join('<br />')
            });
            return false;
        }

        $(this).text($t("We're validating your credentials...")).attr('disabled', true);

        var self = this;
        $.post(endpoint, {
            environment: env_id,
            merchant_id: merch_id,
            public_key: public_id,
            private_key: private_id
        }).done(function () {
            $('<div class="message message-success braintree-credentials-success-message">' + $t("Your credentials are valid.") + '</div>').insertAfter(self);
        }).fail(function () {
            alert({
                title: $t('Braintree Credential Validation Failed'),
                content: $t('Your Braintree Credentials could not be validated. Please ensure you have selected the correct environment and entered a valid Merchant ID, Public Key and Private Key.')
            });
        }).always(function () {
            $(self).text($t("Validate Credentials")).attr('disabled', false);
        });
    }


    // HiC Activation
    window.activateHicAccount = function (endpoint, url_id, email_id, pw_id) {
        url_id = $('[data-ui-id="' + url_id + '"]').val();
        email_id = $('[data-ui-id="' + email_id + '"]').val();
        pw_id = $('[data-ui-id="' + pw_id + '"]').val();

        /* Remove previous success message if present */
        if ($(".hic-activation-success-message")) {
            $(".hic-activation-success-message").remove();
        }

        /* Basic field validation */
        var errors = [];

        if (!url_id) {
            errors.push($t("Please enter your site url"));
        }

        if (!email_id) {
            errors.push($t("Please enter an email"));
        }

        if (!pw_id) {
            errors.push($t('Please enter a password'));
        }

        if (errors.length > 0) {
            alert({
                title: $t('HiConversion Account Activation Failed'),
                content:  errors.join('<br />')
            });
            return false;
        }

        $(this).text($t("We're activating your account...")).attr('disabled', true);

        var self = this;
        $.post(endpoint, {
            site_url: url_id,
            email: email_id,
            password: pw_id
        }).done(function () {
            $('<div class="message message-success hic-activation-success-message">' + $t("Your account was successfully activated.") + '</div>').insertAfter(self);
        }).fail(function () {
            alert({
                title: $t('HiConversion Account Activation Failed'),
                content: $t('Your HiConversion account could not be activated. Please ensure you have entered a valid site url, email address, and password.')
            });
        }).always(function () {
            $(self).text($t("Activate HiConversion Account")).attr('disabled', false);
        });
    }
});