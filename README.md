# Braintree Payments

Module Magento\Braintree implements integration with the Braintree payment system.

## Overview

This module overwrites the original Magento Braintree module, to provide additional features and bug fixes.

## Available Payment Methods
* Credit Card
    * Visa
    * Mastercard
    * Amex
    * Discover
    * JCB
    * Diners
    * Maestro
    * UnionPay
    * Restrictions apply.
* PayPal
* PayPal Credit
    * US and UK only. Restrictions apply.
* Google Pay
* Apple Pay
* Venmo (US only)
* ACH Direct Debit (US only)

## Additional Features

### Custom Fields
If you would like to add [Custom Fields](https://articles.braintreepayments.com/control-panel/custom-fields) to your
Braintree transactions, we provide an example module [here](https://github.com/genecommerce/module-braintree-customfields-example)
that can be used to create a custom module for your store to add these fields.