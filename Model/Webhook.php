<?php
declare(strict_types=1);

namespace Magento\Braintree\Model;

use Braintree\Exception\InvalidSignature;
use Braintree\WebhookNotification;
use Braintree\WebhookTesting;
use Magento\Braintree\Api\WebhookInterface;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
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
     * Webhook constructor.
     *
     * @param BraintreeAdapter $adapter
     * @param LoggerInterface $logger
     */
    public function __construct(BraintreeAdapter $adapter, LoggerInterface $logger)
    {
        $this->adapter = $adapter;
        $this->logger = $logger;
    }

    /**
     * @param string $signature
     * @param string $payload
     * @return mixed|string|void
     */
    public function getData(string $signature, string $payload)
    {
        try {
            $this->webhookNotification = $this->adapter->webhookNotification($signature, $payload);
        } catch (InvalidSignature $e) {
            $this->logger->error($e->getMessage());
            return;
        }
        return \GuzzleHttp\json_encode($this->webhookNotification);
    }
}
