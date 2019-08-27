<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Venmo\Ui;

use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Venmo\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository;

/**
 * Class ConfigProvider
 *
 * @package Magento\Braintree\Model\Venmo\Ui
 * @author Paul Canning <paul.canning@gene.co.uk>
 */
class ConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'braintree_venmo';
    /**
     * @var Config
     */
    private $config;
    /**
     * @var BraintreeAdapter
     */
    private $adapter;
    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * ConfigProvider constructor.
     *
     * @param Config $config
     * @param BraintreeAdapter $adapter
     */
    public function __construct(Config $config, BraintreeAdapter $adapter, Repository $assetRepo)
    {
        $this->config = $config;
        $this->adapter = $adapter;
        $this->assetRepo = $assetRepo;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::METHOD_CODE => [
                    'environment' => $this->getEnvironment(),
                    'paymentMarkSrc' => $this->getPaymentMarkSrc()
                ]
            ]
        ];
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getEnvironment(): string
    {
        return $this->config->getEnvironment();
    }

    /**
     * @return mixed
     */
    public function getPaymentMarkSrc()
    {
        return $this->assetRepo->getUrl('Magento_Braintree::images/venmo_logo_blue.png');
    }
}
