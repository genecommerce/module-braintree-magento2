<?php

namespace Magento\Braintree\Gateway\Request;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Braintree\Gateway\Config\Config;
use Psr\Log\LoggerInterface;

/**
 * Class CvvDataBuilder
 * @package Magento\Braintree\Gateway\Request
 */
class CvvDataBuilder implements BuilderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CvvDataBuilder constructor.
     * @param RequestInterface $request
     * @param Config $config
     * @param DriverInterface $driver
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        Config $config,
        DriverInterface $driver,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->config = $config;
        $this->driver = $driver;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        if (!$this->request->isSecure() || !$this->config->isCvvEnabledVault()) {
            return [];
        }

        try {
            $input = $this->driver->fileGetContents('php://input');
            if ($input) {
                $input = json_decode($input, true);
                if (!empty($input['paymentMethod']['additional_data']['cvv'])) {
                    return [
                        'creditCard' => [
                            'cvv' => $input['paymentMethod']['additional_data']['cvv']
                        ]
                    ];
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return [];
    }
}
