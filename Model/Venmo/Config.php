<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Venmo;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Config
 * @package Magento\Braintree\Model\Venmo
 * @author Paul Canning <paul.canning@gene.co.uk>
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';

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
     * Map Braintree Environment setting
     *
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getEnvironment(): string
    {
        if ('production' !== $this->braintreeConfig->getEnvironment()) {
            return 'TEST';
        }

        return 'PRODUCTION';
    }
}
