<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Paypal;

use Magento\Braintree\Gateway\Config\PayPalCredit\Config;
use Magento\Braintree\Model\Paypal\CreditApi;
use Magento\Framework\App\Cache\Type\Config as Cache;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Adapter\Curl;

class CreditApiTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Curl|\PHPUnit\Framework\MockObject\MockObject
     */
    private $curl;

    protected function setUp(): void
    {
        $this->curl = $this->createMock(Curl::class);
    }

    /**
     * The token request must be sent as a POST to the PayPal OAuth endpoint.
     */
    public function testGetAuthorizationSendsPostRequest()
    {
        $cache = $this->createStub(Cache::class);
        $cache->method('test')->willReturn(false);
        $config = $this->createStub(Config::class);
        $config->method('isSandbox')->willReturn(true);
        $config->method('getClientId')->willReturn('test-client');
        $config->method('getSecret')->willReturn('test-secret');

        $this->curl->expects(self::once())
            ->method('write')
            ->with(
                'POST',
                'https://api.sandbox.paypal.com/v1/oauth2/token',
                '1.1',
                ['Accept: application/json'],
                'grant_type=client_credentials'
            );
        $this->curl->method('read')
            ->willReturn(json_encode(
                ['token_type' => 'Bearer', 'access_token' => 'test-token', 'expires_in' => 3600]
            ));

        $creditApi = new CreditApi($this->curl, $this->createStub(ScopeConfigInterface::class), $cache, $config);

        self::assertEquals('Bearer test-token', $creditApi->getAuthorization());
    }

    /**
     * The financing options request must be sent as a POST to the calculated-financing-options endpoint.
     */
    public function testGetPriceOptionsSendsPostRequest()
    {
        $cache = $this->createStub(Cache::class);
        $cache->method('test')->willReturn(true);
        $cache->method('load')->willReturn('Bearer test-token');
        $config = $this->createStub(Config::class);
        $config->method('isSandbox')->willReturn(true);

        $this->curl->expects(self::once())
            ->method('write')
            ->with(
                'POST',
                'https://api.sandbox.paypal.com/v1/credit/calculated-financing-options',
                '1.1',
                self::anything(),
                self::anything()
            );
        $this->curl->method('read')
            ->willReturn(json_encode([
                'financing_options' => [
                    [
                        'qualifying_financing_options' => [
                            [
                                'credit_financing' => [
                                    'credit_type' => 'INST',
                                    'enabled' => true,
                                    'term' => 12,
                                    'apr' => 9.9
                                ],
                                'monthly_payment' => ['value' => 50.00],
                                'total_cost' => ['value' => 600.00],
                                'total_interest' => ['value' => 100.00]
                            ]
                        ]
                    ]
                ]
            ]));

        $creditApi = new CreditApi($this->curl, $this->createStub(ScopeConfigInterface::class), $cache, $config);

        $options = $creditApi->getPriceOptions(600.00);

        self::assertCount(1, $options);
        self::assertSame(12, $options[0]['term']);
        self::assertEquals(50.0, $options[0]['monthly_payment']);
    }
}
