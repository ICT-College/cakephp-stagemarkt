<?php

namespace IctCollege\Stagemarkt\Response;

use IctCollege\Stagemarkt\Entity\Company;
use IctCollege\Stagemarkt\Entity\Position;

class DetailsResponse extends Response
{

    private $__position = [];
    private $__company = [];

    /**
     * @param Position $result
     *
     * @return $this
     */
    public function position($result = null)
    {
        if ($result === null) {
            return $this->__position;
        }

        $this->__position = $result;

        return $this;
    }

    /**
     * @param Company $result
     *
     * @return $this
     */
    public function company($result = null)
    {
        if ($result === null) {
            return $this->__company;
        }

        $this->__company = $result;

        return $this;
    }
}
