<?php
/**
 * HiConversion
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * [http://opensource.org/licenses/MIT]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @Copyright © 2015 HiConversion, Inc. All rights reserved.
 * @license [http://opensource.org/licenses/MIT] MIT License
 */

namespace Magento\Braintree\Model\Hic;

use Magento\Catalog\Helper\Product\Configuration;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Helper\Image;
use Magento\Payment\Model\CcConfig;
use Magento\Store\Model\StoreManagerInterface;
use \Psr\Log\LoggerInterface;
use \Datetime;

/**
 * Integration data model
 *
 * @author HiConversion <support@hiconversion.com>
 */
class Data extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var RequestInterface
     */
    private $request;
    
    /**
     * @var CatalogHelper
     */
    private $catalogData;
 
    /**
     * @var Product
     */
    private $productHelper;

    /**
     * Catalog product configuration
     *
     * @var Configuration
     */
    private $productConfig;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
   
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var CcConfig
     */
    private $ccConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param RequestInterface $request
     * @param CatalogHelper $catalogData
     * @param Product $productHelper
     * @param Configuration $productConfig
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerSession $customerSession
     * @param OrderRepositoryInterface $orderRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StockRegistryInterface $stockRegistry
     * @param CheckoutSession $checkoutSession
     * @param Image $imageHelper
     * @param CcConfig $ccConfig
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Registry $registry,
        RequestInterface $request,
        CatalogHelper $catalogData,
        Product $productHelper,
        Configuration $productConfig,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        OrderRepositoryInterface $orderRepository,
        CategoryRepositoryInterface $categoryRepository,
        StockRegistryInterface $stockRegistry,
        CheckoutSession $checkoutSession,
        Image $imageHelper,
        CcConfig $ccConfig,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->catalogData = $catalogData;
        $this->productHelper = $productHelper;
        $this->productConfig = $productConfig;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
        $this->categoryRepository = $categoryRepository;
        $this->stockRegistry = $stockRegistry;
        $this->checkoutSession = $checkoutSession;
        $this->imageHelper = $imageHelper;
        $this->ccConfig = $ccConfig;
        $this->storeManager = $storeManager;
        $this->logger = $logger;

        parent::__construct(
            $context,
            $registry
        );
    }

    /**
     * Determines and returns page route
     *
     * @return string
     */
    private function getRoute()
    {
        return $this->request->getFullActionName();
    }

    /**
     * Determines if its a product page or not
     *
     * @return boolean
     */
    public function isProduct()
    {
        return 'catalog_product_view' == $this->getRoute();
    }

     /**
      * Determines if Confirmation page or not
      *
      * @return boolean
      */
    public function isConfirmation()
    {
        return 'checkout_onepage_success' == $this->getRoute();
    }

     /**
      * Retrieves page route and breadcrumb info and populates page
      * attribute
      *
      * @return $this
      */
    public function populatePageData()
    {
        $crumb = [];
        foreach ($this->catalogData->getBreadcrumbPath() as $item) {
            $crumb[] = $item['label'];
        }

        $this->setPage(
            [
                'route' => $this->getRoute(),
                'bc' => $crumb
            ]
        );
        return $this;
    }

     /**
      * Returns category names for each product
      * passed into function
      *
      * @param \Magento\Catalog\Api\Data\ProductInterface $product
      * @return array $categoryNames
      */
    private function getCategoryNames($product)
    {
        
        $categoryNames = [];
        foreach ($product->getCategoryIds() as $categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
                array_push($categoryNames, $category->getName());
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
 
        return $categoryNames;
    }

    /**
     * Get item options for

     * @param array $options
     * @return array
     */
    private function getItemOptions($options)
    {
        $result = [];
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }
        return $result;
    }

    /**
     * Get list of all options for product
     * @param \Magento\Sales\Model\Order\Item|\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @param $isOrder
     * @return array
     */
    private function getOptionList($item, $isOrder)
    {
        if ($isOrder) {
            $options = $item->getProductOptions();
            $options = $this->getItemOptions($options);
        } else {
            $options = $this->productConfig->getOptions($item);
        }
        $opts = [];
        foreach ($options as $option) {
            $formattedValue = $this->productConfig->getFormattedOptionValue($option);
            $opts[$option['label']] = $formattedValue['value'];
        }
        return $opts;
    }

     /**
      * Returns product information for each cart
      * item passed into function
      *
      * @param array $items
      * @param boolean $isOrder
      * @return array $data
      */
    private function getCartItems($items, $isOrder)
    {
        $data = [];

        foreach ($items as $item) {
            $product = $item->getProduct();
            $imageHelper = $this->imageHelper->init($product, 'cart_page_product_thumbnail');

            $info = [];
            $info['ds'] = (float)$item->getDiscountAmount();
            $info['tx'] = (float)$item->getTaxAmount();
            $info['pr'] = (float)$product->getFinalPrice();
            $info['bpr'] = (float)$item->getPrice();
            if ($isOrder) {
                $info['qt'] = (float)$item->getQtyOrdered();
            } else {
                $info['qt'] = (float)$item->getQty();
            }
            $stockItem = $this->stockRegistry
                ->getStockItemBySku($item->getSku(), $product->getStore()->getWebsiteId());
            if ($stockItem) {
                $info['sq'] =  $stockItem->getQty();
            }
            $info['id'] = $item->getId();
            $info['url'] = $this->productHelper->getProductUrl($product);
            $info['nm'] = $item->getName();
            $info['img'] = $imageHelper->getUrl();
            $info['sku'] = $item->getSku();
            $info['cat'] = $this->getCategoryNames($product);

            $info['opt'] = $this->getOptionList($item, $isOrder);

            $data[] = $info;
        }

        return $data;
    }

     /**
      * Returns currency information
      *
      * @return array $currencyInfo
      */
    private function getCurrencyInfo()
    {
        $currencyInfo = [];
  
        if ($this->storeManager->getStore()->getCurrentCurrencyCode()) {
            $currencyInfo['cu'] = $this->storeManager->getStore()->getCurrentCurrencyCode();
        }
        if ($this->storeManager->getStore()->getBaseCurrencyCode()) {
            $currencyInfo['bcu'] = $this->storeManager->getStore()->getBaseCurrencyCode();
        }
        if ($this->storeManager->getStore()->getCurrentCurrencyRate()) {
            $currencyInfo['cr'] = $this->storeManager->getStore()->getCurrentCurrencyRate();
        }
  
        return $currencyInfo;
    }

     /**
      * Retrieves all orders for a given customer id
      *
      * @param int $customerId
      * @return \Magento\Sales\Api\Data\OrderInterface[] Array of items
      */
    private function getOrders($customerId)
    {
        $this->searchCriteriaBuilder->addFilter('customer_id', $customerId);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->orderRepository->getList($searchCriteria);
        return $searchResults->getItems();
    }

    /**
     * Retrieves product information and populates product attribute
     *
     * @return $this
     */
    public function populateProductData()
    {
        $currentProduct = $this->catalogData->getProduct();
        if ($currentProduct) {
            $imageHelper = $this->imageHelper->init($currentProduct, 'cart_page_product_thumbnail');
            $data['cat'] = $this->getCategoryNames($currentProduct);
            $data['id']  = $currentProduct->getId();
            $data['nm']  = $currentProduct->getName();
            $data['url'] = $this->productHelper->getProductUrl($currentProduct);
            $data['sku'] = $currentProduct->getSku();
            $data['bpr'] = $currentProduct->getPrice();
            $data['pr'] = $currentProduct->getFinalPrice();
            $stockItem = $this->stockRegistry
                ->getStockItemBySku($currentProduct->getSku(), $currentProduct->getStore()->getWebsiteId());
            if ($stockItem) {
                $data['sq'] =  $stockItem->getQty();
            }
            $data['img'] = $imageHelper->getUrl();
            $data['cur'] = $this->getCurrencyInfo();
            $this->setProduct($data);
        }
        return $this;
    }

    /**
     * Retrieves cart information and populates cart attribute
     *
     * @return $this
     */
    public function populateCartData()
    {
        $cartQuote = $this->checkoutSession->getQuote();
  
        $data = [];
        $data['st'] = (float)$cartQuote->getSubtotal();
        $data['tt'] = (float)$cartQuote->getGrandTotal();
        $data['qt'] = (float)$cartQuote->getItemsQty();
        $data['cur'] = $this->getCurrencyInfo();
        $data['li'] = $this
            ->getCartItems($cartQuote->getAllVisibleItems(), false);
        $this->setCart($data);
          
        return $this;
    }
    
    /**
     * Retrieves user information and populates user attribute
     *
     * @return $this
     */
    public function populateUserData()
    {
        $data = [];
        $data['auth'] = $this->customerSession->isLoggedIn();
        $data['ht'] = false;
        $data['nv'] = true;
        $data['cg'] = $this->customerSession->getCustomerGroupId();
        $data['cur'] = $this->getCurrencyInfo();
        $customerId = $this->customerSession->getId();
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            if ($customer) {
                $orders = $this->getOrders($customerId);
                if ($orders) {
                    $ocnt = count($orders);
                    if ($ocnt > 0) {
                        $data['ht'] = true;
                        $data['ocnt'] = $ocnt;
                    }
                }
                if ($customer->getDob()) {
                    $dob = new DateTime($customer->getDob());
                    $data['by'] = $dob->format('Y');
                }
                if ($customer->getGender()) {
                    $data['gndr'] = $customer->getGender();
                }
                $data['id'] = $customer->getId();
                $data['nv'] = false;
                $data['since'] = $customer->getCreatedAt();
            }
        }
        $this->setUser($data);
        
        return $this;
    }

    /**
     * Retrieves order information and populates tr attribute
     *
     * @return $this
     */
    public function populateOrderData()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        
        if ($order) {
            if ($order->getIncrementId()) {
                $transaction['id'] = $order->getIncrementId();
            }
            if ($order->getSubtotal()) {
                $transaction['st'] = (float)$order->getSubtotal();
            }
            if ($order->getTaxAmount()) {
                $transaction['tx'] = (float)$order->getTaxAmount();
            }
            if ($order->getPayment()->getMethodInstance()->getTitle()) {
                $transaction['type'] = $order->getPayment()->getMethodInstance()->getTitle();
            }
            $ccType = $order->getPayment()->getCcType();
            if ($ccType) {
                $cardTypes = $this->ccConfig->getCcAvailableTypes();
                if (array_key_exists($ccType, $cardTypes)) {
                    $cardName = $cardTypes[$ccType];
                    if ($cardName) {
                         $transaction['ccType'] = $cardName;
                    }
                } else {
                    $transaction['ccType'] = $ccType;
                }
            }

            if ($order->getGrandTotal()) {
                $transaction['tt'] = (float)$order->getGrandTotal();
            }
            if ($order->getTotalQtyOrdered()) {
                $transaction['qt'] = (float)$order->getTotalQtyOrdered();
            }
            if ($order->getCouponCode()) {
                $transaction['coup'] = [$order->getCouponCode()];
            }
            if ($order->getDiscountAmount() > 0) {
                $transaction['ds'] = -1 * $order->getDiscountAmount();
            }
            $transaction['cur'] = $this->getCurrencyInfo();
            $transaction['li'] = $this
                ->getCartItems($order->getAllVisibleItems(), true);
            $transaction['sh'] = (float)$order->getShippingAmount();
            $transaction['shm'] = $order->getShippingMethod()
                ? $order->getShippingMethod() : '';
            $this->setTr($transaction);
        }
        return $this;
    }
}
