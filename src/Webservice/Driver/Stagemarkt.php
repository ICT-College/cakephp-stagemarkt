<?php

namespace IctCollege\Stagemarkt\Webservice\Driver;

use Cake\Database\Log\QueryLogger;
use IctCollege\Stagemarkt\Stagemarkt as StagemarktClient;
use Muffin\Webservice\AbstractDriver;

class Stagemarkt extends AbstractDriver
{

    protected $_defaultConfig = [
        'testing' => false
    ];

    /**
     * Whether to log queries generated during this connection.
     *
     * @var bool
     */
    protected $_logQueries = false;

    /**
     * Logger object instance.
     *
     * @var QueryLogger
     */
    protected $_logger = null;

    /**
     * Initialize is used to easily extend the constructor.
     *
     * @return void
     */
    public function initialize()
    {
        $this->_client = new StagemarktClient($this->config());
        $this->_client->logger($this->logger());

        $this->webservice('search', $this->_client->searchClient());
    }

    public function logger($instance = null)
    {
        if ($instance === null) {
            if ($this->_logger === null) {
                $this->_logger = new QueryLogger;
            }
            return $this->_logger;
        }

        if ($this->_client) {
            $this->_client->logger($instance);
        }

        $this->_logger = $instance;
    }

    public function configName()
    {
        if (empty($this->_config['name'])) {
            return '';
        }
        return $this->_config['name'];
    }

    public function logQueries($enable = null)
    {
        if ($enable === null) {
            return $this->_logQueries;
        }
        $this->_logQueries = $enable;
    }
}
