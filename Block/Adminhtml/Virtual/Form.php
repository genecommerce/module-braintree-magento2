<?php

namespace Magento\Braintree\Block\Adminhtml\Virtual;

/**
 * Class Form
 * @package Magento\Braintree\Block\Adminhtml\Virtual
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Form extends \Magento\Backend\Block\Widget\Form\Container
{
    protected function _construct() // @codingStandardsIgnoreLine
    {
        $this->_blockGroup = 'Magento_Braintree';
        $this->_controller = 'adminhtml_virtual';
        parent::_construct();

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('save');
        $this->addButton(
            'save',
            [
                'label' => __('Take Payment'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'takePayment', 'target' => '#payment_form_braintree']],
                ]
            ],
            1
        );
    }
}
