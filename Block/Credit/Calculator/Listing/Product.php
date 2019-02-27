<?php

namespace Magento\Braintree\Block\Credit\Calculator\Listing;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\View\Element\Template;
use Magento\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;

/**
 * Class Product
 * @package Magento\Braintree\Block\Credit\Calculator\Listing
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Product extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Braintree::credit/product/listing.phtml'; // @codingStandardsIgnoreLine

    /**
     * @var \Magento\Braintree\Api\CreditPriceRepositoryInterface
     */
    protected $creditPriceRepository;

    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var PayPalCreditConfig
     */
    protected $config;

    /**
     * Product constructor.
     * @param Template\Context $context
     * @param PayPalCreditConfig $config
     * @param \Magento\Braintree\Api\CreditPriceRepositoryInterface $creditPriceRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PayPalCreditConfig $config,
        \Magento\Braintree\Api\CreditPriceRepositoryInterface $creditPriceRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->creditPriceRepository = $creditPriceRepository;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml() // @codingStandardsIgnoreLine
    {
        if ($this->config->isCalculatorEnabled()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @param $product
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;
    }

    /**
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return \Magento\Braintree\Api\Data\CreditPriceDataInterface|bool
     */
    public function getPriceData()
    {
        $productId = $this->getProduct()->getId();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();

        $data = $this->creditPriceRepository->getCheapestByProductId($productId, $websiteId);
        if ($data->getId()) {
            return $data;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getMerchantName()
    {
        return $this->config->getMerchantName();
    }
}
