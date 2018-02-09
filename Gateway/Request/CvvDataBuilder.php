<?php

namespace Magento\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Braintree\Gateway\Config\Config;

/**
 * Class CvvDataBuilder
 */
class CvvDataBuilder implements BuilderInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var Config
     */
    private $config;

    /**
     * CvvDataBuilder constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        Config $config
    ) {
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        if (!$this->request->isSecure() || !$this->config->isCvvEnabledVault()) {
            return [];
        }

        try {
            $input = file_get_contents('php://input');
            if ($input) {
                $input = json_decode($input, true);
                if (!empty($input['paymentMethod']['additional_data']['cvv'])) {
                    return [
                        'creditCard' => [
                            'cvv' => $input['paymentMethod']['additional_data']['cvv']
                        ]
                    ];
                }
            }
        } catch (\Exception $e) {
            // do nothing
        }

        return [];
    }
}
