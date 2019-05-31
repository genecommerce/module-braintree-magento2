<?php

namespace Magento\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Braintree\Model\CustomFields\Pool;

/**
 * Class CustomFieldsDataBuilder
 * @package Magento\Braintree\Gateway\Request
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class CustomFieldsDataBuilder implements BuilderInterface
{
    const CUSTOM_FIELDS = 'customFields';

    /**
     * @var Pool $pool
     */
    protected $pool;

    /**
     * CustomFieldsDataBuilder constructor
     *
     * @param Pool $pool
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        return [
            self::CUSTOM_FIELDS => $this->pool->getFields($buildSubject)
        ];
    }
}
