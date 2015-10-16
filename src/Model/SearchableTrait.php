<?php

namespace IctCollege\Stagemarkt\Model;

use Muffin\Webservice\Query;

trait SearchableTrait
{
    /**
     * findSearchable
     * Parses the get parameters into query conditions based on the rules defined in the table filterArgs property
     *
     * @param Query $query The query to find with
     * @param array $data Criteria of key->value pairs from post/named parameters
     *
     * @return Query
     */
    public function findSearchable(Query $query, $data)
    {
        $this->setupFilterArgs();

        foreach ($this->filterArgs as $field) {
            // If this field was not passed and a default value exists, use that instead.
            if (!array_key_exists($field['name'], $data) && array_key_exists('defaultValue', $field)) {
                $data[$field['name']] = $field['defaultValue'];
            }

            if (!isset($data[$field['name']])) {
                continue;
            }

            switch ($field['type']) {
                case 'like':
                    $query = $query->conditions([$field['name'] => '%' . $data[$field['name']] . '%']);

                    break;
                case 'value':
                    $query = $query->conditions([$field['name'] => $data[$field['name']]]);
            }
        }
        return $query;
    }

    /**
     * Prepares the filter args based on the model information and calls
     * Model::getFilterArgs if present to set up the filterArgs with proper model
     * aliases.
     *
     * @return bool|array
     */
    public function setupFilterArgs()
    {
        if (method_exists($this, 'getFilterArgs')) {
            $this->getFilterArgs();
        }
        if (empty($this->filterArgs)) {
            return false;
        }
        foreach ($this->filterArgs as $key => $val) {
            if (!isset($val['name'])) {
                $this->filterArgs[$key]['name'] = $key;
            }
            if (!isset($val['field'])) {
                $this->filterArgs[$key]['field'] = $this->filterArgs[$key]['name'];
            }
            if (!isset($val['type'])) {
                $this->filterArgs[$key]['type'] = 'value';
            }
        }
        return $this->filterArgs;
    }
}
