<?php

namespace Magento\Braintree\Plugin;

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
     */
    public function beforeLoad(Collection $subject)
    {
        if (!$subject->isLoaded()) {
            $primaryKey = $subject->getResource()->getIdFieldName();

            $subject->getSelect()->joinLeft(
                'braintree_transaction_details',
                'braintree_transaction_details.order_id = main_table.' . $primaryKey,
                'braintree_transaction_details.transaction_source'
            );
        }

        return null;
    }
}
