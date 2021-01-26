<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Braintree\Gateway\Request\VaultDataBuilder;
use Magento\Braintree\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend\Log\Filter\Mock;

/**
 * Class VaultDataBuilderTest
 * @package Magento\Braintree\Test\Unit\Gateway\Request
 */
class VaultDataBuilderTest extends TestCase
{
    /**
     * @var PaymentDataObjectInterface | MockObject
     */
    private $paymentDO;

    public function setUp()
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->builder = new VaultDataBuilder();
    }

    public function testBuild()
    {
        $expectedResult = [
            VaultDataBuilder::OPTIONS => [
                VaultDataBuilder::STORE_IN_VAULT_ON_SUCCESS => true
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDO
        ];

        static::assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }
}
