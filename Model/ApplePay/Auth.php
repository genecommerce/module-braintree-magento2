<?php

namespace Magento\Braintree\Model\ApplePay;

use Magento\Braintree\Api\AuthInterface;
use Magento\Braintree\Api\Data\AuthDataInterface;
use Magento\Braintree\Api\Data\AuthDataInterfaceFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Auth
 * @package Magento\Braintree\Model\ApplePay
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Auth implements AuthInterface
{
    /**
     * @var AuthDataInterfaceFactory
     */
    private $authData;

    /**
     * @var Ui\ConfigProvider
     */
    private $configProvider;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Auth constructor.
     * @param AuthDataInterfaceFactory $authData
     * @param Ui\ConfigProvider $configProvider
     * @param UrlInterface $url
     * @param CustomerSession $customerSession
     * @param StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        AuthDataInterfaceFactory $authData,
        Ui\ConfigProvider $configProvider,
        UrlInterface $url,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->authData = $authData;
        $this->configProvider = $configProvider;
        $this->url = $url;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManagerInterface;
    }

    /**
     * @inheritdoc
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function get(): AuthDataInterface
    {
        /** @var $data AuthDataInterface */
        $data = $this->authData->create();
        $data->setClientToken($this->getClientToken());
        $data->setDisplayName($this->getDisplayName());
        $data->setActionSuccess($this->getActionSuccess());
        $data->setIsLoggedIn($this->isLoggedIn());
        $data->setStoreCode($this->getStoreCode());

        return $data;
    }

    /**
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function getClientToken(): string
    {
        return $this->configProvider->getClientToken();
    }

    /**
     * @return string
     */
    protected function getDisplayName(): string
    {
        return $this->configProvider->getMerchantName();
    }

    /**
     * @return string
     */
    protected function getActionSuccess(): string
    {
        return $this->url->getUrl('checkout/onepage/success', ['_secure' => true]);
    }

    /**
     * @return bool
     */
    protected function isLoggedIn(): bool
    {
        return (bool) $this->customerSession->isLoggedIn();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getStoreCode(): string
    {
        return $this->storeManager->getStore()->getCode();
    }
}
