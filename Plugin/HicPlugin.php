<?php
/**
 * HiConversion
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * [http://opensource.org/licenses/MIT]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @Copyright © 2015 HiConversion, Inc. All rights reserved.
 * @license [http://opensource.org/licenses/MIT] MIT License
 */

namespace Magento\Braintree\Plugin;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Page\Config\Renderer;

/**
 * Plugin for injecting head content at top of head
 *
 * @author HiConversion <support@hiconversion.com>
 */
class HicPlugin
{
  
    /**
     * @var ObjectManagerInterface
     */
    private $manager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->manager = $objectManager;
    }

    /**
     * @param string $templateName
     * @return string
     **/
    private function getBlockHtml($templateName)
    {
        return $this->manager->create('Magento\Framework\View\Element\Template')
            ->setTemplate('Magento_Braintree::hic/' . $templateName)
            ->toHtml();
    }

    /**
     * @param Renderer $subject
     * @param string $html
     * @return string
     */
    public function afterRenderHeadContent(Renderer $subject, $html)
    {
        $tagAlways = $this->getBlockHtml('headAlways.phtml');

        $tagPage = $this->getBlockHtml('headPage.phtml');

        $tagNever = $this->getBlockHtml('headNever.phtml');

        return $tagAlways . $tagPage . $tagNever . $html;
    }
}
