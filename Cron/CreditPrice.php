<?php

namespace Magento\Braintree\Cron;

use Exception;
use Magento\Braintree\Api\CreditPriceRepositoryInterface;
use Magento\Braintree\Api\Data\CreditPriceDataInterface;
use Magento\Braintree\Api\Data\CreditPriceDataInterfaceFactory;
use Magento\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;
use Magento\Braintree\Model\Paypal\CreditApi;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Class CreditPrice
 * @package Magento\Braintree\Cron
 */
class CreditPrice
{
    /**
     * @var CreditPriceRepositoryInterface
     */
    private $creditPriceRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollection;

    /**
     * @var CreditPriceDataInterfaceFactory
     */
    private $creditPriceFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CreditApi
     */
    private $creditApi;

    /**
     * @var PayPalCreditConfig
     */
    private $config;

    /**
     * CreditPrice constructor.
     * @param CreditPriceRepositoryInterface $creditPriceRepository
     * @param CreditPriceDataInterfaceFactory $creditPriceDataInterfaceFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param CreditApi $creditApi
     * @param ProductCollectionFactory $productCollection
     * @param LoggerInterface $logger
     * @param PayPalCreditConfig $config
     */
    public function __construct(
        CreditPriceRepositoryInterface $creditPriceRepository,
        CreditPriceDataInterfaceFactory $creditPriceDataInterfaceFactory,
        ScopeConfigInterface $scopeConfig,
        CreditApi $creditApi,
        ProductCollectionFactory $productCollection,
        LoggerInterface $logger,
        PayPalCreditConfig $config
    ) {
        $this->creditPriceRepository = $creditPriceRepository;
        $this->scopeConfig = $scopeConfig;
        $this->productCollection = $productCollection;
        $this->logger = $logger;
        $this->creditPriceFactory = $creditPriceDataInterfaceFactory;
        $this->creditApi = $creditApi;
        $this->config = $config;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function execute(): self
    {
        if (!$this->config->isCalculatorEnabled()) {
            return $this;
        }

        // Retrieve paginated collection of product and their price
        $collection = $this->productCollection->create();
        $collection->addAttributeToSelect('price')
            ->setPageSize(100);

        $lastPage = $collection->getLastPageNumber();
        for ($i = 1; $i <= $lastPage; $i++) {
            $collection->setCurPage($i);
            $collection->load();

            foreach ($collection as $product) {
                try {
                    // Delete by product_id
                    $this->creditPriceRepository->deleteByProductId($product->getId());

                    // Retrieve data from PayPal
                    $priceOptions = $this->creditApi->getPriceOptions($product->getFinalPrice());
                    foreach ($priceOptions as $priceOption) {
                        // Populate model
                        /** @var CreditPriceDataInterface $model */
                        $model = $this->creditPriceFactory->create();
                        $model->setProductId($product->getId());
                        $model->setTerm($priceOption['term']);
                        $model->setMonthlyPayment($priceOption['monthly_payment']);
                        $model->setInstalmentRate($priceOption['instalment_rate']);
                        $model->setCostOfPurchase($priceOption['cost_of_purchase']);
                        $model->setTotalIncInterest($priceOption['total_inc_interest']);

                        $this->creditPriceRepository->save($model);
                    }
                } catch (LocalizedException $e) {
                    $this->logger->critical($e->getMessage());
                }
            }

            $collection->clear();
        }

        return $this;
    }
}
