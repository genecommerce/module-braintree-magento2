/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(
    [
        'MSP_ReCaptcha/js/reCaptcha',
        'jquery',
        'MSP_ReCaptcha/js/registry'
    ],
    function (Component, $, registry) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'MSP_ReCaptcha/reCaptcha',
                reCaptchaId: 'msp-recaptcha'
            },
            /**
             * Recaptcha callback
             * @param {String} token
             */
            reCaptchaCallback: function (token) {
                this.tokenField.value = token;
                this.$parentForm.trigger('captcha:endExecute');
            },

            /**
             * Initialize reCaptcha after first rendering
             */
            initCaptcha: function () {
                var me = this,
                    $parentForm,
                    $wrapper,
                    $reCaptcha,
                    widgetId,
                    isEnabled = window.checkoutConfig.msp_recaptcha_braintree;

                if (!isEnabled) {
                    return;
                }
                if (this.captchaInitialized) {
                    return;
                }

                this.captchaInitialized = true;

                /*
                 * Workaround for data-bind issue:
                 * We cannot use data-bind to link a dynamic id to our component
                 * See:
                 * https://stackoverflow.com/questions/46657573/recaptcha-the-bind-parameter-must-be-an-element-or-id
                 *
                 * We create a wrapper element with a wrapping id and we inject the real ID with jQuery.
                 * In this way we have no data-bind attribute at all in our reCaptcha div
                 */
                $wrapper = $('#' + this.getReCaptchaId() + '-wrapper');
                $reCaptcha = $wrapper.find('.g-recaptcha');
                $reCaptcha.attr('id', this.getReCaptchaId());

                $parentForm = $wrapper.parents('form');

                // eslint-disable-next-line no-undef
                widgetId = grecaptcha.render(this.getReCaptchaId(), {
                    'sitekey': this.settings.siteKey,
                    'theme': this.settings.theme,
                    'size': this.settings.size,
                    'badge': this.badge ? this.badge : this.settings.badge,
                    'callback': function (token) { // jscs:ignore jsDoc
                        me.reCaptchaCallback(token);
                        me.validateReCaptcha(true);
                    },
                    'expired-callback': function () { // jscs:ignore jsDoc
                        me.validateReCaptcha(false);
                    }
                });

                $parentForm.on('captcha:startExecute', function (event) {
                    if (!me.tokenField.value && me.settings.size === 'invisible') {
                        // eslint-disable-next-line no-undef
                        grecaptcha.execute(widgetId);
                        event.preventDefault(event);
                        event.stopImmediatePropagation();
                    } else {
                        me.$parentForm.trigger('captcha:endExecute');
                    }
                });

                // Create a virtual token field
                this.tokenField = $('<input type="text" id="token-grecaptcha-braintree" name="token-grecaptcha-braintree" style="display: none" />')[0];
                this.$parentForm = $parentForm;
                $parentForm.append(this.tokenField);

                registry.ids.push(this.getReCaptchaId());
                registry.captchaList.push(widgetId);
                registry.tokenFields.push(this.tokenField);
            },
            /**
             * Return true if reCaptcha is visible
             * @returns {Boolean}
             */
            getIsVisible: function () {
                return window.checkoutConfig.msp_recaptcha_braintree;
            },
        });
    }
);
