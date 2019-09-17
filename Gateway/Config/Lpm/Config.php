<?php
declare(strict_types=1);

namespace Magento\Braintree\Gateway\Config\Lpm;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Braintree\Model\StoreConfigResolver;

/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';

    const KEY_MERCHANT_ACCOUNT_ID = 'merchant_account_id';

    /**
     * @var StoreConfigResolver
     */
    private $storeConfigResolver;

    /**
     * Config constructor.
     * @param StoreConfigResolver $storeConfigResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        StoreConfigResolver $storeConfigResolver,
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->storeConfigResolver = $storeConfigResolver;
    }

    /**
     * Get Payment configuration status
     *
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function isActive(): bool
    {
        $active = $this->getValue(
            self::KEY_ACTIVE,
            $this->storeConfigResolver->getStoreId()
        );
        return (bool) $active;
    }

    /**
     * Get Merchant account ID
     *
     * @return string|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getMerchantAccountId()
    {
        return $this->getValue(
            self::KEY_MERCHANT_ACCOUNT_ID,
            $this->storeConfigResolver->getStoreId()
        );
    }
}
