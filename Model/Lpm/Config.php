<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Lpm;

use Magento\Braintree\Gateway\Config\Config as BraintreeConfig;
use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\StoreConfigResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\Repository;

/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';
    const KEY_ALLOWED_METHODS = 'allowed_methods';
    const KEY_TITLE = 'title';
    const PAYMENT_METHODS = [
        'bancontact' => [
            'method' => 'bancontact',
            'countries' => 'BE',
            'label' => 'Bancontact',
            'image' => 'bancontact.svg'
        ],
        'eps' => [
            'method' => 'eps',
            'countries' => 'AT',
            'label' => 'EPS',
            'image' => 'eps.svg'
        ],
        'giropay' => [
            'method' => 'giropay',
            'countries' => 'DE',
            'label' => 'giropay',
            'image' => 'giropay.svg'
        ],
        'ideal' => [
            'method' => 'ideal',
            'countries' => 'NL',
            'label' => 'iDEAL',
            'image' => 'ideal.svg'
        ],
        'sofort' => [
            'method' => 'sofort',
            'countries' => ['AT', 'BE', 'DE', 'ES', 'IT', 'NL'],
            'label' => 'Klarna Pay Now / SOFORT',
            'image' => 'sofort.svg'
        ],
        'mybank' => [
            'method' => 'mybank',
            'countries' => 'IT',
            'label' => 'MyBank',
            'image' => 'mybank.svg'
        ],
        'p24' => [
            'method' => 'p24',
            'countries' => 'PL',
            'label' => 'P24',
            'image' => 'p24.svg'
        ],
        'sepa' => [
            'method' => 'sepa',
            'countries' => ['AT', 'DE'],
            'label' => 'SEPA/ELV Direct Debit',
            'image' => 'sepa.svg'
        ]
    ];

    /**
     * @var StoreConfigResolver
     */
    private $storeConfigResolver;

    /**
     * @var string
     */
    private $clientToken = '';

    /**
     * @var BraintreeAdapter
     */
    private $adapter;

    /**
     * @var BraintreeConfig
     */
    private $braintreeConfig;

    /**
     * @var array
     */
    private $allowedMethods;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @param StoreConfigResolver $storeConfigResolver
     * {@inheritDoc}
     */
    public function __construct(
        BraintreeAdapter $adapter,
        BraintreeConfig $braintreeConfig,
        StoreConfigResolver $storeConfigResolver,
        Repository $assetRepo,
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = \Magento\Payment\Gateway\Config\Config::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->adapter = $adapter;
        $this->braintreeConfig = $braintreeConfig;
        $this->storeConfigResolver = $storeConfigResolver;
        $this->assetRepo = $assetRepo;
    }

    /**
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function isActive(): bool
    {
        return (bool) $this->getValue(
            self::KEY_ACTIVE,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getAllowedMethods(): array
    {
        $allowedMethods = explode(
            ',',
            $this->getValue(
                self::KEY_ALLOWED_METHODS,
                $this->storeConfigResolver->getStoreId()
            )
        );

        foreach ($allowedMethods as $allowedMethod) {
            $this->allowedMethods[] = self::PAYMENT_METHODS[$allowedMethod];
        }

        return $this->allowedMethods;
    }

    /**
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getClientToken(): string
    {
        if (empty($this->clientToken) && $this->isActive()) {
            $params = [];

            $merchantAccountId = $this->braintreeConfig->getMerchantAccountId();
            if (!empty($merchantAccountId)) {
                $params[PaymentDataBuilder::MERCHANT_ACCOUNT_ID] = $merchantAccountId;
            }

            $this->clientToken = $this->adapter->generate($params);
        }

        return $this->clientToken;
    }

    /**
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getMerchantAccountId(): string
    {
        return $this->getValue(
            self::KEY_TITLE,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @return array
     */
    public function getPaymentIcons(): array
    {
        $icons = [
            'bancontact' => $this->assetRepo->getUrl('Magento_Braintree::images/bancontact.svg'),
            'eps' => $this->assetRepo->getUrl('Magento_Braintree::images/eps.svg'),
            'giropay' => $this->assetRepo->getUrl('Magento_Braintree::images/giropay.svg'),
            'ideal' => $this->assetRepo->getUrl('Magento_Braintree::images/ideal.svg'),
            'sofort' => $this->assetRepo->getUrl('Magento_Braintree::images/sofort.svg'),
            'mybank' => $this->assetRepo->getUrl('Magento_Braintree::images/mybank.svg'),
            'p24' => $this->assetRepo->getUrl('Magento_Braintree::images/p24.svg'),
            'sepa' => $this->assetRepo->getUrl('Magento_Braintree::images/sepa.svg')
        ];

        return $icons;
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
