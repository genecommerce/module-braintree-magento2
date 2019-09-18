<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Lpm;

use Magento\Braintree\Model\StoreConfigResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';
    const KEY_TITLE = 'title';
    /**
     * @var StoreConfigResolver
     */
    private $storeConfigResolver;

    /**
     * {@inheritDoc}
     */
    public function __construct(
        StoreConfigResolver $storeConfigResolver,
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = \Magento\Payment\Gateway\Config\Config::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->storeConfigResolver = $storeConfigResolver;
    }

    /**
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getTitle(): string
    {
        return $this->getValue(
            self::KEY_TITLE,
            $this->storeConfigResolver->getStoreId()
        );
    }
}
