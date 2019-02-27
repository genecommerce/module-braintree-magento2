<?php

namespace Magento\Braintree\Cron;

use Magento\Braintree\Api\Data\CreditPriceDataInterface;
use Magento\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\Website;

/**
 * Class CreditPrice
 * @package Magento\Braintree\Cron
 */
class CreditPrice
{
    /**
     * @var \Magento\Braintree\Api\CreditPriceRepositoryInterface
     */
    private $creditPriceRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollection;

    /**
     * @var \Magento\Braintree\Api\Data\CreditPriceDataInterfaceFactory
     */
    private $creditPriceFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Braintree\Model\Paypal\CreditApi
     */
    private $creditApi;

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
     * @param \Magento\Braintree\Api\CreditPriceRepositoryInterface $creditPriceRepository
     * @param \Magento\Braintree\Api\Data\CreditPriceDataInterfaceFactory $creditPriceDataInterfaceFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Braintree\Model\Paypal\CreditApi $creditApi
     * @param ProductCollectionFactory $productCollection
     * @param LoggerInterface $logger
     * @param PayPalCreditConfig $config
     * @param StoreManager $storeManager
     */
    public function __construct(
        \Magento\Braintree\Api\CreditPriceRepositoryInterface $creditPriceRepository,
        \Magento\Braintree\Api\Data\CreditPriceDataInterfaceFactory $creditPriceDataInterfaceFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Braintree\Model\Paypal\CreditApi $creditApi,
        ProductCollectionFactory $productCollection,
        LoggerInterface $logger,
        PayPalCreditConfig $config,
        StoreManager $storeManager
    ) {
        $this->creditPriceRepository = $creditPriceRepository;
        $this->scopeConfig = $scopeConfig;
        $this->productCollection = $productCollection;
        $this->logger = $logger;
        $this->creditPriceFactory = $creditPriceDataInterfaceFactory;
        $this->creditApi = $creditApi;
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

                        $productPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

                        // Retrieve data from PayPal
                        $priceOptions = $this->creditApi->getPriceOptions($productPrice);
                        foreach ($priceOptions as $priceOption) {
                            // Populate model
                            /** @var $model \Magento\Braintree\Api\Data\CreditPriceDataInterface */
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
