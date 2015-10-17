<?php

namespace IctCollege\Stagemarkt\Soap;

use IctCollege\Stagemarkt\Soap\Exception\NoLicenseException;
use IctCollege\Stagemarkt\Stagemarkt;

abstract class SoapClient extends \SoapClient
{

    /**
     * @var Stagemarkt
     */
    protected $_stagemarktClient;

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

    /**
     * Return the testing URL
     *
     * @return string
     */
    abstract public function testingUrl();

    /**
     * Return the live URL
     *
     * @return mixed
     */
    abstract public function liveUrl();

    /**
     * Return the property in which the actual result is in
     *
     * @return string
     */
    abstract public function resultProperty();

    /**
     * Return an instance of the Stagemarkt client
     *
     * @param null|\IctCollege\Stagemarkt\Stagemarkt $stagemarktClient The Stagemarkt client instance to set
     *
     * @return \IctCollege\Stagemarkt\Stagemarkt|$this
     */
    public function stagemarktClient(Stagemarkt $stagemarktClient = null)
    {
        if ($stagemarktClient === null) {
            return $this->_stagemarktClient;
        }

        $this->_stagemarktClient = $stagemarktClient;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \IctCollege\Stagemarkt\Soap\Exception\NoLicenseException
     */
    public function __call($functionName, $arguments)
    {
        array_unshift($arguments, $functionName);

        $response = call_user_func_array([$this, '__soapCall'], $arguments);

        $result = $response->{$this->resultProperty()};
        switch ($result->Signaalcode) {
            case 996:
                throw new NoLicenseException();
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function __soapCall($functionName, $arguments, $options = [], $inputHeaders = [], &$outputHeaders = [])
    {
        $arguments['Licentie'] = $this->_license;

        $arguments = [
            'request' => $arguments
        ];

        return parent::__soapCall($functionName, [$arguments], $options, $inputHeaders, $outputHeaders);
    }

    /**
     * {@inheritDoc}
     */
    public function __debugInfo()
    {
        return [
            'license' => $this->_license,
            'wsdl' => $this->_wsdl
        ];
    }
}
