<?php
declare(strict_types=1);

namespace Magento\Braintree\Model;

use Braintree\Exception\InvalidSignature;
use Braintree\WebhookNotification;
use Braintree\WebhookTesting;
use Magento\Braintree\Api\WebhookInterface;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\ManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Webhook
 */
class Webhook implements WebhookInterface
{
    /**
     * @var BraintreeAdapter $adapter
     */
    protected $adapter;
    /**
     * @var LoggerInterface $logger
     */
    protected $logger;
    /**
     * @var WebhookNotification $webhookNotification
     */
    protected $webhookNotification;
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * Webhook constructor.
     *
     * @param BraintreeAdapter $adapter
     * @param LoggerInterface $logger
     */
    public function __construct(
        BraintreeAdapter $adapter,
        ManagerInterface $eventManager,
        LoggerInterface $logger
    ) {
        $this->adapter = $adapter;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
    }

    /**
     * @param string $signature
     * @param string $payload
     * @return void
     * @throws InvalidSignature
     */
    public function getData(string $signature, string $payload)
    {
        $sampleNotification = WebhookTesting::sampleNotification(
            WebhookNotification::LOCAL_PAYMENT_COMPLETED,
            'my_id'
        );

        $this->webhookNotification = $this->adapter->webhookNotification(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

//        $this->webhookNotification = $this->adapter->webhookNotification($signature, $payload);

        if ($this->webhookNotification->kind) {
            // dispatch event
            $this->eventManager->dispatch('braintree_webhook_handler', ['eventData' => $this->webhookNotification]);
        }
    }
}
