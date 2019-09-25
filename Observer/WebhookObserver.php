<?php
declare(strict_types=1);

namespace Magento\Braintree\Observer;

use Braintree\WebhookNotification;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class WebhookObserver
 */
class WebhookObserver implements ObserverInterface
{
    const WEBHOOK_KIND = [
        WebhookNotification::LOCAL_PAYMENT_COMPLETED => 'localPaymentComplete'
    ];

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $webhookData = $observer->getData('eventData');

        if (array_key_exists($webhookData->kind, self::WEBHOOK_KIND)) {
            // Turn underscore string to camelCased
            $kind = preg_replace_callback(
                '/_(.?)/',
                static function ($matches) {
                    return strtoupper($matches[1]);
                },
                $webhookData->kind
            );

            $this->$kind($webhookData);
        }
    }

    /**
     * @param $data
     * @return mixed
     */
    public function localPaymentCompleted($data)
    {
        return $data;
    }
}
