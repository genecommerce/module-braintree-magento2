# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.1.2]
### Added
- Callback to delete stored card in Braintree when Customer deletes card in account

### Changed

### Deprecated

### Removed

### Fixed
- Order button "unstuck" after invalid card details/failed payment
- Sort PayPal Credit instalments on Product page
- Stop cards always being stored after successful order
- PayPal billing address
- PayPal quote updater
- PayPal shipping address
- Vaulted cards now work with 3DS
- PayPal voucher redirect loop
- Apple Pay no longer adding shipping twice

### Security

## [3.1.1] - 2019-03-05
### Fixed
- Fix bug that stopped PayPal working on mini-cart

## [3.1.0] - 2019-02-27
### Added
- Functionality to add PayPal button to Product page

## [3.0.7] - 2019-01-30
### Fixed
- Vaulted cards now work correctly

[Unreleased]: https://github.com/genecommerce/module-braintree-magento2/compare/3.1.2...develop
[3.1.2]: https://github.com/genecommerce/module-braintree-magento2/compare/3.1.1...3.1.2
[3.1.1]: https://github.com/genecommerce/module-braintree-magento2/compare/3.1.0...3.1.1
[3.1.0]: https://github.com/genecommerce/module-braintree-magento2/compare/3.0.7...3.1.0
[3.0.7]: https://github.com/genecommerce/module-braintree-magento2/compare/3.0.6...3.0.7