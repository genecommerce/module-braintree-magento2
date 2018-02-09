<?php

namespace Magento\Braintree\Model\ResourceModel\CreditPrice;

/**
 * Class Collection
 * @package Gene\Log\Model\ResourceModel\ChangeLog
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id'; //@codingStandardsIgnoreLine

    protected function _construct() //@codingStandardsIgnoreLine
    {
        $this->_init('Magento\Braintree\Model\CreditPrice', 'Magento\Braintree\Model\ResourceModel\CreditPrice');
    }
}
