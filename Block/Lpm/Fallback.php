<?php
declare(strict_types=1);

namespace Magento\Braintree\Block\Lpm;

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
     * @return array
     */
    public function getFallbackData()
    {
        return $this->fallbackData;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setFallbackData(array $data)
    {
        $this->fallbackData = $data;
        return $this;
    }
}
