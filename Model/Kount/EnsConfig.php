<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Kount;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class EnsConfig
 */
class EnsConfig
{
    const CONFIG_ALLOWED_IPS = 'payment/braintree/kount_allowed_ips';
    const CONFIG_ENVIRONMENT = 'payment/braintree/kount_environment';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
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
}
