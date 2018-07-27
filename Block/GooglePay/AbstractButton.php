<?php

namespace Magento\Braintree\Block\GooglePay;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\MethodInterface;

/**
 * Class AbstractButton
 * @package Magento\Braintree\Model\GooglePay
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
abstract class AbstractButton extends Template
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var MethodInterface
     */
    protected $payment;

    /**
     * @var \Magento\Braintree\Model\GooglePay\Auth
     */
    protected $auth;

    /**
     * Button constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param MethodInterface $payment
     * @param \Magento\Braintree\Model\GooglePay\Auth $auth
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        MethodInterface $payment,
        \Magento\Braintree\Model\GooglePay\Auth $auth,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->payment = $payment;
        $this->auth = $auth;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml() // @codingStandardsIgnoreLine
    {
        if ($this->isActive()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->payment->isAvailable($this->checkoutSession->getQuote());
    }

    /**
     * Merchant name to display in popup
     * @return string
     */
    public function getMerchantId()
    {
        return $this->auth->getMerchantId();
    }

    /**
     * Get environment code
     * @return string
     */
    public function getEnvironment()
    {
        return $this->auth->getEnvironment();
    }

    /**
     * Braintree's API token
     * @return string|null
     */
    public function getClientToken()
    {
        return $this->auth->getClientToken();
    }

    /**
     * URL To success page
     * @return string
     */
    public function getActionSuccess()
    {
        return $this->getUrl('braintree/googlepay/review', ['_secure' => true]);
    }

    /**
     * Currency code
     * @return float
     */
    public function getCurrencyCode()
    {
        return $this->checkoutSession->getQuote()->getCurrency()->getBaseCurrencyCode();
    }

    /**
     * Cart grand total
     * @return float
     */
    public function getAmount()
    {
        return $this->checkoutSession->getQuote()->getBaseGrandTotal();
    }

    /**
     * Available card types
     * @return mixed
     */
    public function getAvailableCardTypes()
    {
        return $this->auth->getAvailableCardTypes();
    }
}
