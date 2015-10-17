<?php

namespace IctCollege\Stagemarkt\Soap\Response;

class DetailsResponse extends Response
{

    private $__position = [];
    private $__company = [];

    /**
     * @param array $result
     *
     * @return array|$this
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
     * @param array $result
     *
     * @return array|$this
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
