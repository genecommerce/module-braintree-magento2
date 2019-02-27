<?php

namespace Magento\Braintree\Cron;

use Magento\Braintree\Api\CreditPriceRepositoryInterface;
use Magento\Braintree\Api\Data\CreditPriceDataInterfaceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Braintree\Model\Paypal\CreditApi;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Psr\Log\LoggerInterface;
use Magento\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;
use Magento\Store\Model\StoreManager;
use Magento\Braintree\Api\Data\CreditPriceDataInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Website;

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
     * @var CreditPriceDataInterfaceFactory
     */
    private $creditPriceFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CreditApi
     */
    private $creditApi;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PayPalCreditConfig
     */
    private $config;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var array
     */
    protected $websites;

    /**
     * CreditPrice constructor.
     * @param CreditPriceRepositoryInterface $creditPriceRepository
     * @param CreditPriceDataInterfaceFactory $creditPriceDataInterfaceFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param CreditApi $creditApi
     * @param ProductCollectionFactory $productCollection
     * @param LoggerInterface $logger
     * @param PayPalCreditConfig $config
     * @param StoreManager $storeManager
     */
    public function __construct(
        CreditPriceRepositoryInterface $creditPriceRepository,
        CreditPriceDataInterfaceFactory $creditPriceDataInterfaceFactory,
        ScopeConfigInterface $scopeConfig,
        CreditApi $creditApi,
        ProductCollectionFactory $productCollection,
        LoggerInterface $logger,
        PayPalCreditConfig $config,
        StoreManager $storeManager
    ) {
        $this->creditPriceRepository = $creditPriceRepository;
        $this->creditPriceFactory = $creditPriceDataInterfaceFactory;
        $this->scopeConfig = $scopeConfig;
        $this->creditApi = $creditApi;
        $this->productCollection = $productCollection;
        $this->logger = $logger;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function execute()
    {
        if (!$this->config->isCalculatorEnabled()) {
            return $this;
        }

        /* @var Website $website */
        foreach ($this->getWebsites() as $website) {
            if (!$website->getDefaultGroup() || !$website->getDefaultGroup()->getDefaultStore()) {
                continue;
            }

            $websiteId = $website->getId();
            $defaultStore = $website->getDefaultGroup()->getDefaultStore()->getId();

            // Set current store to allow for store specific catalog price rules to be applied
            $this->storeManager->setCurrentStore($defaultStore);

            // Retrieve paginated collection of product and their price
            $collection = $this->productCollection->create();
            $collection->addAttributeToSelect('price')
                ->addWebsiteFilter($websiteId)
                ->setPageSize(100);

            $lastPage = $collection->getLastPageNumber();
            for ($i = 1; $i <= $lastPage; $i++) {
                $collection->setCurPage($i);
                $collection->load();

                foreach ($collection as $product) {
                    try {
                        // Delete by product_id
                        $this->creditPriceRepository->deleteByProductId($product->getId(), $websiteId);

                        // Get product price including any catalog price rules or discounts
                        $productPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

                        // Retrieve data from PayPal
                        $priceOptions = $this->creditApi->getPriceOptions($productPrice);
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
                            $model->setWebsiteId($websiteId);

                            $this->creditPriceRepository->save($model);
                        }
                    } catch (AuthenticationException $e) {
                        throw new \Exception($e->getMessage());
                    } catch (LocalizedException $e) {
                        $this->logger->critical($e->getMessage());
                    }
                }

                $collection->clear();
            }
        }

        return $this;
    }

    /**
     * Retrieve website collection array
     *
     * @return array
     */
    private function getWebsites()
    {
        if ($this->websites === null) {
            $this->websites = $this->storeManager->getWebsites();
        }

        return $this->websites;
    }
}
