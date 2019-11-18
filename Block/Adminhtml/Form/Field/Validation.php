<?php

namespace Magento\Braintree\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;

/**
 * Class Validation
 * @package Magento\Braintree\Block\Adminhtml\Form\Field
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Validation extends Field
{
    /**
     * @inheritDoc
     */
    protected function _renderScopeLabel(AbstractElement $element): string
    {
        // Return empty label
        return '';
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        // Replace field markup with validation button
        $title = __('Validate Credentials');
        $envId = 'select-groups-braintree-section-groups-braintree-groups-braintree-'
            . 'required-fields-environment-value';
        $merchantId = 'text-groups-braintree-section-groups-braintree-groups-braintree-'
            . 'required-fields-merchant-id-value';
        $publicKeyId = 'password-groups-braintree-section-groups-braintree-groups-braintree-'
            . 'required-fields-public-key-value';
        $privateKeyId = 'password-groups-braintree-section-groups-braintree-groups-braintree-'
            . 'required-fields-private-key-value';
        $storeId = 0;

        if ($this->getRequest()->getParam('website')) {
            $website = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'));
            if ($website->getId()) {
                /** @var Store $store */
                $store = $website->getDefaultStore();
                $storeId = $store->getStoreId();
            }
        }

        $endpoint = $this->getUrl('braintree/configuration/validate', ['storeId' => $storeId]);

        // @codingStandardsIgnoreStart
        $html = <<<TEXT
            <button
                type="button"
                title="{$title}"
                class="button"
                onclick="braintreeValidator.call(this, '{$endpoint}', '{$envId}', '{$merchantId}', '{$publicKeyId}', '{$privateKeyId}')">
                <span>{$title}</span>
            </button>
TEXT;
        // @codingStandardsIgnoreEnd

        return $html;
    }
}
