<?php

namespace IctCollege\Stagemarkt\Soap\Response;

class Response
{

    private $__code;

    /**
     * @param int $code The response code returned by the Stagemarkt API
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->__code = $code;

        return $this;
    }
}
