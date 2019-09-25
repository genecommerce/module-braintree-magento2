<?php
declare(strict_types=1);

namespace Magento\Braintree\Webapi\Rest\Request\Deserializer;

use Magento\Framework\Webapi\Rest\Request\DeserializerInterface as DeserializerInterfaceAlias;

/**
 * Class XWwwFormUrlencoded
 *
 * @author Paul Canning <paul.canning@gene.co.uk>
 */
class XWwwFormUrlencoded implements DeserializerInterfaceAlias
{
    /**
     * Parse Request body into array of params.
     *
     * @param string $encodedBody Posted content from request.
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function deserialize($encodedBody)
    {
        if (!is_string($encodedBody)) {
            throw new \InvalidArgumentException(
                __("'%s' data type is invalid. String is expected.", gettype($encodedBody))
            );
        }
        return $encodedBody;
    }
}
