<?php

namespace Magento\Braintree\Block\System\Config\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Fieldset
 * @package Magento\Braintree\Block\System\Config\Form
 */
class Fieldset extends \Magento\Paypal\Block\Adminhtml\System\Config\Fieldset\Payment
{
    /**
     * @var \Magento\Braintree\Gateway\Config\PayPalCredit\Config
     */
    private $config;

    /**
     * Fieldset constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Config\Model\Config $backendConfig
     * @param \Magento\Braintree\Gateway\Config\PayPalCredit\Config $config
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Config\Model\Config $backendConfig,
        \Magento\Braintree\Gateway\Config\PayPalCredit\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $backendConfig, $data);
        $this->config = $config;
    }

    /**
     * Remove UK specific fields from the form when on a non-UK merchant country
     * @param AbstractElement $element
     * @return string
     */
    protected function _getChildrenElementsHtml(AbstractElement $element) // @codingStandardsIgnoreLine
    {
        $countryCode = $this->getRequest()->getParam("paypal_country");
        if ($countryCode) {
            $locale = strtolower($countryCode);
        } else {
            $locale = strtolower($this->config->getMerchantCountry());
        }

        // Only available to GB
        if ($locale != "gb") {
            $element->removeField(
                'payment_' . $locale . '_braintree_section_braintree_braintree_paypal_credit'
            );
            $element->removeField(
                'payment_other_braintree_section_braintree_braintree_paypal_credit'
            );
        }

        if ($locale != "gb" && $locale != "us") {
            $element->removeField(
                'payment_other_braintree_section_braintree_braintree_paypal_credit_active'
            );
        }

//        print_r(array_keys($element->getForm()->_elementsIndex));exit;

        return parent::_getChildrenElementsHtml($element);
    }
}
