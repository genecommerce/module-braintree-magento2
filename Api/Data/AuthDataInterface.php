<?php

namespace Magento\Braintree\Api\Data;

/**
 * Interface AuthDataInterface
 * @api
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
interface AuthDataInterface
{
    /**
     * Braintree client token
     * @return string
     */
    public function getClientToken(): string;

    /**
     * Merchant display name
     * @return string
     */
    public function getDisplayName(): string;

    /**
     * URL To success page
     * @return string
     */
    public function getActionSuccess(): string;

    /**
     * @return bool
     */
    public function isLoggedIn(): bool;

    /**
     * Get current store code
     * @return string
     */
    public function getStoreCode(): string;

    /**
     * Set Braintree client token
     * @var $value string
     * @return string|null
     */
    public function setClientToken($value);

    /**
     * Set Merchant display name
     * @var $value string
     * @return string|null
     */
    public function setDisplayName($value);

    /**
     * Set URL To success page
     * @var $value string
     * @return string|null
     */
    public function setActionSuccess($value);

    /**
     * Set if user is logged in
     * @var $value bool
     * @return bool|null
     */
    public function setIsLoggedIn($value);

    /**
     * Set current store code
     * @var $value string
     * @return string|null
     */
    public function setStoreCode($value);
}
