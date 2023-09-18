<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\Paypal;

use Magento\Braintree\Model\Paypal\CreditApi;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception;

/**
 * Class GetNonce
 */
class Cart extends Action
{
    /**
     * @var CreditApi
     */
    private $creditApi;

    /**
     * Cart constructor.
     * @param Context $context
     * @param CreditApi $creditApi
     */
    public function __construct(
        Context $context,
        CreditApi $creditApi
    ) {
        parent::__construct($context);
        $this->creditApi = $creditApi;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $amount = number_format((float)$this->getRequest()->getParam('amount', 0), 2, '.', '');

        if (!$amount || $amount <= 0) {
            return $this->processBadRequest($response);
        }

        try {
            $results = $this->creditApi->getPriceOptions($amount);
            $options = [];
            foreach ($results as $priceOption) {
                $options[] = [
                    'term' => $priceOption['term'],
                    'monthlyPayment' => $priceOption['monthly_payment'],
                    'apr' => $priceOption['instalment_rate'],
                    'cost' => $priceOption['cost_of_purchase'],
                    'costIncInterest' => $priceOption['total_inc_interest']
                ];
            }

            // Sort $options by term, ascending.
            usort($options, static function ($a, $b) {
                return $a['term'] <=> $b['term'];
            });

            $response->setData($options);
        } catch (\Exception|LocalizedException $exception) {
            return $this->processBadRequest($response, $exception);
        }

        return $response;
    }

    /**
     * Return response for bad request
     * @param ResultInterface $response
     * @param \Exception|LocalizedException|null $exception
     * @return ResultInterface
     */
    private function processBadRequest(ResultInterface $response, $exception = null): ResultInterface
    {
        $response->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
        if ($exception === null || empty($exception->getMessage())) {
            $response->setData([
                'message' => __('No Credit Options available')
            ]);
        } else {
            $response->setData([
                'message' => __($exception->getMessage())
            ]);
        }

        return $response;
    }
}
