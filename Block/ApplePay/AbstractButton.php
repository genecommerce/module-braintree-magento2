<?php

namespace Magento\Braintree\Block\ApplePay;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\MethodInterface;

/**
 * Class AbstractButton
 * @package Magento\Braintree\Model\ApplePay
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
     * @var \Magento\Braintree\Model\ApplePay\Auth
     */
    protected $auth;

    /**
     * Button constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param MethodInterface $payment
     * @param \Magento\Braintree\Model\ApplePay\Auth $auth
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        MethodInterface $payment,
        \Magento\Braintree\Model\ApplePay\Auth $auth,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->payment = $payment;
        $this->auth = $auth->get();
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
    public function getMerchantName()
    {
        return $this->auth->getDisplayName();
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
        return $this->auth->getActionSuccess();
    }

    /**
     * Is customer logged in flag
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->auth->getIsLoggedIn();
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
     * @return float
     */
    public function getStorecode()
    {
        return $this->auth->getStoreCode();
    }
}
