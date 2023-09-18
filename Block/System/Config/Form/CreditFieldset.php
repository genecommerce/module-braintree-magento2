<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Block\System\Config\Form;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Braintree\Gateway\Config\PayPalCredit\Config;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;

class CreditFieldset extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * CreditFieldset constructor
     *
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->config = $config;
    }

    /**
     * Remove US & UK specific fields from the form if merchant country is not UK or US.
     *
     * @inheritDoc
     */
    public function _getChildrenElementsHtml(AbstractElement $element): string // @codingStandardsIgnoreLine
    {
        $countryCode = $this->getRequest()->getParam('paypal_country');
        if ($countryCode) {
            $locale = $countryCode;
        } else {
            $locale = $this->config->getMerchantCountry();
        }
        if (is_string($locale)) {
            $locale = strtolower($locale);
        }

        // Only available to GB
        if ($locale !== 'gb') {
            $element->removeField(
                'payment_' . $locale . '_braintree_section_braintree_braintree_paypal_braintree_paypal_credit'
            );
            $element->removeField(
                'payment_other_braintree_section_braintree_braintree_paypal_braintree_paypal_credit'
            );
        }

        // Only available to GB and US
        if ($locale !== 'gb' && $locale !== 'us') {
            $element->removeField(
                'payment_' . $locale . '_braintree_section_braintree_braintree_paypal_braintree_paypal_credit_active'
            );
            $element->removeField(
                'payment_other_braintree_section_braintree_braintree_paypal_braintree_paypal_credit_active'
            );
        }

        return parent::_getChildrenElementsHtml($element);
    }
}
