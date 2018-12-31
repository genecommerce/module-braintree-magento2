<?php

namespace Magento\Braintree\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

/**
 * Class HicActivation
 */
class HicActivation extends Field
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
     * Replace field markup with activation button
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) // @codingStandardsIgnoreLine
    {
        $title = __("Activate HiConversion Account");
        $urlId = 'text-groups-braintree-section-groups-braintree-groups-braintree-'
            . 'hic-groups-create-account-fields-site-url-value';
        $emailId = 'text-groups-braintree-section-groups-braintree-groups-braintree-'
            . 'hic-groups-create-account-fields-email-value';
        $pwId = 'text-groups-braintree-section-groups-braintree-groups-braintree-'
            . 'hic-groups-create-account-fields-password-value';
        $storeId = 0;

        if ($this->getRequest()->getParam("website")) {
            $website = $this->_storeManager->getWebsite($this->getRequest()->getParam("website"));
            if ($website->getId()) {
                $storeId = $website->getId();
            }
        }

        $endpoint = $this->getUrl("braintree/configuration/activateHiconversion", ['storeId' => $storeId]);
        $html = '<button type="button" title="' . $title . '" class="button" onclick="' .
            "activateHicAccount.call(this, '$endpoint', '$urlId', '$emailId', '$pwId')" .
            '"><span>' . $title . '</span></button>';

        return $html;
    }
}
