<?php

namespace Magento\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class TransactionSourceDataBuilder
 * @package Magento\Braintree\Gateway\Request
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class TransactionSourceDataBuilder implements BuilderInterface
{
    const TRANSACTION_SOURCE = 'transactionSource';

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * TransactionSourceDataBuilder constructor.
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\App\State $state
    ) {
        $this->state = $state;
    }

    /**
     * Set TRANSACTION_SOURCE to moto if within the admin
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        if ($this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            return [
                self::TRANSACTION_SOURCE => 'moto'
            ];
        }

        return [];
    }
}
