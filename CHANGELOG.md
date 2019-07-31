# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.2.0]
### Added
- 3DS 2 now supported
- Basic validation to Dynamic Descriptor configuration options to alleviate errors in checkout

### Fixed
- PayPal breaking Grouped Product pages
- Return type stopping Swagger from compiling (https://github.com/Thundar)
- Handling of exceptions in GatewayCommand class that would show "blank" errors on checkout
- Broken CSS selector
- Giftcards not working with PayPal
- Reverted a change introduced in 3.1.2 where card details were only stored in the database if 
the Vault config option was enabled. This is because partial invoicing, refunds etc need the stored card data. However,
a bug in core Magento 2.3.1 means that if the Vault is turned off, cards are always shown in customer accounts

### Removed
- Layout options for PayPal buttons, due to the buttons now being rendered separately

## [3.1.3]
### Fixed
- Issue with Configurable Product prices
- Return type issue for Google Pay configuration

## [3.1.2]
### Added
- Callback to delete stored card in Braintree when Customer deletes card in account

### Fixed
- Vaulted cards now work with 3DS
- Order button "unstuck" after invalid card details/failed payment
- Stop cards always being stored after successful order
- No cart session exception handled correctly (https://github.com/shilpambb)
- PayPal
  - Credit instalments now sorted on Product page
  - Billing address now updated correctly
  - Quote updater no longer throws an error if store uses DB table prefix
  - Shipping address now used for Virtual Products
  - Voucher redirect loop fixed
  - 2nd address line now included (https://github.com/igor-imaginemage)
  - Credit calculator now uses correct total values (https://github.com/diazwatson)
  - Region now added to shipping address correctly on PayPal OneClick/Review screen
- Apple Pay
  - Shipping cost is no longer added multiple times
  - Apple Pay dialog now shows correct total on initial popup

## [3.1.1] - 2019-03-05
### Fixed
- Fix bug that stopped PayPal working on mini-cart

## [3.1.0] - 2019-02-27
### Added
- Functionality to add PayPal button to Product page

## [3.0.7] - 2019-01-30
### Fixed
- Vaulted cards now work correctly

[3.2.0]: https://github.com/genecommerce/module-braintree-magento2/compare/3.1.3...3.2.0
[3.1.3]: https://github.com/genecommerce/module-braintree-magento2/compare/3.1.2...3.1.3
[3.1.2]: https://github.com/genecommerce/module-braintree-magento2/compare/3.1.1...3.1.2
[3.1.1]: https://github.com/genecommerce/module-braintree-magento2/compare/3.1.0...3.1.1
[3.1.0]: https://github.com/genecommerce/module-braintree-magento2/compare/3.0.7...3.1.0
[3.0.7]: https://github.com/genecommerce/module-braintree-magento2/compare/3.0.6...3.0.7