<?php

namespace Magento\Braintree\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

/**
 * Class SalesOrderGridPlugin
 * @package Magento\Braintree\Plugin
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class SalesOrderGridPlugin
{
    /**
     * @param Collection $subject
     * @return null
     * @throws LocalizedException
     */
    public function beforeLoad(Collection $subject)
    {
        if (!$subject->isLoaded()) {
            $primaryKey = $subject->getResource()->getIdFieldName();
            $tableName = $subject->getResource()->getTable('braintree_transaction_details');

            $subject->getSelect()->joinLeft(
                $tableName,
                $tableName . '.order_id = main_table.' . $primaryKey,
                $tableName . '.transaction_source'
            );
        }

        return null;
    }
}
