<?php

namespace IctCollege\Stagemarkt\Response;

class SearchResponse extends Response
{

    private $__positions = [];
    private $__companies = [];
    private $__total = [];

    /**
     * @param array $results
     *
     * @return $this
     */
    public function positions($results = null)
    {
        if ($results === null) {
            return $this->__positions;
        }

        $this->__positions = $results;

        return $this;
    }

    /**
     * @param array $results
     *
     * @return $this
     */
    public function companies($results = null)
    {
        if ($results === null) {
            return $this->__companies;
        }

        $this->__companies = $results;

        return $this;
    }

    public function total($total = null)
    {
        if ($total === null) {
            return $this->__total;
        }

        $this->__total = $total;

        return $this;
    }
}
