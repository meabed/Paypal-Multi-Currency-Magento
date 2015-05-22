<?php

class Web_Ppfix_Model_Source_Currency extends Mage_Adminhtml_Model_System_Config_Source_Currency
{
    protected $_options;

    public function toOptionArray($isMultiselect)
    {
        $_supportedCurrencyCodes = Mage::helper('ppfix')->getSupportedCurrency();
        if (!$this->_options) {
            $this->_options = Mage::app()->getLocale()->getOptionCurrencies();
        }
        $options = array();
        foreach ($this->_options as $option) {
            if (in_array($option['value'], $_supportedCurrencyCodes)) {
                $options[] = $option;
            }
        }
        return $options;
    }
}