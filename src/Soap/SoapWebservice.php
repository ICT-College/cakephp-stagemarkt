<?php

namespace IctCollege\Stagemarkt\Soap;

use Cake\Database\Log\LoggedQuery;
use Cake\Database\Log\QueryLogger;
use DebugKit\DebugTimer;
use DebugKit\Log\Engine\DebugKitLog;
use Muffin\Webservice\Webservice\WebserviceInterface;
use Psr\Log\LoggerAwareTrait;

abstract class SoapWebservice extends SoapClient implements WebserviceInterface
{

    use LoggerAwareTrait;

    protected $_logger;

    /**
     * @param QueryLogger|null $logger
     * @return QueryLogger|$this
     */
    public function logger(QueryLogger $logger = null)
    {
        if ($logger === null) {
            return $this->_logger;
        }

        $this->_logger = $logger;

        return $this;
    }

    public function __soapCall($function_name, $arguments, $options = [], $input_headers = [], &$output_headers = [])
    {
        $query = new LoggedQuery;
        $query->query = $function_name;
        $params = [];
        foreach ($arguments as $key => $value) {
            if (is_array($value)) {
                $params[$key] = print_r($value, true);

                continue;
            }

            $params[$key] = $value;

        }

        DebugTimer::start('stagemarkt-' . $function_name);

        $response = parent::__soapCall($function_name, $arguments, $options, $input_headers, $output_headers);

        DebugTimer::stop('stagemarkt-' . $function_name);

        $this->logger()->log($query);

        $this->setLogger(new DebugKitLog());

        return $response;
    }
}
