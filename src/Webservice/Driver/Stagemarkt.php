<?php

namespace IctCollege\Stagemarkt\Webservice\Driver;

use IctCollege\Stagemarkt\Stagemarkt as StagemarktClient;
use Muffin\Webservice\AbstractDriver;

class Stagemarkt extends AbstractDriver
{

    protected $_defaultConfig = [
        'testing' => false
    ];

    /**
     * Initialize is used to easily extend the constructor.
     *
     * @return void
     */
    public function initialize()
    {
        $this->_client = new StagemarktClient($this->config());
    }
}
