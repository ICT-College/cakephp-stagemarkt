<?php

namespace IctCollege\Stagemarkt\Soap;

use DebugKit\DebugTimer;

abstract class SoapClient extends \SoapClient
{

    protected $_license;
    protected $_wsdl;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $options = null)
    {
        if (!isset($options['testing'])) {
            $options['testing'] = false;
        }

        $this->_license = $options['license'];
        $this->_wsdl = ($options['testing']) ? $this->testingUrl() : $this->liveUrl();

        parent::__construct($this->_wsdl, $options);
    }

    abstract public function testingUrl();

    abstract public function liveUrl();

    public function __call($function_name, $arguments)
    {
        array_unshift($arguments, $function_name);

        $response = call_user_func_array([$this, '__soapCall'], $arguments);

        return $response->{$this->resultProperty()};
    }

    abstract public function resultProperty();

    /**
     * {@inheritDoc}
     */
    public function __soapCall($function_name, $arguments, $options = [], $input_headers = [], &$output_headers = [])
    {
        DebugTimer::start('SoapRequest');
        $arguments['Licentie'] = $this->_license;

        $arguments = [
            'request' => $arguments
        ];

        $soapCall = parent::__soapCall($function_name, [$arguments], $options, $input_headers, $output_headers);

        DebugTimer::stop('SoapRequest');

        return $soapCall;
    }

    public function __debugInfo()
    {
        return [
            'license' => $this->_license,
            'wsdl' => $this->_wsdl
        ];
    }
}
