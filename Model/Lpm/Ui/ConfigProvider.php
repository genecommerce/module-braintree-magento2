<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Lpm\Ui;

use Magento\Braintree\Model\Lpm\Config;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'braintree_local_payment';
    /**
     * @var Config
     */
    private $config;

    /**
     * ConfigProvider constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::METHOD_CODE => [
                    'isActive' => $this->isActive(),
                    'title' => $this->config->getTitle()
                ]
            ]
        ];
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return true;
    }
}
