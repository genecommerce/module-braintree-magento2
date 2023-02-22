<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\Request\ThreeDSecureDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\Order\AddressAdapter;
use Magento\Braintree\Gateway\Helper\SubjectReader;

/**
 * Class ThreeDSecureDataBuilderTest
 */
class ThreeDSecureDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ThreeDSecureDataBuilder
     */
    private $builder;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var PaymentDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDO;

    /**
     * @var OrderAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $order;

    /**
     * @var \Magento\Payment\Gateway\Data\Order\AddressAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $billingAddress;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReaderMock;

    protected function setUp(): void
    {
        $this->initOrderMock();

        $this->paymentDO = $this->getMockBuilder(PaymentDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder', 'getPayment'])
            ->getMock();
        $this->paymentDO->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->order);

        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isVerify3DSecure', 'is3DSAlwaysRequested', 'getThresholdAmount', 'get3DSecureSpecificCountries'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new ThreeDSecureDataBuilder($this->configMock, $this->subjectReaderMock);
    }

    /**
     * @param bool $verify
     * @param bool $challengeRequested
     * @param float $thresholdAmount
     * @param string $countryId
     * @param array $countries
     * @param array $expected
     * @covers \Magento\Braintree\Gateway\Request\ThreeDSecureDataBuilder::build
     * @dataProvider buildDataProvider
     */
    public function testBuild($verify, $challengeRequested, $thresholdAmount, $countryId, array $countries, array $expected)
    {
        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => 25
        ];

        $this->configMock->expects(static::once())
            ->method('isVerify3DSecure')
            ->willReturn($verify);

        $this->configMock->expects(static::once())
            ->method('is3DSAlwaysRequested')
            ->willReturn($challengeRequested);

        $this->configMock->expects(static::any())
            ->method('getThresholdAmount')
            ->willReturn($thresholdAmount);

        $this->configMock->expects(static::any())
            ->method('get3DSecureSpecificCountries')
            ->willReturn($countries);

        $this->billingAddress->expects(static::any())
            ->method('getCountryId')
            ->willReturn($countryId);

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDO);
        $this->subjectReaderMock->expects(self::once())
            ->method('readAmount')
            ->with($buildSubject)
            ->willReturn(25);

        $result = $this->builder->build($buildSubject);
        static::assertEquals($expected, $result);
    }

    /**
     * Get list of variations for build test
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            ['verify' => true, 'challengeRequested' => true, 'amount' => 20, 'countryId' => 'US', 'countries' => [], 'result' => [
                'options' => [
                    'threeDSecure' => [
                        'required' => true
                    ]
                ]
            ]],
            ['verify' => true, 'challengeRequested' => true, 'amount' => 0, 'countryId' => 'US', 'countries' => ['US', 'GB'], 'result' => [
                'options' => [
                    'threeDSecure' => [
                        'required' => true
                    ]
                ]
            ]],
            ['verify' => true, 'challengeRequested' => true, 'amount' => 40, 'countryId' => 'US', 'countries' => [], 'result' => []],
            ['verify' => false, 'challengeRequested' => false, 'amount' => 40, 'countryId' => 'US', 'countries' => [], 'result' => []],
            ['verify' => false, 'challengeRequested' => false, 'amount' => 20, 'countryId' => 'US', 'countries' => [], 'result' => []],
            ['verify' => true, 'challengeRequested' => true, 'amount' => 20, 'countryId' => 'CA', 'countries' => ['US', 'GB'], 'result' => []],
        ];
    }

    /**
     * Create mock object for order adapter
     */
    private function initOrderMock()
    {
        $this->billingAddress = $this->getMockBuilder(AddressAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCountryId'])
            ->getMock();

        $this->order = $this->getMockBuilder(OrderAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBillingAddress'])
            ->getMock();

        $this->order->expects(static::any())
            ->method('getBillingAddress')
            ->willReturn($this->billingAddress);
    }
}
