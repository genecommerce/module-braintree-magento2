<?php

namespace Magento\Braintree\Block\Credit\Calculator\Product;

use Magento\Framework\View\Element\Template;
use Magento\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;

/**
 * Class View
 * @package Magento\Braintree\Block\Credit\Calculator\Product
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class View extends Template
{
    /**
     * @var \Magento\Braintree\Api\CreditPriceRepositoryInterface
     */
    protected $creditPriceRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var PayPalCreditConfig
     */
    protected $config;

    /**
     * View constructor.
     * @param Template\Context $context
     * @param PayPalCreditConfig $config
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Braintree\Api\CreditPriceRepositoryInterface $creditPriceRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PayPalCreditConfig $config,
        \Magento\Framework\Registry $registry,
        \Magento\Braintree\Api\CreditPriceRepositoryInterface $creditPriceRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->creditPriceRepository = $creditPriceRepository;
        $this->coreRegistry = $registry;
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
     * Retrieve current product model
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * @return string|bool
     */
    public function getPriceData()
    {
        if ($this->getProduct()) {
            $results = $this->creditPriceRepository->getByProductId($this->getProduct()->getId());
            if (!empty($results)) {
                $options = [];
                foreach ($results as $option) {
                    $options[] = [
                        'term' => $option->getTerm(),
                        'monthlyPayment' => $option->getMonthlyPayment(),
                        'apr' => $option->getInstalmentRate(),
                        'cost' => $option->getCostOfPurchase(),
                        'costIncInterest' => $option->getTotalIncInterest()
                    ];
                }

                return json_encode($options);
            }
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
