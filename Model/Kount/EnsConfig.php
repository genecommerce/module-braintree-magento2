<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Kount;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class EnsConfig
 */
class EnsConfig
{
    const CONFIG_KOUNT_ID = 'payment/braintree/kount_id';
    const CONFIG_ALLOWED_IPS = 'payment/braintree/kount_allowed_ips';
    const CONFIG_ENVIRONMENT = 'payment/braintree/kount_environment';

    const RESPONSE_DECLINE = 'D';
    const RESPONSE_APPROVE = 'A';
    const RESPONSE_REVIEW = 'R';
    const RESPONSE_ESCALATE = 'E';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param OrderInterface $order
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        OrderInterface $order
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->order = $order;
    }

    /**
     * @return array
     */
    public function getAllowedIps(): array
    {
        $ips = $this->scopeConfig->getValue(self::CONFIG_ALLOWED_IPS);
        return $ips ? explode(',', $ips) : [];
    }

    /**
     * @param $ip
     * @param $range
     * @return bool
     */
    private function isIpInRange($ip, $range): bool
    {
        if (strpos($range, '/') === false) {
            $range .= '/255';
        }
        // $range is in IP/CIDR format eg 127.0.0.1/255
        list($range, $netmask) = explode('/', $range, 2);

        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = (2 ** (32 - $netmask)) - 1;
        $netmask_decimal = ~ $wildcard_decimal;

        return (($ip_decimal & $netmask_decimal) === ($range_decimal & $netmask_decimal));
    }

    /**
     * @param $remoteAddress
     * @return bool
     */
    public function isAllowed($remoteAddress): bool
    {
        $allowedIps = $this->getAllowedIps();

        if (!$allowedIps) {
            return true;
        }

        foreach ($allowedIps as $allowedIp) {
            if ($this->isIpInRange($remoteAddress, $allowedIp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->scopeConfig->getValue(self::CONFIG_ENVIRONMENT) === 'sandbox';
    }

    /**
     * @param $merchantId
     * @return bool
     */
    public function validateMerchantId($merchantId): bool
    {
        $stores = $this->storeManager->getStores();

        foreach ($stores as $store) {
            $storeMerchantId = $this->scopeConfig->getValue(
                self::CONFIG_KOUNT_ID,
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            );

            return ((int) $storeMerchantId === $merchantId);
        }

        return false;
    }

    /**
     * @param $event
     * @return bool
     */
    public function processEvent($event): bool
    {
        if ((string) $event->name === 'WORKFLOW_STATUS_EDIT') {
            return $this->workflowStatusEdit($event);
        }

        return false;
    }

    /**
     * @param $event
     * @return bool
     */
    public function workflowStatusEdit($event): bool
    {
        $incrementId = $this->getIncrementId($event);
        $kountTransactionId = $this->getKountTransactionId($event);

        if ($incrementId && $kountTransactionId) {
            /** @var Order $order */
            $order = $this->order->loadByIncrementId($incrementId);

            if ($order) {
                $payment = $order->getPayment();
                $paymentKountId = $payment->getAdditionalInformation('riskDataId');

                if ($kountTransactionId === $paymentKountId) {
                    if ((string) $event->old_value === self::RESPONSE_REVIEW ||
                        (string) $event->old_value === self::RESPONSE_ESCALATE
                    ) {
                        if ((string) $event->new_value === self::RESPONSE_APPROVE) {
                            return $this->approveOrder($order);
                        }

                        if ((string) $event->new_value === self::RESPONSE_DECLINE) {
                            return $this->declineOrder($order);
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param $event
     * @return int|null
     */
    public function getIncrementId($event)
    {
        if (isset($event->key['order_number'])) {
            return (int) $event->key['order_number'];
        }

        return null;
    }

    /**
     * @param $event
     * @return string|null
     */
    public function getKountTransactionId($event)
    {
        if (isset($event->key)) {
            return (string) $event->key;
        }

        return null;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    private function approveOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        if ($order->getStatus() === Order::STATE_PAYMENT_REVIEW) {
            $invoices = $order->getInvoiceCollection();
        }

        return false;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    private function declineOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        return false;
    }
}
