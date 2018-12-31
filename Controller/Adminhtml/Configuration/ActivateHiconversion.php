<?php

namespace Magento\Braintree\Controller\Adminhtml\Configuration;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Zend\Http\Client;
use Zend\Http\Headers;
use Zend\Http\Request;

/**
 * Class ActivateHiconversion
 */
class ActivateHiconversion extends \Magento\Backend\App\Action
{
    const BASE_URL = "http://h30-local.hiconversion.net:9000/api/extensions/";
    const CREATE_ACCOUNT_URL = self::BASE_URL . "signup";
    const GET_SITES_URL = self::BASE_URL . "user/sites";

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface;
     */
    private $configWriter;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $curl;

    private $logger;

    /**
     * Validate constructor.
     *
     * @param Action\Context  $context
     * @param WriterInterface $configWriter
     */
    public function __construct(
        Action\Context $context,
        WriterInterface $configWriter,
        Curl $curl,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->configWriter = $configWriter;
        $this->curl = $curl;

        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $siteUrl = $this->getRequest()->getParam("site_url");
        $email = $this->getRequest()->getParam("email");
        $pw = $this->getRequest()->getParam("password");
        $storeId = $this->getRequest()->getParam("storeId", 0);

        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->curl->setOption(CURLOPT_ENCODING, "");
            $this->curl->setOption(CURLOPT_MAXREDIRS, 10);
            $this->curl->setOption(CURLOPT_TIMEOUT, 30);
            $this->curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            $this->curl->setHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest'
                ]);

            $this->curl->post(self::CREATE_ACCOUNT_URL, json_encode([
                'email' => $email,
                'url' => $siteUrl,
                'password' => $pw,
                'leadSource' => 'Braintree-Magento'
            
            ]));
            
            $this->logger->debug("HIC CURL BODY:" . $this->curl->getBody());

            $result = json_decode($this->curl->getBody(), true);

            $this->logger->debug("HIC SIGNUP RESULTS:" . print_r($result, true));
            if (isset($result) && $result['result'] == "success") {
                $this->curl->post(self::GET_SITES_URL, json_encode([
                    'email' => $email
                ]));
                $sites = json_decode($this->curl->getBody(), true);
                $this->logger->debug("HIC SITES: " . print_r($sites, true));
                if (is_array($sites)) {
                    foreach ($sites as $site) {
                        if (isset($site['url']) & $site['url'] == $siteUrl) {
                            $this->logger->debug("SETTING SITE ID " . $site['external']);
                            $this->configWriter->
                            save(
                                'hiconversion/configuration/site_id',
                                $site['external'],
                                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                            );
                            break;
                        }
                    }
                }
                $response->setHttpResponseCode(200);
            } else {
                $response->setHttpResponseCode(400);
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
            $response->setHttpResponseCode(400);
        }

        return $response;
    }
}
