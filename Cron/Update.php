<?php

namespace Magento\Braintree\Cron;

use Exception;
use Magento\AdminNotification\Model\Inbox;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Notification\NotifierInterface as NotifierPool;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Update
 * @package Magento\Braintree\Cron
 */
class Update
{
    const LEVEL_1 = 'critical';
    const LEVEL_2 = 'major';
    const LEVEL_3 = 'minor';
    const LEVEL_4 = 'notice';

    /**
     * @var NotifierPool
     */
    private $notifier;

    /**
     * @var Inbox
     */
    private $inbox;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var array
     */
    private $updateInformation;

    /**
     * Update constructor.
     * @param NotifierPool $notifier
     * @param Inbox $inbox
     * @param ModuleListInterface $moduleList
     * @param StoreManagerInterface $storeManager
     * @param Curl $curl
     * @param Json $json
     */
    public function __construct(
        NotifierPool $notifier,
        Inbox $inbox,
        ModuleListInterface $moduleList,
        StoreManagerInterface $storeManager,
        Curl $curl,
        Json $json
    ) {
        $this->notifier = $notifier;
        $this->inbox = $inbox;
        $this->moduleList = $moduleList;
        $this->storeManager = $storeManager;
        $this->curl = $curl;
        $this->json = $json;
    }

    /**
     * @return $this
     */
    public function execute(): self
    {
        if ($this->updateAvailable()) {
            $this->addNotification();
        }

        return $this;
    }

    /**
     * Determine if an update is available
     * @return bool
     */
    private function updateAvailable()
    {
        $moduleData = $this->moduleList->getOne('Magento_Braintree');
        if (empty($moduleData['setup_version'])) {
            return false;
        }

        // We send two bits of information; current version and store URL
        // We send store URL in order to streamline our support service, allowing us to determine the
        // version in use and provide appropriate support. if you'd like to remove this, remove the cron entry in
        // crontab.xml.
        $currentVersion = $moduleData['setup_version'];

        try {
            $storeUrl = $this->storeManager->getDefaultStoreView()
                ->getBaseUrl(UrlInterface::URL_TYPE_WEB);

            $this->curl->post(
                'https://braintree.gene.co.uk/',
                [
                    'magento' => '2',
                    'version' => $currentVersion,
                    'storeUrl' => $storeUrl
                ]
            );
            $response = $this->curl->getBody();
            if (!$response) {
                return false;
            }

            $response = $this->json->unserialize($response);
            if (isset($response['latest']) && $response['latest'] === false) {
                $this->updateInformation = [
                    'title' => $response['title'],
                    'message' => $response['message'],
                    'link' => $response['link'],
                    'level' => $this->getUpdateLevel($response['level'])
                ];
                return true;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return $this
     */
    private function addNotification(): self
    {
        $title = $this->updateInformation['title'];
        $message = $this->updateInformation['message'];
        $link = $this->updateInformation['link'];
        $level = $this->updateInformation['level'];

        if ($this->notificationExists($title)) {
            return $this;
        }

        switch ($level) {
            case self::LEVEL_1:
                $this->notifier->addCritical($title, $message, $link);
                break;
            case self::LEVEL_2:
                $this->notifier->addMajor($title, $message, $link);
                break;
            case self::LEVEL_3:
                $this->notifier->addMinor($title, $message, $link);
                break;
            case self::LEVEL_4:
                $this->notifier->addNotice($title, $message, $link);
                break;
        }

        return $this;
    }

    /**
     * Determine if a notification has been added already
     * @param $title
     * @return bool
     */
    private function notificationExists($title)
    {
        try {
            $resource = $this->inbox->getResource();
            $connection = $resource->getConnection();
            $select = $connection->select()->from(
                $resource->getMainTable()
            )->order(
                $resource->getIdFieldName() . ' DESC'
            )->where(
                'title = ?',
                $title
            )->limit(
                1
            );
            $data = $connection->fetchRow($select);
            if (!empty($data['notification_id'])) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * @param $level
     * @return string
     */
    private function getUpdateLevel($level)
    {
        switch ($level) {
            case self::LEVEL_1:
                return self::LEVEL_1;
            case self::LEVEL_2:
                return self::LEVEL_2;
            case self::LEVEL_3:
                return self::LEVEL_3;
            default:
                return self::LEVEL_4;
        }
    }
}
