<?php

namespace Magento\Braintree\Model\CustomFields;

/**
 * Interface CustomFieldInterface
 * @package Magento\Braintree\Model\CustomFields
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
interface CustomFieldInterface
{
    /**
     * API Name as defined in the Braintree Control Panel
     *
     * @return string
     */
    public function getApiName(): string;

    /**
     * Value for the field
     *
     * @param array $buildSubject When used with SubjectReader this will return information about the order
     * @see \Magento\Braintree\Gateway\Helper\SubjectReader
     * @return mixed
     */
    public function getValue($buildSubject);
}
