<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class BnCodeDataBuilder
 */
class ChannelDataBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    private static $channel = 'channel';

    /**
     * @var string
     */
    private static $channelValue = 'Magento2GeneBT';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        return [
            self::$channel => self::$channelValue
        ];
    }
}
