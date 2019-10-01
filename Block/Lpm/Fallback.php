<?php
declare(strict_types=1);

namespace Magento\Braintree\Block\Lpm;

use Magento\Braintree\Model\Lpm\Config;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;

/**
 * Class Fallback
 */
class Fallback extends Template
{
    /**
     * @var array $fallbackDataa
     */
    private $fallbackData;
    /**
     * @var Config
     */
    private $config;

    /**
     * Fallback constructor.
     *
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(Template\Context $context, Config $config, array $data = [])
    {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getClientToken(): string
    {
        return $this->config->getClientToken();
    }

    /**
     * @return array
     */
    public function getFallbackData(): array
    {
        return $this->fallbackData;
    }

    /**
     * @param array $data
     * @return self
     */
    public function setFallbackData(array $data): self
    {
        $this->fallbackData = $data;
        return $this;
    }
}
