<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Ui\PayPal;

use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Ui\PayPal\ConfigProvider;
use Magento\Framework\Locale\ResolverInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Braintree\Gateway\Config\PayPalCredit\Config as CreditConfig;

/**
 * Class ConfigProviderTest
 *
 * Test for class \Magento\Braintree\Model\Ui\PayPal\ConfigProvider
 */
class ConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolver;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CreditConfig|MockObject
     */
    private $creditConfig;

    protected function setUp()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->creditConfig = $this->getMockBuilder(CreditConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeResolver = $this->getMockForAbstractClass(ResolverInterface::class);

        $this->configProvider = new ConfigProvider(
            $this->config,
            $this->creditConfig,
            $this->localeResolver
        );
    }

    /**
     * Run test getConfig method
     *
     * @param array $expected
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig($expected)
    {
        $this->config->method('isActive')
            ->willReturn(true);

        $this->config->method('isAllowToEditShippingAddress')
            ->willReturn(true);

        $this->config->method('getMerchantName')
            ->willReturn('Test');

        $this->config->method('getTitle')
            ->willReturn('Payment Title');

        $this->localeResolver->method('getLocale')
            ->willReturn('en_US');

        $this->config->method('getPayPalIcon')
            ->willReturn([
                'width' => 30, 'height' => 26, 'url' => 'https://icon.test.url'
            ]);

        self::assertEquals($expected, $this->configProvider->getConfig());
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            [
                'expected' => [
                    'payment' => [
                        ConfigProvider::PAYPAL_CODE => [
                            'isActive' => true,
                            'title' => 'Payment Title',
                            'isAllowShippingAddressOverride' => true,
                            'merchantName' => 'Test',
                            'payeeEmail' => null,
                            'locale' => 'en_US',
                            'paymentAcceptanceMarkSrc' =>
                                'https://www.paypalobjects.com/webstatic/en_US/i/buttons/pp-acceptance-medium.png',
                            'vaultCode' => ConfigProvider::PAYPAL_VAULT_CODE,
                            'paymentIcon' => [
                                'width' => 30, 'height' => 26, 'url' => 'https://icon.test.url'
                            ],
                            'style' => [
                                'shape' => null,
                                'size' => null,
                                'color' => null
                            ]
                        ],

                        ConfigProvider::PAYPAL_CREDIT_CODE => [
                            'isActive' => null,
                            'title' => __('PayPal Credit'),
                            'isAllowShippingAddressOverride' => true,
                            'merchantName' => 'Test',
                            'payeeEmail' => null,
                            'locale' => 'en_US',
                            'paymentAcceptanceMarkSrc' =>
                                'https://www.paypalobjects.com/webstatic/en_US/i/buttons/ppc-acceptance-medium.png',
                            'paymentIcon' => [
                                'width' => 30, 'height' => 26, 'url' => 'https://icon.test.url'
                            ],
                            'style' => [
                                'shape' => null,
                                'size' => null,
                                'color' => null
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
