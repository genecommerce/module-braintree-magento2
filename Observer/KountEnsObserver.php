<?php
declare(strict_types=1);

namespace Magento\Braintree\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class KountEnsObserver
 */
class KountEnsObserver implements ObserverInterface
{

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getData('event');
    }
}
