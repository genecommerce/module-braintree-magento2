define([
    'mage/utils/wrapper',
    'jquery'
], function (wrapper, $) {
    'use strict';

    let mixin = {
        handleHash: function (originalFn) {
            var hashString = window.location.hash.replace('#', '');
            return (hashString.includes('venmo')) ? false : originalFn();
        }
    };

    return function (target) {
        return wrapper.extend(target, mixin);
    };
});
