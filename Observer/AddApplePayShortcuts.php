<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Observer;

use Magento\Braintree\Block\ApplePay\Shortcut\Button;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Checkout\Block\QuoteShortcutButtons;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class AddApplePayShortcuts implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * AddApplePayShortcuts Constructor
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Add Apple Pay shortcut button
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isActive()) {
            return;
        }

        // Remove button from catalog pages
        if ($observer->getData('is_catalog_product')) {
            return;
        }

        /** @var ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();
        $shortcut = $shortcutButtons->getLayout()->createBlock(Button::class);
        $shortcut->setIsCart(get_class($shortcutButtons) === QuoteShortcutButtons::class);
        $shortcutButtons->addShortcut($shortcut);
    }
}
