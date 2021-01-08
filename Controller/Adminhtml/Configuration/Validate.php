<?php

namespace Magento\Braintree\Controller\Adminhtml\Configuration;

use Braintree\Configuration;
use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Framework\Controller\ResultInterface;
use Magento\Braintree\Model\Adminhtml\Source\Environment;

/**
 * Class Validate
 * @package Magento\Braintree\Controller\Adminhtml\Payment
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Validate extends Action
{
    const ADMIN_RESOURCE = 'Magento_Config::config';

    /**
     * @var Config
     */
    protected $config;

    /**
     * Validate constructor.
     * @param Action\Context $context
     * @param Config $config
     */
    public function __construct(
        Action\Context $context,
        Config $config
    ) {
        parent::__construct($context);
        $this->config = $config;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $publicKey = $this->getRequest()->getParam('public_key');
        $privateKey = $this->getRequest()->getParam('private_key');
        $storeId = $this->getRequest()->getParam('storeId', 0);
        $environment = $this->getRequest()->getParam('environment');

        if (false !== strpos($publicKey, '*')) {
            if ($environment === Environment::ENVIRONMENT_SANDBOX) {
                $publicKey = $this->config->getValue(Config::KEY_SANDBOX_PUBLIC_KEY, $storeId);
            } else {
                $publicKey = $this->config->getValue(Config::KEY_PUBLIC_KEY, $storeId);
            }
        }

        if (false !== strpos($privateKey, '*')) {
            if ($environment === Environment::ENVIRONMENT_SANDBOX) {
                $privateKey = $this->config->getValue(Config::KEY_SANDBOX_PRIVATE_KEY, $storeId);
            } else {
                $privateKey = $this->config->getValue(Config::KEY_PRIVATE_KEY, $storeId);
            }
        }

        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            Configuration::environment($environment);
            Configuration::merchantId($this->getRequest()->getParam('merchant_id'));
            Configuration::publicKey($publicKey);
            Configuration::privateKey($privateKey);

            Configuration::gateway()->plan()->all();

            $response->setHttpResponseCode(200);
        } catch (Exception $e) {
            $response->setHttpResponseCode(400);
        }

        return $response;
    }
}
