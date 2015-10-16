<?php

namespace IctCollege\Stagemarkt\Soap;

use IctCollege\Stagemarkt\Stagemarkt;

abstract class StagemarktService extends SoapWebservice
{

    /**
     * @var Stagemarkt
     */
    protected $_stagemarktClient;

    /**
     * @param Stagemarkt|null $stagemarktClient
     * @return $this|Stagemarkt
     */
    public function stagemarktClient(Stagemarkt $stagemarktClient = null)
    {
        if ($stagemarktClient === null) {
            return $this->_stagemarktClient;
        }

        $this->_stagemarktClient = $stagemarktClient;

        return $this;
    }

}
