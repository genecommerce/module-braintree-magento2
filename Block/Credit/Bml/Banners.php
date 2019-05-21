<?php

namespace Magento\Braintree\Block\Credit\Bml;

use Magento\Framework\View\Element\Template;
use Magento\Braintree\Gateway\Config\PayPalCredit\Config as BraintreeConfig;
use Magento\Paypal\Model\Config;

/**
 * Class Banners
 *
 * @see \Magento\Paypal\Block\Bml\Banners
 */
class Banners extends Template
{
    /**
     * @var string
     */
    protected $section;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var BraintreeConfig
     */
    protected $config;

    /**
     * @var Config
     */
    protected $paypalConfig;

    /**
     * Banners constructor.
     * @param Template\Context $context
     * @param BraintreeConfig $config
     * @param Config $paypalConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        BraintreeConfig $config,
        Config $paypalConfig,
        array $data = []
    ) {
        $this->section = isset($data['section']) ? (string)$data['section'] : '';
        $this->position = isset($data['position']) ? (int)$data['position'] : 0;
        $this->config = $config;
        $this->paypalConfig = $paypalConfig;

        parent::__construct($context, $data);
    }

    /**
     * Disable block output if banner turned off or PublisherId is missing
     *
     * @inheritDoc
     */
    protected function _toHtml(): string
    {
        if (!$this->config->isActive() || !$this->config->isUS()) {
            return '';
        }

        $publisherId = $this->paypalConfig->getBmlPublisherId();
        $display = $this->config->getBmlDisplay($this->section);
        $position = $this->config->getBmlPosition($this->section);

        if (!$publisherId || $display === 0 || $this->position === $position) {
            return '';
        }

        $this->setData('publisher_id', $publisherId);
        $this->setData('size', $this->config->getBmlSize($this->section));

        return parent::_toHtml();
    }
}
