<?php

namespace IctCollege\Stagemarkt\Model\Endpoint;

use IctCollege\Stagemarkt\Model\SearchableTrait;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Schema;

class CompaniesEndpoint extends Endpoint
{

    use SearchableTrait;

    public $filterArgs = [
        'name' => [
            'type' => 'like'
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $schema = new Schema(null, [
            'id' => [
                'type' => 'string'
            ],
            'name' => [
                'type' => 'string'
            ]
        ]);
        $schema->addConstraint('primary', [
            'type' => Schema::CONSTRAINT_PRIMARY,
            'columns' => 'id'
        ]);
        $this->schema($schema);
        $this->webservice('search');
    }

    /**
     * {@inheritDoc}
     */
    public function find($type = 'all', $options = [])
    {
        $query = parent::find($type, $options);

        $query->where([
            'type' => 'company'
        ]);

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function get($primaryKey, $options = [])
    {
        $this->webservice('details');

        $result = parent::get($primaryKey, $options);

        $this->webservice('search');

        return $result;
    }
}
