<?php
declare(strict_types=1);

namespace Magento\Braintree\Api;

/**
 * Interface WebhookInterface
 */
interface WebhookInterface
{
    /**
     * @param string $btSignature
     * @param string $btPayload
     * @return mixed
     */
    public function getData(string $btSignature, string $btPayload);
}