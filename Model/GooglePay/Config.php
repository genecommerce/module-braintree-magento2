<?php

namespace Magento\Braintree\Model\GooglePay;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Config
 * @package Magento\Braintree\Model\GooglePay
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';
    const KEY_CC_TYPES = 'cctypes';

    /**
     * @var \Magento\Braintree\Gateway\Config\Config
     */
    protected $braintreeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Braintree\Gateway\Config\Config $braintreeConfig
     * @param null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Braintree\Gateway\Config\Config $braintreeConfig,
        $methodCode = null,
        $pathPattern = \Magento\Payment\Gateway\Config\Config::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->braintreeConfig = $braintreeConfig;
    }

    /**
     * Get merchant name to display
     *
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->getValue('merchant_id');
    }

    /**
     * Get Payment configuration status
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    /**
     * Get allowed payment card types
     *
     * @return array
     */
    public function getAvailableCardTypes(): array
    {
        $ccTypes = $this->getValue(self::KEY_CC_TYPES);

        return !empty($ccTypes) ? explode(',', $ccTypes) : [];
    }

    /**
     * Map Braintree Environment setting
     *
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getEnvironment(): string
    {
        if ($this->braintreeConfig->getEnvironment() !== 'production') {
            return 'TEST';
        }

        return 'PRODUCTION';
    }
}
