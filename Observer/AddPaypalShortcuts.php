<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Observer;

use Magento\Braintree\Block\Paypal\Button;
use Magento\Braintree\Gateway\Config\PayPal\Config as PayPalConfig;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Checkout\Block\QuoteShortcutButtons;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class AddPaypalShortcuts implements ObserverInterface
{
    /**
     * Block class
     */
    const PAYPAL_SHORTCUT_BLOCK = Button::class;

    /**
     * @var PayPalConfig
     */
    private $payPalConfig;

    /**
     * AddPaypalShortcuts Constructor
     *
     * @param PayPalConfig $config
     */
    public function __construct(
        PayPalConfig $config
    ) {
        $this->payPalConfig = $config;
    }

    /**
     * Add Braintree PayPal shortcut buttons
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->payPalConfig->isActive()) {
            return;
        }

        // Remove button from catalog pages
        if ($observer->getData('is_catalog_product')) {
            return;
        }

        /** @var ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();
        $shortcut = $shortcutButtons->getLayout()->createBlock(self::PAYPAL_SHORTCUT_BLOCK);
        $shortcut->setIsCart(get_class($shortcutButtons) === QuoteShortcutButtons::class);
        $shortcutButtons->addShortcut($shortcut);
    }
}
