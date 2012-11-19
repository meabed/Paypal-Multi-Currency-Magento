<?php

class Web_Ppfix_Model_Config extends Mage_Paypal_Model_Config
{
    protected $_supportedCurrencyCodes = array('AUD', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN',
        'NOK', 'NZD', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'USD', 'TWD', 'THB');

    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_supportedCurrencyCodes = array_merge($this->_supportedCurrencyCodes, Mage::helper('ppfix')->getCurrencyArray());
    }
}