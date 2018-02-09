<?php

namespace Magento\Braintree\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class TransactionDetail
 * @package Magento\Braintree\Model\ResourceModel
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class TransactionDetail extends AbstractDb
{
    /**
     * Model Initialization
     * @return void
     */
    protected function _construct() // @codingStandardsIgnoreLine
    {
        $this->_init('braintree_transaction_details', 'entity_id');
    }
}
