<?php

namespace Magento\Braintree\Model\ResourceModel\CreditPrice;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Braintree\Model\CreditPrice;
use Magento\Braintree\Model\ResourceModel\CreditPrice as CreditPriceResource;

/**
 * Class Collection
 * @package Gene\Log\Model\ResourceModel\ChangeLog
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id'; //@codingStandardsIgnoreLine

    protected function _construct() //@codingStandardsIgnoreLine
    {
        $this->_init(CreditPrice::class, CreditPriceResource::class);
    }
}
