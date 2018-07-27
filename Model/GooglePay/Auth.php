<?php

namespace Magento\Braintree\Model\GooglePay;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\UrlInterface;

/**
 * Class Auth
 * @package Magento\Braintree\Model\GooglePay
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Auth
{
    /**
     * @var \Magento\Braintree\Api\Data\AuthDataInterfaceFactory
     */
    private $authData;

    /**
     * @var Ui\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Auth constructor.
     * @param \Magento\Braintree\Api\Data\AuthDataInterfaceFactory $authData
     * @param Ui\ConfigProvider $configProvider
     * @param UrlInterface $url
     * @param CustomerSession $customerSession
     */
    public function __construct(
        \Magento\Braintree\Api\Data\AuthDataInterfaceFactory $authData,
        Ui\ConfigProvider $configProvider,
        UrlInterface $url,
        CustomerSession $customerSession
    ) {
        $this->authData = $authData;
        $this->configProvider = $configProvider;
        $this->url = $url;
        $this->customerSession = $customerSession;
    }

    public function getClientToken()
    {
        return $this->configProvider->getClientToken();
    }

    public function getEnvironment()
    {
        return $this->configProvider->getEnvironment();
    }

    public function getMerchantId()
    {
        return $this->configProvider->getMerchantId();
    }

    public function getActionSuccess()
    {
        return $this->url->getUrl('checkout/onepage/success', ['_secure' => true]);
    }

    public function getAvailableCardTypes()
    {
        return $this->configProvider->getAvailableCardTypes();
    }
}
