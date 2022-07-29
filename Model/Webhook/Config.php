<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Braintree\Model\Webhook;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Braintree\Gateway\Helper\SubjectReader;

class Config
{
    private CONST WEBHOOK_ENABLED = 'payment/braintree_webhook/enabled';
    private CONST WEBHOOK_FRAUD_PROTECTION_URL = 'payment/braintree_webhook/fraud_protection_url';
    private CONST WEBHOOK_APPROVE_ORDER_STATUS = 'payment/braintree_webhook/approve_order_status';
    private CONST WEBHOOK_REJECT_ORDER_STATUS = 'payment/braintree_webhook/reject_order_status';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor
     *
     * @param SubjectReader $subjectReader
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SubjectReader $subjectReader,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->subjectReader = $subjectReader;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is webhook enabled
     *
     * @param int|null $storeId
     * @return mixed
     */
    public function isEnabled(int $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::WEBHOOK_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Fraud Protection URL
     *
     * @param int|null $storeId
     * @return mixed
     */
    public function getFraudProtectionUrl(int $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::WEBHOOK_FRAUD_PROTECTION_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get fraud protection approve order status
     *
     * @param int|null $storeId
     * @return mixed
     */
    public function getFraudApproveOrderStatus(int $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::WEBHOOK_APPROVE_ORDER_STATUS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get fraud protection reject order status
     *
     * @param int|null $storeId
     * @return mixed
     */
    public function getFraudRejectOrderStatus(int $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::WEBHOOK_REJECT_ORDER_STATUS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
