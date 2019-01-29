<?php

namespace Magento\Braintree\Controller\Paypal;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Paypal\Helper\QuoteUpdater;

/**
 * Class OneClick
 * Used by the product page to create a quote for a single product
 * @package Magento\Braintree\Controller\Paypal
 */
class OneClick extends Review
{
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    protected $quote;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * OneClick constructor.
     * @param Context $context
     * @param Config $config
     * @param Session $checkoutSession
     * @param QuoteUpdater $quoteUpdater
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        Config $config,
        Session $checkoutSession,
        QuoteUpdater $quoteUpdater,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        CustomerSession $customerSession
    ) {
        $this->productRepository = $productRepository;
        $this->quoteFactory = $quoteFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->jsonHelper = $jsonHelper;

        parent::__construct(
            $context,
            $config,
            $checkoutSession,
            $quoteUpdater
        );
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        // Convert JSON form fields to array keys & extract form_key
        $requestData = $this->jsonHelper->jsonDecode(
            $this->getRequest()->getPostValue('result', '{}')
        );

        if (!empty($requestData['additionalData'])) {
            parse_str($requestData['additionalData'], $requestData['additionalData']);
        }
        if (!empty($requestData['additionalData']['form_key'])) {
            $this->getRequest()->setParams(['form_key' => $requestData['additionalData']['form_key']]);
        }
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage("Invalid Formkey");
            return $resultRedirect->setPath($this->_redirect->getRefererUrl());
        }

        // Retrieve product form values
        if (empty($requestData['additionalData']['product'])) {
            $this->messageManager->addErrorMessage("No product specified");
            return $resultRedirect->setPath($this->_redirect->getRefererUrl());
        }

        // Create a blank quote to just purchase this one product
        $quote = $this->quoteFactory->create();

        $currentCustomer = $this->customerSession->getCustomer();
        if ($currentCustomer->getId()) {
            $quote->setCustomer($currentCustomer);
            $quote->setCustomerIsGuest(0);
        } else {
            $quote->setCustomerIsGuest(1);
        }

        /** @var $product \Magento\Quote\Api\Data\CartItemInterface */
        try {
            $product = $this->productRepository->getById(
                $requestData['additionalData']['product'],
                false,
                $this->storeManager->getStore()->getId()
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addExceptionMessage($e);
            return $resultRedirect->setPath($this->_redirect->getRefererUrl());
        }

        // Add product to quote
        $quote->setInventoryProcessed(false);
        $additionalData = new \Magento\Framework\DataObject;
        $additionalData->setData($requestData['additionalData']);
        try {
            $quote->addProduct($product, $additionalData);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
            return $resultRedirect->setPath($this->_redirect->getRefererUrl());
        }
        $quote->collectTotals();
        $quote->save($quote);
        $quote->setTotalsCollectedFlag(false);

        // Replace the user's current cart with this one to ensure the place order actions work correctly
        $this->checkoutSession->setBraintreeOneClickQuoteId($quote->getId());
        $this->checkoutSession->replaceQuote($quote);

        return parent::execute();
    }

    /**
     * Return this controller's quote instance
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    protected function getQuote()
    {
        if ($this->quote) {
            return $this->quote;
        }
        return parent::getQuote();
    }
}
