<?php

namespace IctCollege\Stagemarkt\Model\Endpoint;

use IctCollege\Stagemarkt\Model\SearchableTrait;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Schema;

class PositionsEndpoint extends Endpoint
{

    use SearchableTrait;

    public $filterArgs = [
        'company_id' => [
            'type' => 'value'
        ],
        'company_name' => [
            'type' => 'like'
        ],
        'company_address_number' => [
            'type' => 'value'
        ],
        'company_address_street' => [
            'type' => 'value'
        ],
        'company_address_postcode' => [
            'type' => 'value'
        ],
        'company_address_city' => [
            'type' => 'like'
        ],
        'company_address_country' => [
            'type' => 'value'
        ],
        'study_program_id' => [
            'type' => 'value'
        ],
        'learning_pathway' => [
            'type' => 'value'
        ],
        'description' => [
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
            'type' => 'position'
        ]);

        return $query;
    }
}
