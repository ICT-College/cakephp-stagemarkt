<?php

namespace IctCollege\Stagemarkt\Response;

class Response
{

    private $__code;

    /**
     * @param mixed $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->__code = $code;

        return $this;
    }

}
