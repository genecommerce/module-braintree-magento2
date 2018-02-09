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
    public function getClientToken();

    /**
     * Merchant display name
     * @return string
     */
    public function getDisplayName();

    /**
     * URL To success page
     * @return string
     */
    public function getActionSuccess();

    /**
     * @return boolean
     */
    public function getIsLoggedIn();

    /**
     * Get current store code
     * @return string
     */
    public function getStoreCode();

    /**
     * Set Braintree client token
     * @var $value string
     * @return null
     */
    public function setClientToken($value);

    /**
     * Set Merchant display name
     * @var $value string
     * @return null
     */
    public function setDisplayName($value);

    /**
     * Set URL To success page
     * @var $value string
     * @return string
     */
    public function setActionSuccess($value);

    /**
     * Set if user is logged in
     * @var $value boolean
     * @return boolean
     */
    public function setIsLoggedIn($value);

    /**
     * Set current store code
     * @var $value string
     * @return null
     */
    public function setStoreCode($value);
}
