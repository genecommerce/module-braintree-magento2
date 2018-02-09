<?php

namespace Magento\Braintree\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

/**
 * Class Validation
 * @package Magento\Braintree\Block\Adminhtml\Form\Field
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Validation extends Field
{
    /**
     * Force scope label to be blank
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderScopeLabel(\Magento\Framework\Data\Form\Element\AbstractElement $element) // @codingStandardsIgnoreLine
    {
        return '';
    }

    /**
     * Replace field markup with validation button
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) // @codingStandardsIgnoreLine
    {
        $title = __("Validate Credentials");
        $envId = 'select-groups-braintree-section-groups-braintree-groups-braintree-'
            . 'required-fields-environment-value';
        $merchantId = 'text-groups-braintree-section-groups-braintree-groups-braintree-'
            . 'required-fields-merchant-id-value';
        $publicKeyId = 'password-groups-braintree-section-groups-braintree-groups-braintree-'
            . 'required-fields-public-key-value';
        $privateKeyId = 'password-groups-braintree-section-groups-braintree-groups-braintree-'
            . 'required-fields-private-key-value';
        $storeId = 0;

        if ($this->getRequest()->getParam("website")) {
            $website = $this->_storeManager->getWebsite($this->getRequest()->getParam("website"));
            if ($website->getId()) {
                $storeId = $website->getId();
            }
        }

        $endpoint = $this->getUrl("braintree/configuration/validate", ['storeId' => $storeId]);
        $html = '<button type="button" title="' .$title . '" class="button" onclick="' .
            "braintreeValidator.call(this, '$endpoint', '$envId', '$merchantId', '$publicKeyId', '$privateKeyId')" .
            '"><span>' . $title . '</span></button>';

        return $html;
    }
}
